<?php
// save_target.php
require_once 'auth.php';
// ตรวจสอบสิทธิ์ (ให้เฉพาะ Admin=1 และ Officer=4 สามารถเพิ่มข้อมูลได้)
requireRole([1, 3, 4]); // Admin, District Chief, Officer

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // รับค่าและตัดช่องว่างซ้ายขวา (Trim)
    $house_name     = isset($_POST['house_name']) ? trim($_POST['house_name']) : '';
    $district_id    = isset($_POST['district_id']) ? trim($_POST['district_id']) : '';
    
    // บังคับใช้อำเภอของตนเองเท่านั้น (ป้องกันการแก้ไข HTML)
    if (!empty($user_district_id)) {
        $district_id = $user_district_id;
    }
    $subdistrict_id = isset($_POST['subdistrict_id']) ? trim($_POST['subdistrict_id']) : null;
    $target_type_id = isset($_POST['target_type_id']) ? trim($_POST['target_type_id']) : '';
    $latitude       = isset($_POST['latitude']) ? trim($_POST['latitude']) : '';
    $longitude      = isset($_POST['longitude']) ? trim($_POST['longitude']) : '';
    $details        = isset($_POST['details']) ? trim($_POST['details']) : '';
    
    // Server-side Validation
    if (empty($house_name) || empty($district_id) || empty($subdistrict_id) || empty($target_type_id) || empty($latitude) || empty($longitude)) {
        die("
            <!DOCTYPE html><html><head><script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script></head><body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน',
                    confirmButtonText: 'กลับไปแก้ไขข้อมูล'
                }).then(() => {
                    window.location.href = 'add_target.php';
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

        $sql = "INSERT INTO target_houses (district_id, subdistrict_id, target_type_id, house_name, latitude, longitude, details, image_before, reported_by, status, created_at) 
                VALUES (:district_id, :subdistrict_id, :target_type_id, :house_name, :latitude, :longitude, :details, :image_before, :reported_by, :status, NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindParam(':district_id', $district_id, PDO::PARAM_INT);
        $stmt->bindParam(':subdistrict_id', $subdistrict_id, PDO::PARAM_INT);
        $stmt->bindParam(':target_type_id', $target_type_id, PDO::PARAM_INT);
        $stmt->bindParam(':house_name', $house_name, PDO::PARAM_STR);
        $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindParam(':details', $details, PDO::PARAM_STR);
        $stmt->bindParam(':image_before', $image_before_path, PDO::PARAM_STR);
        $stmt->bindParam(':reported_by', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        
        $stmt->execute();
        
        echo "
            <!DOCTYPE html><html><head><script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script></head><body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกข้อมูลสำเร็จ!',
                    text: 'พิกัดบ้านเป้าหมายถูกเพิ่มลงในระบบเรียบร้อยแล้ว',
                    showDenyButton: true,
                    confirmButtonText: 'แจ้งบ้านเป้าหมายเพิ่มเติม',
                    denyButtonText: 'ดูแผนที่รวม',
                    confirmButtonColor: '#2563eb',
                    denyButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'add_target.php';
                    } else {
                        window.location.href = 'map_dashboard.php';
                    }
                });
            </script>
            </body></html>
        ";
        
    } catch (\PDOException $e) {
        die("
            <!DOCTYPE html><html><head><script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script></head><body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'ระบบขัดข้อง!',
                    text: 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . addslashes($e->getMessage()) . "',
                    confirmButtonText: 'กลับไปหน้าฟอร์ม'
                }).then(() => {
                    window.location.href = 'add_target.php';
                });
            </script>
            </body></html>
        ");
    }

} else {
    header('Location: add_target.php');
    exit();
}
?>

