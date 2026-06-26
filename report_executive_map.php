<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานแผนที่ผู้บริหาร - CRIME MAP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Google Fonts (Kanit) -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .map-container { height: calc(100vh - 180px); min-height: 500px; }
        @media (max-width: 1024px) {
            .map-container { height: 400px; }
        }
        /* Custom map labels */
        .leaflet-tooltip-custom {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #1e40af;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            font-family: 'Kanit', sans-serif;
            font-weight: 600;
            color: #1e40af;
            padding: 4px 8px;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-4 lg:p-6 w-full max-w-[1600px] mx-auto">
        
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    รายงานสรุปแผนที่ผู้บริหาร (Interactive Map)
                </h1>
                <p class="text-gray-500 text-sm mt-1">คลิกที่อำเภอบนแผนที่เพื่อดูข้อมูลสถิติเฉพาะอำเภอนั้นๆ</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <button onclick="loadData('all')" id="btn-reset" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium transition hidden shadow-sm border border-gray-300">
                    แสดงข้อมูลภาพรวมทั้งหมด
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Column: Map -->
            <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col relative">
                <div class="bg-blue-800 text-white p-3 font-semibold text-center border-b border-blue-900">
                    แผนที่จังหวัดพัทลุง <span id="map-subtitle" class="text-blue-200 text-sm font-normal ml-1">(ภาพรวม)</span>
                </div>
                <div id="map" class="map-container w-full relative z-10"></div>
                <!-- Map Overlay Loading -->
                <div id="map-loading" class="absolute inset-0 bg-white/70 z-20 flex items-center justify-center hidden">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-800"></div>
                </div>
            </div>

            <!-- Right Column: Stats & Charts -->
            <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-6">
                
                <!-- Status Title -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800" id="stat-title">สถิติภาพรวมทุกอำเภอ</h2>
                        <p class="text-sm text-gray-500" id="stat-subtitle">ข้อมูลจุดเสี่ยงและบ้านเป้าหมายทั้งหมดในระบบ</p>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-sm">
                        <span class="text-sm font-medium text-blue-800 mb-1">จุดเสี่ยงรวม</span>
                        <span class="text-3xl font-bold text-blue-900" id="val-risk-total">-</span>
                    </div>
                    <div class="bg-green-50 border border-green-100 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-sm">
                        <span class="text-sm font-medium text-green-800 mb-1">แก้ไขแล้ว</span>
                        <span class="text-3xl font-bold text-green-700" id="val-risk-resolved">-</span>
                    </div>
                    <div class="bg-red-50 border border-red-100 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-sm">
                        <span class="text-sm font-medium text-red-800 mb-1">เป้าหมายรวม</span>
                        <span class="text-3xl font-bold text-red-900" id="val-target-total">-</span>
                    </div>
                    <div class="bg-teal-50 border border-teal-100 rounded-xl p-4 flex flex-col justify-center items-center text-center shadow-sm">
                        <span class="text-sm font-medium text-teal-800 mb-1">ตรวจสอบแล้ว</span>
                        <span class="text-3xl font-bold text-teal-700" id="val-target-resolved">-</span>
                    </div>
                </div>

                <!-- Charts Area -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 flex-grow">
                    <!-- Chart 1 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-l-4 border-blue-500 pl-2">ประเภทจุดเสี่ยง</h3>
                        <div class="relative flex-grow min-h-[250px] w-full flex justify-center items-center">
                            <canvas id="riskTypeChart"></canvas>
                            <div id="riskChartEmpty" class="absolute inset-0 flex items-center justify-center text-gray-400 hidden bg-white">ไม่มีข้อมูล</div>
                        </div>
                    </div>
                    <!-- Chart 2 -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 flex flex-col">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-l-4 border-red-500 pl-2">ประเภทบ้านเป้าหมาย</h3>
                        <div class="relative flex-grow min-h-[250px] w-full flex justify-center items-center">
                            <canvas id="targetTypeChart"></canvas>
                            <div id="targetChartEmpty" class="absolute inset-0 flex items-center justify-center text-gray-400 hidden bg-white">ไม่มีข้อมูล</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
        // Data & Variables
        let map;
        let districtMarkersLayer;
        let currentDistrictId = 'all';
        let riskChartInstance = null;
        let targetChartInstance = null;
        let isMapLoading = false;

        const districtsData = [
            { id: 1, name: 'เมืองพัทลุง', lat: 7.616667, lng: 100.083333 },
            { id: 2, name: 'กงหรา', lat: 7.433333, lng: 99.950000 },
            { id: 3, name: 'ควนขนุน', lat: 7.733333, lng: 100.016667 },
            { id: 4, name: 'ตะโหมด', lat: 7.333333, lng: 100.083333 },
            { id: 5, name: 'เขาชัยสน', lat: 7.450000, lng: 100.133333 },
            { id: 6, name: 'ปากพะยูน', lat: 7.350000, lng: 100.316667 },
            { id: 7, name: 'ศรีบรรพต', lat: 7.650000, lng: 99.883333 },
            { id: 8, name: 'ป่าบอน', lat: 7.266667, lng: 100.166667 },
            { id: 9, name: 'บางแก้ว', lat: 7.433333, lng: 100.183333 },
            { id: 10, name: 'ป่าพะยอม', lat: 7.850000, lng: 99.933333 },
            { id: 11, name: 'ศรีนครินทร์', lat: 7.550000, lng: 99.950000 }
        ];

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            loadData('all');
        });

        function initMap() {
            // Use Light map base for executive view
            const baseLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 20
            });

            map = L.map('map', {
                center: [7.55, 100.05], // Center of Phatthalung
                zoom: 10,
                layers: [baseLayer],
                zoomControl: false // Move zoom control if needed
            });
            L.control.zoom({ position: 'bottomright' }).addTo(map);

            districtMarkersLayer = L.layerGroup().addTo(map);
        }

        function loadData(districtId) {
            currentDistrictId = districtId;
            document.getElementById('map-loading').classList.remove('hidden');

            const url = districtId === 'all' ? 'api_dashboard.php' : 'api_dashboard.php?district_id=' + districtId;
            
            fetch(url)
                .then(res => res.json())
                .then(result => {
                    document.getElementById('map-loading').classList.add('hidden');
                    if (result.status === 'success') {
                        updateUI(result.data, districtId);
                    } else {
                        console.error('API Error:', result.message);
                    }
                })
                .catch(err => {
                    document.getElementById('map-loading').classList.add('hidden');
                    console.error('Fetch Error:', err);
                });
        }

        function updateUI(data, districtId) {
            // Update Text
            const districtObj = districtsData.find(d => d.id == districtId);
            const titleName = districtId === 'all' ? 'ทุกอำเภอ' : `อำเภอ${districtObj.name}`;
            
            document.getElementById('stat-title').innerText = `สถิติพื้นที่: ${titleName}`;
            document.getElementById('map-subtitle').innerText = `(${districtId === 'all' ? 'ภาพรวม' : districtObj.name})`;
            
            if (districtId === 'all') {
                document.getElementById('btn-reset').classList.add('hidden');
            } else {
                document.getElementById('btn-reset').classList.remove('hidden');
            }

            // Update Cards
            document.getElementById('val-risk-total').innerText = data.total || 0;
            document.getElementById('val-risk-resolved').innerText = data.resolved || 0;
            document.getElementById('val-target-total').innerText = data.target_total || 0;
            document.getElementById('val-target-resolved').innerText = data.target_resolved || 0;

            // Update Charts
            updateCharts(data.by_type, data.target_by_type);

            // Update Map Markers (only recalculate bubbles if we are in 'all' view)
            if (districtId === 'all') {
                renderMapBubbles(data.by_district, data.target_by_district);
                // map.flyTo([7.55, 100.05], 10, { animate: true, duration: 1 });
            } else if (districtObj) {
                // Focus on the clicked district (Disabled per user request)
                // map.flyTo([districtObj.lat, districtObj.lng], 12, { animate: true, duration: 1 });
                
                // Highlight the active circle (optional UI touch)
                highlightCircleMarker(districtId);
            }
        }

        function renderMapBubbles(riskDistrictStats, targetDistrictStats) {
            districtMarkersLayer.clearLayers();

            districtsData.forEach(d => {
                // Find stats for this district
                const rStat = riskDistrictStats.find(r => r.district_name === d.name) || { count: 0 };
                const tStat = targetDistrictStats.find(t => t.district_name === d.name) || { count: 0 };
                
                const totalPoints = parseInt(rStat.count) + parseInt(tStat.count);
                
                // Calculate radius based on data (min 8, max 30)
                let radius = 10;
                if (totalPoints > 0) {
                    radius = Math.min(35, Math.max(12, Math.sqrt(totalPoints) * 3));
                }

                // Determine color based on intensity
                let fillColor = '#3b82f6'; // default blue
                if (totalPoints > 50) fillColor = '#ef4444'; // red
                else if (totalPoints > 20) fillColor = '#f59e0b'; // amber

                // Create Circle Marker
                const circle = L.circleMarker([d.lat, d.lng], {
                    radius: radius,
                    fillColor: fillColor,
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.8,
                    districtId: d.id // custom property
                });

                // Add Tooltip (Always visible label)
                circle.bindTooltip(`อ.${d.name}<br><span style="font-size:11px;font-weight:normal">จุดเสี่ยง: ${rStat.count} | เป้าหมาย: ${tStat.count}</span>`, {
                    permanent: true,
                    direction: 'top',
                    className: 'leaflet-tooltip-custom',
                    offset: [0, -radius]
                });

                // Click Event
                circle.on('click', function() {
                    loadData(d.id);
                });

                // Hover Event
                circle.on('mouseover', function() {
                    if (currentDistrictId == 'all' || currentDistrictId != d.id) {
                        this.setStyle({ weight: 4, color: '#1e40af' });
                    }
                });
                circle.on('mouseout', function() {
                    if (currentDistrictId != d.id) {
                        this.setStyle({ weight: 2, color: '#ffffff' });
                    }
                });

                districtMarkersLayer.addLayer(circle);
            });
        }

        function highlightCircleMarker(districtId) {
            districtMarkersLayer.eachLayer(layer => {
                if (layer.options.districtId == districtId) {
                    layer.setStyle({ weight: 4, color: '#1e3a8a', fillOpacity: 1 });
                    layer.bringToFront();
                } else {
                    layer.setStyle({ weight: 2, color: '#ffffff', fillOpacity: 0.4 });
                }
            });
        }

        function updateCharts(riskTypeData, targetTypeData) {
            // Common Options
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, font: { family: "'Kanit', sans-serif" } } }
                },
                cutout: '60%',
                animation: { duration: 800 }
            };

            // Risk Chart
            const ctxRisk = document.getElementById('riskTypeChart').getContext('2d');
            const elEmptyRisk = document.getElementById('riskChartEmpty');
            if (riskTypeData.length === 0) {
                elEmptyRisk.classList.remove('hidden');
                if(riskChartInstance) riskChartInstance.clear();
            } else {
                elEmptyRisk.classList.add('hidden');
                const rLabels = riskTypeData.map(t => t.type_name);
                const rData = riskTypeData.map(t => t.count);
                const rColors = riskTypeData.map(t => t.marker_color);

                if (riskChartInstance) {
                    riskChartInstance.data.labels = rLabels;
                    riskChartInstance.data.datasets[0].data = rData;
                    riskChartInstance.data.datasets[0].backgroundColor = rColors;
                    riskChartInstance.update();
                } else {
                    riskChartInstance = new Chart(ctxRisk, {
                        type: 'doughnut',
                        data: {
                            labels: rLabels,
                            datasets: [{ data: rData, backgroundColor: rColors, borderWidth: 2, borderColor: '#fff' }]
                        },
                        options: chartOptions
                    });
                }
            }

            // Target Chart
            const ctxTarget = document.getElementById('targetTypeChart').getContext('2d');
            const elEmptyTarget = document.getElementById('targetChartEmpty');
            if (targetTypeData.length === 0) {
                elEmptyTarget.classList.remove('hidden');
                if(targetChartInstance) targetChartInstance.clear();
            } else {
                elEmptyTarget.classList.add('hidden');
                const tLabels = targetTypeData.map(t => t.type_name);
                const tData = targetTypeData.map(t => t.count);
                const tColors = targetTypeData.map(t => t.marker_color);

                if (targetChartInstance) {
                    targetChartInstance.data.labels = tLabels;
                    targetChartInstance.data.datasets[0].data = tData;
                    targetChartInstance.data.datasets[0].backgroundColor = tColors;
                    targetChartInstance.update();
                } else {
                    targetChartInstance = new Chart(ctxTarget, {
                        type: 'doughnut',
                        data: {
                            labels: tLabels,
                            datasets: [{ data: tData, backgroundColor: tColors, borderWidth: 2, borderColor: '#fff' }]
                        },
                        options: chartOptions
                    });
                }
            }
        }
    </script>
</body>
</html>
