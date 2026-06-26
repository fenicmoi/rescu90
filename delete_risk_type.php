<?php
require_once 'auth.php';
requireRole([1]);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        try {
            // Check if it's being used in risk_locations
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM risk_locations WHERE risk_type_id = ?");
            $stmtCheck->execute([$id]);
            $count = $stmtCheck->fetchColumn();

            if ($count > 0) {
                $_SESSION['error_msg'] = "ไม่สามารถลบได้ เนื่องจากมีจุดเสี่ยงที่ใช้ประเภทนี้อยู่ $count จุด";
            } else {
                $stmt = $pdo->prepare("DELETE FROM risk_types WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_msg'] = "ลบประเภทความเสี่ยงเรียบร้อยแล้ว";
            }
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "Error deleting data: " . $e->getMessage();
        }
    }
}
header("Location: manage_risk_types.php");
exit;
