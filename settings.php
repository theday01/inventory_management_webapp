<?php
require_once 'db.php';
require_once 'session.php';

// Handle POST request to save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_to_save = [
        'shopName' => $_POST['shopName'] ?? '',
        'shopPhone' => $_POST['shopPhone'] ?? '',
        'shopAddress' => $_POST['shopAddress'] ?? '',
        'shopDescription' => $_POST['shopDescription'] ?? '',
        'darkMode' => isset($_POST['darkMode']) ? '1' : '0',
        'soundNotifications' => isset($_POST['soundNotifications']) ? '1' : '0',
        'currency' => $_POST['currency'] ?? 'MAD',
        'taxEnabled' => isset($_POST['taxEnabled']) ? '1' : '0',
        'taxRate' => $_POST['taxRate'] ?? '20',
        'taxLabel' => $_POST['taxLabel'] ?? 'TVA'
    ];

    $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

    foreach ($settings_to_save as $name => $value) {
        $stmt->bind_param("sss", $name, $value, $value);
        $stmt->execute();
    }

    $stmt->close();
    // Redirect to avoid form resubmission
    header("Location: settings.php?saved=true");
    exit();
}

$page_title = 'الإعدادات - Smart Shop';
$current_page = 'settings.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch all settings from the database
$result = $conn->query("SELECT * FROM settings");
$settings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
}

?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div
        class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <form method="POST" action="settings.php" class="flex-1 flex flex-col">
        <!-- Header -->
        <header
            class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
            <h2 class="text-xl font-bold text-white">الإعدادات العامة</h2>
            <div class="flex items-center gap-4">
                <?php if (isset($_GET['saved']) && $_GET['saved'] === 'true'): ?>
                    <div class="bg-green-500/20 text-green-400 px-4 py-2 rounded-xl flex items-center gap-2">
                        <span class="material-icons-round text-sm">check_circle</span>
                        <span>تم حفظ التغييرات بنجاح</span>
                    </div>
                <?php endif; ?>
                <button type="submit"
                    class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                    <span class="material-icons-round text-sm">save</span>
                    <span>حفظ التغييرات</span>
                </button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Settings Menu -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
                        <nav class="flex flex-col">
                            <a href="settings.php"
                                class="px-6 py-4 flex items-center gap-3 bg-primary/10 text-primary border-r-2 border-primary">
                                <span class="material-icons-round">store</span>
                                <span class="font-bold">إعدادات المتجر</span>
                            </a>
                            <a href="users.php"
                                class="px-6 py-4 flex items-center gap-3 text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-r-2 border-transparent">
                                <span class="material-icons-round">group</span>
                                <span class="font-bold">المستخدمين</span>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Settings Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- General Info -->
                    <section
                        class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">store</span>
                            بيانات المتجر
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">اسم المتجر</label>
                                <input type="text" id="shopName" name="shopName" value="<?php echo htmlspecialchars($settings['shopName'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">رقم الهاتف</label>
                                <input type="text" id="shopPhone" name="shopPhone" value="<?php echo htmlspecialchars($settings['shopPhone'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-400 mb-2">العنوان</label>
                                <input type="text" id="shopAddress" name="shopAddress" value="<?php echo htmlspecialchars($settings['shopAddress'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-400 mb-2">وصف مختصر</label>
                                <textarea rows="3" id="shopDescription" name="shopDescription"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all"><?php echo htmlspecialchars($settings['shopDescription'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </section>

                    <!-- Tax Settings -->
                    <section
                        class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">receipt</span>
                            إعدادات الضريبة
                        </h3>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                                <div>
                                    <h4 class="font-bold text-white mb-1">تفعيل الضريبة</h4>
                                    <p class="text-xs text-gray-400">إضافة الضريبة على المبيعات</p>
                                </div>
                                <div
                                    class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="taxEnabled" id="toggle-tax" value="1"
                                        class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer left-0 top-0 checked:left-6 checked:bg-primary transition-all duration-300"
                                        <?php echo (isset($settings['taxEnabled']) && $settings['taxEnabled'] == '1') ? 'checked' : ''; ?> />
                                    <label for="toggle-tax"
                                        class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">نسبة الضريبة (%)</label>
                                    <input type="number" id="taxRate" name="taxRate" 
                                        value="<?php echo htmlspecialchars($settings['taxRate'] ?? '20'); ?>"
                                        step="0.01" min="0" max="100"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                                    <p class="text-xs text-gray-500 mt-1">مثال: 20 للـ 20%</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">تسمية الضريبة</label>
                                    <input type="text" id="taxLabel" name="taxLabel" 
                                        value="<?php echo htmlspecialchars($settings['taxLabel'] ?? 'TVA'); ?>"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                                    <p class="text-xs text-gray-500 mt-1">مثال: TVA أو ضريبة القيمة المضافة</p>
                                </div>
                            </div>

                            <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <span class="material-icons-round text-blue-400 mt-0.5">info</span>
                                    <div>
                                        <h5 class="font-bold text-blue-400 mb-1">ملاحظة</h5>
                                        <p class="text-sm text-blue-300">سيتم تطبيق نسبة الضريبة المحددة على جميع المبيعات في نقطة البيع. يمكنك تعطيل الضريبة في أي وقت.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Preferences -->
                    <section
                        class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">tune</span>
                            تفضيلات النظام
                        </h3>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                                <div>
                                    <h4 class="font-bold text-white mb-1">الوضع الليلي</h4>
                                    <p class="text-xs text-gray-400">تفعيل الوضع المظلم بشكل دائم</p>
                                </div>
                                <div
                                    class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="darkMode" id="toggle-dark" value="1"
                                        class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer left-0 top-0 checked:left-6 checked:bg-primary transition-all duration-300"
                                        <?php echo (isset($settings['darkMode']) && $settings['darkMode'] == '1') ? 'checked' : ''; ?> />
                                    <label for="toggle-dark"
                                        class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                                <div>
                                    <h4 class="font-bold text-white mb-1">الإشعارات الصوتية</h4>
                                    <p class="text-xs text-gray-400">تشغيل صوت عند إتمام عملية بيع</p>
                                </div>
                                <div
                                    class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="soundNotifications" id="toggle-sound" value="1"
                                        class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer left-0 top-0 checked:left-6 checked:bg-primary transition-all duration-300"
                                        <?php echo (isset($settings['soundNotifications']) && $settings['soundNotifications'] == '1') ? 'checked' : ''; ?> />
                                    <label for="toggle-sound"
                                        class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-700 cursor-pointer"></label>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl">
                                <div>
                                    <h4 class="font-bold text-white mb-1">العملة</h4>
                                    <p class="text-xs text-gray-400">اختر العملة المستخدمة في النظام</p>
                                </div>
                                <div class="relative">
                                    <select name="currency" id="currency"
                                        class="w-48 bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                                        <option value="MAD" <?php echo (isset($settings['currency']) && $settings['currency'] == 'MAD') ? 'selected' : ''; ?>>الدرهم المغربي</option>
                                        <option value="SAR" <?php echo (isset($settings['currency']) && $settings['currency'] == 'SAR') ? 'selected' : ''; ?>>ريال سعودي</option>
                                        <option value="QAR" <?php echo (isset($settings['currency']) && $settings['currency'] == 'QAR') ? 'selected' : ''; ?>>ريال قطري</option>
                                        <option value="BHD" <?php echo (isset($settings['currency']) && $settings['currency'] == 'BHD') ? 'selected' : ''; ?>>دينار بحريني</option>
                                        <option value="EGP" <?php echo (isset($settings['currency']) && $settings['currency'] == 'EGP') ? 'selected' : ''; ?>>جنيه مصري</option>
                                        <option value="LYD" <?php echo (isset($settings['currency']) && $settings['currency'] == 'LYD') ? 'selected' : ''; ?>>دينار ليبي</option>
                                        <option value="DZD" <?php echo (isset($settings['currency']) && $settings['currency'] == 'DZD') ? 'selected' : ''; ?>>دينار جزائري</option>
                                        <option value="TND" <?php echo (isset($settings['currency']) && $settings['currency'] == 'TND') ? 'selected' : ''; ?>>دينار تونسي</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </form>
</main>

<?php require_once 'src/footer.php'; ?>