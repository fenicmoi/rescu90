<?php
require_once 'auth.php';
requireRole([1, 2]); // Admins and Governor

require_once 'db_config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // Check if type is in use
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM cctv_locations WHERE camera_type_id = ?");
        $stmtCheck->execute([$id]);
        $count = $stmtCheck->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error_msg'] = "ไม่สามารถลบได้เนื่องจากมีกล้อง CCTV ใช้ประเภทนี้อยู่ $count ตัว";
        } else {
            $stmt = $pdo->prepare("DELETE FROM camera_types WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success_msg'] = "ลบข้อมูลสำเร็จ";
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error deleting record: " . $e->getMessage();
    }
}

header("Location: manage_camera_types.php");
exit;
