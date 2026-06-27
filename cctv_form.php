<?php
require_once 'auth.php';
requireRole([1, 2, 3, 4]); // Admins and Officers

require_once 'db_config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cctv = null;
$action = "เพิ่มกล้อง CCTV";

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM cctv_locations WHERE id = ?");
    $stmt->execute([$id]);
    $cctv = $stmt->fetch();
    
    if (!$cctv) {
        die("ไม่พบข้อมูลกล้อง CCTV");
    }
    $action = "แก้ไขข้อมูลกล้อง CCTV";
}

// Fetch Districts
$districts = [];
try {
    $stmtD = $pdo->query("SELECT id, name_th FROM districts ORDER BY name_th ASC");
    $districts = $stmtD->fetchAll();
} catch (PDOException $e) { }

// Fetch Police Stations
$police_stations = [];
try {
    $stmtPS = $pdo->query("SELECT id, station_name FROM police_stations ORDER BY station_name ASC");
    $police_stations = $stmtPS->fetchAll();
} catch (PDOException $e) { }

// Fetch Camera Types
$camera_types = [];
try {
    $stmtCT = $pdo->query("SELECT id, type_name FROM camera_types ORDER BY type_name ASC");
    $camera_types = $stmtCT->fetchAll();
} catch (PDOException $e) { }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action ?> - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        body { font-family: 'Kanit', sans-serif; }
        #map { height: 400px; border-radius: 0.5rem; z-index: 10; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">

    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-6 lg:p-8 max-w-4xl mx-auto w-full">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">🎥 <?= $action ?></h1>
            <a href="manage_cctv.php" class="text-blue-600 hover:underline text-sm font-medium">← กลับไปหน้ารายการ</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="save_cctv.php" method="POST">
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="station_id" class="block text-sm font-medium text-gray-700 mb-1">รหัสสถานี/รหัสกล้อง <span class="text-red-500">*</span></label>
                        <input type="text" id="station_id" name="station_id" required 
                               class="w-full border-gray-300 rounded-md shadow-sm p-2.5 border focus:border-purple-500 focus:ring focus:ring-purple-200"
                               value="<?= $cctv ? htmlspecialchars($cctv['station_id']) : '' ?>">
                    </div>
                    <div>
                        <label for="camera_type_id" class="block text-sm font-medium text-gray-700 mb-1">ประเภทกล้อง</label>
                        <select id="camera_type_id" name="camera_type_id" class="w-full border-gray-300 rounded-md shadow-sm p-2.5 border focus:border-purple-500 focus:ring focus:ring-purple-200">
                            <option value="">-- เลือกประเภทกล้อง --</option>
                            <?php foreach ($camera_types as $ct): ?>
                                <option value="<?= $ct['id'] ?>" <?= ($cctv && $cctv['camera_type_id'] == $ct['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ct['type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="affiliation" class="block text-sm font-medium text-gray-700 mb-1">หน่วยงานต้นสังกัด <span class="text-red-500">*</span></label>
                        <input type="text" id="affiliation" name="affiliation" required placeholder="เช่น ภ.จว.พัทลุง, อบจ., เทศบาล"
                               class="w-full border-gray-300 rounded-md shadow-sm p-2.5 border focus:border-purple-500 focus:ring focus:ring-purple-200"
                               value="<?= $cctv ? htmlspecialchars($cctv['affiliation']) : '' ?>">
                    </div>
                    <div>
                        <label for="police_station_id" class="block text-sm font-medium text-gray-700 mb-1">สถานีตำรวจพื้นที่</label>
                        <select id="police_station_id" name="police_station_id" class="w-full border-gray-300 rounded-md shadow-sm p-2.5 border focus:border-purple-500 focus:ring focus:ring-purple-200">
                            <option value="">-- เลือกสถานีตำรวจ --</option>
                            <?php foreach ($police_stations as $ps): ?>
                                <option value="<?= $ps['id'] ?>" <?= ($cctv && $cctv['police_station_id'] == $ps['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ps['station_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="location_name" class="block text-sm font-medium text-gray-700 mb-1">จุดที่ติดตั้ง (รายละเอียดสถานที่) <span class="text-red-500">*</span></label>
                        <input type="text" id="location_name" name="location_name" required placeholder="เช่น หน้า รร.สตรีพัทลุง ถนน..."
                               class="w-full border-gray-300 rounded-md shadow-sm p-2.5 border focus:border-purple-500 focus:ring focus:ring-purple-200"
                               value="<?= $cctv ? htmlspecialchars($cctv['location_name']) : '' ?>">
                    </div>

                    <!-- Area Selection -->
                    <div>
                        <label for="district_id" class="block text-sm font-medium text-gray-700 mb-1">อำเภอ</label>
                        <select id="district_id" name="district_id" onchange="fetchSubdistricts(this.value)" class="w-full border-gray-300 rounded-md shadow-sm p-2.5 border focus:border-purple-500 focus:ring focus:ring-purple-200">
                            <option value="">-- เลือกอำเภอ --</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($cctv && $cctv['district_id'] == $d['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['name_th']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="subdistrict_id" class="block text-sm font-medium text-gray-700 mb-1">ตำบล</label>
                        <select id="subdistrict_id" name="subdistrict_id" class="w-full border-gray-300 rounded-md shadow-sm p-2.5 border focus:border-purple-500 focus:ring focus:ring-purple-200">
                            <option value="">-- กรุณาเลือกอำเภอก่อน --</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">พิกัดแผนที่ (คลิกเพื่อเลือกจุดตั้งกล้อง) <span class="text-red-500">*</span></label>
                    <div id="map" class="mb-3 border border-gray-300"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-xs font-medium text-gray-500">ละติจูด (Latitude)</label>
                            <input type="text" id="latitude" name="latitude" required readonly
                                   class="w-full bg-gray-50 border-gray-300 rounded-md shadow-sm p-2 border text-sm"
                                   value="<?= $cctv ? htmlspecialchars($cctv['latitude']) : '' ?>">
                        </div>
                        <div>
                            <label for="longitude" class="block text-xs font-medium text-gray-500">ลองจิจูด (Longitude)</label>
                            <input type="text" id="longitude" name="longitude" required readonly
                                   class="w-full bg-gray-50 border-gray-300 rounded-md shadow-sm p-2 border text-sm"
                                   value="<?= $cctv ? htmlspecialchars($cctv['longitude']) : '' ?>">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">คำแนะนำ: เลื่อนและคลิกบนแผนที่เพื่อปักหมุดพิกัดกล้อง</p>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <a href="manage_cctv.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2.5 px-6 rounded mr-3 transition">ยกเลิก</a>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-8 rounded shadow transition">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        // Fetch subdistricts based on district selection
        function fetchSubdistricts(districtId, selectedSubdistrict = null) {
            const subSelect = document.getElementById('subdistrict_id');
            subSelect.innerHTML = '<option value="">กำลังโหลด...</option>';
            
            if (!districtId) {
                subSelect.innerHTML = '<option value="">-- กรุณาเลือกอำเภอก่อน --</option>';
                return;
            }

            fetch('get_subdistricts.php?district_id=' + districtId)
                .then(response => response.json())
                .then(data => {
                    subSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>';
                    data.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub.id;
                        option.textContent = sub.name_th;
                        if (selectedSubdistrict && selectedSubdistrict == sub.id) {
                            option.selected = true;
                        }
                        subSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error fetching subdistricts:', error);
                    subSelect.innerHTML = '<option value="">-- เกิดข้อผิดพลาด --</option>';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Load subdistricts if editing
            <?php if ($cctv && $cctv['district_id']): ?>
                fetchSubdistricts(<?= $cctv['district_id'] ?>, <?= $cctv['subdistrict_id'] ? $cctv['subdistrict_id'] : 'null' ?>);
            <?php endif; ?>

            // Default center: Phatthalung City
            let initialLat = 7.616667;
            let initialLng = 100.083333;
            let zoomLevel = 10;
            let hasExistingMarker = false;

            <?php if($cctv && $cctv['latitude'] && $cctv['longitude']): ?>
                initialLat = <?= $cctv['latitude'] ?>;
                initialLng = <?= $cctv['longitude'] ?>;
                zoomLevel = 16;
                hasExistingMarker = true;
            <?php endif; ?>

            const map = L.map('map').setView([initialLat, initialLng], zoomLevel);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            let marker;
            
            const cctvIcon = L.divIcon({
                className: '',
                html: `<div style="font-size: 16px; background: white; border-radius: 50%; width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.3); border: 2px solid #9333ea;">🎥</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });

            if (hasExistingMarker) {
                marker = L.marker([initialLat, initialLng], { icon: cctvIcon }).addTo(map);
            }

            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                if (marker) {
                    map.removeLayer(marker);
                }
                
                marker = L.marker([lat, lng], { icon: cctvIcon }).addTo(map);

                document.getElementById('latitude').value = lat.toFixed(7);
                document.getElementById('longitude').value = lng.toFixed(7);
            });
            
            // Get user current location if no existing marker
            if (!hasExistingMarker && navigator.geolocation) {
                const locationBtn = L.control({position: 'topright'});
                locationBtn.onAdd = function (map) {
                    const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
                    div.innerHTML = '<button type="button" class="bg-white p-2 text-xl hover:bg-gray-100" title="พิกัดปัจจุบันของคุณ">📍</button>';
                    div.style.backgroundColor = 'white';
                    div.style.cursor = 'pointer';
                    
                    div.onclick = function(e) {
                        e.preventDefault();
                        navigator.geolocation.getCurrentPosition(function(position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            map.flyTo([lat, lng], 16);
                            
                            if (marker) { map.removeLayer(marker); }
                            marker = L.marker([lat, lng], { icon: cctvIcon }).addTo(map);
                            
                            document.getElementById('latitude').value = lat.toFixed(7);
                            document.getElementById('longitude').value = lng.toFixed(7);
                        });
                    }
                    return div;
                };
                locationBtn.addTo(map);
            }
        });
    </script>
</body>
</html>
