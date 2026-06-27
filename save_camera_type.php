<?php
require_once 'auth.php';
requireRole([1, 2]); // Admins and Governor

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $type_name = trim($_POST['type_name'] ?? '');

    if (empty($type_name)) {
        $_SESSION['error_msg'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("Location: camera_type_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE camera_types SET type_name = ? WHERE id = ?");
            $stmt->execute([$type_name, $id]);
            $_SESSION['success_msg'] = "อัปเดตข้อมูลสำเร็จ";
        } else {
            $stmt = $pdo->prepare("INSERT INTO camera_types (type_name) VALUES (?)");
            $stmt->execute([$type_name]);
            $_SESSION['success_msg'] = "เพิ่มข้อมูลสำเร็จ";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            $_SESSION['error_msg'] = "มีชื่อประเภทกล้องนี้อยู่ในระบบแล้ว";
        } else {
            $_SESSION['error_msg'] = "Error: " . $e->getMessage();
        }
    }

    header("Location: manage_camera_types.php");
    exit;
} else {
    header("Location: manage_camera_types.php");
    exit;
}
