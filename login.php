<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ศูนย์ข้อมูลอัจฉริยะพัทลุงปลอดภัย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
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
<body class="bg-blue-900 flex items-center justify-center min-h-screen relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50"></div>

    <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md z-10 relative">
        <div class="text-center mb-8">
            <div class="text-5xl mb-4">🛡️</div>
            <h1 class="text-2xl font-bold text-gray-800">ศูนย์ข้อมูลอัจฉริยะพัทลุงปลอดภัย</h1>
            <p class="text-sm font-bold text-red-500 animate-pulse mt-2">สำหรับทดลอง (Demo Version)</p>
            <p class="text-sm text-gray-500 mt-1">เข้าสู่ระบบเพื่อจัดการข้อมูล</p>
        </div>



        <form action="login_action.php" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">ชื่อผู้ใช้งาน (Username)</label>
                <input type="text" name="username" id="username" required 
                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่าน (Password)</label>
                <input type="password" name="password" id="password" required 
                       class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            </div>

            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                เข้าสู่ระบบ
            </button>
        </form>

        <div class="mt-8 text-center text-xs text-gray-600 border-t pt-4 bg-gray-50 rounded-lg p-3">
            <p class="mb-2 font-bold text-gray-800">ทดสอบระบบ (Demo Accounts):</p>
            <div class="grid grid-cols-2 gap-2 text-left w-full mx-auto">
                <div><strong>admin / admin</strong></div><div class="text-gray-500">Super Admin</div>
                <div><strong>governor / governor</strong></div><div class="text-gray-500">ผู้ว่าฯ (ดูภาพรวม)</div>
                <div><strong>chief_mueang / chief_mueang</strong></div><div class="text-gray-500">นายอำเภอ (อัปเดตสถานะ)</div>
                <div><strong>officer_mueang / officer_mueang</strong></div><div class="text-gray-500">เจ้าหน้าที่ (แจ้งจุดเสี่ยง)</div>
            </div>
        </div>

        <div class="mt-6">
            <a href="public_dashboard.php" class="w-full flex justify-center py-3 px-4 border border-blue-600 rounded-lg shadow-sm text-sm font-medium text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                📊 สำหรับประชาชน (ดูสถิติ/ข้อมูลสาธารณะ)
            </a>
        </div>

        <div class="mt-4 text-center text-sm font-medium text-gray-500">
            พัฒนาโดย <span class="font-bold text-blue-700">จังหวัดพัทลุง</span>
        </div>
    </div>

    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'ไม่สามารถเข้าสู่ระบบได้',
            text: <?= json_encode($error) ?>,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'ลองใหม่อีกครั้ง'
        });
    </script>
    <?php endif; ?>
</body>
</html>
