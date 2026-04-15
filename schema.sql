-- ============================================================
-- DARBCO Workflow Management & Financial Processing System
-- Database Schema - MySQL Workbench
-- Author: DARBCO Dev Team
-- Created: 2026-04-15
-- ============================================================

CREATE DATABASE IF NOT EXISTS darbco_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE darbco_system;

-- ============================================================
-- TABLE: users
-- Stores all system user accounts with role-based access
-- Roles: admin, production_clerk, payroll_personnel,
--         finance_officer, bookkeeper
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    user_id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(150)  NOT NULL,
    username      VARCHAR(80)   NOT NULL UNIQUE,
    email         VARCHAR(180)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM(
                      'admin',
                      'production_clerk',
                      'payroll_personnel',
                      'finance_officer',
                      'bookkeeper'
                  ) NOT NULL DEFAULT 'production_clerk',
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: workers
-- Registry of farm workers / harvest teams
-- ============================================================
CREATE TABLE IF NOT EXISTS workers (
    worker_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name     VARCHAR(50)  NOT NULL,
    last_name      VARCHAR(50)  NOT NULL,
    contact_number VARCHAR(20)  DEFAULT NULL,
    is_active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_workers_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: inventory_data
-- Tracks farming materials (fertilizers, bags, etc.)
-- Used by Bookkeeper; auto-deducted on production entry
-- ============================================================
CREATE TABLE IF NOT EXISTS inventory_data (
    item_id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_name       VARCHAR(150)     NOT NULL,
    category        VARCHAR(100)     NOT NULL DEFAULT 'General',
    unit            VARCHAR(50)      NOT NULL DEFAULT 'pcs',
    quantity_on_hand DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,
    reorder_level   DECIMAL(10, 2)   NOT NULL DEFAULT 10.00,
    unit_cost       DECIMAL(10, 2)   NOT NULL DEFAULT 0.00,
    description     TEXT,
    created_by      INT UNSIGNED     NOT NULL,
    created_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inventory_user
        FOREIGN KEY (created_by) REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_inventory_category (category),
    INDEX idx_inventory_reorder  (quantity_on_hand, reorder_level)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: production_data
-- Records daily harvest outputs entered by Production Clerk
-- ============================================================
CREATE TABLE IF NOT EXISTS production_data (
    production_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id       INT UNSIGNED     NOT NULL,
    harvest_date    DATE             NOT NULL,
    boxes_produced  INT UNSIGNED     NOT NULL DEFAULT 0,
    field_location  VARCHAR(200),
    notes           TEXT,
    recorded_by     INT UNSIGNED     NOT NULL,   -- FK -> users
    created_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_production_worker
        FOREIGN KEY (worker_id) REFERENCES workers (worker_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_production_recorder
        FOREIGN KEY (recorded_by) REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_production_date   (harvest_date),
    INDEX idx_production_worker (worker_id),
    INDEX idx_production_boxes  (boxes_produced)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: production_materials
-- Junction table: links production records to inventory items
-- used; drives the automatic inventory deduction
-- ============================================================
CREATE TABLE IF NOT EXISTS production_materials (
    pm_id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    production_id   INT UNSIGNED     NOT NULL,
    item_id         INT UNSIGNED     NOT NULL,
    quantity_used   DECIMAL(10, 2)   NOT NULL,
    CONSTRAINT fk_pm_production
        FOREIGN KEY (production_id) REFERENCES production_data (production_id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pm_inventory
        FOREIGN KEY (item_id)       REFERENCES inventory_data (item_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_pm_production (production_id),
    INDEX idx_pm_item       (item_id)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: payroll_data
-- Computed payroll records tied to production output
-- Status flow: pending_review -> reviewed -> approved
-- ============================================================
CREATE TABLE IF NOT EXISTS payroll_data (
    payroll_id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    production_id   INT UNSIGNED    NOT NULL,                -- source record
    worker_id       INT UNSIGNED    NOT NULL,
    harvest_date    DATE            NOT NULL,
    boxes_produced  INT UNSIGNED    NOT NULL DEFAULT 0,
    rate_per_box    DECIMAL(10, 2)  NOT NULL DEFAULT 0.00,   -- PHP rate
    gross_pay       DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,   -- boxes * rate
    deductions      DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,
    net_pay         DECIMAL(12, 2)  NOT NULL DEFAULT 0.00,   -- gross - deduct
    period_start    DATE            NOT NULL,
    period_end      DATE            NOT NULL,
    status          ENUM(
                        'pending_review',
                        'reviewed',
                        'approved'
                    ) NOT NULL DEFAULT 'pending_review',
    computed_by     INT UNSIGNED    NOT NULL,   -- FK Payroll Personnel
    reviewed_by     INT UNSIGNED,               -- FK Finance Officer
    approved_by     INT UNSIGNED,               -- FK Admin/Manager
    reviewed_at     TIMESTAMP       NULL,
    approved_at     TIMESTAMP       NULL,
    remarks         TEXT,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payroll_production
        FOREIGN KEY (production_id) REFERENCES production_data (production_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payroll_worker
        FOREIGN KEY (worker_id) REFERENCES workers (worker_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payroll_computed_by
        FOREIGN KEY (computed_by)   REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_payroll_reviewed_by
        FOREIGN KEY (reviewed_by)   REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_payroll_approved_by
        FOREIGN KEY (approved_by)   REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_payroll_status      (status),
    INDEX idx_payroll_worker      (worker_id),
    INDEX idx_payroll_period      (period_start, period_end),
    INDEX idx_payroll_date        (harvest_date)
) ENGINE=InnoDB;

-- ============================================================
-- TABLE: transaction_logs
-- Audit trail for all significant system events
-- ============================================================
CREATE TABLE IF NOT EXISTS transaction_logs (
    log_id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED    NOT NULL,
    action_type     ENUM(
                        'production_insert',
                        'production_update',
                        'production_delete',
                        'inventory_insert',
                        'inventory_update',
                        'inventory_deduction',
                        'payroll_compute',
                        'payroll_review',
                        'payroll_approve',
                        'user_login',
                        'user_logout',
                        'user_create',
                        'user_update'
                    ) NOT NULL,
    reference_table VARCHAR(100),    -- e.g. 'production_data'
    reference_id    INT UNSIGNED,    -- PK of the referenced record
    description     TEXT             NOT NULL,
    ip_address      VARCHAR(45),     -- supports IPv6
    created_at      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_log_user        (user_id),
    INDEX idx_log_action      (action_type),
    INDEX idx_log_reference   (reference_table, reference_id),
    INDEX idx_log_created     (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- TRIGGER: trg_deduct_inventory
-- Automatically deducts inventory on production_materials insert
-- ============================================================
DELIMITER $$

CREATE TRIGGER trg_deduct_inventory
AFTER INSERT ON production_materials
FOR EACH ROW
BEGIN
    UPDATE inventory_data
    SET    quantity_on_hand = quantity_on_hand - NEW.quantity_used,
           updated_at       = CURRENT_TIMESTAMP
    WHERE  item_id          = NEW.item_id;
END$$

DELIMITER ;

-- ============================================================
-- TRIGGER: trg_restore_inventory
-- Restores inventory when a production_materials row is deleted
-- (supports record corrections / rollback)
-- ============================================================
DELIMITER $$

CREATE TRIGGER trg_restore_inventory
AFTER DELETE ON production_materials
FOR EACH ROW
BEGIN
    UPDATE inventory_data
    SET    quantity_on_hand = quantity_on_hand + OLD.quantity_used,
           updated_at       = CURRENT_TIMESTAMP
    WHERE  item_id          = OLD.item_id;
END$$

DELIMITER ;

-- ============================================================
-- SEED DATA: Default Admin Account
-- Password: Admin@DARBCO2026  (change immediately after login)
-- Hash generated via PASSWORD_BCRYPT cost=12
-- ============================================================
INSERT INTO users (full_name, username, email, password_hash, role)
VALUES (
    'System Administrator',
    'admin',
    'admin@darbco.local',
    '$2y$12$YourBcryptHashGeneratedInPHPHere',  -- regenerate in PHP!
    'admin'
);

-- ============================================================
-- SEED DATA: Initial Inventory Categories
-- ============================================================
INSERT INTO inventory_data (item_name, category, unit, quantity_on_hand, reorder_level, unit_cost, created_by)
VALUES
    ('Banana Bags (Blue)',    'Packaging',   'pcs',  5000.00, 500.00,  2.50,  1),
    ('Fertilizer (Urea)',     'Fertilizer',  'kg',    500.00,  50.00, 45.00,  1),
    ('Fertilizer (Complete)', 'Fertilizer',  'kg',    300.00,  30.00, 52.00,  1),
    ('Twine / Rope',          'Supplies',    'roll',   80.00,  10.00, 35.00,  1),
    ('Cardboard Boxes',       'Packaging',   'pcs',  2000.00, 200.00,  8.00,  1),
    ('Pesticide (Manzate)',   'Chemical',    'kg',    150.00,  20.00, 95.00,  1);
