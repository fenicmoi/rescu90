<?php
require_once 'auth.php';
requireRole([1]); // Only Super Admin

require_once 'db_config.php';

try {
    $stmt = $pdo->query("
        SELECT r.*, COUNT(l.id) as usage_count 
        FROM risk_types r
        LEFT JOIN risk_locations l ON r.id = l.risk_type_id
        GROUP BY r.id
        ORDER BY r.id ASC
    ");
    $risk_types = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching risk types: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการประเภทความเสี่ยง - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Kanit', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">

    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-6 lg:p-8 max-w-5xl mx-auto w-full">
        <?php 
            $success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
            $error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
            unset($_SESSION['success_msg'], $_SESSION['error_msg']);
        ?>

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">จัดการประเภทความเสี่ยง</h1>
                <p class="text-gray-500 text-sm mt-1">กำหนดประเภทอาชญากรรมและความเสี่ยงในระบบ</p>
            </div>
            <a href="risk_type_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                เพิ่มประเภท
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ประเภทความเสี่ยง</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สี Marker</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">จำนวนจุดเสี่ยงที่ใช้</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($risk_types as $type): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $type['id'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($type['type_name']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full border border-gray-300 shadow-sm" style="background-color: <?= htmlspecialchars($type['marker_color']) ?>"></div>
                                <span class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($type['marker_color']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $type['usage_count'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= $type['usage_count'] ?> หมุด
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="risk_type_form.php?id=<?= $type['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">✏️ แก้ไข</a>
                            <?php if ($type['usage_count'] == 0): ?>
                                <button onclick="deleteType(<?= $type['id'] ?>, '<?= htmlspecialchars($type['type_name']) ?>')" class="text-red-600 hover:text-red-900">🗑️ ลบ</button>
                            <?php else: ?>
                                <span class="text-gray-300 cursor-not-allowed" title="ไม่สามารถลบได้เนื่องจากมีการใช้งานแล้ว">🗑️ ลบ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if(empty($risk_types)): ?>
                <div class="p-8 text-center text-gray-500">ไม่มีข้อมูลประเภทความเสี่ยงในระบบ</div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function deleteType(id, name) {
            Swal.fire({
                title: 'ยืนยันการลบ',
                text: 'คุณต้องการลบประเภทความเสี่ยง "' + name + '" ใช่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ลบข้อมูล',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_risk_type.php';
                    
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    idInput.value = id;
                    
                    form.appendChild(idInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>

    <?php if ($success_msg): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: <?= json_encode($success_msg) ?>,
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    <?php endif; ?>

    <?php if ($error_msg): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'ข้อผิดพลาด',
            text: <?= json_encode($error_msg) ?>
        });
    </script>
    <?php endif; ?>
    
    <!-- Footer -->
    <footer class="mt-auto py-4 text-center text-sm text-gray-500 bg-white border-t border-gray-200">
        พัฒนาโดย <span class="font-bold text-blue-700">จังหวัดพัทลุง</span>
    </footer>
</body>
</html>
