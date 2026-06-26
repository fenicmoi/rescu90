<?php
function sendTelegramNotify($pdo, $message) {
    try {
        // Fetch token and chat_id from settings
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('TELEGRAM_BOT_TOKEN', 'TELEGRAM_CHAT_ID')");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $botToken = $settings['TELEGRAM_BOT_TOKEN'] ?? '';
        $chatId = $settings['TELEGRAM_CHAT_ID'] ?? '';

        if (empty($botToken) || empty($chatId)) {
            // Not configured, do nothing
            return false;
        }

        $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout after 5 seconds to avoid slowing down form submission
        
        $response = curl_exec($ch);
        curl_close($ch);

        return true;
    } catch (Exception $e) {
        // Silently fail to not break the user flow
        return false;
    }
}
?>
