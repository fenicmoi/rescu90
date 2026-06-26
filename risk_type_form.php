<?php
require_once 'auth.php';
requireRole([1]); // Only Super Admin

require_once 'db_config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = [
    'id' => 0,
    'type_name' => '',
    'marker_color' => '#3B82F6' // Default blue
];
$is_edit = false;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM risk_types WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $fetched = $stmt->fetch();
    if ($fetched) {
        $type = $fetched;
        $is_edit = true;
    }
}

$error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
unset($_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'แก้ไขประเภท' : 'เพิ่มประเภทใหม่' ?> - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Kanit', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-6 lg:p-8 max-w-2xl mx-auto w-full">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800"><?= $is_edit ? 'แก้ไขประเภทความเสี่ยง' : 'เพิ่มประเภทความเสี่ยงใหม่' ?></h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="save_risk_type.php" method="POST">
                <input type="hidden" name="id" value="<?= $type['id'] ?>">
                
                <div class="mb-6">
                    <label for="type_name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อประเภทความเสี่ยง <span class="text-red-500">*</span></label>
                    <input type="text" id="type_name" name="type_name" value="<?= htmlspecialchars($type['type_name']) ?>" required placeholder="เช่น ยาเสพติด, อาวุธปืน" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>

                <div class="mb-6">
                    <label for="marker_color" class="block text-sm font-medium text-gray-700 mb-2">สีสัญลักษณ์ (Marker Color) <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-4">
                        <input type="color" id="marker_color" name="marker_color" value="<?= htmlspecialchars($type['marker_color']) ?>" required class="h-10 w-20 cursor-pointer border-0 p-0 rounded">
                        <span class="text-sm text-gray-500" id="color_hex"><?= htmlspecialchars($type['marker_color']) ?></span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">คลิกที่แถบสีเพื่อเลือกสีที่ต้องการให้แสดงบนแผนที่</p>
                </div>

                <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                    <a href="manage_risk_types.php" class="px-4 py-2 text-gray-600 hover:text-gray-900 font-medium">ยกเลิก</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow text-sm font-medium transition">
                        <?= $is_edit ? 'บันทึกการแก้ไข' : 'สร้างประเภทใหม่' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('marker_color').addEventListener('input', function(e) {
            document.getElementById('color_hex').textContent = e.target.value.toUpperCase();
        });
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
