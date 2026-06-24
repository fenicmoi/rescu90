<?php
// login_action.php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "กรุณากรอก Username และ Password";
        header("Location: login.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.password, u.name, u.role_id, u.district_id, r.role_name 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.username = :username
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login สำเร็จ
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['user_district_id'] = $user['district_id'];

            // พาไปหน้าแผนที่หลัก
            header("Location: index.php");
            exit();
        } else {
            // Login ไม่สำเร็จ
            $_SESSION['login_error'] = "Username หรือ Password ไม่ถูกต้อง";
            header("Location: login.php");
            exit();
        }

    } catch (\PDOException $e) {
        $_SESSION['login_error'] = "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
