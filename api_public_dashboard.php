<?php
// api_public_dashboard.php
// ดึงข้อมูลสรุปสถิติสำหรับหน้า Public Dashboard (ไม่ต้อง Login)
require_once 'db_config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $params = [];

    // 1. ยอดรวมจุดเสี่ยงทั้งหมด และที่แก้ไขแล้ว
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) as total, SUM(IF(status = 'resolved', 1, 0)) as resolved FROM risk_locations");
    $stmtTotal->execute();
    $riskStats = $stmtTotal->fetch();
    $totalLocations = $riskStats['total'];
    $resolvedLocations = $riskStats['resolved'] ?? 0;

    // 2. สถิติแยกตามประเภทความเสี่ยง (สำหรับ Card และ Pie Chart)
    $sqlType = "SELECT rt.id, rt.type_name, rt.marker_color, COUNT(rl.id) as count 
                FROM risk_types rt 
                LEFT JOIN risk_locations rl ON rt.id = rl.risk_type_id
                GROUP BY rt.id ORDER BY count DESC";
    $stmtType = $pdo->prepare($sqlType);
    $stmtType->execute();
    $typeStats = $stmtType->fetchAll();

    // 3. สถิติแยกตามอำเภอ (สำหรับ Bar Chart)
    $sqlDistrict = "SELECT d.name_th as district_name, COUNT(rl.id) as count 
                    FROM districts d 
                    LEFT JOIN risk_locations rl ON d.id = rl.district_id
                    GROUP BY d.id ORDER BY count DESC";
    $stmtDistrict = $pdo->prepare($sqlDistrict);
    $stmtDistrict->execute();
    $districtStats = $stmtDistrict->fetchAll();

    // 4. รายการที่ได้รับแจ้งล่าสุด 10 รายการ (สำหรับตาราง)
    $sqlRecent = "SELECT rl.id, rl.location_name, rl.details, rl.created_at, 
                         d.name_th as district_name, sd.name_th as subdistrict_name, 
                         rt.type_name, rt.marker_color, 'risk' as record_type
                  FROM risk_locations rl 
                  LEFT JOIN districts d ON rl.district_id = d.id 
                  LEFT JOIN subdistricts sd ON rl.subdistrict_id = sd.id 
                  LEFT JOIN risk_types rt ON rl.risk_type_id = rt.id 
                  UNION ALL
                  SELECT th.id, th.house_name as location_name, th.details, th.created_at,
                         d.name_th as district_name, sd.name_th as subdistrict_name,
                         tt.type_name, tt.marker_color, 'target' as record_type
                  FROM target_houses th
                  LEFT JOIN districts d ON th.district_id = d.id 
                  LEFT JOIN subdistricts sd ON th.subdistrict_id = sd.id 
                  LEFT JOIN target_types tt ON th.target_type_id = tt.id
                  ORDER BY created_at DESC LIMIT 10";

    $stmtRecent = $pdo->prepare($sqlRecent);
    $stmtRecent->execute();
    $recentReports = $stmtRecent->fetchAll();

    // 5. ยอดรวมบ้านเป้าหมายทั้งหมด และที่ดำเนินการแล้ว
    $stmtTargetTotal = $pdo->prepare("SELECT COUNT(*) as total, SUM(IF(status = 'resolved', 1, 0)) as resolved FROM target_houses");
    $stmtTargetTotal->execute();
    $targetStats = $stmtTargetTotal->fetch();
    $totalTargetHouses = $targetStats['total'];
    $resolvedTargetHouses = $targetStats['resolved'] ?? 0;

    // 6. สถิติบ้านเป้าหมายแยกตามประเภท
    $sqlTargetType = "SELECT tt.id, tt.type_name, tt.marker_color, COUNT(th.id) as count 
                      FROM target_types tt 
                      LEFT JOIN target_houses th ON tt.id = th.target_type_id
                      GROUP BY tt.id ORDER BY count DESC";
    $stmtTargetType = $pdo->prepare($sqlTargetType);
    $stmtTargetType->execute();
    $targetTypeStats = $stmtTargetType->fetchAll();

    // 7. สถิติบ้านเป้าหมายแยกตามอำเภอ
    $sqlTargetDistrict = "SELECT d.name_th as district_name, COUNT(th.id) as count 
                          FROM districts d 
                          LEFT JOIN target_houses th ON d.id = th.district_id
                          GROUP BY d.id ORDER BY count DESC";
    $stmtTargetDistrict = $pdo->prepare($sqlTargetDistrict);
    $stmtTargetDistrict->execute();
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
