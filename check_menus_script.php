<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SELECT * FROM menus");
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($menus);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
