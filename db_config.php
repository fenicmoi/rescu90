<?php
// db_config.php
// ไฟล์กำหนดค่าการเชื่อมต่อฐานข้อมูล

// ตรวจสอบ Environment ว่ารันอยู่บนเครื่อง Local หรือ Hosting จริง
$is_localhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']);

if ($is_localhost) {
    // การตั้งค่าสำหรับ Localhost (WAMP)
    $host = '127.0.0.1';
    $db   = '90day';
    $user = 'root';
    $pass = '';
} else {
    // การตั้งค่าสำหรับ Hosting จริง (Production)
    $host = 'localhost'; // โดยส่วนใหญ่ใช้ localhost สำหรับ Host จริง
    $db   = 'phatthalun_90day';
    $user = 'phatthalun_dol';
    $pass = 'nSSYV5cJ';
}

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// กำหนด Options สำหรับ PDO เพื่อความปลอดภัยและรัดกุม
$options = [
    // เปิด Error Reporting แบบ Exception เพื่อให้สามารถ try/catch ได้และไม่แสดง Error ออกหน้าเว็บโดยตรง
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // คืนค่าข้อมูลแบบ Associative Array เป็นค่าเริ่มต้น
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // ปิดการจำลอง Prepared Statements เพื่อให้ใช้ฟีเจอร์ของ Database จริงๆ ช่วยป้องกัน SQL Injection
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // บังคับให้ PDO คุยกับ MySQL ด้วย UTF-8 เพื่อป้องกันปัญหาอักขระภาษาไทยอ่านไม่ออก
    $pdo->exec("SET NAMES utf8mb4");
} catch (\PDOException $e) {
    // ในกรณีใช้งานจริง (Production) ควรบันทึก Error ลง Log แทนการแสดงผลเพื่อป้องกันไม่ให้ข้อมูลระบบรั่วไหล
    error_log("Database connection failed: " . $e->getMessage());
    
    // แสดงข้อความทั่วไปกรณีเชื่อมต่อไม่ได้
    die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ");
}
?>
