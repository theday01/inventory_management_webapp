<?php
// Sidebar موحّد لصفحات الإعدادات والمستخدمين والإصدار والترخيص
// يوفّر أقسام الإعدادات كعناصر موحّدة، ويبرز الرابط النشط حسب $current_page
// وفي صفحة الإعدادات، يمكن تمرير $active_tab لإبراز القسم الحالي
$active_tab = $active_tab ?? (isset($_GET['tab']) ? $_GET['tab'] : null);
function tabClass($tab, $current_page, $active_tab) {
    $base = 'tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-right transition-all group';
    if ($current_page === 'settings.php' && $active_tab === $tab) {
        return $base . ' active-tab';
    }
    return $base . ' text-gray-400 hover:text-white hover:bg-white/5';
}
?>
<aside class="w-72 bg-dark-surface/30 backdrop-blur-md border-l border-white/5 flex flex-col shrink-0 py-6 px-4 gap-2 overflow-y-auto">
    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider px-4 mb-2">أقسام الإعدادات</div>

    <a href="settings.php?tab=store" id="tab-btn-store" class="<?php echo tabClass('store', $current_page, $active_tab); ?>" data-tab="store">
        <span class="material-icons-round text-[20px] transition-colors">store</span>
        <div class="flex-1">
            <span class="font-bold text-sm block">بيانات المتجر</span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">الاسم، العنوان، الهاتف</span>
        </div>
    </a>

    <a href="settings.php?tab=delivery" id="tab-btn-delivery" class="<?php echo tabClass('delivery', $current_page, $active_tab); ?>" data-tab="delivery">
        <span class="material-icons-round text-[20px] transition-colors">local_shipping</span>
        <div class="flex-1">
            <span class="font-bold text-sm block">الشحن والتوصيل</span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">المدن والأسعار</span>
        </div>
    </a>

    <a href="settings.php?tab=rental" id="tab-btn-rental" class="<?php echo tabClass('rental', $current_page, $active_tab); ?>" data-tab="rental">
        <span class="material-icons-round text-[20px] transition-colors">home_work</span>
        <div class="flex-1">
            <span class="font-bold text-sm block">إدارة الإيجار</span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">الدفعات والتذكيرات</span>
        </div>
    </a>

    <a href="settings.php?tab=finance" id="tab-btn-finance" class="<?php echo tabClass('finance', $current_page, $active_tab); ?>" data-tab="finance">
        <span class="material-icons-round text-[20px] transition-colors">account_balance</span>
        <div class="flex-1">
            <span class="font-bold text-sm block">المالية والضرائب</span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">العملة ونسب الضريبة</span>
        </div>
    </a>

    <a href="settings.php?tab=invoice" id="tab-btn-invoice" class="<?php echo tabClass('invoice', $current_page, $active_tab); ?>" data-tab="invoice">
        <span class="material-icons-round text-[20px] transition-colors">receipt_long</span>
        <div class="flex-1">
            <span class="font-bold text-sm block">الفاتورة والقوالب</span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">تعديل قالب الفاتورة</span>
        </div>
    </a>

    <a href="settings.php?tab=keyboard" id="tab-btn-keyboard" class="<?php echo tabClass('keyboard', $current_page, $active_tab); ?>" data-tab="keyboard">
        <span class="material-icons-round text-[20px] transition-colors">keyboard</span>
        <div class="flex-1">
            <span class="font-bold text-sm block">لوحة المفاتيح</span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">شاشة اللمس، اللغة</span>
        </div>
    </a>

    <a href="settings.php?tab=system" id="tab-btn-system" class="<?php echo tabClass('system', $current_page, $active_tab); ?>" data-tab="system">
        <span class="material-icons-round text-[20px] transition-colors">tune</span>
        <div class="flex-1">
            <span class="font-bold text-sm block">النظام والتنبيهات</span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">الوضع الليلي والمخزون</span>
        </div>
    </a>

    <div class="my-2 border-t border-white/5"></div>
    <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page === 'users.php') ? 'bg-primary/10 text-primary border border-primary/20 shadow-lg shadow-primary/5' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
        <span class="material-icons-round text-[20px]">people</span>
        <span class="font-<?php echo ($current_page === 'users.php') ? 'bold' : 'medium'; ?> text-sm">المستخدمين</span>
    </a>

    <div class="my-2 border-t border-white/5"></div>
    <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
        النظام
    </div>
    <a href="version.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page === 'version.php') ? 'bg-primary/10 text-primary border border-primary/20 shadow-lg shadow-primary/5' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
        <span class="material-icons-round text-[20px]">info</span>
        <span class="font-<?php echo ($current_page === 'version.php') ? 'bold' : 'medium'; ?> text-sm">إصدار النظام</span>
    </a>
     
    <a href="license.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page === 'license.php') ? 'bg-primary/10 text-primary border border-primary/20 shadow-lg shadow-primary/5' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
        <span class="material-icons-round text-[20px]">verified_user</span>
        <span class="font-<?php echo ($current_page === 'license.php') ? 'bold' : 'medium'; ?> text-sm">الترخيص</span>
    </a>
</aside>
