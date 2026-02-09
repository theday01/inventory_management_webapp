<?php
require_once 'session.php';
require_once 'src/language.php';
require_once 'src/config_version.php';

$page_title = __('version_page_title');
$current_page = 'version.php';

require_once 'src/header.php';
require_once 'src/sidebar.php';

// System Data
$systemVersion = defined('APP_VERSION') ? APP_VERSION : '2.5.0';
$buildNumber = defined('BUILD_NUMBER') ? BUILD_NUMBER : '20250103-RC';
$releaseDate = defined('RELEASE_DATE') ? RELEASE_DATE : '2025-01-03';

// Technical Data
$phpVersion = phpversion();
$dbStatus = $conn->ping() ? __('db_connected') : __('db_disconnected');
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? php_uname('s') . ' ' . php_uname('r');
$memoryLimit = ini_get('memory_limit');
$uploadMax = ini_get('upload_max_filesize');
$serverIP = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$loadedExtensions = get_loaded_extensions();
$extensionCount = count($loadedExtensions);

// Get MySQL Version
$mysqlVersion = 'Unknown';
if ($conn) {
    $verRes = $conn->query("SELECT VERSION() as ver");
    if ($verRes && $verRes->num_rows > 0) {
        $mysqlVersion = $verRes->fetch_assoc()['ver'];
    }
}

?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-4 lg:px-8 relative z-20 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">settings_suggest</span>
            <?php echo __('version_page_title'); ?>
        </h2>
    </header>

    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative z-10">

        <?php require_once 'src/settings_sidebar.php'; ?>

        <div class="flex-1 overflow-y-auto p-4 lg:p-8 relative z-10 custom-scrollbar">
        <div class="max-w-5xl mx-auto space-y-6">
            
            <!-- Main Version Card -->
            <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl p-8 glass-panel text-center relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-primary via-accent to-purple-600"></div>
                
                <div class="h-6"></div>

                <h1 class="text-3xl font-bold text-white mb-2">Smart Shop <span class="text-primary text-sm px-2 py-0.5 bg-primary/10 rounded-lg border border-primary/20"><?php echo __('smart_shop_title_sub'); ?></span></h1>
                
                <div class="flex flex-wrap items-center justify-center gap-3 mb-8">
                    <span class="px-3 py-1 rounded-full bg-green-500/10 text-green-500 text-xs font-bold border border-green-500/20 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        <?php echo __('stable'); ?>
                    </span>
                    <span class="px-3 py-1 rounded-full bg-white/5 text-gray-400 text-xs font-bold border border-white/10 font-mono">
                        <?php echo __('build_label'); ?>: <?php echo $buildNumber; ?>
                    </span>
                    <span class="px-3 py-1 rounded-full bg-blue-500/10 text-blue-400 text-xs font-bold border border-blue-500/20">
                        <?php echo __('env_label'); ?>: <?php echo __('env_production'); ?>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center border-t border-white/5 pt-6">
                    <div class="p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition-colors">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <span class="material-icons-round text-gray-400 text-sm">tag</span>
                            <p class="text-gray-400 text-sm"><?php echo __('version_number'); ?></p>
                        </div>
                        <p class="text-2xl font-bold text-white font-mono tracking-wider"><?php echo $systemVersion; ?></p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition-colors">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <span class="material-icons-round text-gray-400 text-sm">event</span>
                            <p class="text-gray-400 text-sm"><?php echo __('update_date'); ?></p>
                        </div>
                        <p class="text-xl font-bold text-white"><?php echo $releaseDate; ?></p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/5 hover:bg-white/10 transition-colors">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <span class="material-icons-round text-gray-400 text-sm">verified_user</span>
                            <p class="text-gray-400 text-sm"><?php echo __('license_status'); ?></p>
                        </div>
                        <p class="text-xl font-bold text-accent flex items-center justify-center gap-1">
                            <span><?php echo __('license_active'); ?></span>
                            <span class="material-icons-round text-sm">check_circle</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- System Health Card -->
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel h-full">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <span class="p-2 bg-pink-500/10 rounded-lg text-pink-500"><span class="material-icons-round">monitor_heart</span></span>
                        <?php echo __('system_health'); ?>
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="material-icons-round text-indigo-400">php</span>
                                <span class="text-gray-300 text-sm"><?php echo __('php_version'); ?></span>
                            </div>
                            <span class="text-white font-mono text-sm bg-black/20 px-2 py-1 rounded"><?php echo $phpVersion; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="material-icons-round text-orange-400">storage</span>
                                <span class="text-gray-300 text-sm"><?php echo __('mysql_version'); ?></span>
                            </div>
                            <span class="text-white font-mono text-sm bg-black/20 px-2 py-1 rounded"><?php echo $mysqlVersion; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="material-icons-round text-blue-400">dns</span>
                                <span class="text-gray-300 text-sm"><?php echo __('server_software'); ?></span>
                            </div>
                            <span class="text-white font-mono text-xs bg-black/20 px-2 py-1 rounded max-w-[150px] truncate" title="<?php echo htmlspecialchars($serverSoftware); ?>">
                                <?php echo htmlspecialchars(substr($serverSoftware, 0, 20)) . '...'; ?>
                            </span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="material-icons-round text-green-400">memory</span>
                                <span class="text-gray-300 text-sm"><?php echo __('memory_limit'); ?></span>
                            </div>
                            <span class="text-white font-bold text-sm"><?php echo $memoryLimit; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="material-icons-round text-yellow-400">cloud_upload</span>
                                <span class="text-gray-300 text-sm"><?php echo __('max_upload_size'); ?></span>
                            </div>
                            <span class="text-white font-bold text-sm"><?php echo $uploadMax; ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="material-icons-round text-purple-400">extension</span>
                                <span class="text-gray-300 text-sm"><?php echo __('php_extensions'); ?></span>
                            </div>
                            <span class="text-gray-400 text-xs"><?php echo sprintf(__('extensions_loaded'), $extensionCount); ?></span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-6">
                    <!-- Update Checker -->
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel flex flex-col justify-center items-center text-center flex-1">
                        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                            <span class="material-icons-round text-3xl text-primary">system_update</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2"><?php echo __('check_updates_title'); ?></h3>
                        <p class="text-gray-400 text-sm mb-6 max-w-xs mx-auto"><?php echo __('check_updates_desc'); ?></p>
                        
                        <button id="check-update-btn" class="bg-primary hover:bg-primary-hover text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all w-full flex items-center justify-center gap-2 group">
                            <span class="material-icons-round group-hover:rotate-180 transition-transform duration-500" id="update-icon">sync</span>
                            <span id="update-text"><?php echo __('check_now_btn'); ?></span>
                        </button>
                        <p id="update-msg" class="text-xs text-gray-500 mt-4 h-4 transition-all opacity-0 transform translate-y-2"></p>
                    </div>

                    <!-- Connection Info -->
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-sm font-bold text-gray-400 mb-4 uppercase tracking-wider"><?php echo __('env_label'); ?></h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-black/20 p-3 rounded-xl text-center">
                                <p class="text-[10px] text-gray-500 mb-1"><?php echo __('server_ip_label'); ?></p>
                                <p class="text-white font-mono text-xs"><?php echo $serverIP; ?></p>
                            </div>
                            <div class="bg-black/20 p-3 rounded-xl text-center">
                                <p class="text-[10px] text-gray-500 mb-1"><?php echo __('client_ip_label'); ?></p>
                                <p class="text-white font-mono text-xs"><?php echo $clientIP; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Changelog -->
            <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="p-2 bg-blue-500/10 rounded-lg text-blue-500"><span class="material-icons-round">history_edu</span></span>
                    <?php echo __('changelog_title'); ?>
                </h3>
                
                <div class="relative border-r border-white/10 mr-3 space-y-8 pr-8">
                    <!-- Upcoming Features -->
                    <div class="relative mb-12">
                        <div class="absolute -right-[39px] top-1 w-5 h-5 rounded-full bg-purple-500 border-4 border-dark-surface shadow-lg shadow-purple-500/50 animate-pulse"></div>
                        <div class="flex items-center gap-3 mb-3">
                            <h4 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-400">
                                <?php echo __('coming_soon_title'); ?>
                            </h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-400 border border-purple-500/20">
                                <?php echo __('next_release'); ?>
                            </span>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-xl p-5 border border-purple-500/20 relative overflow-hidden group hover:border-purple-500/40 transition-colors">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-2xl -mr-16 -mt-16 pointer-events-none"></div>
                            
                            <p class="text-sm text-gray-300 mb-4 relative z-10 italic">
                                <?php echo __('coming_soon_desc'); ?>
                            </p>
                            
                            <ul class="space-y-3 relative z-10">
                                <li class="flex items-start gap-3 bg-dark-surface/50 p-3 rounded-lg border border-white/5">
                                    <span class="material-icons-round text-purple-400 mt-0.5">local_shipping</span>
                                    <div>
                                        <h5 class="text-white font-bold text-sm mb-1"><?php echo __('feature_delivery_system'); ?></h5>
                                        <p class="text-xs text-gray-400 leading-relaxed"><?php echo __('feature_delivery_desc'); ?></p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Version 2.5.0 -->
                    <div class="relative">
                        <div class="absolute -right-[39px] top-1 w-5 h-5 rounded-full bg-primary border-4 border-dark-surface shadow-lg shadow-primary/20"></div>
                        <div class="flex items-center gap-3 mb-3">
                            <h4 class="text-white font-bold text-xl">v<?php echo $systemVersion; ?></h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary/20 text-primary border border-primary/20"><?php echo __('current_version_tag'); ?></span>
                            <span class="text-xs text-gray-500"><?php echo $releaseDate; ?></span>
                        </div>
                        
                        <div class="bg-white/5 rounded-xl p-4 border border-white/5">
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 text-sm text-gray-300">
                                    <span class="material-icons-round text-green-500 text-sm mt-0.5">check_circle</span>
                                    <span><?php echo __('changelog_1'); ?></span>
                                </li>
                                <li class="flex items-start gap-3 text-sm text-gray-300">
                                    <span class="material-icons-round text-green-500 text-sm mt-0.5">check_circle</span>
                                    <span><?php echo __('changelog_2'); ?></span>
                                </li>
                                <li class="flex items-start gap-3 text-sm text-gray-300">
                                    <span class="material-icons-round text-blue-500 text-sm mt-0.5">build</span>
                                    <span><?php echo __('changelog_3'); ?></span>
                                </li>
                                <li class="flex items-start gap-3 text-sm text-gray-300">
                                    <span class="material-icons-round text-blue-500 text-sm mt-0.5">build</span>
                                    <span><?php echo __('changelog_4'); ?></span>
                                </li>
                                <li class="flex items-start gap-3 text-sm text-gray-300">
                                    <span class="material-icons-round text-purple-500 text-sm mt-0.5">auto_awesome</span>
                                    <span><?php echo __('changelog_5'); ?></span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Version 2.4.0 -->
                    <div class="relative opacity-60 hover:opacity-100 transition-opacity">
                        <div class="absolute -right-[39px] top-1 w-5 h-5 rounded-full bg-gray-600 border-4 border-dark-surface"></div>
                        <div class="flex items-center gap-3 mb-3">
                            <h4 class="text-gray-300 font-bold text-lg"><?php echo __('v2_4_0_title'); ?> (v2.4.0)</h4>
                            <span class="text-xs text-gray-500">2024-06-15</span>
                        </div>
                        <ul class="space-y-2 pl-2">
                            <li class="flex items-start gap-2 text-sm text-gray-400">
                                <span class="material-icons-round text-gray-500 text-sm mt-0.5">check</span>
                                <span><?php echo __('v2_4_0_desc1'); ?></span>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-400">
                                <span class="material-icons-round text-gray-500 text-sm mt-0.5">check</span>
                                <span><?php echo __('v2_4_0_desc2'); ?></span>
                            </li>
                        </ul>
                    </div>

                    <!-- Version 2.3.0 -->
                    <div class="relative opacity-60 hover:opacity-100 transition-opacity">
                        <div class="absolute -right-[39px] top-1 w-5 h-5 rounded-full bg-gray-600 border-4 border-dark-surface"></div>
                        <div class="flex items-center gap-3 mb-3">
                            <h4 class="text-gray-300 font-bold text-lg"><?php echo __('v2_3_0_title'); ?> (v2.3.0)</h4>
                            <span class="text-xs text-gray-500">2024-01-10</span>
                        </div>
                        <ul class="space-y-2 pl-2">
                            <li class="flex items-start gap-2 text-sm text-gray-400">
                                <span class="material-icons-round text-gray-500 text-sm mt-0.5">check</span>
                                <span><?php echo __('v2_3_0_desc1'); ?></span>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-400">
                                <span class="material-icons-round text-gray-500 text-sm mt-0.5">check</span>
                                <span><?php echo __('v2_3_0_desc2'); ?></span>
                            </li>
                        </ul>
                    </div>

                    <!-- Version 2.0.0 -->
                    <div class="relative opacity-50 hover:opacity-100 transition-opacity">
                        <div class="absolute -right-[39px] top-1 w-5 h-5 rounded-full bg-gray-700 border-4 border-dark-surface"></div>
                        <div class="flex items-center gap-3 mb-3">
                            <h4 class="text-gray-400 font-bold text-lg"><?php echo __('v2_0_0_title'); ?> (v2.0.0)</h4>
                            <span class="text-xs text-gray-600">2023-05-20</span>
                        </div>
                        <ul class="space-y-2 pl-2">
                            <li class="flex items-start gap-2 text-sm text-gray-500">
                                <span class="material-icons-round text-gray-600 text-sm mt-0.5">star</span>
                                <span><?php echo __('v2_0_0_desc1'); ?></span>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-500">
                                <span class="material-icons-round text-gray-600 text-sm mt-0.5">translate</span>
                                <span><?php echo __('v2_0_0_desc2'); ?></span>
                            </li>
                        </ul>
                    </div>

                    <!-- Version 1.5.0 -->
                    <div class="relative opacity-50 hover:opacity-100 transition-opacity">
                        <div class="absolute -right-[39px] top-1 w-5 h-5 rounded-full bg-gray-700 border-4 border-dark-surface"></div>
                        <div class="flex items-center gap-3 mb-3">
                            <h4 class="text-gray-400 font-bold text-lg"><?php echo __('v1_5_0_title'); ?> (v1.5.0)</h4>
                            <span class="text-xs text-gray-600">2022-11-05</span>
                        </div>
                        <ul class="space-y-2 pl-2">
                            <li class="flex items-start gap-2 text-sm text-gray-500">
                                <span class="material-icons-round text-gray-600 text-sm mt-0.5">trending_up</span>
                                <span><?php echo __('v1_5_0_desc1'); ?></span>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-500">
                                <span class="material-icons-round text-gray-600 text-sm mt-0.5">inventory_2</span>
                                <span><?php echo __('v1_5_0_desc2'); ?></span>
                            </li>
                        </ul>
                    </div>

                    <!-- Version 1.0.0 -->
                    <div class="relative opacity-40 hover:opacity-100 transition-opacity">
                        <div class="absolute -right-[39px] top-1 w-5 h-5 rounded-full bg-gray-800 border-4 border-dark-surface"></div>
                        <div class="flex items-center gap-3 mb-3">
                            <h4 class="text-gray-500 font-bold text-lg"><?php echo __('v1_0_0_title'); ?> (v1.0.0)</h4>
                            <span class="text-xs text-gray-600">2022-01-01</span>
                        </div>
                        <ul class="space-y-2 pl-2">
                            <li class="flex items-start gap-2 text-sm text-gray-500">
                                <span class="material-icons-round text-gray-600 text-sm mt-0.5">rocket_launch</span>
                                <span><?php echo __('v1_0_0_desc1'); ?></span>
                            </li>
                            <li class="flex items-start gap-2 text-sm text-gray-500">
                                <span class="material-icons-round text-gray-600 text-sm mt-0.5">check</span>
                                <span><?php echo __('v1_0_0_desc2'); ?></span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

            <div class="flex justify-center gap-6 pt-4 pb-2">
                <a href="license.php" class="text-sm text-gray-400 hover:text-white transition-colors border-b border-transparent hover:border-primary pb-0.5 flex items-center gap-1">
                    <span class="material-icons-round text-xs">gavel</span>
                    <?php echo __('license_agreement_link'); ?>
                </a>
                <a href="contact.php" class="text-sm text-gray-400 hover:text-white transition-colors border-b border-transparent hover:border-primary pb-0.5 flex items-center gap-1">
                    <span class="material-icons-round text-xs">support_agent</span>
                    <?php echo __('contact_link'); ?>
                </a>
            </div>
            
            <div class="text-center text-xs text-gray-600 pb-8" dir="ltr">
                <?php echo sprintf(__('copyright_footer'), date('Y')); ?>
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
</main>

<script>
    document.getElementById('check-update-btn').addEventListener('click', function() {
        const btn = this;
        const icon = document.getElementById('update-icon');
        const text = document.getElementById('update-text');
        const msg = document.getElementById('update-msg');
        
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        icon.classList.add('animate-spin');
        icon.textContent = 'sync';
        text.textContent = '<?php echo __('checking_updates'); ?>';
        
        msg.textContent = '';
        msg.classList.remove('opacity-100', 'translate-y-0');
        msg.classList.add('opacity-0', 'translate-y-2');

        // Simulate Server Connection
        setTimeout(() => {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            icon.classList.remove('animate-spin');
            icon.textContent = 'check_circle';
            text.textContent = '<?php echo __('check_now_btn'); ?>';
            
            // Format message with version
            const versionMsg = "<?php echo __('latest_version_msg'); ?>".replace('%s', 'v<?php echo $systemVersion; ?>');
            msg.textContent = versionMsg;
            msg.className = 'text-xs text-green-500 mt-4 h-4 font-bold transition-all opacity-100 transform translate-y-0';
            
            setTimeout(() => {
                icon.textContent = 'sync';
            }, 3000);
        }, 2000);
    });
</script>
<?php require_once 'src/footer.php'; ?>
