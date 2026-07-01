<?php
// api_dashboard.php
// ดึงข้อมูลสรุปสถิติสำหรับหน้า Executive Dashboard
require_once 'auth.php';
require_once 'db_config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $whereRisk = "";
    $whereTarget = "";
    $params = [];

    if ($user_role_id == 3 || $user_role_id == 4) {
        $whereRisk = "WHERE district_id = :user_district_id";
        $whereTarget = "WHERE district_id = :user_district_id";
        $params[':user_district_id'] = $user_district_id;
    } else {
        if (isset($_GET['district_id']) && is_numeric($_GET['district_id']) && $_GET['district_id'] > 0) {
            $whereRisk = "WHERE district_id = :filter_district_id";
            $whereTarget = "WHERE district_id = :filter_district_id";
            $params[':filter_district_id'] = $_GET['district_id'];
        }
    }

    // 1. ยอดรวมจุดเสี่ยงทั้งหมด และที่แก้ไขแล้ว
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) as total, SUM(IF(status = 'resolved', 1, 0)) as resolved FROM risk_locations $whereRisk");
    $stmtTotal->execute($params);
    $riskStats = $stmtTotal->fetch();
    $totalLocations = $riskStats['total'];
    $resolvedLocations = $riskStats['resolved'] ?? 0;

    // 2. สถิติแยกตามประเภทความเสี่ยง (สำหรับ Card และ Pie Chart)
    $whereRiskJoin = $whereRisk ? str_replace("WHERE ", "AND rl.", $whereRisk) : "";
    $sqlType = "SELECT rt.id, rt.type_name, rt.marker_color, COUNT(rl.id) as count 
                FROM risk_types rt 
                LEFT JOIN risk_locations rl ON rt.id = rl.risk_type_id $whereRiskJoin
                GROUP BY rt.id ORDER BY count DESC";
    $stmtType = $pdo->prepare($sqlType);
    $stmtType->execute($params);
    $typeStats = $stmtType->fetchAll();

    // 3. สถิติแยกตามอำเภอ (สำหรับ Bar Chart)
    $sqlDistrict = "SELECT d.name_th as district_name, COUNT(rl.id) as count 
                    FROM districts d 
                    LEFT JOIN risk_locations rl ON d.id = rl.district_id $whereRiskJoin
                    GROUP BY d.id ORDER BY count DESC";
    $stmtDistrict = $pdo->prepare($sqlDistrict);
    $stmtDistrict->execute($params);
    $districtStats = $stmtDistrict->fetchAll();

    // 4. รายการที่ได้รับแจ้งล่าสุด 10 รายการ (สำหรับตาราง)
    $whereRiskRl = $whereRisk ? str_replace("WHERE ", "WHERE rl.", $whereRisk) : "";
    $whereTargetTh = $whereTarget ? str_replace("WHERE ", "WHERE th.", $whereTarget) : "";

    $sqlRecent = "SELECT rl.id, rl.location_name, rl.details, rl.created_at, 
                         d.name_th as district_name, sd.name_th as subdistrict_name, 
                         rt.type_name, rt.marker_color, 'risk' as record_type
                  FROM risk_locations rl 
                  LEFT JOIN districts d ON rl.district_id = d.id 
                  LEFT JOIN subdistricts sd ON rl.subdistrict_id = sd.id 
                  LEFT JOIN risk_types rt ON rl.risk_type_id = rt.id 
                  $whereRiskRl
                  UNION ALL
                  SELECT th.id, th.house_name as location_name, th.details, th.created_at,
                         d.name_th as district_name, sd.name_th as subdistrict_name,
                         tt.type_name, tt.marker_color, 'target' as record_type
                  FROM target_houses th
                  LEFT JOIN districts d ON th.district_id = d.id 
                  LEFT JOIN subdistricts sd ON th.subdistrict_id = sd.id 
                  LEFT JOIN target_types tt ON th.target_type_id = tt.id
                  $whereTargetTh
                  ORDER BY created_at DESC LIMIT 10";
    // We duplicate params because they are used twice in UNION
    $unionParams = [];
    foreach ($params as $k => $v) {
        $unionParams[$k] = $v;
        $unionParams[$k . '2'] = $v; // For the second part of union
    }
    
    // Quick hack for PDO named params in union, replace in query
    if ($user_role_id == 3 || $user_role_id == 4) {
        $sqlRecent = str_replace(":user_district_id", ":user_district_id_1", $sqlRecent);
        // Replace ONLY the second occurrence
        $pos = strpos($sqlRecent, ":user_district_id_1", strpos($sqlRecent, "UNION ALL"));
        if ($pos !== false) {
            $sqlRecent = substr_replace($sqlRecent, ":user_district_id_2", $pos, strlen(":user_district_id_1"));
        }
        $unionParams = [':user_district_id_1' => $user_district_id, ':user_district_id_2' => $user_district_id];
    } elseif (isset($_GET['district_id']) && is_numeric($_GET['district_id']) && $_GET['district_id'] > 0) {
        $sqlRecent = str_replace(":filter_district_id", ":filter_district_id_1", $sqlRecent);
        $pos = strpos($sqlRecent, ":filter_district_id_1", strpos($sqlRecent, "UNION ALL"));
        if ($pos !== false) {
            $sqlRecent = substr_replace($sqlRecent, ":filter_district_id_2", $pos, strlen(":filter_district_id_1"));
        }
        $unionParams = [':filter_district_id_1' => $_GET['district_id'], ':filter_district_id_2' => $_GET['district_id']];
    } else {
        $unionParams = [];
    }

    $stmtRecent = $pdo->prepare($sqlRecent);
    $stmtRecent->execute($unionParams);
    $recentReports = $stmtRecent->fetchAll();

    // 5. ยอดรวมบ้านเป้าหมายทั้งหมด และที่ดำเนินการแล้ว
    $stmtTargetTotal = $pdo->prepare("SELECT COUNT(*) as total, SUM(IF(status = 'resolved', 1, 0)) as resolved FROM target_houses $whereTarget");
    $stmtTargetTotal->execute($params);
    $targetStats = $stmtTargetTotal->fetch();
    $totalTargetHouses = $targetStats['total'];
    $resolvedTargetHouses = $targetStats['resolved'] ?? 0;

    // 6. สถิติบ้านเป้าหมายแยกตามประเภท
    $whereTargetJoin = $whereTarget ? str_replace("WHERE ", "AND th.", $whereTarget) : "";
    $sqlTargetType = "SELECT tt.id, tt.type_name, tt.marker_color, COUNT(th.id) as count 
                      FROM target_types tt 
                      LEFT JOIN target_houses th ON tt.id = th.target_type_id $whereTargetJoin
                      GROUP BY tt.id ORDER BY count DESC";
    $stmtTargetType = $pdo->prepare($sqlTargetType);
    $stmtTargetType->execute($params);
    $targetTypeStats = $stmtTargetType->fetchAll();

    // 7. สถิติบ้านเป้าหมายแยกตามอำเภอ
    $sqlTargetDistrict = "SELECT d.name_th as district_name, COUNT(th.id) as count 
                          FROM districts d 
                          LEFT JOIN target_houses th ON d.id = th.district_id $whereTargetJoin
                          GROUP BY d.id ORDER BY count DESC";
    $stmtTargetDistrict = $pdo->prepare($sqlTargetDistrict);
    $stmtTargetDistrict->execute($params);
    $targetDistrictStats = $stmtTargetDistrict->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total' => $totalLocations,
            'resolved' => $resolvedLocations,
            'by_type' => $typeStats,
            'by_district' => $districtStats,
            'recent' => $recentReports,
            'target_total' => $totalTargetHouses,
            'target_resolved' => $resolvedTargetHouses,
            'target_by_type' => $targetTypeStats,
            'target_by_district' => $targetDistrictStats
        ]
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถิติ: ' . $e->getMessage()
    ]);
}
?>
