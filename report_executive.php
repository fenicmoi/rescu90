<?php
require_once 'auth.php';
// All logged in users can generate reports. Can restrict by role if needed in future.
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานผู้บริหาร - CRIME MAP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Google Fonts (Sarabun is ideal for formal Thai documents) -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f1f5f9; }
        
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 10mm auto;
            border-radius: 5px;
            background: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        @media print {
            body { background: white; margin: 0; padding: 0; }
            .a4-page {
                margin: 0;
                border: initial;
                border-radius: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="text-gray-800">

    <!-- Controls (Not printed/exported) -->
    <div class="no-print bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-50 shadow-md">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="hover:text-blue-300 transition font-medium text-sm">&larr; กลับไปยัง Dashboard</a>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-gray-100 text-gray-800 px-4 py-2 rounded shadow hover:bg-gray-200 font-medium text-sm transition">🖨️ สั่งพิมพ์ (Print)</button>
            <button id="btn-download" class="bg-blue-600 text-white px-5 py-2 rounded shadow hover:bg-blue-700 font-bold transition text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                บันทึกเป็น PDF
            </button>
        </div>
    </div>

    <!-- Report Content (A4 Format) -->
    <div id="report-content" class="a4-page">
        <!-- Official Header -->
        <div class="text-center mb-8 border-b-2 border-gray-800 pb-4">
            <div class="text-5xl mb-2">🛡️</div>
            <h1 class="text-2xl font-bold">รายงานสรุปผลการปฏิบัติการเชิงสถิติ</h1>
            <h2 class="text-xl font-semibold mt-1">CRIME MAP (Phatthalung Smart Safety Data Center)</h2>
            <p class="text-gray-600 mt-2">พิมพ์เมื่อ: <?= date('d/m/Y H:i') ?></p>
        </div>

        <!-- 1. Executive Summary: Risk Locations -->
        <div class="mb-6">
            <h3 class="text-lg font-bold border-l-4 border-blue-800 pl-3 mb-3">1. สรุปข้อมูลจุดเสี่ยงเชิงพื้นที่</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="border border-gray-300 p-4 rounded-lg bg-gray-50 text-center">
                    <p class="text-gray-600 font-medium text-sm">จำนวนจุดเสี่ยงแจ้งเข้าระบบสะสม</p>
                    <p class="text-3xl font-bold text-blue-800 mt-2" id="val-risk-total">-</p>
                    <p class="text-xs text-gray-500 mt-1">จุด</p>
                </div>
                <div class="border border-green-300 p-4 rounded-lg bg-green-50 text-center">
                    <p class="text-green-800 font-medium text-sm">ได้รับการแก้ไข/ดำเนินการแล้ว</p>
                    <p class="text-3xl font-bold text-green-600 mt-2" id="val-risk-resolved">-</p>
                    <p class="text-xs text-green-700 mt-1">จุด</p>
                </div>
            </div>
        </div>

        <!-- 2. Executive Summary: Target Houses -->
        <div class="mb-8">
            <h3 class="text-lg font-bold border-l-4 border-red-800 pl-3 mb-3">2. สรุปข้อมูลบ้านเป้าหมาย</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="border border-gray-300 p-4 rounded-lg bg-gray-50 text-center">
                    <p class="text-gray-600 font-medium text-sm">จำนวนบ้านเป้าหมายสะสม</p>
                    <p class="text-3xl font-bold text-red-800 mt-2" id="val-target-total">-</p>
                    <p class="text-xs text-gray-500 mt-1">แห่ง</p>
                </div>
                <div class="border border-green-300 p-4 rounded-lg bg-green-50 text-center">
                    <p class="text-green-800 font-medium text-sm">ได้รับการจัดการ/ตรวจสอบแล้ว</p>
                    <p class="text-3xl font-bold text-green-600 mt-2" id="val-target-resolved">-</p>
                    <p class="text-xs text-green-700 mt-1">แห่ง</p>
                </div>
            </div>
        </div>

        <!-- 3. Charts -->
        <div class="mb-8">
            <h3 class="text-lg font-bold border-l-4 border-gray-800 pl-3 mb-4">3. การกระจายตัวของจุดเสี่ยง</h3>
            <div class="grid grid-cols-2 gap-8">
                <div>
                    <h4 class="text-sm font-semibold mb-2 text-center text-gray-700">ปริมาณจุดเสี่ยงแยกตามอำเภอ</h4>
                    <div class="relative h-60 w-full">
                        <canvas id="pdfDistrictChart"></canvas>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold mb-2 text-center text-gray-700">สัดส่วนประเภทความเสี่ยง</h4>
                    <div class="relative h-60 w-full flex justify-center">
                        <canvas id="pdfTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Data Table -->
        <div class="mb-8">
            <h3 class="text-lg font-bold border-l-4 border-gray-800 pl-3 mb-3">4. ข้อมูลสถิติเชิงพื้นที่</h3>
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead>
                    <tr class="bg-blue-50 text-blue-900">
                        <th class="border border-gray-300 px-4 py-2 text-left">พื้นที่ปกครอง (อำเภอ)</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">จำนวนการรับแจ้ง (จุด)</th>
                        <th class="border border-gray-300 px-4 py-2 text-center">หมายเหตุ</th>
                    </tr>
                </thead>
                <tbody id="district-table-body">
                    <tr><td colspan="3" class="text-center py-4 text-gray-500">กำลังประมวลผลข้อมูล...</td></tr>
                </tbody>
            </table>
        </div>


        
        <!-- Footer Print -->
        <div class="absolute bottom-10 left-0 right-0 text-center">
            <p class="text-[10px] text-gray-400">สร้างจากระบบฐานข้อมูลอัตโนมัติ (Automated Report) - พัฒนาโดย จังหวัดพัทลุง</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch API data
            fetch('api_dashboard.php')
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;
                        
                        // Populate Text
                        document.getElementById('val-risk-total').innerText = data.total;
                        document.getElementById('val-risk-resolved').innerText = data.resolved;
                        document.getElementById('val-target-total').innerText = data.target_total;
                        document.getElementById('val-target-resolved').innerText = data.target_resolved;

                        // Render Charts
                        renderCharts(data.by_district, data.by_type);

                        // Render Table
                        renderTable(data.by_district);
                    }
                });

            function renderCharts(districtStats, typeStats) {
                // Disable animations for html2pdf to capture correctly immediately
                Chart.defaults.animation = false;

                // District Chart
                const ctxDist = document.getElementById('pdfDistrictChart').getContext('2d');
                new Chart(ctxDist, {
                    type: 'bar',
                    data: {
                        labels: districtStats.map(d => d.district_name),
                        datasets: [{
                            data: districtStats.map(d => d.count),
                            backgroundColor: 'rgba(30, 64, 175, 0.8)',
                            borderColor: '#1e40af',
                            borderWidth: 1,
                            borderRadius: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });

                // Type Chart
                const ctxType = document.getElementById('pdfTypeChart').getContext('2d');
                new Chart(ctxType, {
                    type: 'pie',
                    data: {
                        labels: typeStats.map(t => t.type_name),
                        datasets: [{
                            data: typeStats.map(t => t.count),
                            backgroundColor: typeStats.map(t => t.marker_color),
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                position: 'right', 
                                labels: { usePointStyle: true, font: { family: "'Sarabun', sans-serif", size: 12 }, padding: 15 } 
                            }
                        }
                    }
                });
            }

            function renderTable(districtStats) {
                const tbody = document.getElementById('district-table-body');
                tbody.innerHTML = '';
                
                if (districtStats.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="border border-gray-300 text-center py-4 text-gray-500">ไม่มีข้อมูลในระบบ</td></tr>';
                    return;
                }

                let totalCount = 0;
                districtStats.forEach(d => {
                    totalCount += d.count;
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="border border-gray-300 px-4 py-2">อำเภอ${d.district_name}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center font-bold text-gray-800">${d.count}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center text-gray-400 text-xs">-</td>
                    `;
                    tbody.appendChild(tr);
                });

                // Total Row
                tbody.innerHTML += `
                    <tr class="bg-gray-100 font-bold">
                        <td class="border border-gray-300 px-4 py-2 text-right">รวมรับแจ้งทั้งหมด</td>
                        <td class="border border-gray-300 px-4 py-2 text-center text-blue-800 text-lg">${totalCount}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"></td>
                    </tr>
                `;
            }

            // Generate PDF
            document.getElementById('btn-download').addEventListener('click', function() {
                const element = document.getElementById('report-content');
                const opt = {
                    margin:       [0, 0, 0, 0],
                    filename:     'Executive_Report_PTL_Safety.pdf',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, logging: false },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                const btn = this;
                const originalText = btn.innerHTML;
                btn.innerHTML = '⏳ กำลังประมวลผล...';
                btn.classList.add('opacity-75', 'cursor-not-allowed');

                html2pdf().set(opt).from(element).save().then(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('opacity-75', 'cursor-not-allowed');
                });
            });
        });
    </script>
</body>
</html>
