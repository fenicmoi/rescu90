<?php
require_once 'auth.php';
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role_id']) || ($_SESSION['role_id'] != 1 && strpos(strtolower($_SESSION['role_name'] ?? ''), 'admin') === false)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$type = $data['type'] ?? null; // 'cctv', 'incident', 'target'
$lat = $data['lat'] ?? null;
$lng = $data['lng'] ?? null;

if (!$id || !$type || !$lat || !$lng) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

try {
    if ($type === 'cctv') {
        $stmt = $pdo->prepare("UPDATE cctv_locations SET latitude = ?, longitude = ? WHERE id = ?");
    } elseif ($type === 'incident') {
        $stmt = $pdo->prepare("UPDATE risk_locations SET latitude = ?, longitude = ? WHERE id = ?");
    } elseif ($type === 'target') {
        $stmt = $pdo->prepare("UPDATE target_houses SET latitude = ?, longitude = ? WHERE id = ?");
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid type']);
        exit;
    }
    
    $stmt->execute([$lat, $lng, $id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
