<?php
require_once 'session.php';
require_once 'db.php';
require_once 'session.php';

// التحقق من أن المستخدم admin فقط يمكنه التعديل
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

// معالجة إعادة التهيئة - فقط للمدراء
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin && isset($_POST['reset_settings'])) {
    // حذف جميع الإعدادات الحالية
    $conn->query("DELETE FROM settings");

    // القيم الافتراضية مع تعطيل الوحدة المفاتيح الوهمية
    $default_settings = [
        'shopName' => '',
        'shopPhone' => '',
        'shopCity' => '',
        'shopAddress' => '',
        'shopLogoUrl' => '',
        'invoiceShowLogo' => '0',
        'darkMode' => '0',
        'soundNotifications' => '0',
        'currency' => 'MAD',
        'taxEnabled' => '0',
        'taxRate' => '20',
        'taxLabel' => 'TVA',
        'low_quantity_alert' => '30',
        'critical_quantity_alert' => '10',
        'deliveryHomeCity' => '',
        'deliveryInsideCity' => '20',
        'deliveryOutsideCity' => '40',
        'stockAlertsEnabled' => '0',
        'stockAlertInterval' => '20',
        'rentalEnabled' => '0',
        'rentalAmount' => '0',
        'rentalPaymentDate' => date('Y-m-01'),
        'rentalType' => 'monthly',
        'rentalReminderDays' => '7',
        'rentalLandlordName' => '',
        'rentalLandlordPhone' => '',
        'rentalNotes' => '',
        'printMode' => 'normal',
        'work_days_enabled' => '1',
        'work_days' => 'monday,tuesday,wednesday,thursday,friday,saturday',
    ];

    $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?)");
    foreach ($default_settings as $name => $value) {
        $stmt->bind_param("ss", $name, $value);
        $stmt->execute();
    }
    $stmt->close();

    // إضافة إشعار
    $msg = "تم إعادة تهيئة النظام بنجاح. جميع الإعدادات تم إرجاعها إلى الحالة الأولية.";
    $notifStmt = $conn->prepare("INSERT INTO notifications (message, type) VALUES (?, ?)");
    $type = "system_reset";
    $notifStmt->bind_param("ss", $msg, $type);
    $notifStmt->execute();
    $notifStmt->close();

    header("Location: settings.php?tab=reset&success=" . urlencode($msg));
    exit();
}

// معالجة حفظ البيانات - فقط للمدراء
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $currentLogo = '';
    $resLogo = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopLogoUrl'");
    if ($resLogo && $resLogo->num_rows > 0) {
        $currentLogo = $resLogo->fetch_assoc()['setting_value'];
    }
    $settings_to_save = [
        'shopName' => $_POST['shopName'] ?? '',
        'shopPhone' => $_POST['shopPhone'] ?? '',
        'shopCity' => $_POST['shopCity'] ?? '',
        'shopAddress' => $_POST['shopAddress'] ?? '',
        'shopLogoUrl' => $currentLogo,
        'invoiceShowLogo' => isset($_POST['invoiceShowLogo']) ? '1' : '0',
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
        'rentalEnabled' => isset($_POST['rentalEnabled']) ? '1' : '0',
        'rentalAmount' => $_POST['rentalAmount'] ?? '0',
        'rentalPaymentDate' => $_POST['rentalPaymentDate'] ?? date('Y-m-01'),
        'rentalType' => $_POST['rentalType'] ?? 'monthly',
        'rentalReminderDays' => $_POST['rentalReminderDays'] ?? '7',
        'rentalLandlordName' => $_POST['rentalLandlordName'] ?? '',
        'rentalLandlordPhone' => $_POST['rentalLandlordPhone'] ?? '',
        'rentalNotes' => $_POST['rentalNotes'] ?? '',
        'printMode' => $_POST['printMode'] ?? 'normal',
        'work_days_enabled' => isset($_POST['work_days_enabled']) ? '1' : '0',
        'work_days' => isset($_POST['work_days']) ? implode(',', $_POST['work_days']) : '',
    ];

    if (isset($_FILES['shopLogoFile']) && $_FILES['shopLogoFile']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['png', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($_FILES['shopLogoFile']['name'], PATHINFO_EXTENSION));
        if ($ext === 'jpeg') $ext = 'jpg';
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $filename = 'shop_logo.' . $ext;
            $destFs = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            if (@move_uploaded_file($_FILES['shopLogoFile']['tmp_name'], $destFs)) {
                $settings_to_save['shopLogoUrl'] = 'src/uploads/' . $filename;
            }
        }
    }

    $existing = [];
    $resAll = $conn->query("SELECT setting_name, setting_value FROM settings");
    if ($resAll) {
        while ($row = $resAll->fetch_assoc()) {
            $existing[$row['setting_name']] = $row['setting_value'];
        }
    }

    $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");

    foreach ($settings_to_save as $name => $value) {
        $stmt->bind_param("sss", $name, $value, $value);
        $stmt->execute();
    }

    $stmt->close();
    $labels = [
        'shopName' => 'اسم المتجر',
        'shopPhone' => 'رقم الهاتف',
        'shopCity' => 'مدينة المتجر',
        'shopAddress' => 'العنوان التفصيلي',
        'shopLogoUrl' => 'شعار المتجر',
        'invoiceShowLogo' => 'إظهار الشعار في الفواتير',
        'darkMode' => 'الوضع الداكن',
        'soundNotifications' => 'تنبيهات الصوت',
        'currency' => 'العملة',
        'taxEnabled' => 'تفعيل الضريبة',
        'taxRate' => 'نسبة الضريبة',
        'taxLabel' => 'وسم الضريبة',
        'low_quantity_alert' => 'حد تنبيه الكمية المنخفضة',
        'critical_quantity_alert' => 'حد الكمية الحرجة',
        'deliveryHomeCity' => 'المدينة الرئيسية للمتجر',
        'deliveryInsideCity' => 'سعر التوصيل داخل المدينة',
        'deliveryOutsideCity' => 'سعر التوصيل خارج المدينة',
        'stockAlertsEnabled' => 'تنبيهات المخزون التلقائية',
        'stockAlertInterval' => 'تكرار فحص المخزون بالدقائق',
        'rentalEnabled' => 'تفعيل تذكير الإيجار',
        'rentalAmount' => 'مبلغ الإيجار',
        'rentalPaymentDate' => 'تاريخ دفع الإيجار',
        'rentalType' => 'نوع الإيجار',
        'rentalReminderDays' => 'أيام التذكير قبل الموعد',
        'rentalLandlordName' => 'اسم المالك',
        'rentalLandlordPhone' => 'هاتف المالك',
        'rentalNotes' => 'ملاحظات الإيجار',
        'work_days_enabled' => 'تفعيل أيام العمل',
        'work_days' => 'أيام العمل',
    ];
    $changedLabels = [];
    foreach ($settings_to_save as $name => $value) {
        $oldVal = isset($existing[$name]) ? (string)$existing[$name] : null;
        if ($oldVal === null || (string)$value !== $oldVal) {
            $changedLabels[] = isset($labels[$name]) ? $labels[$name] : $name;
        }
    }
    if (!empty($changedLabels)) {
        $msg = "تم تحديث الإعدادات (" . count($changedLabels) . " عنصر): " . implode('، ', $changedLabels);
        if (in_array('deliveryInsideCity', $changedLabels) || in_array('deliveryOutsideCity', $changedLabels)) {
            $msg .= " | داخل المدينة " . ($settings_to_save['deliveryInsideCity'] ?? '') . "، خارج المدينة " . ($settings_to_save['deliveryOutsideCity'] ?? '');
        }
        $notifStmt = $conn->prepare("INSERT INTO notifications (message, type) VALUES (?, ?)");
        $type = "settings_update";
        $notifStmt->bind_param("ss", $msg, $type);
        $notifStmt->execute();
        $notifStmt->close();
    }
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

    <form method="POST" action="settings.php" enctype="multipart/form-data" class="flex-1 flex flex-col overflow-hidden" <?php echo $isAdmin ? '' : 'onsubmit="return false;"'; ?>>
        
        <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
            <div class="flex items-center gap-4">
                <div class="p-2.5 bg-primary/10 rounded-xl">
                    <span class="material-icons-round text-primary">settings</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">إعدادات النظام</h2>
                    <p class="text-xs text-gray-400">تحكم كامل وسهل في إعدادات المتجر والعلامة التجارية</p>
                    <div id="unsaved-changes-alert" class="hidden mt-2 text-xs bg-orange-500/10 text-orange-500 border border-orange-500/20 px-3 py-1 rounded-full font-bold flex items-center gap-1">
                        <span class="material-icons-round text-xs">warning</span>
                        <span>تنبيه: لديك تغييرات غير محفوظة - احفظ التغييرات لتطبيقها</span>
                    </div>
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
                    <button type="submit" id="save-settings-btn" disabled class="bg-gray-500/50 text-gray-400 px-8 py-2.5 rounded-xl font-bold shadow-lg transition-all flex items-center gap-2 cursor-not-allowed">
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
            
            <?php
                $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'store';
                require_once 'src/settings_sidebar.php';
            ?>

            <div class="flex-1 overflow-y-auto p-8 relative scroll-smooth" id="settings-content-area">
                
                <div id="tab-content-store" class="tab-content space-y-6 max-w-5xl mx-auto animate-fade-in">
                    
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl overflow-hidden glass-panel">
                        <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                            <h3 class="text-lg font-bold text-white flex items-center gap-3">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <span class="material-icons-round">storefront</span>
                                </div>
                                هوية المتجر والعلامة التجارية
                            </h3>
                        </div>

                        <div class="p-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            
                            <div class="lg:col-span-4 flex flex-col items-center justify-center p-6 border border-dashed border-white/10 rounded-2xl bg-white/[0.02] hover:bg-white/[0.04] transition-colors group relative">
                                <div class="w-32 h-32 rounded-full bg-gradient-to-tr from-gray-800 to-gray-700 flex items-center justify-center mb-4 shadow-xl shadow-black/20 overflow-hidden relative">
                                    <?php if (!empty($settings['shopLogoUrl'] ?? '')): ?>
                                        <img src="<?php echo htmlspecialchars($settings['shopLogoUrl']); ?>" alt="Logo" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="material-icons-round text-5xl text-gray-500 group-hover:scale-110 transition-transform duration-300">add_a_photo</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-bold text-white mb-1">شعار المتجر</p>
                                    <p class="text-[10px] text-gray-400 mb-3">تنسيق PNG أو JPG (مربع)</p>
                                    <button type="button" onclick="document.getElementById('shopLogoFile').click();" class="px-4 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition-all <?php echo $disabledAttr; ?>">
                                        تغيير الصورة
                                    </button>
                                </div>
                                <input type="file" name="shopLogoFile" id="shopLogoFile" accept="image/png,image/jpeg" class="absolute inset-0 opacity-0 cursor-pointer <?php echo $isAdmin ? '' : 'pointer-events-none'; ?>" title="">
                            </div>

                            <div class="lg:col-span-8 space-y-6">
                                <?php $hasLogo = !empty($settings['shopLogoUrl'] ?? ''); ?>
                                <label class="inline-flex items-center gap-2 invoice-logo-checkbox <?php echo $hasLogo ? '' : 'hidden'; ?>">
                                    <input type="checkbox" name="invoiceShowLogo" value="1" <?php echo (($settings['invoiceShowLogo'] ?? '0') === '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?>>
                                    <span class="text-xs font-bold text-gray-300">إضافة الشعار إلى الفواتير</span>
                                </label>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2 mr-1">اسم المتجر (يظهر في الفواتير)</label>
                                    <div class="relative group">
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-primary transition-colors">
                                            <span class="material-icons-round text-lg">edit</span>
                                        </div>
                                        <input type="text" name="shopName" value="<?php echo htmlspecialchars($settings['shopName'] ?? ''); ?>"
                                            placeholder="مثال: متجر الأناقة للأحذية"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right pr-12 pl-4 py-3.5 rounded-xl focus:outline-none focus:border-primary/50 focus:ring-4 focus:ring-primary/10 transition-all font-bold text-lg placeholder-gray-600 <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                    </div>
                                </div>

                               
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl overflow-hidden glass-panel">
                        <div class="px-8 py-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                            <h3 class="text-lg font-bold text-white flex items-center gap-3">
                                <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
                                    <span class="material-icons-round">contact_phone</span>
                                </div>
                                معلومات التواصل والموقع
                            </h3>
                        </div>

                        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold text-gray-400 mb-2 mr-1">رقم الهاتف الرسمي</label>
                                <div class="relative group">
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-blue-500 transition-colors">
                                        <span class="material-icons-round text-lg">phone</span>
                                    </div>
                                    <input type="text" name="shopPhone" value="<?php echo htmlspecialchars($settings['shopPhone'] ?? ''); ?>"
                                        placeholder="05 XX XX XX XX"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right pr-12 pl-4 py-3 rounded-xl focus:outline-none focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10 transition-all font-mono text-left <?php echo $readonlyClass; ?> dir-ltr"
                                        <?php echo $disabledAttr; ?>>
                                </div>
                            </div>

                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold text-gray-400 mb-2 mr-1">المدينة</label>
                                <div class="relative group">
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-blue-500 transition-colors">
                                        <span class="material-icons-round text-lg">location_city</span>
                                    </div>
                                    <input type="text" name="shopCity" value="<?php echo htmlspecialchars($settings['shopCity'] ?? ''); ?>"
                                        placeholder="الرباط، الدار البيضاء..."
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right pr-12 pl-4 py-3 rounded-xl focus:outline-none focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                </div>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-400 mb-2 mr-1">العنوان التفصيلي</label>
                                <div class="relative group">
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-blue-500 transition-colors">
                                        <span class="material-icons-round text-lg">place</span>
                                    </div>
                                    <input type="text" name="shopAddress" value="<?php echo htmlspecialchars($settings['shopAddress'] ?? ''); ?>"
                                        placeholder="رقم 12، شارع محمد الخامس، حي الرياض..."
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right pr-12 pl-4 py-3 rounded-xl focus:outline-none focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                </div>
                            </div>

                            <div class="col-span-2 mt-2">
                                <div class="bg-gradient-to-r from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-white/5 flex items-center gap-3 opacity-60">
                                    <span class="material-icons-round text-yellow-500">tips_and_updates</span>
                                    <p class="text-[10px] text-gray-400">
                                        تأكد من صحة هذه البيانات، فهي ستظهر بشكل تلقائي في تذييل الفواتير (Footer) وعلى واجهة الطباعة الحرارية.
                                    </p>
                                </div>
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
                                            style="color-scheme: dark;"
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
                             <div class="p-3 bg-white/5 rounded-xl"><span class="material-icons-round text-yellow-500">currency_exchange</span></div>
                            <div class="flex-1"><h3 class="text-lg font-bold text-white">عملة النظام</h3></div>
                            <div class="w-48">
                                <select name="currency" class="w-full bg-dark border border-white/10 text-white text-right px-4 py-2.5 rounded-xl" <?php echo $disabledAttr; ?>>
                                    <option value="MAD" <?php echo (isset($settings['currency']) && $settings['currency'] == 'MAD') ? 'selected' : ''; ?>>الدرهم المغربي (MAD)</option>
                                    <option value="USD" <?php echo (isset($settings['currency']) && $settings['currency'] == 'USD') ? 'selected' : ''; ?>>دولار أمريكي (USD)</option>
                                    </select>
                            </div>
                        </div>
                     </div>
                     <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-8 glass-panel">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3"><span class="material-icons-round text-primary">receipt</span>إعدادات الضريبة</h3>
                             <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-400">تفعيل الضريبة</span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="taxEnabled" id="toggle-tax" value="1" class="toggle-checkbox" <?php echo (isset($settings['taxEnabled']) && $settings['taxEnabled'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> />
                                    <label for="toggle-tax" class="toggle-label block overflow-hidden h-6 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-white/5 rounded-2xl border border-white/5">
                            <div><label class="block text-sm font-medium text-gray-400 mb-2">اسم الضريبة</label><input type="text" name="taxLabel" value="<?php echo htmlspecialchars($settings['taxLabel'] ?? 'TVA'); ?>" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl" <?php echo $disabledAttr; ?>></div>
                            <div><label class="block text-sm font-medium text-gray-400 mb-2">نسبة الضريبة (%)</label><input type="number" name="taxRate" value="<?php echo htmlspecialchars($settings['taxRate'] ?? '20'); ?>" step="0.01" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl" <?php echo $disabledAttr; ?>></div>
                        </div>
                     </div>
                </div>

                <div id="tab-content-print" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">print</span>
                            إعدادات الطباعة
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label for="printMode" class="block text-sm font-medium text-gray-300 mb-2">نوع الطباعة الافتراضي</label>
                                <select id="printMode" name="printMode" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                    <option value="normal" <?php echo ($settings['printMode'] ?? 'normal') == 'normal' ? 'selected' : ''; ?>>طباعة عادية (A4)</option>
                                    <option value="thermal" <?php echo ($settings['printMode'] ?? 'normal') == 'thermal' ? 'selected' : ''; ?>>طباعة حرارية (Thermal)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-2">اختر نوع الطباعة الذي سيظهر تلقائياً بعد إتمام عملية البيع. الطباعة الحرارية مخصصة للطابعات الحرارية الصغيرة.</p>
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
                                        <h4 class="font-bold text-white text-sm">الوضع الليلي (ننصح به)</h4>
                                        <p class="text-[10px] text-gray-400">تعتيم الواجهة لراحة العين</p>
                                    </div>
                                </div>
                                <div class="relative inline-block w-10 align-middle select-none">
                                    <input type="checkbox" name="darkMode" id="toggle-dark" value="1" class="toggle-checkbox" <?php echo $isAdmin ? 'onchange="this.form.submit()"' : ''; ?> <?php echo (isset($settings['darkMode']) && $settings['darkMode'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> />
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
                                    <input type="checkbox" name="soundNotifications" id="toggle-sound" value="1" class="toggle-checkbox" <?php echo (isset($settings['soundNotifications']) && $settings['soundNotifications'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> />
                                    <label for="toggle-sound" class="toggle-label block overflow-hidden h-5 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">inventory</span>
                            تنبيهات حدود المخزون
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="low_quantity_alert" class="block text-sm font-medium text-gray-300 mb-2">حد المخزون المنخفض</label>
                                <input type="number" id="low_quantity_alert" name="low_quantity_alert" value="<?php echo htmlspecialchars($settings['low_quantity_alert'] ?? '30'); ?>" class="w-full bg-dark/50 border border-white/10 text-white text-center px-4 py-3 rounded-xl font-bold text-lg focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                <p class="text-xs text-gray-500 mt-2">عندما تصل كمية المنتج لهذا العدد، سيتم تمييزه باللون <span class="text-orange-400 font-bold">البرتقالي</span> في صفحة إدارة المخزون ونقطة البيع</p>
                            </div>
                            <div>
                                <label for="critical_quantity_alert" class="block text-sm font-medium text-gray-300 mb-2">حد المخزون الحرج</label>
                                <input type="number" id="critical_quantity_alert" name="critical_quantity_alert" value="<?php echo htmlspecialchars($settings['critical_quantity_alert'] ?? '10'); ?>" class="w-full bg-dark/50 border border-white/10 text-white text-center px-4 py-3 rounded-xl font-bold text-lg focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                <p class="text-xs text-gray-500 mt-2">عندما تصل كمية المنتج لهذا العدد، سيتم تمييزه باللون <span class="text-red-500 font-bold">الأحمر</span> في صفحة إدارة المخزون ونقطة البيع</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel" id="stock-alerts-container">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><span class="material-icons-round text-primary">update</span>فحص المخزون التلقائي</h3>
                             <div class="relative inline-block w-10 align-middle select-none">
                                <input type="checkbox" name="stockAlertsEnabled" id="toggle-stock-alerts" value="1" class="toggle-checkbox" <?php echo (isset($settings['stockAlertsEnabled']) && $settings['stockAlertsEnabled'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> onchange="handleStockAlertToggle(this)" />
                                <label for="toggle-stock-alerts" class="toggle-label block overflow-hidden h-5 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                            </div>
                        </div>
                         <div id="stock-alerts-settings" class="<?php echo (!isset($settings['stockAlertsEnabled']) || $settings['stockAlertsEnabled'] == '0') ? 'opacity-50 pointer-events-none' : ''; ?>">
                            <div class="flex items-end gap-4">
                                <div class="flex-1"><label class="block text-sm font-medium text-gray-400 mb-2">تكرار الفحص (بالدقائق)</label><input type="number" name="stockAlertInterval" value="<?php echo htmlspecialchars($settings['stockAlertInterval'] ?? '20'); ?>" min="1" max="1440" step="1" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl" <?php echo $disabledAttr; ?>></div>
                                 <button type="button" onclick="openStockGuideModal()" class="px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-primary text-sm font-bold flex items-center gap-2 transition-all"><span class="material-icons-round text-sm">help_outline</span>كيف أختار؟</button>
                            </div>
                        </div>
                         <div class="mt-6 pt-6 border-t border-white/5">
                             <button type="button" id="enable-windows-notifications" onclick="enableStockNotifications()" class="w-full bg-primary/10 hover:bg-primary/20 text-primary border border-primary/20 hover:border-primary/50 px-4 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2"><span class="material-icons-round text-sm">notifications_active</span><span>تفعيل إشعارات سطح المكتب (Windows)</span></button>
                        </div>
                     </div>

                </div>

                <div id="tab-content-workdays" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <!-- Work Days Settings -->
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-8 glass-panel">
                        <div class="flex items-center justify-between mb-8 border-b border-white/5 pb-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">calendar_month</span>
                                أيام العمل الأسبوعية
                            </h3>
                            <div class="flex items-center gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                                <span class="text-sm text-gray-300">تفعيل أيام العمل</span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="work_days_enabled" id="toggle-work-days" value="1"
                                        class="toggle-checkbox"
                                        <?php echo (isset($settings['work_days_enabled']) && $settings['work_days_enabled'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?>
                                        onchange="toggleWorkDaysSettings(this)" />
                                    <label for="toggle-work-days" class="toggle-label block overflow-hidden h-6 rounded-full <?php echo $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed'; ?>"></label>
                                </div>
                            </div>
                        </div>

                        <div id="work-days-settings-content" class="transition-all duration-300 <?php echo (!isset($settings['work_days_enabled']) || $settings['work_days_enabled'] == '0') ? 'opacity-50 pointer-events-none filter blur-sm' : ''; ?>">
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                                <?php
                                $days = ['monday' => 'الاثنين', 'tuesday' => 'الثلاثاء', 'wednesday' => 'الأربعاء', 'thursday' => 'الخميس', 'friday' => 'الجمعة', 'saturday' => 'السبت', 'sunday' => 'الأحد'];
                                $work_days = explode(',', $settings['work_days'] ?? 'monday,tuesday,wednesday,thursday,friday,saturday');
                                foreach ($days as $en => $ar) {
                                    $checked = in_array($en, $work_days) ? 'checked' : '';
                                    echo "
                                    <label class='flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl p-3 cursor-pointer hover:bg-white/10 transition-colors'>
                                        <input type='checkbox' name='work_days[]' value='$en' $checked class='form-checkbox h-5 w-5 text-primary bg-dark border-white/20 rounded focus:ring-primary/50' $disabledAttr>
                                        <span class='text-white font-bold text-sm'>$ar</span>
                                    </label>
                                    ";
                                }
                                ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-4">حدد الأيام التي يعمل فيها المتجر. سيتم استخدام هذه الإعدادات في التقارير المستقبلية.</p>

                            <div id="work-days-warning" class="mt-6 bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 flex items-start gap-3 transition-all duration-500">
                                <span class="material-icons-round text-blue-400 mt-0.5">info</span>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-blue-400 mb-1">تنبيه بخصوص تغيير أيام العمل</h4>
                                    <p class="text-xs text-gray-300 leading-relaxed">
                                        أي تغيير في أيام العمل سيبدأ تأثيره على التقارير والإحصائيات ابتداءً من المرة القادمة التي تقوم فيها ببدء يوم عمل جديد. 
                                        النظام يحافظ على دقة التقارير السابقة كما هي؛ التغيير يشمل البيانات المستقبلية فقط.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-content-reset" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-8 glass-panel">
                        <div class="text-center mb-8">
                            <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="material-icons-round text-red-500 text-3xl">warning</span>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">إعادة تهيئة النظام</h3>
                            <p class="text-gray-400">هذه العملية ستعيد ضبط جميع إعدادات النظام إلى الحالة الأولية</p>
                        </div>

                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-6 mb-6">
                            <h4 class="text-lg font-bold text-red-400 mb-4 flex items-center gap-2">
                                <span class="material-icons-round">error_outline</span>
                                تحذير هام
                            </h4>
                            <ul class="text-sm text-gray-300 space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-red-500 text-sm mt-0.5">cancel</span>
                                    سيتم حذف جميع الإعدادات المخصصة (الاسم، العنوان، الشعار، إلخ)
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-red-500 text-sm mt-0.5">cancel</span>
                                    سيتم تعطيل الوحدة المفاتيح الوهمية نهائياً
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-red-500 text-sm mt-0.5">cancel</span>
                                    سيتم إعادة تعيين أسعار التوصيل والإيجار والضرائب إلى القيم الافتراضية
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-red-500 text-sm mt-0.5">cancel</span>
                                    لا يمكن التراجع عن هذه العملية
                                </li>
                            </ul>
                        </div>

                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-6 mb-6">
                            <h4 class="text-lg font-bold text-yellow-400 mb-4 flex items-center gap-2">
                                <span class="material-icons-round">info</span>
                                ما سيحدث بعد إعادة التهيئة
                            </h4>
                            <ul class="text-sm text-gray-300 space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-yellow-500 text-sm mt-0.5">check_circle</span>
                                    سيتم إرجاع جميع الإعدادات إلى القيم الافتراضية
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-yellow-500 text-sm mt-0.5">check_circle</span>
                                    الوحدة المفاتيح الوهمية ستكون معطلة
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-yellow-500 text-sm mt-0.5">check_circle</span>
                                    سيتم تسجيل إشعار في سجل النظام
                                </li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <button type="button" onclick="openResetModal()" class="bg-red-500 hover:bg-red-600 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg shadow-red-500/20 transition-all hover:-translate-y-0.5 flex items-center gap-3 mx-auto">
                                <span class="material-icons-round">restart_alt</span>
                                إعادة تهيئة النظام
                            </button>
                            <p class="text-xs text-gray-500 mt-4">هذه العملية تتطلب تأكيد إضافي</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>

<style>
    /* ... (Same styles) ... */
    .tab-btn.active-tab { background-color: rgba(var(--primary-rgb), 0.1); color: var(--primary-color); border-right: 3px solid var(--primary-color); }
    .tab-btn.active-tab .material-icons-round { color: var(--primary-color); }
    /* ... */
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
        
        localStorage.setItem('activeSettingsTab', tabName);
    }

    function toggleKeyboardSettings(checkbox) {
         const content = document.getElementById('keyboard-settings-content');
         if (!checkbox.checked) {
             content.classList.add('opacity-50', 'pointer-events-none', 'filter', 'blur-sm');
         } else {
             content.classList.remove('opacity-50', 'pointer-events-none', 'filter', 'blur-sm');
         }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const urlTab = new URLSearchParams(window.location.search).get('tab');
        const initialTab = urlTab || 'store';
        switchTab(initialTab);
        document.querySelectorAll('a[data-tab]').forEach(a => {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                const t = a.getAttribute('data-tab');
                switchTab(t);
                history.replaceState(null, '', `settings.php?tab=${t}`);
            });
        });
    });

    // ... (Other scripts) ...
</script>

<?php if ($isAdmin): ?>
<script>
    // ... (Same admin scripts) ...
    // ... (Reset Delivery, Toggle Rental, Notifications logic) ...
    function resetDeliveryPrices() {
        if(confirm('هل أنت متأكد من إعادة تعيين أسعار التوصيل إلى القيم الافتراضية (20/40)؟\nيجب عليك حفظ التغييرات بعد ذلك.')) {
            const insideCity = document.getElementById('deliveryInsideCity');
            const outsideCity = document.getElementById('deliveryOutsideCity');
            insideCity.value = '20'; outsideCity.value = '40';
            [insideCity, outsideCity].forEach(el => { el.style.transition = 'all 0.3s'; el.style.color = '#10b981'; setTimeout(() => el.style.color = '', 1000); });
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

    function toggleWorkDaysSettings(checkbox) {
        const content = document.getElementById('work-days-settings-content');
        if (!checkbox.checked) {
            if (confirm('⚠️ تنبيه!\n\nهل أنت متأكد من تعطيل أيام العمل الأسبوعية؟')) {
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

    // === بداية كود الحفظ التلقائي للشعار ===
    document.getElementById('shopLogoFile').addEventListener('change', async function(event) {
        if (event.target.files && event.target.files[0]) {
            const file = event.target.files[0];
            const formData = new FormData();
            formData.append('shopLogoFile', file);

            // عرض مؤشر التحميل
            const previewContainer = document.querySelector('.w-32.h-32.rounded-full');
            const previewImage = previewContainer.querySelector('img');
            const originalContent = previewContainer.innerHTML;
            previewContainer.innerHTML = `
                <div class="w-full h-full flex items-center justify-center bg-dark/50">
                    <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>`;

            try {
                const response = await fetch('api.php?action=updateShopLogo', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success && result.logoUrl) {
                    // تحديث الصورة في الواجهة
                    const newImageUrl = result.logoUrl + '?t=' + new Date().getTime(); // لمنع التخزين المؤقت
                    previewContainer.innerHTML = `<img src="${newImageUrl}" alt="Logo" class="w-full h-full object-cover">`;
                                        
                    // إظهار رسالة نجاح
                    showToast('success', 'تم تحديث الشعار بنجاح!');
                                        
                    // تحديث رابط الصورة في كل مكان آخر إذا لزم الأمر
                    // مثال: تحديث الشعار في الشريط الجانبي
                    const sidebarLogo = document.querySelector('.sidebar-logo');
                    if(sidebarLogo) sidebarLogo.src = newImageUrl;
                                                             
                    // جعل خانة "إضافة الشعار إلى الفواتير" مرئية تلقائيًا
                    // نبحث عن عنصر checkbox ونجعله مرئيًا
                    const invoiceCheckboxContainer = document.querySelector('.invoice-logo-checkbox');
                    if(invoiceCheckboxContainer) {
                        invoiceCheckboxContainer.classList.remove('hidden');
                        // تحسين الظهور باستخدام fade in
                        setTimeout(() => {
                            invoiceCheckboxContainer.style.opacity = '0';
                            invoiceCheckboxContainer.style.transition = 'opacity 0.3s ease-in-out';
                            setTimeout(() => {
                                invoiceCheckboxContainer.style.opacity = '1';
                            }, 10);
                        }, 10);
                    }
                    
                } else {
                    // إرجاع المحتوى الأصلي عند الفشل
                    previewContainer.innerHTML = originalContent;
                    showToast('error', result.message || 'حدث خطأ غير متوقع');
                }
            } catch (error) {
                previewContainer.innerHTML = originalContent;
                showToast('error', 'فشل الاتصال بالخادم');
                console.error('Error uploading logo:', error);
            }
        }
    });
    // === نهاية كود الحفظ التلقائي للشعار ===


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

<script>
    // Unsaved Changes Detection
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const saveBtn = document.getElementById('save-settings-btn');
        const alertDiv = document.getElementById('unsaved-changes-alert');
        let hasChanges = false;

        // Function to enable save button and show alert
        function enableSave() {
            if (!hasChanges) {
                hasChanges = true;
                saveBtn.disabled = false;
                saveBtn.className = "bg-primary hover:bg-primary-hover text-white px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 flex items-center gap-2 cursor-pointer";
                alertDiv.classList.remove('hidden');
            }
        }

        // Function to disable save button and hide alert
        function disableSave() {
            hasChanges = false;
            saveBtn.disabled = true;
            saveBtn.className = "bg-gray-500/50 text-gray-400 px-8 py-2.5 rounded-xl font-bold shadow-lg transition-all flex items-center gap-2 cursor-not-allowed";
            alertDiv.classList.add('hidden');
        }

        // Add event listeners to all form elements
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', enableSave);
            input.addEventListener('change', enableSave);
        });

        // On form submit, disable after save (assuming success)
        form.addEventListener('submit', function() {
            // After successful save, disable (this would be handled by page reload or AJAX)
            // For now, assume reload disables it
        });

        // Initially disable
        disableSave();

        // Work days change listener
        let workDaysWarningTimer = null;
        document.querySelectorAll('input[name="work_days[]"]').forEach(cb => {
            cb.addEventListener('change', () => {
                const warning = document.getElementById('work-days-warning');
                if (warning) {
                    if (workDaysWarningTimer) clearTimeout(workDaysWarningTimer);
                    
                    warning.classList.remove('bg-blue-500/10', 'border-blue-500/20');
                    warning.classList.add('bg-orange-500/20', 'border-orange-500/40', 'scale-[1.02]');
                    
                    workDaysWarningTimer = setTimeout(() => {
                        warning.classList.remove('bg-orange-500/20', 'border-orange-500/40', 'scale-[1.02]');
                        warning.classList.add('bg-blue-500/10', 'border-blue-500/20');
                        workDaysWarningTimer = null;
                    }, 2000);
                }
            });
        });
    });
</script>

<div id="resetModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="resetBackdrop" onclick="closeResetModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-[#1e1e2e] border border-white/10 rounded-2xl w-full max-w-md transform scale-95 opacity-0 transition-all duration-300 relative shadow-2xl overflow-hidden flex flex-col" id="resetContent">
            <div class="px-6 py-4 bg-red-500/10 border-b border-red-500/20 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-bold text-red-400 flex items-center gap-2">
                    <span class="material-icons-round">warning</span>
                    تأكيد إعادة التهيئة
                </h3>
                <button onclick="closeResetModal()" class="text-gray-400 hover:text-white p-1 hover:bg-white/10 rounded-lg transition-colors"><span class="material-icons-round">close</span></button>
            </div>
            <div class="p-6 text-center">
                <div class="w-12 h-12 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-icons-round text-red-500 text-2xl">restart_alt</span>
                </div>
                <p class="text-white text-lg font-bold mb-2">هل أنت متأكد تماماً؟</p>
                <p class="text-gray-400 text-sm mb-6">سيتم حذف جميع الإعدادات المخصصة وإرجاع النظام إلى الحالة الأولية. هذه العملية لا يمكن التراجع عنها.</p>
                <div class="flex gap-3">
                    <button onclick="closeResetModal()" class="flex-1 bg-gray-500/20 hover:bg-gray-500/30 text-gray-300 border border-gray-500/30 px-4 py-3 rounded-xl font-bold transition-all">إلغاء</button>
                    <button onclick="confirmReset()" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-xl font-bold transition-all shadow-lg shadow-red-500/20">تأكيد الإعادة</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openResetModal() {
        const modal = document.getElementById('resetModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('resetBackdrop').classList.remove('opacity-0');
            const content = document.getElementById('resetContent');
            content.classList.remove('opacity-0', 'scale-95');
            content.classList.add('opacity-100', 'scale-100');
        }, 10);
    }

    function closeResetModal() {
        document.getElementById('resetBackdrop').classList.add('opacity-0');
        const content = document.getElementById('resetContent');
        content.classList.remove('opacity-100', 'scale-100');
        content.classList.add('opacity-0', 'scale-95');
        setTimeout(() => document.getElementById('resetModal').classList.add('hidden'), 300);
    }

    function confirmReset() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'settings.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'reset_settings';
        input.value = '1';
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }

    // دوال إدارة شاشة التحميل
    function showLoadingOverlay(message = 'جاري معالجة البيانات...') {
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
    }

    function hideLoadingOverlay() {
        const loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.classList.add('hidden');
    }
</script>

<div id="loading-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl p-12 border border-white/10 flex flex-col items-center gap-6">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-transparent border-t-primary border-r-primary rounded-full animate-spin"></div>
            <div class="absolute inset-2 border-4 border-transparent border-b-primary/50 rounded-full animate-spin" style="animation-direction: reverse;"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-white mb-2">جاري التحميل...</h3>
            <p id="loading-message" class="text-sm text-gray-400">يرجى الانتظار قليلاً</p>
        </div>
    </div>
</div>

<?php endif; ?>

<?php require_once 'src/footer.php'; ?>
