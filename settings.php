<?php
require_once 'db.php';
require_once 'session.php';

// التحقق من أن المستخدم admin فقط يمكنه التعديل
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

// معالجة حفظ البيانات - فقط للمدراء
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $settings_to_save = [
        'shopName' => $_POST['shopName'] ?? '',
        'shopPhone' => $_POST['shopPhone'] ?? '',
        'shopCity' => $_POST['shopCity'] ?? '',
        'shopAddress' => $_POST['shopAddress'] ?? '',
        'shopDescription' => $_POST['shopDescription'] ?? '',
        'darkMode' => isset($_POST['darkMode']) ? '1' : '0',
        'soundNotifications' => isset($_POST['soundNotifications']) ? '1' : '0',
        'currency' => $_POST['currency'] ?? 'MAD',
        'taxEnabled' => isset($_POST['taxEnabled']) ? '1' : '0',
        'taxRate' => $_POST['taxRate'] ?? '20',
        'taxLabel' => $_POST['taxLabel'] ?? 'TVA',
        'deliveryHomeCity' => $_POST['deliveryHomeCity'] ?? '',
        'deliveryInsideCity' => $_POST['deliveryInsideCity'] ?? '0',
        'deliveryOutsideCity' => $_POST['deliveryOutsideCity'] ?? '0',
        'stockAlertsEnabled' => isset($_POST['stockAlertsEnabled']) ? '1' : '0',
        'stockAlertInterval' => $_POST['stockAlertInterval'] ?? '20'
    ];

    $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

    foreach ($settings_to_save as $name => $value) {
        $stmt->bind_param("sss", $name, $value, $value);
        $stmt->execute();
    }

    $stmt->close();
    header("Location: settings.php?success=" . urlencode("تم حفظ التغييرات بنجاح"));
    exit();
}

$page_title = 'الإعدادات';
$current_page = 'settings.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// جلب الإعدادات
$result = $conn->query("SELECT * FROM settings");
$settings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
}

// تحديد ما إذا كان يجب تعطيل الحقول
$disabledAttr = $isAdmin ? '' : 'disabled';
$readonlyClass = $isAdmin ? '' : 'opacity-60 cursor-not-allowed';
?>

<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <form method="POST" action="settings.php" class="flex-1 flex flex-col overflow-hidden" <?php echo $isAdmin ? '' : 'onsubmit="return false;"'; ?>>
        <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
            <div class="flex items-center gap-4">
                <h2 class="text-xl font-bold text-white">الإعدادات العامة</h2>
                <?php if (!$isAdmin): ?>
                    <span class="text-xs bg-yellow-500/20 text-yellow-500 px-3 py-1 rounded-full font-bold flex items-center gap-1">
                        <span class="material-icons-round text-sm">visibility</span>
                        وضع القراءة فقط
                    </span>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-4">
                <?php if ($isAdmin): ?>
                    <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                        <span class="material-icons-round text-sm">save</span>
                        <span>حفظ التغييرات</span>
                    </button>
                <?php else: ?>
                    <div class="bg-gray-500/20 text-gray-400 px-6 py-2 rounded-xl font-bold flex items-center gap-2 cursor-not-allowed">
                        <span class="material-icons-round text-sm">lock</span>
                        <span>غير مسموح بالتعديل</span>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <?php if (!$isAdmin): ?>
            <div class="mx-8 mt-6 bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 flex items-start gap-3 relative z-10">
                <span class="material-icons-round text-yellow-500 text-xl">info</span>
                <div class="flex-1">
                    <h4 class="text-yellow-500 font-bold mb-1">معلومة هامة</h4>
                    <p class="text-sm text-yellow-500/80">أنت تشاهد الإعدادات في وضع القراءة فقط. للقيام بأي تعديلات، تواصل مع مدير الموقع</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex-1 overflow-y-auto p-8 relative z-10" style="max-height: calc(100vh - 5rem); scroll-behavior: smooth;">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-1">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden sticky top-0">
                        <nav class="flex flex-col">
                            <a href="#store-settings" onclick="document.getElementById('store-settings').scrollIntoView({behavior: 'smooth'}); return false;"
                                class="px-6 py-4 flex items-center gap-3 bg-primary/10 text-primary border-r-2 border-primary hover:bg-primary/20 transition-colors">
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

                <div class="lg:col-span-2 space-y-6">
                    
                    <section id="store-settings" class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel scroll-mt-4">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">store</span>
                            بيانات المتجر
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">اسم المتجر</label>
                                <input type="text" name="shopName" value="<?php echo htmlspecialchars($settings['shopName'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">رقم الهاتف</label>
                                <input type="text" name="shopPhone" value="<?php echo htmlspecialchars($settings['shopPhone'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">المدينة</label>
                                <input type="text" name="shopCity" value="<?php echo htmlspecialchars($settings['shopCity'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">العنوان</label>
                                <input type="text" name="shopAddress" value="<?php echo htmlspecialchars($settings['shopAddress'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-400 mb-2">وصف مختصر</label>
                                <textarea rows="3" name="shopDescription"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>><?php echo htmlspecialchars($settings['shopDescription'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </section>

                    <section id="delivery-section" class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel scroll-mt-4">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <span class="material-icons-round text-primary">local_shipping</span>
                                إعدادات التوصيل
                            </h3>
                            <?php if ($isAdmin): ?>
                                <button type="button" onclick="resetDeliveryPrices()" 
                                    class="group flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 transition-all text-xs text-gray-400 hover:text-white">
                                    <span class="material-icons-round text-sm group-hover:rotate-180 transition-transform duration-500">restart_alt</span>
                                    <span>إعادة تعيين افتراضي</span>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2 flex items-center gap-2">
                                    <span class="material-icons-round text-sm">location_city</span>
                                    مدينة المحل التجاري
                                </label>
                                <input type="text" name="deliveryHomeCity" 
                                    value="<?php echo htmlspecialchars($settings['deliveryHomeCity'] ?? ''); ?>"
                                    placeholder="مثال: الرباط، الدار البيضاء، طنجة..."
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                                <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                    <span class="material-icons-round text-xs">info</span>
                                    سيتم اعتماد هذه المدينة لحساب تكلفة التوصيل "داخل المدينة" تلقائياً
                                </p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">التوصيل داخل المدينة</label>
                                    <div class="relative">
                                        <input type="number" id="deliveryInsideCity" name="deliveryInsideCity" step="0.01" min="0"
                                            value="<?php echo htmlspecialchars($settings['deliveryInsideCity'] ?? '0'); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold"><?php echo htmlspecialchars($settings['currency'] ?? 'MAD'); ?></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">التوصيل خارج المدينة</label>
                                    <div class="relative">
                                        <input type="number" id="deliveryOutsideCity" name="deliveryOutsideCity" step="0.01" min="0"
                                            value="<?php echo htmlspecialchars($settings['deliveryOutsideCity'] ?? '0'); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold"><?php echo htmlspecialchars($settings['currency'] ?? 'MAD'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <section class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                            <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                <span class="material-icons-round text-primary">receipt</span>
                                إعدادات الضريبة
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl <?php echo $readonlyClass; ?>">
                                    <div>
                                        <h4 class="font-bold text-white mb-1">تفعيل الضريبة</h4>
                                        <p class="text-xs text-gray-400">إضافة الضريبة على المبيعات</p>
                                    </div>
                                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="taxEnabled" id="toggle-tax" value="1"
                                            class="toggle-checkbox"
                                            <?php echo (isset($settings['taxEnabled']) && $settings['taxEnabled'] == '1') ? 'checked' : ''; ?>
                                            <?php echo $disabledAttr; ?> />
                                        <label for="toggle-tax" class="toggle-label block overflow-hidden h-6 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">نسبة الضريبة (%)</label>
                                    <input type="number" name="taxRate" value="<?php echo htmlspecialchars($settings['taxRate'] ?? '20'); ?>" step="0.01"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">تسمية الضريبة</label>
                                    <input type="text" name="taxLabel" value="<?php echo htmlspecialchars($settings['taxLabel'] ?? 'TVA'); ?>"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                </div>
                            </div>
                        </section>

                        <section class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                            <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                                <span class="material-icons-round text-primary">tune</span>
                                تفضيلات النظام
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl <?php echo $readonlyClass; ?>">
                                    <div>
                                        <h4 class="font-bold text-white mb-1">الوضع الليلي</h4>
                                        <p class="text-xs text-gray-400">تفعيل الوضع المظلم</p>
                                    </div>
                                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="darkMode" id="toggle-dark" value="1"
                                            class="toggle-checkbox"
                                            <?php echo $isAdmin ? 'onchange="this.form.submit()"' : ''; ?>
                                            <?php echo (isset($settings['darkMode']) && $settings['darkMode'] == '1') ? 'checked' : ''; ?>
                                            <?php echo $disabledAttr; ?> />
                                        <label for="toggle-dark" class="toggle-label block overflow-hidden h-6 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl <?php echo $readonlyClass; ?>">
                                    <div>
                                        <h4 class="font-bold text-white mb-1">الإشعارات الصوتية</h4>
                                        <p class="text-xs text-gray-400">تشغيل صوت عند البيع</p>
                                    </div>
                                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="soundNotifications" id="toggle-sound" value="1"
                                            class="toggle-checkbox"
                                            <?php echo (isset($settings['soundNotifications']) && $settings['soundNotifications'] == '1') ? 'checked' : ''; ?>
                                            <?php echo $disabledAttr; ?> />
                                        <label for="toggle-sound" class="toggle-label block overflow-hidden h-6 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-4 p-4 bg-white/5 rounded-xl transition-colors duration-300" id="stock-alerts-container">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <h4 class="font-bold text-white">تنبيهات المخزون المنخفض</h4>
                                            <button type="button" onclick="openStockGuideModal()" class="flex items-center gap-1 cursor-pointer px-3 py-1 rounded-full bg-primary/10 hover:bg-primary/20 border border-primary/20 hover:border-primary/50 transition-all duration-300 group">
                                                <span class="text-xs font-bold text-primary">كيف تختار؟</span>
                                                <span class="material-icons-round text-[16px] text-primary group-hover:scale-110 transition-transform">help_outline</span>
                                            </button>
                                        </div>

                                        <!-- Enable/Disable Switch -->
                                        <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" name="stockAlertsEnabled" id="toggle-stock-alerts" value="1"
                                                class="toggle-checkbox"
                                                <?php echo (isset($settings['stockAlertsEnabled']) && $settings['stockAlertsEnabled'] == '1') ? 'checked' : ''; ?>
                                                <?php echo $disabledAttr; ?>
                                                onchange="handleStockAlertToggle(this)" />
                                            <label for="toggle-stock-alerts" class="toggle-label block overflow-hidden h-6 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                        </div>
                                    </div>


                                    <div class="p-4 bg-white/5 rounded-xl transition-all duration-300 <?php echo $readonlyClass; ?> <?php echo (!isset($settings['stockAlertsEnabled']) || $settings['stockAlertsEnabled'] == '0') ? 'opacity-50 pointer-events-none grayscale' : ''; ?>" id="stock-alerts-settings">

                                        <label class="block text-sm font-medium text-gray-400 mb-2">
                                            <span class="material-icons-round text-xs align-middle">schedule</span>
                                            مدة التنبيهات (بالدقائق)
                                        </label>
                                        <div class="relative">
                                            <input type="number" 
                                                name="stockAlertInterval" 
                                                value="<?php echo htmlspecialchars($settings['stockAlertInterval'] ?? '20'); ?>"
                                                min="1" 
                                                max="1440" 
                                                step="1"
                                                class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                                placeholder="20"
                                                <?php echo $disabledAttr; ?>>
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold">دقيقة</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2">
                                            <span class="material-icons-round text-xs align-middle">info</span>
                                            سيتم التحقق من حالة المخزون وإرسال الإشعارات كل هذه المدة
                                        </p>
                                    </div>
                                </div>

                                <!-- إشعارات Windows -->
                                <div class="p-4 bg-white/5 rounded-xl">
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <h4 class="font-bold text-white mb-1">إشعارات Windows للمخزون</h4>
                                            <p class="text-xs text-gray-400">تلقي إشعارات نظام Windows للمخزون المنخفض أو المنتهي</p>
                                        </div>
                                    </div>
                                    <button type="button" id="enable-windows-notifications" onclick="enableStockNotifications()" 
                                        class="w-full bg-primary/10 hover:bg-primary/20 text-primary px-4 py-2 rounded-lg font-bold transition-all flex items-center justify-center gap-2">
                                        <span class="material-icons-round text-sm">notifications_active</span>
                                        <span>تفعيل إشعارات Windows</span>
                                    </button>
                                    <p class="text-xs text-gray-500 mt-2 text-center">انقر للسماح بإرسال الإشعارات من المتصفح</p>
                                </div>
                                <div class="p-4 bg-white/5 rounded-xl <?php echo $readonlyClass; ?>">
                                    <div class="mb-3">
                                        <h4 class="font-bold text-white mb-1">العملة</h4>
                                        <p class="text-xs text-gray-400">اختر العملة</p>
                                    </div>
                                    <select name="currency" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
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
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<?php if ($isAdmin): ?>
<script>
    function resetDeliveryPrices() {
        if(confirm('هل أنت متأكد من إعادة تعيين أسعار التوصيل إلى القيم الافتراضية (20/40)؟\nيجب عليك حفظ التغييرات بعد ذلك.')) {
            const insideCity = document.getElementById('deliveryInsideCity');
            const outsideCity = document.getElementById('deliveryOutsideCity');
            
            insideCity.value = '20';
            outsideCity.value = '40';
            
            [insideCity, outsideCity].forEach(el => {
                el.style.transition = 'all 0.3s';
                el.style.borderColor = '#10b981';
                el.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                
                setTimeout(() => {
                    el.style.borderColor = '';
                    el.style.backgroundColor = '';
                }, 500);
            });
        }
    }
</script>
<script>
    // تحديث حالة زر إشعارات Windows
    document.addEventListener('DOMContentLoaded', function() {
        const notifButton = document.getElementById('enable-windows-notifications');
        if (notifButton && 'Notification' in window) {
            if (Notification.permission === 'granted') {
                notifButton.innerHTML = `
                    <span class="material-icons-round text-sm">check_circle</span>
                    <span>إشعارات Windows مفعلة</span>
                `;
                notifButton.classList.remove('bg-primary/10', 'hover:bg-primary/20', 'text-primary');
                notifButton.classList.add('bg-green-500/10', 'hover:bg-green-500/20', 'text-green-500');
                notifButton.disabled = true;
                notifButton.style.cursor = 'default';
            } else if (Notification.permission === 'denied') {
                notifButton.innerHTML = `
                    <span class="material-icons-round text-sm">block</span>
                    <span>الإشعارات محظورة</span>
                `;
                notifButton.classList.remove('bg-primary/10', 'hover:bg-primary/20', 'text-primary');
                notifButton.classList.add('bg-red-500/10', 'hover:bg-red-500/20', 'text-red-500');
                notifButton.onclick = function() {
                    alert('تم حظر الإشعارات. يرجى تفعيلها من إعدادات المتصفح:\n\n1. انقر على أيقونة القفل في شريط العناوين\n2. ابحث عن "الإشعارات"\n3. غير الإعداد إلى "السماح"');
                };
            }
        }
    });
</script>
<script>
    function handleStockAlertToggle(checkbox) {
        const container = document.getElementById('stock-alerts-settings');
        const mainContainer = document.getElementById('stock-alerts-container');
        
        if (!checkbox.checked) {
            // User tries to disable
            const warningMsg = `⚠️ تنبيه هام!\n\nتعطيل تنبيهات المخزون قد يجعلك تفقد السيطرة على منتجاتك وتنفد الكميات دون علمك، مما يؤثر سلباً على مبيعاتك ورضا عملائك.\n\nهل أنت متأكد 100% أنك تريد إيقاف هذه الخاصية الحيوية؟`;
            
            if (confirm(warningMsg)) {
                // Confirmed disable
                container.classList.add('opacity-50', 'pointer-events-none', 'grayscale');
            } else {
                // Cancelled
                checkbox.checked = true;
            }
        } else {
            // User enabled
            container.classList.remove('opacity-50', 'pointer-events-none', 'grayscale');
        }
    }
</script>

<!-- Stock Guide Modal -->
<div id="stockGuideModal" class="fixed inset-0 z-[100] hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="stockGuideBackdrop" onclick="closeStockGuideModal()"></div>
    
    <!-- Modal Content -->
    <?php
    // Theme colors based on Night Mode
    $modalBg = $darkMode ? 'bg-[#1e1e2e]' : 'bg-white';
    $modalBorder = $darkMode ? 'border-white/10' : 'border-gray-200';
    $modalText = $darkMode ? 'text-white' : 'text-gray-900';
    $modalSubText = $darkMode ? 'text-gray-400' : 'text-gray-500';
    $modalSubBg = $darkMode ? 'bg-white/5' : 'bg-gray-50';
    $modalSubBorder = $darkMode ? 'border-white/5' : 'border-gray-200';
    $modalHoverBorder = $darkMode ? 'hover:border-primary/30' : 'hover:border-primary/50';
    ?>
    
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="<?php echo $modalBg; ?> border <?php echo $modalBorder; ?> rounded-2xl w-full max-w-2xl transform scale-95 opacity-0 transition-all duration-300 relative shadow-2xl overflow-hidden" id="stockGuideContent">
            
            <!-- Header -->
            <div class="px-6 py-4 <?php echo $modalSubBg; ?> border-b <?php echo $modalSubBorder; ?> flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <span class="material-icons-round text-primary">tips_and_updates</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold <?php echo $modalText; ?>">دليل اختيار مدة التنبيهات</h3>
                        <p class="text-sm <?php echo $modalSubText; ?>">إرشادات لمساعدتك على اختيار الوقت المناسب</p>
                    </div>
                </div>
                <button onclick="closeStockGuideModal()" class="<?php echo $modalSubText; ?> hover:text-primary transition-colors p-1 hover:bg-black/5 rounded-lg">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Examples Section -->
                    <div>
                        <h5 class="flex items-center gap-2 text-sm font-bold <?php echo $modalSubText; ?> uppercase tracking-wider mb-4 border-b <?php echo $modalSubBorder; ?> pb-2">
                            <span>🔧</span> أمثلة على الاستخدام
                        </h5>
                        <div class="space-y-3">
                            <div class="<?php echo $modalSubBg; ?> rounded-xl p-4 border <?php echo $modalSubBorder; ?> <?php echo $modalHoverBorder; ?> transition-all group">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-bold <?php echo $modalText; ?> group-hover:text-primary transition-colors">للمتاجر الصغيرة</div>
                                    <span class="text-primary font-bold bg-primary/10 px-2 py-0.5 rounded text-xs">30 دقيقة</span>
                                </div>
                                <p class="text-xs <?php echo $modalSubText; ?> leading-relaxed">مناسبة للمحلات ذات حركة البيع المحدودة والمخزون الصغير، لا تتطلب تحديثاً مستمر.</p>
                            </div>

                            <div class="<?php echo $modalSubBg; ?> rounded-xl p-4 border <?php echo $modalSubBorder; ?> <?php echo $modalHoverBorder; ?> transition-all group">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-bold <?php echo $modalText; ?> group-hover:text-primary transition-colors">للمتاجر المتوسطة</div>
                                    <span class="text-primary font-bold bg-primary/10 px-2 py-0.5 rounded text-xs">60 دقيقة</span>
                                </div>
                                <p class="text-xs <?php echo $modalSubText; ?> leading-relaxed">الخيار الأمثل للسوبر ماركت والمتاجر ذات الحجم المتوسط، يوازن بين التحديث والأداء.</p>
                            </div>

                            <div class="<?php echo $modalSubBg; ?> rounded-xl p-4 border <?php echo $modalSubBorder; ?> <?php echo $modalHoverBorder; ?> transition-all group">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-bold <?php echo $modalText; ?> group-hover:text-primary transition-colors">للمتاجر الكبيرة</div>
                                    <span class="text-primary font-bold bg-primary/10 px-2 py-0.5 rounded text-xs">120 دقيقة</span>
                                </div>
                                <p class="text-xs <?php echo $modalSubText; ?> leading-relaxed">للمتاجر الكبيرة جداً (هايبر ماركت) حيث كثرة الاشعارات قد تكون مزعجة.</p>
                            </div>

                            <div class="<?php echo $modalSubBg; ?> rounded-xl p-4 border <?php echo $modalSubBorder; ?> <?php echo $modalHoverBorder; ?> transition-all group">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="font-bold <?php echo $modalText; ?> group-hover:text-primary transition-colors">للمخازن والمستودعات</div>
                                    <span class="text-primary font-bold bg-primary/10 px-2 py-0.5 rounded text-xs">4 ساعات</span>
                                </div>
                                <p class="text-xs <?php echo $modalSubText; ?> leading-relaxed">المخازن لا تحتاج لتحديث لحظي، يكفي التفتقد عدة مرات في اليوم.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tips Section -->
                    <div>
                        <h5 class="flex items-center gap-2 text-sm font-bold <?php echo $modalSubText; ?> uppercase tracking-wider mb-4 border-b <?php echo $modalSubBorder; ?> pb-2">
                            <span>💡</span> نصائح واحتراف
                        </h5>
<div class="space-y-3">
                            <div class="flex items-start gap-4 p-3 rounded-xl <?php echo $modalSubBg; ?> border <?php echo $modalSubBorder; ?> transition-all hover:translate-x-1">
                                <div class="p-2 rounded-lg bg-green-500/10 shrink-0">
                                    <span class="material-icons-round text-green-500">verified</span>
                                </div>
                                <div>
                                    <h6 class="font-bold text-sm <?php echo $modalText; ?> mb-1">نصيحتنا الذهبية</h6>
                                    <p class="text-xs <?php echo $modalSubText; ?> leading-relaxed">ابدأ بالقيمة الافتراضية <strong>(20 دقيقة)</strong> ثم قم بزيادتها إذا وجدت الإشعارات كثيرة جداً.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 p-3 rounded-xl <?php echo $modalSubBg; ?> border <?php echo $modalSubBorder; ?> transition-all hover:translate-x-1">
                                <div class="p-2 rounded-lg bg-red-500/10 shrink-0">
                                    <span class="material-icons-round text-red-500">speed</span>
                                </div>
                                <div>
                                    <h6 class="font-bold text-sm <?php echo $modalText; ?> mb-1">الأداء والموارد</h6>
                                    <p class="text-xs <?php echo $modalSubText; ?> leading-relaxed">تجنب المدد القصيرة جداً (أقل من 5 دقائق) لأنها تستهلك موارد النظام وتسبب ضغطاً بصرياً.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 p-3 rounded-xl <?php echo $modalSubBg; ?> border <?php echo $modalSubBorder; ?> transition-all hover:translate-x-1">
                                <div class="p-2 rounded-lg bg-orange-500/10 shrink-0">
                                    <span class="material-icons-round text-orange-500">trending_up</span>
                                </div>
                                <div>
                                    <h6 class="font-bold text-sm <?php echo $modalText; ?> mb-1">المواسم والذروة</h6>
                                    <p class="text-xs <?php echo $modalSubText; ?> leading-relaxed">في أوقات ضغط العمل، قم بتقليل المدة لمتابعة نفاذ الكميات بشكل أدق وأسرع.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 p-3 rounded-xl <?php echo $modalSubBg; ?> border <?php echo $modalSubBorder; ?> transition-all hover:translate-x-1">
                                <div class="p-2 rounded-lg bg-blue-500/10 shrink-0">
                                    <span class="material-icons-round text-blue-500">desktop_windows</span>
                                </div>
                                <div>
                                    <h6 class="font-bold text-sm <?php echo $modalText; ?> mb-1">إشعارات سطح المكتب</h6>
                                    <p class="text-xs <?php echo $modalSubText; ?> leading-relaxed">فعّل إشعارات Windows لتبقى على اطلاع دائم حتى لو كانت نافذة المتصفح مصغرة.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 <?php echo $modalSubBg; ?> border-t <?php echo $modalSubBorder; ?> flex justify-end">
                <button onclick="closeStockGuideModal()" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold transition-all shadow-lg shadow-primary/20 hover:-translate-y-0.5">
                    فهمت، شكراً
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openStockGuideModal() {
        const modal = document.getElementById('stockGuideModal');
        const backdrop = document.getElementById('stockGuideBackdrop');
        const content = document.getElementById('stockGuideContent');
        
        modal.classList.remove('hidden');
        // Trigger reflow
        void modal.offsetWidth;
        
        backdrop.classList.remove('opacity-0');
        content.classList.remove('opacity-0', 'scale-95');
        content.classList.add('opacity-100', 'scale-100');
    }

    function closeStockGuideModal() {
        const modal = document.getElementById('stockGuideModal');
        const backdrop = document.getElementById('stockGuideBackdrop');
        const content = document.getElementById('stockGuideContent');
        
        backdrop.classList.add('opacity-0');
        content.classList.remove('opacity-100', 'scale-100');
        content.classList.add('opacity-0', 'scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
<?php endif; ?>

<?php require_once 'src/footer.php'; ?>