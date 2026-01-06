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
        'low_quantity_alert' => $_POST['low_quantity_alert'] ?? '30',
        'critical_quantity_alert' => $_POST['critical_quantity_alert'] ?? '10',
        'deliveryHomeCity' => $_POST['deliveryHomeCity'] ?? '',
        'deliveryInsideCity' => $_POST['deliveryInsideCity'] ?? '0',
        'deliveryOutsideCity' => $_POST['deliveryOutsideCity'] ?? '0',
        'stockAlertsEnabled' => isset($_POST['stockAlertsEnabled']) ? '1' : '0',
        'stockAlertInterval' => $_POST['stockAlertInterval'] ?? '20',
        // ===== إعدادات الإيجار المحدثة =====
        'rentalEnabled' => isset($_POST['rentalEnabled']) ? '1' : '0',
        'rentalAmount' => $_POST['rentalAmount'] ?? '0',
        'rentalPaymentDate' => $_POST['rentalPaymentDate'] ?? date('Y-m-01'), // التاريخ الجديد
        'rentalType' => $_POST['rentalType'] ?? 'monthly', // نوعية التأجير
        'rentalReminderDays' => $_POST['rentalReminderDays'] ?? '7',
        'rentalLandlordName' => $_POST['rentalLandlordName'] ?? '',
        'rentalLandlordPhone' => $_POST['rentalLandlordPhone'] ?? '',
        'rentalNotes' => $_POST['rentalNotes'] ?? ''
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

$page_title = 'إعدادات النظام';
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

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <form method="POST" action="settings.php" class="flex-1 flex flex-col overflow-hidden" <?php echo $isAdmin ? '' : 'onsubmit="return false;"'; ?>>
        
        <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
            <div class="flex items-center gap-4">
                <div class="p-2.5 bg-primary/10 rounded-xl">
                    <span class="material-icons-round text-primary">settings</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">إعدادات النظام</h2>
                    <p class="text-xs text-gray-400">تحكم في تفاصيل المتجر، التنبيهات، والضرائب</p>
                </div>
                
                <?php if (!$isAdmin): ?>
                    <span class="mr-4 text-xs bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 px-3 py-1 rounded-full font-bold flex items-center gap-1">
                        <span class="material-icons-round text-sm">visibility</span>
                        وضع القراءة فقط
                    </span>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-4">
                <?php if ($isAdmin): ?>
                    <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
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

        <div class="flex-1 overflow-hidden flex relative z-10">
            
            <aside class="w-72 bg-dark-surface/30 backdrop-blur-md border-l border-white/5 flex flex-col shrink-0 py-6 px-4 gap-2 overflow-y-auto">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider px-4 mb-2">أقسام الإعدادات</div>
                
                <button type="button" onclick="switchTab('store')" id="tab-btn-store" class="tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-right transition-all group active-tab">
                    <span class="material-icons-round text-[20px] transition-colors">store</span>
                    <div class="flex-1">
                        <span class="font-bold text-sm block">بيانات المتجر</span>
                        <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">الاسم، العنوان، الهاتف</span>
                    </div>
                </button>

                <button type="button" onclick="switchTab('delivery')" id="tab-btn-delivery" class="tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-right transition-all group text-gray-400 hover:text-white hover:bg-white/5">
                    <span class="material-icons-round text-[20px] transition-colors">local_shipping</span>
                    <div class="flex-1">
                        <span class="font-bold text-sm block">الشحن والتوصيل</span>
                        <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">المدن والأسعار</span>
                    </div>
                </button>

                <button type="button" onclick="switchTab('rental')" id="tab-btn-rental" class="tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-right transition-all group text-gray-400 hover:text-white hover:bg-white/5">
                    <span class="material-icons-round text-[20px] transition-colors">home_work</span>
                    <div class="flex-1">
                        <span class="font-bold text-sm block">إدارة الإيجار</span>
                        <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">الدفعات والتذكيرات</span>
                    </div>
                </button>

                <button type="button" onclick="switchTab('finance')" id="tab-btn-finance" class="tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-right transition-all group text-gray-400 hover:text-white hover:bg-white/5">
                    <span class="material-icons-round text-[20px] transition-colors">receipt_long</span>
                    <div class="flex-1">
                        <span class="font-bold text-sm block">المالية والضرائب</span>
                        <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">العملة ونسب الضريبة</span>
                    </div>
                </button>

                <button type="button" onclick="switchTab('system')" id="tab-btn-system" class="tab-btn flex items-center gap-3 px-4 py-3 rounded-xl text-right transition-all group text-gray-400 hover:text-white hover:bg-white/5">
                    <span class="material-icons-round text-[20px] transition-colors">tune</span>
                    <div class="flex-1">
                        <span class="font-bold text-sm block">النظام والتنبيهات</span>
                        <span class="text-[10px] text-gray-400 block group-hover:text-gray-300">الوضع الليلي والمخزون</span>
                    </div>
                </button>

                <div class="my-2 border-t border-white/5"></div>
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wider px-4 mb-2">روابط سريعة</div>

                <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-primary transition-colors">people</span>
                    <span class="font-medium text-sm">المستخدمين</span>
                </a>
                
                <a href="version.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-primary transition-colors">info</span>
                    <span class="font-medium text-sm">إصدار النظام</span>
                </a>

                <a href="license.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 transition-all group">
                    <span class="material-icons-round text-[20px] group-hover:text-yellow-500 transition-colors">verified_user</span>
                    <span class="font-medium text-sm">الترخيص</span>
                </a>
            </aside>

            <div class="flex-1 overflow-y-auto p-8 relative scroll-smooth" id="settings-content-area">
                
                <div id="tab-content-store" class="tab-content space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-8 glass-panel">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3 border-b border-white/5 pb-4">
                            <span class="material-icons-round text-primary">store</span>
                            البيانات الأساسية للمتجر
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-400 mb-2">اسم المتجر</label>
                                <input type="text" name="shopName" value="<?php echo htmlspecialchars($settings['shopName'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>
                            
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-400 mb-2">رقم الهاتف</label>
                                <input type="text" name="shopPhone" value="<?php echo htmlspecialchars($settings['shopPhone'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-400 mb-2">وصف مختصر (يظهر في الفواتير)</label>
                                <textarea rows="2" name="shopDescription"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all resize-none <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>><?php echo htmlspecialchars($settings['shopDescription'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-400 mb-2">المدينة</label>
                                <input type="text" name="shopCity" value="<?php echo htmlspecialchars($settings['shopCity'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>

                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-sm font-medium text-gray-400 mb-2">العنوان</label>
                                <input type="text" name="shopAddress" value="<?php echo htmlspecialchars($settings['shopAddress'] ?? ''); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-content-delivery" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-8 glass-panel">
                        <div class="flex items-center justify-between mb-6 border-b border-white/5 pb-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">local_shipping</span>
                                إعدادات التوصيل
                            </h3>
                            <?php if ($isAdmin): ?>
                                <button type="button" onclick="resetDeliveryPrices()" 
                                    class="text-xs flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all">
                                    <span class="material-icons-round text-sm">restart_alt</span>
                                    <span>إعادة تعيين</span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                <label class="block text-sm font-bold text-white mb-2 flex items-center gap-2">
                                    <span class="material-icons-round text-primary text-sm">location_city</span>
                                    المدينة الرئيسية للمتجر
                                </label>
                                <input type="text" name="deliveryHomeCity" 
                                    value="<?php echo htmlspecialchars($settings['deliveryHomeCity'] ?? ''); ?>"
                                    placeholder="مثال: الرباط، الدار البيضاء..."
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                                <p class="text-xs text-gray-500 mt-2">
                                    سيتم احتساب سعر "داخل المدينة" لأي طلب يتم شحنه لهذه المدينة، وسعر "خارج المدينة" لباقي المدن.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                    <label class="block text-sm font-medium text-gray-300 mb-3">تكلفة التوصيل داخل المدينة</label>
                                    <div class="relative">
                                        <input type="number" id="deliveryInsideCity" name="deliveryInsideCity" step="0.01" min="0"
                                            value="<?php echo htmlspecialchars($settings['deliveryInsideCity'] ?? '0'); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all font-bold text-lg <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold"><?php echo htmlspecialchars($settings['currency'] ?? 'MAD'); ?></span>
                                    </div>
                                </div>
                                <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                    <label class="block text-sm font-medium text-gray-300 mb-3">تكلفة التوصيل خارج المدينة</label>
                                    <div class="relative">
                                        <input type="number" id="deliveryOutsideCity" name="deliveryOutsideCity" step="0.01" min="0"
                                            value="<?php echo htmlspecialchars($settings['deliveryOutsideCity'] ?? '0'); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all font-bold text-lg <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold"><?php echo htmlspecialchars($settings['currency'] ?? 'MAD'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-content-rental" class="tab-content hidden space-y-6 max-w-5xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-8 glass-panel">
                        <div class="flex items-center justify-between mb-8 border-b border-white/5 pb-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">home_work</span>
                                إدارة إيجار المحل
                            </h3>
                            <div class="flex items-center gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                                <span class="text-sm text-gray-300">تفعيل نظام الإيجار</span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="rentalEnabled" id="toggle-rental" value="1"
                                        class="toggle-checkbox"
                                        <?php echo (isset($settings['rentalEnabled']) && $settings['rentalEnabled'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?>
                                        onchange="toggleRentalSettings(this)" />
                                    <label for="toggle-rental" class="toggle-label block overflow-hidden h-6 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                </div>
                            </div>
                        </div>

                        <div id="rental-settings-content" class="transition-all duration-300 <?php echo (!isset($settings['rentalEnabled']) || $settings['rentalEnabled'] == '0') ? 'opacity-50 pointer-events-none filter blur-sm' : ''; ?>">
                            
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                                <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                        <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wider">مبلغ الإيجار</label>
                                        <div class="relative">
                                            <input type="number" name="rentalAmount" step="0.01" min="0"
                                                value="<?php echo htmlspecialchars($settings['rentalAmount'] ?? '0'); ?>"
                                                class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all font-bold text-lg <?php echo $readonlyClass; ?>"
                                                <?php echo $disabledAttr; ?>>
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold"><?php echo htmlspecialchars($settings['currency'] ?? 'MAD'); ?></span>
                                        </div>
                                    </div>

                                    <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                        <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wider">نظام الدفع</label>
                                        <select name="rentalType"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                            <option value="monthly" <?php echo (isset($settings['rentalType']) && $settings['rentalType'] == 'monthly') ? 'selected' : ''; ?>>شهري (كل شهر)</option>
                                            <option value="yearly" <?php echo (isset($settings['rentalType']) && $settings['rentalType'] == 'yearly') ? 'selected' : ''; ?>>سنوي (كل سنة)</option>
                                        </select>
                                    </div>

                                    <div class="bg-white/5 border border-white/5 rounded-xl p-5 md:col-span-2">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">تاريخ الاستحقاق القادم</label>
                                            <span class="text-[10px] text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded">يحدث تلقائياً عند الدفع</span>
                                        </div>
                                        <input type="date" name="rentalPaymentDate"
                                            value="<?php echo htmlspecialchars($settings['rentalPaymentDate'] ?? date('Y-m-01')); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                    </div>
                                </div>

                                <div class="lg:col-span-4 bg-gradient-to-br from-primary/10 to-transparent border border-primary/20 rounded-xl p-5 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center gap-2 mb-4 text-primary">
                                            <span class="material-icons-round">notifications_active</span>
                                            <h4 class="font-bold">التنبيه المبكر</h4>
                                        </div>
                                        <label class="block text-sm text-gray-300 mb-2">تذكيري قبل الموعد بـ (أيام):</label>
                                        <input type="number" name="rentalReminderDays" min="1" max="30"
                                            value="<?php echo htmlspecialchars($settings['rentalReminderDays'] ?? '7'); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-center px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 font-bold text-xl <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                    </div>
                                    <div class="mt-4 text-xs text-gray-400 leading-relaxed bg-black/20 p-3 rounded-lg">
                                        سيصلك إشعار داخل النظام، وإشعار Windows إذا كان مفعلاً، قبل الموعد المحدد أعلاه.
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                    <h4 class="font-bold text-white mb-4 flex items-center gap-2">
                                        <span class="material-icons-round text-sm text-gray-400">person</span>
                                        بيانات المالك (اختياري)
                                    </h4>
                                    <div class="space-y-4">
                                        <input type="text" name="rentalLandlordName" placeholder="اسم المالك"
                                            value="<?php echo htmlspecialchars($settings['rentalLandlordName'] ?? ''); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <input type="text" name="rentalLandlordPhone" placeholder="رقم الهاتف"
                                            value="<?php echo htmlspecialchars($settings['rentalLandlordPhone'] ?? ''); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <textarea name="rentalNotes" rows="2" placeholder="ملاحظات (رقم العقد، إلخ)"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all resize-none <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>><?php echo htmlspecialchars($settings['rentalNotes'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-5">
                                        <div class="flex items-center gap-3 mb-4">
                                            <div class="p-2 bg-green-500/20 rounded-lg text-green-500">
                                                <span class="material-icons-round">verified</span>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-white">إجراء سريع</h4>
                                                <p class="text-xs text-green-400">تسجيل دفع إيجار الشهر الحالي</p>
                                            </div>
                                        </div>
                                        <button type="button" id="btn-rental-paid" class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-green-600/20 flex items-center justify-center gap-2 <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                            <span>تأكيد الدفع الآن</span>
                                            <span class="material-icons-round text-sm">check</span>
                                        </button>
                                    </div>

                                    <button type="button" id="btn-rental-payments-log" class="w-full py-3 bg-white/5 hover:bg-white/10 border border-white/10 text-gray-300 hover:text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2 <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                        <span class="material-icons-round text-sm">history</span>
                                        <span>عرض سجل المدفوعات السابق</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-content-finance" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white/5 rounded-xl">
                                <span class="material-icons-round text-yellow-500">currency_exchange</span>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-white">عملة النظام</h3>
                                <p class="text-xs text-gray-400">العملة التي ستظهر في الفواتير والتقارير</p>
                            </div>
                            <div class="w-48">
                                <select name="currency" class="w-full bg-dark border border-white/10 text-white text-right px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all cursor-pointer <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                    <option value="MAD" <?php echo (isset($settings['currency']) && $settings['currency'] == 'MAD') ? 'selected' : ''; ?>>الدرهم المغربي (MAD)</option>
                                    <option value="SAR" <?php echo (isset($settings['currency']) && $settings['currency'] == 'SAR') ? 'selected' : ''; ?>>ريال سعودي (SAR)</option>
                                    <option value="USD" <?php echo (isset($settings['currency']) && $settings['currency'] == 'USD') ? 'selected' : ''; ?>>دولار أمريكي (USD)</option>
                                    <option value="EUR" <?php echo (isset($settings['currency']) && $settings['currency'] == 'EUR') ? 'selected' : ''; ?>>يورو (EUR)</option>
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

                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-8 glass-panel">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">receipt</span>
                                إعدادات الضريبة
                            </h3>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-400">تفعيل الضريبة</span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="taxEnabled" id="toggle-tax" value="1"
                                        class="toggle-checkbox"
                                        <?php echo (isset($settings['taxEnabled']) && $settings['taxEnabled'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?> />
                                    <label for="toggle-tax" class="toggle-label block overflow-hidden h-6 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-white/5 rounded-2xl border border-white/5">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">اسم الضريبة (يظهر في الفاتورة)</label>
                                <input type="text" name="taxLabel" value="<?php echo htmlspecialchars($settings['taxLabel'] ?? 'TVA'); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">نسبة الضريبة (%)</label>
                                <div class="relative">
                                    <input type="number" name="taxRate" value="<?php echo htmlspecialchars($settings['taxRate'] ?? '20'); ?>" step="0.01"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-content-system" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">palette</span>
                            تفضيلات الواجهة والصوت
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-icons-round text-gray-400">dark_mode</span>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">الوضع الليلي</h4>
                                        <p class="text-[10px] text-gray-400">تعتيم الواجهة لراحة العين</p>
                                    </div>
                                </div>
                                <div class="relative inline-block w-10 align-middle select-none">
                                    <input type="checkbox" name="darkMode" id="toggle-dark" value="1"
                                        class="toggle-checkbox"
                                        <?php echo $isAdmin ? 'onchange="this.form.submit()"' : ''; ?>
                                        <?php echo (isset($settings['darkMode']) && $settings['darkMode'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?> />
                                    <label for="toggle-dark" class="toggle-label block overflow-hidden h-5 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-icons-round text-gray-400">volume_up</span>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">أصوات النظام</h4>
                                        <p class="text-[10px] text-gray-400">تشغيل صوت عند إتمام البيع</p>
                                    </div>
                                </div>
                                <div class="relative inline-block w-10 align-middle select-none">
                                    <input type="checkbox" name="soundNotifications" id="toggle-sound" value="1"
                                        class="toggle-checkbox"
                                        <?php echo (isset($settings['soundNotifications']) && $settings['soundNotifications'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?> />
                                    <label for="toggle-sound" class="toggle-label block overflow-hidden h-5 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">inventory</span>
                            حدود الكميات
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-5 rounded-xl bg-yellow-500/5 border border-yellow-500/10 hover:border-yellow-500/30 transition-all">
                                <div class="flex items-center gap-2 mb-3 text-yellow-500">
                                    <span class="material-icons-round">warning</span>
                                    <h4 class="font-bold">تنبيه الكمية المنخفضة</h4>
                                </div>
                                <div class="relative mb-2">
                                    <input type="number" name="low_quantity_alert" value="<?php echo htmlspecialchars($settings['low_quantity_alert'] ?? '30'); ?>" step="1"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-2 rounded-lg focus:outline-none focus:border-yellow-500/50 transition-all font-bold <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs">قطعة</span>
                                </div>
                                <p class="text-[10px] text-gray-400">يظهر المنتج بلون أصفر عندما يصل لهذا العدد. (نقطة إعادة الطلب)</p>
                            </div>

                            <div class="p-5 rounded-xl bg-red-500/5 border border-red-500/10 hover:border-red-500/30 transition-all">
                                <div class="flex items-center gap-2 mb-3 text-red-500">
                                    <span class="material-icons-round">report</span>
                                    <h4 class="font-bold">تنبيه الخطر (الحرج)</h4>
                                </div>
                                <div class="relative mb-2">
                                    <input type="number" name="critical_quantity_alert" value="<?php echo htmlspecialchars($settings['critical_quantity_alert'] ?? '10'); ?>" step="1"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-2 rounded-lg focus:outline-none focus:border-red-500/50 transition-all font-bold <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs">قطعة</span>
                                </div>
                                <p class="text-[10px] text-gray-400">يظهر المنتج بلون أحمر. يجب توفير المخزون فوراً.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel" id="stock-alerts-container">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <span class="material-icons-round text-primary">update</span>
                                فحص المخزون التلقائي
                            </h3>
                            <div class="relative inline-block w-10 align-middle select-none">
                                <input type="checkbox" name="stockAlertsEnabled" id="toggle-stock-alerts" value="1"
                                    class="toggle-checkbox"
                                    <?php echo (isset($settings['stockAlertsEnabled']) && $settings['stockAlertsEnabled'] == '1') ? 'checked' : ''; ?>
                                    <?php echo $disabledAttr; ?>
                                    onchange="handleStockAlertToggle(this)" />
                                <label for="toggle-stock-alerts" class="toggle-label block overflow-hidden h-5 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                            </div>
                        </div>

                        <div id="stock-alerts-settings" class="transition-all duration-300 <?php echo (!isset($settings['stockAlertsEnabled']) || $settings['stockAlertsEnabled'] == '0') ? 'opacity-50 pointer-events-none' : ''; ?>">
                            <div class="flex items-end gap-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-400 mb-2">تكرار الفحص (بالدقائق)</label>
                                    <input type="number" name="stockAlertInterval" 
                                        value="<?php echo htmlspecialchars($settings['stockAlertInterval'] ?? '20'); ?>"
                                        min="1" max="1440" step="1"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                </div>
                                <button type="button" onclick="openStockGuideModal()" class="px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-primary text-sm font-bold flex items-center gap-2 transition-all">
                                    <span class="material-icons-round text-sm">help_outline</span>
                                    كيف أختار؟
                                </button>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-white/5">
                             <button type="button" id="enable-windows-notifications" onclick="enableStockNotifications()" 
                                class="w-full bg-primary/10 hover:bg-primary/20 text-primary border border-primary/20 hover:border-primary/50 px-4 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                                <span class="material-icons-round text-sm">notifications_active</span>
                                <span>تفعيل إشعارات سطح المكتب (Windows)</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</main>

<style>
    /* Custom Styling for the redesign */
    .tab-btn.active-tab {
        background-color: rgba(var(--primary-rgb), 0.1);
        color: var(--primary-color);
        border-right: 3px solid var(--primary-color);
    }
    .tab-btn.active-tab .material-icons-round {
        color: var(--primary-color);
    }
    .glass-panel {
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    }
    /* Simple fade in animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }
    .toggle-checkbox:checked {
        right: 0;
        border-color: #10B981;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #10B981;
    }
    .toggle-label {
        width: 100%;
        background-color: #374151;
        transition: background-color 0.2s ease-in;
    }
    .toggle-checkbox {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 0;
        width: 0;
    }
    .toggle-label:before {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 16px;
        height: 16px; /* slightly smaller than container height 20px/24px */
        border-radius: 50%;
        background-color: white;
        transition: transform 0.2s ease-in;
    }
    /* Adjust for specific heights */
    .h-6.toggle-label:before { height: 20px; width: 20px; }
    .h-5.toggle-label:before { height: 16px; width: 16px; }
    
    .toggle-checkbox:checked + .toggle-label:before {
        transform: translateX(100%);
        /* Need to adjust calculate based on width, usually straightforward in CSS relative */
        transform: translateX(24px); /* Rough calc */
    }
    /* Fix toggle CSS alignment */
    .toggle-label { position: relative; width: 48px; }
    .toggle-checkbox:checked + .toggle-label:before { transform: translateX(24px); }
</style>

<script>
    // Tab Switching Logic
    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active-tab', 'bg-primary/10', 'text-primary', 'border-r-4', 'border-primary');
            btn.classList.add('text-gray-400');
        });

        const targetContent = document.getElementById('tab-content-' + tabName);
        if(targetContent) targetContent.classList.remove('hidden');
        
        const activeBtn = document.getElementById('tab-btn-' + tabName);
        if(activeBtn) {
            activeBtn.classList.add('active-tab');
            activeBtn.classList.remove('text-gray-400');
        }
    }

    // تعديل هنا: الصفحة تفتح دائماً على 'store' عند التحميل
    document.addEventListener('DOMContentLoaded', () => {
        switchTab('store'); 
    });

    // Initialize Tab on Load
    document.addEventListener('DOMContentLoaded', () => {
        const lastTab = localStorage.getItem('activeSettingsTab') || 'store';
        switchTab(lastTab);
    });
</script>

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
                el.style.color = '#10b981';
                setTimeout(() => el.style.color = '', 1000);
            });
        }
    }
    
    function toggleRentalSettings(checkbox) {
        const content = document.getElementById('rental-settings-content');
        if (!checkbox.checked) {
            if (confirm('⚠️ تنبيه!\n\nهل أنت متأكد من تعطيل ميزة تذكير الإيجار؟')) {
                content.classList.add('opacity-50', 'pointer-events-none', 'filter', 'blur-sm');
            } else {
                checkbox.checked = true;
            }
        } else {
            content.classList.remove('opacity-50', 'pointer-events-none', 'filter', 'blur-sm');
        }
    }

    function handleStockAlertToggle(checkbox) {
        const container = document.getElementById('stock-alerts-settings');
        if (!checkbox.checked) {
            if (confirm(`⚠️ تنبيه هام!\n\nتعطيل تنبيهات المخزون قد يجعلك تفقد السيطرة على منتجاتك.\nهل أنت متأكد؟`)) {
                container.classList.add('opacity-50', 'pointer-events-none');
            } else {
                checkbox.checked = true;
            }
        } else {
            container.classList.remove('opacity-50', 'pointer-events-none');
        }
    }

    // Windows Notification Logic
    document.addEventListener('DOMContentLoaded', function() {
        const notifButton = document.getElementById('enable-windows-notifications');
        if (notifButton && 'Notification' in window) {
            if (Notification.permission === 'granted') {
                notifButton.innerHTML = `<span class="material-icons-round text-sm">check_circle</span><span>إشعارات Windows مفعلة</span>`;
                notifButton.className = "w-full bg-green-500/10 text-green-500 border border-green-500/20 px-4 py-3 rounded-xl font-bold flex items-center justify-center gap-2 cursor-default";
                notifButton.disabled = true;
            } else if (Notification.permission === 'denied') {
                notifButton.innerHTML = `<span class="material-icons-round text-sm">block</span><span>الإشعارات محظورة من المتصفح</span>`;
                notifButton.className = "w-full bg-red-500/10 text-red-500 border border-red-500/20 px-4 py-3 rounded-xl font-bold flex items-center justify-center gap-2 cursor-not-allowed";
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Rental Paid Button Logic
        const btn = document.getElementById('btn-rental-paid');
        if (btn) {
            btn.addEventListener('click', async function() {
                if(!confirm('هل تريد تسجيل دفع إيجار هذا الشهر؟ سيتم تحديث تاريخ الاستحقاق تلقائياً.')) return;
                try {
                    btn.disabled = true;
                    btn.classList.add('opacity-70');
                    const res = await fetch('api.php?action=markRentalPaidThisMonth', { method: 'POST' });
                    const data = await res.json();
                    if (data.success) {
                        const dateInput = document.querySelector('input[name="rentalPaymentDate"]');
                        if (dateInput && data.next_payment_date) dateInput.value = data.next_payment_date;
                        localStorage.removeItem('rental_notify_day');
                        alert('✅ تم تسجيل الدفع بنجاح');
                    } else {
                        alert('❌ ' + (data.message || 'فشل التسجيل'));
                    }
                } catch (e) {
                    alert('خطأ في الاتصال');
                } finally {
                    btn.disabled = false;
                    btn.classList.remove('opacity-70');
                }
            });
        }
        
        // Bind Log Modal Button
        const logBtn = document.getElementById('btn-rental-payments-log');
        if(logBtn) logBtn.addEventListener('click', () => { openRentalPaymentsModal(); loadRentalPayments(1); });
    });
</script>

<div id="rental-payments-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="rentalPaymentsBackdrop" onclick="closeRentalPaymentsModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-[#1e1e2e] border border-white/10 rounded-2xl w-full max-w-3xl transform scale-95 opacity-0 transition-all duration-300 relative shadow-2xl overflow-hidden flex flex-col max-h-[85vh]" id="rentalPaymentsContent">
            <div class="px-6 py-4 bg-white/5 border-b border-white/5 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-primary">receipt_long</span>
                    سجل مدفوعات الإيجار
                </h3>
                <button onclick="closeRentalPaymentsModal()" class="text-gray-400 hover:text-white p-1 hover:bg-white/10 rounded-lg transition-colors"><span class="material-icons-round">close</span></button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <div id="rentalPaymentsTable" class="space-y-3"></div>
                <div id="rentalPaymentsPagination" class="mt-4 flex justify-center gap-2 pt-4 border-t border-white/5"></div>
            </div>
        </div>
    </div>
</div>

<div id="stockGuideModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="stockGuideBackdrop" onclick="closeStockGuideModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-[#1e1e2e] border border-white/10 rounded-2xl w-full max-w-2xl transform scale-95 opacity-0 transition-all duration-300 relative shadow-2xl overflow-hidden flex flex-col max-h-[85vh]" id="stockGuideContent">
            <div class="px-6 py-4 bg-white/5 border-b border-white/5 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-primary">tips_and_updates</span>
                    دليل اختيار مدة التنبيهات
                </h3>
                <button onclick="closeStockGuideModal()" class="text-gray-400 hover:text-white p-1 hover:bg-white/10 rounded-lg transition-colors"><span class="material-icons-round">close</span></button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="text-sm font-bold text-gray-400 mb-3 border-b border-white/5 pb-2">أمثلة مقترحة</h5>
                        <div class="space-y-3">
                            <div class="bg-white/5 p-3 rounded-xl border border-white/5">
                                <div class="flex justify-between text-white font-bold text-sm mb-1"><span>للمتاجر الصغيرة</span><span class="text-primary">30 دقيقة</span></div>
                                <p class="text-xs text-gray-500">حركة بيع متوسطة، مخزون محدود.</p>
                            </div>
                            <div class="bg-white/5 p-3 rounded-xl border border-white/5">
                                <div class="flex justify-between text-white font-bold text-sm mb-1"><span>سوبر ماركت</span><span class="text-primary">60 دقيقة</span></div>
                                <p class="text-xs text-gray-500">موازنة بين الأداء ودقة المخزون.</p>
                            </div>
                        </div>
                    </div>
                    <div>
                         <h5 class="text-sm font-bold text-gray-400 mb-3 border-b border-white/5 pb-2">نصائح</h5>
                         <ul class="space-y-2 text-xs text-gray-400 list-disc mr-4">
                             <li>ابدأ بالقيمة الافتراضية (20 دقيقة).</li>
                             <li>قلل الوقت في أوقات الذروة والأعياد.</li>
                             <li>لا تستخدم أقل من 5 دقائق لتجنب بطء النظام.</li>
                         </ul>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-white/5 flex justify-end shrink-0">
                <button onclick="closeStockGuideModal()" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold">فهمت</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal Functions
    function openRentalPaymentsModal() {
        const modal = document.getElementById('rental-payments-modal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('rentalPaymentsBackdrop').classList.remove('opacity-0');
            const content = document.getElementById('rentalPaymentsContent');
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        }, 10);
    }
    function closeRentalPaymentsModal() {
        document.getElementById('rentalPaymentsBackdrop').classList.add('opacity-0');
        const content = document.getElementById('rentalPaymentsContent');
        content.classList.remove('opacity-100', 'scale-100');
        content.classList.add('opacity-0', 'scale-95');
        setTimeout(() => document.getElementById('rental-payments-modal').classList.add('hidden'), 300);
    }
    
    // Stock Guide Modal
    function openStockGuideModal() {
        const modal = document.getElementById('stockGuideModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('stockGuideBackdrop').classList.remove('opacity-0');
            const content = document.getElementById('stockGuideContent');
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        }, 10);
    }
    function closeStockGuideModal() {
        document.getElementById('stockGuideBackdrop').classList.add('opacity-0');
        const content = document.getElementById('stockGuideContent');
        content.classList.remove('opacity-100', 'scale-100');
        content.classList.add('opacity-0', 'scale-95');
        setTimeout(() => document.getElementById('stockGuideModal').classList.add('hidden'), 300);
    }

    // Load Rental Data Logic (Simplified for brevity, same as original logic)
    function formatArDate(d) {
        return new Date(d).toLocaleDateString('ar-MA', { year: 'numeric', month: 'long', day: 'numeric' });
    }
    async function loadRentalPayments(page = 1) {
        const table = document.getElementById('rentalPaymentsTable');
        const pag = document.getElementById('rentalPaymentsPagination');
        table.innerHTML = '<div class="text-center py-8 text-gray-500">جاري التحميل...</div>';
        try {
            const res = await fetch('api.php?action=getRentalPayments&page='+page+'&limit=20');
            const data = await res.json();
            if(!data.success || !data.data.length) {
                table.innerHTML = '<div class="text-center py-8 text-gray-500">لا توجد مدفوعات مسجلة</div>';
                pag.innerHTML = '';
                return;
            }
            let html = '';
            data.data.forEach(r => {
                html += `
                <div class="bg-white/5 border border-white/10 rounded-xl p-4 flex justify-between items-center">
                    <div>
                        <div class="font-bold text-white text-sm">عن شهر: ${r.paid_month}</div>
                        <div class="text-xs text-gray-400">${formatArDate(r.payment_date)}</div>
                    </div>
                    <div class="text-left">
                         <div class="font-bold text-primary">${parseFloat(r.amount).toFixed(2)} ${r.currency}</div>
                         <div class="text-[10px] text-gray-500">${r.rental_type == 'yearly' ? 'سنوي' : 'شهري'}</div>
                    </div>
                </div>`;
            });
            table.innerHTML = html;
            // Add simple pagination if needed based on data.pagination
        } catch(e) {
            table.innerHTML = '<div class="text-center text-red-500">فشل في جلب البيانات</div>';
        }
    }
</script>
<?php endif; ?>

<?php require_once 'src/footer.php'; ?>