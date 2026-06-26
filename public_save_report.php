<?php
session_start();
require_once 'db_config.php';
require_once 'telegram_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['report_type'] ?? 'risk'; // 'risk' or 'target'
    $locationName = trim($_POST['location_name'] ?? '');
    $districtId = $_POST['district_id'] ?? null;
    $subdistrictId = $_POST['subdistrict_id'] ?? null;
    $details = trim($_POST['details'] ?? '');
    $lat = $_POST['latitude'] ?? null;
    $lng = $_POST['longitude'] ?? null;

    $reporterName = trim($_POST['reporter_name'] ?? '');
    $reporterPhone = trim($_POST['reporter_phone'] ?? '');
    $incidentDate = trim($_POST['incident_date'] ?? date('Y-m-d'));

    if (!$locationName || !$districtId || !$subdistrictId || !$lat || !$lng || !$reporterName || !$reporterPhone) {
        $_SESSION['error_msg'] = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
        header("Location: public_report.php");
        exit();
    }

    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($fileExt, $allowedExt)) {
            $imageName = md5(uniqid(rand(), true)) . '.' . $fileExt;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        } else {
            $_SESSION['error_msg'] = "รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, WEBP) เท่านั้น";
            header("Location: public_report.php");
            exit();
        }
    }

    try {
        if ($reportType === 'risk') {
            $riskTypeId = $_POST['risk_type_id'] ?? null;
            if (!$riskTypeId) throw new Exception("กรุณาเลือกประเภทความเสี่ยง");

            $stmt = $pdo->prepare("INSERT INTO risk_locations (district_id, subdistrict_id, risk_type_id, location_name, latitude, longitude, status, details, incident_date, image_before, reporter_name, reporter_phone) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
            $stmt->execute([$districtId, $subdistrictId, $riskTypeId, $locationName, $lat, $lng, $details, $incidentDate, $imageName, $reporterName, $reporterPhone]);
            
            $typeNameQuery = $pdo->prepare("SELECT type_name FROM risk_types WHERE id = ?");
            $typeNameQuery->execute([$riskTypeId]);
            $typeName = $typeNameQuery->fetchColumn();

        } else {
            $targetTypeId = $_POST['target_type_id'] ?? null;
            if (!$targetTypeId) throw new Exception("กรุณาเลือกประเภทบ้านเป้าหมาย");

            $stmt = $pdo->prepare("INSERT INTO target_houses (district_id, subdistrict_id, target_type_id, house_name, latitude, longitude, status, details, incident_date, image_before, reporter_name, reporter_phone) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
            $stmt->execute([$districtId, $subdistrictId, $targetTypeId, $locationName, $lat, $lng, $details, $incidentDate, $imageName, $reporterName, $reporterPhone]);
            
            $typeNameQuery = $pdo->prepare("SELECT type_name FROM target_types WHERE id = ?");
            $typeNameQuery->execute([$targetTypeId]);
            $typeName = $typeNameQuery->fetchColumn();
        }

        // Send Telegram Notify
        $stmtDist = $pdo->prepare("SELECT d.name_th as district, s.name_th as subdistrict FROM districts d JOIN subdistricts s ON s.district_id = d.id WHERE d.id = ? AND s.id = ?");
        $stmtDist->execute([$districtId, $subdistrictId]);
        $area = $stmtDist->fetch(PDO::FETCH_ASSOC);
        $areaText = $area ? "อ.{$area['district']} ต.{$area['subdistrict']}" : "ไม่ระบุพื้นที่";

        $message = "🚨 <b>มีการแจ้งเหตุใหม่เข้าสู่ระบบ!</b>\n";
        $message .= "<b>ประเภท:</b> " . ($reportType === 'risk' ? "จุดเสี่ยง" : "บ้านเป้าหมาย") . " ($typeName)\n";
        $message .= "<b>สถานที่:</b> $locationName\n";
        $message .= "<b>พื้นที่:</b> $areaText\n";
        $message .= "<b>พิกัด:</b> <a href=\"https://www.google.com/maps/search/?api=1&query={$lat},{$lng}\">Google Maps</a>\n";
        $message .= "\n⚠️ <i>กรุณาเข้าสู่ระบบ CRIME MAP เพื่อตรวจสอบและอนุมัติ</i>";
        
        sendTelegramNotify($pdo, $message);

        $_SESSION['success_msg'] = "ส่งข้อมูลสำเร็จ! เจ้าหน้าที่จะดำเนินการตรวจสอบข้อมูลของคุณโดยเร็วที่สุด";
        header("Location: public_report.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: public_report.php");
        exit();
    }
} else {
    header("Location: public_report.php");
    exit();
}
?>
