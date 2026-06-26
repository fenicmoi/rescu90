<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Ensure $pdo is available (usually included via db_config.php or auth.php)
if (!isset($pdo)) {
    require_once 'db_config.php';
}

if (isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM menus WHERE menu_type = 'backend' AND is_active = 1 ORDER BY order_num ASC");
    $stmt->execute();
    $all_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $allowed_menus = [];
    foreach ($all_menus as $menu) {
        if (!empty($menu['allowed_roles'])) {
            $menu_allowed_roles = json_decode($menu['allowed_roles'], true);
            $current_role = isset($user_role_id) ? $user_role_id : 0;
            if (!in_array($current_role, $menu_allowed_roles)) {
                continue;
            }
        }
        $allowed_menus[] = $menu;
    }

    $menu_tree = [];
    foreach ($allowed_menus as $menu) {
        if (empty($menu['parent_id'])) {
            $menu['children'] = [];
            $menu_tree[$menu['id']] = $menu;
        }
    }
    foreach ($allowed_menus as $menu) {
        if (!empty($menu['parent_id']) && isset($menu_tree[$menu['parent_id']])) {
            $menu_tree[$menu['parent_id']]['children'][] = $menu;
        }
    }
} else {
    $menu_tree = [];
}
?>
<!-- Add FontAwesome to ensure icons work -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Navbar -->
<nav class="bg-blue-800 text-white shadow-lg shrink-0 z-50 relative">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <span class="text-lg sm:text-xl font-bold tracking-wide truncate">🛡️ CRIME MAP</span>
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
            <div class="hidden md:flex items-center gap-1 lg:gap-3">
                
                <?php foreach ($menu_tree as $menu): ?>
                    <?php if (empty($menu['children'])): ?>
                        <!-- Single Menu -->
                        <a href="<?= htmlspecialchars($menu['url']) ?>" class="flex items-center gap-1 whitespace-nowrap px-3 py-2 rounded-md text-sm font-medium <?= $current_page == $menu['url'] ? 'bg-blue-900 text-white shadow-inner' : 'text-blue-200 hover:text-white hover:bg-blue-700 transition' ?>">
                            <?php if($menu['icon']): ?><i class="<?= htmlspecialchars($menu['icon']) ?>"></i><?php endif; ?>
                            <?= htmlspecialchars($menu['title']) ?>
                        </a>
                    <?php else: ?>
                        <!-- Dropdown Menu -->
                        <div class="relative group">
                            <button class="flex items-center gap-1 whitespace-nowrap px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-700 transition">
                                <?php if($menu['icon']): ?><i class="<?= htmlspecialchars($menu['icon']) ?>"></i><?php endif; ?>
                                <?= htmlspecialchars($menu['title']) ?> 
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="absolute left-0 top-full pt-1 w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 overflow-hidden">
                                    <?php foreach($menu['children'] as $child): ?>
                                    <a href="<?= htmlspecialchars($child['url']) ?>" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 <?= $current_page == $child['url'] ? 'bg-blue-50 text-blue-700 font-bold border-l-4 border-blue-600' : 'border-l-4 border-transparent' ?>">
                                        <?php if($child['icon']): ?><i class="<?= htmlspecialchars($child['icon']) ?> mr-1 w-4 text-center"></i><?php endif; ?>
                                        <?= htmlspecialchars($child['title']) ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <div class="ml-2 pl-2 lg:ml-4 lg:pl-4 border-l border-blue-700 flex items-center gap-3">
                    <div class="text-sm text-right hidden lg:block">
                        <div class="text-white font-medium whitespace-nowrap"><?= htmlspecialchars($user_name ?? '') ?></div>
                        <div class="text-blue-300 text-xs whitespace-nowrap"><?= htmlspecialchars($user_role_name ?? '') ?></div>
                    </div>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-xs font-medium transition whitespace-nowrap">ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="md:hidden hidden absolute w-full z-50 bg-blue-800 shadow-xl border-b-4 border-blue-900" id="mobile-menu">
        <div class="px-2 pt-4 pb-4 space-y-4 sm:px-3">
            
            <?php foreach ($menu_tree as $menu): ?>
                <?php if (empty($menu['children'])): ?>
                    <div class="space-y-1">
                        <a href="<?= htmlspecialchars($menu['url']) ?>" class="block px-3 py-2.5 rounded-md text-base font-medium <?= $current_page == $menu['url'] ? 'bg-blue-900 text-white border-l-4 border-blue-400' : 'text-blue-100 hover:text-white hover:bg-blue-700 border-l-4 border-transparent' ?>">
                            <?php if($menu['icon']): ?><i class="<?= htmlspecialchars($menu['icon']) ?> mr-2"></i><?php endif; ?>
                            <?= htmlspecialchars($menu['title']) ?>
                        </a>
                    </div>
                <?php else: ?>
                    <div>
                        <div class="px-3 text-xs font-bold text-blue-300 uppercase tracking-wider mb-2">
                            <?php if($menu['icon']): ?><i class="<?= htmlspecialchars($menu['icon']) ?> mr-1"></i><?php endif; ?>
                            <?= htmlspecialchars($menu['title']) ?>
                        </div>
                        <div class="space-y-1">
                            <?php foreach($menu['children'] as $child): ?>
                            <a href="<?= htmlspecialchars($child['url']) ?>" class="block px-3 py-2.5 rounded-md text-base font-medium pl-6 <?= $current_page == $child['url'] ? 'bg-blue-900 text-white border-l-4 border-blue-400' : 'text-blue-100 hover:text-white hover:bg-blue-700 border-l-4 border-transparent' ?>">
                                <?php if($child['icon']): ?><i class="<?= htmlspecialchars($child['icon']) ?> w-5 text-center mr-1 text-sm"></i><?php endif; ?>
                                <?= htmlspecialchars($child['title']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

        </div>
        <div class="pt-4 pb-4 border-t border-blue-700 bg-blue-900/50">
            <div class="flex items-center justify-between px-5">
                <div class="flex items-center">
                    <div class="ml-3">
                        <div class="text-base font-medium text-white"><?= htmlspecialchars($user_name ?? '') ?></div>
                        <div class="text-sm font-medium text-blue-300"><?= htmlspecialchars($user_role_name ?? '') ?></div>
                    </div>
                </div>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-sm">ออกจากระบบ</a>
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
