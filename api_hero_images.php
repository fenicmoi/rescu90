<?php
// api_hero_images.php
require_once 'db_config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->prepare("SELECT * FROM hero_images WHERE is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $images = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $images]);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
