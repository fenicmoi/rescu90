<?php
require_once 'auth.php';
requireRole([1, 2, 3, 4]); // Admins and Officers

require_once 'db_config.php';

try {
    // No longer fetching all data here, will use server-side processing via api_dt_cctv.php
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลกล้องวงจรปิด (CCTV) - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Kanit', sans-serif; }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin-left: 0.5rem;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.25rem 2rem 0.25rem 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">

    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-6 lg:p-8 max-w-7xl mx-auto w-full">
        <?php 
            $success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
            $error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
            unset($_SESSION['success_msg'], $_SESSION['error_msg']);
        ?>

        <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">🎥 จัดการข้อมูลกล้องวงจรปิด (CCTV)</h1>
                <p class="text-gray-500 text-sm mt-1">เพิ่ม แก้ไข และลบ พิกัดกล้องวงจรปิดในระบบ</p>
            </div>
            <div class="flex gap-2">
                <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition flex items-center gap-2">
                    📤 Import CSV
                </button>
                <a href="cctv_form.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    เพิ่มกล้อง CCTV
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <table id="tableCctv" class="display responsive nowrap w-full" style="width:100%">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase">รหัสสถานี</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase">สังกัด / สถานี</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase">ประเภท</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase">จุดที่ติดตั้ง</th>
                        <th class="text-center text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded by DataTables Server-Side Processing -->
                </tbody>
            </table>
        </div>
    </main>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-2">📤 อัปโหลดไฟล์ CSV</h3>
                <div class="mt-2 px-2 py-3">
                    <p class="text-sm text-gray-500 mb-4">อัปโหลดไฟล์ CSV ที่มีข้อมูลพิกัดกล้อง CCTV</p>
                    <a href="download_cctv_template.php" class="text-blue-600 hover:underline text-sm mb-4 inline-block font-medium">⬇️ ดาวน์โหลดไฟล์ Template (.csv)</a>
                    
                    <form action="import_cctv_upload.php" method="POST" enctype="multipart/form-data" class="mt-4">
                        <input type="file" name="csv_file" accept=".csv" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-6 border p-2 rounded"/>
                        
                        <div class="flex justify-center gap-3">
                            <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-5 py-2.5 bg-gray-200 text-gray-800 font-medium rounded hover:bg-gray-300 transition">ยกเลิก</button>
                            <button type="submit" class="px-5 py-2.5 bg-green-600 text-white font-medium rounded hover:bg-green-700 transition shadow">นำเข้าข้อมูล</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery & DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tableCctv').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'api_dt_cctv.php',
                    type: 'POST'
                },
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
                },
                order: [[0, 'desc']]
            });

            <?php if($success_msg): ?>
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: '<?= htmlspecialchars($success_msg) ?>',
                timer: 2000,
                showConfirmButton: false
            });
            <?php endif; ?>

            <?php if($error_msg): ?>
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: '<?= htmlspecialchars($error_msg) ?>'
            });
            <?php endif; ?>
        });

        function deleteCctv(id) {
            Swal.fire({
                title: 'ยืนยันการลบ?',
                text: "คุณต้องการลบกล้อง CCTV นี้ใช่หรือไม่? (ไม่สามารถกู้คืนได้)",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_cctv.php?id=' + id;
                }
            });
        }
    </script>
</body>
</html>
