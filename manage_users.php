<?php
require_once 'auth.php';
requireRole([1]); // Only Super Admin

require_once 'db_config.php';

try {
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.name, u.agency, u.position, u.phone, r.role_name, u.district_id, d.name_th as district_name, u.created_at 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        LEFT JOIN districts d ON u.district_id = d.id
        ORDER BY u.id ASC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการผู้ใช้งานระบบ - ศูนย์ข้อมูลอัจฉริยะพัทลุงปลอดภัย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Kanit', sans-serif; }</style>
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
                <h1 class="text-2xl font-bold text-gray-800">จัดการผู้ใช้งานระบบ</h1>
                <p class="text-gray-500 text-sm mt-1">รายชื่อบัญชีผู้ใช้ทั้งหมดและสิทธิ์การเข้าถึง (Role-Based Access Control)</p>
            </div>
            <a href="user_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                เพิ่มผู้ใช้ใหม่
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ-สกุล / ตำแหน่ง</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ติดต่อ</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">บัญชีเข้าใช้งาน</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อำเภอที่รับผิดชอบ</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ระดับสิทธิ์ (Role)</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สร้าง</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= $user['id'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></div>
                            <div class="text-xs text-gray-500"><?= htmlspecialchars($user['position'] ?? '') ?><?= !empty($user['agency']) ? ' (' . htmlspecialchars($user['agency']) . ')' : '' ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?= htmlspecialchars($user['phone'] ?? '-') ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($user['username']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?= $user['district_name'] ? 'อ.' . htmlspecialchars($user['district_name']) : '<span class="text-gray-300">ไม่ระบุ / ทั้งจังหวัด</span>' ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                                $roleClass = 'bg-gray-100 text-gray-800';
                                if ($user['role_name'] == 'Admin') $roleClass = 'bg-purple-100 text-purple-800';
                                if ($user['role_name'] == 'Governor') $roleClass = 'bg-blue-100 text-blue-800';
                                if ($user['role_name'] == 'District Chief') $roleClass = 'bg-green-100 text-green-800';
                                if ($user['role_name'] == 'Officer') $roleClass = 'bg-yellow-100 text-yellow-800';
                            ?>
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $roleClass ?>">
                                <?= htmlspecialchars($user['role_name']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('d M Y, H:i', strtotime($user['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="user_form.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">✏️ แก้ไข</a>
                            <?php if ($user['id'] != $user_id): ?>
                                <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')" class="text-red-600 hover:text-red-900">🗑️ ลบ</button>
                            <?php else: ?>
                                <span class="text-gray-300 cursor-not-allowed" title="ไม่สามารถลบบัญชีตัวเองได้">🗑️ ลบ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function deleteUser(id, username) {
            Swal.fire({
                title: 'ยืนยันการลบผู้ใช้',
                text: 'คุณต้องการลบผู้ใช้งาน ' + username + ' ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้',
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
                    form.action = 'delete_user.php';
                    
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
