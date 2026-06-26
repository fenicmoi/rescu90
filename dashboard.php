<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Dashboard - CRIME MAP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Google Fonts (Kanit) -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow p-6 lg:p-8 max-w-7xl mx-auto w-full">
        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">สรุปภาพรวมสถานการณ์จุดเสี่ยง (Executive Dashboard)</h1>
                <p class="text-gray-500 text-sm mt-1">ข้อมูลเชิงสถิติและการกระจายตัวของเหตุการณ์ในจังหวัดพัทลุง</p>
            </div>
            <div>
                <a href="report_executive.php" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow-md font-medium transition whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    ออกรายงาน PDF
                </a>
            </div>
        </div>

        <!-- Top Row: Card Stats -->
        <h2 class="text-xl font-bold text-gray-800 mb-3 border-l-4 border-blue-500 pl-3">📍 สถิติจุดเสี่ยงเชิงพื้นที่</h2>
        <div id="stats-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <!-- Loading placeholder -->
            <div class="col-span-full text-center text-gray-500 py-4">กำลังโหลดข้อมูลสถิติ...</div>
        </div>

        <h2 class="text-xl font-bold text-gray-800 mb-3 border-l-4 border-red-500 pl-3 mt-6">🏠 สถิติบ้านเป้าหมาย</h2>
        <div id="target-stats-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <div class="col-span-full text-center text-gray-500 py-4">กำลังโหลดข้อมูลสถิติ...</div>
        </div>

        <!-- Middle Row: Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Bar Chart (District) -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">จำนวนจุดเสี่ยงแยกตามอำเภอ</h2>
                <div class="relative h-72 w-full">
                    <canvas id="districtChart"></canvas>
                </div>
            </div>

            <!-- Pie Chart (Risk Types) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">สัดส่วนประเภทความเสี่ยง</h2>
                <div class="relative h-72 w-full flex justify-center">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Recent Data Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-700">รายการรับแจ้งล่าสุด 10 อันดับ</h2>
                <a href="map_dashboard.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium">ดูในแผนที่ &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ประเภท</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานที่</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">พื้นที่ (ตำบล/อำเภอ)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">พฤติการณ์</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่แจ้ง</th>
                        </tr>
                    </thead>
                    <tbody id="recent-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Populated by JS -->
                        <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">กำลังโหลด...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch data from API
            fetch('api_dashboard.php')
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;
                        renderCards(data.total, data.resolved, data.by_type, data.target_total, data.target_resolved, data.target_by_type);
                        renderDistrictChart(data.by_district);
                        renderTypeChart(data.by_type);
                        renderTable(data.recent);
                    } else {
                        console.error('Error:', result.message);
                    }
                })
                .catch(err => console.error('Fetch Error:', err));

            function renderCards(total, resolved, byType, targetTotal, targetResolved, targetByType) {
                const container = document.getElementById('stats-container');
                container.innerHTML = ''; // clear loading

                // Total Card
                container.innerHTML += `
                    <div class="bg-blue-600 rounded-xl shadow-md p-4 text-white flex flex-col justify-center items-center transform transition hover:scale-105 relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-green-500 text-white text-xs px-2 py-1 rounded-bl-lg shadow">แก้ไขแล้ว ${resolved}</div>
                        <span class="text-sm font-medium opacity-80 uppercase tracking-wider mb-1 mt-2">รวมทั้งหมด</span>
                        <span class="text-4xl font-bold">${total}</span>
                        <span class="text-xs mt-1 opacity-70">จุด</span>
                    </div>
                `;

                // Type Cards
                byType.forEach(type => {
                    container.innerHTML += `
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col justify-between transform transition hover:-translate-y-1 hover:shadow-md">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-600 line-clamp-1">${type.type_name}</span>
                                <span class="w-3 h-3 rounded-full" style="background-color: ${type.marker_color}"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="text-3xl font-bold text-gray-800">${type.count}</span>
                            </div>
                        </div>
                    `;
                });

                // Target Cards
                const targetContainer = document.getElementById('target-stats-container');
                targetContainer.innerHTML = `
                    <div class="bg-red-600 rounded-xl shadow-md p-4 text-white flex flex-col justify-center items-center transform transition hover:scale-105 relative overflow-hidden">
                        <div class="absolute top-0 right-0 bg-green-500 text-white text-xs px-2 py-1 rounded-bl-lg shadow">ดำเนินการแล้ว ${targetResolved}</div>
                        <span class="text-sm font-medium opacity-80 uppercase tracking-wider mb-1 mt-2">รวมเป้าหมาย</span>
                        <span class="text-4xl font-bold">${targetTotal}</span>
                        <span class="text-xs mt-1 opacity-70">แห่ง</span>
                    </div>
                `;
                targetByType.forEach(type => {
                    targetContainer.innerHTML += `
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-col justify-between transform transition hover:-translate-y-1 hover:shadow-md border-b-4" style="border-bottom-color: ${type.marker_color}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-600 line-clamp-1">${type.type_name}</span>
                                <span class="w-3 h-3 rounded-full" style="background-color: ${type.marker_color}"></span>
                            </div>
                            <div class="flex items-end gap-2">
                                <span class="text-3xl font-bold text-gray-800">${type.count}</span>
                            </div>
                        </div>
                    `;
                });
            }

            function renderDistrictChart(districtStats) {
                const ctx = document.getElementById('districtChart').getContext('2d');
                const labels = districtStats.map(d => d.district_name);
                const data = districtStats.map(d => d.count);

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'จำนวนจุดเสี่ยง',
                            data: data,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)', // blue-500
                            borderColor: 'rgb(37, 99, 235)', // blue-600
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            }

            function renderTypeChart(typeStats) {
                const ctx = document.getElementById('typeChart').getContext('2d');
                const labels = typeStats.map(t => t.type_name);
                const data = typeStats.map(t => t.count);
                const colors = typeStats.map(t => t.marker_color);

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: { family: "'Kanit', sans-serif" }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }

            function renderTable(reports) {
                const tbody = document.getElementById('recent-table-body');
                tbody.innerHTML = '';

                if (reports.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">ยังไม่มีข้อมูล</td></tr>';
                    return;
                }

                reports.forEach(r => {
                    // Format date roughly if exists
                    let dateStr = '-';
                    if (r.created_at) {
                        const d = new Date(r.created_at);
                        dateStr = d.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' });
                    }

                    const subdistrict = r.subdistrict_name ? 'ต.' + r.subdistrict_name : '-';
                    const district = r.district_name ? 'อ.' + r.district_name : '-';
                    
                    const icon = r.record_type === 'target' ? '🏠' : '📍';

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50 transition-colors';
                    tr.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">${icon}</span>
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-opacity-20" 
                                      style="color: ${r.marker_color}; background-color: ${r.marker_color}33;">
                                    ${r.type_name}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">${r.location_name}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">${subdistrict} <br> <span class="text-xs text-gray-400">${district}</span></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500 line-clamp-2 max-w-xs" title="${r.details || '-'}">${r.details || '-'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            ${dateStr}
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        });
    </script>
    <!-- Footer -->
    <footer class="mt-8 py-4 text-center text-sm text-gray-500 bg-white border-t border-gray-200">
        พัฒนาโดย <span class="font-bold text-blue-700">จังหวัดพัทลุง</span>
    </footer>
</body>
</html>

