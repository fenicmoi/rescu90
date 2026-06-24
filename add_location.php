<?php
// add_location.php
// หน้าฟอร์มสำหรับเจ้าหน้าที่เพื่อเพิ่มจุดเสี่ยงใหม่

require_once 'auth.php';
requireRole([1, 3, 4]); // Admin, District Chief, Officer
require_once 'db_config.php';

// ดึงข้อมูลอำเภอและประเภทความเสี่ยงมาทำ Dropdown
try {
    // กรองอำเภอเฉพาะที่ตนเองสังกัด
    if (!empty($user_district_id)) {
        $stmtDistricts = $pdo->prepare("SELECT id, name_th FROM districts WHERE id = ? ORDER BY name_th ASC");
        $stmtDistricts->execute([$user_district_id]);
    } else {
        $stmtDistricts = $pdo->query("SELECT id, name_th FROM districts ORDER BY name_th ASC");
    }
    $districts = $stmtDistricts->fetchAll();

    $stmtRiskTypes = $pdo->query("SELECT id, type_name FROM risk_types ORDER BY id ASC");
    $riskTypes = $stmtRiskTypes->fetchAll();
} catch (\PDOException $e) {
    die("เกิดข้อผิดพลาดในการเตรียมข้อมูลฟอร์ม: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลจุดเสี่ยง - ศูนย์ข้อมูลอัจฉริยะพัทลุงปลอดภัย</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Leaflet Geocoder CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- Google Fonts (Kanit) -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
    </style>
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1e40af">
    <link rel="apple-touch-icon" href="icons/icon-192.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js');
            });
        }
    </script>
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">เพิ่มจุดเสี่ยงเชิงพื้นที่</h1>
            <p class="mt-2 text-sm text-gray-600">สำหรับเจ้าหน้าที่ระดับอำเภอในการรายงานพิกัดจุดเสี่ยงเข้าสู่ระบบศูนย์กลาง</p>
        </div>

        <form action="save_location.php" method="POST" enctype="multipart/form-data" id="locationForm" class="bg-white p-6 md:p-8 rounded-lg shadow-md border border-gray-200">
            
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                
                <!-- Left Column: Form Fields (40%) -->
                <div class="space-y-5 lg:col-span-2">
                    
                    <div>
                        <label for="location_name" class="block text-sm font-medium text-gray-700">ชื่อสถานที่ / ชุมชน <span class="text-red-500">*</span></label>
                        <input type="text" id="location_name" name="location_name" required placeholder="เช่น ศาลาริมน้ำ, ซอยเปลี่ยวหลังตลาด..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>

                    <div>
                        <label for="district_id" class="block text-sm font-medium text-gray-700">อำเภอที่รับผิดชอบ <span class="text-red-500">*</span></label>
                        <select id="district_id" name="district_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">-- เลือกอำเภอ --</option>
                            <?php foreach($districts as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name_th']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="subdistrict_id" class="block text-sm font-medium text-gray-700">ตำบล <span class="text-red-500">*</span></label>
                        <select id="subdistrict_id" name="subdistrict_id" required disabled
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border bg-gray-50 focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">-- กรุณาเลือกอำเภอก่อน --</option>
                        </select>
                    </div>

                    <div>
                        <label for="risk_type_id" class="block text-sm font-medium text-gray-700">ประเภทจุดเสี่ยง <span class="text-red-500">*</span></label>
                        <select id="risk_type_id" name="risk_type_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">-- เลือกประเภทความเสี่ยง --</option>
                            <?php foreach($riskTypes as $rt): ?>
                                <option value="<?= $rt['id'] ?>"><?= htmlspecialchars($rt['type_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="details" class="block text-sm font-medium text-gray-700">รายละเอียดเพิ่มเติม / พฤติการณ์</label>
                        <textarea id="details" name="details" rows="3" placeholder="ระบุช่วงเวลาที่เกิดเหตุ หรือรายละเอียดผู้รวมกลุ่ม..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200"></textarea>
                    </div>

                    <div>
                        <label for="image_before" class="block text-sm font-medium text-gray-700">รูปถ่ายสถานที่จริง / สภาพปัญหา (ถ้ามี)</label>
                        <input type="file" id="image_before" name="image_before" accept="image/*" capture="environment"
                            class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100 border border-gray-300 rounded-md p-1">
                        <p class="text-xs text-gray-500 mt-1">สามารถถ่ายจากกล้องมือถือได้โดยตรง (รองรับ JPG, PNG)</p>
                    </div>

                    <div class="p-4 bg-blue-50 rounded border border-blue-100">
                        <h4 class="text-sm font-semibold text-blue-800 mb-2">พิกัดทางภูมิศาสตร์</h4>
                        <p class="text-xs text-blue-600 mb-3">คำแนะนำ: กรุณาเลื่อนแผนที่ฝั่งขวาและคลิกบริเวณที่เกิดเหตุ ระบบจะดึงพิกัดมาใส่อัตโนมัติ</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="latitude" class="block text-xs font-medium text-gray-700">ละติจูด (Latitude) <span class="text-red-500">*</span></label>
                                <input type="text" id="latitude" name="latitude" required readonly 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm p-2 border text-sm text-gray-600 cursor-not-allowed">
                            </div>
                            <div>
                                <label for="longitude" class="block text-xs font-medium text-gray-700">ลองจิจูด (Longitude) <span class="text-red-500">*</span></label>
                                <input type="text" id="longitude" name="longitude" required readonly 
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm p-2 border text-sm text-gray-600 cursor-not-allowed">
                            </div>
                        </div>
                        <div id="coordinateError" class="text-xs text-red-500 mt-2 hidden">กรุณาระบุตำแหน่งบนแผนที่</div>
                    </div>
                </div>

                <!-- Right Column: Interactive Map (60%) -->
                <div class="h-[350px] md:h-[500px] lg:h-[calc(100vh-280px)] min-h-[350px] md:min-h-[500px] lg:col-span-3 relative rounded-md overflow-hidden border border-gray-300 shadow-sm">
                    <!-- Instruction Overlay -->
                    <div class="absolute top-2 left-1/2 transform -translate-x-1/2 z-[400] bg-white/90 px-3 py-1.5 rounded-full shadow-md text-sm font-semibold text-gray-800 pointer-events-none">
                        📍 คลิกพื้นที่บนแผนที่เพื่อปักหมุด
                    </div>
                    <!-- The Map Container -->
                    <div id="map" class="w-full h-full z-10"></div>
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end gap-3">
                <a href="index.php" class="px-5 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    กลับไปหน้าแผนที่
                </a>
                <button type="submit" class="px-5 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    บันทึกจุดเสี่ยง
                </button>
            </div>
        </form>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- Leaflet Geocoder JS -->
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial view to Phatthalung Province
            const initialLat = 7.616667;
            const initialLng = 100.083333;
            
            // Define Base Layers
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            });

            const googleStreets = L.tileLayer('http://{s}.google.com/vt?lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains:['mt0','mt1','mt2','mt3'],
                attribution: '&copy; Google Maps'
            });

            const googleSatellite = L.tileLayer('http://{s}.google.com/vt?lyrs=s,h&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains:['mt0','mt1','mt2','mt3'],
                attribution: '&copy; Google Satellite'
            });

            // Initialize the map centered around Phatthalung Province
            const map = L.map('map', {
                center: [initialLat, initialLng],
                zoom: 10,
                layers: [googleStreets] // Default layer
            });

            // Add Layer Control
            const baseMaps = {
                "Google Maps (แผนที่ถนน)": googleStreets,
                "Google Satellite (ดาวเทียม)": googleSatellite,
                "OpenStreetMap": osm
            };
            L.control.layers(baseMaps).addTo(map);

            // Add Search Control (Geocoder)
            L.Control.geocoder({
                defaultMarkGeocode: false,
                placeholder: "ค้นหาสถานที่..."
            })
            .on('markgeocode', function(e) {
                // Fly to the searched location
                map.flyTo(e.geocode.center, 16, { animate: true, duration: 1.5 });
            })
            .addTo(map);

            // พิกัดศูนย์กลางโดยประมาณของแต่ละอำเภอในจังหวัดพัทลุง
            const districtCoordinates = {
                1: [7.616667, 100.083333], // เมืองพัทลุง
                2: [7.433333, 99.950000],  // กงหรา
                3: [7.733333, 100.016667], // ควนขนุน
                4: [7.333333, 100.083333], // ตะโหมด
                5: [7.450000, 100.133333], // ควนขนุน (เขาชัยสน)
                6: [7.350000, 100.316667], // ปากพะยูน
                7: [7.650000, 99.883333],  // ศรีบรรพต
                8: [7.266667, 100.166667], // ป่าบอน
                9: [7.433333, 100.183333], // บางแก้ว
                10: [7.850000, 99.933333], // ป่าพะยอม
                11: [7.550000, 99.950000]  // ศรีนครินทร์
            };

            let districtBoundaryLayer = null;

            // ดักจับ Event เมื่อมีการเปลี่ยนอำเภอใน Dropdown
            document.getElementById('district_id').addEventListener('change', function(e) {
                const selectedId = e.target.value;
                const districtName = e.target.options[e.target.selectedIndex].text;
                const subdistrictSelect = document.getElementById('subdistrict_id');
                
                // Reset boundary
                if (districtBoundaryLayer) {
                    map.removeLayer(districtBoundaryLayer);
                    districtBoundaryLayer = null;
                }

                // Reset dropdown ตำบล
                subdistrictSelect.innerHTML = '<option value="">-- กำลังโหลดข้อมูล... --</option>';
                subdistrictSelect.disabled = true;
                
                if (selectedId) {
                    // 1. วาดเส้นขอบเขตอำเภอ
                    fetch(`https://nominatim.openstreetmap.org/search.php?q=อำเภอ${encodeURIComponent(districtName)}+จังหวัดพัทลุง&polygon_geojson=1&format=json`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                const result = data.find(item => item.geojson && (item.geojson.type === 'Polygon' || item.geojson.type === 'MultiPolygon'));
                                if (result) {
                                    districtBoundaryLayer = L.geoJSON(result.geojson, {
                                        style: { color: '#ef4444', weight: 3, opacity: 0.8, fillOpacity: 0.1 }
                                    }).addTo(map);
                                    map.fitBounds(districtBoundaryLayer.getBounds(), { padding: [20, 20] });
                                } else if (districtCoordinates[selectedId]) {
                                    map.flyTo(districtCoordinates[selectedId], 12, { animate: true, duration: 1.5 });
                                }
                            } else if (districtCoordinates[selectedId]) {
                                map.flyTo(districtCoordinates[selectedId], 12, { animate: true, duration: 1.5 });
                            }
                        }).catch(() => {
                            if (districtCoordinates[selectedId]) map.flyTo(districtCoordinates[selectedId], 12, { animate: true, duration: 1.5 });
                        });
                    
                    // 2. โหลดข้อมูลตำบล (AJAX)
                    fetch('api_get_subdistricts.php?district_id=' + selectedId)
                        .then(response => response.json())
                        .then(result => {
                            subdistrictSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>';
                            if (result.status === 'success') {
                                result.data.forEach(tambon => {
                                    const option = document.createElement('option');
                                    option.value = tambon.id;
                                    option.textContent = tambon.name_th;
                                    if (tambon.latitude && tambon.longitude) {
                                        option.dataset.lat = tambon.latitude;
                                        option.dataset.lng = tambon.longitude;
                                    }
                                    subdistrictSelect.appendChild(option);
                                });
                                subdistrictSelect.disabled = false;
                                subdistrictSelect.classList.remove('bg-gray-50');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching subdistricts:', error);
                            subdistrictSelect.innerHTML = '<option value="">-- เกิดข้อผิดพลาดในการโหลดข้อมูล --</option>';
                        });
                        
                } else {
                    // หากไม่ได้เลือกอำเภอ
                    map.flyTo([initialLat, initialLng], 10);
                    subdistrictSelect.innerHTML = '<option value="">-- กรุณาเลือกอำเภอก่อน --</option>';
                    subdistrictSelect.disabled = true;
                    subdistrictSelect.classList.add('bg-gray-50');
                }
            });

            // ดักจับ Event เมื่อมีการเปลี่ยนตำบลใน Dropdown
            document.getElementById('subdistrict_id').addEventListener('change', function(e) {
                const selectedOption = this.options[this.selectedIndex];
                const lat = selectedOption.dataset.lat;
                const lng = selectedOption.dataset.lng;
                
                if (lat && lng) {
                    // ให้แผนที่บินไปยังพิกัดของตำบลนั้น พร้อมซูมลึกขึ้น (ระดับ 14)
                    map.flyTo([parseFloat(lat), parseFloat(lng)], 14, {
                        animate: true,
                        duration: 1.5
                    });
                }
            });

            let currentMarker = null;

            // Handle map click
            map.on('click', function(e) {
                const lat = e.latlng.lat;
                const lng = e.latlng.lng;

                // Remove existing marker if any
                if (currentMarker) {
                    map.removeLayer(currentMarker);
                }

                // Add new marker
                currentMarker = L.marker([lat, lng]).addTo(map);

                // Update input fields with coordinates (truncated to 8 decimal places for consistency)
                document.getElementById('latitude').value = lat.toFixed(8);
                document.getElementById('longitude').value = lng.toFixed(8);
                
                // Hide error message if it was shown
                document.getElementById('coordinateError').classList.add('hidden');
            });

            // Form validation before submit
            document.getElementById('locationForm').addEventListener('submit', function(e) {
                const lat = document.getElementById('latitude').value;
                const lng = document.getElementById('longitude').value;
                
                if (!lat || !lng) {
                    e.preventDefault(); // Prevent submission
                    document.getElementById('coordinateError').classList.remove('hidden');
                    // Scroll to map to draw user's attention
                    document.getElementById('map').scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            });
        });
    </script>
    <!-- Footer -->
    <footer class="mt-auto py-4 text-center text-sm text-gray-500 bg-white border-t border-gray-200">
        พัฒนาโดย <span class="font-bold text-blue-700">จังหวัดพัทลุง</span>
    </footer>
</body>
</html>
