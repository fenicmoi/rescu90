<?php
require_once 'auth.php';
requireRole([1, 2]); // Admins and Governor

require_once 'db_config.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการประเภทกล้องวงจรปิด - CRIME MAP</title>
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

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-6 lg:p-8 max-w-7xl mx-auto w-full">
        <?php 
            $success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
            $error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
            unset($_SESSION['success_msg'], $_SESSION['error_msg']);
        ?>

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">จัดการประเภทกล้องวงจรปิด</h1>
                <p class="text-gray-500 text-sm mt-1">ตั้งค่าประเภทของกล้อง CCTV (Master Data)</p>
            </div>
            <a href="camera_type_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                เพิ่มประเภทกล้อง
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 overflow-hidden">
            <table class="display responsive nowrap w-full" style="width:100%">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ประเภทกล้อง</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">จำนวนกล้องที่ใช้</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Data will be loaded by DataTables Server-Side Processing -->
                </tbody>
            </table>
        </div>
    </main>

    <!-- jQuery & DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'api_dt_camera_types.php',
                    type: 'POST'
                },
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
                }
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

        function deleteType(id, name) {
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: 'คุณต้องการลบประเภท "' + name + '" ใช่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete_camera_type.php?id=' + id;
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
