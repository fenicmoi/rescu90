<?php
$host = '127.0.0.1';
$db   = '90day';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // 1. Create table
    $pdo->exec("CREATE TABLE IF NOT EXISTS cctv_locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        station_id VARCHAR(50),
        affiliation VARCHAR(100),
        police_station VARCHAR(100),
        camera_type VARCHAR(50),
        location_name VARCHAR(255),
        latitude VARCHAR(50),
        longitude VARCHAR(50)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table cctv_locations created.\n";

    $pdo->exec("TRUNCATE TABLE cctv_locations");

    // 2. Read CSV and insert
    $file = fopen('cctv_data.csv', 'r');
    if ($file) {
        $header = fgetcsv($file); // skip header
        $count = 0;
        
        $stmt = $pdo->prepare("INSERT INTO cctv_locations (station_id, affiliation, police_station, camera_type, location_name, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        while (($row = fgetcsv($file)) !== false) {
            // Check if row has enough columns and latitude is not empty
            if (count($row) >= 7 && !empty(trim($row[5]))) {
                // Convert TIS-620 to UTF-8
                $station_id = iconv('TIS-620', 'UTF-8', $row[0]);
                $affiliation = iconv('TIS-620', 'UTF-8', $row[1]);
                $police_station = iconv('TIS-620', 'UTF-8', $row[2]);
                $camera_type = iconv('TIS-620', 'UTF-8', $row[3]);
                $location_name = iconv('TIS-620', 'UTF-8', $row[4]);
                $latitude = iconv('TIS-620', 'UTF-8', $row[5]);
                $longitude = iconv('TIS-620', 'UTF-8', $row[6]);

                $stmt->execute([$station_id, $affiliation, $police_station, $camera_type, $location_name, $latitude, $longitude]);
                $count++;
            }
        }
        fclose($file);
        echo "Successfully imported $count CCTV locations.\n";
    } else {
        echo "Failed to open cctv_data.csv\n";
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
