<?php
require_once 'auth.php';
requireRole([1, 2, 3, 4]);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    
    $file = $_FILES['csv_file'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_msg'] = "อัปโหลดไฟล์ไม่สำเร็จ Error Code: " . $file['error'];
        header("Location: manage_cctv.php");
        exit;
    }
    
    // Check file extension
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($ext) !== 'csv') {
        $_SESSION['error_msg'] = "กรุณาอัปโหลดเฉพาะไฟล์ .csv เท่านั้น";
        header("Location: manage_cctv.php");
        exit;
    }
    
    $handle = fopen($file['tmp_name'], "r");
    
    if ($handle !== FALSE) {
        
        $row = 0;
        $success_count = 0;
        
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("INSERT INTO cctv_locations (station_id, affiliation, police_station, camera_type, location_name, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                
                // Skip header (first row)
                if ($row === 1) {
                    continue;
                }
                
                // Make sure we have at least 7 columns
                if (count($data) >= 7) {
                    
                    // Function to safely convert encoding (TIS-620/Windows-874 from Excel CSV to UTF-8)
                    $cleanData = array_map(function($val) {
                        // Remove BOM if exists
                        $val = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $val);
                        
                        // Check if it's already valid UTF-8
                        if (mb_check_encoding($val, 'UTF-8')) {
                            return trim($val);
                        } else {
                            // Convert from TIS-620 to UTF-8
                            $converted = @iconv('TIS-620', 'UTF-8//IGNORE', $val);
                            return trim($converted !== false ? $converted : '');
                        }
                    }, $data);
                    
                    $station_id = $cleanData[0];
                    $affiliation = $cleanData[1];
                    $police_station = $cleanData[2];
                    $camera_type = $cleanData[3];
                    $location_name = $cleanData[4];
                    $latitude = floatval($cleanData[5]);
                    $longitude = floatval($cleanData[6]);
                    
                    // Basic validation
                    if (!empty($station_id) && !empty($location_name) && !empty($latitude) && !empty($longitude)) {
                        $stmt->execute([$station_id, $affiliation, $police_station, $camera_type, $location_name, $latitude, $longitude]);
                        $success_count++;
                    }
                }
            }
            
            $pdo->commit();
            fclose($handle);
            
            if ($success_count > 0) {
                $_SESSION['success_msg'] = "นำเข้าข้อมูล CCTV สำเร็จจำนวน {$success_count} รายการ";
            } else {
                $_SESSION['error_msg'] = "ไม่มีข้อมูลถูกนำเข้า (อาจจัดคอลัมน์ไม่ถูกต้อง หรือไม่มีพิกัด Latitude/Longitude)";
            }
            
        } catch (Exception $e) {
            $pdo->rollBack();
            fclose($handle);
            $_SESSION['error_msg'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_msg'] = "ไม่สามารถเปิดไฟล์อัปโหลดได้";
    }
    
    header("Location: manage_cctv.php");
    exit;
} else {
    header("Location: manage_cctv.php");
    exit;
}
?>
