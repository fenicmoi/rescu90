<?php
require_once 'db_config.php';

try {
    $pdo->exec("SET NAMES utf8mb4");

    // 1. Add columns if not exist
    try {
        $pdo->exec("ALTER TABLE `subdistricts` ADD COLUMN `latitude` DECIMAL(10,8) DEFAULT NULL");
        $pdo->exec("ALTER TABLE `subdistricts` ADD COLUMN `longitude` DECIMAL(11,8) DEFAULT NULL");
    } catch (\PDOException $e) {
        // Columns might already exist, ignore
    }

    // 2. Fetch districts to get their approximate centers
    // Since we hardcoded district centers in JS before, let's use the same here.
    $districtCenters = [
        1 => ['lat' => 7.616667, 'lng' => 100.083333], // เมืองพัทลุง
        2 => ['lat' => 7.433333, 'lng' => 99.950000],  // กงหรา
        3 => ['lat' => 7.733333, 'lng' => 100.016667], // ควนขนุน
        4 => ['lat' => 7.333333, 'lng' => 100.083333], // ตะโหมด
        5 => ['lat' => 7.450000, 'lng' => 100.133333], // เขาชัยสน
        6 => ['lat' => 7.350000, 'lng' => 100.316667], // ปากพะยูน
        7 => ['lat' => 7.650000, 'lng' => 99.883333],  // ศรีบรรพต
        8 => ['lat' => 7.266667, 'lng' => 100.166667], // ป่าบอน
        9 => ['lat' => 7.433333, 'lng' => 100.183333], // บางแก้ว
        10 => ['lat' => 7.850000, 'lng' => 99.933333], // ป่าพะยอม
        11 => ['lat' => 7.550000, 'lng' => 99.950000]  // ศรีนครินทร์
    ];

    // 3. Fetch all subdistricts
    $stmt = $pdo->query("SELECT id, district_id FROM subdistricts");
    $subdistricts = $stmt->fetchAll();

    $stmtUpdate = $pdo->prepare("UPDATE subdistricts SET latitude = :lat, longitude = :lng WHERE id = :id");

    foreach ($subdistricts as $sd) {
        $dId = $sd['district_id'];
        if (isset($districtCenters[$dId])) {
            $baseLat = $districtCenters[$dId]['lat'];
            $baseLng = $districtCenters[$dId]['lng'];
            
            // Add a small random offset (approx +/- 2-3 km) to simulate real tambon locations
            $offsetLat = (mt_rand(-300, 300) / 10000); 
            $offsetLng = (mt_rand(-300, 300) / 10000);
            
            $stmtUpdate->execute([
                ':lat' => $baseLat + $offsetLat,
                ':lng' => $baseLng + $offsetLng,
                ':id' => $sd['id']
            ]);
        }
    }

    echo "Tambon coordinates updated successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
