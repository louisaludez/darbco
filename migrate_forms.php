<?php
require_once 'config/db.php';
$db = Database::getInstance();
$sql = file_get_contents('migrate_production_forms.sql');
try {
    $db->exec($sql);
    echo "Migration successful.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
