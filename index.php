<?php
require_once 'db_config.php';

// Fetch frontend menus
$stmt = $pdo->query("SELECT * FROM menus WHERE menu_type = 'frontend' AND is_active = 1 ORDER BY order_num ASC");
$frontend_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลสถิติสาธารณะ - CRIME MAP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Google Fonts (Kanit) -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    <!-- Public Navbar -->
    <nav class="bg-blue-800 text-white shadow-lg shrink-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <span class="text-lg sm:text-xl font-bold tracking-wide truncate">🛡️ CRIME MAP</span>
                </div>
                <!-- Desktop Menu -->
                <div class="flex items-center gap-2 lg:gap-4">
                    <?php foreach ($frontend_menus as $menu): ?>
                        <a href="<?= htmlspecialchars($menu['url']) ?>" class="<?= htmlspecialchars($menu['css_class'] ?? '') ?>">
                            <?php if ($menu['icon']): ?><i class="<?= htmlspecialchars($menu['icon']) ?> mr-1"></i><?php endif; ?>
                            <?= htmlspecialchars($menu['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Slider Section -->
    <div class="w-full relative bg-gray-900" style="height: 60vh; min-height: 400px;">
        <div class="swiper heroSwiper w-full h-full">
            <div class="swiper-wrapper" id="hero-wrapper">
                <!-- Default Slide in case no images or loading -->
                <div class="swiper-slide w-full h-full relative flex items-center justify-center bg-blue-900">
                    <div class="absolute inset-0 bg-blue-900 opacity-80 mix-blend-multiply"></div>
                    <div class="relative z-10 text-center text-white px-4">
                        <h1 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">พัทลุงปลอดภัย</h1>
                        <p class="text-lg md:text-xl max-w-2xl mx-auto drop-shadow-md">ร่วมสร้างชุมชนให้น่าอยู่ และปลอดภัยไปด้วยกัน</p>
                    </div>
                </div>
            </div>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
            <!-- Add Navigation -->
            <div class="swiper-button-next !text-white"></div>
            <div class="swiper-button-prev !text-white"></div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow p-6 lg:p-8 max-w-7xl mx-auto w-full">
        
        <div class="mb-6 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">สรุปภาพรวมสถานการณ์จุดเสี่ยง (สำหรับประชาชน)</h1>
                <p class="text-gray-500 text-sm mt-1">ข้อมูลเชิงสถิติและการกระจายตัวของเหตุการณ์ในจังหวัดพัทลุง</p>
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



    </main>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load Hero Images
            fetch('api_hero_images.php')
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success' && result.data.length > 0) {
                        const wrapper = document.getElementById('hero-wrapper');
                        wrapper.innerHTML = ''; // clear default
                        result.data.forEach(img => {
                            const titleHtml = img.title ? `<div class="absolute bottom-10 left-0 right-0 text-center"><h2 class="text-3xl font-bold text-white drop-shadow-lg bg-black/40 inline-block px-6 py-2 rounded-lg">${img.title}</h2></div>` : '';
                            wrapper.innerHTML += `
                                <div class="swiper-slide w-full h-full relative">
                                    <img src="uploads/hero/${img.image_path}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
                                    ${titleHtml}
                                </div>
                            `;
                        });
                    }
                    // Initialize Swiper after loading images
                    new Swiper('.heroSwiper', {
                        loop: true,
                        autoplay: { delay: 5000, disableOnInteraction: false },
                        pagination: { el: '.swiper-pagination', clickable: true },
                        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                        effect: 'fade',
                        fadeEffect: { crossFade: true }
                    });
                })
                .catch(err => {
                    console.error('Error loading hero images:', err);
                    new Swiper('.heroSwiper', { loop: true }); // init with default slide
                });

            // Fetch data from API
            fetch('api_public_dashboard.php')
                .then(res => res.json())
                .then(result => {
                    if (result.status === 'success') {
                        const data = result.data;
                        renderCards(data.total, data.resolved, data.by_type, data.target_total, data.target_resolved, data.target_by_type);
                        renderDistrictChart(data.by_district);
                        renderTypeChart(data.by_type);
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


        });
    </script>
    <!-- Footer -->
    <footer class="mt-8 py-4 text-center text-sm text-gray-500 bg-white border-t border-gray-200">
        พัฒนาโดย <span class="font-bold text-blue-700">จังหวัดพัทลุง</span>
    </footer>
</body>
</html>
