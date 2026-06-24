<?php
require_once 'db_config.php';

try {
    $pdo->exec("SET NAMES utf8mb4");

    // Add status to risk_locations
    $sql1 = "ALTER TABLE `risk_locations` ADD COLUMN `status` ENUM('active', 'resolved') DEFAULT 'active' COMMENT 'สถานะ: active=มีความเสี่ยง, resolved=แก้ไขแล้ว' AFTER `longitude`";
    $pdo->exec($sql1);
    echo "Added status to risk_locations.\n";

} catch (Exception $e) {
    echo "Notice/Error risk_locations: " . $e->getMessage() . "\n";
}

try {
    // Add status to target_houses
    $sql2 = "ALTER TABLE `target_houses` ADD COLUMN `status` ENUM('active', 'resolved') DEFAULT 'active' COMMENT 'สถานะ: active=รอตรวจสอบ, resolved=ดำเนินการแล้ว' AFTER `longitude`";
    $pdo->exec($sql2);
    echo "Added status to target_houses.\n";

} catch (Exception $e) {
    echo "Notice/Error target_houses: " . $e->getMessage() . "\n";
}
?>
