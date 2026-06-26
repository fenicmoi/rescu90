<?php
// my_reports.php
require_once 'auth.php';
require_once 'db_config.php';

// Only allow Admin, District Chief, Officer
requireRole([1, 3, 4]);

// ดึงข้อมูลจุดเสี่ยง
$sqlRisks = "
    SELECT r.id, r.location_name, r.status, r.created_at, rt.type_name, d.name_th as district_name, r.image_before, r.image_after,
           r.details, r.preventive_measures, r.incident_date, r.latitude, r.longitude, r.reporter_name, r.reporter_phone
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
    $sqlRisks .= " AND (r.reported_by = :user_id OR r.district_id = :dist_id)";
    $paramsRisks[':user_id'] = $user_id;
    $paramsRisks[':dist_id'] = $user_district_id;
}
$sqlRisks .= " ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sqlRisks);
$stmt->execute($paramsRisks);
$risks = $stmt->fetchAll();

// ดึงข้อมูลบ้านเป้าหมาย
$sqlTargets = "
    SELECT t.id, t.house_name, t.status, t.created_at, tt.type_name, d.name_th as district_name, t.image_before, t.image_after,
           t.details, t.preventive_measures, t.incident_date, t.latitude, t.longitude, t.reporter_name, t.reporter_phone
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
    $sqlTargets .= " AND (t.reported_by = :user_id OR t.district_id = :dist_id)";
    $paramsTargets[':user_id'] = $user_id;
    $paramsTargets[':dist_id'] = $user_district_id;
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
    <title>จัดการข้อมูลของฉัน - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- SweetAlert2 สำหรับแจ้งเตือน -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery & DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
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

        <div class="mb-6 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg border-blue-600 text-blue-600 font-bold" id="risks-tab" type="button" role="tab" onclick="switchTab('risks')">📍 จัดการข้อมูลจุดเสี่ยงเชิงพื้นที่ (<?= count($risks) ?>)</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg border-transparent hover:text-gray-600 hover:border-gray-300 font-bold" id="targets-tab" type="button" role="tab" onclick="switchTab('targets')">🏠 จัดการข้อมูลบ้านเป้าหมาย (<?= count($targets) ?>)</button>
                </li>
            </ul>
        </div>

        <div>
            <!-- Risks Tab -->
            <div class="block" id="risks" role="tabpanel">
                <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                    <div class="overflow-x-auto w-full">
                        <table id="tableRisks" class="min-w-full divide-y divide-gray-200" style="width:100%">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานที่</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อำเภอ</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php // DataTables handles empty table automatically ?>
                            <?php foreach($risks as $r): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="javascript:void(0)" onclick="showDetailsModal(<?= htmlspecialchars(json_encode($r)) ?>)" class="text-blue-600 hover:text-blue-800 underline">
                                        <?= htmlspecialchars($r['location_name']) ?>
                                    </a>
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
                                    <?php elseif($r['status'] === 'pending'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 animate-pulse">รออนุมัติ</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">มีความเสี่ยง</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex flex-col gap-1">
                                        <?php if($r['status'] === 'pending'): ?>
                                            <button onclick="updateStatus('risk', <?= $r['id'] ?>, 'active')" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded shadow-sm text-xs transition w-full">อนุมัติ</button>
                                            <a href="edit_report.php?type=risk&id=<?= $r['id'] ?>" class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded shadow-sm text-xs transition w-full text-center">แก้ไข</a>
                                            <button onclick="updateStatus('risk', <?= $r['id'] ?>, 'delete')" class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded shadow-sm text-xs transition w-full">ลบทิ้ง</button>
                                        <?php elseif($r['status'] === 'active'): ?>
                                            <a href="edit_report.php?type=risk&id=<?= $r['id'] ?>" class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded shadow-sm text-xs transition w-full text-center">แก้ไข</a>
                                            <button onclick="updateStatus('risk', <?= $r['id'] ?>, 'resolved')" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded shadow-sm text-xs transition w-full">มาร์คว่าปลอดภัย</button>
                                        <?php else: ?>
                                            <button onclick="updateStatus('risk', <?= $r['id'] ?>, 'active')" class="text-gray-500 hover:text-gray-700 underline text-xs w-full">ยกเลิกผลการดำเนินการ</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Targets Tab -->
            <div class="hidden" id="targets" role="tabpanel">
                <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                    <div class="overflow-x-auto w-full">
                        <table id="tableTargets" class="min-w-full divide-y divide-gray-200" style="width:100%">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เป้าหมาย</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อำเภอ</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php // DataTables handles empty table automatically ?>
                            <?php foreach($targets as $t): ?>
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="javascript:void(0)" onclick="showDetailsModal(<?= htmlspecialchars(json_encode($t)) ?>)" class="text-blue-600 hover:text-blue-800 underline">
                                        <?= htmlspecialchars($t['house_name']) ?>
                                    </a>
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
                                    <?php elseif($t['status'] === 'pending'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 animate-pulse">รออนุมัติ</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">รอตรวจสอบ</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex flex-col gap-1">
                                        <?php if($t['status'] === 'pending'): ?>
                                            <button onclick="updateStatus('target', <?= $t['id'] ?>, 'active')" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded shadow-sm text-xs transition w-full">อนุมัติ</button>
                                            <a href="edit_report.php?type=target&id=<?= $t['id'] ?>" class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded shadow-sm text-xs transition w-full text-center">แก้ไข</a>
                                            <button onclick="updateStatus('target', <?= $t['id'] ?>, 'delete')" class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded shadow-sm text-xs transition w-full">ลบทิ้ง</button>
                                        <?php elseif($t['status'] === 'active'): ?>
                                            <a href="edit_report.php?type=target&id=<?= $t['id'] ?>" class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded shadow-sm text-xs transition w-full text-center">แก้ไข</a>
                                            <button onclick="updateStatus('target', <?= $t['id'] ?>, 'resolved')" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1 rounded shadow-sm text-xs transition w-full">ดำเนินการแล้ว</button>
                                        <?php else: ?>
                                            <button onclick="updateStatus('target', <?= $t['id'] ?>, 'active')" class="text-gray-500 hover:text-gray-700 underline text-xs w-full">ยกเลิกผลการดำเนินการ</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Details Modal -->
    <div id="detailsModal" class="fixed inset-0 z-[500] hidden flex items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shrink-0">
                <h3 class="text-lg font-bold" id="modalTitle">รายละเอียดสถานที่</h3>
                <button onclick="closeDetailsModal()" class="text-blue-100 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <!-- Body -->
            <div class="p-6 overflow-y-auto grow">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">ประเภท</p>
                        <p class="font-medium text-gray-800" id="modalType">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">วันที่แจ้งเข้าระบบ</p>
                        <p class="font-medium text-gray-800" id="modalDate">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">วันที่เกิดเหตุ / พบเห็น</p>
                        <p class="font-medium text-gray-800" id="modalIncidentDate">-</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">รายละเอียดเพิ่มเติม</p>
                        <p class="font-medium text-gray-800 bg-gray-50 p-3 rounded border border-gray-100" id="modalDetails">-</p>
                    </div>
                    <div class="md:col-span-2 hidden" id="modalMeasuresContainer">
                        <p class="text-sm text-blue-700 font-bold mb-1">🛡️ มาตรการเฝ้าระวังและป้องกันเชิงรุก</p>
                        <p class="font-medium text-blue-900 bg-blue-50 p-3 rounded border border-blue-200" id="modalMeasures">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">ผู้แจ้ง</p>
                        <p class="font-medium text-gray-800" id="modalReporterName">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">เบอร์โทรศัพท์</p>
                        <p class="font-medium text-gray-800" id="modalReporterPhone">-</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 mb-1">พิกัด</p>
                        <p class="font-medium text-gray-800 flex items-center gap-2">
                            <span id="modalCoords">-</span>
                            <a id="modalMapLink" href="#" target="_blank" class="text-blue-600 hover:text-blue-800 underline text-sm ml-2 hidden">
                                🗺️ ดูบน Google Maps
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end border-t border-gray-200 shrink-0">
                <button onclick="closeDetailsModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow-sm font-medium transition-colors">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function updateStatus(type, id, newStatus) {
            let confirmHtml = '';
            if (newStatus === 'resolved') {
                confirmHtml = `<p class="text-sm text-gray-700 mb-4">คุณยืนยันว่าได้ดำเนินการจัดการปัญหานี้แล้วใช่หรือไม่? หมุดจะเปลี่ยนเป็นสีเขียว</p>
                   <div class="text-left mt-4 p-3 bg-gray-50 rounded border border-gray-200">
                       <label for="swal-image-after" class="block text-sm font-medium text-gray-700 mb-2">แนบรูปภาพหลังแก้ไข (ถ้ามี)</label>
                       <input type="file" id="swal-image-after" accept="image/*" capture="environment" class="block w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                   </div>`;
            } else if (newStatus === 'delete') {
                confirmHtml = `<p class="text-sm text-red-600">คุณต้องการลบรายการที่รออนุมัตินี้ทิ้งใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้</p>`;
            } else if (newStatus === 'active') {
                confirmHtml = `<p class="text-sm text-gray-700">ยืนยันการตั้งค่าสถานะเป็น Active / อนุมัติใช่หรือไม่?</p>`;
            }
            
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

        // Modal Logic
        function showDetailsModal(data) {
            const isTarget = data.house_name !== undefined;
            document.getElementById('modalTitle').textContent = isTarget ? data.house_name : data.location_name;
            document.getElementById('modalType').textContent = data.type_name || '-';
            document.getElementById('modalDate').textContent = data.created_at || '-';
            document.getElementById('modalIncidentDate').textContent = data.incident_date || 'ไม่ระบุ';
            document.getElementById('modalDetails').textContent = data.details || 'ไม่มีรายละเอียด';
            
            const measuresContainer = document.getElementById('modalMeasuresContainer');
            if (data.preventive_measures && data.preventive_measures.trim() !== '') {
                document.getElementById('modalMeasures').textContent = data.preventive_measures;
                measuresContainer.classList.remove('hidden');
            } else {
                measuresContainer.classList.add('hidden');
            }

            document.getElementById('modalReporterName').textContent = data.reporter_name || 'ไม่ประสงค์ออกนาม';
            document.getElementById('modalReporterPhone').textContent = data.reporter_phone || '-';
            
            const coordsText = document.getElementById('modalCoords');
            const mapLink = document.getElementById('modalMapLink');
            if (data.latitude && data.longitude) {
                coordsText.textContent = `${data.latitude}, ${data.longitude}`;
                mapLink.href = `https://www.google.com/maps?q=${data.latitude},${data.longitude}`;
                mapLink.classList.remove('hidden');
            } else {
                coordsText.textContent = 'ไม่มีข้อมูลพิกัด';
                mapLink.classList.add('hidden');
            }

            document.getElementById('detailsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Tab Switching Logic
        function switchTab(tabId) {
            const tabs = ['risks', 'targets'];
            tabs.forEach(id => {
                const tabEl = document.getElementById(id);
                const btnEl = document.getElementById(id + '-tab');
                if (id === tabId) {
                    tabEl.classList.remove('hidden');
                    tabEl.classList.add('block');
                    btnEl.classList.add('border-blue-600', 'text-blue-600');
                    btnEl.classList.remove('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                } else {
                    tabEl.classList.add('hidden');
                    tabEl.classList.remove('block');
                    btnEl.classList.remove('border-blue-600', 'text-blue-600');
                    btnEl.classList.add('border-transparent', 'hover:text-gray-600', 'hover:border-gray-300');
                }
            });
        }

        // Initialize DataTables
        $(document).ready(function() {
            const dtOptions = {
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
                    paginate: {
                        next: 'ถัดไป',
                        previous: 'ก่อนหน้า'
                    }
                },
                columnDefs: [
                    { orderable: false, targets: -1 } // Disable sorting on the 'Actions' column
                ]
            };
            $('#tableRisks').DataTable(dtOptions);
            $('#tableTargets').DataTable(dtOptions);
        });
    </script>
    <!-- Footer -->
    <footer class="mt-auto py-4 text-center text-sm text-gray-500 bg-white border-t border-gray-200">
        พัฒนาโดย <span class="font-bold text-blue-700">จังหวัดพัทลุง</span>
    </footer>
</body>
</html>
