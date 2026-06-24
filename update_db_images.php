<?php
require_once 'db_config.php';

try {
    // Add image columns to risk_locations
    $pdo->exec("ALTER TABLE `risk_locations` ADD COLUMN `image_before` VARCHAR(255) DEFAULT NULL AFTER `details`");
    $pdo->exec("ALTER TABLE `risk_locations` ADD COLUMN `image_after` VARCHAR(255) DEFAULT NULL AFTER `image_before`");
    echo "Added image columns to risk_locations.<br>";
} catch (PDOException $e) {
    echo "Info: " . $e->getMessage() . "<br>";
}

try {
    // Add image columns to target_houses
    $pdo->exec("ALTER TABLE `target_houses` ADD COLUMN `image_before` VARCHAR(255) DEFAULT NULL AFTER `details`");
    $pdo->exec("ALTER TABLE `target_houses` ADD COLUMN `image_after` VARCHAR(255) DEFAULT NULL AFTER `image_before`");
    echo "Added image columns to target_houses.<br>";
} catch (PDOException $e) {
    echo "Info: " . $e->getMessage() . "<br>";
}

echo "<br><b>Database updated successfully!</b>";
?>
