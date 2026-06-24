<?php
require_once 'db_config.php';

try {
    $pdo->exec("SET NAMES utf8mb4");

    // 1. Create `target_types` table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `target_types` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `type_name` VARCHAR(255) NOT NULL,
      `marker_color` VARCHAR(50) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Clear and insert default types
    $pdo->exec("TRUNCATE TABLE `target_types`");
    $pdo->exec("INSERT INTO `target_types` (`id`, `type_name`, `marker_color`) VALUES
    (1, 'บ้านค้ายาเสพติด', '#DC2626'),
    (2, 'บ้านมั่วสุม', '#F59E0B'),
    (3, 'บ้านผู้มีอิทธิพล', '#8B5CF6'),
    (4, 'บ้านครอบครองอาวุธปืน', '#374151');
    ");

    // 2. Create `target_houses` table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `target_houses` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `district_id` INT DEFAULT NULL,
      `subdistrict_id` INT DEFAULT NULL,
      `target_type_id` INT DEFAULT NULL,
      `house_name` VARCHAR(255) NOT NULL,
      `latitude` DECIMAL(10, 8) NOT NULL,
      `longitude` DECIMAL(11, 8) NOT NULL,
      `details` TEXT DEFAULT NULL,
      `reported_by` INT DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      CONSTRAINT `fk_target_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
      CONSTRAINT `fk_target_subdistrict` FOREIGN KEY (`subdistrict_id`) REFERENCES `subdistricts` (`id`) ON DELETE SET NULL,
      CONSTRAINT `fk_target_type` FOREIGN KEY (`target_type_id`) REFERENCES `target_types` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Target houses tables created successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
