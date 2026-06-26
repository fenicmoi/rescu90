<?php
// edit_report.php
require_once 'auth.php';
requireRole([1, 3, 4]); // Admin, District Chief, Officer
require_once 'db_config.php';

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

if (!in_array($type, ['risk', 'target']) || !$id) {
    die("ข้อมูลไม่ถูกต้อง");
}

try {
    if ($type === 'risk') {
        $stmt = $pdo->prepare("SELECT * FROM risk_locations WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        $nameField = 'location_name';
        $typeField = 'risk_type_id';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM target_houses WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        $nameField = 'house_name';
        $typeField = 'target_type_id';
    }

    if (!$data) {
        die("ไม่พบข้อมูล");
    }

    // Permission Check: Officers/District Chiefs can only edit if it's their district or they reported it
    if ($user_role_id == 3 || $user_role_id == 4) {
        if ($data['district_id'] != $user_district_id && $data['reported_by'] != $_SESSION['user_id']) {
            die("ไม่มีสิทธิ์เข้าถึงข้อมูลนี้");
        }
    }

    // Fetch lists
    $stmtDistricts = $pdo->query("SELECT id, name_th FROM districts ORDER BY name_th ASC");
    $districts = $stmtDistricts->fetchAll();

    if ($type === 'risk') {
        $stmtTypes = $pdo->query("SELECT id, type_name FROM risk_types ORDER BY id ASC");
    } else {
        $stmtTypes = $pdo->query("SELECT id, type_name FROM target_types ORDER BY id ASC");
    }
    $types = $stmtTypes->fetchAll();

    // Fetch subdistricts of current district
    $stmtSub = $pdo->prepare("SELECT id, name_th FROM subdistricts WHERE district_id = ? ORDER BY name_th ASC");
    $stmtSub->execute([$data['district_id']]);
    $subdistricts = $stmtSub->fetchAll();

} catch (\PDOException $e) {
    die("Database Error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลการแจ้งเหตุ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style> body { font-family: 'Kanit', sans-serif; } </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <?php include 'navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">
                ✏️ แก้ไขข้อมูล: <?= $type === 'risk' ? 'จุดเสี่ยง' : 'บ้านเป้าหมาย' ?>
            </h1>
        </div>

        <form action="save_edit_report.php" method="POST" id="editForm" class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Left: Form -->
                <div class="lg:col-span-2 flex flex-col gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ชื่อสถานที่/เป้าหมาย <span class="text-red-500">*</span></label>
                        <input type="text" name="location_name" required value="<?= htmlspecialchars($data[$nameField]) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">ประเภท <span class="text-red-500">*</span></label>
                        <select name="type_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <?php foreach ($types as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $data[$typeField] == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['type_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">วันที่เกิดเหตุ / วันที่พบเห็น <span class="text-red-500">*</span></label>
                        <input type="date" name="incident_date" required value="<?= htmlspecialchars($data['incident_date'] ?? date('Y-m-d')) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">รายละเอียดเพิ่มเติม</label>
                        <textarea name="details" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200"><?= htmlspecialchars($data['details'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-blue-700">🛡️ มาตรการเฝ้าระวังและป้องกันเชิงรุก</label>
                        <textarea name="preventive_measures" rows="3" placeholder="ระบุมาตรการที่ได้ดำเนินการ เช่น ส่งสายตรวจลงพื้นที่, ตั้งด่าน, ประสานผู้นำชุมชน..." class="mt-1 block w-full rounded-md border-blue-300 bg-blue-50 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200"><?= htmlspecialchars($data['preventive_measures'] ?? '') ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">อำเภอ <span class="text-red-500">*</span></label>
                            <select name="district_id" id="district_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= $data['district_id'] == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name_th']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ตำบล <span class="text-red-500">*</span></label>
                            <select name="subdistrict_id" id="subdistrict_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                                <?php foreach ($subdistricts as $sd): ?>
                                    <option value="<?= $sd['id'] ?>" <?= $data['subdistrict_id'] == $sd['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sd['name_th']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded border border-gray-200">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">ละติจูด <span class="text-red-500">*</span></label>
                            <input type="text" id="latitude" name="latitude" value="<?= htmlspecialchars($data['latitude']) ?>" required readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm p-2 border text-sm text-gray-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">ลองจิจูด <span class="text-red-500">*</span></label>
                            <input type="text" id="longitude" name="longitude" value="<?= htmlspecialchars($data['longitude']) ?>" required readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm p-2 border text-sm text-gray-600">
                        </div>
                    </div>

                    <div class="mt-2 text-xs text-gray-500">
                        * หมายเหตุ: คุณสามารถแก้ไขพิกัดได้โดยการคลิกหรือลากหมุดบนแผนที่ทางด้านขวา
                    </div>
                </div>

                <!-- Right: Map -->
                <div class="lg:col-span-3 h-[400px] md:h-[500px] relative rounded-md overflow-hidden border border-gray-300 shadow-sm">
                    <div id="map" class="w-full h-full z-10"></div>
                </div>
            </div>

            <div class="mt-8 pt-5 border-t border-gray-200 flex justify-end gap-3">
                <a href="my_reports.php" class="px-5 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">ยกเลิก</a>
                <button type="submit" class="px-5 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const initialLat = <?= json_encode((float)$data['latitude']) ?>;
            const initialLng = <?= json_encode((float)$data['longitude']) ?>;

            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 });
            const googleStreets = L.tileLayer('http://{s}.google.com/vt?lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3']});
            const googleSatellite = L.tileLayer('http://{s}.google.com/vt?lyrs=s,h&x={x}&y={y}&z={z}', { maxZoom: 20, subdomains:['mt0','mt1','mt2','mt3']});

            const map = L.map('map', {
                center: [initialLat, initialLng],
                zoom: 16,
                layers: [googleStreets]
            });

            L.control.layers({
                "Google Maps (แผนที่ถนน)": googleStreets,
                "Google Satellite (ดาวเทียม)": googleSatellite,
                "OpenStreetMap": osm
            }).addTo(map);

            L.Control.geocoder({ defaultMarkGeocode: false, placeholder: "ค้นหาสถานที่..." })
            .on('markgeocode', function(e) {
                map.flyTo(e.geocode.center, 16);
            }).addTo(map);

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
</body>
</html>
