<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ศูนย์ข้อมูลอัจฉริยะพัทลุงปลอดภัย - แผนที่จุดเสี่ยง</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <!-- Google Fonts (Kanit) -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Kanit', sans-serif; }
        #map { height: 100vh; width: 100%; z-index: 10; }
        .sidebar { height: 100vh; overflow-y: auto; }
        
        /* Custom map marker style to use dynamic colors */
        .custom-marker {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.4);
        }
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
<body class="bg-gray-50 text-gray-800 flex flex-col h-screen overflow-hidden">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="flex flex-1 overflow-hidden relative">
        <!-- Sidebar (Left Column) -->
    <aside id="main-sidebar" class="sidebar absolute md:relative -translate-x-full md:translate-x-0 transition-transform duration-300 w-80 bg-white shadow-xl flex-shrink-0 z-[1000] flex flex-col h-full">
        <!-- Close button for mobile -->
        <button id="close-sidebar-btn" class="md:hidden absolute top-4 right-4 text-white bg-blue-800/80 hover:bg-blue-800 rounded-full p-1 z-50">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        
        <div class="p-6 bg-blue-700 text-white">
            <h1 class="text-xl font-bold pr-6">ศูนย์ข้อมูลอัจฉริยะพัทลุงปลอดภัย</h1>
            <p class="text-sm mt-1 text-blue-100">ระบบบริหารจัดการจุดเสี่ยงเชิงพื้นที่</p>
        </div>
        
        <div class="p-6 flex-grow overflow-y-auto custom-scrollbar">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">ตัวกรองข้อมูล</h2>
            
            <!-- District Filter -->
            <div class="mb-4">
                <label for="district-filter" class="block text-sm font-medium text-gray-700 mb-2">เลือกอำเภอ</label>
                <select id="district-filter" class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-gray-50 focus:border-blue-500 focus:ring focus:ring-blue-200">
                    <?php if (in_array($user_role_id, [3, 4])): ?>
                        <option value="all">พื้นที่รับผิดชอบของท่าน</option>
                    <?php else: ?>
                        <option value="all">ทั้งหมด ทุกอำเภอ</option>
                    <?php endif; ?>
                    <!-- Options populated by JS -->
                </select>
            </div>

            <!-- Subdistrict Filter -->
            <div class="mb-6">
                <label for="subdistrict-filter" class="block text-sm font-medium text-gray-700 mb-2">เลือกตำบล</label>
                <select id="subdistrict-filter" class="w-full border-gray-300 rounded-md shadow-sm p-2 border bg-gray-50 focus:border-blue-500 focus:ring focus:ring-blue-200" disabled>
                    <option value="all">-- กรุณาเลือกอำเภอก่อน --</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="mb-6 border-t pt-4 border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">สถานะการตรวจสอบ</label>
                <select id="filter_status" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200 bg-blue-50 text-blue-900 font-medium">
                    <option value="">📌 ทั้งหมด (All)</option>
                    <option value="active" selected>⚠️ ยังมีความเสี่ยง/รอตรวจสอบ</option>
                    <option value="resolved">✔️ ดำเนินการแล้ว/ปลอดภัย</option>
                </select>
            </div>
            
            <!-- Layer Toggle -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">แสดงข้อมูล</label>
                <div class="space-y-3 bg-blue-50 p-3 rounded border border-blue-100">
                    <div class="flex items-center">
                        <input type="checkbox" id="layer-risks" class="layer-toggle h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                        <label for="layer-risks" class="ml-2 block text-sm font-semibold text-blue-800 cursor-pointer">
                            📍 จุดเสี่ยงเชิงพื้นที่
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="layer-targets" class="layer-toggle h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                        <label for="layer-targets" class="ml-2 block text-sm font-semibold text-blue-800 cursor-pointer">
                            🏠 บ้านเป้าหมาย
                        </label>
                    </div>
                </div>
            </div>

            <!-- Risk Types Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ประเภทจุดเสี่ยง</label>
                <div id="risk-types-filter" class="space-y-3">
                    <!-- Checkboxes populated by JS -->
                </div>
            </div>

            <!-- Target Types Filter -->
            <div class="mt-6 border-t pt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">ประเภทบ้านเป้าหมาย</label>
                <div id="target-types-filter" class="space-y-3">
                    <!-- Checkboxes populated by JS -->
                </div>
            </div>
        </div>
        
        <div class="p-4 border-t bg-gray-50 text-xs text-center text-gray-500">
            <p>ระบบบริหารจัดการข้อมูล</p>
            <p class="mt-1 font-semibold text-blue-700">พัฒนาโดย จังหวัดพัทลุง</p>
        </div>
    </aside>

    <!-- Map Area (Right Column) -->
    <main class="flex-grow relative h-full w-full">
        <!-- Open Sidebar button for mobile -->
        <button id="open-sidebar-btn" class="md:hidden absolute top-20 right-4 z-[400] bg-white text-blue-700 p-2.5 rounded-full shadow-lg border border-gray-200 flex items-center justify-center font-medium hover:bg-gray-50 transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        </button>
        <div id="map" class="w-full h-full z-10"></div>
    </main>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar Toggle for Mobile
            const sidebar = document.getElementById('main-sidebar');
            const openBtn = document.getElementById('open-sidebar-btn');
            const closeBtn = document.getElementById('close-sidebar-btn');
            
            if (openBtn && closeBtn && sidebar) {
                openBtn.addEventListener('click', () => {
                    sidebar.classList.remove('-translate-x-full');
                });
                closeBtn.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                });
            }

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
                center: [7.616667, 100.083333],
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

            // พิกัดศูนย์กลางอำเภอ (ประมาณการ) เพื่อให้แผนที่ซูมไปได้
            const districtCoordinates = {
                '1': [7.616667, 100.083333], // เมืองพัทลุง
                '2': [7.433333, 99.950000],  // กงหรา
                '3': [7.733333, 100.016667], // ควนขนุน
                '4': [7.333333, 100.083333], // ตะโหมด
                '5': [7.450000, 100.133333], // เขาชัยสน
                '6': [7.350000, 100.316667], // ปากพะยูน
                '7': [7.650000, 99.883333],  // ศรีบรรพต
                '8': [7.266667, 100.166667], // ป่าบอน
                '9': [7.433333, 100.183333], // บางแก้ว
                '10': [7.850000, 99.933333], // ป่าพะยอม
                '11': [7.550000, 99.950000]  // ศรีนครินทร์
            };

            let allLocations = [];
            let allTargets = [];
            let markersLayer = L.layerGroup().addTo(map);
            let targetsLayer = L.layerGroup().addTo(map);

            function createCustomIcon(color, status, isTarget) {
                const finalColor = status === 'resolved' ? '#22c55e' : color;
                return L.divIcon({
                    className: '',
                    html: `<div class="custom-marker" style="background-color: ${finalColor};"></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
            }
            
            // Fetch data from our APIs
            Promise.all([
                fetch('api_get_locations.php').then(res => res.json()),
                fetch('api_get_targets.php').then(res => res.json())
            ])
            .then(([locationsResult, targetsResult]) => {
                if (locationsResult.status === 'success' && targetsResult.status === 'success') {
                    allLocations = locationsResult.data.locations;
                    allTargets = targetsResult.data.locations;
                    
                    populateFilters(locationsResult.data.districts, locationsResult.data.risk_types, targetsResult.data.target_types);
                    applyFilters();
                } else {
                    console.error('API Error');
                }
            })
            .catch(error => console.error('Fetch Error:', error));

            // Populate Sidebar Filters
            function populateFilters(districts, riskTypes, targetTypes) {
                // Populate District Dropdown
                const districtSelect = document.getElementById('district-filter');
                districts.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.id;
                    option.textContent = d.name_th;
                    districtSelect.appendChild(option);
                });
                
                // Add event listener to dropdown
                let districtBoundaryLayer = null;

                districtSelect.addEventListener('change', function(e) {
                    const selectedId = e.target.value;
                    const districtName = selectedId !== 'all' ? e.target.options[e.target.selectedIndex].text : '';
                    const subdistrictSelect = document.getElementById('subdistrict-filter');
                    
                    if (districtBoundaryLayer) {
                        map.removeLayer(districtBoundaryLayer);
                        districtBoundaryLayer = null;
                    }

                    // 1. Zoom to District and Draw Polygon Boundary
                    if (selectedId !== 'all') {
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
                    } else {
                        map.flyTo([7.616667, 100.083333], 10, { animate: true, duration: 1.5 }); // กลับมาภาพรวม
                    }

                    // 2. Fetch subdistricts for dropdown
                    subdistrictSelect.innerHTML = '<option value="all">-- โหลดข้อมูล... --</option>';
                    subdistrictSelect.disabled = true;

                    if (selectedId !== 'all') {
                        fetch('api_get_subdistricts.php?district_id=' + selectedId)
                            .then(response => response.json())
                            .then(result => {
                                subdistrictSelect.innerHTML = '<option value="all">ทั้งหมด ในอำเภอนี้</option>';
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
                            });
                    } else {
                        subdistrictSelect.innerHTML = '<option value="all">-- กรุณาเลือกอำเภอก่อน --</option>';
                        subdistrictSelect.disabled = true;
                        subdistrictSelect.classList.add('bg-gray-50');
                    }
                    
                    // 3. Apply Filters
                    applyFilters();
                });

                // Subdistrict Dropdown Event Listener
                const subdistrictSelect = document.getElementById('subdistrict-filter');
                subdistrictSelect.addEventListener('change', function(e) {
                    const selectedOption = this.options[this.selectedIndex];
                    const lat = selectedOption.dataset.lat;
                    const lng = selectedOption.dataset.lng;
                    
                    if (lat && lng) {
                        map.flyTo([parseFloat(lat), parseFloat(lng)], 14, { animate: true, duration: 1.5 });
                    } else if (this.value === 'all') {
                        // ซูมกลับไปที่ระดับอำเภอ
                        const districtId = document.getElementById('district-filter').value;
                        if (districtId !== 'all' && districtCoordinates[districtId]) {
                            map.flyTo(districtCoordinates[districtId], 12, { animate: true, duration: 1.5 });
                        }
                    }
                    
                    applyFilters();
                });

                // Populate Risk Types Checkboxes
                const riskTypesContainer = document.getElementById('risk-types-filter');
                riskTypes.forEach(rt => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-center';
                    
                    wrapper.innerHTML = `
                        <input type="checkbox" id="risk_${rt.id}" value="${rt.id}" class="risk-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                        <span class="ml-3 h-3 w-3 rounded-full inline-block border border-gray-200" style="background-color: ${rt.marker_color};"></span>
                        <label for="risk_${rt.id}" class="ml-2 block text-sm text-gray-800 cursor-pointer">
                            ${rt.type_name}
                        </label>
                    `;
                    riskTypesContainer.appendChild(wrapper);
                });
                
                // Populate Target Types Checkboxes
                const targetTypesContainer = document.getElementById('target-types-filter');
                targetTypes.forEach(tt => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex items-center';
                    
                    wrapper.innerHTML = `
                        <input type="checkbox" id="target_${tt.id}" value="${tt.id}" class="target-checkbox h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500" checked>
                        <span class="ml-3 text-sm">🏠</span>
                        <label for="target_${tt.id}" class="ml-1 block text-sm cursor-pointer" style="color: ${tt.marker_color}; font-weight: 500;">
                            ${tt.type_name}
                        </label>
                    `;
                    targetTypesContainer.appendChild(wrapper);
                });
                
                // Add event listeners to checkboxes and toggles
                document.querySelectorAll('.risk-checkbox, .target-checkbox, .layer-toggle, #filter_status').forEach(cb => {
                    cb.addEventListener('change', applyFilters);
                });
            }

            // Filter logic
            function applyFilters() {
                const selectedDistrict = document.getElementById('district-filter').value;
                const selectedSubdistrict = document.getElementById('subdistrict-filter').value;
                const statusFilter = document.getElementById('filter_status').value;
                
                const showRisks = document.getElementById('layer-risks').checked;
                const showTargets = document.getElementById('layer-targets').checked;

                const checkedRiskTypes = Array.from(document.querySelectorAll('.risk-checkbox:checked')).map(cb => cb.value);
                const checkedTargetTypes = Array.from(document.querySelectorAll('.target-checkbox:checked')).map(cb => cb.value);
                
                // Render Risks
                if (showRisks) {
                    const filteredRisks = allLocations.filter(loc => {
                        const matchDistrict = (selectedDistrict === 'all') || (loc.district_id == selectedDistrict);
                        const matchSubdistrict = (selectedSubdistrict === 'all') || (loc.subdistrict_id == selectedSubdistrict);
                        const matchRiskType = checkedRiskTypes.includes(String(loc.risk_type_id));
                        const matchStatus = (statusFilter === '') || (loc.status === statusFilter);
                        return matchDistrict && matchSubdistrict && matchRiskType && matchStatus;
                    });
                    renderMarkers(filteredRisks);
                } else {
                    markersLayer.clearLayers();
                }

                // Render Targets
                let targetUrl = 'api_get_targets.php?';
                if (selectedDistrict !== 'all') targetUrl += 'district_id=' + selectedDistrict + '&';
                if (statusFilter) targetUrl += 'status=' + statusFilter + '&';

                if (showTargets) {
                    const filteredTargets = allTargets.filter(loc => {
                        const matchDistrict = (selectedDistrict === 'all') || (loc.district_id == selectedDistrict);
                        const matchSubdistrict = (selectedSubdistrict === 'all') || (loc.subdistrict_id == selectedSubdistrict);
                        const matchTargetType = checkedTargetTypes.includes(String(loc.target_type_id));
                        const matchStatus = (statusFilter === '') || (loc.status === statusFilter);
                        return matchDistrict && matchSubdistrict && matchTargetType && matchStatus;
                    });
                    renderTargetMarkers(filteredTargets);
                } else {
                    targetsLayer.clearLayers();
                }
            }

            // Render markers on the map
            function renderMarkers(locations) {
                markersLayer.clearLayers(); // Remove existing markers
                
                locations.forEach(loc => {
                    const customIcon = createCustomIcon(loc.marker_color || '#3b82f6', loc.status, false);
                    
                    // Create marker
                    const marker = L.marker([parseFloat(loc.latitude), parseFloat(loc.longitude)], { icon: customIcon });
                    
                    // Generate image tags if available
                    const imageBefore = loc.image_before ? `<div class="mt-2 flex-1"><p class="text-xs text-gray-500 mb-1">ภาพสถานที่:</p><img src="uploads/${loc.image_before}" class="w-full h-24 object-cover rounded shadow-sm cursor-pointer hover:opacity-80 transition" onclick="window.open(this.src)"></div>` : '';
                    const imageAfter = loc.image_after ? `<div class="mt-2 flex-1"><p class="text-xs text-gray-500 mb-1">หลังแก้ไข:</p><img src="uploads/${loc.image_after}" class="w-full h-24 object-cover rounded shadow-sm cursor-pointer border-2 border-green-500 hover:opacity-80 transition" onclick="window.open(this.src)"></div>` : '';
                    
                    // Bind Popup Content
                    const popupContent = `
                        <div class="font-sans min-w-[220px]">
                            <h3 class="font-bold text-lg mb-1" style="color: ${loc.marker_color};">${loc.type_name}</h3>
                            <p class="text-sm font-semibold text-gray-800 mb-1">📍 ${loc.location_name}</p>
                            <p class="text-sm text-gray-600 mb-2"><strong>ตำบล:</strong> ${loc.subdistrict_name || 'ไม่ระบุ'}, <strong>อำเภอ:</strong> ${loc.district_name || 'ไม่ระบุ'}</p>
                            ${loc.status === 'resolved' ? '<p class="text-sm text-green-700 bg-green-50 p-2 rounded font-bold mb-2">✔️ แก้ไขแล้ว/ปลอดภัย</p>' : '<p class="text-sm text-orange-700 bg-orange-50 p-2 rounded font-bold mb-2">⚠️ ยังมีความเสี่ยง</p>'}
                            <p class="text-sm text-gray-700 bg-gray-100 p-2 rounded mb-2">${loc.details || 'ไม่มีรายละเอียด'}</p>
                            <div class="flex gap-2">
                                ${imageBefore}
                                ${imageAfter}
                            </div>
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                    
                    // Add to layer group
                    markersLayer.addLayer(marker);
                });
            }

            // Render Target Markers on the map
            function renderTargetMarkers(locations) {
                targetsLayer.clearLayers(); // Remove existing markers
                
                locations.forEach(loc => {
                    const customIcon = createCustomIcon(loc.marker_color || '#ef4444', loc.status, true);
                    const marker = L.marker([parseFloat(loc.latitude), parseFloat(loc.longitude)], { icon: customIcon });
                    
                    // Generate image tags if available
                    const imageBefore = loc.image_before ? `<div class="mt-2 flex-1"><p class="text-xs text-gray-500 mb-1">ภาพเป้าหมาย:</p><img src="uploads/${loc.image_before}" class="w-full h-24 object-cover rounded shadow-sm cursor-pointer hover:opacity-80 transition" onclick="window.open(this.src)"></div>` : '';
                    const imageAfter = loc.image_after ? `<div class="mt-2 flex-1"><p class="text-xs text-gray-500 mb-1">ภาพหลักฐาน:</p><img src="uploads/${loc.image_after}" class="w-full h-24 object-cover rounded shadow-sm cursor-pointer border-2 border-green-500 hover:opacity-80 transition" onclick="window.open(this.src)"></div>` : '';

                    const popupContent = `
                        <div class="font-sans border-l-4 pl-3 min-w-[220px]" style="border-color: ${loc.status === 'resolved' ? '#22c55e' : loc.marker_color};">
                            <h3 class="font-bold text-lg mb-1" style="color: ${loc.status === 'resolved' ? '#22c55e' : loc.marker_color};">${loc.type_name}</h3>
                            <p class="text-sm font-semibold text-gray-800 mb-1">เป้าหมาย: ${loc.location_name}</p>
                            <p class="text-sm text-gray-600 mb-2"><strong>ตำบล:</strong> ${loc.subdistrict_name || 'ไม่ระบุ'}, <strong>อำเภอ:</strong> ${loc.district_name || 'ไม่ระบุ'}</p>
                            ${loc.status === 'resolved' ? '<p class="text-sm text-green-700 bg-green-50 p-2 rounded font-bold mb-2">✔️ ดำเนินการแล้ว</p>' : '<p class="text-sm text-red-700 bg-red-50 p-2 rounded font-bold mb-2">🚨 รอตรวจสอบ/จับกุม</p>'}
                            <p class="text-sm text-gray-700 bg-gray-100 p-2 rounded mb-2">พฤติการณ์: ${loc.details || 'ไม่มีรายละเอียด'}</p>
                            <div class="flex gap-2">
                                ${imageBefore}
                                ${imageAfter}
                            </div>
                        </div>
                    `;
                    marker.bindPopup(popupContent);
                    
                    targetsLayer.addLayer(marker);
                });
            }
        });
    </script>
</body>
</html>
