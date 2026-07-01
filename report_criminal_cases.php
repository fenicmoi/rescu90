<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>การจับกุมคดียาเสพติด (Open Data) - CRIME MAP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jQuery & DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <!-- Google Fonts (Kanit) -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Kanit', sans-serif; }
        /* Tailwind override for DataTables */
        .dataTables_wrapper .dataTables_length select, .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.25rem; margin-left: 0.5rem; outline: none;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.75rem; margin: 0 0.125rem; border-radius: 0.375rem;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-4 lg:p-6 w-full max-w-[1200px] mx-auto">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    การจับกุมคดียาเสพติดจังหวัดพัทลุง (Open Data)
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    ข้อมูลดึงตรงจากศูนย์กลางข้อมูลเปิดภาครัฐ data.go.th <br>
                    <span id="meta-owner" class="inline-block mt-2 bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs font-medium border border-blue-100">
                        <i class="fas fa-building mr-1"></i> เจ้าของข้อมูล: กำลังโหลด...
                    </span>
                    <span id="meta-date" class="inline-block mt-2 ml-1 bg-green-50 text-green-700 px-2 py-0.5 rounded text-xs font-medium border border-green-100">
                        <i class="fas fa-calendar-check mr-1"></i> วันที่ปรับปรุงข้อมูล: กำลังโหลด...
                    </span>
                    <span id="meta-freq" class="inline-block mt-2 ml-1 bg-purple-50 text-purple-700 px-2 py-0.5 rounded text-xs font-medium border border-purple-100">
                        <i class="fas fa-sync-alt mr-1"></i> ความถี่ในการอัปเดต: กำลังโหลด...
                    </span>
                </p>
            </div>
        </div>

        <!-- Charts Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Bar Chart: Arrests by Station -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center border-b pb-2">📊 สถิติการจับกุม แยกตามสถานีตำรวจ</h3>
                <div class="relative h-72 w-full">
                    <canvas id="stationChart"></canvas>
                </div>
            </div>
            
            <!-- Doughnut Chart: Arrests by Charge -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center border-b pb-2">🍩 สัดส่วนคดี แยกตามข้อหา</h3>
                <div class="relative h-72 w-full flex justify-center">
                    <canvas id="chargeChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-4">
            <!-- Dynamic Data Table -->
            <div class="overflow-x-auto w-full">
                <table id="dataTable" class="w-full text-left border-collapse display">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 text-sm border-b border-gray-200">
                            <th class="py-3 px-4 font-semibold whitespace-nowrap">ID</th>
                            <th class="py-3 px-4 font-semibold whitespace-nowrap">จังหวัด</th>
                            <th class="py-3 px-4 font-semibold whitespace-nowrap">สถานีตำรวจ</th>
                            <th class="py-3 px-4 font-semibold whitespace-nowrap">ข้อหา</th>
                            <th class="py-3 px-4 font-semibold whitespace-nowrap">จำนวน</th>
                            <th class="py-3 px-4 font-semibold whitespace-nowrap">หน่วยวัด</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </main>

    <script>
        let currentOffset = 0;
        const limit = 500; // Fetch more data for local datatables filtering
        
        let stationChartInstance = null;
        let chargeChartInstance = null;
        let dataTableInstance = null;

        document.addEventListener('DOMContentLoaded', () => {
            fetchData();
        });

        async function fetchData() {
            try {
                const url = `api_opend_criminal.php?limit=${limit}&offset=${currentOffset}`;
                const response = await fetch(url);
                
                const rawText = await response.text();
                
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (parseError) {
                    console.error("JSON Parse Error:", parseError);
                    console.error("Raw Response:", rawText);
                    
                    if (rawText.includes('<html') || rawText.includes('login') || rawText.includes('Warning') || rawText.includes('Fatal error')) {
                        alert('เซสชั่นหมดอายุหรือเกิดข้อผิดพลาดในเซิร์ฟเวอร์ (กรุณาดู Console)');
                        // window.location.reload(); // Disable reload so user can see error
                    } else {
                        alert('เกิดข้อผิดพลาดในการดึงข้อมูลจากเซิร์ฟเวอร์ (Invalid Data Format)');
                    }
                    return;
                }

                if (data.success && data.result) {
                    if (data.metadata) {
                        document.getElementById('meta-owner').innerHTML = `<i class="fas fa-building mr-1"></i> เจ้าของข้อมูล: ${data.metadata.author}`;
                        document.getElementById('meta-date').innerHTML = `<i class="fas fa-calendar-check mr-1"></i> วันที่ปรับปรุงข้อมูล: ${data.metadata.last_update}`;
                        document.getElementById('meta-freq').innerHTML = `<i class="fas fa-sync-alt mr-1"></i> ความถี่ในการอัปเดต: ${data.metadata.frequency}`;
                    }
                    renderTable(data.result.records);
                    renderCharts(data.result.records);
                } else {
                    alert(data.message || 'ไม่สามารถดึงข้อมูลได้');
                }
            } catch (error) {
                console.error("Fetch error:", error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย');
            }
        }

        function renderTable(records) {
            if (dataTableInstance) {
                dataTableInstance.destroy();
            }

            dataTableInstance = $('#dataTable').DataTable({
                data: records,
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                },
                columns: [
                    { data: '_id' },
                    { data: 'จังหวัด' },
                    { data: 'ชื่อสถานีตำรวจภูธร' },
                    { 
                        data: 'ข้อหา',
                        render: function(data, type, row) {
                            return `<span class="bg-red-50 text-red-700 px-2 py-1 rounded text-xs font-medium border border-red-200">${data || '-'}</span>`;
                        }
                    },
                    { 
                        data: 'จำนวน',
                        render: function(data, type, row) {
                            return data ? parseInt(data).toLocaleString() : '-';
                        }
                    },
                    { data: 'หน่วย' }
                ],
                order: [[0, 'desc']],
                pageLength: 10
            });
        }

        function renderCharts(records) {
            if (!records || records.length === 0) return;

            // Aggregate Data
            const stations = {};
            const charges = {};

            records.forEach(row => {
                const count = parseInt(row['จำนวน']) || 0;
                
                // Station Aggregation
                const st = row['ชื่อสถานีตำรวจภูธร'] || 'ไม่ระบุ';
                if (!stations[st]) stations[st] = 0;
                stations[st] += count;

                // Charge Aggregation
                const ch = row['ข้อหา'] || 'ไม่ระบุ';
                if (!charges[ch]) charges[ch] = 0;
                charges[ch] += count;
            });

            // Sort Stations by Count
            const sortedStations = Object.entries(stations).sort((a, b) => b[1] - a[1]);
            const stLabels = sortedStations.map(i => i[0]);
            const stData = sortedStations.map(i => i[1]);

            // Draw Station Bar Chart
            if (stationChartInstance) stationChartInstance.destroy();
            const ctx1 = document.getElementById('stationChart').getContext('2d');
            stationChartInstance = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: stLabels,
                    datasets: [{
                        label: 'จำนวนคดี (ราย)',
                        data: stData,
                        backgroundColor: 'rgba(59, 130, 246, 0.6)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });

            // Draw Charge Doughnut Chart
            if (chargeChartInstance) chargeChartInstance.destroy();
            const ctx2 = document.getElementById('chargeChart').getContext('2d');
            chargeChartInstance = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(charges),
                    datasets: [{
                        data: Object.values(charges),
                        backgroundColor: [
                            '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#64748b'
                        ],
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { font: { family: 'Kanit' } } }
                    }
                }
            });
        }

        // showError removed since DataTables handles empty state naturally
    </script>
</body>
</html>
