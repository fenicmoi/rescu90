<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require_once 'db_config.php';

echo "=== risk_types ===\n";
$stmt = $pdo->query("DESCRIBE risk_types");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "=== target_types ===\n";
$stmt = $pdo->query("DESCRIBE target_types");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
