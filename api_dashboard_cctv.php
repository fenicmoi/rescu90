<?php
require_once 'auth.php';
require_once 'db_config.php';
requireRole([1, 2, 3, 4]); // Requires admin/officer access

header('Content-Type: application/json; charset=utf-8');

try {
    $data = [];

    // 1. Total Cameras
    $stmtTotal = $pdo->query("SELECT COUNT(*) as total FROM cctv_locations");
    $data['total'] = $stmtTotal->fetchColumn();

    // 2. By Camera Type
    $stmtByType = $pdo->query("
        SELECT t.type_name, COUNT(c.id) as count 
        FROM cctv_locations c 
        LEFT JOIN camera_types t ON c.camera_type_id = t.id 
        GROUP BY c.camera_type_id, t.type_name
        ORDER BY count DESC
    ");
    $data['by_type'] = $stmtByType->fetchAll(PDO::FETCH_ASSOC);
    // Replace null types with "ไม่ระบุ"
    foreach ($data['by_type'] as &$t) {
        if (empty($t['type_name'])) {
            $t['type_name'] = 'ไม่ได้ระบุประเภท';
        }
    }

    // 3. By District
    $stmtByDist = $pdo->query("
        SELECT d.name_th as district_name, COUNT(c.id) as count 
        FROM cctv_locations c 
        LEFT JOIN districts d ON c.district_id = d.id 
        GROUP BY c.district_id, d.name_th
        ORDER BY count DESC
    ");
    $data['by_district'] = $stmtByDist->fetchAll(PDO::FETCH_ASSOC);
    foreach ($data['by_district'] as &$d) {
        if (empty($d['district_name'])) {
            $d['district_name'] = 'ไม่ระบุอำเภอ';
        }
    }

    // 4. By Affiliation (Top 10)
    $stmtByAff = $pdo->query("
        SELECT affiliation, COUNT(id) as count 
        FROM cctv_locations 
        GROUP BY affiliation
        ORDER BY count DESC 
        LIMIT 10
    ");
    $data['by_affiliation'] = $stmtByAff->fetchAll(PDO::FETCH_ASSOC);

    // 5. Recent CCTVs (Last 10 added, if we had created_at, but we'll sort by id DESC)
    $stmtRecent = $pdo->query("
        SELECT c.id, c.station_id, c.location_name, c.affiliation, d.name_th as district_name, t.type_name
        FROM cctv_locations c
        LEFT JOIN districts d ON c.district_id = d.id
        LEFT JOIN camera_types t ON c.camera_type_id = t.id
        ORDER BY c.id DESC
        LIMIT 10
    ");
    $data['recent'] = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
