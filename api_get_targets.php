<?php
// api_get_targets.php
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
        $whereClause = "WHERE th.district_id = :user_district_id";
        $params[':user_district_id'] = $user_district_id;
    } elseif ($user_role_id == 4) {
        $whereClause = "WHERE th.reported_by = :user_id OR th.district_id = :user_district_id";
        $params[':user_id'] = $user_id;
        $params[':user_district_id'] = $user_district_id;
    }
    // 1. Get Target Types
    $stmtTypes = $pdo->query("SELECT * FROM target_types ORDER BY id ASC");
    $targetTypes = $stmtTypes->fetchAll();

    // 2. Get Target Houses (Aliasing columns to match existing logic in JS)
    $sql_targets = "
        SELECT 
            th.id, 
            th.house_name as location_name, 
            th.latitude, 
            th.longitude, 
            th.details, 
            th.status,
            th.district_id,
            d.name_th as district_name,
            th.subdistrict_id,
            sd.name_th as subdistrict_name,
            th.target_type_id,
            tt.type_name,
            tt.marker_color,
            th.preventive_measures,
            th.incident_date,
            th.image_before,
            th.image_after
        FROM target_houses th
        LEFT JOIN districts d ON th.district_id = d.id
        LEFT JOIN subdistricts sd ON th.subdistrict_id = sd.id
        LEFT JOIN target_types tt ON th.target_type_id = tt.id
        WHERE 1=1
    ";

    $params = [];
    
    // Role-based filtering
    if ($user_role_id == 3) {
        $sql_targets .= " AND th.district_id = :user_district_id";
        $params[':user_district_id'] = $user_district_id;
    } elseif ($user_role_id == 4) {
        $sql_targets .= " AND th.reported_by = :user_id";
        $params[':user_id'] = $user_id;
    }

    // Optional filters
    if (!empty($_GET['district_id'])) {
        $sql_targets .= " AND th.district_id = :district_id";
        $params[':district_id'] = $_GET['district_id'];
    }
    if (!empty($_GET['target_type_id'])) {
        $sql_targets .= " AND th.target_type_id = :target_type_id";
        $params[':target_type_id'] = $_GET['target_type_id'];
    }
    if (!empty($_GET['status'])) {
        $sql_targets .= " AND th.status = :status";
        $params[':status'] = $_GET['status'];
    }

    $stmtTargets = $pdo->prepare($sql_targets);
    $stmtTargets->execute($params);
    $locations = $stmtTargets->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'target_types' => $targetTypes,
            'locations' => $locations
        ]
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . $e->getMessage()
    ]);
}
?>
