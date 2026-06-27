<?php
// setup_police_stations.php
$_SERVER['HTTP_HOST'] = '127.0.0.1'; // For CLI
require 'db_config.php';

try {
    $pdo->beginTransaction();

    // 1. Create table `police_stations`
    $pdo->exec("CREATE TABLE IF NOT EXISTS police_stations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        station_name VARCHAR(150) NOT NULL,
        district_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_district_id (district_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "1. Created police_stations table.\n";

    // 2. Insert mapped police stations
    $mapping = [
        'สภ.กงหรา' => 2,
        'สภ.เขาชัยสน' => 5,
        'สภ.ควนขนุน' => 3,
        'สภ.โคกชะงาย' => 1,
        'สภ.ตะโหมด' => 4,
        'สภ.ทะเลน้อย' => 3,
        'สภ.นาขยาด' => 3,
        'สภ.บางแก้ว' => 9,
        'สภ.ปากพะยูน' => 6,
        'สภ.เกาะนางคำ' => 6,
        'สภ.ป่าบอน' => 8,
        'สภ.ป่าพะยอม' => 10,
        'สภ.เมืองพัทลุง' => 1,
        'สภ.ลำปำ' => 1,
        'สภ.ศรีนครินทร์' => 11,
        'สภ.ศรีบรรพต' => 7
    ];

    $stmtInsertStation = $pdo->prepare("INSERT INTO police_stations (station_name, district_id) VALUES (?, ?)");
    foreach ($mapping as $station => $district_id) {
        // check if exists
        $stmtCheck = $pdo->prepare("SELECT id FROM police_stations WHERE station_name = ?");
        $stmtCheck->execute([$station]);
        if (!$stmtCheck->fetch()) {
            $stmtInsertStation->execute([$station, $district_id]);
        }
    }
    echo "2. Inserted police stations data.\n";

    // 3. Add `police_station_id` to `cctv_locations`
    try {
        $pdo->exec("ALTER TABLE cctv_locations ADD COLUMN police_station_id INT NULL AFTER affiliation");
        echo "3. Added police_station_id to cctv_locations.\n";
    } catch (PDOException $e) {
        // Column might already exist
        echo "3. (Column police_station_id might already exist or error: " . $e->getMessage() . ")\n";
    }

    // 4. Update `police_station_id` based on `police_station` string
    $pdo->exec("
        UPDATE cctv_locations c
        JOIN police_stations p ON c.police_station = p.station_name
        SET c.police_station_id = p.id
    ");
    echo "4. Updated cctv_locations with police_station_id.\n";

    // 5. Drop `police_station` string column
    try {
        $pdo->exec("ALTER TABLE cctv_locations DROP COLUMN police_station");
        echo "5. Dropped police_station string column.\n";
    } catch (PDOException $e) {
        echo "5. (Column police_station might already be dropped or error: " . $e->getMessage() . ")\n";
    }

    // Add Menu item
    $stmtMenu = $pdo->prepare("SELECT id FROM menus WHERE title = 'ตั้งค่าระบบ' AND parent_id IS NULL");
    $stmtMenu->execute();
    $parent = $stmtMenu->fetch();
    
    if ($parent) {
        $stmtCheckMenu = $pdo->prepare("SELECT id FROM menus WHERE title = 'จัดการสถานีตำรวจ' AND parent_id = ?");
        $stmtCheckMenu->execute([$parent['id']]);
        if (!$stmtCheckMenu->fetch()) {
            $stmtInsertMenu = $pdo->prepare("INSERT INTO menus (menu_type, title, url, parent_id, order_num) VALUES (?, ?, ?, ?, ?)");
            $stmtInsertMenu->execute(['backend', 'จัดการสถานีตำรวจ', 'manage_police_stations.php', $parent['id'], 6]);
            echo "6. Menu item added.\n";
        }
    }

    $pdo->commit();
    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Migration failed: " . $e->getMessage();
}
