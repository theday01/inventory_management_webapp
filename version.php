<?php
$page_title = 'إصدار النظام';
$current_page = 'version.php';
$current_page = 'settings.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// محاكاة بيانات النظام
$systemVersion = '2.5.0';
$buildNumber = '20250103-RC';
$releaseDate = '2025-01-03';
$phpVersion = phpversion();
$dbStatus = $conn->ping() ? 'متصل' : 'غير متصل';
?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-20 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">settings_suggest</span>
            إصدار النظام
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

                <a href="version.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary border border-primary/20 transition-all shadow-lg shadow-primary/5">
                    <span class="material-icons-round text-[20px]">info</span>
                    <span class="font-bold text-sm">إصدار النظام</span>
                </a>
                 
                <a href="license.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-yellow-500 transition-colors">verified_user</span>
                    <span class="font-medium text-sm">الترخيص</span>
                </a>
            </div>
        </aside>

        <div class="flex-1 overflow-y-auto p-8 relative z-10">
        <div class="max-w-4xl mx-auto space-y-8">
            
            <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl p-8 glass-panel text-center relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary via-accent to-purple-600"></div>
                <h1 class="text-3xl font-bold text-white mb-2">Smart Shop <span style="font-size: 14px;" class="text-primary">نظام إدارة المتاجر الذكي</span></h1>
                <div class="flex items-center justify-center gap-2 mb-6">
                    <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-500 text-xs font-bold border border-green-500/20">Stable</span>
                    <span class="px-3 py-1 rounded-full bg-white/5 text-gray-400 text-xs font-bold border border-white/10">Build <?php echo $buildNumber; ?></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center border-t border-white/5 pt-6">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">رقم الإصدار</p>
                        <p class="text-xl font-bold text-white"><?php echo $systemVersion; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">تاريخ التحديث</p>
                        <p class="text-xl font-bold text-white"><?php echo $releaseDate; ?></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">حالة الترخيص</p>
                        <p class="text-xl font-bold text-accent flex items-center justify-center gap-1">
                            <span class="material-icons-round text-sm">verified</span>
                            <span>نشط</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <span class="material-icons-round text-gray-400">memory</span>
                        البيئة التشغيلية
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl">
                            <span class="text-white font-mono text-sm"><?php echo $phpVersion; ?></span>
                            <span class="text-gray-400 text-sm">PHP Version</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl">
                            <span class="text-white font-mono text-sm truncate max-w-[200px]" title="<?php echo htmlspecialchars($serverSoftware); ?>">Apache/Linux</span>
                            <span class="text-gray-400 text-sm">Server</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl">
                            <span class="text-green-500 font-bold text-sm flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                <?php echo $dbStatus; ?>
                            </span>
                            <span class="text-gray-400 text-sm">Database</span>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel flex flex-col justify-center items-center text-center">
                    <h3 class="text-lg font-bold text-white mb-2">التحقق من التحديثات</h3>
                    <p class="text-gray-400 text-sm mb-6">تحقق مما إذا كان هناك إصدار جديد متوفر للنظام</p>
                    
                    <button id="check-update-btn" class="bg-primary hover:bg-primary-hover text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all w-full flex items-center justify-center gap-2">
                        <span class="material-icons-round animate-spin hidden" id="update-spinner">sync</span>
                        <span id="update-text">فحص الآن</span>
                    </button>
                    <p id="update-msg" class="text-xs text-gray-500 mt-3 h-4"></p>
                </div>
            </div>

            <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-gray-400">history</span>
                    سجل التغييرات (Changelog)
                </h3>
                
                <div class="relative border-r border-white/10 mr-3 space-y-8 pr-8">
                    <div class="relative">
                        <div class="absolute -right-[37px] top-1 w-4 h-4 rounded-full bg-primary border-4 border-dark-surface"></div>
                        <h4 class="text-white font-bold text-lg mb-1">v2.5.0 <span class="text-xs font-normal text-gray-500 mr-2">الإصدار الحالي</span></h4>
                        <ul class="space-y-2 mt-3">
                            <li class="flex items-start gap-2 text-sm text-gray-300">
                                <span class="material-icons-round text-green-500 text-sm mt-0.5">check_circle</span>
                                <span>إضافة نظام التنبيهات المتقدم للمخزون (Windows Notifications).</span>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-300">
                                <span class="material-icons-round text-green-500 text-sm mt-0.5">check_circle</span>
                                <span>تحسين واجهة نقاط البيع (POS) ودعم الطباعة الحرارية.</span>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-300">
                                <span class="material-icons-round text-blue-500 text-sm mt-0.5">build</span>
                                <span>تحسينات في أداء قاعدة البيانات وتسريع التقارير.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="relative opacity-60">
                        <div class="absolute -right-[37px] top-1 w-4 h-4 rounded-full bg-gray-600 border-4 border-dark-surface"></div>
                        <h4 class="text-gray-300 font-bold text-lg mb-1">v2.4.0</h4>
                        <ul class="space-y-2 mt-3">
                            <li class="flex items-start gap-2 text-sm text-gray-400">
                                <span class="material-icons-round text-gray-500 text-sm mt-0.5">check</span>
                                <span>إضافة الوضع الليلي (Dark Mode) للنظام بالكامل.</span>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-400">
                                <span class="material-icons-round text-gray-500 text-sm mt-0.5">check</span>
                                <span>دعم تعدد العملات والفواتير الضريبية.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex justify-center gap-6 pt-4">
                <a href="license.php" class="text-sm text-gray-400 hover:text-white transition-colors border-b border-transparent hover:border-primary pb-0.5">اتفاقية الترخيص</a>
            </div>
            
            <div class="text-center text-xs text-gray-600 pb-4" dir="ltr">
                &copy; 2025 Eagle Shadow Technology, HAMZA SAADI, All Rights Reserved.
            </div>

        </div>
    </div>
</main>

<script>
    document.getElementById('check-update-btn').addEventListener('click', function() {
        const btn = this;
        const spinner = document.getElementById('update-spinner');
        const text = document.getElementById('update-text');
        const msg = document.getElementById('update-msg');
        
        btn.disabled = true;
        btn.classList.add('opacity-75');
        spinner.classList.remove('hidden');
        text.textContent = 'جاري التحقق...';
        msg.textContent = '';
        msg.className = 'text-xs text-gray-500 mt-3 h-4';

        // محاكاة الاتصال بالسيرفر
        setTimeout(() => {
            btn.disabled = false;
            btn.classList.remove('opacity-75');
            spinner.classList.add('hidden');
            text.textContent = 'فحص الآن';
            
            msg.textContent = 'أنت تستخدم أحدث إصدار من النظام (v2.5.0)';
            msg.className = 'text-xs text-green-500 mt-3 h-4 font-bold';
        }, 2000);
    });
</script>
<?php require_once 'src/footer.php'; ?>