<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ผลการยึดทรัพย์คดียาเสพติด (Open Data) - CRIME MAP</title>
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

    <main class="flex-grow p-4 lg:p-6 w-full max-w-[1400px] mx-auto">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    ผลการยึดทรัพย์ที่เกี่ยวเนื่องจากคดียาเสพติด (Open Data)
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6" id="charts-container" style="display: none;">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center border-b pb-2">📊 สรุปยอดรวม (กราฟแท่ง)</h3>
                <div class="relative h-72 w-full">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center border-b pb-2">🍩 สัดส่วนข้อมูล (กราฟโดนัท)</h3>
                <div class="relative h-72 w-full flex justify-center">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-4">
            <!-- Dynamic Data Table -->
            <div class="overflow-x-auto w-full">
                <table id="dataTable" class="w-full text-left border-collapse display">
                    <thead>
                        <tr id="table-head" class="bg-gray-100 text-gray-700 text-sm border-b border-gray-200">
                            <!-- Headers injected by JS -->
                            <th class="py-3 px-4 font-semibold whitespace-nowrap text-center">กำลังโหลดข้อมูล...</th>
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
        const limit = 500; // Fetch enough data for datatables search/sort
        
        let barChartInstance = null;
        let pieChartInstance = null;
        let dataTableInstance = null;

        document.addEventListener('DOMContentLoaded', () => {
            fetchData();
        });

        async function fetchData() {
            try {
                const url = `api_opend_asset_seizures.php?limit=${limit}&offset=0`;
                const response = await fetch(url);
                
                const rawText = await response.text();
                
                // Try parsing JSON, if it fails it might be HTML or PHP error
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
                    attemptRenderCharts(data.result.records);
                } else {
                    alert(data.message || 'ไม่สามารถดึงข้อมูลได้');
                }
            } catch (error) {
                console.error("Fetch error:", error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย');
            }
        }

        function renderTable(records) {
            const thead = document.getElementById('table-head');
            thead.innerHTML = '';

            if (dataTableInstance) {
                dataTableInstance.destroy();
                $('#dataTable').empty(); // DataTables requires empty container for new columns
            }

            if (!records || records.length === 0) {
                thead.innerHTML = `<th class="py-3 px-4 font-semibold text-center">ไม่มีข้อมูล</th>`;
                return;
            }

            const keys = Object.keys(records[0]);
            
            // Exclude specific columns requested by user
            const excludedColumns = ['จังหวัด', 'วันที่ยึดทรัพย์'];
            const filteredKeys = keys.filter(key => !excludedColumns.includes(key));
            
            // Generate columns definition for DataTables
            const dtColumns = filteredKeys.map(key => {
                return {
                    data: key,
                    title: key,
                    render: function(data, type, row) {
                        let val = data;
                        if (val && !isNaN(val) && Number(val) > 1000 && key !== '_id' && !key.includes('ปี')) {
                            return `<span class="text-green-700 font-semibold">${Number(val).toLocaleString()}</span>`;
                        } else if (key === '_id') {
                            return `<span class="text-gray-400">#${val}</span>`;
                        }
                        return val || '-';
                    }
                };
            });

            dataTableInstance = $('#dataTable').DataTable({
                data: records,
                columns: dtColumns,
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                },
                order: [[0, 'desc']],
                pageLength: 10
            });
        }

        function attemptRenderCharts(records) {
            if (!records || records.length === 0) return;
            
            const keys = Object.keys(records[0]);
            let numericKey = null;
            let yearKey = null;
            let categoryKey2 = null;

            // Simple heuristic to guess fields
            for (let key of keys) {
                if (key !== '_id' && !key.includes('ปี') && !isNaN(records[0][key])) {
                    numericKey = key; // Found a numeric value column (e.g. มูลค่า, จำนวน)
                }
                if (key.includes('ปี')) {
                    yearKey = key; // Found year column
                }
                if (isNaN(records[0][key]) && key !== 'ที่มา' && key !== 'หน่วย' && key.length < 20 && !key.includes('ปี')) {
                    if (!categoryKey2 && key !== 'จังหวัด') categoryKey2 = key; // Found another category for Pie chart
                }
            }

            if (!numericKey) return; // Cannot chart without numbers

            document.getElementById('charts-container').style.display = 'grid';

            // Aggregate Data
            const aggYear = {};
            const aggCat = {};

            records.forEach(row => {
                const val = parseFloat(row[numericKey]) || 0;
                
                // Aggregate by Year for Bar Chart
                if (yearKey) {
                    const yearVal = row[yearKey] || 'ไม่ระบุปี';
                    if (!aggYear[yearVal]) aggYear[yearVal] = 0;
                    aggYear[yearVal] += val;
                }

                // Aggregate by Category for Pie Chart
                if (categoryKey2) {
                    const catVal = row[categoryKey2] || 'ไม่ระบุ';
                    if (!aggCat[catVal]) aggCat[catVal] = 0;
                    aggCat[catVal] += val;
                }
            });

            // Update Chart Titles dynamically if we found the keys
            if (yearKey) {
                document.querySelector('#barChart').closest('.bg-white').querySelector('h3').innerText = `📊 เปรียบเทียบตาม${yearKey} (กราฟแท่ง)`;
            }

            // Draw Bar Chart (By Year)
            if (yearKey && Object.keys(aggYear).length > 0) {
                const sortedYear = Object.entries(aggYear).sort((a, b) => a[0].localeCompare(b[0])); // Sort by year ascending
                if (barChartInstance) barChartInstance.destroy();
                const ctx1 = document.getElementById('barChart').getContext('2d');
                barChartInstance = new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: sortedYear.map(i => i[0]),
                        datasets: [{
                            label: numericKey,
                            data: sortedYear.map(i => i[1]),
                            backgroundColor: 'rgba(59, 130, 246, 0.6)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            // Draw Pie Chart (By Category)
            if (categoryKey2 && Object.keys(aggCat).length > 0) {
                const sortedCat = Object.entries(aggCat).sort((a, b) => b[1] - a[1]).slice(0, 8);
                if (pieChartInstance) pieChartInstance.destroy();
                const ctx2 = document.getElementById('pieChart').getContext('2d');
                pieChartInstance = new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: sortedCat.map(i => i[0]),
                        datasets: [{
                            data: sortedCat.map(i => i[1]),
                            backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#64748b', '#14b8a6'],
                            borderWidth: 2
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });
            }
        }

        // showError removed as DataTables handles empty states automatically
    </script>
</body>
</html>
