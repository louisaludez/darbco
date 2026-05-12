-- ============================================================
-- DARBCO System — Paper Forms Digitization Migration
-- Run this SQL against your darbco_system database
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. ADD sub_code TO workers TABLE (if not already present)
-- ============================================================
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'darbco_system' AND TABLE_NAME = 'workers' AND COLUMN_NAME = 'sub_code');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE workers ADD COLUMN sub_code VARCHAR(20) DEFAULT NULL AFTER worker_id',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Also add area field to workers (for Harvest Proceeds form)
SET @col_exists2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'darbco_system' AND TABLE_NAME = 'workers' AND COLUMN_NAME = 'area');
SET @sql2 = IF(@col_exists2 = 0,
    'ALTER TABLE workers ADD COLUMN area VARCHAR(100) DEFAULT NULL AFTER contact_number',
    'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;


-- ============================================================
-- 2. EXTEND production_data TABLE
--    Adds fields from Daily Harvest Sheet (Form 3)
-- ============================================================
ALTER TABLE production_data
    ADD COLUMN IF NOT EXISTS stems_cut       INT UNSIGNED NOT NULL DEFAULT 0 AFTER boxes_produced,
    ADD COLUMN IF NOT EXISTS group_number    TINYINT UNSIGNED DEFAULT NULL AFTER stems_cut,
    ADD COLUMN IF NOT EXISTS block_number    VARCHAR(20)  DEFAULT NULL AFTER group_number,
    ADD COLUMN IF NOT EXISTS carrier_name    VARCHAR(100) DEFAULT NULL AFTER block_number,
    ADD COLUMN IF NOT EXISTS arrival_time    TIME         DEFAULT NULL AFTER carrier_name,
    ADD COLUMN IF NOT EXISTS first_box_out   TIME         DEFAULT NULL AFTER arrival_time,
    ADD COLUMN IF NOT EXISTS last_box_out    TIME         DEFAULT NULL AFTER first_box_out,
    ADD COLUMN IF NOT EXISTS week_number     VARCHAR(10)  DEFAULT NULL AFTER last_box_out,
    ADD COLUMN IF NOT EXISTS cycle_code      VARCHAR(30)  DEFAULT NULL AFTER week_number;

-- Index for week/cycle queries
CREATE INDEX IF NOT EXISTS idx_prod_week ON production_data (week_number);
CREATE INDEX IF NOT EXISTS idx_prod_cycle ON production_data (cycle_code);


-- ============================================================
-- 3. CREATE production_stem_details TABLE
--    Per-row stem counts (columns 11,12,13,14 from Harvest Sheet)
-- ============================================================
CREATE TABLE IF NOT EXISTS production_stem_details (
    detail_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    production_id   INT UNSIGNED NOT NULL,
    row_number      TINYINT UNSIGNED NOT NULL COMMENT 'Row 11, 12, 13, or 14',
    stem_count      INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_stemdetail_production
        FOREIGN KEY (production_id) REFERENCES production_data (production_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_stemdetail_prod (production_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. CREATE production_box_breakdown TABLE (if not exists)
-- ============================================================
CREATE TABLE IF NOT EXISTS production_box_breakdown (
    breakdown_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    production_id    INT UNSIGNED NOT NULL,
    box_class        CHAR(1) NOT NULL COMMENT 'A or B',
    box_spec         VARCHAR(50) NOT NULL COMMENT 'e.g. 4 Hands, BCP, SH, F.P',
    tally_count      INT UNSIGNED NOT NULL DEFAULT 0,
    adjusted_count   INT UNSIGNED NOT NULL DEFAULT 0,
    should_be_count  INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_boxbreakdown_production
        FOREIGN KEY (production_id) REFERENCES production_data (production_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_boxbrk_prod (production_id),
    INDEX idx_boxbrk_class (box_class)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. OVERHAUL payroll_data TABLE
--    Add fields from Harvest Proceeds (Form 4)
-- ============================================================
ALTER TABLE payroll_data
    ADD COLUMN IF NOT EXISTS area            VARCHAR(100) DEFAULT NULL AFTER worker_id,
    ADD COLUMN IF NOT EXISTS week_number     VARCHAR(10)  DEFAULT NULL AFTER area,
    ADD COLUMN IF NOT EXISTS cycle_code      VARCHAR(30)  DEFAULT NULL AFTER week_number,
    ADD COLUMN IF NOT EXISTS forex_rate      DECIMAL(10,4) NOT NULL DEFAULT 1.0000 AFTER rate_per_box,
    ADD COLUMN IF NOT EXISTS total_material_cost  DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER deductions,
    ADD COLUMN IF NOT EXISTS total_labor_cost     DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER total_material_cost,
    ADD COLUMN IF NOT EXISTS total_personal       DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER total_labor_cost,
    ADD COLUMN IF NOT EXISTS cash_advance         DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER total_personal,
    ADD COLUMN IF NOT EXISTS guaranteed_income    DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER cash_advance,
    ADD COLUMN IF NOT EXISTS other_deductions     DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER guaranteed_income,
    ADD COLUMN IF NOT EXISTS total_contributions  DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER other_deductions,
    ADD COLUMN IF NOT EXISTS bs_ratio             DECIMAL(6,3) DEFAULT NULL AFTER total_contributions,
    ADD COLUMN IF NOT EXISTS stems_cut_payroll    INT UNSIGNED NOT NULL DEFAULT 0 AFTER bs_ratio;


-- ============================================================
-- 6. CREATE payroll_box_details TABLE
--    Per-spec pricing from Harvest Proceeds form
-- ============================================================
CREATE TABLE IF NOT EXISTS payroll_box_details (
    detail_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_id      INT UNSIGNED NOT NULL,
    box_spec        VARCHAR(50) NOT NULL COMMENT 'e.g. 456H, CB HP, CS FD',
    quantity        INT UNSIGNED NOT NULL DEFAULT 0,
    price_per_box   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    forex_rate      DECIMAL(10,4) NOT NULL DEFAULT 1.0000,
    amount          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_payboxdetail_payroll
        FOREIGN KEY (payroll_id) REFERENCES payroll_data (payroll_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_payboxdetail_payroll (payroll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. CREATE payroll_deductions TABLE
--    Itemized deductions (labor, materials, personal, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS payroll_deductions (
    deduction_id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_id      INT UNSIGNED NOT NULL,
    category        ENUM(
                        'material',
                        'labor',
                        'personal',
                        'cash_advance',
                        'contribution',
                        'other'
                    ) NOT NULL DEFAULT 'other',
    description     VARCHAR(200) NOT NULL,
    quantity        DECIMAL(10,2) DEFAULT NULL,
    unit_cost       DECIMAL(10,2) DEFAULT NULL,
    amount          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_paydeduction_payroll
        FOREIGN KEY (payroll_id) REFERENCES payroll_data (payroll_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_paydeduction_payroll (payroll_id),
    INDEX idx_paydeduction_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. CREATE payroll_contributions TABLE
--    Tracks contributions like Dale Capital, CEFUAPCO, etc.
-- ============================================================
CREATE TABLE IF NOT EXISTS payroll_contributions (
    contribution_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payroll_id      INT UNSIGNED NOT NULL,
    contribution_type VARCHAR(100) NOT NULL COMMENT 'e.g. Dale Capital Share, CEFUAPCO CBU',
    previous_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    current_amount  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    running_total   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_paycontrib_payroll
        FOREIGN KEY (payroll_id) REFERENCES payroll_data (payroll_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_paycontrib_payroll (payroll_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. ALTER production_materials TABLE
--    Add unit_price field for materials used
-- ============================================================
ALTER TABLE production_materials
    ADD COLUMN IF NOT EXISTS unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantity_used;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DONE. All paper form fields are now available in the database.
-- ============================================================
