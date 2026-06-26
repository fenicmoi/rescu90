<?php
require_once 'db_config.php';

// Fetch options for forms
try {
    $districts = $pdo->query("SELECT * FROM districts ORDER BY id ASC")->fetchAll();
    $riskTypes = $pdo->query("SELECT * FROM risk_types ORDER BY id ASC")->fetchAll();
    $targetTypes = $pdo->query("SELECT * FROM target_types ORDER BY id ASC")->fetchAll();
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งพิกัดจุดเสี่ยง/บ้านเป้าหมาย - พัทลุงปลอดภัย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <!-- Leaflet Geocoder CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>body { font-family: 'Kanit', sans-serif; } #map { height: 300px; width: 100%; z-index: 10; }</style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
    
    <!-- Public Navbar -->
    <nav class="bg-blue-800 text-white shadow-lg shrink-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <a href="index.php" class="text-lg sm:text-xl font-bold tracking-wide truncate flex items-center gap-2 hover:text-blue-200 transition">
                        🛡️ CRIME MAP
                    </a>
                </div>
                <div class="flex items-center gap-2 lg:gap-4">
                    <a href="login.php" class="bg-white text-blue-800 hover:bg-gray-100 px-4 py-2 rounded-md text-sm font-bold shadow transition">เข้าสู่ระบบเจ้าหน้าที่</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow p-6 lg:p-8 max-w-7xl mx-auto w-full">
        <?php 
            session_start();
            $success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
            $error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
            unset($_SESSION['success_msg'], $_SESSION['error_msg']);
        ?>

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">ร่วมแจ้งพิกัดจุดเสี่ยง / บ้านเป้าหมาย</h1>
            <p class="text-gray-500 mt-1">ข้อมูลที่แจ้งจะถูกส่งให้เจ้าหน้าที่ตรวจสอบ (รอ Approve) ก่อนแสดงผลบนแผนที่สาธารณะ</p>
        </div>

        <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b">
                <button type="button" id="tab-risk" class="flex-1 py-4 text-center font-bold text-blue-700 border-b-2 border-blue-700 bg-blue-50 focus:outline-none">📍 แจ้งจุดเสี่ยง</button>
                <button type="button" id="tab-target" class="flex-1 py-4 text-center font-bold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 focus:outline-none">🏠 แจ้งบ้านเป้าหมาย</button>
            </div>

            <div class="p-6">
                <form action="public_save_report.php" method="POST" enctype="multipart/form-data" id="report-form">
                    <input type="hidden" name="report_type" id="report_type" value="risk">
                    
                    <!-- Step 1: Reporter Info -->
                    <div id="step-1" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">ขั้นตอนที่ 1: ข้อมูลผู้แจ้ง</h3>
                        <p class="text-sm text-red-600 mb-6 bg-red-50 p-3 rounded border border-red-100">
                            <strong>* ข้อมูลของท่านจะถูกปกปิดเป็นความลับ และเข้าถึงได้เฉพาะเจ้าหน้าที่รัฐที่เกี่ยวข้องเท่านั้น เพื่อความปลอดภัยของผู้แจ้ง</strong>
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ - นามสกุล <span class="text-red-500">*</span></label>
                                <input type="text" name="reporter_name" id="reporter_name" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="ระบุชื่อและนามสกุล">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทรศัพท์ติดต่อ <span class="text-red-500">*</span></label>
                                <input type="text" name="reporter_phone" id="reporter_phone" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="08xxxxxxxx">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button" id="btn-next" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-colors">
                                ถัดไป &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Location Details (Initially Hidden) -->
                    <div id="step-2" class="hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-6">
                            <!-- Left Column: Form Fields -->
                            <div class="flex flex-col gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อสถานที่ / จุดเกิดเหตุ <span class="text-red-500">*</span></label>
                                <input type="text" name="location_name" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="เช่น ศาลาหมู่บ้าน, ซอยเปลี่ยว...">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">อำเภอ <span class="text-red-500">*</span></label>
                                    <select name="district_id" id="district_id" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                                        <option value="">-- เลือกอำเภอ --</option>
                                        <?php foreach($districts as $d): ?>
                                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name_th']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ตำบล <span class="text-red-500">*</span></label>
                                    <select name="subdistrict_id" id="subdistrict_id" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200" disabled>
                                        <option value="">-- กรุณาเลือกอำเภอก่อน --</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Risk Type (Visible by default) -->
                            <div id="risk-type-container">
                                <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทความเสี่ยง <span class="text-red-500">*</span></label>
                                <select name="risk_type_id" id="risk_type_id" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">-- เลือกประเภทความเสี่ยง --</option>
                                    <?php foreach($riskTypes as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['type_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Target Type (Hidden by default) -->
                            <div id="target-type-container" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">ประเภทบ้านเป้าหมาย <span class="text-red-500">*</span></label>
                                <select name="target_type_id" id="target_type_id" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                                    <option value="">-- เลือกประเภทบ้านเป้าหมาย --</option>
                                    <?php foreach($targetTypes as $t): ?>
                                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['type_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">วันที่เกิดเหตุ / วันที่พบเห็น <span class="text-red-500">*</span></label>
                                <input type="date" name="incident_date" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200" value="<?= date('Y-m-d') ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียดพฤติการณ์ / ข้อมูลเพิ่มเติม</label>
                                <textarea name="details" rows="3" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200" placeholder="อธิบายรายละเอียด..."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">ภาพประกอบ (ถ้ามี)</label>
                                <input type="file" name="image" accept="image/*" class="w-full border-gray-300 rounded-md shadow-sm p-1.5 border bg-gray-50">
                            </div>
                        </div>

                        <!-- Right Column: Map -->
                        <div class="flex flex-col h-full bg-blue-50 p-4 rounded border border-blue-100">
                            <h4 class="text-sm font-semibold text-blue-800 mb-2">พิกัดทางภูมิศาสตร์</h4>
                            <p class="text-xs text-blue-600 mb-3">คำแนะนำ: กรุณาเลื่อนแผนที่และคลิกบริเวณที่เกิดเหตุ ระบบจะดึงพิกัดมาใส่อัตโนมัติ</p>
                            <div id="map" class="flex-grow rounded-md border border-gray-300 mb-3" style="min-height: 400px;"></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">ละติจูด (Latitude) <span class="text-red-500">*</span></label>
                                    <input type="text" name="latitude" id="latitude" readonly required class="mt-1 w-full bg-gray-100 border-gray-300 rounded-md shadow-sm p-2 border text-sm cursor-not-allowed">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">ลองจิจูด (Longitude) <span class="text-red-500">*</span></label>
                                    <input type="text" name="longitude" id="longitude" readonly required class="mt-1 w-full bg-gray-100 border-gray-300 rounded-md shadow-sm p-2 border text-sm cursor-not-allowed">
                                </div>
                            </div>
                            <button type="button" id="btn-current-location" class="mt-3 text-sm text-blue-600 font-medium hover:text-blue-800 flex items-center gap-1 justify-center w-full py-2 bg-white rounded border border-blue-200 shadow-sm">
                                📍 ใช้ตำแหน่งปัจจุบันของฉัน
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-4">
                        <button type="button" id="btn-back" class="w-1/3 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-colors text-lg">
                            &larr; ย้อนกลับ
                        </button>
                        <button type="submit" class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md transition-colors text-lg">
                            ส่งข้อมูลแจ้งเหตุ
                        </button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Leaflet Geocoder JS -->
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tabs logic
            const tabRisk = document.getElementById('tab-risk');
            const tabTarget = document.getElementById('tab-target');
            const reportType = document.getElementById('report-type');
            const riskContainer = document.getElementById('risk-type-container');
            const targetContainer = document.getElementById('target-type-container');
            const riskSelect = document.getElementById('risk_type_id');
            const targetSelect = document.getElementById('target_type_id');
            const typeInput = document.getElementById('report_type');

            tabRisk.addEventListener('click', () => {
                typeInput.value = 'risk';
                tabRisk.className = 'flex-1 py-4 text-center font-bold text-blue-700 border-b-2 border-blue-700 bg-blue-50 focus:outline-none';
                tabTarget.className = 'flex-1 py-4 text-center font-bold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 focus:outline-none';
                riskContainer.classList.remove('hidden');
                targetContainer.classList.add('hidden');
                riskSelect.required = true;
                targetSelect.required = false;
            });

            tabTarget.addEventListener('click', () => {
                typeInput.value = 'target';
                tabTarget.className = 'flex-1 py-4 text-center font-bold text-blue-700 border-b-2 border-blue-700 bg-blue-50 focus:outline-none';
                tabRisk.className = 'flex-1 py-4 text-center font-bold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 focus:outline-none';
                targetContainer.classList.remove('hidden');
                riskContainer.classList.add('hidden');
                targetSelect.required = true;
                riskSelect.required = false;
            });

            // Multi-step logic
            const btnNext = document.getElementById('btn-next');
            const btnBack = document.getElementById('btn-back');
            const step1 = document.getElementById('step-1');
            const step2 = document.getElementById('step-2');
            const reporterName = document.getElementById('reporter_name');
            const reporterPhone = document.getElementById('reporter_phone');

            btnNext.addEventListener('click', () => {
                if (reporterName.checkValidity() && reporterPhone.checkValidity()) {
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');
                    // Need to invalidate size since map might not load tiles properly if initialized in hidden div
                    setTimeout(() => { map.invalidateSize(); }, 100);
                } else {
                    reporterName.reportValidity();
                    reporterPhone.reportValidity();
                }
            });

            btnBack.addEventListener('click', () => {
                step2.classList.add('hidden');
                step1.classList.remove('hidden');
            });

            // Initialize required state
            riskSelect.required = true;

            // Map logic
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

            let map = L.map('map', {
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
                map.flyTo(e.geocode.center, 16, { animate: true, duration: 1.5 });
            })
            .addTo(map);

            let marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(map);

            marker.on('dragend', function (e) {
                document.getElementById('latitude').value = marker.getLatLng().lat.toFixed(8);
                document.getElementById('longitude').value = marker.getLatLng().lng.toFixed(8);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat.toFixed(8);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(8);
            });

            // Get Current Location
            document.getElementById('btn-current-location').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const newLatLng = new L.LatLng(lat, lng);
                        marker.setLatLng(newLatLng);
                        map.setView(newLatLng, 15);
                        document.getElementById('latitude').value = lat.toFixed(8);
                        document.getElementById('longitude').value = lng.toFixed(8);
                    }, function(error) {
                        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถดึงตำแหน่งปัจจุบันได้ กรุณาตรวจสอบการอนุญาตใช้งานพิกัดในเบราว์เซอร์', 'error');
                    });
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', 'เบราว์เซอร์ของคุณไม่รองรับการดึงตำแหน่งปัจจุบัน', 'error');
                }
            });

            // District change & map fly
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

            document.getElementById('district_id').addEventListener('change', function(e) {
                const districtId = this.value;
                const districtName = this.options[this.selectedIndex].text;
                const subSelect = document.getElementById('subdistrict_id');
                
                // Reset boundary
                if (districtBoundaryLayer) {
                    map.removeLayer(districtBoundaryLayer);
                    districtBoundaryLayer = null;
                }

                if (!districtId) {
                    subSelect.innerHTML = '<option value="">-- กรุณาเลือกอำเภอก่อน --</option>';
                    subSelect.disabled = true;
                    map.flyTo([initialLat, initialLng], 10);
                    return;
                }

                subSelect.disabled = false;
                subSelect.innerHTML = '<option value="">กำลังโหลด...</option>';

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
                            } else if (districtCoordinates[districtId]) {
                                map.flyTo(districtCoordinates[districtId], 12, { animate: true, duration: 1.5 });
                            }
                        } else if (districtCoordinates[districtId]) {
                            map.flyTo(districtCoordinates[districtId], 12, { animate: true, duration: 1.5 });
                        }
                    }).catch(() => {
                        if (districtCoordinates[districtId]) map.flyTo(districtCoordinates[districtId], 12, { animate: true, duration: 1.5 });
                    });

                // 2. Load subdistricts
                fetch(`api_get_subdistricts.php?district_id=${districtId}`)
                    .then(response => response.json())
                    .then(data => {
                        subSelect.innerHTML = '<option value="">-- เลือกตำบล --</option>';
                        data.data.forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub.id;
                            option.textContent = sub.name_th;
                            if (sub.latitude && sub.longitude) {
                                option.dataset.lat = sub.latitude;
                                option.dataset.lng = sub.longitude;
                            }
                            subSelect.appendChild(option);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        subSelect.innerHTML = '<option value="">เกิดข้อผิดพลาดในการโหลดข้อมูล</option>';
                    });
            });

            // Handle Subdistrict change
            document.getElementById('subdistrict_id').addEventListener('change', function(e) {
                const selectedOption = this.options[this.selectedIndex];
                const lat = selectedOption.dataset.lat;
                const lng = selectedOption.dataset.lng;
                
                if (lat && lng) {
                    map.flyTo([parseFloat(lat), parseFloat(lng)], 14, {
                        animate: true,
                        duration: 1.5
                    });
                }
            });
        });
    </script>
    <?php if ($success_msg): ?>
    <script>Swal.fire({icon: 'success', title: 'สำเร็จ', text: <?= json_encode($success_msg) ?>, confirmButtonColor: '#3085d6'});</script>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <script>Swal.fire({icon: 'error', title: 'เกิดข้อผิดพลาด', text: <?= json_encode($error_msg) ?>, confirmButtonColor: '#d33'});</script>
    <?php endif; ?>
</body>
</html>
