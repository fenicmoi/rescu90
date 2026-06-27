<?php
require_once 'auth.php';
requireRole([1]);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        try {
            // Check if it's being used in target_houses
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM target_houses WHERE target_type_id = ?");
            $stmtCheck->execute([$id]);
            $count = $stmtCheck->fetchColumn();

            if ($count > 0) {
                $_SESSION['error_msg'] = "ไม่สามารถลบได้ เนื่องจากมีบ้านเป้าหมายที่ใช้ประเภทนี้อยู่ $count แห่ง";
            } else {
                $stmt = $pdo->prepare("DELETE FROM target_types WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_msg'] = "ลบประเภทบ้านเป้าหมายเรียบร้อยแล้ว";
            }
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Error deleting data: " . $e->getMessage();
        }
    }
}
header("Location: manage_target_types.php");
exit;
