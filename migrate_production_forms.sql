-- Migration Script for Production UI Alignment
USE darbco_system;

-- 1. Update production_data (Individual ARB logs)
ALTER TABLE `production_data`
ADD COLUMN `hands` INT UNSIGNED DEFAULT 0 AFTER `stems_cut`,
ADD COLUMN `small_hands` INT UNSIGNED DEFAULT 0 AFTER `hands`,
ADD COLUMN `class_a_fp` INT UNSIGNED DEFAULT 0 AFTER `small_hands`,
ADD COLUMN `class_b_h` INT UNSIGNED DEFAULT 0 AFTER `class_a_fp`,
ADD COLUMN `class_b_id` INT UNSIGNED DEFAULT 0 AFTER `class_b_h`,
ADD COLUMN `class_b_cl_b` INT UNSIGNED DEFAULT 0 AFTER `class_b_id`;

-- 2. Update dpr_boxes (Daily Reports / Group level)
ALTER TABLE `dpr_boxes`
ADD COLUMN `tally_count` INT UNSIGNED DEFAULT 0 AFTER `box_spec`,
ADD COLUMN `adjusted_count` INT UNSIGNED DEFAULT 0 AFTER `tally_count`,
ADD COLUMN `should_be_count` INT UNSIGNED DEFAULT 0 AFTER `adjusted_count`,
DROP COLUMN `box_count`;

-- Also update uiui.sql in the repo directly to reflect these changes going forward
