<?php
// جلب اسم المتجر من قاعدة البيانات (إذا لم يكن محملاً بالفعل من header.php)
if (!isset($shopName)) {
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
    $shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'Smart Shop';
}
?>
<!-- Mobile Overlay -->
<div id="mobile-menu-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden backdrop-blur-sm transition-opacity" onclick="toggleMobileMenu()"></div>

<!-- Mobile Menu Button -->
<button id="mobile-menu-toggle" class="md:hidden fixed top-4 start-4 z-[60] p-2 bg-dark-surface text-white rounded-lg shadow-lg border border-white/10 transition-all hover:bg-dark-surface/80" onclick="toggleMobileMenu()">
    <span class="material-icons-round">menu</span>
</button>

<!-- Sidebar -->
<aside id="main-sidebar" class="hidden md:flex w-72 bg-dark-surface border-l border-white/5 dark:bg-dark-surface dark:border-white/5 bg-white border-gray-200 flex-col shrink-0 z-30 transition-colors duration-200">
    <!-- Logo -->
    <div class="h-20 flex items-center justify-center px-6 border-b border-white/5 dark:border-white/5 border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
                <span class="material-icons-round text-white text-xl">storefront</span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white dark:text-white text-gray-900"><?php echo htmlspecialchars($shopName); ?></h1>
                <p class="text-xs text-gray-400 dark:text-gray-400 text-gray-600"><?php echo __('smart_shop_system'); ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-4">
        <div class="space-y-2">
            <a href="reports.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'reports.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">bar_chart</span>
                <span class="font-bold"><?php echo __('dashboard'); ?></span>
            </a>

            <a href="pos.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'pos.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">point_of_sale</span>
                <span class="font-bold"><?php echo __('pos'); ?></span>
            </a>

            <a href="invoices.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'invoices.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">receipt_long</span>
                <span class="font-bold"><?php echo __('invoices'); ?></span>
            </a>

            <a href="refunds.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'refunds.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">assignment_return</span>
                <span class="font-bold"><?php echo __('refunds'); ?></span>
            </a>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="expenses.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'expenses.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">payments</span>
                <span class="font-bold"><?php echo __('expenses'); ?></span>
            </a>
            <?php endif; ?>
            
            <a href="products.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'products.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">inventory_2</span>
                <span class="font-bold"><?php echo __('products_management'); ?></span>
            </a>


            <a href="customers.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'customers.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">people</span>
                <span class="font-bold"><?php echo __('customers'); ?></span>
            </a>
            
            <div class="my-2 mx-4 border-t border-white/10 dark:border-white/10 border-gray-200"></div>

            <a href="removed_products.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'removed_products.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">auto_delete</span>
                <span class="font-bold"><?php echo __('deleted_products'); ?></span>
            </a>
            
            <a href="zakat_calculator.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'zakat_calculator.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">mosque</span>
                <span class="font-bold"><?php echo __('zakat_calculator'); ?></span>
            </a>

        </div>
    </nav>

    <!-- Secondary Navigation -->
    <div class="px-4 py-2 space-y-2 border-t border-white/5 dark:border-white/5 border-gray-200">
        <a href="notifications.php" class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'notifications.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
            <div class="flex items-center gap-3">
                <span class="material-icons-round text-xl">notifications</span>
                <span class="font-bold"><?php echo __('notifications'); ?></span>
            </div>
            <span id="notification-count" class="px-2 py-0.5 text-xs font-bold text-white bg-green-500 rounded-full" style="display: none;">0</span>
        </a>

        <a href="settings.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'settings.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
            <span class="material-icons-round text-xl">settings</span>
            <span class="font-bold"><?php echo __('settings'); ?></span>
        </a>

    </div>

    <!-- User Profile -->
    <div class="p-4 border-t border-white/5 dark:border-white/5 border-gray-200">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 dark:bg-white/5 bg-gray-100">
            <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold">
                <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-white dark:text-white text-gray-900"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></p>
                <p class="text-xs text-gray-400 dark:text-gray-400 text-gray-600"><?php echo htmlspecialchars($_SESSION['role'] ?? 'Admin'); ?></p>
            </div>
            <a href="logout.php"
                class="p-2 text-gray-400 dark:text-gray-400 text-gray-600 hover:text-red-500 transition-colors"
                title="<?php echo __('logout'); ?>">
                <span class="material-icons-round">logout</span>
            </a>
        </div>
    </div>
</aside>

<script>
    function toggleMobileMenu() {
        const sidebar = document.getElementById('main-sidebar');
        const overlay = document.getElementById('mobile-menu-overlay');
        const btnIcon = document.querySelector('#mobile-menu-toggle span');
        
        if (sidebar.classList.contains('hidden')) {
            // Open
            sidebar.classList.remove('hidden');
            sidebar.classList.add('flex', 'fixed', 'inset-y-0', 'start-0', 'z-50', 'w-72', 'shadow-2xl');
            overlay.classList.remove('hidden');
            btnIcon.textContent = 'close';
        } else {
            // Close
            sidebar.classList.add('hidden');
            sidebar.classList.remove('flex', 'fixed', 'inset-y-0', 'start-0', 'z-50', 'w-72', 'shadow-2xl');
            overlay.classList.add('hidden');
            btnIcon.textContent = 'menu';
        }
    }

    // Reset on resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('mobile-menu-overlay');
            const btnIcon = document.querySelector('#mobile-menu-toggle span');
            
            // Reset to desktop state (hidden class is overridden by md:flex, but we should clean up mobile classes)
            sidebar.classList.remove('fixed', 'inset-y-0', 'start-0', 'z-50', 'w-72', 'shadow-2xl');
            
            // Ensure hidden is present so it defaults to hidden on mobile if resized back down
            if (!sidebar.classList.contains('hidden')) {
                sidebar.classList.add('hidden');
            }
            
            overlay.classList.add('hidden');
            if (btnIcon) btnIcon.textContent = 'menu';
        }
    });
</script>