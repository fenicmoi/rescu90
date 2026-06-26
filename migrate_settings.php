<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Create settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT,
        description VARCHAR(255)
    )");
    echo "Settings table created or already exists.\n";

    // Insert Telegram Token
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    $stmt->execute(['TELEGRAM_BOT_TOKEN', '', 'Telegram Bot Token from BotFather']);
    $stmt->execute(['TELEGRAM_CHAT_ID', '', 'Telegram Chat ID (e.g., -1001234567890)']);
    echo "Default Telegram settings inserted.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
