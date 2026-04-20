-- ============================================================
-- DARBCO Migration: Physical Report Data Capture
-- Run this against: darbco_system
-- Date: 2026-04-20
-- ============================================================

USE darbco_system;

-- ── 1. Add ARB sub_code to workers ───────────────────────────
ALTER TABLE workers
    ADD COLUMN sub_code VARCHAR(20) NULL
        COMMENT 'ARB sub-code from physical report (e.g. 042, 181)'
        AFTER worker_id;

-- ── 2. Add stems_cut and group_number to production_data ─────
ALTER TABLE production_data
    ADD COLUMN stems_cut    INT UNSIGNED NOT NULL DEFAULT 0
        COMMENT 'Total stems cut by this ARB on this day'
        AFTER boxes_produced,
    ADD COLUMN group_number TINYINT UNSIGNED NULL
        COMMENT 'Packing group: 1 = Group 1, 3 = Group 3'
        AFTER stems_cut;

-- ── 3. Create production_box_breakdown table ─────────────────
-- Stores per-ARB per-day breakdown by box class and spec
-- matching the Daily Boxes Per Group & Per-Beneficiary reports
CREATE TABLE IF NOT EXISTS production_box_breakdown (
    breakdown_id    INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    production_id   INT UNSIGNED    NOT NULL,
    box_class       ENUM('A','B')   NOT NULL     COMMENT 'Class A or Class B',
    box_spec        VARCHAR(50)     NOT NULL     COMMENT 'e.g. 4 Hands, 7 Hands, 4.7k, 7.2k, BCP, Clusters, F.P',
    tally_count     INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'Actual tally count',
    adjusted_count  INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'ADJ count after correction',
    should_be_count INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'Expected / plan count',
    CONSTRAINT fk_breakdown_production
        FOREIGN KEY (production_id) REFERENCES production_data (production_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_breakdown_production (production_id),
    INDEX idx_breakdown_class      (box_class),
    INDEX idx_breakdown_spec       (box_spec)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- ── 4. Create packing_sessions table (Efficiency Report) ─────
-- Captures crew-level timing and volume totals per packing day
CREATE TABLE IF NOT EXISTS packing_sessions (
    session_id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    packing_date        DATE            NOT NULL,
    crew_size           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    first_fruit_in      TIME            NULL,
    last_fruit_in       TIME            NULL,
    first_box_out       TIME            NULL,
    last_box_out        TIME            NULL,
    stems_cut_total     INT UNSIGNED    NOT NULL DEFAULT 0,
    bs_ratio            DECIMAL(5,2)    NULL COMMENT 'B/S ratio',
    per_pack_plan       INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'Per pack plan count',
    recorded_by         INT UNSIGNED    NOT NULL,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_session_recorder
        FOREIGN KEY (recorded_by) REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_session_date (packing_date)
) ENGINE=InnoDB
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
