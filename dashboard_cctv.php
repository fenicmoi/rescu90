<?php require_once 'auth.php'; requireRole([1, 2, 3, 4]); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCTV Dashboard - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background-color: #f8fafc; }
        
        /* Custom Animations */
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes fade-in {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        @keyframes slide-in-right {
            0% { opacity: 0; transform: translateX(20px); }
            100% { opacity: 1; transform: translateX(0); }
        }
        
        .animate-fade-in-up { animation: fade-in-up 0.6s ease-out forwards; }
        .animate-fade-in { animation: fade-in 0.8s ease-out forwards; }
        .animate-slide-in-right { animation: slide-in-right 0.6s ease-out forwards; }
        
        /* Staggered animation delays */
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        .delay-400 { animation-delay: 400ms; }
        
        /* Glassmorphism utilities */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        }
        
        /* Smooth transitions */
        .hover-lift {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px -10px rgba(0, 0, 0, 0.12);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col relative overflow-x-hidden">
    <!-- Background Decorators -->
    <div class="fixed top-0 left-0 w-full h-96 bg-gradient-to-br from-indigo-50 via-blue-50 to-slate-100 -z-10"></div>
    <div class="fixed -top-24 -right-24 w-96 h-96 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 -z-10 animate-fade-in"></div>
    <div class="fixed top-32 -left-24 w-72 h-72 bg-indigo-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 -z-10 animate-fade-in delay-200"></div>

    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-6 lg:p-8 max-w-7xl mx-auto w-full z-10">
        
        <!-- Header Section -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-fade-in-up">
            <div>
                <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-800 to-indigo-800 tracking-tight">
                    ภาพรวมระบบกล้องวงจรปิด
                </h1>
                <p class="text-gray-500 text-sm mt-1.5 font-medium flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-indigo-500"></span>
                    สถิติและข้อมูลการกระจายตัวของกล้องวงจรปิดในจังหวัดพัทลุง
                </p>
            </div>
            <div>
                <a href="manage_cctv.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white px-6 py-2.5 rounded-full shadow-lg shadow-indigo-200 font-medium transition-all hover:scale-105 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    จัดการข้อมูล CCTV
                </a>
            </div>
        </div>

        <!-- Top Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Card -->
            <div class="bg-gradient-to-br from-indigo-500 via-blue-600 to-indigo-700 rounded-2xl shadow-xl shadow-indigo-200/50 p-6 text-white flex flex-col justify-center items-center relative overflow-hidden hover-lift opacity-0 animate-fade-in-up delay-100">
                <div class="absolute -right-6 -top-6 text-white/10 transform rotate-12">
                    <svg class="w-36 h-36" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path></svg>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1/2 bg-gradient-to-t from-black/20 to-transparent"></div>
                <span class="text-sm font-medium text-indigo-100 tracking-wider mb-2 z-10 uppercase">จำนวนกล้องทั้งหมด</span>
                <div class="flex items-end gap-2 z-10">
                    <span class="text-6xl font-extrabold tracking-tight drop-shadow-md" id="cctv-total">
                        <span class="animate-pulse">...</span>
                    </span>
                    <span class="text-xl font-medium text-indigo-100 pb-1.5">ตัว</span>
                </div>
            </div>
            
            <!-- Type Cards -->
            <div class="md:col-span-3 opacity-0 animate-fade-in-up delay-200">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-6 w-1.5 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-lg font-bold text-gray-700">แยกตามประเภทกล้อง (Top)</h2>
                </div>
                <div id="cctv-types-container" class="grid grid-cols-2 lg:grid-cols-4 gap-4 h-[calc(100%-2rem)]">
                    <div class="col-span-full flex items-center justify-center text-indigo-400 py-8 bg-white/50 rounded-2xl border border-white">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        กำลังโหลดข้อมูลสถิติ...
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- District Chart -->
            <div class="glass-card rounded-2xl p-6 opacity-0 animate-fade-in-up delay-300">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-800">จำนวนกล้องแยกตามอำเภอ</h2>
                    <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></span>
                </div>
                <div class="relative h-72 w-full">
                    <canvas id="districtChart"></canvas>
                </div>
            </div>

            <!-- Affiliation Chart -->
            <div class="glass-card rounded-2xl p-6 opacity-0 animate-fade-in-up delay-400">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-800">Top 10 หน่วยงานเจ้าของกล้อง</h2>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></span>
                </div>
                <div class="relative h-72 w-full">
                    <canvas id="affiliationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Table -->
        <div class="glass-card rounded-2xl overflow-hidden opacity-0 animate-fade-in-up delay-400 mb-8 border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100/50 flex justify-between items-center bg-white/40">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    กล้อง CCTV ที่เพิ่งเพิ่มล่าสุด
                </h2>
                <a href="map_dashboard.php" class="group text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1 transition-colors">
                    ดูในแผนที่ 
                    <svg class="w-4 h-4 transform transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">รหัสกล้อง</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">สถานที่ติดตั้ง</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">สังกัด</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ประเภท</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">อำเภอ</th>
                        </tr>
                    </thead>
                    <tbody id="recent-table-body" class="bg-white/30 divide-y divide-gray-100/50">
                        <tr><td colspan="5" class="px-6 py-8 text-center text-sm text-indigo-400">
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <svg class="animate-spin h-6 w-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>กำลังโหลดข้อมูล...</span>
                            </div>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // Set Chart.js defaults for Kanit font
            Chart.defaults.font.family = "'Kanit', sans-serif";
            Chart.defaults.color = '#64748b'; // slate-500

            fetch('api_dashboard_cctv.php')
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;
                        
                        // Animate counting up for total
                        animateValue("cctv-total", 0, data.total, 1500);
                        
                        renderTypeCards(data.by_type);
                        renderDistrictChart(data.by_district);
                        renderAffiliationChart(data.by_affiliation);
                        renderTable(data.recent);
                    } else {
                        console.error('Error:', result.message);
                    }
                })
                .catch(err => console.error('Fetch Error:', err));

            // Custom gradients for type cards
            const typeGradients = [
                'from-blue-500 to-indigo-600', 
                'from-emerald-400 to-teal-500', 
                'from-amber-400 to-orange-500', 
                'from-purple-500 to-pink-600'
            ];
            const typeShadows = [
                'shadow-blue-200/50', 'shadow-emerald-200/50', 'shadow-orange-200/50', 'shadow-purple-200/50'
            ];
            const typeText = [
                'text-blue-600', 'text-teal-600', 'text-orange-600', 'text-purple-600'
            ];

            function renderTypeCards(byType) {
                const container = document.getElementById('cctv-types-container');
                container.innerHTML = '';
                
                // Show up to 4 top types
                const topTypes = byType.slice(0, 4);
                
                topTypes.forEach((type, index) => {
                    const gradient = typeGradients[index % typeGradients.length];
                    const shadow = typeShadows[index % typeShadows.length];
                    const textColor = typeText[index % typeText.length];
                    
                    container.innerHTML += `
                        <div class="glass-card rounded-2xl shadow-lg ${shadow} p-5 flex flex-col justify-between hover-lift relative overflow-hidden group">
                            <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b ${gradient}"></div>
                            <div class="absolute -right-4 -bottom-4 w-16 h-16 rounded-full bg-gradient-to-br ${gradient} opacity-10 group-hover:scale-150 transition-transform duration-500"></div>
                            
                            <span class="text-sm font-semibold text-gray-500 mb-2 truncate relative z-10" title="${type.type_name}">${type.type_name}</span>
                            <div class="flex items-end gap-1 relative z-10">
                                <span class="text-3xl font-extrabold ${textColor}">${type.count}</span>
                                <span class="text-xs text-gray-400 mb-1.5 font-medium">ตัว</span>
                            </div>
                        </div>
                    `;
                });
            }

            function renderDistrictChart(districtStats) {
                const ctx = document.getElementById('districtChart').getContext('2d');
                const labels = districtStats.map(d => d.district_name);
                const data = districtStats.map(d => d.count);
                
                // Create gradient for bars
                let gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 1)');   // indigo-500
                gradient.addColorStop(1, 'rgba(168, 85, 247, 0.6)'); // purple-500 semi-transparent

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'จำนวนกล้อง',
                            data: data,
                            backgroundColor: gradient,
                            borderRadius: 6,
                            borderSkipped: false,
                            barThickness: 'flex',
                            maxBarThickness: 32
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                titleFont: { size: 14, family: "'Kanit', sans-serif" },
                                bodyFont: { size: 14, family: "'Kanit', sans-serif" },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        scales: { 
                            x: { 
                                grid: { display: false, drawBorder: false }
                            },
                            y: { 
                                beginAtZero: true,
                                grid: { 
                                    color: 'rgba(241, 245, 249, 1)', // slate-100
                                    drawBorder: false,
                                    borderDash: [5, 5]
                                },
                                border: { display: false }
                            } 
                        },
                        animation: {
                            y: { duration: 2000, easing: 'easeOutQuart' }
                        }
                    }
                });
            }

            function renderAffiliationChart(affStats) {
                const ctx = document.getElementById('affiliationChart').getContext('2d');
                const labels = affStats.map(a => a.affiliation || 'ไม่ระบุ');
                const data = affStats.map(a => a.count);

                // Create gradient for bars
                let gradient = ctx.createLinearGradient(0, 0, 300, 0);
                gradient.addColorStop(0, 'rgba(56, 189, 248, 1)');   // sky-400
                gradient.addColorStop(1, 'rgba(59, 130, 246, 0.8)'); // blue-500

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'จำนวนกล้อง',
                            data: data,
                            backgroundColor: gradient,
                            borderRadius: 6,
                            borderSkipped: false,
                            barThickness: 'flex',
                            maxBarThickness: 24
                        }]
                    },
                    options: {
                        indexAxis: 'y', 
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                titleFont: { size: 14, family: "'Kanit', sans-serif" },
                                bodyFont: { size: 14, family: "'Kanit', sans-serif" },
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false
                            }
                        },
                        scales: { 
                            x: { 
                                beginAtZero: true,
                                grid: { 
                                    color: 'rgba(241, 245, 249, 1)',
                                    drawBorder: false,
                                    borderDash: [5, 5]
                                },
                                border: { display: false }
                            },
                            y: { 
                                grid: { display: false, drawBorder: false },
                                ticks: {
                                    font: { size: 11 }
                                }
                            } 
                        },
                        animation: {
                            x: { duration: 2000, easing: 'easeOutQuart' }
                        }
                    }
                });
            }

            function renderTable(recentData) {
                const tbody = document.getElementById('recent-table-body');
                tbody.innerHTML = '';
                
                if (recentData.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400 font-medium">ยังไม่มีข้อมูล</td></tr>';
                    return;
                }

                recentData.forEach((item, idx) => {
                    // Stagger animation for rows
                    const delay = (idx % 10) * 50;
                    
                    const tr = document.createElement('tr');
                    tr.className = `hover:bg-indigo-50/50 transition-colors duration-200 opacity-0 animate-slide-in-right`;
                    tr.style.animationDelay = `${delay}ms`;
                    
                    tr.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-800">${item.station_id || '-'}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-700">${item.location_name || '-'}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200 shadow-sm">
                                ${item.affiliation || '-'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 bg-gray-100 px-2.5 py-1 rounded-md">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                ${item.type_name || 'ไม่ระบุ'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-600 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                ${item.district_name || '-'}
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            
            // Helper for number animation
            function animateValue(id, start, end, duration) {
                if (start === end) {
                    document.getElementById(id).innerHTML = end;
                    return;
                }
                const obj = document.getElementById(id);
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    // easeOutQuart
                    const easeProgress = 1 - Math.pow(1 - progress, 4);
                    obj.innerHTML = Math.floor(easeProgress * (end - start) + start);
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    } else {
                        obj.innerHTML = end;
                    }
                };
                window.requestAnimationFrame(step);
            }
        });
    </script>
</body>
</html>
