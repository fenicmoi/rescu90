<?php
require_once 'auth.php';
requireRole([1]); // Only Super Admin

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $agency = trim($_POST['agency'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 0;
    $district_id = !empty($_POST['district_id']) ? (int)$_POST['district_id'] : null;

    // Validate
    if (empty($username) || empty($name) || empty($role_id)) {
        $_SESSION['error_msg'] = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
        header("Location: user_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit();
    }

    if (in_array($role_id, [3, 4]) && empty($district_id)) {
        $_SESSION['error_msg'] = "สิทธิ์ระดับนี้ จำเป็นต้องระบุอำเภอที่รับผิดชอบ";
        header("Location: user_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit();
    }

    if (!in_array($role_id, [3, 4])) {
        $district_id = null; // Admin/Governor do not need district
    }

    try {
        if ($id > 0) {
            // Edit User
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = :name, agency = :agency, position = :position, phone = :phone, password = :password, role_id = :role_id, district_id = :district_id WHERE id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':agency' => $agency,
                    ':position' => $position,
                    ':phone' => $phone,
                    ':password' => $hash,
                    ':role_id' => $role_id,
                    ':district_id' => $district_id,
                    ':id' => $id
                ]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = :name, agency = :agency, position = :position, phone = :phone, role_id = :role_id, district_id = :district_id WHERE id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':agency' => $agency,
                    ':position' => $position,
                    ':phone' => $phone,
                    ':role_id' => $role_id,
                    ':district_id' => $district_id,
                    ':id' => $id
                ]);
            }
            $_SESSION['success_msg'] = "บันทึกการแก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว";
        } else {
            // Add User
            if (empty($password)) {
                $_SESSION['error_msg'] = "กรุณากำหนดรหัสผ่านสำหรับการสร้างผู้ใช้ใหม่";
                header("Location: user_form.php");
                exit();
            }

            // Check if username exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['error_msg'] = "Username นี้มีในระบบแล้ว กรุณาใช้ชื่ออื่น";
                header("Location: user_form.php");
                exit();
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, name, agency, position, phone, role_id, district_id) VALUES (:username, :password, :name, :agency, :position, :phone, :role_id, :district_id)");
            $stmt->execute([
                ':username' => $username,
                ':password' => $hash,
                ':name' => $name,
                ':agency' => $agency,
                ':position' => $position,
                ':phone' => $phone,
                ':role_id' => $role_id,
                ':district_id' => $district_id
            ]);
            $_SESSION['success_msg'] = "สร้างผู้ใช้ใหม่เรียบร้อยแล้ว";
        }

        header("Location: manage_users.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาดฐานข้อมูล: " . $e->getMessage();
        header("Location: user_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit();
    }
} else {
    header("Location: manage_users.php");
    exit();
}
