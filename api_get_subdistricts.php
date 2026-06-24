<?php
// api_get_subdistricts.php
require_once 'db_config.php';

header('Content-Type: application/json; charset=utf-8');

$district_id = $_GET['district_id'] ?? 0;

if (empty($district_id)) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name_th, latitude, longitude FROM subdistricts WHERE district_id = :district_id ORDER BY name_th ASC");
    $stmt->execute([':district_id' => $district_id]);
    $subdistricts = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $subdistricts
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch subdistricts'
    ]);
}
?>
