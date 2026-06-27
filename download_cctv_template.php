<?php
require_once 'auth.php';
requireRole([1, 2, 3, 4]);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=cctv_template.csv');

$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Excel compatibility
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Write Headers
fputcsv($output, [
    'รหัสสถานี (Station ID)',
    'สังกัด (Affiliation)',
    'สถานีตำรวจพื้นที่ (Police Station)',
    'ประเภทกล้อง (Camera Type)',
    'จุดที่ติดตั้ง (Location Name)',
    'ละติจูด (Latitude)',
    'ลองจิจูด (Longitude)'
]);

// Write Example Row
fputcsv($output, [
    'CCTV-001',
    'ภ.จว.พัทลุง',
    'สภ.เมืองพัทลุง',
    'IP Camera',
    'สี่แยกเอเชียพัทลุง',
    '7.616667',
    '100.083333'
]);

fclose($output);
exit;
?>
