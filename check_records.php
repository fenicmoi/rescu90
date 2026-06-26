<?php
require_once 'db_config.php';
$stmt = $pdo->query("SELECT r.id, r.location_name, r.district_id, d.name_th, r.reported_by FROM risk_locations r LEFT JOIN districts d ON r.district_id = d.id");
$risks = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Risks:\n";
print_r($risks);
