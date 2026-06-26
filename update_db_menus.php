<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("SET NAMES utf8mb4");


try {
    // 1. Create table
    $sql = "
    CREATE TABLE IF NOT EXISTS `menus` (
      `id` int NOT NULL AUTO_INCREMENT,
      `menu_type` enum('frontend', 'backend') NOT NULL DEFAULT 'backend',
      `title` varchar(100) NOT NULL,
      `url` varchar(255) DEFAULT '#',
      `icon` varchar(100) DEFAULT NULL COMMENT 'FontAwesome class e.g. fas fa-home',
      `css_class` varchar(255) DEFAULT NULL COMMENT 'Custom CSS classes for buttons',
      `parent_id` int DEFAULT NULL,
      `order_num` int DEFAULT 0,
      `allowed_roles` json DEFAULT NULL COMMENT 'JSON array of allowed role_ids, NULL means all',
      `is_active` tinyint(1) DEFAULT 1,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`parent_id`) REFERENCES `menus`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Table 'menus' created or already exists.\n";

    // Check if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM menus");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // 2. Insert Default Data
        
        // --- Frontend Menus ---
        $stmt = $pdo->prepare("INSERT INTO menus (menu_type, title, url, icon, css_class, order_num, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['frontend', 'แจ้งจุดเสี่ยง', 'public_report.php', 'fas fa-bullhorn', 'bg-red-600 text-white hover:bg-red-700 px-4 py-2 rounded-md text-sm font-bold shadow transition animate-bounce', 1, 1]);
        $stmt->execute(['frontend', 'เข้าสู่ระบบเจ้าหน้าที่', 'login.php', 'fas fa-sign-in-alt', 'bg-white text-blue-800 hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-bold shadow transition', 2, 1]);
        
        // --- Backend Menus ---
        
        // Group 1: ภาพรวม
        $stmt = $pdo->prepare("INSERT INTO menus (menu_type, title, url, icon, order_num, allowed_roles) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['backend', 'ภาพรวม', '#', 'fas fa-chart-line', 1, null]);
        $overview_id = $pdo->lastInsertId();
        
        $stmt_child = $pdo->prepare("INSERT INTO menus (menu_type, title, url, parent_id, order_num) VALUES (?, ?, ?, ?, ?)");
        $stmt_child->execute(['backend', 'Dashboard', 'dashboard.php', $overview_id, 1]);
        $stmt_child->execute(['backend', 'แผนที่รวม', 'map_dashboard.php', $overview_id, 2]);
        $stmt_child->execute(['backend', 'แผนที่ผู้บริหาร', 'report_executive_map.php', $overview_id, 3]);

        // Group 2: จัดการข้อมูล
        $stmt->execute(['backend', 'จัดการข้อมูล', '#', 'fas fa-edit', 2, json_encode([1, 3, 4])]);
        $manage_id = $pdo->lastInsertId();
        
        $stmt_child->execute(['backend', 'แจ้งจุดเสี่ยง', 'add_location.php', $manage_id, 1]);
        $stmt_child->execute(['backend', 'แจ้งบ้านเป้าหมาย', 'add_target.php', $manage_id, 2]);
        $stmt_child->execute(['backend', 'รายการข้อมูลของฉัน', 'my_reports.php', $manage_id, 3]);

        // Group 3: ตั้งค่าระบบ
        $stmt->execute(['backend', 'ตั้งค่าระบบ', '#', 'fas fa-cog', 3, json_encode([1])]);
        $settings_id = $pdo->lastInsertId();
        
        $stmt_child->execute(['backend', 'จัดการผู้ใช้', 'manage_users.php', $settings_id, 1]);
        $stmt_child->execute(['backend', 'จัดการแบนเนอร์', 'manage_hero.php', $settings_id, 2]);
        $stmt_child->execute(['backend', 'จัดการเมนู', 'manage_menus.php', $settings_id, 3]);

        echo "Default menus inserted successfully.\n";
    } else {
        echo "Menus already exist, skipping default insert.\n";
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
