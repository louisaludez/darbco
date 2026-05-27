-- ============================================================
-- DARBCO System — Harvest Parameter & Daily Report Migration
-- Run this SQL against your darbco_system database
-- ============================================================

USE darbco_system;

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. HARVEST PARAMETERS (Form 1)
-- ============================================================
CREATE TABLE IF NOT EXISTS harvest_parameters (
    hp_id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    harvest_date        DATE NOT NULL,
    cutting_group       VARCHAR(50) DEFAULT NULL,
    crew_size           INT UNSIGNED DEFAULT 0,
    manhours            DECIMAL(10,2) DEFAULT 0.00,
    stem_cut            INT UNSIGNED DEFAULT 0,
    farm_rejects_total  INT UNSIGNED DEFAULT 0,
    ave_fingerlength    DECIMAL(10,2) DEFAULT 0.00,
    ave_handclass       DECIMAL(10,2) DEFAULT 0.00,
    ave_stem_weight     DECIMAL(10,2) DEFAULT 0.00,
    percent_area_covered DECIMAL(10,2) DEFAULT 0.00,
    ave_calibration     DECIMAL(10,2) DEFAULT 0.00,
    recorded_by         INT UNSIGNED NOT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_hp_recorded_by FOREIGN KEY (recorded_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_hp_date (harvest_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hp_calibrations (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hp_id       INT UNSIGNED NOT NULL,
    data_type   ENUM('CALIBRATION', 'COLOR_CODE') NOT NULL,
    week_11     VARCHAR(20) DEFAULT NULL,
    week_12     VARCHAR(20) DEFAULT NULL,
    week_13     VARCHAR(20) DEFAULT NULL,
    week_14     VARCHAR(20) DEFAULT NULL,
    CONSTRAINT fk_hpcal_hp FOREIGN KEY (hp_id) REFERENCES harvest_parameters (hp_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hp_farm_rejects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hp_id       INT UNSIGNED NOT NULL,
    code_11     VARCHAR(20) DEFAULT NULL,
    code_12     VARCHAR(20) DEFAULT NULL,
    code_13     VARCHAR(20) DEFAULT NULL,
    code_14     VARCHAR(20) DEFAULT NULL,
    total       VARCHAR(20) DEFAULT NULL,
    CONSTRAINT fk_hprej_hp FOREIGN KEY (hp_id) REFERENCES harvest_parameters (hp_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS hp_defects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hp_id       INT UNSIGNED NOT NULL,
    defect_name VARCHAR(100) NOT NULL,
    age_8_wks   VARCHAR(20) DEFAULT NULL,
    age_9_wks   VARCHAR(20) DEFAULT NULL,
    age_10_wks  VARCHAR(20) DEFAULT NULL,
    age_11_wks  VARCHAR(20) DEFAULT NULL,
    total       VARCHAR(20) DEFAULT NULL,
    CONSTRAINT fk_hpdef_hp FOREIGN KEY (hp_id) REFERENCES harvest_parameters (hp_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. DAILY PRODUCTION REPORTS (Form 2 / Efficiency Performance)
-- ============================================================
CREATE TABLE IF NOT EXISTS daily_production_reports (
    dpr_id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_date         DATE NOT NULL,
    week_no             VARCHAR(10) DEFAULT NULL,
    brand_name          VARCHAR(100) DEFAULT NULL,
    crew_size           INT UNSIGNED DEFAULT 0,
    first_fruit_in      TIME DEFAULT NULL,
    last_box_out        TIME DEFAULT NULL,
    first_box_out       TIME DEFAULT NULL,
    volume_stems_cut    INT UNSIGNED DEFAULT 0,
    bs_ratio            VARCHAR(20) DEFAULT NULL,
    per_pack_plan       VARCHAR(50) DEFAULT NULL,
    recorded_by         INT UNSIGNED NOT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_dpr_recorded_by FOREIGN KEY (recorded_by) REFERENCES users (user_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_dpr_date (report_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dpr_boxes (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dpr_id          INT UNSIGNED NOT NULL,
    box_class       ENUM('A', 'B') NOT NULL,
    group_name      VARCHAR(50) NOT NULL COMMENT 'GRP 1, GRP 3, HMLND',
    box_spec        VARCHAR(50) NOT NULL COMMENT '4 Hands, 5 Hands, etc.',
    box_count       INT UNSIGNED DEFAULT 0,
    CONSTRAINT fk_dprbox_dpr FOREIGN KEY (dpr_id) REFERENCES daily_production_reports (dpr_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
