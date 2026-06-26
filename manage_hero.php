<?php
require_once 'auth.php';
requireRole([1]); // Only Super Admin

require_once 'db_config.php';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['hero_image'])) {
    $title = $_POST['title'] ?? '';
    
    $uploadDir = 'uploads/hero/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileExt = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileExt, $allowedExt)) {
        $newFilename = md5(uniqid(rand(), true)) . '.' . $fileExt;
        $destPath = $uploadDir . $newFilename;

        if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $destPath)) {
            $stmt = $pdo->prepare("INSERT INTO hero_images (image_path, title, is_active) VALUES (?, ?, 1)");
            $stmt->execute([$newFilename, $title]);
            $_SESSION['success_msg'] = "อัปโหลดภาพสำเร็จ";
        } else {
            $_SESSION['error_msg'] = "ไม่สามารถบันทึกไฟล์ได้";
        }
    } else {
        $_SESSION['error_msg'] = "รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, WEBP) เท่านั้น";
    }
    header("Location: manage_hero.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM hero_images WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetch();
    if ($img) {
        $path = 'uploads/hero/' . $img['image_path'];
        if (file_exists($path)) unlink($path);
        $pdo->prepare("DELETE FROM hero_images WHERE id = ?")->execute([$id]);
        $_SESSION['success_msg'] = "ลบภาพสำเร็จ";
    }
    header("Location: manage_hero.php");
    exit();
}

// Handle toggle active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE hero_images SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage_hero.php");
    exit();
}

// Fetch images
$stmt = $pdo->query("SELECT * FROM hero_images ORDER BY created_at DESC");
$images = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการแบนเนอร์ - CRIME MAP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Kanit', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
    <?php include 'navbar.php'; ?>
    <main class="flex-grow p-6 lg:p-8 max-w-7xl mx-auto w-full">
        <?php 
            $success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
            $error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
            unset($_SESSION['success_msg'], $_SESSION['error_msg']);
        ?>

        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">จัดการรูปภาพแบนเนอร์ (Hero Slider)</h1>
                <p class="text-gray-500 text-sm mt-1">อัปโหลดและจัดการภาพที่จะแสดงผลในหน้าแรกของประชาชน</p>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4 text-gray-700">อัปโหลดภาพแบนเนอร์ใหม่</h2>
            <form action="manage_hero.php" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">หัวข้อ/คำอธิบายภาพ (ถ้ามี)</label>
                    <input type="text" name="title" class="w-full border-gray-300 rounded-md shadow-sm p-2 border focus:border-blue-500 focus:ring focus:ring-blue-200">
                </div>
                <div class="w-full md:w-1/3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">เลือกไฟล์รูปภาพ</label>
                    <input type="file" name="hero_image" accept="image/*" required class="w-full border-gray-300 rounded-md shadow-sm p-1.5 border bg-gray-50">
                </div>
                <div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow text-sm font-medium transition">อัปโหลด</button>
                </div>
            </form>
            <p class="text-xs text-gray-500 mt-2">* ขนาดภาพที่แนะนำ: 1920x600 px (สัดส่วนแบบแนวนอน)</p>
        </div>

        <!-- Images List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($images as $img): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="h-48 bg-gray-200 relative">
                    <img src="uploads/hero/<?= htmlspecialchars($img['image_path']) ?>" class="w-full h-full object-cover">
                    <div class="absolute top-2 right-2 flex gap-2">
                        <a href="manage_hero.php?toggle=<?= $img['id'] ?>" class="px-2 py-1 text-xs font-bold rounded shadow text-white <?= $img['is_active'] ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 hover:bg-gray-500' ?>">
                            <?= $img['is_active'] ? 'เปิดใช้งาน' : 'ซ่อน' ?>
                        </a>
                        <a href="manage_hero.php?delete=<?= $img['id'] ?>" onclick="return confirm('ยืนยันการลบภาพนี้?');" class="px-2 py-1 text-xs font-bold rounded shadow bg-red-500 hover:bg-red-600 text-white">ลบ</a>
                    </div>
                </div>
                <div class="p-4">
                    <p class="font-medium text-gray-800 line-clamp-1"><?= htmlspecialchars($img['title']) ?: '<span class="text-gray-400 italic">ไม่มีหัวข้อ</span>' ?></p>
                    <p class="text-xs text-gray-500 mt-1">อัปโหลดเมื่อ: <?= date('d/m/Y H:i', strtotime($img['created_at'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($images)): ?>
                <div class="col-span-full text-center py-10 text-gray-500">ยังไม่มีภาพแบนเนอร์ในระบบ</div>
            <?php endif; ?>
        </div>
    </main>

    <?php if ($success_msg): ?>
    <script>Swal.fire({icon: 'success', title: 'สำเร็จ', text: <?= json_encode($success_msg) ?>, confirmButtonColor: '#3085d6'});</script>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <script>Swal.fire({icon: 'error', title: 'เกิดข้อผิดพลาด', text: <?= json_encode($error_msg) ?>, confirmButtonColor: '#d33'});</script>
    <?php endif; ?>
</body>
</html>
