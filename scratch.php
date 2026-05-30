<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo = Database::getInstance();
    
    // Create parent table
    $sql1 = "CREATE TABLE IF NOT EXISTS daily_prod_beneficiary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        packing_date DATE NOT NULL,
        recorded_by INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (recorded_by) REFERENCES users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql1);
    
    // Create child table
    // Fields are mostly VARCHAR(20) to support string inputs like "3+2"
    $sql2 = "CREATE TABLE IF NOT EXISTS daily_prod_beneficiary_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_id INT NOT NULL,
        sub_code VARCHAR(50),
        arb_name VARCHAR(150),
        stems_cut VARCHAR(20),
        class_a_hands VARCHAR(20),
        class_a_sh VARCHAR(20),
        class_a_blank1 VARCHAR(20),
        class_a_fp VARCHAR(20),
        class_a_blank2 VARCHAR(20),
        class_a_blank3 VARCHAR(20),
        class_a_cl_b VARCHAR(20),
        class_b_h VARCHAR(20),
        class_b_id VARCHAR(20),
        FOREIGN KEY (parent_id) REFERENCES daily_prod_beneficiary(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql2);
    
    echo "Tables created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
