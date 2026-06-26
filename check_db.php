<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require 'db_config.php';
$stmt = $pdo->query("DESCRIBE risk_locations");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("DESCRIBE target_houses");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
