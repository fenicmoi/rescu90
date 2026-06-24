<?php
require_once 'auth.php';
requireRole([1]); // Only Super Admin

require_once 'db_config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = [
    'id' => 0,
    'username' => '',
    'name' => '',
    'agency' => '',
    'position' => '',
    'phone' => '',
    'role_id' => '',
    'district_id' => ''
];
$is_edit = false;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT id, username, name, agency, position, phone, role_id, district_id FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $fetched = $stmt->fetch();
    if ($fetched) {
        $user = $fetched;
        $is_edit = true;
    }
}

// Fetch roles
$stmtRoles = $pdo->query("SELECT id, role_name, description FROM roles ORDER BY id ASC");
$roles = $stmtRoles->fetchAll();

// Fetch districts
$stmtDistricts = $pdo->query("SELECT id, name_th FROM districts ORDER BY name_th ASC");
$districts = $stmtDistricts->fetchAll();

$error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
unset($_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'แก้ไขผู้ใช้' : 'เพิ่มผู้ใช้ใหม่' ?> - ศูนย์ข้อมูลอัจฉริยะพัทลุงปลอดภัย</title>
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

    <main class="flex-grow p-6 lg:p-8 max-w-3xl mx-auto w-full">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800"><?= $is_edit ? 'แก้ไขข้อมูลผู้ใช้' : 'เพิ่มผู้ใช้ใหม่' ?></h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="save_user.php" method="POST">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username (สำหรับเข้าสู่ระบบ)</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200" <?= $is_edit ? 'readonly class="bg-gray-100"' : '' ?>>
                        <?php if($is_edit): ?>
                            <p class="text-xs text-gray-500 mt-1">ไม่อนุญาตให้แก้ไข Username</p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password (รหัสผ่าน)</label>
                        <input type="password" id="password" name="password" <?= !$is_edit ? 'required' : '' ?> class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                        <?php if($is_edit): ?>
                            <p class="text-xs text-blue-600 mt-1">ปล่อยว่างไว้หากต้องการใช้รหัสผ่านเดิม</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อ - นามสกุล</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="agency" class="block text-sm font-medium text-gray-700 mb-2">หน่วยงาน</label>
                        <input type="text" id="agency" name="agency" value="<?= htmlspecialchars($user['agency'] ?? '') ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-2">ตำแหน่ง</label>
                        <input type="text" id="position" name="position" value="<?= htmlspecialchars($user['position'] ?? '') ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">เบอร์โทรศัพท์</label>
                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">ระดับสิทธิ์ (Role)</label>
                        <select id="role_id" name="role_id" required class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200" onchange="toggleDistrictField()">
                            <option value="">-- เลือกระดับสิทธิ์ --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $user['role_id'] == $r['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['role_name']) ?> - <?= htmlspecialchars($r['description']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div id="district_container" style="<?= in_array($user['role_id'], [3, 4]) ? 'display:block;' : 'display:none;' ?>">
                        <label for="district_id" class="block text-sm font-medium text-gray-700 mb-2">อำเภอที่รับผิดชอบ</label>
                        <select id="district_id" name="district_id" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <option value="">-- ระบุอำเภอ --</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= $user['district_id'] == $d['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['name_th']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">ต้องระบุสำหรับสิทธิ์ระดับ 3 และ 4</p>
                    </div>
                </div>

                <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                    <a href="manage_users.php" class="px-4 py-2 text-gray-600 hover:text-gray-900 font-medium">ยกเลิก</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow text-sm font-medium transition">
                        <?= $is_edit ? 'บันทึกการแก้ไข' : 'สร้างผู้ใช้ใหม่' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function toggleDistrictField() {
            const roleSelect = document.getElementById('role_id');
            const districtContainer = document.getElementById('district_container');
            const districtSelect = document.getElementById('district_id');
            
            // สิทธิ์ 3(นายอำเภอ) และ 4(เจ้าหน้าที่) ต้องระบุอำเภอ
            if (roleSelect.value === '3' || roleSelect.value === '4') {
                districtContainer.style.display = 'block';
                districtSelect.required = true;
            } else {
                districtContainer.style.display = 'none';
                districtSelect.required = false;
                districtSelect.value = ''; // เคลียร์ค่า
            }
        }
        
        // รันครั้งแรกตอนโหลดหน้า
        document.addEventListener('DOMContentLoaded', toggleDistrictField);
    </script>
    
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
