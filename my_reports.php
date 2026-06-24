<?php
// my_reports.php
require_once 'auth.php';
require_once 'db_config.php';

// Only allow Admin, District Chief, Officer
requireRole([1, 3, 4]);

// ดึงข้อมูลจุดเสี่ยง
$sqlRisks = "
    SELECT r.id, r.location_name, r.status, r.created_at, rt.type_name, d.name_th as district_name, r.image_before, r.image_after 
    FROM risk_locations r
    LEFT JOIN risk_types rt ON r.risk_type_id = rt.id
    LEFT JOIN districts d ON r.district_id = d.id
    WHERE 1=1
";
$paramsRisks = [];
if ($user_role_id == 3) {
    $sqlRisks .= " AND r.district_id = :dist_id";
    $paramsRisks[':dist_id'] = $user_district_id;
} elseif ($user_role_id == 4) {
    $sqlRisks .= " AND r.reported_by = :user_id";
    $paramsRisks[':user_id'] = $user_id;
}
$sqlRisks .= " ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sqlRisks);
$stmt->execute($paramsRisks);
$risks = $stmt->fetchAll();

// ดึงข้อมูลบ้านเป้าหมาย
$sqlTargets = "
    SELECT t.id, t.house_name, t.status, t.created_at, tt.type_name, d.name_th as district_name, t.image_before, t.image_after 
    FROM target_houses t
    LEFT JOIN target_types tt ON t.target_type_id = tt.id
    LEFT JOIN districts d ON t.district_id = d.id
    WHERE 1=1
";
$paramsTargets = [];
if ($user_role_id == 3) {
    $sqlTargets .= " AND t.district_id = :dist_id";
    $paramsTargets[':dist_id'] = $user_district_id;
} elseif ($user_role_id == 4) {
    $sqlTargets .= " AND t.reported_by = :user_id";
    $paramsTargets[':user_id'] = $user_id;
}
$sqlTargets .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($sqlTargets);
$stmt->execute($paramsTargets);
$targets = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลของฉัน - ศูนย์ข้อมูลอัจฉริยะพัทลุงปลอดภัย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- SweetAlert2 สำหรับแจ้งเตือน -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<body class="bg-gray-100 text-gray-800">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">จัดการผลการปฏิบัติงาน</h1>
            <p class="mt-2 text-sm text-gray-600">อัปเดตสถานะของจุดเสี่ยงและบ้านเป้าหมายที่คุณรับผิดชอบ เพื่อให้แผนที่แสดงผลสีเขียว</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Left: Risk Locations -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-blue-800">📍 จุดเสี่ยงเชิงพื้นที่ (<?= count($risks) ?>)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานที่</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อำเภอ</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($risks) === 0): ?>
                            <tr><td colspan="4" class="px-4 py-4 text-center text-gray-500 text-sm">ไม่มีข้อมูล</td></tr>
                            <?php endif; ?>
                            <?php foreach($risks as $r): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($r['location_name']) ?>
                                    <?php if($r['image_before']): ?>
                                        <a href="uploads/<?= $r['image_before'] ?>" target="_blank" class="ml-1 text-blue-500 hover:text-blue-700" title="ดูรูปแจ้งเหตุ">📸</a>
                                    <?php endif; ?>
                                    <?php if($r['image_after']): ?>
                                        <a href="uploads/<?= $r['image_after'] ?>" target="_blank" class="ml-1 text-green-500 hover:text-green-700" title="ดูรูปหลังแก้ไข">📸</a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($r['district_name']) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <?php if($r['status'] === 'resolved'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">ปลอดภัย</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">มีความเสี่ยง</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                    <?php if($r['status'] === 'active'): ?>
                                        <button onclick="updateStatus('risk', <?= $r['id'] ?>, 'resolved')" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded shadow-sm text-xs transition">มาร์คว่าปลอดภัย</button>
                                    <?php else: ?>
                                        <button onclick="updateStatus('risk', <?= $r['id'] ?>, 'active')" class="text-gray-500 hover:text-gray-700 underline text-xs">ยกเลิก</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Target Houses -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                <div class="bg-red-50 px-6 py-4 border-b border-red-100 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-red-800">🏠 บ้านเป้าหมาย (<?= count($targets) ?>)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เป้าหมาย</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อำเภอ</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (count($targets) === 0): ?>
                            <tr><td colspan="4" class="px-4 py-4 text-center text-gray-500 text-sm">ไม่มีข้อมูล</td></tr>
                            <?php endif; ?>
                            <?php foreach($targets as $t): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($t['house_name']) ?>
                                    <?php if($t['image_before']): ?>
                                        <a href="uploads/<?= $t['image_before'] ?>" target="_blank" class="ml-1 text-blue-500 hover:text-blue-700" title="ดูรูปแจ้งเหตุ">📸</a>
                                    <?php endif; ?>
                                    <?php if($t['image_after']): ?>
                                        <a href="uploads/<?= $t['image_after'] ?>" target="_blank" class="ml-1 text-green-500 hover:text-green-700" title="ดูรูปหลังแก้ไข">📸</a>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($t['district_name']) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <?php if($t['status'] === 'resolved'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">ดำเนินการแล้ว</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">รอตรวจสอบ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                    <?php if($t['status'] === 'active'): ?>
                                        <button onclick="updateStatus('target', <?= $t['id'] ?>, 'resolved')" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded shadow-sm text-xs transition">ดำเนินการแล้ว</button>
                                    <?php else: ?>
                                        <button onclick="updateStatus('target', <?= $t['id'] ?>, 'active')" class="text-gray-500 hover:text-gray-700 underline text-xs">ยกเลิก</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(type, id, newStatus) {
            let confirmHtml = newStatus === 'resolved' 
                ? `<p class="text-sm text-gray-700 mb-4">คุณยืนยันว่าได้ดำเนินการจัดการปัญหานี้แล้วใช่หรือไม่? หมุดจะเปลี่ยนเป็นสีเขียว</p>
                   <div class="text-left mt-4 p-3 bg-gray-50 rounded border border-gray-200">
                       <label for="swal-image-after" class="block text-sm font-medium text-gray-700 mb-2">แนบรูปภาพหลังแก้ไข (ถ้ามี)</label>
                       <input type="file" id="swal-image-after" accept="image/*" capture="environment" class="block w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                   </div>` 
                : `<p class="text-sm text-gray-700">คุณต้องการยกเลิกสถานะกลับไปเป็นรอตรวจสอบใช่หรือไม่?</p>`;
            
            Swal.fire({
                title: 'ยืนยันการทำรายการ',
                html: confirmHtml,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                preConfirm: () => {
                    if (newStatus === 'resolved') {
                        const fileInput = document.getElementById('swal-image-after');
                        return fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;
                    }
                    return null;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Call API
                    const formData = new FormData();
                    formData.append('type', type);
                    formData.append('id', id);
                    formData.append('status', newStatus);
                    if (result.value) {
                        formData.append('image_after', result.value);
                    }

                    fetch('update_status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'สำเร็จ!',
                                text: 'อัปเดตสถานะเรียบร้อยแล้ว',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('ข้อผิดพลาด', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                    });
                }
            });
        }
    </script>
    <!-- Footer -->
    <footer class="mt-auto py-4 text-center text-sm text-gray-500 bg-white border-t border-gray-200">
        พัฒนาโดย <span class="font-bold text-blue-700">จังหวัดพัทลุง</span>
    </footer>
</body>
</html>
