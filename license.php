<?php
$page_title = 'اتفاقية الترخيص';
$current_page = 'license.php';
$current_page = 'settings.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-20 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">settings_suggest</span>
            إتفاقيات الترخيص
        </h2>
    </header>

    <div class="flex-1 flex overflow-hidden relative z-10">

        <aside class="w-64 bg-dark-surface/30 backdrop-blur-md border-l border-white/5 flex flex-col overflow-y-auto shrink-0">
            <div class="p-4 space-y-2">
                <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    الإعدادات العامة
                </div>

                <a href="settings.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-primary transition-colors">store</span>
                    <span class="font-medium text-sm">إعدادات المتجر</span>
                </a>

                <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-primary transition-colors">people</span>
                    <span class="font-medium text-sm">المستخدمين</span>
                </a>

                <div class="my-2 border-t border-white/5"></div>

                <div class="px-4 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    النظام
                </div>

                <a href="version.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-primary transition-colors">info</span>
                    <span class="font-medium text-sm">إصدار النظام</span>
                </a>
                 
                <a href="license.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary border border-primary/20 transition-all shadow-lg shadow-primary/5">
                    <span class="material-icons-round text-[20px]">verified_user</span>
                    <span class="font-bold text-sm">الترخيص</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <div class="max-w-4xl mx-auto">
                
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl overflow-hidden glass-panel">
                    <div class="p-8 border-b border-white/5 bg-white/5 flex justify-between items-start">
                        <div>
                            <h1 class="text-2xl font-bold text-white mb-2">Smart Shop <span style="font-size: 14px;" class="text-primary">نظام إدارة المتاجر الذكي</span></h1>
                            <p class="text-sm text-gray-400">اتفاقية ترخيص البرمجيات - الإصدار 2.0</p>
                        </div>
                        <span class="material-icons-round text-4xl text-white/10">gavel</span>
                    </div>

                    <div class="p-8 space-y-8 text-gray-300 leading-relaxed text-sm md:text-base h-[600px] overflow-y-auto custom-scrollbar">
                        
                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">1.</span> منح الترخيص
                            </h3>
                            <p class="mb-2">تمنحك <strong class="text-white">Eagle Shadow Technology</strong> ("المرخص") ترخيصاً غير حصري، وغير قابل للتحويل، ومحدوداً لاستخدام هذا البرنامج ("Smart Shop") وفقاً للشروط المنصوص عليها في هذه الاتفاقية.</p>
                            <p>يسمح لك هذا الترخيص بتثبيت واستخدام نسخة واحدة من البرنامج على جهاز خادم واحد أو شبكة محلية واحدة لغرض إدارة أنشطتك التجارية فقط.</p>
                        </section>

                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">2.</span> حقوق الملكية الفكرية
                            </h3>
                            <p>البرنامج محمي بموجب قوانين حقوق النشر والمعاهدات الدولية للملكية الفكرية. تحتفظ Eagle Shadow Technology بجميع الحقوق والملكيات والمصالح في البرنامج (بما في ذلك الكود المصدري، التصميم، والخوارزميات).</p>
                            <ul class="list-disc list-inside mt-2 space-y-1 text-gray-400">
                                <li>لا يجوز لك تعديل، أو هندسة عكسية، أو فك تشفير البرنامج.</li>
                                <li>لا يجوز لك تأجير، أو إقراض، أو إعادة بيع البرنامج لطرف ثالث.</li>
                                <li>جميع العلامات التجارية والشعارات هي ملك لأصحابها المعنيين.</li>
                            </ul>
                        </section>

                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">3.</span> حقوق الملكية الفكرية
                            </h3>
                            <p>البرنامج محمي بموجب قوانين حقوق النشر والمعاهدات الدولية للملكية الفكرية. تحتفظ Eagle Shadow Technology بجميع الحقوق والملكيات والمصالح في البرنامج (بما في ذلك الكود المصدري، التصميم، والخوارزميات).</p>
                        </section>

                        <section>
                            <h3 class="text-white font-bold text-lg mb-3 flex items-center gap-2">
                                <span class="text-primary">4.</span> الدعم والتحديثات
                            </h3>
                            <p>قد توفر تحديثات دورية للبرنامج لتحسين الأداء أو إضافة ميزات جديدة. يخضع هذا الترخيص لأي تحديثات مستقبلية ما لم ترفق بشروط منفصلة.</p>
                        </section>

                        <div class="mt-8 pt-8 border-t border-white/5 text-center">
                            <p class="text-xs text-gray-500">للاستفسارات القانونية: <span class="text-primary">support@eagleshadow.technology</span> أو عبر واتساب: <span style="color: rgb(59 130 246 / var(--tw-text-opacity, 1));;" dir="ltr">+212 700-979284</span></p>
                        </div>
                    </div>

                    <div class="p-6 bg-white/5 border-t border-white/5 flex justify-between items-center">
                        <div class="text-xs text-gray-400 flex items-center gap-2">
                            <span class="material-icons-round text-green-500 text-sm">check_circle</span>
                            استخدامك للنظام يعني موافقتك على هذه الشروط.
                        </div>
                        <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-dark-surface border border-white/10 hover:bg-white/5 transition-colors text-sm text-white no-print">
                            <span class="material-icons-round text-sm">print</span>
                            طباعة الوثيقة
                        </button>
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
    main > .absolute {
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
    .backdrop-blur-md {
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