<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Add column to risk_locations if it doesn't exist
    $pdo->exec("ALTER TABLE risk_locations ADD COLUMN preventive_measures TEXT DEFAULT NULL");
    echo "Added preventive_measures to risk_locations.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column preventive_measures already exists in risk_locations.\n";
    } else {
        echo "Error on risk_locations: " . $e->getMessage() . "\n";
    }
}

try {
    // Add column to target_houses if it doesn't exist
    $pdo->exec("ALTER TABLE target_houses ADD COLUMN preventive_measures TEXT DEFAULT NULL");
    echo "Added preventive_measures to target_houses.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column preventive_measures already exists in target_houses.\n";
    } else {
        echo "Error on target_houses: " . $e->getMessage() . "\n";
    }
}
