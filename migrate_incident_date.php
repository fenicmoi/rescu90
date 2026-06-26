<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Add incident_date to risk_locations
    $pdo->exec("ALTER TABLE risk_locations ADD COLUMN incident_date DATE DEFAULT NULL");
    echo "Added incident_date to risk_locations.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column incident_date already exists in risk_locations.\n";
    } else {
        echo "Error on risk_locations: " . $e->getMessage() . "\n";
    }
}

try {
    // Add incident_date to target_houses
    $pdo->exec("ALTER TABLE target_houses ADD COLUMN incident_date DATE DEFAULT NULL");
    echo "Added incident_date to target_houses.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column incident_date already exists in target_houses.\n";
    } else {
        echo "Error on target_houses: " . $e->getMessage() . "\n";
    }
}
