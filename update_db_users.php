<?php
require_once 'db_config.php';

try {
    $pdo->exec("SET NAMES utf8mb4");

    // 1. Create `roles` table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `roles` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `role_name` VARCHAR(50) NOT NULL,
      `description` VARCHAR(255) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Check if roles exist before inserting to avoid duplicates if run multiple times without truncate
    $stmt = $pdo->query("SELECT COUNT(*) FROM `roles`");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
        (1, 'Super Admin', 'ผู้ดูแลระบบสูงสุด จัดการผู้ใช้ได้ เพิ่มข้อมูลได้'),
        (2, 'Officer', 'เจ้าหน้าที่บันทึกข้อมูล เพิ่มข้อมูลจุดเสี่ยงได้'),
        (3, 'Viewer', 'ผู้เข้าชมสถิติ ดูแผนที่และข้อมูลได้อย่างเดียว');
        ");
    }

    // 2. Create `users` table
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `users` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `username` VARCHAR(50) NOT NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL,
      `name` VARCHAR(100) NOT NULL,
      `role_id` INT NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Insert default users if not exist
    $stmtInsertUser = $pdo->prepare("INSERT IGNORE INTO `users` (`username`, `password`, `name`, `role_id`) VALUES (?, ?, ?, ?)");
    
    // Hash passwords (using password_hash)
    $stmtInsertUser->execute(['admin', password_hash('admin', PASSWORD_DEFAULT), 'ผู้ดูแลระบบสูงสุด', 1]);
    $stmtInsertUser->execute(['officer', password_hash('officer', PASSWORD_DEFAULT), 'เจ้าหน้าที่บันทึก', 2]);
    $stmtInsertUser->execute(['viewer', password_hash('viewer', PASSWORD_DEFAULT), 'ผู้เข้าชมข้อมูล', 3]);

    echo "User tables and default data created successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
