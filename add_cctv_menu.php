<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1';
require 'db_config.php';

try {
    $pdo->beginTransaction();

    // Get the parent_id for "ระบบกล้องวงจรปิด"
    $stmt = $pdo->prepare("SELECT id FROM menus WHERE title = 'ระบบกล้องวงจรปิด'");
    $stmt->execute();
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($parent) {
        $parentId = $parent['id'];

        // Check if menu already exists
        $stmtCheck = $pdo->prepare("SELECT id FROM menus WHERE url = 'dashboard_cctv.php'");
        $stmtCheck->execute();
        
        if (!$stmtCheck->fetch()) {
            // Shift existing menus down
            $pdo->exec("UPDATE menus SET order_num = order_num + 1 WHERE parent_id = $parentId");

            // Insert new menu at the top of the group (order_num = 0)
            $stmtInsert = $pdo->prepare("INSERT INTO menus (menu_type, title, url, icon, parent_id, order_num, allowed_roles, is_active) 
                                   VALUES ('backend', 'Dashboard (CCTV)', 'dashboard_cctv.php', '', ?, 0, '[1, 2, 3, 4]', 1)");
            $stmtInsert->execute([$parentId]);
            echo "Added Dashboard (CCTV) menu successfully.\n";
        } else {
            echo "Menu already exists.\n";
        }
    } else {
        echo "Parent menu not found.\n";
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
