<?php
// api_get_locations.php
// API สำหรับดึงข้อมูลจุดเสี่ยง ข้อมูลอำเภอ และประเภทความเสี่ยง เพื่อนำไปแสดงผลบนแผนที่และตัวกรอง
require_once 'auth.php';
require_once 'db_config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $whereClause = "";
    $params = [];

    // 1. Admin, 2. Governor: See all on map
    // 3. District Chief: See only own district
    // 4. Officer: See only own inputted data
    if ($user_role_id == 3) {
        $whereClause = "WHERE rl.district_id = :user_district_id";
        $params[':user_district_id'] = $user_district_id;
    } elseif ($user_role_id == 4) {
        $whereClause = "WHERE rl.reported_by = :user_id OR rl.district_id = :user_district_id";
        $params[':user_id'] = $user_id;
        $params[':user_district_id'] = $user_district_id;
    }
    
    if (!empty($_GET['status'])) {
        $whereClause .= ($whereClause ? " AND " : " WHERE ") . "rl.status = :status";
        $params[':status'] = $_GET['status'];
    }

    // 1. ดึงข้อมูลจุดเสี่ยง พร้อม join ข้อมูลชื่ออำเภอและประเภทความเสี่ยง
    $sql_locations = "
        SELECT 
            rl.id, 
            rl.location_name, 
            rl.latitude, 
            rl.longitude, 
            rl.details, 
            rl.district_id,
            d.name_th as district_name,
            rl.subdistrict_id,
            sd.name_th as subdistrict_name,
            rl.risk_type_id,
            rt.type_name,
            rt.marker_color,
            rl.status,
            rl.preventive_measures,
            rl.incident_date,
            rl.image_before,
            rl.image_after
        FROM risk_locations rl
        LEFT JOIN districts d ON rl.district_id = d.id
        LEFT JOIN subdistricts sd ON rl.subdistrict_id = sd.id
        LEFT JOIN risk_types rt ON rl.risk_type_id = rt.id
        $whereClause
    ";
    $stmt = $pdo->prepare($sql_locations);
    $stmt->execute($params);
    $locations = $stmt->fetchAll();
    
    // 2. ดึงข้อมูลอำเภอ สำหรับทำ Dropdown ตัวกรอง
    if (in_array($user_role_id, [3, 4]) && !empty($user_district_id)) {
        $stmtDistricts = $pdo->prepare("SELECT id, name_th FROM districts WHERE id = :dist_id ORDER BY name_th ASC");
        $stmtDistricts->execute([':dist_id' => $user_district_id]);
        $districts = $stmtDistricts->fetchAll();
    } else {
        $stmtDistricts = $pdo->query("SELECT id, name_th FROM districts ORDER BY name_th ASC");
        $districts = $stmtDistricts->fetchAll();
    }
    
    // 3. ดึงข้อมูลประเภทความเสี่ยง สำหรับทำ Checkbox ตัวกรอง
    $stmt = $pdo->query("SELECT id, type_name, marker_color FROM risk_types ORDER BY id ASC");
    $risk_types = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'locations' => $locations,
            'districts' => $districts,
            'risk_types' => $risk_types
        ]
    ]);

} catch (\PDOException $e) {
    // ส่ง Error response หากดึงข้อมูลไม่สำเร็จ
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage()
    ]);
}
?>
