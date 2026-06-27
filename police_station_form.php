<?php
require_once 'auth.php';
requireRole([1]);

require_once 'db_config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = $id > 0 ? "แก้ไข" : "เพิ่ม";
$row = [
    'station_code' => '',
    'station_name' => '',
    'district_id' => ''
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM police_stations WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        $_SESSION['error_msg'] = "ไม่พบข้อมูลสถานีตำรวจ";
        header("Location: manage_police_stations.php");
        exit;
    }
}

// Fetch districts
$stmtDistricts = $pdo->query("SELECT id, name_th FROM districts ORDER BY name_th");
$districts = $stmtDistricts->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $action ?>สถานีตำรวจ - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Kanit', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
    <?php include 'navbar.php'; ?>
    <main class="flex-grow p-6 lg:p-8 max-w-2xl mx-auto w-full">
        <div class="mb-6">
            <a href="manage_police_stations.php" class="text-blue-600 hover:underline text-sm font-medium">&larr; กลับหน้ารายการ</a>
            <h1 class="text-2xl font-bold text-gray-800 mt-2"><?= $action ?>สถานีตำรวจ</h1>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-xl shadow-sm border border-gray-100">
            <form action="save_police_station.php" method="POST">
                <?php if ($id > 0): ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                <?php endif; ?>
                
                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">รหัสสถานี <span class="text-red-500">*</span></label>
                    <input type="text" name="station_code" value="<?= htmlspecialchars($row['station_code']) ?>" required
                           placeholder="เช่น 71159"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-2">ชื่อสถานีตำรวจ <span class="text-red-500">*</span></label>
                    <input type="text" name="station_name" value="<?= htmlspecialchars($row['station_name']) ?>" required
                           placeholder="เช่น สภ.เมืองพัทลุง"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">อำเภอที่ตั้ง <span class="text-red-500">*</span></label>
                    <select name="district_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        <option value="">-- เลือกอำเภอ --</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $row['district_id'] == $d['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['name_th']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="manage_police_stations.php" class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        ยกเลิก
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 shadow-sm transition-colors">
                        บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
