<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Truncate locations first to avoid foreign key issues
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
    $pdo->exec("TRUNCATE TABLE risk_locations;");
    $pdo->exec("TRUNCATE TABLE risk_types;");
    
    // Insert new 8 types
    $types = [
        ['ยาเสพติด', '#DC2626'], // Red
        ['อาวุธปืน', '#D97706'], // Amber
        ['การพนัน', '#059669'], // Emerald
        ['ลักทรัพย์', '#2563EB'], // Blue
        ['การฉ้อโกงออนไลน์', '#7C3AED'], // Violet
        ['กลุ่มผู้มีอิทธิพล', '#DB2777'], // Pink
        ['ความรุนแรงในครอบครัว', '#EA580C'], // Orange
        ['อาชญากรรมทางเพศ', '#9333EA'] // Purple
    ];

    $stmt = $pdo->prepare("INSERT INTO risk_types (type_name, marker_color) VALUES (?, ?)");
    foreach ($types as $t) {
        $stmt->execute([$t[0], $t[1]]);
    }
    
    // Insert Menu
    // Check if parent menu "ตั้งค่าระบบ" exists (id=11)
    $stmtMenu = $pdo->prepare("SELECT id FROM menus WHERE title = 'ตั้งค่าระบบ' AND parent_id IS NULL");
    $stmtMenu->execute();
    $parent = $stmtMenu->fetch();
    
    if ($parent) {
        // Check if already exists
        $stmtCheck = $pdo->prepare("SELECT id FROM menus WHERE title = 'จัดการประเภทความเสี่ยง' AND parent_id = ?");
        $stmtCheck->execute([$parent['id']]);
        if (!$stmtCheck->fetch()) {
            $stmtInsert = $pdo->prepare("INSERT INTO menus (menu_type, title, url, parent_id, order_num) VALUES (?, ?, ?, ?, ?)");
            $stmtInsert->execute(['backend', 'จัดการประเภทความเสี่ยง', 'manage_risk_types.php', $parent['id'], 4]);
        }
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    echo "Successfully updated risk types and menu.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
