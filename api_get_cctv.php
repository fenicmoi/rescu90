<?php
require_once 'auth.php';
require_once 'db_config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // ดึงข้อมูลกล้อง CCTV ทั้งหมด
    $stmt = $pdo->query("SELECT c.id, c.station_id, c.affiliation, p.station_name as police_station, t.type_name as camera_type, c.location_name, c.latitude, c.longitude, c.district_id, c.subdistrict_id 
                         FROM cctv_locations c
                         LEFT JOIN police_stations p ON c.police_station_id = p.id
                         LEFT JOIN camera_types t ON c.camera_type_id = t.id");
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $locations
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
