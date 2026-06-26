<?php
require_once 'auth.php';
requireRole([1]);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $type_name = isset($_POST['type_name']) ? trim($_POST['type_name']) : '';
    $marker_color = isset($_POST['marker_color']) ? trim($_POST['marker_color']) : '#3B82F6';

    if (empty($type_name) || empty($marker_color)) {
        $_SESSION['error_msg'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("Location: risk_type_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    try {
        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE risk_types SET type_name = ?, marker_color = ? WHERE id = ?");
            $stmt->execute([$type_name, $marker_color, $id]);
            $_SESSION['success_msg'] = "อัปเดตประเภทความเสี่ยงเรียบร้อยแล้ว";
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO risk_types (type_name, marker_color) VALUES (?, ?)");
            $stmt->execute([$type_name, $marker_color]);
            $_SESSION['success_msg'] = "เพิ่มประเภทความเสี่ยงใหม่เรียบร้อยแล้ว";
        }
        header("Location: manage_risk_types.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error saving data: " . $e->getMessage();
        header("Location: risk_type_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }
}
header("Location: manage_risk_types.php");
exit;
