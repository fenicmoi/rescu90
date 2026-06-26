<?php
// seed_mock_data.php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    // Get all districts
    $stmt = $pdo->query("SELECT id, name_th FROM districts");
    $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all subdistricts with their coordinates
    $stmt = $pdo->query("SELECT id, district_id, name_th, latitude, longitude FROM subdistricts WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
    $subdistricts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group subdistricts by district
    $subsByDist = [];
    foreach ($subdistricts as $sd) {
        $subsByDist[$sd['district_id']][] = $sd;
    }

    // Get all risk types
    $stmt = $pdo->query("SELECT id, type_name FROM risk_types");
    $riskTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $count = 0;

    foreach ($districts as $dist) {
        $distId = $dist['id'];
        if (!isset($subsByDist[$distId]) || empty($subsByDist[$distId])) {
            continue;
        }
        
        $numPoints = rand(5, 10);
        
        for ($i = 0; $i < $numPoints; $i++) {
            // Random subdistrict
            $sd = $subsByDist[$distId][array_rand($subsByDist[$distId])];
            $sdId = $sd['id'];
            
            // Random coordinate around subdistrict center (approx +/- 0.02 deg)
            $lat = $sd['latitude'] + (rand(-200, 200) / 10000);
            $lng = $sd['longitude'] + (rand(-200, 200) / 10000);
            
            // Random risk type
            $rt = $riskTypes[array_rand($riskTypes)];
            
            // Random status
            $statuses = ['active', 'resolved', 'pending'];
            $status = $statuses[array_rand($statuses)];
            
            // Random location name
            $locationName = "จุดเสี่ยงพื้นที่ " . $sd['name_th'] . " ม." . rand(1, 15);
            
            // Insert
            $stmt = $pdo->prepare("INSERT INTO risk_locations 
                (location_name, risk_type_id, district_id, subdistrict_id, details, latitude, longitude, status, reporter_name, preventive_measures) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $details = "พบปัญหา " . $rt['type_name'] . " บริเวณนี้ โปรดเฝ้าระวัง";
            $measures = $status === 'resolved' || rand(1, 10) > 5 ? "ได้สั่งการให้สายตรวจลงพื้นที่ตรวจสอบแล้ว" : null;

            $stmt->execute([
                $locationName,
                $rt['id'],
                $distId,
                $sdId,
                $details,
                $lat,
                $lng,
                $status,
                'Mock User',
                $measures
            ]);
            
            $count++;
        }
    }

    echo "Successfully inserted $count mock records into risk_locations.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
