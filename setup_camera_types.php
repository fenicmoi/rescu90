<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1';
require 'db_config.php';

try {
    echo "Starting Camera Types Normalization Migration...\n";
    $pdo->beginTransaction();

    // 1. Create `camera_types` table
    echo "1. Creating camera_types table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS camera_types (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type_name VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 2. Insert Standardized Categories
    echo "2. Inserting Standardized Categories...\n";
    $standardCategories = [
        'IP Camera',
        'Analog Camera',
        'Digital Camera',
        'Infrared Camera',
        'General CCTV',
        'Unknown'
    ];

    $typeMap = []; // Maps standard category name to its new ID
    foreach ($standardCategories as $category) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO camera_types (type_name) VALUES (?)");
        $stmt->execute([$category]);
        
        $stmt = $pdo->prepare("SELECT id FROM camera_types WHERE type_name = ?");
        $stmt->execute([$category]);
        $typeMap[$category] = $stmt->fetchColumn();
    }

    // 3. Add `camera_type_id` to `cctv_locations`
    echo "3. Adding camera_type_id to cctv_locations...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM cctv_locations LIKE 'camera_type_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE cctv_locations ADD COLUMN camera_type_id INT NULL AFTER camera_type");
        $pdo->exec("ALTER TABLE cctv_locations ADD CONSTRAINT fk_camera_type FOREIGN KEY (camera_type_id) REFERENCES camera_types(id) ON DELETE RESTRICT");
    }

    // 4. Data Cleansing & Mapping
    echo "4. Migrating and Cleansing Data...\n";
    // Get all cameras
    $stmt = $pdo->query("SELECT id, camera_type FROM cctv_locations");
    $cameras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countUpdated = 0;
    foreach ($cameras as $camera) {
        $oldType = trim($camera['camera_type']);
        $lowerType = mb_strtolower($oldType, 'UTF-8');
        
        $newCategoryId = $typeMap['Unknown']; // Default

        if (empty($oldType) || $oldType == '.') {
            $newCategoryId = $typeMap['Unknown'];
        }
        // Match IP Camera
        elseif (preg_match('/ip|ไอพี|ไอ พี|wifi|lan|แลน/i', $lowerType)) {
            $newCategoryId = $typeMap['IP Camera'];
        }
        // Match Analog Camera
        elseif (preg_match('/analog|อนาล็อก|อานาล็อก|อนาล๊อก|อนาล๊อด|อนะล๊อด|dvr|dvc/i', $lowerType)) {
            $newCategoryId = $typeMap['Analog Camera'];
        }
        // Match Digital Camera
        elseif (preg_match('/digital|ดิจิตอล|ดิจิทอล/i', $lowerType)) {
            $newCategoryId = $typeMap['Digital Camera'];
        }
        // Match Infrared Camera
        elseif (preg_match('/infrared|อินฟราเรด|ฟราเรด/i', $lowerType)) {
            $newCategoryId = $typeMap['Infrared Camera'];
        }
        // Match General CCTV or specific brands/others
        elseif (preg_match('/cctv|วงจร|กล้อง|มาตรฐาน|บันทึกภาพ|วาตาช|panasonic|sony|samsung|pix|บันทึก/i', $lowerType)) {
            $newCategoryId = $typeMap['General CCTV'];
        }
        // Anything else (like location names put in camera type)
        else {
            // Some people put "โรงพยาบาล" in camera_type. Map these to Unknown or General.
            $newCategoryId = $typeMap['General CCTV'];
        }

        // Update record
        $updateStmt = $pdo->prepare("UPDATE cctv_locations SET camera_type_id = ? WHERE id = ?");
        $updateStmt->execute([$newCategoryId, $camera['id']]);
        $countUpdated++;
    }
    echo "   Mapped $countUpdated camera records.\n";

    // 5. Drop old column
    echo "5. Dropping old camera_type column...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM cctv_locations LIKE 'camera_type'");
    if ($stmt->fetch()) {
        $pdo->exec("ALTER TABLE cctv_locations DROP COLUMN camera_type");
    }

    $pdo->commit();
    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Migration failed: " . $e->getMessage() . "\n";
}
