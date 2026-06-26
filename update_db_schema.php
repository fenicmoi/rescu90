<?php
$_SERVER['HTTP_HOST'] = 'localhost';
require 'db_config.php';

try {
    // Add reporter_name and reporter_phone to risk_locations if they don't exist
    $pdo->exec("ALTER TABLE risk_locations ADD COLUMN reporter_name VARCHAR(255) NULL AFTER image_after");
    echo "Added reporter_name to risk_locations.\n";
} catch (PDOException $e) {
    echo "risk_locations: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE risk_locations ADD COLUMN reporter_phone VARCHAR(50) NULL AFTER reporter_name");
    echo "Added reporter_phone to risk_locations.\n";
} catch (PDOException $e) {
    echo "risk_locations: " . $e->getMessage() . "\n";
}

try {
    // Add reporter_name and reporter_phone to target_houses if they don't exist
    $pdo->exec("ALTER TABLE target_houses ADD COLUMN reporter_name VARCHAR(255) NULL AFTER image_after");
    echo "Added reporter_name to target_houses.\n";
} catch (PDOException $e) {
    echo "target_houses: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE target_houses ADD COLUMN reporter_phone VARCHAR(50) NULL AFTER reporter_name");
    echo "Added reporter_phone to target_houses.\n";
} catch (PDOException $e) {
    echo "target_houses: " . $e->getMessage() . "\n";
}

echo "Database schema update completed.";
?>
