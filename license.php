<?php
require_once 'src/language.php';
$page_title = __('license_page_title');
$current_page = 'license.php';
require_once 'session.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-4 lg:px-8 relative z-20 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">settings_suggest</span>
            <?php echo __('license_header'); ?>
        </h2>
    </header>

    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative z-10">

        <?php require_once 'src/settings_sidebar.php'; ?>

        <div class="flex-1 overflow-y-auto p-4 lg:p-8 custom-scrollbar">
            <div class="max-w-4xl mx-auto">
                
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl overflow-hidden glass-panel">
                    <div class="p-8 border-b border-white/5 bg-white/5 flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl font-bold text-white mb-2">Smart Shop <span style="font-size: 14px;" class="text-primary"><?php echo __('smart_shop_title_sub'); ?></span></h1>
                            <p class="text-sm text-gray-400"><?php echo __('license_agreement_version'); ?></p>
                        </div>
                        <span class="material-icons-round text-4xl text-white/10">gavel</span>
                    </div>

                    <div class="p-8 space-y-8 text-gray-300 leading-relaxed text-sm md:text-base h-[600px] overflow-y-auto custom-scrollbar">
                        
                        <!-- Preamble -->
                        <div class="bg-white/5 p-4 rounded-xl border border-white/5 mb-6">
                            <p class="italic text-gray-400 text-sm"><?php echo __('license_preamble'); ?></p>
                        </div>

                        <!-- 1. License Grant -->
                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">1.</span> <?php echo __('license_grant_title'); ?>
                            </h3>
                            <p class="mb-2"><?php echo __('license_grant_text'); ?></p>
                        </section>

                        <!-- 2. Intellectual Property -->
                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">2.</span> <?php echo __('ip_rights_title'); ?>
                            </h3>
                            <p><?php echo __('ip_rights_text'); ?></p>
                        </section>

                        <!-- 3. Restrictions -->
                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">3.</span> <?php echo __('license_restrictions_title'); ?>
                            </h3>
                            <p><?php echo __('license_restrictions_text'); ?></p>
                            <ul class="list-disc list-inside mt-2 space-y-1 text-gray-400 pl-4 rtl:pr-4">
                                <li><?php echo __('license_restrictions_list_1'); ?></li>
                                <li><?php echo __('license_restrictions_list_2'); ?></li>
                                <li><?php echo __('license_restrictions_list_3'); ?></li>
                            </ul>
                        </section>

                        <!-- 4. Data Privacy -->
                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">4.</span> <?php echo __('license_privacy_title'); ?>
                            </h3>
                            <p><?php echo __('license_privacy_text'); ?></p>
                        </section>

                        <!-- 6. Limitation of Liability -->
                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">5.</span> <?php echo __('license_liability_title'); ?>
                            </h3>
                            <p><?php echo __('license_liability_text'); ?></p>
                        </section>
                        
                        <!-- 7. Termination -->
                         <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">6.</span> <?php echo __('license_termination_title'); ?>
                            </h3>
                            <p><?php echo __('license_termination_text'); ?></p>
                        </section>

                        <!-- Contact -->
                        <div class="mt-8 pt-8 border-t border-white/5 text-center">
                            <p class="text-sm text-gray-500 mb-2"><?php echo __('legal_inquiries'); ?></p>
                            <p class="text-primary font-bold">support@eagleshadow.technology</p>
                            <p class="text-xs text-gray-500 mt-2"><?php echo __('or_via_whatsapp'); ?> <span style="color: rgb(59 130 246 / var(--tw-text-opacity, 1));;" dir="ltr">+212 700-979284</span></p>
                        </div>
                    </div>

                    <div class="p-6 bg-white/5 border-t border-white/5 flex justify-between items-center">
                        <div class="text-xs text-gray-400 flex items-center gap-2">
                            <span class="material-icons-round text-green-500 text-sm">check_circle</span>
                            <?php echo __('usage_consent'); ?>
                        </div>
                        <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-dark-surface border border-white/10 hover:bg-white/5 transition-colors text-sm text-white no-print">
                            <span class="material-icons-round text-sm">print</span>
                            <?php echo __('print_document'); ?>
                        </button>
                    </div>
                </div>

                <!-- Developer Logo -->
                <div class="mt-8 text-center">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 inline-block">
                        <a href="https://eagleshadow.technology" target="_blank" class="block">
                            <img src="src/support/logo.png" alt="<?= __('developer_logo_alt') ?>" class="h-25 w-auto mx-auto mb-4 opacity-80 hover:opacity-100 transition-opacity duration-300 cursor-pointer">
                        </a>
                        <p class="text-sm text-gray-400"><?= __('developed_by') ?></p>
                        <p class="text-white font-semibold">EagleShadow Technology</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<style>
    /* Custom scrollbar for the legal text area */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* Print styles */
@media print {
    /* إخفاء العناصر غير المطلوبة */
    body > header,
    body > nav,
    body > footer,
    .sidebar,
    aside,
    main > header,
    button,
    main > .absolute,
    .no-print {
        display: none !important;
    }
    
    /* إعدادات الصفحة */
    body, html {
        margin: 0;
        padding: 20px;
        background: white !important;
        color: black !important;
    }
    
    main {
        background: white !important;
        padding: 0 !important;
        display: block !important;
    }
    
    main > div {
        overflow: visible !important;
        display: block !important;
    }
    
    main > div > div {
        width: 100% !important;
        padding: 0 !important;
    }
    
    /* تنسيق البطاقة */
    .glass-panel {
        border: 2px solid #333 !important;
        border-radius: 8px !important;
        background: white !important;
        box-shadow: none !important;
    }
    
    /* رأس البطاقة */
    .glass-panel > div:first-child {
        background: #f5f5f5 !important;
        border-bottom: 2px solid #333 !important;
    }
    
    /* تنسيق النصوص */
    * {
        color: black !important;
    }
    
    h1, h2, h3, strong {
        color: black !important;
        font-weight: bold !important;
    }
    
    .text-primary {
        color: #0066cc !important;
    }
    
    /* إزالة الخلفيات الداكنة */
    .bg-dark-surface,
    .bg-white\/5,
    .backdrop-blur-md,
    .bg-red-500\/10 {
        background: white !important;
    }
    
    /* الحدود */
    .border-white\/5,
    .border-white\/10 {
        border-color: #ddd !important;
    }
    
    /* ضبط الارتفاع */
    .h-\[600px\] {
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }
    
    /* إخفاء الأيقونات */
    .material-icons-round {
        display: none !important;
    }
    
    /* إظهار المحتوى */
    .overflow-hidden,
    .overflow-y-auto {
        overflow: visible !important;
    }
    
    .flex-1 {
        width: 100% !important;
    }
    
    /* تباعد الأقسام */
    section {
        page-break-inside: avoid;
        margin-bottom: 25px;
    }
    
    /* القوائم */
    ul {
        color: black !important;
    }
    
    ul li {
        color: #333 !important;
    }
    
    /* تذييل البطاقة */
    .glass-panel > div:last-child {
        background: #f5f5f5 !important;
        border-top: 1px solid #ddd !important;
    }
}
</style>
<?php require_once 'src/footer.php'; ?>
