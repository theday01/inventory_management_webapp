<?php
// Sidebar موحّد لصفحات الإعدادات والمستخدمين والإصدار والترخيص
// يوفّر أقسام الإعدادات كعناصر موحّدة، ويبرز الرابط النشط حسب $current_page
// وفي صفحة الإعدادات، يمكن تمرير $active_tab لإبراز القسم الحالي
$active_tab = $active_tab ?? (isset($_GET['tab']) ? $_GET['tab'] : null);
function tabClass($tab, $current_page, $active_tab) {
    $base = 'tab-btn flex items-center gap-3 px-4 py-3 rounded-xl transition-all group';
    $textAlign = get_dir() === 'rtl' ? 'text-right' : 'text-left';
    if ($current_page === 'settings.php' && $active_tab === $tab) {
        return $base . ' ' . $textAlign . ' active-tab';
    }
    return $base . ' ' . $textAlign . ' text-gray-400 hover:text-white hover:bg-white/5';
}
?>
<aside class="w-72 bg-dark-surface/30 backdrop-blur-md border-l border-white/5 flex flex-col shrink-0 py-6 px-4 gap-2 overflow-y-auto" dir="<?php echo get_dir(); ?>">
    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider px-4 mb-2"><?php echo __('settings_sections_title'); ?></div>

    <a href="settings.php?tab=store" id="tab-btn-store" class="<?php echo tabClass('store', $current_page, $active_tab); ?>" data-tab="store">
        <span class="material-icons-round text-[20px] transition-colors">store</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('store_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('store_tab_desc'); ?></span>
        </div>
    </a>

    <a href="settings.php?tab=delivery" id="tab-btn-delivery" class="<?php echo tabClass('delivery', $current_page, $active_tab); ?>" data-tab="delivery">
        <span class="material-icons-round text-[20px] transition-colors">local_shipping</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('delivery_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('delivery_tab_desc'); ?></span>
        </div>
    </a>

    <a href="settings.php?tab=rental" id="tab-btn-rental" class="<?php echo tabClass('rental', $current_page, $active_tab); ?>" data-tab="rental">
        <span class="material-icons-round text-[20px] transition-colors">home_work</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('rental_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('rental_tab_desc'); ?></span>
        </div>
    </a>

    <a href="settings.php?tab=finance" id="tab-btn-finance" class="<?php echo tabClass('finance', $current_page, $active_tab); ?>" data-tab="finance">
        <span class="material-icons-round text-[20px] transition-colors">account_balance</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('finance_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('finance_tab_desc'); ?></span>
        </div>
    </a>

    <a href="settings.php?tab=print" id="tab-btn-print" class="<?php echo tabClass('print', $current_page, $active_tab); ?>" data-tab="print">
        <span class="material-icons-round text-[20px] transition-colors">print</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('print_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('print_tab_desc'); ?></span>
        </div>
    </a>

    <a href="settings.php?tab=system" id="tab-btn-system" class="<?php echo tabClass('system', $current_page, $active_tab); ?>" data-tab="system">
        <span class="material-icons-round text-[20px] transition-colors">tune</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('system_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('system_tab_desc'); ?></span>
        </div>
    </a>

    <a href="settings.php?tab=language" id="tab-btn-language" class="<?php echo tabClass('language', $current_page, $active_tab); ?>" data-tab="language">
        <span class="material-icons-round text-[20px] transition-colors">translate</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('system_language_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('system_language_desc'); ?></span>
        </div>
    </a>

    <a href="settings.php?tab=workdays" id="tab-btn-workdays" class="<?php echo tabClass('workdays', $current_page, $active_tab); ?>" data-tab="workdays">
        <span class="material-icons-round text-[20px] transition-colors">event_available</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('workdays_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('workdays_tab_desc'); ?></span>
        </div>
    </a>
    
    <div class="my-2 border-t border-white/5"></div>
    <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page === 'users.php') ? 'bg-primary/10 text-primary border border-primary/20 shadow-lg shadow-primary/5' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
        <span class="material-icons-round text-[20px]">people</span>
        <span class="font-<?php echo ($current_page === 'users.php') ? 'bold' : 'medium'; ?> text-sm"><?php echo __('users_link'); ?></span>
    </a>

    <div class="my-2 border-t border-white/5"></div>
    <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
        <?php echo __('system_section_title'); ?>
    </div>

    <a href="settings.php?tab=backup" id="tab-btn-backup" class="<?php echo tabClass('backup', $current_page, $active_tab); ?>" data-tab="backup">
        <span class="material-icons-round text-[20px] transition-colors">backup</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('backup_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('backup_tab_desc'); ?></span>
        </div>
    </a>

    <a href="settings.php?tab=reset" id="tab-btn-reset" class="<?php echo tabClass('reset', $current_page, $active_tab); ?>" data-tab="reset">
        <span class="material-icons-round text-[20px] transition-colors">restart_alt</span>
        <div class="flex-1">
            <span class="font-bold text-sm block"><?php echo __('reset_tab_title'); ?></span>
            <span class="text-[10px] text-gray-400 block group-hover:text-gray-300"><?php echo __('reset_tab_desc'); ?></span>
        </div>
    </a>
    
    <a href="version.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page === 'version.php') ? 'bg-primary/10 text-primary border border-primary/20 shadow-lg shadow-primary/5' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
        <span class="material-icons-round text-[20px]">info</span>
        <span class="font-<?php echo ($current_page === 'version.php') ? 'bold' : 'medium'; ?> text-sm"><?php echo __('version_link'); ?></span>
    </a>
     
    <a href="license.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page === 'license.php') ? 'bg-primary/10 text-primary border border-primary/20 shadow-lg shadow-primary/5' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
        <span class="material-icons-round text-[20px]">verified_user</span>
        <span class="font-<?php echo ($current_page === 'license.php') ? 'bold' : 'medium'; ?> text-sm"><?php echo __('license_link'); ?></span>
    </a>

    <a href="contact.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all group <?php echo ($current_page === 'contact.php') ? 'bg-primary/10 text-primary border border-primary/20 shadow-lg shadow-primary/5' : 'text-gray-400 hover:text-white hover:bg-white/5'; ?>">
        <span class="material-icons-round text-[20px]">support_agent</span>
        <span class="font-<?php echo ($current_page === 'contact.php') ? 'bold' : 'medium'; ?> text-sm"><?php echo __('contact_link'); ?></span>
    </a>
</aside>
