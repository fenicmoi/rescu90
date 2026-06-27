<?php
require_once 'auth.php';
requireRole([1, 2, 3, 4]);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    $station_id = trim($_POST['station_id']);
    $camera_type_id = !empty($_POST['camera_type_id']) ? intval($_POST['camera_type_id']) : null;
    $affiliation = trim($_POST['affiliation']);
    $police_station_id = !empty($_POST['police_station_id']) ? intval($_POST['police_station_id']) : null;
    $location_name = trim($_POST['location_name']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);
    
    $district_id = !empty($_POST['district_id']) ? intval($_POST['district_id']) : null;
    $subdistrict_id = !empty($_POST['subdistrict_id']) ? intval($_POST['subdistrict_id']) : null;

    if(empty($station_id) || empty($affiliation) || empty($location_name) || empty($latitude) || empty($longitude)) {
        $_SESSION['error_msg'] = "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน";
        header("Location: manage_cctv.php");
        exit;
    }

    try {
        if ($id > 0) {
            // Update
            $sql = "UPDATE cctv_locations SET 
                    station_id = ?, 
                    camera_type_id = ?, 
                    affiliation = ?, 
                    police_station_id = ?, 
                    location_name = ?, 
                    latitude = ?, 
                    longitude = ?,
                    district_id = ?,
                    subdistrict_id = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$station_id, $camera_type_id, $affiliation, $police_station_id, $location_name, $latitude, $longitude, $district_id, $subdistrict_id, $id]);
            
            $_SESSION['success_msg'] = "อัปเดตข้อมูล CCTV เรียบร้อยแล้ว";
        } else {
            // Insert
            $sql = "INSERT INTO cctv_locations (station_id, camera_type_id, affiliation, police_station_id, location_name, latitude, longitude, district_id, subdistrict_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$station_id, $camera_type_id, $affiliation, $police_station_id, $location_name, $latitude, $longitude, $district_id, $subdistrict_id]);
            
            $_SESSION['success_msg'] = "เพิ่มข้อมูล CCTV เรียบร้อยแล้ว";
        }
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    
    header("Location: manage_cctv.php");
    exit;
} else {
    header("Location: manage_cctv.php");
    exit;
}
?>
