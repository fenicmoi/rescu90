<?php
// save_location.php
require_once 'auth.php';
// ตรวจสอบสิทธิ์ (ให้เฉพาะ Admin=1 และ Officer=4 สามารถเพิ่มข้อมูลได้)
requireRole([1, 3, 4]); // Admin, District Chief, Officer

require_once 'db_config.php';

// ตรวจสอบว่าเป็นการส่งข้อมูลแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // รับค่าและตัดช่องว่างซ้ายขวา (Trim)
    $location_name  = isset($_POST['location_name']) ? trim($_POST['location_name']) : '';
    $district_id    = isset($_POST['district_id']) ? trim($_POST['district_id']) : '';
    
    // บังคับใช้อำเภอของตนเองเท่านั้น (ป้องกันการแก้ไข HTML)
    if (!empty($user_district_id)) {
        $district_id = $user_district_id;
    }
    $subdistrict_id = isset($_POST['subdistrict_id']) ? trim($_POST['subdistrict_id']) : null;
    $risk_type_id   = isset($_POST['risk_type_id']) ? trim($_POST['risk_type_id']) : '';
    $latitude       = isset($_POST['latitude']) ? trim($_POST['latitude']) : '';
    $longitude      = isset($_POST['longitude']) ? trim($_POST['longitude']) : '';
    $details        = isset($_POST['details']) ? trim($_POST['details']) : '';
    
    // Server-side Validation: ตรวจสอบความครบถ้วนของข้อมูลที่จำเป็น
    if (empty($location_name) || empty($district_id) || empty($subdistrict_id) || empty($risk_type_id) || empty($latitude) || empty($longitude)) {
        die("
            <!DOCTYPE html><html><head><script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script></head><body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน โดยเฉพาะพิกัดละติจูดและลองจิจูด',
                    confirmButtonText: 'กลับไปแก้ไข'
                }).then(() => {
                    window.location.href = 'add_location.php';
                });
            </script>
            </body></html>
        ");
    }

    // File upload handling for image_before
    $image_before_path = null;
    if (isset($_FILES['image_before']) && $_FILES['image_before']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_before']['tmp_name'];
        $fileName = $_FILES['image_before']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_before_path = $newFileName;
            }
        }
    }

    try {
        // ตรวจสอบสิทธิ์และพื้นที่เพื่อกำหนดสถานะ (ถ้าเป็นเจ้าหน้าที่ในพื้นที่ตัวเอง ให้อนุมัติอัตโนมัติ)
        $status = 'pending';
        if ($user_role_id == 1 || $user_role_id == 2) {
            $status = 'active'; // Admin, Governor อนุมัติเลย
        } elseif (($user_role_id == 3 || $user_role_id == 4) && $district_id == $user_district_id) {
            $status = 'active'; // เจ้าหน้าที่อำเภอแจ้งในอำเภอตัวเอง อนุมัติเลย
        }

        // 3. เตรียมคำสั่ง SQL บันทึกข้อมูล
        $sql = "INSERT INTO risk_locations (district_id, subdistrict_id, risk_type_id, location_name, latitude, longitude, details, image_before, reported_by, status, created_at)
                VALUES (:district_id, :subdistrict_id, :risk_type_id, :location_name, :latitude, :longitude, :details, :image_before, :reported_by, :status, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        // ผูกตัวแปร (Bind Parameters)
        $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        $stmt->bindParam(':subdistrict_id', $subdistrict_id, PDO::PARAM_INT);
        $stmt->bindParam(':risk_type_id', $risk_type_id, PDO::PARAM_INT);
        $stmt->bindParam(':location_name', $location_name, PDO::PARAM_STR);
        $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindParam(':details', $details, PDO::PARAM_STR);
        $stmt->bindParam(':image_before', $image_before_path, PDO::PARAM_STR);
        $stmt->bindParam(':reported_by', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        
        // รันคำสั่ง
        $stmt->execute();
        
        // บันทึกสำเร็จ แสดง Alert และ Redirect กลับไปหน้าแรกเพื่อดูหมุดใหม่
        echo "
            <!DOCTYPE html><html><head><script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script></head><body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: 'บันทึกข้อมูลพิกัดจุดเสี่ยงเรียบร้อยแล้ว',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    window.location.href = 'map_dashboard.php';
                });
            </script>
            </body></html>
        ";
        exit;
        
    } catch (\PDOException $e) {
        // หากเกิดข้อผิดพลาดจากฐานข้อมูล
        error_log("Insert Location Error: " . $e->getMessage());
        die("
            <!DOCTYPE html><html><head><script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script></head><body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'ระบบขัดข้อง!',
                    text: 'ไม่สามารถบันทึกข้อมูลได้ในขณะนี้ กรุณาติดต่อผู้ดูแลระบบ',
                    confirmButtonText: 'กลับไปหน้าฟอร์ม'
                }).then(() => {
                    window.location.href = 'add_location.php';
                });
            </script>
            </body></html>
        ");
    }
} else {
    // ถ้าเข้าหน้านี้โดยตรงโดยไม่ผ่านฟอร์ม ให้เด้งกลับไปหน้าฟอร์ม
    header("Location: add_location.php");
    exit;
}
?>

