<?php
require_once 'db_config.php';

try {
    $pdo->exec("SET NAMES utf8mb4");

    // 1. แก้ไขข้อมูลอำเภอ ID 5 เป็น เขาชัยสน
    $pdo->exec("UPDATE `districts` SET `name_th` = 'เขาชัยสน' WHERE `id` = 5");

    // 2. สร้างตาราง subdistricts
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `subdistricts` (
      `id` INT NOT NULL AUTO_INCREMENT,
      `district_id` INT NOT NULL,
      `name_th` VARCHAR(255) NOT NULL,
      PRIMARY KEY (`id`),
      CONSTRAINT `fk_subdistrict_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $pdo->exec("TRUNCATE TABLE `subdistricts`");

    // 3. เพิ่มข้อมูลตำบล (ตัวอย่างบางส่วนให้ครอบคลุมทุกอำเภอ)
    $subdistrictsData = [
        1 => ['คูหาสวรรค์', 'เขาเจียก', 'ท่ามิหรำ', 'โคกชะงาย', 'ลำปำ', 'ตำนาน', 'ควนมะพร้าว'],
        2 => ['กงหรา', 'ชะรัด', 'คลองทรายขาว', 'สมหวัง', 'คลองเฉลิม'],
        3 => ['ควนขนุน', 'มะกอกเหนือ', 'ทะเลน้อย', 'ดอนทราย', 'พะนางตุง', 'แพรกหา'],
        4 => ['ตะโหมด', 'แม่ขรี', 'คลองใหญ่'],
        5 => ['เขาชัยสน', 'โคกม่วง', 'หานโพธิ์', 'จองถนน'],
        6 => ['ปากพะยูน', 'ดอนประดู่', 'เกาะหมาก', 'เกาะนางคำ'],
        7 => ['เขาย่า', 'ตะแพน', 'เตาปูน'],
        8 => ['ป่าบอน', 'โคกทราย', 'หนองธง', 'ทุ่งนารี'],
        9 => ['ท่ามะเดื่อ', 'นาปะขอ', 'โคกสัก'],
        10 => ['ป่าพะยอม', 'ลานข่อย', 'เกาะเต่า', 'บ้านพร้าว'],
        11 => ['ชุมพล', 'บ้านนา', 'อ่างทอง', 'ลำสินธุ์']
    ];

    $stmtInsertSubdistrict = $pdo->prepare("INSERT INTO `subdistricts` (`district_id`, `name_th`) VALUES (?, ?)");
    foreach ($subdistrictsData as $distId => $tambons) {
        foreach ($tambons as $tambon) {
            $stmtInsertSubdistrict->execute([$distId, $tambon]);
        }
    }

    // 4. เพิ่มคอลัมน์ subdistrict_id ใน risk_locations ถ้ายังไม่มี
    try {
        $pdo->exec("ALTER TABLE `risk_locations` ADD COLUMN `subdistrict_id` INT DEFAULT NULL AFTER `district_id`");
        $pdo->exec("ALTER TABLE `risk_locations` ADD CONSTRAINT `fk_risk_subdistrict` FOREIGN KEY (`subdistrict_id`) REFERENCES `subdistricts` (`id`) ON DELETE SET NULL");
    } catch (\PDOException $e) {
        // คอลัมน์อาจจะมีอยู่แล้ว ข้ามไป
    }

    echo "Database updated successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
