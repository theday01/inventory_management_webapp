<?php
// جلب اسم المتجر من قاعدة البيانات (إذا لم يكن محملاً بالفعل من header.php)
if (!isset($shopName)) {
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
    $shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'Smart Shop';
}
?>
<!-- Sidebar -->
<aside class="w-72 bg-dark-surface border-l border-white/5 dark:bg-dark-surface dark:border-white/5 bg-white border-gray-200 flex flex-col shrink-0 z-30 transition-colors duration-200">
    <!-- Logo -->
    <div class="h-20 flex items-center justify-center px-6 border-b border-white/5 dark:border-white/5 border-gray-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
                <span class="material-icons-round text-white text-xl">storefront</span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white dark:text-white text-gray-900"><?php echo htmlspecialchars($shopName); ?></h1>
                <p class="text-xs text-gray-400 dark:text-gray-400 text-gray-600">نظام إدارة المتاجر الذكي</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-6 px-4">
        <div class="space-y-2">
            <a href="reports.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'reports.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">bar_chart</span>
                <span class="font-bold">الرئيسية</span>
            </a>

            <a href="pos.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'pos.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">point_of_sale</span>
                <span class="font-bold">نقطة البيع</span>
            </a>

            <a href="invoices.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'invoices.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">receipt_long</span>
                <span class="font-bold">الفواتير</span>
            </a>

            <a href="refunds.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'refunds.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">assignment_return</span>
                <span class="font-bold">المسترجعات</span>
            </a>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="expenses.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'expenses.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">payments</span>
                <span class="font-bold">المصاريف</span>
            </a>
            <?php endif; ?>
            
            <a href="products.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'products.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">inventory_2</span>
                <span class="font-bold">إدارة المخزون والمنتجات</span>
            </a>


            <a href="customers.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'customers.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">people</span>
                <span class="font-bold">العملاء (الزبناء)</span>
            </a>
            
            <div class="my-2 mx-4 border-t border-white/10 dark:border-white/10 border-gray-200"></div>

            <a href="removed_products.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'removed_products.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">auto_delete</span>
                <span class="font-bold">منتجات محذوفة</span>
            </a>
            
            <a href="zakat_calculator.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'zakat_calculator.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
                <span class="material-icons-round text-xl">mosque</span>
                <span class="font-bold">حساب الزكاة</span>
            </a>

        </div>
    </nav>

    <!-- Secondary Navigation -->
    <div class="px-4 py-2 space-y-2 border-t border-white/5 dark:border-white/5 border-gray-200">
        <a href="notifications.php" class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'notifications.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
            <div class="flex items-center gap-3">
                <span class="material-icons-round text-xl">notifications</span>
                <span class="font-bold">الإشعارات</span>
            </div>
            <span id="notification-count" class="px-2 py-0.5 text-xs font-bold text-white bg-green-500 rounded-full" style="display: none;">0</span>
        </a>

        <a href="settings.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page == 'settings.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 dark:text-gray-400 text-gray-700 hover:bg-white/5 dark:hover:bg-white/5 hover:bg-gray-100'; ?>">
            <span class="material-icons-round text-xl">settings</span>
            <span class="font-bold">الإعدادات</span>
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
                title="تسجيل الخروج">
                <span class="material-icons-round">logout</span>
            </a>
        </div>
    </div>
</aside>