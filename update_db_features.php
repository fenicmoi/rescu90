<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'db_config.php';

try {
    // 1. Alter ENUMs in risk_locations
    $pdo->exec("ALTER TABLE `risk_locations` MODIFY COLUMN `status` enum('pending','active','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'สถานะ: pending=รอตรวจสอบ, active=มีความเสี่ยง, resolved=แก้ไขแล้ว'");
    echo "Altered risk_locations status ENUM successfully.<br>";

    // 2. Alter ENUMs in target_houses
    $pdo->exec("ALTER TABLE `target_houses` MODIFY COLUMN `status` enum('pending','active','resolved') COLLATE utf8mb4_unicode_ci DEFAULT 'pending' COMMENT 'สถานะ: pending=รอตรวจสอบ, active=รอตรวจสอบ(เป้าหมาย), resolved=ดำเนินการแล้ว'");
    echo "Altered target_houses status ENUM successfully.<br>";

    // 3. Create hero_images table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `hero_images` (
        `id` int NOT NULL AUTO_INCREMENT,
        `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `is_active` tinyint(1) DEFAULT '1',
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Created hero_images table successfully.<br>";

    // 4. Create uploads/hero directory if it doesn't exist
    if (!is_dir('uploads/hero')) {
        mkdir('uploads/hero', 0777, true);
        echo "Created uploads/hero directory.<br>";
    }

} catch (\PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
