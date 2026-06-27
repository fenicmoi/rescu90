<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("ALTER TABLE cctv_locations ADD COLUMN district_id INT DEFAULT NULL, ADD COLUMN subdistrict_id INT DEFAULT NULL;");
    echo "Columns added.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
