<?php
// auth.php
session_start();

// ตรวจสอบว่าผู้ใช้ Login หรือยัง และ Session ข้อมูลครบถ้วนหรือไม่
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name']) || !isset($_SESSION['role_id']) || !isset($_SESSION['role_name'])) {
    header("Location: logout.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role_id = $_SESSION['role_id'];
$user_role_name = $_SESSION['role_name'];
$user_district_id = $_SESSION['user_district_id'] ?? null;

// ฟังก์ชันสำหรับเช็คสิทธิ์ (ใช้ในหน้าต่างๆ)
function requireRole($allowed_roles) {
    global $user_role_id;
    if (!in_array($user_role_id, $allowed_roles)) {
        // ไม่มีสิทธิ์
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
        echo "<h1 style='color:red;'>Access Denied / ไม่มีสิทธิ์เข้าถึง</h1>";
        echo "<p>คุณไม่มีสิทธิ์เข้าถึงหน้านี้ หรือทำรายการนี้</p>";
        echo "<a href='index.php'>กลับหน้าหลัก</a>";
        echo "</div>";
        exit();
    }
}
?>
