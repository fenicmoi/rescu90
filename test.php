<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1';
require 'db_config.php';
$stmt = $pdo->query('SELECT police_station_id, station_id, COUNT(*) as cnt FROM cctv_locations GROUP BY police_station_id, station_id');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
