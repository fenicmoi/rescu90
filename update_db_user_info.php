<?php
require_once 'db_config.php';

try {
    $pdo->exec("SET NAMES utf8mb4");

    $sql = "
        ALTER TABLE `users` 
        ADD COLUMN `agency` VARCHAR(255) DEFAULT NULL COMMENT 'หน่วยงาน' AFTER `name`,
        ADD COLUMN `position` VARCHAR(255) DEFAULT NULL COMMENT 'ตำแหน่ง' AFTER `agency`,
        ADD COLUMN `phone` VARCHAR(50) DEFAULT NULL COMMENT 'เบอร์โทรศัพท์' AFTER `position`;
    ";
    
    $pdo->exec($sql);
    echo "Database updated successfully with new user info columns.\n";

} catch (Exception $e) {
    // If it fails, maybe columns already exist
    echo "Notice/Error: " . $e->getMessage() . "\n";
}
?>
