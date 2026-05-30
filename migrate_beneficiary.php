<?php
$db = new PDO('mysql:host=localhost;dbname=darbco_system', 'root', '123456789');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $db->exec('ALTER TABLE production_data DROP FOREIGN KEY fk_production_worker;');
    $db->exec('ALTER TABLE production_data MODIFY worker_id int unsigned NULL;');
    $db->exec('ALTER TABLE production_data ADD COLUMN beneficiary_name varchar(255) NULL AFTER worker_id;');
    $db->exec('ALTER TABLE production_data ADD CONSTRAINT fk_production_worker FOREIGN KEY (worker_id) REFERENCES workers(worker_id) ON DELETE RESTRICT ON UPDATE CASCADE;');
    echo "Migration successful\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
