<?php
// update_status.php
require_once 'auth.php';
require_once 'db_config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$type = $_POST['type'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$new_status = $_POST['status'] ?? '';

if (!in_array($type, ['risk', 'target']) || $id <= 0 || !in_array($new_status, ['active', 'resolved'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

try {
    // Permission Check: 
    // Admin (1) can update anything
    // District Chief (3) can update anything in their district
    // Officer (4) can update only their own
    $table = ($type === 'risk') ? 'risk_locations' : 'target_houses';
    
    // Check ownership/permission
    $stmtCheck = $pdo->prepare("SELECT district_id, reported_by FROM {$table} WHERE id = :id");
    $stmtCheck->execute([':id' => $id]);
    $record = $stmtCheck->fetch();

    if (!$record) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Record not found']);
        exit;
    }

    $can_update = false;
    if ($user_role_id == 1) { // Admin
        $can_update = true;
    } elseif ($user_role_id == 3 && $record['district_id'] == $user_district_id) { // District Chief
        $can_update = true;
    } elseif ($user_role_id == 4 && $record['reported_by'] == $user_id) { // Officer
        $can_update = true;
    }

    if (!$can_update) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit;
    }

    // File upload handling for image_after
    $image_after_path = null;
    if ($new_status === 'resolved' && isset($_FILES['image_after']) && $_FILES['image_after']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image_after']['tmp_name'];
        $fileName = $_FILES['image_after']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . "after" . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_after_path = $newFileName;
            }
        }
    }

    // Perform Update
    if ($image_after_path) {
        $stmtUpdate = $pdo->prepare("UPDATE {$table} SET status = :status, image_after = :image_after WHERE id = :id");
        $stmtUpdate->execute([':status' => $new_status, ':image_after' => $image_after_path, ':id' => $id]);
    } else {
        $stmtUpdate = $pdo->prepare("UPDATE {$table} SET status = :status WHERE id = :id");
        $stmtUpdate->execute([':status' => $new_status, ':id' => $id]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Status updated successfully']);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
