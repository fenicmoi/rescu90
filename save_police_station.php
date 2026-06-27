<?php
require_once 'auth.php';
requireRole([1]);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $station_code = trim($_POST['station_code'] ?? '');
    $station_name = trim($_POST['station_name'] ?? '');
    $district_id = intval($_POST['district_id'] ?? 0);

    if (empty($station_code) || empty($station_name) || $district_id <= 0) {
        $_SESSION['error_msg'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        header("Location: police_station_form.php" . ($id > 0 ? "?id=$id" : ""));
        exit;
    }

    try {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE police_stations SET station_code = ?, station_name = ?, district_id = ? WHERE id = ?");
            $stmt->execute([$station_code, $station_name, $district_id, $id]);
            $_SESSION['success_msg'] = "อัปเดตข้อมูลสำเร็จ";
        } else {
            $stmt = $pdo->prepare("INSERT INTO police_stations (station_code, station_name, district_id) VALUES (?, ?, ?)");
            $stmt->execute([$station_code, $station_name, $district_id]);
            $_SESSION['success_msg'] = "เพิ่มข้อมูลสำเร็จ";
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

header("Location: manage_police_stations.php");
exit;
