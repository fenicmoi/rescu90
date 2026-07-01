<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถิติคดีอาญาที่มีการจับกุม (Open Data) - CRIME MAP</title>
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
                    สถิติคดีอาญาที่มีการจับกุม (Open Data)
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

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="summaryCards">
            <!-- Summary cards will be rendered here by JS -->
        </div>

        <!-- Charts Dashboard -->
        <div class="mb-6">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-500"></i> กราฟแท่งเปรียบเทียบสถิติรายปี
                </h3>
                <div class="relative h-80 w-full">
                    <canvas id="yearlyChart"></canvas>
                </div>
            </div>
        </div>
        

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-4">
            <!-- Dynamic Data Table -->
            <div class="overflow-x-auto w-full">
                <table id="dataTable" class="w-full text-left border-collapse display">
                    <thead id="dataTableHead">
                        <tr class="bg-gray-100 text-gray-700 text-sm border-b border-gray-200">
                            <th class="py-3 px-4 font-semibold whitespace-nowrap">กำลังโหลด...</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        let currentOffset = 0;
        const limit = 500; // Fetch more data for local datatables filtering
        
        let yearlyChartInstance = null;
        let dataTableInstance = null;

        document.addEventListener('DOMContentLoaded', () => {
            fetchData();
        });

        async function fetchData() {
            try {
                const url = `api_opend_case1.php?limit=${limit}&offset=${currentOffset}`;
                const response = await fetch(url);
                
                const rawText = await response.text();
                
                let data;
                try {
                    data = JSON.parse(rawText);
                } catch (parseError) {
                    console.error("JSON Parse Error:", parseError);
                    if (rawText.includes('<html') || rawText.includes('login') || rawText.includes('Warning') || rawText.includes('Fatal error')) {
                        alert('เซสชั่นหมดอายุหรือเกิดข้อผิดพลาดในเซิร์ฟเวอร์ (กรุณาดู Console)');
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
            if (!records || records.length === 0) return;
            
            if (dataTableInstance) {
                dataTableInstance.destroy();
                $('#dataTable').empty(); // clear existing DOM completely
            }
            
            // Generate columns dynamically
            const keys = Object.keys(records[0]);
            const columns = keys.map(k => ({ data: k, title: k }));
            
            dataTableInstance = $('#dataTable').DataTable({
                data: records,
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                },
                columns: columns,
                order: [[0, 'desc']],
                pageLength: 10
            });
        }

        function renderCharts(records) {
            if (!records || records.length === 0) return;

            // Find the key that represents "Year"
            let yearKey = Object.keys(records[0]).find(k => k.includes('ปี'));
            let countKey = Object.keys(records[0]).find(k => k.includes('จำนวน') || k.includes('คดีอญาทีจับกุม') || k.includes('คดี'));
            
            if (!countKey) {
                // Fallback: Find any numeric column that is not Year or ID
                const numericKeys = Object.keys(records[0]).filter(k => k !== yearKey && k !== '_id' && !isNaN(parseFloat(records[0][k])));
                if (numericKeys.length > 0) countKey = numericKeys[0];
            }
            
            // If no year key, we can't draw the chart properly, just abort or mock
            if (!yearKey) {
                console.warn("No year column found. Using fallback.");
                return;
            }
            
            renderSummaryCards(records, yearKey, countKey);

            // Aggregate Data by Year
            const yearlyData = {};

            records.forEach(row => {
                let y = row[yearKey] || 'ไม่ระบุ';
                // Clean year string just in case
                if (typeof y === 'string') y = y.replace(/[^0-9]/g, '');
                if (!y) y = 'ไม่ระบุ';
                
                let count = 1;
                if (countKey && row[countKey]) {
                    count = parseInt(row[countKey]) || 1;
                }
                
                if (!yearlyData[y]) yearlyData[y] = 0;
                yearlyData[y] += count;
            });

            // Sort Years (keys) numerically
            const sortedYears = Object.keys(yearlyData).sort((a, b) => {
                if (a === 'ไม่ระบุ') return 1;
                if (b === 'ไม่ระบุ') return -1;
                return parseInt(a) - parseInt(b);
            });
            
            const stLabels = sortedYears;
            const stData = sortedYears.map(y => yearlyData[y]);

            // Draw Yearly Bar Chart
            if (yearlyChartInstance) yearlyChartInstance.destroy();
            const ctx1 = document.getElementById('yearlyChart').getContext('2d');
            
            const backgroundColors = stData.map((_, i) => `hsla(${(i * 360 / stData.length)}, 70%, 55%, 0.7)`);
            const borderColors = stData.map((_, i) => `hsl(${(i * 360 / stData.length)}, 70%, 50%)`);

            yearlyChartInstance = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: stLabels,
                    datasets: [{
                        label: 'จำนวนคดีอาญา (คดี)',
                        data: stData,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: borderColors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                            titleFont: { family: 'Kanit', size: 14 },
                            bodyFont: { family: 'Kanit', size: 14 },
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return ' จำนวน: ' + context.parsed.y.toLocaleString() + ' คดี';
                                }
                            }
                        }
                    },
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            ticks: { precision: 0, font: { family: 'Kanit' } },
                            grid: { borderDash: [5, 5], color: '#e5e7eb' }
                        },
                        x: {
                            ticks: { font: { family: 'Kanit', size: 14, weight: 'bold' } },
                            grid: { display: false }
                        }
                    },
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
        
        function renderSummaryCards(records, yearKey, countKey) {
            if (!records || records.length === 0) return;
            
            let totalCases = 0;
            let latestYear = 0;
            let latestYearCases = 0;
            
            records.forEach(row => {
                let y = parseInt(row[yearKey]) || 0;
                let c = countKey && row[countKey] ? parseInt(row[countKey]) : 1;
                
                totalCases += c;
                if (y > latestYear) {
                    latestYear = y;
                    latestYearCases = c;
                } else if (y === latestYear) {
                    latestYearCases += c;
                }
            });
            
            const html = `
                <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl shadow-lg p-5 text-white flex items-center justify-between transform transition hover:-translate-y-1 hover:shadow-xl">
                    <div>
                        <p class="text-blue-100 text-sm font-medium mb-1">จำนวนคดีทั้งหมด</p>
                        <h4 class="text-3xl font-bold">${totalCases.toLocaleString()} <span class="text-base font-normal">คดี</span></h4>
                    </div>
                    <div class="bg-white/20 p-4 rounded-full">
                        <i class="fas fa-balance-scale text-2xl"></i>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg p-5 text-white flex items-center justify-between transform transition hover:-translate-y-1 hover:shadow-xl">
                    <div>
                        <p class="text-purple-100 text-sm font-medium mb-1">สถิติปีล่าสุด (${latestYear})</p>
                        <h4 class="text-3xl font-bold">${latestYearCases.toLocaleString()} <span class="text-base font-normal">คดี</span></h4>
                    </div>
                    <div class="bg-white/20 p-4 rounded-full">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-xl shadow-lg p-5 text-white flex items-center justify-between transform transition hover:-translate-y-1 hover:shadow-xl">
                    <div>
                        <p class="text-emerald-100 text-sm font-medium mb-1">พื้นที่รายงาน</p>
                        <h4 class="text-2xl font-bold truncate max-w-[150px]">${records[0]['จังหวัด'] || 'พัทลุง'}</h4>
                    </div>
                    <div class="bg-white/20 p-4 rounded-full">
                        <i class="fas fa-map-marker-alt text-2xl text-center w-6"></i>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-amber-500 to-amber-700 rounded-xl shadow-lg p-5 text-white flex items-center justify-between transform transition hover:-translate-y-1 hover:shadow-xl">
                    <div>
                        <p class="text-amber-100 text-sm font-medium mb-1">ข้อมูลย้อนหลัง</p>
                        <h4 class="text-3xl font-bold">${records.length} <span class="text-base font-normal">ปี</span></h4>
                    </div>
                    <div class="bg-white/20 p-4 rounded-full">
                        <i class="fas fa-history text-2xl"></i>
                    </div>
                </div>
            `;
            
            document.getElementById('summaryCards').innerHTML = html;
        }



        // showError removed since DataTables handles empty state naturally
    </script>
</body>
</html>
