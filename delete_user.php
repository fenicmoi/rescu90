<?php
require_once 'auth.php';
requireRole([1]); // Only Super Admin

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id <= 0) {
        $_SESSION['error_msg'] = "ไม่พบรหัสผู้ใช้ที่ต้องการลบ";
        header("Location: manage_users.php");
        exit();
    }

    if ($id == $user_id) {
        $_SESSION['error_msg'] = "ไม่สามารถลบบัญชีของตนเองที่กำลังเข้าสู่ระบบได้";
        header("Location: manage_users.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        $_SESSION['success_msg'] = "ลบบัญชีผู้ใช้เรียบร้อยแล้ว";
        header("Location: manage_users.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาดฐานข้อมูล: " . $e->getMessage();
        header("Location: manage_users.php");
        exit();
    }
} else {
    header("Location: manage_users.php");
    exit();
}
