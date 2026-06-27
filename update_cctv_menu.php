<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    // 1. Get the parent_id for "จัดการข้อมูล"
    $stmt = $pdo->query("SELECT id FROM menus WHERE title = 'จัดการข้อมูล' AND menu_type = 'backend' LIMIT 1");
    $parent = $stmt->fetch();
    
    if ($parent) {
        $parent_id = $parent['id'];
        
        // 2. Check if CCTV menu already exists
        $stmtCheck = $pdo->prepare("SELECT id FROM menus WHERE url = 'manage_cctv.php'");
        $stmtCheck->execute();
        
        if (!$stmtCheck->fetch()) {
            // 3. Insert CCTV menu
            $sql = "INSERT INTO menus (menu_type, title, url, icon, parent_id, order_num, allowed_roles) 
                    VALUES ('backend', 'จัดการข้อมูล CCTV', 'manage_cctv.php', 'fas fa-video', ?, 4, '[1,2,3,4]')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$parent_id]);
            echo "CCTV menu added successfully.\n";
        } else {
            echo "CCTV menu already exists.\n";
        }
    } else {
        echo "Parent menu not found.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
