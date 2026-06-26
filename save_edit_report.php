<?php
// save_edit_report.php
session_start();
require_once 'auth.php';
requireRole([1, 3, 4]); // Admin, District Chief, Officer
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $type = $_POST['type'] ?? '';
    $locationName = trim($_POST['location_name'] ?? '');
    $typeId = $_POST['type_id'] ?? null;
    $districtId = $_POST['district_id'] ?? null;
    $subdistrictId = $_POST['subdistrict_id'] ?? null;
    $incidentDate = $_POST['incident_date'] ?? null;
    $details = trim($_POST['details'] ?? '');
    $preventive_measures = trim($_POST['preventive_measures'] ?? '');
    $lat = $_POST['latitude'] ?? null;
    $lng = $_POST['longitude'] ?? null;

    if (!$id || !in_array($type, ['risk', 'target']) || !$locationName || !$typeId || !$districtId || !$subdistrictId || !$lat || !$lng) {
        $_SESSION['error_msg'] = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
        header("Location: my_reports.php");
        exit();
    }

    // Permission Check: Officers/District Chiefs can only edit if it's their district or they reported it
    if ($user_role_id == 3 || $user_role_id == 4) {
        $checkStmt = $type === 'risk' ? 
            $pdo->prepare("SELECT district_id, reported_by FROM risk_locations WHERE id = ?") :
            $pdo->prepare("SELECT district_id, reported_by FROM target_houses WHERE id = ?");
        $checkStmt->execute([$id]);
        $oldData = $checkStmt->fetch();

        if (!$oldData || ($oldData['district_id'] != $user_district_id && $oldData['reported_by'] != $_SESSION['user_id'])) {
            $_SESSION['error_msg'] = "ไม่มีสิทธิ์เข้าถึงข้อมูลนี้";
            header("Location: my_reports.php");
            exit();
        }
    }

    try {
        if ($type === 'risk') {
            $stmt = $pdo->prepare("
                UPDATE risk_locations 
                SET location_name = ?, risk_type_id = ?, district_id = ?, subdistrict_id = ?, 
                    details = ?, preventive_measures = ?, incident_date = ?, latitude = ?, longitude = ?
                WHERE id = ?
            ");
            $stmt->execute([$locationName, $typeId, $districtId, $subdistrictId, $details, $preventive_measures, $incidentDate, $lat, $lng, $id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE target_houses 
                SET house_name = ?, target_type_id = ?, district_id = ?, subdistrict_id = ?, 
                    details = ?, preventive_measures = ?, incident_date = ?, latitude = ?, longitude = ?
                WHERE id = ?
            ");
            $stmt->execute([$locationName, $typeId, $districtId, $subdistrictId, $details, $preventive_measures, $incidentDate, $lat, $lng, $id]);
        }

        $_SESSION['success_msg'] = "แก้ไขข้อมูลและพิกัดสำเร็จ!";
        header("Location: my_reports.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: my_reports.php");
        exit();
    }
} else {
    header("Location: my_reports.php");
    exit();
}
?>
