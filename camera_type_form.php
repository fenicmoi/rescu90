<?php
require_once 'auth.php';
requireRole([1, 2]); // Admins and Governor

require_once 'db_config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = $id > 0 ? "แก้ไข" : "เพิ่ม";
$row = [
    'type_name' => ''
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM camera_types WHERE id = ?");
    $stmt->execute([$id]);
    $fetched = $stmt->fetch();
    if ($fetched) {
        $row = $fetched;
    } else {
        $_SESSION['error_msg'] = "ไม่พบข้อมูลประเภทกล้อง";
        header("Location: manage_camera_types.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action ?>ประเภทกล้องวงจรปิด - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Kanit', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">

    <?php include 'navbar.php'; ?>

    <main class="flex-grow p-6 lg:p-8 max-w-3xl mx-auto w-full">
        <div class="mb-6 flex items-center gap-4">
            <a href="manage_camera_types.php" class="text-gray-500 hover:text-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= $action ?>ประเภทกล้องวงจรปิด</h1>
                <p class="text-gray-500 text-sm mt-1">จัดการชื่อประเภทกล้อง CCTV ในระบบ</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <form action="save_camera_type.php" method="POST" class="p-6">
                <?php if ($id > 0): ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                <?php endif; ?>
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">ประเภทกล้องวงจรปิด <span class="text-red-500">*</span></label>
                    <input type="text" name="type_name" value="<?= htmlspecialchars($row['type_name']) ?>" required
                           placeholder="เช่น IP Camera, Analog Camera"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="mt-8 flex justify-end gap-3 pt-5 border-t border-gray-100">
                    <a href="manage_camera_types.php" class="px-5 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">ยกเลิก</a>
                    <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                        💾 บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <footer class="mt-auto py-4 text-center text-sm text-gray-500 bg-white border-t border-gray-200">
        พัฒนาโดย <span class="font-bold text-blue-700">จังหวัดพัทลุง</span>
    </footer>
</body>
</html>
