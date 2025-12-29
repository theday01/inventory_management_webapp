<?php
// Fetch shop name for the sidebar
$shopNameResult = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
$shopName = 'your store name here'; // Default value
if ($shopNameResult && $shopNameResult->num_rows > 0) {
    $row = $shopNameResult->fetch_assoc();
    // Use the value from DB only if it's not empty
    if (!empty(trim($row['setting_value']))) {
        $shopName = $row['setting_value'];
    }
}
?>
<!-- Sidebar -->
<aside class="w-64 bg-dark-surface/80 backdrop-blur-xl border-l border-white/5 flex flex-col hidden md:flex z-50">
    <div class="h-20 flex items-center justify-center border-b border-white/5">
        <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent"><?php echo htmlspecialchars($shopName); ?></h1>
    </div>

    <nav class="flex-1 overflow-y-auto py-6 space-y-2 px-4">
        <a href="dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo ($current_page === 'dashboard.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 hover:bg-white/5 hover:text-white'; ?>">
            <span class="material-icons-round">dashboard</span>
            <span class="font-medium">لوحة التحكم</span>
        </a>
        <a href="pos.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo ($current_page === 'pos.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 hover:bg-white/5 hover:text-white'; ?>">
            <span class="material-icons-round">point_of_sale</span>
            <span class="font-medium">نقطة البيع</span>
        </a>
        <a href="products.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo ($current_page === 'products.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 hover:bg-white/5 hover:text-white'; ?>">
            <span class="material-icons-round">inventory_2</span>
            <span class="font-medium">المنتجات (إدارة المخزون)</span>
        </a>
        <a href="customers.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo ($current_page === 'customers.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 hover:bg-white/5 hover:text-white'; ?>">
            <span class="material-icons-round">people</span>
            <span class="font-medium">العملاء</span>
        </a>
        <a href="invoices.php"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo ($current_page === 'invoices.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 hover:bg-white/5 hover:text-white'; ?>">
            <span class="material-icons-round">receipt_long</span>
            <span class="font-medium">الفواتير</span>
        </a>
        <div class="pt-4 mt-4 border-t border-white/5">
            <a href="settings.php"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?php echo ($current_page === 'settings.php') ? 'bg-primary/10 text-primary' : 'text-gray-400 hover:bg-white/5 hover:text-white'; ?>">
                <span class="material-icons-round">settings</span>
                <span class="font-medium">الإعدادات</span>
            </a>            
        </div>
    </nav>

    <div class="p-4 border-t border-white/5">
        <a href="logout.php"
            class="flex items-center gap-3 px-4 py-3 text-red-400 hover:bg-red-500/10 rounded-xl transition-all">
            <span class="material-icons-round">logout</span>
            <span class="font-medium">تسجيل الخروج</span>
        </a>
    </div>
</aside>
