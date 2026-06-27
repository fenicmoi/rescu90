<?php
require_once 'auth.php';
requireRole([1, 2, 3, 4]);

require_once 'db_config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM cctv_locations WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_msg'] = "ลบข้อมูล CCTV เรียบร้อยแล้ว";
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
} else {
    $_SESSION['error_msg'] = "รหัสไม่ถูกต้อง";
}

header("Location: manage_cctv.php");
exit;
?>
