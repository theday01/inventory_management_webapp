<?php
require_once 'session.php';
require_once 'db.php';
require_once 'src/language.php';

// Check if user is admin
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

// Handle Reset - Admin Only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin && isset($_POST['reset_settings'])) {
    // Disable FK checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Tables to truncate (Wipe all data)
    $tables_to_wipe = [
        'invoices', 'invoice_items', 'products', 'categories', 'category_fields', 
        'product_field_values', 'customers', 'expenses', 'refunds', 'rental_payments', 
        'notifications', 'business_days', 'removed_products', 'media_gallery', 'holidays'
    ];

    foreach ($tables_to_wipe as $table) {
        $conn->query("TRUNCATE TABLE `$table`");
    }

    // Delete all users except current admin to prevent lockout
    $currentUserId = (int)$_SESSION['id'];
    $conn->query("DELETE FROM users WHERE id != $currentUserId");

    // Delete all current settings
    $conn->query("DELETE FROM settings");
    
    // Re-enable FK checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // Default settings
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
        'expense_cycle' => 'monthly',
        'expense_cycle_last_change' => '',
        'work_days_enabled' => '1',
        'holidays_enabled' => '1',
        'work_days' => 'monday,tuesday,wednesday,thursday,friday,saturday',
    ];

    $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?)");
    foreach ($default_settings as $name => $value) {
        $stmt->bind_param("ss", $name, $value);
        $stmt->execute();
    }
    $stmt->close();

    // Add notification
    $msg = __('action_success') . ". " . __('system_reset_subtitle');
    $notifStmt = $conn->prepare("INSERT INTO notifications (message, type) VALUES (?, ?)");
    $type = "system_reset";
    $notifStmt->bind_param("ss", $msg, $type);
    $notifStmt->execute();
    $notifStmt->close();

    header("Location: settings.php?tab=reset&success=" . urlencode($msg));
    exit();
}

// Handle Save - Admin Only
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
        'enable_delivery' => isset($_POST['enable_delivery']) ? '1' : '0',
        'enable_discount' => isset($_POST['enable_discount']) ? '1' : '0',
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
        'expense_cycle' => $_POST['expense_cycle'] ?? 'monthly',
        'work_days_enabled' => isset($_POST['work_days_enabled']) ? '1' : '0',
        'holidays_enabled' => isset($_POST['holidays_enabled']) ? '1' : '0',
        'work_days' => isset($_POST['work_days']) ? implode(',', $_POST['work_days']) : '',
        'day_start_time' => !empty($_POST['day_start_time']) ? date('H:i', strtotime($_POST['day_start_time'])) : '05:00',
        'day_end_time' => !empty($_POST['day_end_time']) ? date('H:i', strtotime($_POST['day_end_time'])) : '00:00',
        'end_day_reminder_enabled' => isset($_POST['end_day_reminder_enabled']) ? '1' : '0',
        'keyboard_enabled' => isset($_POST['keyboard_enabled']) ? '1' : '0',
        'customer_screen_mode' => $_POST['customer_screen_mode'] ?? 'standard',
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
        if ($name === 'expense_cycle' && isset($existing['expense_cycle']) && $existing['expense_cycle'] !== $value) {
            $now = date('Y-m-d H:i:s');
            $conn->query("INSERT INTO settings (setting_name, setting_value) VALUES ('expense_cycle_last_change', '$now') ON DUPLICATE KEY UPDATE setting_value = '$now'");
        }
        $stmt->bind_param("sss", $name, $value, $value);
        $stmt->execute();
    }

    $stmt->close();
    
    // Check for changes
    $changed = false;
    foreach ($settings_to_save as $name => $value) {
        $oldVal = isset($existing[$name]) ? (string)$existing[$name] : null;
        if ($oldVal === null || (string)$value !== $oldVal) {
            $changed = true;
            break;
        }
    }

    if ($changed) {
        $msg = __('settings_updated_success');
        $notifStmt = $conn->prepare("INSERT INTO notifications (message, type) VALUES (?, ?)");
        $type = "settings_update";
        $notifStmt->bind_param("ss", $msg, $type);
        $notifStmt->execute();
        $notifStmt->close();
    }
    header("Location: settings.php?success=" . urlencode(__('changes_saved_success')));
    exit();
}

$page_title = __('settings_page_title');
$current_page = 'settings.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch settings
$result = $conn->query("SELECT * FROM settings");
$settings = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
}

// Disabled attributes
$disabledAttr = 'disabled';
$readonlyClass = 'opacity-60 cursor-not-allowed pointer-events-none';
?>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <form method="POST" action="settings.php" enctype="multipart/form-data" class="flex-1 flex flex-col overflow-hidden" onsubmit="return false;">
        
        <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-4 lg:px-8 relative z-10 shrink-0">
            <div class="flex items-center gap-4">
                <div class="p-2.5 bg-primary/10 rounded-xl">
                    <span class="material-icons-round text-primary">settings</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white"><?php echo __('settings_page_title'); ?></h2>
                    <p class="text-xs text-gray-400"><?php echo __('settings_subtitle'); ?></p>
                    <div id="unsaved-changes-alert" class="hidden mt-2 text-xs bg-orange-500/10 text-orange-500 border border-orange-500/20 px-3 py-1 rounded-full font-bold flex items-center gap-1">
                        <span class="material-icons-round text-xs">warning</span>
                        <span><?php echo __('unsaved_changes_warning'); ?></span>
                    </div>
                </div>
                
                <?php if (!$isAdmin): ?>
                    <span class="mr-4 text-xs bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 px-3 py-1 rounded-full font-bold flex items-center gap-1">
                        <span class="material-icons-round text-sm">visibility</span>
                        <?php echo __('read_only_mode'); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-4">
                <?php if ($isAdmin): ?>
                    <button type="submit" id="save-settings-btn" disabled class="bg-gray-500/50 text-gray-400 px-4 md:px-8 py-2.5 rounded-xl font-bold shadow-lg transition-all flex items-center gap-2 cursor-not-allowed">
                        <span class="material-icons-round text-sm">save</span>
                        <span class="hidden md:inline"><?php echo __('save_changes_btn'); ?></span>
                    </button>
                <?php else: ?>
                    <div class="bg-gray-500/20 text-gray-400 px-6 py-2 rounded-xl font-bold flex items-center gap-2 cursor-not-allowed">
                        <span class="material-icons-round text-sm">lock</span>
                        <span><?php echo __('editing_not_allowed'); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <div class="flex-1 overflow-hidden flex flex-col lg:flex-row relative z-10">
            
            <?php
                $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'store';
                require_once 'src/settings_sidebar.php';
            ?>

            <div class="flex-1 overflow-y-auto p-4 lg:p-8 relative scroll-smooth" id="settings-content-area">
                
                <div id="tab-content-store" class="tab-content space-y-6 max-w-5xl mx-auto animate-fade-in">
                    
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl overflow-hidden glass-panel">
                        <div class="px-4 lg:px-8 py-4 lg:py-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                            <h3 class="text-lg font-bold text-white flex items-center gap-3">
                                <div class="p-2 bg-primary/10 rounded-lg text-primary">
                                    <span class="material-icons-round">storefront</span>
                                </div>
                                <?php echo __('store_identity_title'); ?>
                            </h3>
                        </div>

                        <div class="p-4 lg:p-8 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
                            
                            <div class="lg:col-span-4 flex flex-col items-center justify-center p-6 border border-dashed border-white/10 rounded-2xl bg-white/[0.02] hover:bg-white/[0.04] transition-colors group relative">
                                <div class="w-32 h-32 rounded-full bg-gradient-to-tr from-gray-800 to-gray-700 flex items-center justify-center mb-4 shadow-xl shadow-black/20 overflow-hidden relative">
                                    <?php if (!empty($settings['shopLogoUrl'] ?? '')): ?>
                                        <img src="<?php echo htmlspecialchars($settings['shopLogoUrl']); ?>" alt="Logo" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <span class="material-icons-round text-5xl text-gray-500 group-hover:scale-110 transition-transform duration-300">add_a_photo</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-bold text-white mb-1"><?php echo __('shop_logo'); ?></p>
                                    <p class="text-[10px] text-gray-400 mb-3"><?php echo __('logo_format_info'); ?></p>
                                    <div class="flex items-center justify-center gap-2 relative z-10">
                                        <button type="button" onclick="document.getElementById('shopLogoFile').click();" class="px-4 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition-all <?php echo $disabledAttr; ?>">
                                            <?php echo __('change_photo_btn'); ?>
                                        </button>
                                        <button type="button" id="btn-delete-logo" onclick="deleteShopLogo()" class="px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-500 transition-all <?php echo empty($settings['shopLogoUrl'] ?? '') ? 'hidden' : ''; ?> <?php echo $disabledAttr; ?>" title="<?php echo __('delete_logo'); ?>">
                                            <span class="material-icons-round text-sm">delete</span>
                                        </button>
                                    </div>
                                </div>
                                <input type="file" name="shopLogoFile" id="shopLogoFile" accept="image/png,image/jpeg" class="absolute inset-0 opacity-0 cursor-not-allowed pointer-events-none" title="">
                            </div>

                            <!-- Favicon Section -->
                            <div class="lg:col-span-2 flex flex-col items-center justify-center p-6 border border-dashed border-white/10 rounded-2xl bg-white/[0.02] transition-colors group relative">
                                <div class="w-16 h-16 rounded-xl bg-gradient-to-tr from-gray-800 to-gray-700 flex items-center justify-center mb-4 shadow-lg overflow-hidden relative">
                                    <?php if (!empty($settings['shopFavicon'] ?? '')): ?>
                                        <img src="<?php echo htmlspecialchars($settings['shopFavicon']); ?>" alt="Favicon" class="w-full h-full object-contain p-2">
                                    <?php else: ?>
                                        <span class="material-icons-round text-3xl text-gray-500">api</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-bold text-white mb-1"><?php echo __('shop_favicon'); ?></p>
                                    <p class="text-[10px] text-gray-400 mb-0"><?php echo __('favicon_auto_generated'); ?></p>
                                </div>
                            </div>

                            <div class="lg:col-span-6 space-y-6">
                                <?php $hasLogo = !empty($settings['shopLogoUrl'] ?? ''); ?>
                                <label class="inline-flex items-center gap-2 invoice-logo-checkbox <?php echo $hasLogo ? '' : 'hidden'; ?>">
                                    <input type="checkbox" name="invoiceShowLogo" value="1" <?php echo (($settings['invoiceShowLogo'] ?? '0') === '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?>>
                                    <span class="text-xs font-bold text-gray-300"><?php echo __('show_logo_on_invoice'); ?></span>
                                </label>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2 mr-1"><?php echo __('shop_name_label'); ?></label>
                                    <div class="relative group">
                                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-primary transition-colors">
                                            <span class="material-icons-round text-lg">edit</span>
                                        </div>
                                        <input type="text" name="shopName" value="<?php echo htmlspecialchars($settings['shopName'] ?? ''); ?>"
                                            placeholder="<?php echo __('shop_name_placeholder'); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right pr-12 pl-4 py-3.5 rounded-xl focus:outline-none focus:border-primary/50 focus:ring-4 focus:ring-primary/10 transition-all font-bold text-lg placeholder-gray-600 <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                    </div>
                                </div>

                               
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl overflow-hidden glass-panel">
                        <div class="px-4 lg:px-8 py-4 lg:py-6 border-b border-white/5 flex items-center justify-between bg-white/[0.02]">
                            <h3 class="text-lg font-bold text-white flex items-center gap-3">
                                <div class="p-2 bg-blue-500/10 rounded-lg text-blue-500">
                                    <span class="material-icons-round">contact_phone</span>
                                </div>
                                <?php echo __('contact_location_title'); ?>
                            </h3>
                        </div>

                        <div class="p-4 lg:p-8 grid grid-cols-1 md:grid-cols-2 gap-x-6 lg:gap-x-8 gap-y-6">
                            
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold text-gray-400 mb-2 mr-1"><?php echo __('official_phone_label'); ?></label>
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
                                <label class="block text-xs font-bold text-gray-400 mb-2 mr-1"><?php echo __('city_label'); ?></label>
                                <div class="relative group">
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-blue-500 transition-colors">
                                        <span class="material-icons-round text-lg">location_city</span>
                                    </div>
                                    <input type="text" name="shopCity" value="<?php echo htmlspecialchars($settings['shopCity'] ?? ''); ?>"
                                        placeholder="<?php echo __('home_city_placeholder'); ?>"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right pr-12 pl-4 py-3 rounded-xl focus:outline-none focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                </div>
                            </div>

                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-400 mb-2 mr-1"><?php echo __('address_label'); ?></label>
                                <div class="relative group">
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-blue-500 transition-colors">
                                        <span class="material-icons-round text-lg">place</span>
                                    </div>
                                    <input type="text" name="shopAddress" value="<?php echo htmlspecialchars($settings['shopAddress'] ?? ''); ?>"
                                        placeholder="<?php echo __('address_placeholder'); ?>"
                                        class="w-full bg-dark/50 border border-white/10 text-white text-right pr-12 pl-4 py-3 rounded-xl focus:outline-none focus:border-blue-500/50 focus:ring-4 focus:ring-blue-500/10 transition-all <?php echo $readonlyClass; ?>"
                                        <?php echo $disabledAttr; ?>>
                                </div>
                            </div>

                            <div class="col-span-2 mt-2">
                                <div class="bg-gradient-to-r from-gray-800/50 to-gray-900/50 rounded-xl p-4 border border-white/5 flex items-center gap-3 opacity-60">
                                    <span class="material-icons-round text-yellow-500">tips_and_updates</span>
                                    <p class="text-[10px] text-gray-400">
                                        <?php echo __('contact_info_tip'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                
                <div id="tab-content-delivery" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 border-b border-white/5 pb-4 gap-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">local_shipping</span>
                                <?php echo __('delivery_settings_title'); ?>
                            </h3>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-between w-full sm:w-auto gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                                    <span class="text-sm text-gray-300"><?php echo __('enable_delivery_label'); ?></span>
                                    <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="enable_delivery" id="toggle-delivery" value="1"
                                            class="toggle-checkbox"
                                            <?php echo (isset($settings['enable_delivery']) && $settings['enable_delivery'] == '1') ? 'checked' : ''; ?>
                                            <?php echo $disabledAttr; ?> />
                                        <label for="toggle-delivery" class="toggle-label block overflow-hidden h-6 rounded-full cursor-not-allowed pointer-events-none"></label>
                                    </div>
                                </div>
                                <?php if ($isAdmin): ?>
                                    <button type="button" disabled 
                                        class="w-full sm:w-auto text-xs flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 text-gray-400 transition-all opacity-50 cursor-not-allowed">
                                        <span class="material-icons-round text-sm">restart_alt</span>
                                        <span><?php echo __('reset_btn'); ?></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                <label class="block text-sm font-bold text-white mb-2 flex items-center gap-2">
                                    <span class="material-icons-round text-primary text-sm">location_city</span>
                                    <?php echo __('home_city_label'); ?>
                                </label>
                                <input type="text" name="deliveryHomeCity" 
                                    value="<?php echo htmlspecialchars($settings['deliveryHomeCity'] ?? ''); ?>"
                                    placeholder="<?php echo __('home_city_placeholder'); ?>"
                                    class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                    <?php echo $disabledAttr; ?>>
                                <p class="text-xs text-gray-500 mt-2">
                                    <?php echo __('home_city_tip'); ?>
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                    <label class="block text-sm font-medium text-gray-300 mb-3"><?php echo __('inside_city_cost_label'); ?></label>
                                    <div class="relative">
                                        <input type="number" id="deliveryInsideCity" name="deliveryInsideCity" step="0.01" min="0"
                                            value="<?php echo htmlspecialchars($settings['deliveryInsideCity'] ?? '0'); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all font-bold text-lg <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold"><?php echo htmlspecialchars($settings['currency'] ?? 'MAD'); ?></span>
                                    </div>
                                </div>
                                <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                    <label class="block text-sm font-medium text-gray-300 mb-3"><?php echo __('outside_city_cost_label'); ?></label>
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
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 border-b border-white/5 pb-4 gap-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">home_work</span>
                                <?php echo __('rental_management_title'); ?>
                            </h3>
                            <div class="flex items-center justify-between w-full sm:w-auto gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                                <span class="text-sm text-gray-300"><?php echo __('enable_rental_system'); ?></span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="rentalEnabled" id="toggle-rental" value="1"
                                        class="toggle-checkbox"
                                        <?php echo (isset($settings['rentalEnabled']) && $settings['rentalEnabled'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?>
                                        onchange="toggleRentalSettings(this)" />
                                    <label for="toggle-rental" class="toggle-label block overflow-hidden h-6 rounded-full cursor-not-allowed pointer-events-none"></label>
                                </div>
                            </div>
                        </div>

                        <div id="rental-settings-content" class="transition-all duration-300 <?php echo (!isset($settings['rentalEnabled']) || $settings['rentalEnabled'] == '0') ? '' : ''; ?>">
                            
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                                <div class="lg:col-span-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                        <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wider"><?php echo __('rental_amount_label'); ?></label>
                                        <div class="relative">
                                            <input type="number" name="rentalAmount" step="0.01" min="0"
                                                value="<?php echo htmlspecialchars($settings['rentalAmount'] ?? '0'); ?>"
                                                class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all font-bold text-lg <?php echo $readonlyClass; ?>"
                                                <?php echo $disabledAttr; ?>>
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold"><?php echo htmlspecialchars($settings['currency'] ?? 'MAD'); ?></span>
                                        </div>
                                    </div>

                                    <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                        <label class="block text-xs font-bold text-gray-400 mb-2 uppercase tracking-wider"><?php echo __('payment_system_label'); ?></label>
                                        <select name="rentalType"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                            <option value="monthly" <?php echo (isset($settings['rentalType']) && $settings['rentalType'] == 'monthly') ? 'selected' : ''; ?>><?php echo __('monthly_payment'); ?></option>
                                            <option value="yearly" <?php echo (isset($settings['rentalType']) && $settings['rentalType'] == 'yearly') ? 'selected' : ''; ?>><?php echo __('yearly_payment'); ?></option>
                                        </select>
                                    </div>

                                    <div class="bg-white/5 border border-white/5 rounded-xl p-5 md:col-span-2">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider"><?php echo __('next_due_date_label'); ?></label>
                                            <span class="text-[10px] text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded"><?php echo __('auto_update_tip'); ?></span>
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
                                            <h4 class="font-bold"><?php echo __('early_alert_title'); ?></h4>
                                        </div>
                                        <label class="block text-sm text-gray-300 mb-2"><?php echo __('reminder_days_label'); ?></label>
                                        <input type="number" name="rentalReminderDays" min="1" max="30"
                                            value="<?php echo htmlspecialchars($settings['rentalReminderDays'] ?? '7'); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-center px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 font-bold text-xl <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                    </div>
                                    <div class="mt-4 text-xs text-gray-400 leading-relaxed bg-black/20 p-3 rounded-lg">
                                        <?php echo __('notification_info_text'); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-white/5 border border-white/5 rounded-xl p-5">
                                    <h4 class="font-bold text-white mb-4 flex items-center gap-2">
                                        <span class="material-icons-round text-sm text-gray-400">person</span>
                                        <?php echo __('landlord_data_title'); ?>
                                    </h4>
                                    <div class="space-y-4">
                                        <input type="text" name="rentalLandlordName" placeholder="<?php echo __('landlord_name_placeholder'); ?>"
                                            value="<?php echo htmlspecialchars($settings['rentalLandlordName'] ?? ''); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <input type="text" name="rentalLandlordPhone" placeholder="<?php echo __('landlord_phone_placeholder'); ?>"
                                            value="<?php echo htmlspecialchars($settings['rentalLandlordPhone'] ?? ''); ?>"
                                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>"
                                            <?php echo $disabledAttr; ?>>
                                        <textarea name="rentalNotes" rows="2" placeholder="<?php echo __('rental_notes_placeholder'); ?>"
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
                                                <h4 class="font-bold text-white"><?php echo __('quick_action_title'); ?></h4>
                                                <p class="text-xs text-green-400"><?php echo __('pay_current_month_label'); ?></p>
                                            </div>
                                        </div>
                                        <button type="button" id="btn-rental-paid" class="w-full py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold transition-all shadow-lg shadow-green-600/20 flex items-center justify-center gap-2 <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                            <span><?php echo __('confirm_payment_now_btn'); ?></span>
                                            <span class="material-icons-round text-sm">check</span>
                                        </button>
                                    </div>

                                    <button type="button" id="btn-rental-payments-log" class="w-full py-3 bg-white/5 hover:bg-white/10 border border-white/10 text-gray-300 hover:text-white rounded-xl font-bold transition-all flex items-center justify-center gap-2 <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                        <span class="material-icons-round text-sm">history</span>
                                        <span><?php echo __('view_payment_history_btn'); ?></span>
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
                            <div class="flex-1"><h3 class="text-lg font-bold text-white"><?php echo __('system_currency_title'); ?></h3></div>
                            <div class="w-48">
                                <input type="text" value="<?php echo __('currency_mad'); ?>" class="w-full bg-dark border border-white/10 text-white text-right px-4 py-2.5 rounded-xl opacity-80 cursor-not-allowed" disabled>
                                <input type="hidden" name="currency" value="MAD">
                            </div>
                        </div>
                     </div>

                     <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">percent</span>
                                <?php echo __('discount'); ?>
                            </h3>
                            <div class="flex items-center justify-between w-full sm:w-auto gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                                <span class="text-sm text-gray-300"><?php echo __('enable_discount_label'); ?></span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="enable_discount" id="toggle-discount" value="1"
                                        class="toggle-checkbox"
                                        <?php echo (isset($settings['enable_discount']) && $settings['enable_discount'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?> />
                                    <label for="toggle-discount" class="toggle-label block overflow-hidden h-6 rounded-full cursor-not-allowed pointer-events-none"></label>
                                </div>
                            </div>
                        </div>
                     </div>

                     <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">update</span>
                                <?php echo __('expense_cycle_title'); ?>
                            </h3>
                            <div class="bg-orange-500/10 border border-orange-500/20 px-4 py-2 rounded-xl">
                                <p class="text-[10px] text-orange-400 font-bold"><?php echo __('expense_cycle_warning'); ?></p>
                            </div>
                        </div>
                        <div class="p-6 bg-white/5 rounded-2xl border border-white/5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('select_cycle_label'); ?></label>
                                    <select name="expense_cycle" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all" <?php echo $disabledAttr; ?>>
                                        <option value="monthly" <?php echo (isset($settings['expense_cycle']) && $settings['expense_cycle'] == 'monthly') ? 'selected' : ''; ?>><?php echo __('monthly_cycle_option'); ?></option>
                                        <option value="bi-monthly" <?php echo (isset($settings['expense_cycle']) && $settings['expense_cycle'] == 'bi-monthly') ? 'selected' : ''; ?>><?php echo __('bi_monthly_cycle_option'); ?></option>
                                    </select>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <p class="mb-2"><strong class="text-gray-300"><?php echo __('monthly'); ?>:</strong> <?php echo __('monthly_cycle_explanation'); ?></p>
                                    <p><strong class="text-gray-300"><?php echo __('bi_monthly'); ?>:</strong> <?php echo __('bi_monthly_cycle_explanation'); ?></p>
                                    <?php if(!empty($settings['expense_cycle_last_change'] ?? '')): ?>
                                        <p class="mt-3 text-primary"><?php echo __('last_change_date_label'); ?> <?php echo htmlspecialchars($settings['expense_cycle_last_change']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                     </div>

                     <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3"><span class="material-icons-round text-primary">receipt</span><?php echo __('tax_settings_title'); ?></h3>
                             <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-400"><?php echo __('enable_tax_label'); ?></span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="taxEnabled" id="toggle-tax" value="1" class="toggle-checkbox" <?php echo (isset($settings['taxEnabled']) && $settings['taxEnabled'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> onchange="toggleTaxSettings(this)" />
                                    <label for="toggle-tax" class="toggle-label block overflow-hidden h-6 rounded-full cursor-not-allowed pointer-events-none"></label>
                                </div>
                            </div>
                        </div>
                        <div id="tax-settings-container" class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-white/5 rounded-2xl border border-white/5 transition-all duration-300 <?php echo (!isset($settings['taxEnabled']) || $settings['taxEnabled'] == '0') ? '' : ''; ?>">
                            <div><label class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('tax_name_label'); ?></label><input type="text" name="taxLabel" value="<?php echo htmlspecialchars($settings['taxLabel'] ?? 'TVA'); ?>" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl" <?php echo $disabledAttr; ?>></div>
                            <div><label class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('tax_rate_label'); ?></label><input type="number" name="taxRate" value="<?php echo htmlspecialchars($settings['taxRate'] ?? '20'); ?>" step="0.01" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl" <?php echo $disabledAttr; ?>></div>
                        </div>
                     </div>
                </div>

                <div id="tab-content-print" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">print</span>
                            <?php echo __('print_settings_title'); ?>
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label for="printMode" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('default_print_mode_label'); ?></label>
                                <select id="printMode" name="printMode" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                    <option value="normal" <?php echo ($settings['printMode'] ?? 'normal') == 'normal' ? 'selected' : ''; ?>><?php echo __('normal_print_option'); ?></option>
                                    <option value="thermal" <?php echo ($settings['printMode'] ?? 'normal') == 'thermal' ? 'selected' : ''; ?>><?php echo __('thermal_print_option'); ?></option>
                                </select>
                                <p class="text-xs text-gray-500 mt-2"><?php echo __('print_mode_info'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-content-system" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">palette</span>
                            <?php echo __('interface_sound_preferences'); ?>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-icons-round text-gray-400">dark_mode</span>
                                    <div>
                                        <h4 class="font-bold text-white text-sm"><?php echo __('dark_mode_title'); ?></h4>
                                        <p class="text-[10px] text-gray-400"><?php echo __('dark_mode_subtitle'); ?></p>
                                    </div>
                                </div>
                                <div class="relative inline-block w-10 align-middle select-none">
                                    <input type="checkbox" name="darkMode" id="toggle-dark" value="1" class="toggle-checkbox"  <?php echo (isset($settings['darkMode']) && $settings['darkMode'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> />
                                    <label for="toggle-dark" class="toggle-label block overflow-hidden h-5 rounded-full cursor-not-allowed pointer-events-none"></label>
                                </div>
                            </div>
                             <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-icons-round text-gray-400">volume_up</span>
                                    <div>
                                        <h4 class="font-bold text-white text-sm"><?php echo __('system_sounds_title'); ?></h4>
                                        <p class="text-[10px] text-gray-400"><?php echo __('system_sounds_subtitle'); ?></p>
                                    </div>
                                </div>
                                <div class="relative inline-block w-10 align-middle select-none">
                                    <input type="checkbox" name="soundNotifications" id="toggle-sound" value="1" class="toggle-checkbox" <?php echo (isset($settings['soundNotifications']) && $settings['soundNotifications'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> />
                                    <label for="toggle-sound" class="toggle-label block overflow-hidden h-5 rounded-full cursor-not-allowed pointer-events-none"></label>
                                </div>
                            </div>

                            <!-- Virtual Keyboard Option -->
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-icons-round text-gray-400">keyboard</span>
                                    <div>
                                        <h4 class="font-bold text-white text-sm"><?php echo __('virtual_keyboard_title'); ?></h4>
                                        <p class="text-[10px] text-gray-400"><?php echo __('virtual_keyboard_subtitle'); ?></p>
                                    </div>
                                </div>
                                <div class="relative inline-block w-10 align-middle select-none">
                                    <input type="checkbox" name="keyboard_enabled" id="toggle-keyboard" value="1" class="toggle-checkbox"  <?php echo (isset($settings['keyboard_enabled']) && $settings['keyboard_enabled'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> />
                                    <label for="toggle-keyboard" class="toggle-label block overflow-hidden h-5 rounded-full cursor-not-allowed pointer-events-none"></label>
                                </div>
                            </div>

                            <!-- Customer Screen Mode -->
                            <div class="col-span-2 flex items-center justify-between p-4 bg-white/5 rounded-xl border border-white/5 hover:border-white/10 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-icons-round text-gray-400">monitor</span>
                                    <div>
                                        <h4 class="font-bold text-white text-sm"><?php echo __('customer_screen_mode_label'); ?></h4>
                                        <p class="text-[10px] text-gray-400"><?php echo __('customer_screen_mode_desc'); ?></p>
                                    </div>
                                </div>
                                <div class="relative w-64">
                                    <select name="customer_screen_mode" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-2 rounded-xl focus:outline-none focus:border-primary/50 transition-all text-sm" <?php echo $disabledAttr; ?>>
                                        <option value="standard" <?php echo ($settings['customer_screen_mode'] ?? 'standard') == 'standard' ? 'selected' : ''; ?>><?php echo __('cs_mode_standard'); ?></option>
                                        <option value="simple" <?php echo ($settings['customer_screen_mode'] ?? 'standard') == 'simple' ? 'selected' : ''; ?>><?php echo __('cs_mode_simple'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">inventory</span>
                            <?php echo __('stock_alerts_title'); ?>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="low_quantity_alert" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('low_stock_limit_label'); ?></label>
                                <input type="number" id="low_quantity_alert" name="low_quantity_alert" value="<?php echo htmlspecialchars($settings['low_quantity_alert'] ?? '30'); ?>" class="w-full bg-dark/50 border border-white/10 text-white text-center px-4 py-3 rounded-xl font-bold text-lg focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                <p class="text-xs text-gray-500 mt-2"><?php echo __('low_stock_info_text'); ?></p>
                            </div>
                            <div>
                                <label for="critical_quantity_alert" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('critical_stock_limit_label'); ?></label>
                                <input type="number" id="critical_quantity_alert" name="critical_quantity_alert" value="<?php echo htmlspecialchars($settings['critical_quantity_alert'] ?? '10'); ?>" class="w-full bg-dark/50 border border-white/10 text-white text-center px-4 py-3 rounded-xl font-bold text-lg focus:outline-none focus:border-primary/50 transition-all <?php echo $readonlyClass; ?>" <?php echo $disabledAttr; ?>>
                                <p class="text-xs text-gray-500 mt-2"><?php echo __('critical_stock_info_text'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel" id="stock-alerts-container">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2"><span class="material-icons-round text-primary">update</span><?php echo __('auto_stock_check_title'); ?></h3>
                             <div class="relative inline-block w-10 align-middle select-none">
                                <input type="checkbox" name="stockAlertsEnabled" id="toggle-stock-alerts" value="1" class="toggle-checkbox" <?php echo (isset($settings['stockAlertsEnabled']) && $settings['stockAlertsEnabled'] == '1') ? 'checked' : ''; ?> <?php echo $disabledAttr; ?> onchange="handleStockAlertToggle(this)" />
                                <label for="toggle-stock-alerts" class="toggle-label block overflow-hidden h-5 rounded-full cursor-not-allowed pointer-events-none"></label>
                            </div>
                        </div>
                         <div id="stock-alerts-settings" class="<?php echo (!isset($settings['stockAlertsEnabled']) || $settings['stockAlertsEnabled'] == '0') ? 'opacity-50 pointer-events-none' : ''; ?>">
                            <div class="flex items-end gap-4">
                                <div class="flex-1"><label class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('check_interval_label'); ?></label><input type="number" name="stockAlertInterval" value="<?php echo htmlspecialchars($settings['stockAlertInterval'] ?? '20'); ?>" min="1" max="1440" step="1" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl" <?php echo $disabledAttr; ?>></div>
                                 <button type="button" disabled class="px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-primary text-sm font-bold flex items-center gap-2 transition-all opacity-50 cursor-not-allowed"><span class="material-icons-round text-sm">help_outline</span><?php echo __('how_to_choose_btn'); ?></button>
                            </div>
                        </div>
                         <div class="mt-6 pt-6 border-t border-white/5">
                             <button type="button" disabled id="enable-windows-notifications" class="w-full bg-primary/10 text-primary border border-primary/20 px-4 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2 opacity-50 cursor-not-allowed"><span class="material-icons-round text-sm">notifications_active</span><span><?php echo __('enable_windows_notifications_btn'); ?></span></button>
                        </div>
                     </div>

                    <!-- End of Day Reminder Settings -->
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-8 glass-panel mt-6">
                        <div class="flex items-center justify-between mb-6 border-b border-white/5 pb-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-yellow-500">access_alarm</span>
                                <?php echo __('end_of_day_reminder_title'); ?>
                            </h3>
                            <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="end_day_reminder_enabled" id="toggle-end-day-reminder" value="1"
                                    class="toggle-checkbox"
                                    <?php echo (($settings['end_day_reminder_enabled'] ?? '1') == '1') ? 'checked' : ''; ?>
                                    <?php echo $disabledAttr; ?>
                                    onchange="toggleEndDaySettings(this)" />
                                <label for="toggle-end-day-reminder" class="toggle-label block overflow-hidden h-6 rounded-full cursor-not-allowed pointer-events-none"></label>
                            </div>
                        </div>

                        <div id="end-day-settings-content" class="grid grid-cols-1 md:grid-cols-2 gap-6 transition-all duration-300 <?php echo (($settings['end_day_reminder_enabled'] ?? '1') == '0') ? '' : ''; ?>">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('day_start_time_label'); ?></label>
                                <div class="relative">
                                    <input type="text" 
                                           name="day_start_time" 
                                           value="<?php echo htmlspecialchars($settings['day_start_time'] ?? '05:00'); ?>" 
                                           placeholder="HH:MM" 
                                           pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                           maxlength="5"
                                           oninput="formatTimeInput(this)"
                                           class="w-full bg-dark/50 border border-white/10 text-white text-center px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all font-bold text-lg ltr" 
                                           <?php echo $disabledAttr; ?>>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none opacity-50">
                                        <span class="text-xs text-gray-500 font-mono">24h</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('day_end_time_label'); ?></label>
                                <div class="relative">
                                    <input type="text" 
                                           name="day_end_time" 
                                           value="<?php echo htmlspecialchars($settings['day_end_time'] ?? '00:00'); ?>" 
                                           placeholder="HH:MM" 
                                           pattern="([01]?[0-9]|2[0-3]):[0-5][0-9]"
                                           maxlength="5"
                                           oninput="formatTimeInput(this)"
                                           class="w-full bg-dark/50 border border-white/10 text-white text-center px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all font-bold text-lg ltr" 
                                           <?php echo $disabledAttr; ?>>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none opacity-50">
                                        <span class="text-xs text-gray-500 font-mono">24h</span>
                                    </div>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 bg-white/5 p-3 rounded-lg border border-white/5 flex items-center gap-2">
                                    <span class="material-icons-round text-sm">info</span>
                                    <?php echo __('end_day_reminder_desc'); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                <div id="tab-content-language" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3 mb-6">
                            <span class="material-icons-round text-primary">translate</span>
                            <?php echo __('system_language_title'); ?>
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Arabic Option -->
                            <div onclick="setSystemLanguage('ar')" class="relative group cursor-pointer block">
                                <div class="absolute inset-0 bg-primary/20 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity <?php echo get_locale() === 'ar' ? 'opacity-100' : ''; ?>"></div>
                                <div class="relative bg-white/5 border <?php echo get_locale() === 'ar' ? 'border-primary' : 'border-white/10 group-hover:border-primary/50'; ?> rounded-2xl p-6 flex items-center gap-4 transition-all">
                                    <div class="w-16 h-16 rounded-full overflow-hidden shadow-inner border border-white/10">
                                        <svg class="w-full h-full object-cover" viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="900" height="600" fill="#c1272d"/>
                                            <path fill="none" stroke="#006233" stroke-width="30" d="M450,191.6 L361.8,462.6 L591.8,295.6 L308.2,295.6 L538.2,462.6 Z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-xl font-bold text-white mb-1"><?php echo __('arabic'); ?></h4>
                                        <p class="text-sm text-gray-400">اللغة العربية</p>
                                    </div>
                                    <?php if(get_locale() === 'ar'): ?>
                                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/30">
                                            <span class="material-icons-round">check</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- French Option -->
                            <div onclick="setSystemLanguage('fr')" class="relative group cursor-pointer block">
                                 <div class="absolute inset-0 bg-primary/20 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity <?php echo get_locale() === 'fr' ? 'opacity-100' : ''; ?>"></div>
                                <div class="relative bg-white/5 border <?php echo get_locale() === 'fr' ? 'border-primary' : 'border-white/10 group-hover:border-primary/50'; ?> rounded-2xl p-6 flex items-center gap-4 transition-all">
                                     <div class="w-16 h-16 rounded-full overflow-hidden shadow-inner border border-white/10">
                                        <svg class="w-full h-full object-cover" viewBox="0 0 900 600" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="300" height="600" fill="#0055A4"/>
                                            <rect x="300" width="300" height="600" fill="#FFFFFF"/>
                                            <rect x="600" width="300" height="600" fill="#EF4135"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-xl font-bold text-white mb-1"><?php echo __('french'); ?></h4>
                                        <p class="text-sm text-gray-400">Français</p>
                                    </div>
                                    <?php if(get_locale() === 'fr'): ?>
                                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/30">
                                            <span class="material-icons-round">check</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="tab-content-workdays" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <!-- Work Days Settings -->
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 border-b border-white/5 pb-4 gap-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">calendar_month</span>
                                <?php echo __('weekly_work_days_title'); ?>
                            </h3>
                            <div class="flex items-center justify-between w-full sm:w-auto gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                                <span class="text-sm text-gray-300"><?php echo __('enable_work_days_label'); ?></span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="work_days_enabled" id="toggle-work-days" value="1"
                                        class="toggle-checkbox"
                                        <?php echo (isset($settings['work_days_enabled']) && $settings['work_days_enabled'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?>
                                        onchange="toggleWorkDaysSettings(this)" />
                                    <label for="toggle-work-days" class="toggle-label block overflow-hidden h-6 rounded-full cursor-not-allowed pointer-events-none"></label>
                                </div>
                            </div>
                        </div>

                        <div id="work-days-settings-content" class="transition-all duration-300 <?php echo (!isset($settings['work_days_enabled']) || $settings['work_days_enabled'] == '0') ? '' : ''; ?>">
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                                <?php
                                $days = ['monday' => __('day_monday'), 'tuesday' => __('day_tuesday'), 'wednesday' => __('day_wednesday'), 'thursday' => __('day_thursday'), 'friday' => __('day_friday'), 'saturday' => __('day_saturday'), 'sunday' => __('day_sunday')];
                                $work_days = explode(',', $settings['work_days'] ?? 'monday,tuesday,wednesday,thursday,friday,saturday');
                                foreach ($days as $en => $ar) {
                                    $checked = in_array($en, $work_days) ? 'checked' : '';
                                    echo "
                                    <label class='flex items-center gap-2 bg-white/5 border border-white/10 rounded-xl p-3 cursor-not-allowed pointer-events-none hover:bg-white/10 transition-colors'>
                                        <input type='checkbox' name='work_days[]' value='$en' $checked class='form-checkbox h-5 w-5 text-primary bg-dark border-white/20 rounded focus:ring-primary/50' $disabledAttr>
                                        <span class='text-white font-bold text-sm'>$ar</span>
                                    </label>
                                    ";
                                }
                                ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-4"><?php echo __('work_days_info'); ?></p>

                            <div id="work-days-warning" class="mt-6 bg-blue-500/10 border border-blue-500/20 rounded-xl p-4 flex items-start gap-3 transition-all duration-500">
                                <span class="material-icons-round text-blue-400 mt-0.5">info</span>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-blue-400 mb-1"><?php echo __('work_days_warning_title'); ?></h4>
                                    <p class="text-xs text-gray-300 leading-relaxed">
                                        <?php echo __('work_days_warning_text'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Moroccan National and Religious Holidays -->
                    <div id="holidays-management-section" class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 border-b border-white/5 pb-4 gap-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">festival</span>
                                <?php echo __('holidays_title'); ?>
                            </h3>
                            <div class="flex items-center justify-between w-full sm:w-auto gap-3 bg-white/5 px-4 py-2 rounded-xl border border-white/5">
                                <span class="text-sm text-gray-300"><?php echo __('enable_holidays_label'); ?></span>
                                <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="holidays_enabled" id="toggle-holidays" value="1"
                                        class="toggle-checkbox"
                                        <?php echo (isset($settings['holidays_enabled']) && $settings['holidays_enabled'] == '1') ? 'checked' : ''; ?>
                                        <?php echo $disabledAttr; ?>
                                        onchange="toggleHolidaysSettings(this)" />
                                    <label for="toggle-holidays" class="toggle-label block overflow-hidden h-6 rounded-full cursor-not-allowed pointer-events-none"></label>
                                </div>
                            </div>
                        </div>

                        <div id="holidays-settings-content" class="transition-all duration-300 <?php echo (!isset($settings['holidays_enabled']) || $settings['holidays_enabled'] == '0') ? '' : ''; ?>">
                            
                        <div class="mb-6">
                            <p class="text-xs text-gray-500 mb-4"><?php echo __('holidays_subtitle'); ?></p>
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div id="holiday-sync-status" class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                    <span class="text-[10px] text-gray-400"><?php echo __('last_sync_label'); ?></span>
                                    <span id="last-sync-date" class="text-[10px] text-primary font-bold">
                                        <?php echo !empty($settings['last_holiday_sync_date'] ?? '') ? htmlspecialchars($settings['last_holiday_sync_date']) : __('not_synced_yet'); ?>
                                    </span>
                                </div>
                                <?php if ($isAdmin): ?>
                                <div id="holiday-action-buttons" class="flex items-center gap-3">
                                    <button type="button" disabled id="sync-holidays-btn" class="bg-blue-600/10 text-blue-400 border border-blue-600/20 px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 opacity-50 cursor-not-allowed">
                                        <span class="material-icons-round text-sm">sync</span>
                                        <span id="sync-btn-text"><?php echo __('update_holidays_now_btn'); ?></span>
                                    </button>
                                    <button type="button" disabled class="bg-primary/10 text-primary border border-primary/20 px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 opacity-50 cursor-not-allowed">
                                        <span class="material-icons-round text-sm">add</span>
                                        <?php echo __('add_custom_holiday_btn'); ?>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <label class="text-xs text-gray-400"><?php echo __('display_year_label'); ?></label>
                                <select id="holiday-year-filter" onchange="loadHolidays()" class="bg-dark/50 border border-white/10 text-white text-xs px-3 py-1.5 rounded-lg">
                                    <?php
                                    $curYear = (int)date('Y');
                                    for($y = $curYear; $y <= $curYear + 2; $y++) {
                                        $sel = ($y == $curYear) ? 'selected' : '';
                                        echo "<option value='$y' $sel>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <div id="online-status" class="flex items-center gap-2 text-[10px] text-gray-500 bg-white/5 px-3 py-1 rounded-full border border-white/5">
                                    <span class="material-icons-round text-xs">language</span>
                                    <span id="status-text"><?php echo __('status_checking'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-white/5">
                            <table class="w-full text-right border-collapse">
                                <thead class="hidden md:table-header-group">
                                    <tr class="bg-white/5 text-gray-400 text-xs uppercase tracking-wider">
                                        <th class="px-6 py-4 font-bold"><?php echo __('holiday_date_col'); ?></th>
                                        <th class="px-6 py-4 font-bold"><?php echo __('holiday_name_col'); ?></th>
                                        <th class="px-6 py-4 font-bold text-center"><?php echo __('adopt_holiday'); ?></th>
                                        <th class="px-6 py-4 font-bold text-center"><?php echo __('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="holidays-table-body" class="text-sm text-gray-300">
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4 p-4 bg-blue-500/5 border border-blue-500/10 rounded-xl flex items-start gap-3">
                            <span class="material-icons-round text-blue-400 text-sm mt-0.5">info</span>
                            <div class="flex-1">
                                <p class="text-[10px] text-gray-400 leading-relaxed mb-2">
                                    <?php echo __('religious_holidays_note'); ?>
                                </p>
                                <p class="text-[10px] text-gray-300 leading-relaxed pt-2 border-t border-blue-500/10">
                                    <span class="font-bold text-primary"><?php echo __('adopt_holiday'); ?>:</span> <?php echo __('adopt_holiday_explanation'); ?>
                                </p>
                            </div>
                        </div>
                        </div> <!-- end holidays-settings-content -->
                    </div>
                </div>

                <div id="tab-content-backup" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-8 border-b border-white/5 pb-4 gap-4">
                            <h3 class="text-xl font-bold text-white flex items-center gap-3">
                                <span class="material-icons-round text-primary">backup</span>
                                <?php echo __('backup_title'); ?>
                            </h3>
                            <button type="button" disabled id="btn-create-backup" class="w-full sm:w-auto bg-primary text-white px-4 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 opacity-50 cursor-not-allowed">
                                <span class="material-icons-round text-lg">add_circle</span>
                                <span><?php echo __('create_backup_btn'); ?></span>
                            </button>
                        </div>

                        <!-- Restore Section -->
                        <div class="bg-gradient-to-br from-indigo-500/10 to-indigo-600/10 border border-indigo-500/20 rounded-xl p-6 mb-8 relative overflow-hidden">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 relative z-10 gap-4">
                                <h4 class="text-base font-bold text-white flex items-center gap-2">
                                    <span class="material-icons-round text-indigo-400">cloud_upload</span>
                                    <?php echo __('restore_backup_title'); ?>
                                </h4>
                                <div class="flex gap-2 w-full sm:w-auto">
                                    <input type="file" id="restore-file-input" accept=".sql" class="hidden" onchange="handleRestoreUpload(this)">
                                    <button type="button" disabled class="w-full sm:w-auto bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 opacity-50 cursor-not-allowed">
                                        <span class="material-icons-round text-sm">upload_file</span>
                                        <?php echo __('upload_restore_btn'); ?>
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 mb-4 relative z-10"><?php echo __('restore_info_text'); ?></p>
                            
                            <!-- Progress Bar -->
                            <div id="restore-progress-container" class="hidden relative z-10 bg-dark/50 rounded-xl p-4 border border-white/5">
                                <div class="flex justify-between text-xs font-bold text-white mb-2">
                                    <span id="restore-status-text"><?php echo __('uploading'); ?></span>
                                    <span id="restore-percent">0%</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                                    <div id="restore-progress-bar" class="bg-indigo-500 h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Auto Backup Settings -->
                        <div class="bg-white/5 border border-white/5 rounded-xl p-6 mb-8">
                            <h4 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                                <span class="material-icons-round text-blue-400">schedule</span>
                                <?php echo __('auto_schedule_title'); ?>
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-sm text-gray-300"><?php echo __('enable_auto_backup_label'); ?></label>
                                        <div class="relative inline-block w-12 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" id="backup_enabled" class="toggle-checkbox" <?php echo ($settings['backup_enabled'] ?? '0') === '1' ? 'checked' : ''; ?> />
                                            <label for="backup_enabled" class="toggle-label block overflow-hidden h-6 rounded-full cursor-not-allowed pointer-events-none"></label>
                                        </div>
                                    </div>
                                    <label class="block text-xs font-bold text-gray-400 mb-2 mt-4"><?php echo __('backup_frequency_label'); ?></label>
                                    <select id="backup_frequency" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                                        <option value="daily" <?php echo ($settings['backup_frequency'] ?? 'daily') === 'daily' ? 'selected' : ''; ?>><?php echo __('daily_frequency'); ?></option>
                                        <option value="weekly" <?php echo ($settings['backup_frequency'] ?? 'daily') === 'weekly' ? 'selected' : ''; ?>><?php echo __('weekly_frequency'); ?></option>
                                        <option value="monthly" <?php echo ($settings['backup_frequency'] ?? 'daily') === 'monthly' ? 'selected' : ''; ?>><?php echo __('monthly_frequency'); ?></option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <button type="button" disabled id="btn-save-backup-settings" class="w-full bg-white/5 text-gray-300 border border-white/10 px-4 py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2 opacity-50 cursor-not-allowed">
                                        <span class="material-icons-round text-sm">save</span>
                                        <span><?php echo __('save_schedule_btn'); ?></span>
                                    </button>
                                    <p class="text-[10px] text-gray-500 text-center">
                                        <?php echo __('last_auto_backup_label'); ?> <span class="text-primary dir-ltr"><?php echo !empty($settings['last_backup_run']) ? $settings['last_backup_run'] : __('not_done_yet'); ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Backup List -->
                        <h4 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                            <span class="material-icons-round text-green-400">history</span>
                            <?php echo __('backup_history_title'); ?>
                        </h4>
                        <div class="overflow-hidden rounded-xl border border-white/5">
                            <table class="w-full text-right border-collapse">
                                <thead class="hidden md:table-header-group">
                                    <tr class="bg-white/5 text-gray-400 text-xs uppercase tracking-wider">
                                        <th class="px-6 py-4 font-bold text-right"><?php echo __('filename_col'); ?></th>
                                        <th class="px-6 py-4 font-bold text-right"><?php echo __('date_label'); ?></th>
                                        <th class="px-6 py-4 font-bold text-center"><?php echo __('size_col'); ?></th>
                                        <th class="px-6 py-4 font-bold text-center"><?php echo __('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="backups-table-body" class="text-sm text-gray-300">
                                    <!-- Dynamic content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="tab-content-reset" class="tab-content hidden space-y-6 max-w-4xl mx-auto animate-fade-in">
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 lg:p-8 glass-panel">
                        <div class="text-center mb-8">
                            <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="material-icons-round text-red-500 text-3xl">warning</span>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2"><?php echo __('system_reset_title'); ?></h3>
                            <p class="text-gray-400"><?php echo __('system_reset_subtitle'); ?></p>
                        </div>

                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-6 mb-6">
                            <h4 class="text-lg font-bold text-red-400 mb-4 flex items-center gap-2">
                                <span class="material-icons-round">error_outline</span>
                                <?php echo __('important_warning_title'); ?>
                            </h4>
                            <ul class="text-sm text-gray-300 space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-red-500 text-sm mt-0.5">cancel</span>
                                    <?php echo __('reset_warning_detail'); ?>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-6 mb-6">
                            <h4 class="text-lg font-bold text-yellow-400 mb-4 flex items-center gap-2">
                                <span class="material-icons-round">info</span>
                                <?php echo __('what_happens_reset_title'); ?>
                            </h4>
                            <ul class="text-sm text-gray-300 space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="material-icons-round text-yellow-500 text-sm mt-0.5">check_circle</span>
                                    <?php echo __('system_reset_subtitle'); ?>
                                </li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <button type="button" disabled class="bg-red-500 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg shadow-red-500/20 transition-all flex items-center gap-3 mx-auto opacity-50 cursor-not-allowed">
                                <span class="material-icons-round">restart_alt</span>
                                <?php echo __('reset_system_btn'); ?>
                            </button>
                            <p class="text-xs text-gray-500 mt-4"><?php echo __('reset_confirm_hint'); ?></p>
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
    // Language Switching Logic
    async function setSystemLanguage(lang) {
        if (!await showConfirmModal(window.__('confirm_action'), window.__('confirm_language_change'))) return;
        
        showLoadingOverlay();
        
        try {
            // 1. Update Database
            const res = await fetch('api.php?action=updateSetting', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    settings: [
                        { name: 'system_language', value: lang }
                    ]
                })
            });
            
            const data = await res.json();
            
            if (data.success) {
                // 2. Set Cookie (to ensure immediate effect)
                document.cookie = "lang=" + lang + "; path=/; max-age=" + (86400 * 30);
                
                // 3. Reload Page
                window.location.href = 'settings.php?tab=language&success=' + encodeURIComponent(window.__('settings_updated_success'));
            } else {
                hideLoadingOverlay();
                showToast(window.__('error') + ': ' + data.message, false);
            }
        } catch (e) {
            hideLoadingOverlay();
            showToast(window.__('server_connection_error'), false);
        }
    }

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
        if(tabName === 'workdays') loadHolidays();
        if(tabName === 'backup') loadBackups();
    }

    // Backup Functions
    async function loadBackups() {
        const tbody = document.getElementById('backups-table-body');
        if(!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">' + window.__('loading_text') + '</td></tr>';
        
        try {
            const res = await fetch('api.php?action=getBackups');
            const data = await res.json();
            if (data.success && data.data.length > 0) {
                let html = '';
                data.data.forEach(b => {
                    // Convert bytes to human readable
                    let size = b.size;
                    let unit = 'B';
                    if(size > 1024) { size = size / 1024; unit = 'KB'; }
                    if(size > 1024) { size = size / 1024; unit = 'MB'; }
                    
                    html += `
                    <tr class="block md:table-row hover:bg-white/[0.02] border-b border-white/5 last:border-0 p-4 mb-4 md:mb-0 bg-white/[0.02] md:bg-transparent rounded-xl md:rounded-none">
                        <td class="px-2 py-2 md:px-6 md:py-4 font-mono text-xs text-primary block md:table-cell flex justify-between items-center md:block">
                            <span class="md:hidden text-gray-400 font-bold">${window.__('filename_col')}</span>
                            <span class="dir-ltr text-right block w-full">${b.name}</span>
                        </td>
                        <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-white block md:table-cell flex justify-between items-center md:block">
                            <span class="md:hidden text-gray-400 font-bold">${window.__('date_label')}</span>
                            <span class="dir-ltr text-right block w-full">${b.date}</span>
                        </td>
                        <td class="px-2 py-2 md:px-6 md:py-4 text-gray-400 block md:table-cell flex justify-between items-center md:block md:text-center">
                            <span class="md:hidden text-gray-400 font-bold">${window.__('size_col')}</span>
                            <span class="dir-ltr block md:inline">${size.toFixed(2)} ${unit}</span>
                        </td>
                        <td class="px-2 py-2 md:px-6 md:py-4 text-center block md:table-cell flex justify-between items-center md:block md:justify-center">
                            <span class="md:hidden text-gray-400 font-bold">${window.__('actions')}</span>
                            <div class="flex justify-center gap-2 opacity-50 cursor-not-allowed pointer-events-none">
                                <a href="#" class="p-2 bg-blue-500/10 text-blue-400 rounded-lg transition-colors" title="${window.__('download_pdf')}">
                                    <span class="material-icons-round text-sm">download</span>
                                </a>
                                <button type="button" disabled class="p-2 bg-indigo-500/10 text-indigo-400 rounded-lg transition-colors" title="${window.__('restore_backup_title')}">
                                    <span class="material-icons-round text-sm">settings_backup_restore</span>
                                </button>
                                <button type="button" disabled class="p-2 bg-red-500/10 text-red-400 rounded-lg transition-colors" title="${window.__('delete')}">
                                    <span class="material-icons-round text-sm">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">' + window.__('no_backups_found') + '</td></tr>';
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-red-400">' + window.__('failed_loading_data') + '</td></tr>';
        }
    }

    async function createBackup() {
        if (!await showConfirmModal(window.__('confirm_action'), window.__('confirm_action'))) return;
        
        const btn = document.getElementById('btn-create-backup');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons-round animate-spin text-lg">sync</span> ' + window.__('creating_backup_text');
        
        try {
            const res = await fetch('api.php?action=createBackup');
            const data = await res.json();
            if (data.success) {
                showToast(window.__('backup_created_success'), true);
                loadBackups();
            } else {
                showToast(window.__('error') + ': ' + data.message, false);
            }
        } catch (e) {
            showToast(window.__('server_connection_error'), false);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round text-lg">add_circle</span> <span>' + window.__('create_backup_btn') + '</span>';
        }
    }

    async function deleteBackup(filename) {
        if (!await showConfirmModal(window.__('confirm_action'), window.__('delete_backup_confirm'))) return;
        
        try {
            const res = await fetch('api.php?action=deleteBackup', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ filename })
            });
            const data = await res.json();
            if (data.success) {
                loadBackups();
            } else {
                showToast(window.__('error') + ': ' + data.message, false);
            }
        } catch (e) {
            showToast(window.__('server_connection_error'), false);
        }
    }
    
    async function saveBackupSettings() {
        const enabled = document.getElementById('backup_enabled').checked ? '1' : '0';
        const frequency = document.getElementById('backup_frequency').value;
        const btn = document.getElementById('btn-save-backup-settings');
        
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons-round animate-spin text-sm">sync</span> ' + window.__('saving_text');

        try {
            const res = await fetch('api.php?action=updateSetting', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    settings: [
                        { name: 'backup_enabled', value: enabled },
                        { name: 'backup_frequency', value: frequency }
                    ]
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast(window.__('settings_updated_success'), true);
            } else {
                showToast(window.__('error') + ': ' + data.message, false);
            }
        } catch (e) {
            showToast(window.__('server_connection_error'), false);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round text-sm">save</span> <span>' + window.__('save_schedule_btn') + '</span>';
        }
    }

    // Restore Functions
    function handleRestoreUpload(input) {
        if (input.files && input.files[0]) {
            startRestoreProcess(input.files[0]);
        }
    }

    async function restoreFromList(filename) {
        if (!await showConfirmModal(window.__('confirm_action'), window.__('restore_confirm_start') + filename + '?\n' + window.__('restore_warning_end'))) return;
        startRestoreProcess(null, filename);
    }

    function startRestoreProcess(file = null, filename = null) {
        const container = document.getElementById('restore-progress-container');
        const progressBar = document.getElementById('restore-progress-bar');
        const statusText = document.getElementById('restore-status-text');
        const percentText = document.getElementById('restore-percent');
        
        container.classList.remove('hidden');
        progressBar.style.width = '0%';
        percentText.innerText = '0%';
        statusText.innerText = file ? window.__('uploading') : window.__('processing');

        // Helper to update polling
        let pollInterval;
        const startPolling = () => {
            pollInterval = setInterval(async () => {
                try {
                    const res = await fetch('api.php?action=getRestoreProgress');
                    const data = await res.json();
                    if (data.success) {
                        progressBar.style.width = data.percent + '%';
                        percentText.innerText = data.percent + '%';
                        statusText.innerText = data.status;
                        
                        if (data.percent >= 100 && (data.status.includes('نجاح') || data.status.includes('success') || data.status.includes('خطأ') || data.status.includes('error'))) {
                            clearInterval(pollInterval);
                            setTimeout(() => {
                                container.classList.add('hidden');
                                // Reload with success message to show toast after reload
                                window.location.href = 'settings.php?tab=backup&success=' + encodeURIComponent(data.status);
                            }, 1000);
                        }
                    }
                } catch (e) {
                    console.error('Polling error', e);
                }
            }, 1000);
        };

        if (file) {
            // Upload with progress
            const formData = new FormData();
            formData.append('backup_file', file);
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'api.php?action=restoreBackup', true);
            
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 50; // Upload is first 50% visually
                    progressBar.style.width = percentComplete + '%';
                    percentText.innerText = Math.round(percentComplete) + '%';
                }
            };
            
            xhr.onload = () => {
                if (xhr.status === 200) {
                    // Upload done, server processing started
                    startPolling();
                } else {
                    showToast(window.__('upload_failed_text'), false);
                    container.classList.add('hidden');
                }
            };
            
            xhr.onerror = () => {
                showToast(window.__('server_connection_error'), false);
                container.classList.add('hidden');
            };
            
            xhr.send(formData);
        } else {
            // Restore existing file
            fetch('api.php?action=restoreBackup', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ filename })
            }).then(() => startPolling())
              .catch(() => {
                  showToast(window.__('action_failed'), false);
                  container.classList.add('hidden');
              });
        }
    }

    async function loadHolidays() {
        const year = document.getElementById('holiday-year-filter').value;
        const tbody = document.getElementById('holidays-table-body');
        if(!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">' + window.__('loading_text') + '</td></tr>';
        
        try {
            const res = await fetch(`api.php?action=getHolidays&year=${year}`);
            const data = await res.json();
            if (data.success && data.data.length > 0) {
                let html = '';
                data.data.forEach(h => {
                    const isChecked = h.is_active == 1 ? 'checked' : '';
                    html += `
                    <tr class="block md:table-row hover:bg-white/[0.02] border-b border-white/5 last:border-0 p-4 mb-4 md:mb-0 bg-white/[0.02] md:bg-transparent rounded-xl md:rounded-none">
                        <td class="px-2 py-2 md:px-6 md:py-4 font-mono text-xs text-primary block md:table-cell flex justify-between items-center md:block">
                            <span class="md:hidden text-gray-400 font-bold">${window.__('holiday_date_col')}</span>
                            <span>${h.date}</span>
                        </td>
                        <td class="px-2 py-2 md:px-6 md:py-4 font-bold text-white block md:table-cell flex justify-between items-center md:block">
                            <span class="md:hidden text-gray-400 font-bold">${window.__('holiday_name_col')}</span>
                            <span>${h.name}</span>
                        </td>
                        <td class="px-2 py-2 md:px-6 md:py-4 text-center block md:table-cell flex justify-between items-center md:block md:justify-center">
                            <span class="md:hidden text-gray-400 font-bold">${window.__('adopt_holiday')}</span>
                            <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" id="holiday-toggle-${h.id}" 
                                    class="toggle-checkbox" 
                                    ${isChecked} 
                                    onchange="toggleHolidayStatus(${h.id}, this)">
                                <label for="holiday-toggle-${h.id}" class="toggle-label block overflow-hidden h-5 rounded-full cursor-not-allowed pointer-events-none"></label>
                            </div>
                        </td>
                        <td class="px-2 py-2 md:px-6 md:py-4 text-center block md:table-cell flex justify-between items-center md:block md:justify-center">
                            <span class="md:hidden text-gray-400 font-bold">${window.__('actions')}</span>
                            <div class="flex gap-2 justify-end md:justify-center opacity-50 cursor-not-allowed pointer-events-none">
                                <button type="button" disabled class="p-2 bg-blue-500/10 text-blue-400 rounded-lg transition-colors">
                                    <span class="material-icons-round text-sm">edit</span>
                                </button>
                                <button type="button" disabled class="p-2 bg-red-500/10 text-red-400 rounded-lg transition-colors">
                                    <span class="material-icons-round text-sm">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">' + window.__('no_holidays_found') + '</td></tr>';
            }
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-red-400">' + window.__('failed_loading_data') + '</td></tr>';
        }
    }

    async function toggleHolidayStatus(id, checkbox) {
        const isActive = checkbox.checked ? 1 : 0;
        try {
            const res = await fetch('api.php?action=toggleHolidayActive', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, is_active: isActive })
            });
            const data = await res.json();
            if (data.success) {
                showToast(window.__('action_success'), true);
            } else {
                checkbox.checked = !checkbox.checked; 
                showToast(data.message || window.__('action_failed'), false);
            }
        } catch (e) {
            checkbox.checked = !checkbox.checked;
            showToast(window.__('server_connection_error'), false);
        }
    }

    async function syncMoroccanHolidays() {
        await updateOnlineStatus();
        const statusText = document.getElementById('status-text');
        if (statusText && statusText.innerText.includes(window.__('status_offline'))) {
             showToast(window.__('server_connection_error'), false); // Reuse for offline
             return;
        }
        const year = document.getElementById('holiday-year-filter').value;
        const btn = document.getElementById('sync-holidays-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons-round animate-spin text-sm">sync</span> ' + window.__('processing');
        
        try {
            const res = await fetch(`api.php?action=syncMoroccanHolidays&year=${year}`);
            const data = await res.json();
            if (data.success) {
                showToast(window.__('sync_holidays_success').replace('%d', data.count), true);
                loadHolidays();
                if(data.last_sync) document.getElementById('last-sync-date').innerText = data.last_sync;
            } else {
                showToast(window.__('action_failed') + ': ' + data.message, false);
            }
        } catch (e) {
            showToast(window.__('server_connection_error'), false);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round text-sm">sync</span> ' + window.__('update_holidays_now_btn');
        }
    }

    function openHolidayModal(id = '', name = '', date = '') {
        const modal = document.getElementById('holidayModal');
        document.getElementById('holiday-id').value = id;
        document.getElementById('holiday-name').value = name;
        document.getElementById('holiday-date').value = date;
        modal.classList.remove('hidden');
        setTimeout(() => {
             document.getElementById('holidayModalBackdrop').classList.remove('opacity-0');
             document.getElementById('holidayModalContent').classList.remove('opacity-0', 'scale-95');
        }, 10);
    }

    function closeHolidayModal() {
        document.getElementById('holidayModalBackdrop').classList.add('opacity-0');
        document.getElementById('holidayModalContent').classList.add('opacity-0', 'scale-95');
        setTimeout(() => document.getElementById('holidayModal').classList.add('hidden'), 300);
    }

    async function saveHoliday() {
        const id = document.getElementById('holiday-id').value;
        const name = document.getElementById('holiday-name').value;
        const date = document.getElementById('holiday-date').value;
        if (!name || !date) return showToast(window.__('fill_all_fields_correctly'), false);

        const action = id ? 'updateHoliday' : 'addHoliday';
        const body = id ? { id, name, date } : { name, date };

        try {
            const res = await fetch(`api.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
            const data = await res.json();
            if (data.success) {
                closeHolidayModal();
                loadHolidays();
                showToast(window.__('action_success'), true);
            } else {
                showToast(data.message, false);
            }
        } catch (e) {
            showToast(window.__('server_connection_error'), false);
        }
    }

    async function deleteHoliday(id) {
        if (!await showConfirmModal(window.__('confirm_action'), window.__('delete_holiday_confirm'))) return;
        try {
            const res = await fetch('api.php?action=deleteHoliday', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            const data = await res.json();
            if (data.success) {
                loadHolidays();
                showToast(window.__('action_success'), true);
            }
        } catch (e) {
            showToast(window.__('server_connection_error'), false);
        }
    }

    function toggleKeyboardSettings(checkbox) {
         const content = document.getElementById('keyboard-settings-content');
         if (!checkbox.checked) {
             content.classList.add('opacity-50', 'pointer-events-none', 'filter', 'blur-sm');
         } else {
             content.classList.remove('opacity-50', 'pointer-events-none', 'filter', 'blur-sm');
         }
    }

    // Online status check for holidays sync
    async function updateOnlineStatus() {
        const statusText = document.getElementById('status-text');
        if (!statusText) return;

        if (!navigator.onLine) {
            statusText.innerText = window.__('status_offline');
            statusText.className = 'text-red-500';
            return;
        }

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 4000);
            
            await fetch('https://api.aladhan.com/v1/hToG/01-01-1445', { 
                method: 'HEAD',
                mode: 'no-cors',
                signal: controller.signal 
            });
            
            clearTimeout(timeoutId);
            statusText.innerText = window.__('status_online');
            statusText.className = 'text-green-500';
        } catch (e) {
            statusText.innerText = window.__('status_local');
            statusText.className = 'text-orange-500';
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

        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();

        if (initialTab === 'workdays') loadHolidays();
    });

</script>

<?php if ($isAdmin): ?>
<script>
    // ... (Same admin scripts) ...
    // ... (Reset Delivery, Toggle Rental, Notifications logic) ...
    async function resetDeliveryPrices() {
        if (await showConfirmModal(window.__('confirm_action'), window.__('confirm_action'))) {
            const insideCity = document.getElementById('deliveryInsideCity');
            const outsideCity = document.getElementById('deliveryOutsideCity');
            insideCity.value = '20'; outsideCity.value = '40';
            [insideCity, outsideCity].forEach(el => { el.style.transition = 'all 0.3s'; el.style.color = '#10b981'; setTimeout(() => el.style.color = '', 1000); });
        }
    }
    
    async function toggleRentalSettings(checkbox) {
        const content = document.getElementById('rental-settings-content');
        if (!checkbox.checked) {
            if (await showConfirmModal(window.__('confirm_action'), '⚠️ ' + window.__('are_you_sure'))) {
                // Removed blur classes
            } else {
                checkbox.checked = true;
            }
        }
    }

    async function toggleTaxSettings(checkbox) {
        const content = document.getElementById('tax-settings-container');
        if (!checkbox.checked) {
            if (await showConfirmModal(window.__('confirm_action'), '⚠️ ' + window.__('are_you_sure'))) {
                // Removed blur classes
            } else {
                checkbox.checked = true;
            }
        }
    }

    async function toggleHolidaysSettings(checkbox) {
        const content = document.getElementById('holidays-settings-content');
        if (!checkbox.checked) {
            if (await showConfirmModal(window.__('confirm_action'), '⚠️ ' + window.__('are_you_sure'))) {
                // Removed blur classes
            } else {
                checkbox.checked = true;
            }
        }
    }

    async function toggleWorkDaysSettings(checkbox) {
        const content = document.getElementById('work-days-settings-content');
        if (!checkbox.checked) {
            if (await showConfirmModal(window.__('confirm_action'), '⚠️ ' + window.__('are_you_sure'))) {
                // Removed blur classes
            } else {
                checkbox.checked = true;
            }
        }
    }

    async function toggleEndDaySettings(checkbox) {
        const content = document.getElementById('end-day-settings-content');
        if (!checkbox.checked) {
            // Removed blur classes
        }
    }

    async function handleStockAlertToggle(checkbox) {
        const container = document.getElementById('stock-alerts-settings');
        if (!checkbox.checked) {
            if (await showConfirmModal(window.__('confirm_action'), `⚠️ ` + window.__('are_you_sure'))) {
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
                notifButton.innerHTML = `<span class="material-icons-round text-sm">check_circle</span><span>` + window.__('windows_notifications_enabled') + `</span>`;
                notifButton.className = "w-full bg-green-500/10 text-green-500 border border-green-500/20 px-4 py-3 rounded-xl font-bold flex items-center justify-center gap-2 cursor-default";
                notifButton.disabled = true;
            } else if (Notification.permission === 'denied') {
                notifButton.innerHTML = `<span class="material-icons-round text-sm">block</span><span>` + window.__('notifications_blocked_browser') + `</span>`;
                notifButton.className = "w-full bg-red-500/10 text-red-500 border border-red-500/20 px-4 py-3 rounded-xl font-bold flex items-center justify-center gap-2 cursor-not-allowed";
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Rental Paid Button Logic
        const btn = document.getElementById('btn-rental-paid');
        if (btn) {
            btn.addEventListener('click', async function() {
                if (!await showConfirmModal(window.__('confirm_action'), window.__('confirm_rental_payment_alert'))) return;
                try {
                    btn.disabled = true;
                    btn.classList.add('opacity-70');
                    const res = await fetch('api.php?action=markRentalPaidThisMonth', { method: 'POST' });
                    const data = await res.json();
                    if (data.success) {
                        const dateInput = document.querySelector('input[name="rentalPaymentDate"]');
                        if (dateInput && data.next_payment_date) dateInput.value = data.next_payment_date;
                        localStorage.removeItem('rental_notify_day');
                        showToast(window.__('rental_paid_success_msg'), true);
                    } else {
                        showToast('❌ ' + (data.message || window.__('action_failed')), false);
                    }
                } catch (e) {
                    showToast(window.__('server_connection_error'), false);
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

    function formatTimeInput(input) {
        let v = input.value.replace(/[^0-9]/g, '');
        if (v.length > 4) v = v.slice(0, 4);
        
        // Hours validation
        if (v.length >= 2) {
            let h = parseInt(v.slice(0, 2));
            if (h > 23) { v = '23' + v.slice(2); }
        }

        // Minutes validation
        if (v.length >= 4) {
             let m = parseInt(v.slice(2, 4));
             if (m > 59) { v = v.slice(0, 2) + '59'; }
        }

        if (v.length > 2) {
            input.value = v.slice(0, 2) + ':' + v.slice(2);
        } else {
            input.value = v;
        }
    }
</script>

<div id="rental-payments-modal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="rentalPaymentsBackdrop" onclick="closeRentalPaymentsModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-[#1e1e2e] border border-white/10 rounded-2xl w-full max-w-3xl transform scale-95 opacity-0 transition-all duration-300 relative shadow-2xl overflow-hidden flex flex-col max-h-[85vh]" id="rentalPaymentsContent">
            <div class="px-6 py-4 bg-white/5 border-b border-white/5 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-primary">receipt_long</span>
                    <?php echo __('rental_management_title'); ?> - <?php echo __('view_payment_history_btn'); ?>
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
                    <?php echo __('stock_guide_title'); ?>
                </h3>
                <button onclick="closeStockGuideModal()" class="text-gray-400 hover:text-white p-1 hover:bg-white/10 rounded-lg transition-colors"><span class="material-icons-round">close</span></button>
            </div>
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h5 class="text-sm font-bold text-gray-400 mb-3 border-b border-white/5 pb-2"><?php echo __('stock_guide_examples'); ?></h5>
                        <div class="space-y-3">
                            <div class="bg-white/5 p-3 rounded-xl border border-white/5">
                                <div class="flex justify-between text-white font-bold text-sm mb-1"><span><?php echo __('stock_guide_small_shops'); ?></span><span class="text-primary">30 <?php echo __('time_mins_short'); ?></span></div>
                                <p class="text-xs text-gray-500"><?php echo __('stock_guide_small_desc'); ?></p>
                            </div>
                            <div class="bg-white/5 p-3 rounded-xl border border-white/5">
                                <div class="flex justify-between text-white font-bold text-sm mb-1"><span><?php echo __('stock_guide_supermarket'); ?></span><span class="text-primary">60 <?php echo __('time_mins_short'); ?></span></div>
                                <p class="text-xs text-gray-500"><?php echo __('stock_guide_supermarket_desc'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div>
                         <h5 class="text-sm font-bold text-gray-400 mb-3 border-b border-white/5 pb-2"><?php echo __('stock_guide_tips_title'); ?></h5>
                         <ul class="space-y-2 text-xs text-gray-400 list-disc mr-4">
                             <li><?php echo __('stock_guide_tip_1'); ?></li>
                             <li><?php echo __('stock_guide_tip_2'); ?></li>
                             <li><?php echo __('stock_guide_tip_3'); ?></li>
                         </ul>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-white/5 flex justify-end shrink-0">
                <button onclick="closeStockGuideModal()" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold"><?php echo __('understood_thanks'); ?></button>
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
                    const newImageUrl = result.logoUrl + '?t=' + new Date().getTime();
                    previewContainer.innerHTML = `<img src="${newImageUrl}" alt="Logo" class="w-full h-full object-cover">`;
                                        
                    // Reload page to show generated favicon or update favicon preview specifically if we want to be fancy
                    // For now, let's just show success
                    showToast(window.__('logo_updated_success'), true);
                    
                    // Update favicon preview if it exists
                    const faviconContainer = document.querySelector('.w-16.h-16.rounded-xl');
                    if (faviconContainer) {
                         // We assume favicon.png is generated
                         const faviconUrl = 'src/uploads/favicon.png?t=' + new Date().getTime();
                         faviconContainer.innerHTML = `<img src="${faviconUrl}" alt="Favicon" class="w-full h-full object-contain p-2">`;
                    }

                    // Update sidebar logo
                    const sidebarLogo = document.querySelector('.sidebar-logo');
                    if(sidebarLogo) sidebarLogo.src = newImageUrl;
                                                             
                    const invoiceCheckboxContainer = document.querySelector('.invoice-logo-checkbox');
                    if(invoiceCheckboxContainer) {
                        invoiceCheckboxContainer.classList.remove('hidden');
                        setTimeout(() => {
                            invoiceCheckboxContainer.style.opacity = '0';
                            invoiceCheckboxContainer.style.transition = 'opacity 0.3s ease-in-out';
                            setTimeout(() => {
                                invoiceCheckboxContainer.style.opacity = '1';
                            }, 10);
                        }, 10);
                    }

                    // Show delete button
                    const deleteBtn = document.getElementById('btn-delete-logo');
                    if(deleteBtn) deleteBtn.classList.remove('hidden');
                    
                } else {
                    previewContainer.innerHTML = originalContent;
                    showToast(result.message || window.__('action_failed'), false);
                }
            } catch (error) {
                previewContainer.innerHTML = originalContent;
                showToast(window.__('server_connection_error'), false);
            }
        }
    });

    // === End Logo/Favicon Code ===

    async function deleteShopLogo() {
        if (!await showConfirmModal(window.__('confirm_action'), window.__('delete_logo_confirm'))) return;
        
        showLoadingOverlay();

        try {
            const res = await fetch('api.php?action=deleteShopLogo');
            const data = await res.json();
            
            if (data.success) {
                // Update Logo UI
                const previewContainer = document.querySelector('.w-32.h-32.rounded-full');
                previewContainer.innerHTML = '<span class="material-icons-round text-5xl text-gray-500 group-hover:scale-110 transition-transform duration-300">add_a_photo</span>';
                
                // Update Favicon UI (if it exists)
                const faviconContainer = document.querySelector('.w-16.h-16.rounded-xl');
                if (faviconContainer) {
                    faviconContainer.innerHTML = '<span class="material-icons-round text-3xl text-gray-500">api</span>';
                }

                // Hide delete button
                const deleteBtn = document.getElementById('btn-delete-logo');
                if(deleteBtn) deleteBtn.classList.add('hidden');
                
                // Hide invoice logo checkbox
                const invoiceCheckboxContainer = document.querySelector('.invoice-logo-checkbox');
                if(invoiceCheckboxContainer) invoiceCheckboxContainer.classList.add('hidden');

                // Update sidebar logo if exists to default (optional, usually hard to revert to default without reload, but let's try hiding it or setting to transparent)
                // Actually if deleted, sidebar logo might be broken image. 
                // Best is to reload or set to default placeholder? 
                // The settings page logo is main concern.
                
                showToast(window.__('logo_deleted_success'), true);
            } else {
                showToast(data.message || window.__('delete_logo_fail'), false);
            }
        } catch (e) {
            showToast(window.__('server_connection_error'), false);
        } finally {
            hideLoadingOverlay();
        }
    }

    // Load Rental Data Logic (Simplified for brevity, same as original logic)
    function formatArDate(d) {
        return new Date(d).toLocaleDateString('<?php echo get_locale(); ?>', { year: 'numeric', month: 'long', day: 'numeric' });
    }
    async function loadRentalPayments(page = 1) {
        const table = document.getElementById('rentalPaymentsTable');
        const pag = document.getElementById('rentalPaymentsPagination');
        table.innerHTML = '<div class="text-center py-8 text-gray-500">' + window.__('loading_text') + '</div>';
        try {
            const res = await fetch('api.php?action=getRentalPayments&page='+page+'&limit=20');
            const data = await res.json();
            if(!data.success || !data.data.length) {
                table.innerHTML = '<div class="text-center py-8 text-gray-500">' + window.__('no_info') + '</div>';
                pag.innerHTML = '';
                return;
            }
            let html = '';
            data.data.forEach(r => {
                html += `
                <div class="bg-white/5 border border-white/10 rounded-xl p-4 flex justify-between items-center">
                    <div>
                        <div class="font-bold text-white text-sm">` + window.__('month') + `: ${r.paid_month}</div>
                        <div class="text-xs text-gray-400">${formatArDate(r.payment_date)}</div>
                    </div>
                    <div class="text-left">
                         <div class="font-bold text-primary">${parseFloat(r.amount).toFixed(2)} ${r.currency}</div>
                         <div class="text-[10px] text-gray-500">${r.rental_type == 'yearly' ? window.__('yearly_payment') : window.__('monthly_payment')}</div>
                    </div>
                </div>`;
            });
            table.innerHTML = html;
            // Add simple pagination if needed based on data.pagination
        } catch(e) {
            table.innerHTML = '<div class="text-center text-red-500">' + window.__('failed_loading_data') + '</div>';
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
                // Keep save button disabled regardless of changes
                saveBtn.disabled = true;
                // saveBtn.className = "bg-primary hover:bg-primary-hover text-white px-4 md:px-8 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 flex items-center gap-2 cursor-not-allowed pointer-events-none";
                alertDiv.classList.remove('hidden');
            }
        }

        // Function to disable save button and hide alert
        function disableSave() {
            hasChanges = false;
            saveBtn.disabled = true;
            saveBtn.className = "bg-gray-500/50 text-gray-400 px-4 md:px-8 py-2.5 rounded-xl font-bold shadow-lg transition-all flex items-center gap-2 cursor-not-allowed";
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
                    <?php echo __('confirm_reset_modal_title'); ?>
                </h3>
                <button onclick="closeResetModal()" class="text-gray-400 hover:text-white p-1 hover:bg-white/10 rounded-lg transition-colors"><span class="material-icons-round">close</span></button>
            </div>
            <div class="p-6 text-center">
                <div class="w-12 h-12 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-icons-round text-red-500 text-2xl">restart_alt</span>
                </div>
                <p class="text-white text-lg font-bold mb-2"><?php echo __('are_you_sure_reset'); ?></p>
                <p class="text-gray-400 text-sm mb-6"><?php echo __('reset_warning_detail'); ?></p>
                <div class="flex gap-3">
                    <button onclick="closeResetModal()" class="flex-1 bg-gray-500/20 hover:bg-gray-500/30 text-gray-300 border border-gray-500/30 px-4 py-3 rounded-xl font-bold transition-all"><?php echo __('cancel_btn'); ?></button>
                    <button onclick="confirmReset()" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-3 rounded-xl font-bold transition-all shadow-lg shadow-red-500/20"><?php echo __('confirm_reset_btn'); ?></button>
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
    function showLoadingOverlay(message = window.__('loading_text')) {
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
            <h3 class="text-lg font-bold text-white mb-2"><?php echo __('loading_text'); ?></h3>
            <p id="loading-message" class="text-sm text-gray-400">...</p>
        </div>
    </div>
</div>

<div id="holidayModal" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" id="holidayModalBackdrop" onclick="closeHolidayModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-[#1e1e2e] border border-white/10 rounded-2xl w-full max-w-md transform scale-95 opacity-0 transition-all duration-300 relative shadow-2xl overflow-hidden flex flex-col" id="holidayModalContent">
            <div class="px-6 py-4 bg-white/5 border-b border-white/5 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-primary">edit_calendar</span>
                    <?php echo __('edit_holiday_title'); ?>
                </h3>
                <button onclick="closeHolidayModal()" class="text-gray-400 hover:text-white p-1 hover:bg-white/10 rounded-lg transition-colors"><span class="material-icons-round">close</span></button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="holiday-id">
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-2 mr-1"><?php echo __('holiday_name_col'); ?></label>
                    <input type="text" id="holiday-name" placeholder="Ex: Eid..." class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 mb-2 mr-1"><?php echo __('holiday_date_col'); ?></label>
                    <input type="date" id="holiday-date" class="w-full bg-dark/50 border border-white/10 text-white text-right px-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all" style="color-scheme: dark;">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeHolidayModal()" class="flex-1 bg-gray-500/20 hover:bg-gray-500/30 text-gray-300 px-4 py-3 rounded-xl font-bold transition-all"><?php echo __('cancel_btn'); ?></button>
                    <button type="button" onclick="saveHoliday()" class="flex-1 bg-primary hover:bg-primary-hover text-white px-4 py-3 rounded-xl font-bold transition-all shadow-lg shadow-primary/20"><?php echo __('save_holiday_btn'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'src/footer.php'; ?>