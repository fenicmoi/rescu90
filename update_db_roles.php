<?php
require_once 'db_config.php';

try {
    $pdo->exec("SET NAMES utf8mb4");

    // 1. Add district_id to users if not exists (handling gracefully if constraint exists)
    try {
        $pdo->exec("
            ALTER TABLE `users` 
            ADD COLUMN `district_id` INT DEFAULT NULL AFTER `role_id`,
            ADD CONSTRAINT `fk_user_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL;
        ");
    } catch (Exception $e) {
        // Ignore if already exists
    }

    // 2. Temporarily disable foreign key checks to update roles
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
    $pdo->exec("TRUNCATE TABLE `roles`;");
    $pdo->exec("INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES
    (1, 'Admin', 'ดูและจัดการข้อมูลได้ทั้งหมดในจังหวัด'),
    (2, 'Governor', 'ผู้ว่าราชการจังหวัด ดูข้อมูลได้ทั้งหมดในจังหวัด'),
    (3, 'District Chief', 'นายอำเภอ สรุปยอดได้เฉพาะอำเภอตนเอง'),
    (4, 'Officer', 'เจ้าหน้าที่ผู้ลงข้อมูล ดูข้อมูลได้เฉพาะของตนเอง');
    ");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

    // 3. Clear users and create new test accounts
    $pdo->exec("DELETE FROM `users`");
    
    $pwdAdmin = password_hash('admin', PASSWORD_DEFAULT);
    $pwdGov = password_hash('governor', PASSWORD_DEFAULT);
    $pwdChief = password_hash('chief_mueang', PASSWORD_DEFAULT);
    $pwdOfficer = password_hash('officer_mueang', PASSWORD_DEFAULT);

    $sql = "INSERT INTO `users` (`username`, `password`, `name`, `role_id`, `district_id`) VALUES
    ('admin', '$pwdAdmin', 'แอดมินระบบ', 1, NULL),
    ('governor', '$pwdGov', 'ผู้ว่าราชการจังหวัด', 2, NULL),
    ('chief_mueang', '$pwdChief', 'นายอำเภอเมืองพัทลุง', 3, 1),
    ('officer_mueang', '$pwdOfficer', 'จนท.เมืองพัทลุง', 4, 1);
    ";
    $pdo->exec($sql);

    echo "Database updated successfully for new RBAC rules.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
