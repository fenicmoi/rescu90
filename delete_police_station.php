<?php
require_once 'auth.php';
requireRole([1]);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        try {
            // Check usage
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM cctv_locations WHERE police_station_id = ?");
            $stmtCheck->execute([$id]);
            $count = $stmtCheck->fetchColumn();

            if ($count > 0) {
                $_SESSION['error_msg'] = "ไม่สามารถลบได้เนื่องจากมีกล้อง CCTV ใช้งานสถานีตำรวจนี้อยู่";
            } else {
                $stmt = $pdo->prepare("DELETE FROM police_stations WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['success_msg'] = "ลบข้อมูลสำเร็จ";
            }
        } catch (PDOException $e) {
            $_SESSION['error_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}

header("Location: manage_police_stations.php");
exit;
