<?php
require_once __DIR__ . '/config/db.php';

try {
    $db = Database::getInstance();
    
    // Drop the existing hp_farm_rejects table
    $db->exec("DROP TABLE IF EXISTS hp_farm_rejects");
    
    // Recreate it with the new schema supporting multiple rows
    $db->exec("
        CREATE TABLE hp_farm_rejects (
            id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            hp_id       INT UNSIGNED NOT NULL,
            reject_code VARCHAR(20) DEFAULT NULL,
            code_11     VARCHAR(20) DEFAULT NULL,
            code_12     VARCHAR(20) DEFAULT NULL,
            code_13     VARCHAR(20) DEFAULT NULL,
            code_14     VARCHAR(20) DEFAULT NULL,
            total       VARCHAR(20) DEFAULT NULL,
            CONSTRAINT fk_hprej_hp FOREIGN KEY (hp_id) REFERENCES harvest_parameters (hp_id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    
    echo "Successfully updated hp_farm_rejects schema.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
