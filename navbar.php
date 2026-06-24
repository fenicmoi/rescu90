<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Navbar -->
<nav class="bg-blue-800 text-white shadow-lg shrink-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <span class="text-lg sm:text-xl font-bold tracking-wide truncate">🛡️ ศูนย์ข้อมูลอัจฉริยะ</span>
            </div>
            <!-- Mobile menu button -->
            <div class="flex md:hidden">
                <button type="button" id="mobile-menu-btn" class="inline-flex items-center justify-center p-2 rounded-md text-blue-200 hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" aria-controls="mobile-menu" aria-expanded="false">
                    <span class="sr-only">Open main menu</span>
                    <!-- Icon when menu is closed -->
                    <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <!-- Icon when menu is open -->
                    <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-2 lg:gap-4">
                <a href="index.php" class="whitespace-nowrap px-3 py-2 rounded-md text-sm font-medium <?= $current_page == 'index.php' ? 'bg-blue-900 text-white shadow-inner' : 'text-blue-200 hover:text-white hover:bg-blue-700 transition' ?>">แผนที่รวม</a>
                
                <?php if (in_array($user_role_id, [1, 3, 4])): ?>
                <a href="add_location.php" class="whitespace-nowrap px-3 py-2 rounded-md text-sm font-medium <?= $current_page == 'add_location.php' ? 'bg-blue-900 text-white shadow-inner' : 'text-blue-200 hover:text-white hover:bg-blue-700 transition' ?>">แจ้งจุดเสี่ยง</a>
                <a href="add_target.php" class="whitespace-nowrap px-3 py-2 rounded-md text-sm font-medium <?= $current_page == 'add_target.php' ? 'bg-blue-900 text-white shadow-inner' : 'text-blue-200 hover:text-white hover:bg-blue-700 transition' ?>">แจ้งบ้านเป้าหมาย</a>
                <a href="my_reports.php" class="whitespace-nowrap px-3 py-2 rounded-md text-sm font-medium <?= $current_page == 'my_reports.php' ? 'bg-blue-900 text-white shadow-inner' : 'text-blue-200 hover:text-white hover:bg-blue-700 transition' ?>">จัดการข้อมูล</a>
                <?php endif; ?>

                <a href="dashboard.php" class="whitespace-nowrap px-3 py-2 rounded-md text-sm font-medium <?= $current_page == 'dashboard.php' ? 'bg-blue-900 text-white shadow-inner' : 'text-blue-200 hover:text-white hover:bg-blue-700 transition' ?>">Dashboard</a>
                
                <?php if ($user_role_id == 1): ?>
                <a href="manage_users.php" class="whitespace-nowrap px-3 py-2 rounded-md text-sm font-medium <?= $current_page == 'manage_users.php' ? 'bg-blue-900 text-white shadow-inner' : 'text-blue-200 hover:text-white hover:bg-blue-700 transition' ?>">จัดการผู้ใช้</a>
                <?php endif; ?>
                
                <div class="ml-2 pl-2 lg:ml-4 lg:pl-4 border-l border-blue-700 flex items-center gap-3">
                    <div class="text-sm text-right">
                        <div class="text-white font-medium whitespace-nowrap"><?= htmlspecialchars($user_name) ?></div>
                        <div class="text-blue-300 text-xs whitespace-nowrap"><?= htmlspecialchars($user_role_name) ?></div>
                    </div>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-medium transition whitespace-nowrap">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-blue-800 shadow-inner">
            <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium <?= $current_page == 'index.php' ? 'bg-blue-900 text-white' : 'text-blue-200 hover:text-white hover:bg-blue-700' ?>">แผนที่รวม</a>
            
            <?php if (in_array($user_role_id, [1, 3, 4])): ?>
            <a href="add_location.php" class="block px-3 py-2 rounded-md text-base font-medium <?= $current_page == 'add_location.php' ? 'bg-blue-900 text-white' : 'text-blue-200 hover:text-white hover:bg-blue-700' ?>">แจ้งจุดเสี่ยง</a>
            <a href="add_target.php" class="block px-3 py-2 rounded-md text-base font-medium <?= $current_page == 'add_target.php' ? 'bg-blue-900 text-white' : 'text-blue-200 hover:text-white hover:bg-blue-700' ?>">แจ้งบ้านเป้าหมาย</a>
            <a href="my_reports.php" class="block px-3 py-2 rounded-md text-base font-medium <?= $current_page == 'my_reports.php' ? 'bg-blue-900 text-white' : 'text-blue-200 hover:text-white hover:bg-blue-700' ?>">จัดการข้อมูล</a>
            <?php endif; ?>

            <a href="dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium <?= $current_page == 'dashboard.php' ? 'bg-blue-900 text-white' : 'text-blue-200 hover:text-white hover:bg-blue-700' ?>">Dashboard</a>
            
            <?php if ($user_role_id == 1): ?>
            <a href="manage_users.php" class="block px-3 py-2 rounded-md text-base font-medium <?= $current_page == 'manage_users.php' ? 'bg-blue-900 text-white' : 'text-blue-200 hover:text-white hover:bg-blue-700' ?>">จัดการผู้ใช้</a>
            <?php endif; ?>
        </div>
        <div class="pt-4 pb-3 border-t border-blue-700 bg-blue-800">
            <div class="flex items-center px-5">
                <div class="ml-3">
                    <div class="text-base font-medium text-white"><?= htmlspecialchars($user_name) ?></div>
                    <div class="text-sm font-medium text-blue-300"><?= htmlspecialchars($user_role_name) ?></div>
                </div>
                <a href="logout.php" class="ml-auto bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-medium transition">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        if(btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
                const icons = btn.querySelectorAll('svg');
                icons[0].classList.toggle('hidden');
                icons[0].classList.toggle('block');
                icons[1].classList.toggle('hidden');
                icons[1].classList.toggle('block');
            });
        }
    });
</script>