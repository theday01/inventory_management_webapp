<?php
// إضافة هذه الأسطر في بداية الملف قبل أي كود آخر
session_start();
require_once __DIR__ . '/src/language.php';

// منع أي output قبل JSON
ob_start();

// إخفاء رسائل الأخطاء من الظهور في JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// التأكد من عدم وجود BOM في بداية الملف
if (ob_get_length()) ob_clean();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => __('login_required')]);
    exit;
}

header('Content-Type: application/json');
require_once 'db.php';
require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'getCategories':
        getCategories($conn);
        break;
    case 'addCategory':
        addCategory($conn);
        break;
    case 'updateCategory':
        updateCategory($conn);
        break;
    case 'deleteCategory':
        deleteCategory($conn);
        break;
    case 'getProducts':
        getProducts($conn);
        break;
    case 'exportProductsExcel':
        exportProductsExcel($conn);
        break;
    case 'addProduct':
        addProduct($conn);
        break;
    case 'updateProduct':
        updateProduct($conn);
        break;
    case 'bulkAddProducts':
        bulkAddProducts($conn);
        break;
    case 'importProducts':
        importProducts($conn);
        break;
    case 'getProductDetails':
        getProductDetails($conn);
        break;
    case 'getCategoryFields':
        getCategoryFields($conn);
        break;
    case 'getCustomers':
        getCustomers($conn);
        break;
    case 'addCustomer':
        addCustomer($conn);
        break;
    case 'getCustomerDetails':
        getCustomerDetails($conn);
        break;
    case 'getLowStockProducts':
        getLowStockProducts($conn);
        break;
    case 'updateCustomer':
        updateCustomer($conn);
        break;
    case 'exportCustomersExcel':
        exportCustomersExcel($conn);
        break;
    case 'createInvoice':
        createInvoice($conn);
        break;
    case 'getInvoice':
        getInvoice($conn);
        break;
    case 'getInvoices':
        getInvoices($conn);
        break;
    case 'getDeliverySettings':
        getDeliverySettings($conn);
        break;
    case 'updateDeliverySettings':
        updateDeliverySettings($conn);
        break;
    case 'getDashboardStats':
        getDashboardStats($conn);
        break;
    case 'getSalesChart':
        getSalesChart($conn);
        break;
    case 'getTopProducts':
        getTopProducts($conn);
        break;
    case 'getCategorySales':
        getCategorySales($conn);
        break;
    case 'getRecentInvoices':
        getRecentInvoices($conn);
        break;
    case 'getTopCustomers':
        getTopCustomers($conn);
        break;
    case 'checkout':
        checkout($conn);
        break;
    case 'bulkUpdateProducts':
        bulkUpdateProducts($conn);
        break;
    case 'bulkDeleteProducts':
        bulkDeleteProducts($conn);
        break;
    case 'getRemovedProducts':
        getRemovedProducts($conn);
        break;
    case 'restoreProducts':
        restoreProducts($conn);
        break;
    case 'permanentlyDeleteProducts':
        permanentlyDeleteProducts($conn);
        break;
    case 'getInventoryStats':
        getInventoryStats($conn);
        break;
    case 'getUploadedImages':
        getUploadedImages($conn);
        break;
    case 'getNotifications':
        checkUpcomingHolidays($conn);
        checkAutoBackup($conn); // Check for auto backup
        getNotifications($conn);
        break;
    case 'getHolidays':
        getHolidays($conn);
        break;
    case 'addHoliday':
        addHoliday($conn);
        break;
    case 'updateHoliday':
        updateHoliday($conn);
        break;
    case 'deleteHoliday':
        deleteHoliday($conn);
        break;
    case 'toggleHolidayActive':
        toggleHolidayActive($conn);
        break;
    case 'syncMoroccanHolidays':
        syncMoroccanHolidays($conn);
        break;
    case 'cleanOldNotifications':
        cleanOldNotifications($conn);
        echo json_encode(['success' => true]);
        break;
    // --- أضف هذه الحالات الجديدة ---
    case 'markAllNotificationsRead':
        markAllNotificationsRead($conn);
        break;
    case 'markNotificationRead':
        markNotificationRead($conn);
        break;
    case 'deleteNotification':
        deleteNotification($conn);
        break;
    case 'checkRentalDue':
        checkRentalDue($conn);
        break;
    case 'checkExpiringProducts':
        checkExpiringProducts($conn);
        echo json_encode(['success' => true]);
        break;
    case 'markRentalPaidThisMonth':
        markRentalPaidThisMonth($conn);
        break;
    case 'getRentalPayments':
        getRentalPayments($conn);
        break;
    case 'uploadImage':
        uploadImage($conn);
        break;
    case 'get_business_day_status':
        get_business_day_status($conn);
        break;
    case 'start_day':
        start_day($conn);
        break;
    case 'reopen_day':
        reopen_day($conn);
        break;
    case 'extend_day':
        extend_day($conn);
        break;
    case 'end_day':
        end_day($conn);
        break;
    case 'get_period_summary':
        get_period_summary($conn);
        break;
    case 'get_invoice_details':
        get_invoice_details($conn);
        break;
    case 'updateShopLogo':
        updateShopLogo($conn);
        break;
    case 'update_first_login':
        updateFirstLogin($conn);
        break;
    case 'updateSetting':
        updateSetting($conn);
        break;
    case 'send_contact_message':
        send_contact_message($conn);
        break;
    case 'get_holiday_status':
        get_holiday_status($conn);
        break;
    case 'getExpenses':
        getExpenses($conn);
        break;
    case 'addExpense':
        addExpense($conn);
        break;
    case 'deleteExpense':
        deleteExpense($conn);
        break;
    case 'refund_invoice':
        refundInvoice($conn);
        break;
    case 'getRefunds':
        getRefunds($conn);
        break;
    // Backup Actions
    case 'createBackup':
        createBackup($conn);
        break;
    case 'getBackups':
        getBackups($conn);
        break;
    case 'deleteBackup':
        deleteBackup($conn);
        break;
    case 'downloadBackup':
        downloadBackup($conn);
        break;
    case 'restoreBackup':
        restoreBackup($conn);
        break;
    case 'getRestoreProgress':
        getRestoreProgress();
        break;
    default:
        echo json_encode(['success' => false, 'message' => __('invalid_data')]);
        break;
}

function isHoliday($conn, $date) {
    // جلب الإعدادات الخاصة بالعطل وأيام العمل
    $settings_res = $conn->query("SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('work_days_enabled', 'work_days', 'holidays_enabled')");
    $settings = [];
    if ($settings_res) {
        while ($row = $settings_res->fetch_assoc()) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
    }

    // 1. التحقق من جدول العطلات الرسمية (فقط إذا كانت ميزة العطل مفعلة)
    if (($settings['holidays_enabled'] ?? '0') === '1') {
        $stmt = $conn->prepare("SELECT name FROM holidays WHERE date = ? AND is_active = 1");
        if ($stmt) {
            $stmt->bind_param("s", $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $holiday = $result->fetch_assoc();
            $stmt->close();
            if ($holiday) return $holiday['name'];
        }
    }

    // 2. التحقق من إعدادات أيام العمل الأسبوعية
    if (($settings['work_days_enabled'] ?? '0') === '1') {
        $timestamp = strtotime($date);
        $day_of_week = strtolower(date('l', $timestamp)); // e.g., "sunday"
        $work_days_str = $settings['work_days'] ?? '';
        $work_days = !empty($work_days_str) ? explode(',', $work_days_str) : [];
        $work_days = array_map('trim', array_map('strtolower', $work_days));
        
        if (!in_array($day_of_week, $work_days)) {
            $days_ar = [
                'monday' => 'الاثنين',
                'tuesday' => 'الثلاثاء',
                'wednesday' => 'الأربعاء',
                'thursday' => 'الخميس',
                'friday' => 'الجمعة',
                'saturday' => 'السبت',
                'sunday' => 'الأحد'
            ];
            $day_name_ar = $days_ar[$day_of_week] ?? $day_of_week;
            return "عطلة أسبوعية (" . $day_name_ar . ")";
        }
    }

    return false;
}

function get_expense_cycle_dates($conn) {
    $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'expense_cycle'");
    $cycle = ($res && $res->num_rows > 0) ? $res->fetch_assoc()['setting_value'] : 'monthly';

    $today = new DateTime();
    $year = $today->format('Y');
    $month = $today->format('m');
    $day = (int)$today->format('d');

    if ($cycle === 'bi-monthly') {
        if ($day <= 15) {
            $start = "$year-$month-01";
            $end = "$year-$month-15";
        } else {
            $start = "$year-$month-16";
            $end = $today->format('Y-m-t'); // Last day of month
        }
    } else {
        $start = "$year-$month-01";
        $end = $today->format('Y-m-t');
    }

    return ['start' => $start, 'end' => $end, 'cycle' => $cycle];
}

function get_holiday_status($conn) {
    $today = date('Y-m-d');
    $holidayName = isHoliday($conn, $today);
    if ($holidayName) {
        echo json_encode(['success' => true, 'is_holiday' => true, 'holiday_name' => $holidayName]);
    } else {
        echo json_encode(['success' => true, 'is_holiday' => false]);
    }
}

function get_business_day_status($conn) {
    $stmt = $conn->prepare("SELECT * FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $day = $result->fetch_assoc();
    $stmt->close();

    if ($day) {
        echo json_encode(['success' => true, 'data' => ['status' => 'open', 'day' => $day]]);
    } else {
        echo json_encode(['success' => true, 'data' => ['status' => 'closed']]);
    }
}

function sendJsonResponse($data) {
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}


function start_day($conn) {
    try {
        // Check if user is logged in and has admin role
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            sendJsonResponse(['success' => false, 'message' => __('login_required')]);
            return;
        }
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            sendJsonResponse(['success' => false, 'message' => __('access_denied')]);
            return;
        }

        // Get and validate input
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['success' => false, 'message' => __('invalid_data')]);
            return;
        }
        
        $opening_balance = isset($data['opening_balance']) ? floatval($data['opening_balance']) : 0;
        $force = isset($data['force']) ? (bool)$data['force'] : false;
        $user_id = intval($_SESSION['id']);

        // Validate user_id
        if ($user_id <= 0) {
            sendJsonResponse(['success' => false, 'message' => __('invalid_user_id')]);
            return;
        }

        // Check if user exists
        $user_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $user_check->bind_param("i", $user_id);
        $user_check->execute();
        $user_result = $user_check->get_result();
        if ($user_result->num_rows === 0) {
            $user_check->close();
            sendJsonResponse(['success' => false, 'message' => __('user_not_found')]);
            return;
        }
        $user_check->close();

        // Check if there's already a business day for today (only if not forcing)
        if (!$force) {
            $today = date('Y-m-d');
            $stmt = $conn->prepare("SELECT id, start_time, end_time FROM business_days WHERE DATE(start_time) = ? ORDER BY start_time DESC LIMIT 1");
            if (!$stmt) {
                sendJsonResponse(['success' => false, 'message' => __('db_error') . ': ' . $conn->error]);
                return;
            }
            
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $day = $result->fetch_assoc();
                $stmt->close();
                
                if ($day['end_time'] === null) {
                    // Business day is open, allow extension
                    sendJsonResponse([
                        'success' => false,
                        'message' => __('business_day_open_exists'),
                        'details' => __('business_day_started_at') . " " . date('Y-m-d H:i', strtotime($day['start_time'])),
                        'code' => 'business_day_open_exists',
                        'day_id' => $day['id']
                    ]);
                    return;
                } else {
                    // Business day is closed, allow reopening
                    sendJsonResponse([
                        'success' => false,
                        'message' => __('business_day_closed_exists'),
                        'details' => __('business_day_can_reopen'),
                        'code' => 'business_day_closed_exists',
                        'day_id' => $day['id']
                    ]);
                    return;
                }
            }
            $stmt->close();
        }

        // Insert new business day
        $stmt = $conn->prepare("INSERT INTO business_days (start_time, opening_balance, user_id) VALUES (NOW(), ?, ?)");
        if (!$stmt) {
            sendJsonResponse(['success' => false, 'message' => __('db_prep_error') . ': ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("di", $opening_balance, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            create_notification($conn, __('notification_business_day_started') . ": " . $opening_balance, "business_day_start");
            sendJsonResponse(['success' => true, 'message' => __('business_day_started_success')]);
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendJsonResponse(['success' => false, 'message' => __('business_day_start_fail') . ': ' . $error]);
        }
        
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => __('error') . ': ' . $e->getMessage()]);
    }
}

function reopen_day($conn) {
    try {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            sendJsonResponse(['success' => false, 'message' => __('access_denied')]);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['success' => false, 'message' => __('invalid_data')]);
            return;
        }

        $day_id = isset($data['day_id']) ? intval($data['day_id']) : 0;
        $additional_balance = isset($data['opening_balance']) ? floatval($data['opening_balance']) : 0;

        if ($day_id <= 0) {
            sendJsonResponse(['success' => false, 'message' => __('invalid_business_day_id')]);
            return;
        }

        $stmt = $conn->prepare("UPDATE business_days SET end_time = NULL, closing_balance = NULL, opening_balance = opening_balance + ? WHERE id = ?");
        if (!$stmt) {
            sendJsonResponse(['success' => false, 'message' => __('db_prep_error') . ': ' . $conn->error]);
            return;
        }

        $stmt->bind_param("di", $additional_balance, $day_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                create_notification($conn, __('notification_business_day_reopened') . ": " . $additional_balance, "business_day_reopen");
                sendJsonResponse(['success' => true, 'message' => __('business_day_reopen_success')]);
            } else {
                sendJsonResponse(['success' => false, 'message' => __('business_day_not_found_reopen')]);
            }
        } else {
            $error = $stmt->error;
            sendJsonResponse(['success' => false, 'message' => __('business_day_reopen_fail') . ': ' . $error]);
        }
        $stmt->close();

    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => __('error') . ': ' . $e->getMessage()]);
    }
}

function extend_day($conn) {
    try {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            sendJsonResponse(['success' => false, 'message' => __('access_denied')]);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['success' => false, 'message' => __('invalid_data')]);
            return;
        }

        $day_id = isset($data['day_id']) ? intval($data['day_id']) : 0;
        $additional_balance = isset($data['opening_balance']) ? floatval($data['opening_balance']) : 0;

        if ($day_id <= 0) {
            sendJsonResponse(['success' => false, 'message' => __('invalid_business_day_id')]);
            return;
        }

        // Add the additional balance to the existing opening_balance
        $stmt = $conn->prepare("UPDATE business_days SET opening_balance = opening_balance + ? WHERE id = ? AND end_time IS NULL");
        if (!$stmt) {
            sendJsonResponse(['success' => false, 'message' => __('db_prep_error') . ': ' . $conn->error]);
            return;
        }

        $stmt->bind_param("di", $additional_balance, $day_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                 create_notification($conn, __('notification_business_day_extended') . ": " . $additional_balance, "business_day_extend");
                sendJsonResponse(['success' => true, 'message' => __('business_day_extend_success')]);
            } else {
                sendJsonResponse(['success' => false, 'message' => __('business_day_not_found_extend')]);
            }
        } else {
            $error = $stmt->error;
            sendJsonResponse(['success' => false, 'message' => __('business_day_extend_fail') . ': ' . $error]);
        }
        $stmt->close();

    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => __('error') . ': ' . $e->getMessage()]);
    }
}

function get_period_summary($conn) {
    try {
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        $sql_start = $start_date . " 00:00:00";
        $sql_end = $end_date . " 23:59:59";

        // 1. Total Sales
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total_sales, COALESCE(SUM(delivery_cost), 0) as total_delivery FROM invoices WHERE created_at BETWEEN ? AND ?");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $total_sales = floatval($res['total_sales']);
        $total_delivery = floatval($res['total_delivery']);
        $stmt->close();

        // 2. Total Refunds
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_refunds FROM refunds WHERE created_at BETWEEN ? AND ?");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $total_refunds = floatval($stmt->get_result()->fetch_assoc()['total_refunds']);
        $stmt->close();
        
        // 3. COGS
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(ii.quantity * ii.cost_price), 0) as total_cogs
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.created_at BETWEEN ? AND ?
        ");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $total_cogs = floatval($stmt->get_result()->fetch_assoc()['total_cogs']);
        $stmt->close();

        // 4. Expenses
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_expenses FROM expenses WHERE expense_date BETWEEN ? AND ?");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $total_general_expenses = floatval($stmt->get_result()->fetch_assoc()['total_expenses']);
        $stmt->close();

        // Drawer Expenses (for closing balance calc)
        // Note: Using created_at for drawer expenses to match shift/day logic better, but here we used expense_date for general report.
        // For consistency in financial report, we use expense_date.
        // But for "Cash in Drawer" estimation, we need paid_from_drawer.
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as drawer_expenses FROM expenses WHERE expense_date BETWEEN ? AND ? AND paid_from_drawer = 1");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $drawer_expenses = floatval($stmt->get_result()->fetch_assoc()['drawer_expenses']);
        $stmt->close();

        // Rent
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_rent FROM rental_payments WHERE payment_date BETWEEN ? AND ?");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $total_rent = floatval($stmt->get_result()->fetch_assoc()['total_rent']);
        $stmt->close();

        $total_other_costs = $total_general_expenses + $total_rent;

        // 5. Opening Balance
        $stmt = $conn->prepare("SELECT COALESCE(opening_balance, 0) as opening_balance FROM business_days WHERE start_time BETWEEN ? AND ? ORDER BY start_time ASC LIMIT 1");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $opening_balance = floatval($stmt->get_result()->fetch_assoc()['opening_balance']);
        $stmt->close();

        // 6. Closing Balance (Cash Flow)
        $closing_balance = $opening_balance + $total_sales - $total_refunds - $drawer_expenses;

        // 7. Net Profit
        // Profit = (Sales - Refunds) - COGS - Expenses
        $net_revenue = $total_sales - $total_refunds;
        $total_profit = $net_revenue - $total_cogs - $total_other_costs;

        // Holiday stats
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as holiday_sales, COUNT(*) as holiday_orders FROM invoices WHERE created_at BETWEEN ? AND ? AND is_holiday = 1");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $holiday_res = $stmt->get_result()->fetch_assoc();
        $holiday_sales = floatval($holiday_res['holiday_sales']);
        $holiday_orders = intval($holiday_res['holiday_orders']);
        $stmt->close();

        // Holiday stats breakdown
        $stmt = $conn->prepare("
            SELECT COALESCE(holiday_name, 'عطلة غير محددة') as holiday_name, COALESCE(SUM(total), 0) as sales, COUNT(*) as orders 
            FROM invoices 
            WHERE created_at BETWEEN ? AND ? AND is_holiday = 1 
            GROUP BY holiday_name
        ");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $holiday_breakdown_res = $stmt->get_result();
        $holiday_breakdown = [];
        while ($row = $holiday_breakdown_res->fetch_assoc()) {
            $holiday_breakdown[] = $row;
        }
        $stmt->close();

        // Get list of invoices in the period
        $stmt = $conn->prepare("
            SELECT i.id, i.total, i.delivery_cost, i.created_at, i.is_holiday, i.holiday_name, c.name as customer_name, c.phone as customer_phone,
                   GROUP_CONCAT(CONCAT(p.name, ' (', ii.quantity, 'x ', ii.price, ')') SEPARATOR ', ') as items
            FROM invoices i
            LEFT JOIN customers c ON i.customer_id = c.id
            LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
            LEFT JOIN products p ON ii.product_id = p.id
            WHERE i.created_at BETWEEN ? AND ?
            GROUP BY i.id
            ORDER BY i.created_at DESC
        ");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $invoices_result = $stmt->get_result();
        $invoices = [];
        while ($row = $invoices_result->fetch_assoc()) {
            $invoices[] = $row;
        }
        $stmt->close();

        $summary = [
            'total_sales' => $total_sales,
            'total_refunds' => $total_refunds,
            'drawer_expenses' => $drawer_expenses,
            'total_delivery' => $total_delivery,
            'opening_balance' => $opening_balance,
            'closing_balance' => $closing_balance,
            'total_cogs' => $total_cogs,
            'total_expenses' => $total_other_costs,
            'total_profit' => $total_profit,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'holiday_sales' => $holiday_sales,
            'holiday_orders' => $holiday_orders,
            'holiday_breakdown' => $holiday_breakdown,
            'invoices' => $invoices
        ];
        
        sendJsonResponse([
            'success' => true, 
            'message' => __('period_summary_success'), 
            'data' => ['summary' => $summary]
        ]);
        
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => __('error') . ': ' . $e->getMessage()]);
    }
}

function get_invoice_details($conn) {
    try {
        $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
        if (!$invoice_id) {
            sendJsonResponse(['success' => false, 'message' => __('invoice_id_required')]);
            return;
        }

        // Get invoice basic info
        $stmt = $conn->prepare("
            SELECT i.*, c.name as customer_name, c.phone as customer_phone
            FROM invoices i
            LEFT JOIN customers c ON i.customer_id = c.id
            WHERE i.id = ?
        ");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $invoice = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$invoice) {
            sendJsonResponse(['success' => false, 'message' => __('invoice_not_found')]);
            return;
        }

        // Get invoice items
        $stmt = $conn->prepare("
            SELECT ii.*, p.name as product_name
            FROM invoice_items ii
            LEFT JOIN products p ON ii.product_id = p.id
            WHERE ii.invoice_id = ?
        ");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        $items = [];
        while ($row = $items_result->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();

        // Prepare data similar to POS
        $invoice_data = [
            'id' => $invoice['id'],
            'date' => $invoice['created_at'],
            'total' => floatval($invoice['total']),
            'delivery' => floatval($invoice['delivery_cost']),
            'deliveryCity' => $invoice['delivery_city'],
            'discount_amount' => floatval($invoice['discount_amount']),
            'amount_received' => floatval($invoice['amount_received']),
            'change_due' => floatval($invoice['change_due']),
            'subtotal' => floatval($invoice['total']) - floatval($invoice['delivery_cost']) + floatval($invoice['discount_amount']),
            'customer' => $invoice['customer_id'] ? [
                'name' => $invoice['customer_name'],
                'phone' => $invoice['customer_phone']
            ] : null,
            'items' => array_map(function($item) {
                return [
                    'name' => $item['product_name'] ?: $item['product_name'],
                    'quantity' => intval($item['quantity']),
                    'price' => floatval($item['price'])
                ];
            }, $items)
        ];

        sendJsonResponse([
            'success' => true,
            'message' => __('invoice_details_success'),
            'data' => $invoice_data
        ]);

    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => __('error') . ': ' . $e->getMessage()]);
    }
}
// Also update the end_day function for consistency:
function end_day($conn) {
    try {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            sendJsonResponse(['success' => false, 'message' => __('access_denied')]);
            return;
        }

        $stmt = $conn->prepare("SELECT * FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        if (!$stmt) {
            sendJsonResponse(['success' => false, 'message' => __('db_error')]);
            return;
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $day = $result->fetch_assoc();
        $stmt->close();

        if (!$day) {
            sendJsonResponse(['success' => false, 'message' => __('business_day_no_open')]);
            return;
        }

        $day_id = intval($day['id']);
        $start_time = $day['start_time'];

        // 1. Calculate Total Sales (Revenue)
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total_sales, COALESCE(SUM(delivery_cost), 0) as total_delivery FROM invoices WHERE created_at >= ?");
        $stmt->bind_param("s", $start_time);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $total_sales = floatval($res['total_sales']);
        $total_delivery = floatval($res['total_delivery']);
        $stmt->close();
        
        // 2. Calculate Total Refunds (Cash Out)
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_refunds FROM refunds WHERE created_at >= ?");
        $stmt->bind_param("s", $start_time);
        $stmt->execute();
        $total_refunds = floatval($stmt->get_result()->fetch_assoc()['total_refunds']);
        $stmt->close();

        // 3. Calculate COGS (Cost of Goods Sold) - Adjusted for Refunds? 
        // Ideally we should subtract COGS of returned items. 
        // For simplicity in this iteration, we keep COGS as "Sold" and Refunds as "Loss of Revenue".
        // Better: Net Sales = Sales - Refunds.
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(ii.quantity * ii.cost_price), 0) as total_cogs
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            WHERE i.created_at >= ?
        ");
        $stmt->bind_param("s", $start_time);
        $stmt->execute();
        $total_cogs = floatval($stmt->get_result()->fetch_assoc()['total_cogs']);
        $stmt->close();

        // 4. Expenses
        $today_date = date('Y-m-d', strtotime($start_time));
        
        // Total Expenses (for Profit calc)
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_expenses FROM expenses WHERE expense_date >= ?");
        $stmt->bind_param("s", $today_date);
        $stmt->execute();
        $total_general_expenses = floatval($stmt->get_result()->fetch_assoc()['total_expenses']);
        $stmt->close();

        // Drawer Expenses (for Cash calc) - Using created_at to match shift
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as drawer_expenses FROM expenses WHERE created_at >= ? AND paid_from_drawer = 1");
        $stmt->bind_param("s", $start_time);
        $stmt->execute();
        $drawer_expenses = floatval($stmt->get_result()->fetch_assoc()['drawer_expenses']);
        $stmt->close();

        // Rent
        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount), 0) as total_rent FROM rental_payments WHERE payment_date >= ?");
        $stmt->bind_param("s", $today_date);
        $stmt->execute();
        $total_rent = floatval($stmt->get_result()->fetch_assoc()['total_rent']);
        $stmt->close();

        $total_other_costs = $total_general_expenses + $total_rent;

        // 5. Closing Balance Calculation (Cash Flow)
        // Closing = Opening + Sales - Refunds - Expenses Paid From Drawer
        $closing_balance = floatval($day['opening_balance']) + $total_sales - $total_refunds - $drawer_expenses;

        // 6. Net Profit Calculation
        // Profit = (Sales - Refunds) - COGS - Expenses
        // Note: We stop subtracting delivery_cost as it's part of Revenue. 
        // If driver cost is in Expenses, it's handled there.
        $net_revenue = $total_sales - $total_refunds;
        $total_profit = $net_revenue - $total_cogs - $total_other_costs;

        $stmt = $conn->prepare("UPDATE business_days SET end_time = NOW(), closing_balance = ? WHERE id = ?");
        $stmt->bind_param("di", $closing_balance, $day_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $summary = [
                'total_sales' => $total_sales,
                'total_refunds' => $total_refunds, // New
                'drawer_expenses' => $drawer_expenses, // New
                'total_delivery' => $total_delivery,
                'opening_balance' => floatval($day['opening_balance']),
                'closing_balance' => $closing_balance,
                'total_cogs' => $total_cogs,
                'total_expenses' => $total_other_costs,
                'total_profit' => $total_profit
            ];
            create_notification($conn, __('notification_business_day_ended') . ": " . $total_sales, "business_day_end");
            sendJsonResponse([
                'success' => true, 
                'message' => __('business_day_ended_success'), 
                'data' => ['summary' => $summary]
            ]);
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendJsonResponse(['success' => false, 'message' => __('business_day_end_fail') . ': ' . $error]);
        }
        
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => __('error') . ': ' . $e->getMessage()]);
    }
}

function updateShopLogo($conn) {
    // فقط المدير يمكنه تغيير الشعار
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }

    if (isset($_FILES['shopLogoFile']) && $_FILES['shopLogoFile']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['png', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($_FILES['shopLogoFile']['name'], PATHINFO_EXTENSION));
        if ($ext === 'jpeg') $ext = 'jpg';

        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0755, true)) {
                    echo json_encode(['success' => false, 'message' => __('upload_dir_fail')]);
                    return;
                }
            }

            // استخدام اسم ثابت لملف الشعار لسهولة الإدارة
            $filename = 'shop_logo.' . $ext;
            $destFs = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            $destUrl = 'src/uploads/' . $filename;
            
            // حذف الشعار القديم إذا كان موجوداً بامتداد مختلف
            $oldLogoJpg = $uploadDir . DIRECTORY_SEPARATOR . 'shop_logo.jpg';
            $oldLogoPng = $uploadDir . DIRECTORY_SEPARATOR . 'shop_logo.png';
            if (file_exists($oldLogoJpg) && $destFs !== $oldLogoJpg) {
                @unlink($oldLogoJpg);
            }
            if (file_exists($oldLogoPng) && $destFs !== $oldLogoPng) {
                @unlink($oldLogoPng);
            }

            if (@move_uploaded_file($_FILES['shopLogoFile']['tmp_name'], $destFs)) {
                // تحديث قاعدة البيانات
                $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES ('shopLogoUrl', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->bind_param("ss", $destUrl, $destUrl);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => __('logo_update_success'), 'logoUrl' => $destUrl]);
                } else {
                    echo json_encode(['success' => false, 'message' => __('db_update_fail')]);
                }
                $stmt->close();

            } else {
                echo json_encode(['success' => false, 'message' => __('file_move_fail')]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => __('invalid_file_type')]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => __('no_file_uploaded')]);
    }
}

function handle_image_upload($conn, $file) {
    $targetDir = "src/img/uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $fileName = uniqid() . '_' . basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowTypes = array('jpg','png','jpeg','gif');
    if (in_array($fileType, $allowTypes)) {
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            $stmt_gallery = $conn->prepare("INSERT IGNORE INTO media_gallery (file_path) VALUES (?)");
            $stmt_gallery->bind_param("s", $targetFilePath);
            $stmt_gallery->execute();
            $stmt_gallery->close();
            return $targetFilePath;
        }
    }
    return null;
}

function uploadImage($conn) {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imagePath = handle_image_upload($conn, $_FILES['image']);
        if ($imagePath) {
            echo json_encode(['success' => true, 'filePath' => $imagePath]);
        } else {
            echo json_encode(['success' => false, 'message' => __('image_upload_fail')]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => __('no_image_sent')]);
    }
}

function is_valid_image_path($path) {
    if (empty($path)) {
        return true; // No image is a valid state
    }

    // Prevent path traversal attacks
    if (strpos($path, '..') !== false) {
        return false;
    }

    $allowed_prefixes = ['src/img/uploads/', 'src/img/'];
    $path_is_allowed = false;
    foreach ($allowed_prefixes as $prefix) {
        if (strpos($path, $prefix) === 0) {
            $path_is_allowed = true;
            break;
        }
    }

    // Check if the file actually exists to prevent pointing to arbitrary non-image files
    return $path_is_allowed && file_exists($path) && is_file($path);
}

function getRemovedProducts($conn) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'removed_at';
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'desc';

    // Auto-cleanup: permanently remove entries older than 30 days
    try {
        $conn->query("DELETE FROM removed_products WHERE removed_at <= (NOW() - INTERVAL 30 DAY)");
    } catch (Exception $e) {
        // ignore cleanup errors
    }

    $baseSql = "FROM removed_products rp LEFT JOIN categories c ON rp.category_id = c.id WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $baseSql .= " AND (rp.name LIKE ? OR rp.barcode LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }

    $countSql = "SELECT COUNT(rp.id) as total " . $baseSql;
    $stmt = $conn->prepare($countSql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total_products = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $allowedSortColumns = ['name', 'price', 'quantity', 'removed_at'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'removed_at';
    }
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

    $offset = ($page - 1) * $limit;
    $dataSql = "SELECT rp.id, rp.name, rp.price, rp.quantity, rp.image, rp.category_id, rp.barcode, rp.removed_at, c.name as category_name "
             . $baseSql 
             . " ORDER BY rp.{$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
    
    $dataTypes = $types . 'ii';
    $dataParams = array_merge($params, [$limit, $offset]);

    $stmt = $conn->prepare($dataSql);
    $stmt->bind_param($dataTypes, ...$dataParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $products, 'total_products' => $total_products]);
}

function restoreProducts($conn) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $product_ids = $data['product_ids'] ?? [];

        if (empty($product_ids)) {
            echo json_encode(['success' => false, 'message' => __('no_products_selected')]);
            return;
        }

        $product_ids = array_map('intval', $product_ids);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $types = str_repeat('i', count($product_ids));
        
        $conn->begin_transaction();

        // 1. Move products back to products table
        $restore_sql = "INSERT INTO products (id, name, price, quantity, category_id, barcode, image, created_at)
                        SELECT id, name, price, quantity, category_id, barcode, image, created_at
                        FROM removed_products
                        WHERE id IN ($placeholders)";
        $restore_stmt = $conn->prepare($restore_sql);
        $restore_stmt->bind_param($types, ...$product_ids);
        if (!$restore_stmt->execute()) {
             throw new Exception(__('product_restore_fail') . ': ' . $restore_stmt->error);
        }
        $restore_stmt->close();
        
        // 2. Delete from removed_products
        $delete_sql = "DELETE FROM removed_products WHERE id IN ($placeholders)";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param($types, ...$product_ids);
        if (!$delete_stmt->execute()) {
            throw new Exception(__('archive_delete_fail') . ': ' . $delete_stmt->error);
        }
        $restored_count = $delete_stmt->affected_rows;
        $delete_stmt->close();

        $conn->commit();
        if ($restored_count > 0) {
            create_notification($conn, sprintf(__('notification_products_restored'), $restored_count), "product_restore");
        }
        echo json_encode(['success' => true, 'message' => sprintf(__('products_restored_success'), $restored_count)]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('product_restore_error') . ': ' . $e->getMessage()]);
    }
}

function permanentlyDeleteProducts($conn) {
     try {
        $data = json_decode(file_get_contents('php://input'), true);
        $product_ids = $data['product_ids'] ?? [];

        if (empty($product_ids)) {
            echo json_encode(['success' => false, 'message' => __('no_products_selected_delete')]);
            return;
        }
        $product_ids = array_map('intval', $product_ids);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $types = str_repeat('i', count($product_ids));
        
        $delete_sql = "DELETE FROM removed_products WHERE id IN ($placeholders)";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param($types, ...$product_ids);
        if (!$delete_stmt->execute()) {
            throw new Exception(__('permanent_delete_fail') . ': ' . $delete_stmt->error);
        }
        $deleted_count = $delete_stmt->affected_rows;
        $delete_stmt->close();
        
        echo json_encode(['success' => true, 'message' => sprintf(__('products_deleted_permanently'), $deleted_count)]);
     } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('permanent_delete_error') . ': ' . $e->getMessage()]);
    }
}

function getInventoryStats($conn) {
    $stats = [];

    $result = $conn->query("SELECT COUNT(*) as total_products, SUM(price * quantity) as total_stock_value FROM products");
    $row = $result->fetch_assoc();
    $stats['total_products'] = $row['total_products'] ?? 0;
    $stats['total_stock_value'] = $row['total_stock_value'] ?? 0;

    $result = $conn->query("SELECT COUNT(*) as out_of_stock FROM products WHERE quantity = 0");
    $stats['out_of_stock'] = $result->fetch_assoc()['out_of_stock'] ?? 0;

    $settings_sql = "SELECT setting_value FROM settings WHERE setting_name = 'low_quantity_alert'";
    $settings_result = $conn->query($settings_sql);
    $low_alert = ($settings_result && $settings_result->num_rows > 0) ? (int)$settings_result->fetch_assoc()['setting_value'] : 10;

    $stmt = $conn->prepare("SELECT COUNT(*) as low_stock FROM products WHERE quantity > 0 AND quantity <= ?");
    $stmt->bind_param("i", $low_alert);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['low_stock'] = $result->fetch_assoc()['low_stock'] ?? 0;
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $stats]);
}

function getUploadedImages($conn) {
    $result = $conn->query("SELECT id, file_path FROM media_gallery ORDER BY uploaded_at DESC");
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $images]);
}

function bulkUpdateProducts($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $product_ids = $data['product_ids'] ?? [];

    if (empty($product_ids)) {
        echo json_encode(['success' => false, 'message' => __('no_products_selected')]);
        return;
    }

    $updates = [];
    $params = [];
    $types = '';

    if (!empty($data['category_id'])) {
        $updates[] = 'category_id = ?';
        $params[] = $data['category_id'];
        $types .= 'i';
    }
    if ($data['price'] !== '' && !is_null($data['price'])) {
        $updates[] = 'price = ?';
        $params[] = $data['price'];
        $types .= 'd';
    }
    if ($data['quantity'] !== '' && !is_null($data['quantity'])) {
        $updates[] = 'quantity = ?';
        $params[] = $data['quantity'];
        $types .= 'i';
    }

    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => __('no_changes_to_apply')]);
        return;
    }

    $in_clause = implode(',', array_fill(0, count($product_ids), '?'));
    $types .= str_repeat('i', count($product_ids));
    $params = array_merge($params, $product_ids);

    $sql = "UPDATE products SET " . implode(', ', $updates) . " WHERE id IN ($in_clause)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $changed = [];
        if (!empty($data['category_id'])) $changed[] = 'الفئة';
        if ($data['price'] !== '' && !is_null($data['price'])) $changed[] = 'السعر';
        if ($data['quantity'] !== '' && !is_null($data['quantity'])) $changed[] = 'الكمية';
        if (!empty($changed)) {
            $msg = "تم تعديل " . implode(' و ', $changed) . " لعدد " . count($product_ids) . " منتج";
            create_notification($conn, $msg, "stock_update");
        }
        echo json_encode(['success' => true, 'message' => __('bulk_update_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('bulk_update_fail')]);
    }
    $stmt->close();
}

function toggleHolidayActive($conn) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => __('holiday_id_required')]);
        return;
    }
    
    $isActive = isset($data['is_active']) && $data['is_active'] ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE holidays SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $isActive, $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => __('action_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('update_fail')]);
    }
    $stmt->close();
}

function bulkDeleteProducts($conn) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $product_ids = $data['product_ids'] ?? [];

        if (empty($product_ids)) {
            echo json_encode(['success' => false, 'message' => __('no_products_selected')]);
            return;
        }

        $product_ids = array_map('intval', $product_ids);
        $product_ids = array_filter($product_ids, function($id) { return $id > 0; });

        if (empty($product_ids)) {
            echo json_encode(['success' => false, 'message' => __('bulk_delete_invalid_ids')]);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $types = str_repeat('i', count($product_ids));
        
        // الحصول على معلومات المنتجات قبل الحذف
        $info_sql = "SELECT p.id, p.name, 
                     COUNT(ii.id) as invoice_count
                     FROM products p
                     LEFT JOIN invoice_items ii ON p.id = ii.product_id
                     WHERE p.id IN ($placeholders)
                     GROUP BY p.id, p.name";
        
        $info_stmt = $conn->prepare($info_sql);
        if (!$info_stmt) {
            throw new Exception('فشل في تحضير استعلام المعلومات: ' . $conn->error);
        }
        
        $info_stmt->bind_param($types, ...$product_ids);
        $info_stmt->execute();
        $result = $info_stmt->get_result();
        
        $products_info = [];
        $linked_products = [];
        while ($row = $result->fetch_assoc()) {
            $products_info[] = $row;
            if ($row['invoice_count'] > 0) {
                $linked_products[] = [
                    'name' => $row['name'],
                    'invoice_count' => $row['invoice_count']
                ];
            }
        }
        $info_stmt->close();

        $conn->begin_transaction();

        // 1. Move products to removed_products table
        $archive_sql = "INSERT INTO removed_products (id, name, price, quantity, category_id, barcode, image, created_at)
                        SELECT id, name, price, quantity, category_id, barcode, image, created_at
                        FROM products
                        WHERE id IN ($placeholders)";
        $archive_stmt = $conn->prepare($archive_sql);
        $archive_stmt->bind_param($types, ...$product_ids);
        if (!$archive_stmt->execute()) {
             throw new Exception('فشل في أرشفة المنتجات: ' . $archive_stmt->error);
        }
        $archive_stmt->close();
        
        // 2. Delete from products table
        $delete_sql = "DELETE FROM products WHERE id IN ($placeholders)";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param($types, ...$product_ids);
        if (!$delete_stmt->execute()) {
            throw new Exception('فشل في حذف المنتجات من القائمة الرئيسية: ' . $delete_stmt->error);
        }
        $deleted_count = $delete_stmt->affected_rows;
        $delete_stmt->close();

        $conn->commit();
        
        if ($deleted_count > 0) {
            $msg = sprintf(__('bulk_delete_success'), $deleted_count);
            if ($deleted_count === 1 && !empty($products_info)) {
                $firstName = $products_info[0]['name'];
                $msg = sprintf(__('bulk_delete_single_success'), $firstName);
            }
            create_notification($conn, $msg, "product_delete");
        }
        
        $response = [
            'success' => true,
            'message' => sprintf(__('bulk_delete_success'), $deleted_count),
            'deleted_count' => $deleted_count
        ];
        
        // معلومات المنتجات المرتبطة بفواتير
        if (!empty($linked_products)) {
            $linked_count = count($linked_products);
            $linked_names = array_map(function($p) { 
                return $p['name'] . " ({$p['invoice_count']} " . __('invoice_count').")"; 
            }, $linked_products);
            
            $response['linked_info'] = [
                'count' => $linked_count,
                'products' => $linked_names,
                'note' => sprintf(__('bulk_delete_linked_note'), $linked_count)
            ];
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => __('bulk_delete_fail') . ': ' . $e->getMessage()
        ]);
    }
}

function getProducts($conn) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    $stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'name';
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'asc';
    $ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

    $settings_sql = "SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('low_quantity_alert', 'critical_quantity_alert')";
    $settings_result = $conn->query($settings_sql);
    $quantity_settings = [];
    while ($row = $settings_result->fetch_assoc()) {
        $quantity_settings[$row['setting_name']] = (int)$row['setting_value'];
    }
    $low_alert = $quantity_settings['low_quantity_alert'] ?? 10;
    $critical_alert = $quantity_settings['critical_quantity_alert'] ?? 5;

    $baseSql = "FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($ids)) {
        $in_clause = implode(',', array_fill(0, count($ids), '?'));
        $baseSql .= " AND p.id IN ($in_clause)";
        $params = array_merge($params, $ids);
        $types .= str_repeat('i', count($ids));
    }

    if (!empty($search)) {
        $baseSql .= " AND (p.name LIKE ? OR p.barcode LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    if ($category_id > 0) {
        $baseSql .= " AND p.category_id = ?";
        $params[] = $category_id;
        $types .= 'i';
    }
    if ($stock_status === 'out_of_stock') {
        $baseSql .= " AND p.quantity = 0";
    } elseif ($stock_status === 'low_stock') {
        $baseSql .= " AND p.quantity > ? AND p.quantity <= ?";
        $params[] = $critical_alert;
        $params[] = $low_alert;
        $types .= 'ii';
    } elseif ($stock_status === 'critical_stock') {
        $baseSql .= " AND p.quantity > 0 AND p.quantity <= ?";
        $params[] = $critical_alert;
        $types .= 'i';
    }

    $countSql = "SELECT COUNT(p.id) as total " . $baseSql;
    $stmt = $conn->prepare($countSql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total_products = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $allowedSortColumns = ['name', 'price', 'quantity'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'name';
    }
    $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

    $offset = ($page - 1) * $limit;
    
    $dataSql = "SELECT p.id, p.name, p.price, p.quantity, p.image, p.category_id, p.barcode, c.name as category_name "
             . $baseSql 
             . " ORDER BY CASE WHEN p.quantity = 0 THEN 1 ELSE 0 END, p.{$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
    
    $dataTypes = $types . 'ii';
    $dataParams = array_merge($params, [$limit, $offset]);

    $stmt = $conn->prepare($dataSql);
    $stmt->bind_param($dataTypes, ...$dataParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $products, 'total_products' => $total_products]);
}

function exportProductsExcel($conn) {
    require_once 'vendor/autoload.php';

    // جلب جميع المنتجات بدون حدود
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    $stock_status = isset($_GET['stock_status']) ? $_GET['stock_status'] : '';

    $settings_sql = "SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('low_quantity_alert', 'critical_quantity_alert', 'currency')";
    $settings_result = $conn->query($settings_sql);
    $quantity_settings = [];
    while ($row = $settings_result->fetch_assoc()) {
        $quantity_settings[$row['setting_name']] = $row['setting_value'];
    }
    $low_alert = (int)($quantity_settings['low_quantity_alert'] ?? 10);
    $critical_alert = (int)($quantity_settings['critical_quantity_alert'] ?? 5);
    $currency = $quantity_settings['currency'] ?? 'MAD';

    $baseSql = "FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $baseSql .= " AND (p.name LIKE ? OR p.barcode LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'ss';
    }
    if ($category_id > 0) {
        $baseSql .= " AND p.category_id = ?";
        $params[] = $category_id;
        $types .= 'i';
    }
    if ($stock_status === 'out_of_stock') {
        $baseSql .= " AND p.quantity = 0";
    } elseif ($stock_status === 'low_stock') {
        $baseSql .= " AND p.quantity > ? AND p.quantity <= ?";
        $params[] = $critical_alert;
        $params[] = $low_alert;
        $types .= 'ii';
    } elseif ($stock_status === 'critical_stock') {
        $baseSql .= " AND p.quantity > 0 AND p.quantity <= ?";
        $params[] = $critical_alert;
        $types .= 'i';
    }

    $dataSql = "SELECT p.id, p.name, p.price, p.cost_price, p.quantity, p.barcode, c.name as category_name, p.created_at "
             . $baseSql 
             . " ORDER BY p.name ASC";

    $stmt = $conn->prepare($dataSql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

    // إنشاء ملف Excel
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // إعدادات الورقة
    $sheet->setTitle('قائمة المنتجات');

    // العناوين
    $headers = ['الرقم', 'اسم المنتج', 'السعر', 'سعر الشراء', 'الكمية', 'الباركود', 'الفئة', 'تاريخ الإضافة'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        $col++;
    }

    // البيانات
    $row = 2;
    foreach ($products as $product) {
        $sheet->setCellValue('A' . $row, $product['id']);
        $sheet->setCellValue('B' . $row, $product['name']);
        $sheet->setCellValue('C' . $row, number_format($product['price'], 2) . ' ' . $currency);
        $sheet->setCellValue('D' . $row, $product['cost_price'] ? number_format($product['cost_price'], 2) . ' ' . $currency : '');
        $sheet->setCellValue('E' . $row, $product['quantity']);
        $sheet->setCellValue('F' . $row, $product['barcode'] ?: '');
        $sheet->setCellValue('G' . $row, $product['category_name'] ?: 'غير مصنف');
        $sheet->setCellValue('H' . $row, date('Y-m-d', strtotime($product['created_at'])));

        // تنسيق الصف
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
        ]);

        // تنسيق الأرقام
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00 "' . $currency . '"');
        if ($product['cost_price']) {
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00 "' . $currency . '"');
        }
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row++;
    }

    // تعديل عرض الأعمدة
    $sheet->getColumnDimension('A')->setWidth(25);
    $sheet->getColumnDimension('B')->setWidth(30);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(10);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(20);
    $sheet->getColumnDimension('H')->setWidth(15);

    // إضافة معلومات إضافية في الأسفل
    $row += 2;
    $sheet->setCellValue('A' . $row, 'إجمالي المنتجات:');
    $sheet->setCellValue('B' . $row, count($products));
    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
    ]);
    $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $totalValue = array_sum(array_map(function($p) { return $p['price'] * $p['quantity']; }, $products));
    $row++;
    $sheet->setCellValue('A' . $row, 'إجمالي قيمة المخزون:');
    $sheet->setCellValue('B' . $row, number_format($totalValue, 2) . ' ' . $currency);
    $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]
    ]);
    $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // إعداد headers للتحميل
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="products_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit;
}

function addProduct($conn) {
    $data = $_POST;
    $imagePath = null;

    if (!empty($data['image_path'])) {
        if (!is_valid_image_path($data['image_path'])) {
            echo json_encode(['success' => false, 'message' => __('product_image_invalid')]);
            return;
        }
        $imagePath = $data['image_path'];
    } else if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imagePath = handle_image_upload($conn, $_FILES['image']);
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO products (name, price, cost_price, quantity, category_id, barcode, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;
        $barcode = !empty($data['barcode']) ? $data['barcode'] : null;
        $cost_price = !empty($data['cost_price']) ? $data['cost_price'] : 0;
        $stmt->bind_param("sddiiss", $data['name'], $data['price'], $cost_price, $data['quantity'], $category_id, $barcode, $imagePath);
        $stmt->execute();
        $productId = $stmt->insert_id;
        $stmt->close();

        if (!empty($data['fields'])) {
            $fields = json_decode($data['fields'], true);
            $stmt = $conn->prepare("INSERT INTO product_field_values (product_id, field_id, value) VALUES (?, ?, ?)");
            foreach ($fields as $field) {
                $stmt->bind_param("iis", $productId, $field['id'], $field['value']);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();
        create_notification($conn, __('notification_product_added') . ": " . $data['name'], "product_add");
        echo json_encode(['success' => true, 'message' => __('product_added_success'), 'id' => $productId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('product_add_fail') . ': ' . $e->getMessage()]);
    }
}

function updateProduct($conn) {
    $data = $_POST;
    $productId = isset($data['id']) ? (int)$data['id'] : 0;

    if ($productId === 0) {
        echo json_encode(['success' => false, 'message' => __('product_id_required')]);
        return;
    }

    $imagePath = null;
    if (!empty($data['image_path'])) {
        if (!is_valid_image_path($data['image_path'])) {
            echo json_encode(['success' => false, 'message' => __('product_image_invalid')]);
            return;
        }
        $imagePath = $data['image_path'];
    } else if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imagePath = handle_image_upload($conn, $_FILES['image']);
    }

    $conn->begin_transaction();

    try {
        $sql = "UPDATE products SET name = ?, price = ?, cost_price = ?, quantity = ?, category_id = ?, barcode = ?";
        $cost_price = !empty($data['cost_price']) ? $data['cost_price'] : 0;
        $params = [
            $data['name'],
            $data['price'],
            $cost_price,
            $data['quantity'],
            !empty($data['category_id']) ? (int)$data['category_id'] : null,
            !empty($data['barcode']) ? $data['barcode'] : null
        ];
        $types = "sddiis";

        if ($imagePath !== null) {
            $sql .= ", image = ?";
            $params[] = $imagePath;
            $types .= "s";
        }

        $sql .= " WHERE id = ?";
        $params[] = $productId;
        $types .= "i";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();

        // Clear existing custom fields
        $stmt = $conn->prepare("DELETE FROM product_field_values WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->close();

        if (!empty($data['fields'])) {
            $fields = json_decode($data['fields'], true);
            if (is_array($fields)) {
                $stmt = $conn->prepare("INSERT INTO product_field_values (product_id, field_id, value) VALUES (?, ?, ?)");
                foreach ($fields as $field) {
                    if (!empty($field['value'])) {
                        $stmt->bind_param("iis", $productId, $field['id'], $field['value']);
                        $stmt->execute();
                    }
                }
                $stmt->close();
            }
        }

        $conn->commit();
        create_notification($conn, __('notification_product_updated') . ": " . $data['name'], "product_update");
        echo json_encode(['success' => true, 'message' => __('product_updated_success')]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('product_update_fail') . ': ' . $e->getMessage()]);
    }
}

function bulkAddProducts($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $products = $data['products'] ?? [];

    if (empty($products)) {
        echo json_encode(['success' => false, 'message' => __('no_products_sent')]);
        return;
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO products (name, price, cost_price, quantity, category_id, barcode, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $name = '';
        $price = 0.0;
        $cost_price = 0.0;
        $quantity = 0;
        $category_id = null;
        $barcode = null;
        $image_path = null;

        $stmt->bind_param("sddiiss", $name, $price, $cost_price, $quantity, $category_id, $barcode, $image_path);

        foreach ($products as $product) {
            $name = $product['name'];
            $price = $product['price'];
            $cost_price = !empty($product['cost_price']) ? $product['cost_price'] : 0.0;
            $quantity = $product['quantity'];
            $category_id = !empty($product['category_id']) ? (int)$product['category_id'] : null;
            $barcode = !empty($product['barcode']) ? $product['barcode'] : null;
            $image_path = !empty($product['image_path']) ? $product['image_path'] : null;
            
            if (!is_valid_image_path($image_path)) {
                throw new Exception(__('invalid_image_path') . ": " . htmlspecialchars($name));
            }

            $stmt->execute();
        }
        
        $stmt->close();
        $conn->commit();
        
        create_notification($conn, sprintf(__('notification_bulk_products_added'), count($products)), "product_add");
        echo json_encode(['success' => true, 'message' => __('bulk_add_success')]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('bulk_add_fail') . ': ' . $e->getMessage()]);
    }
}

function importProducts($conn) {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => __('import_no_file')]);
        return;
    }

    $file = $_FILES['excel_file']['tmp_name'];
    $skipDuplicates = isset($_POST['skip_duplicates']) && $_POST['skip_duplicates'] === 'on';
    $isPreview = isset($_POST['preview']) && $_POST['preview'] === 'true';

    try {
        $spreadsheet = IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            echo json_encode(['success' => false, 'message' => __('import_empty_file')]);
            return;
        }

        if (count($rows) < 2) {
            echo json_encode(['success' => false, 'message' => __('import_headers_only')]);
            return;
        }

        // Get headers from first row
        $headers = array_map('strtolower', array_map('trim', $rows[0]));
        $dataRows = array_slice($rows, 1);

        // Function to find column index with multiple possible names
        function findColumn($possibleNames, $headers) {
            foreach ($possibleNames as $name) {
                $index = array_search(strtolower(trim($name)), $headers);
                if ($index !== false) {
                    return $index;
                }
            }
            return false;
        }

        // Map expected columns with multiple possible names
        $columnMap = [
            'name' => findColumn(['name', 'Name', 'اسم', 'اسم المنتج', 'product name', 'Product Name', 'منتج'], $headers),
            'price' => findColumn(['price', 'Price', 'سعر', 'سعر البيع', 'selling price', 'Selling Price', 'السعر'], $headers),
            'quantity' => findColumn(['quantity', 'Quantity', 'كمية', 'الكمية', 'qty', 'Qty', 'الكمية'], $headers),
            'barcode' => findColumn(['barcode', 'Barcode', 'باركود', 'الباركود', 'code', 'Code', 'الباركود'], $headers),
            'category' => findColumn(['category', 'Category', 'فئة', 'الفئة', 'type', 'Type', 'الفئة'], $headers),
            'cost_price' => findColumn(['cost price', 'Cost Price', 'سعر التكلفة', 'تكلفة', 'cost', 'Cost', 'سعر التكلفة'], $headers),
            'image' => findColumn(['image', 'Image', 'صورة', 'الصورة', 'photo', 'Photo', 'الصورة'], $headers),
        ];

        // Check required columns
        if ($columnMap['name'] === false || $columnMap['price'] === false || $columnMap['quantity'] === false) {
            echo json_encode(['success' => false, 'message' => sprintf(__('import_missing_columns'), implode(', ', $headers))]);
            return;
        }

        $errors = [];
        $validProducts = [];
        $categoriesToCreate = [];

        foreach ($dataRows as $rowIndex => $row) {
            $rowNum = $rowIndex + 2; // +2 because array is 0-based and we skipped header

            $name = isset($row[$columnMap['name']]) ? trim($row[$columnMap['name']]) : '';
            $price = isset($row[$columnMap['price']]) ? trim($row[$columnMap['price']]) : '';
            $quantity = isset($row[$columnMap['quantity']]) ? trim($row[$columnMap['quantity']]) : '';
            $barcode = ($columnMap['barcode'] !== false && isset($row[$columnMap['barcode']])) ? trim($row[$columnMap['barcode']]) : '';
            $categoryName = ($columnMap['category'] !== false && isset($row[$columnMap['category']])) ? trim($row[$columnMap['category']]) : '';
            $costPrice = ($columnMap['cost_price'] !== false && isset($row[$columnMap['cost_price']])) ? trim($row[$columnMap['cost_price']]) : '';
            $image = ($columnMap['image'] !== false && isset($row[$columnMap['image']])) ? trim($row[$columnMap['image']]) : '';

            // Validate required fields
            if (empty($name)) {
                $errors[] = sprintf(__('import_row_error_name'), $rowNum);
                continue;
            }
            if (!is_numeric($price) || $price < 0) {
                $errors[] = sprintf(__('import_row_error_price'), $rowNum);
                continue;
            }
            if (!is_numeric($quantity) || $quantity < 0) {
                $errors[] = sprintf(__('import_row_error_qty'), $rowNum);
                continue;
            }

            // Check for duplicates if requested
            if ($skipDuplicates) {
                $stmt = $conn->prepare("SELECT id FROM products WHERE name = ? AND (barcode = ? OR barcode IS NULL)");
                $stmt->bind_param("ss", $name, $barcode);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    continue; // Skip duplicate
                }
                $stmt->close();
            }

            // Handle category
            $categoryId = null;
            if (!empty($categoryName)) {
                $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
                $stmt->bind_param("s", $categoryName);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $categoryId = $result->fetch_assoc()['id'];
                } else {
                    // Mark for creation
                    if (!in_array($categoryName, $categoriesToCreate)) {
                        $categoriesToCreate[] = $categoryName;
                    }
                }
                $stmt->close();
            }

            $validProducts[] = [
                'name' => $name,
                'price' => (float)$price,
                'cost_price' => !empty($costPrice) && is_numeric($costPrice) ? (float)$costPrice : 0.0,
                'quantity' => (int)$quantity,
                'barcode' => $barcode,
                'category_name' => $categoryName,
                'category_id' => $categoryId,
                'image' => $image,
            ];
        }

        if ($isPreview) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'total_rows' => count($dataRows),
                    'valid_products' => count($validProducts),
                    'errors' => $errors,
                    'categories_to_create' => $categoriesToCreate,
                ]
            ]);
            return;
        }

        // If not preview, proceed with import
        $conn->begin_transaction();

        // Create categories if needed
        foreach ($categoriesToCreate as $catName) {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $catName);
            $stmt->execute();
            $stmt->close();
        }

        // Insert products
        $insertedCount = 0;
        $stmt = $conn->prepare("INSERT INTO products (name, price, cost_price, quantity, category_id, barcode, image) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($validProducts as $product) {
            // Get category ID if it was created
            if (!empty($product['category_name']) && !$product['category_id']) {
                $stmtCat = $conn->prepare("SELECT id FROM categories WHERE name = ?");
                $stmtCat->bind_param("s", $product['category_name']);
                $stmtCat->execute();
                $result = $stmtCat->get_result();
                if ($result->num_rows > 0) {
                    $product['category_id'] = $result->fetch_assoc()['id'];
                }
                $stmtCat->close();
            }

            $stmt->bind_param("sddiiss", 
                $product['name'], 
                $product['price'], 
                $product['cost_price'], 
                $product['quantity'], 
                $product['category_id'], 
                $product['barcode'], 
                $product['image']
            );
            $stmt->execute();
            $insertedCount++;
        }

        $stmt->close();
        $conn->commit();

        create_notification($conn, sprintf(__('notification_import_success'), $insertedCount), "product_add");

        $message = sprintf(__('import_success_with_count'), $insertedCount);
        if (count($errors) > 0) {
            $message .= sprintf(__('import_skipped_errors'), count($errors));
        }

        echo json_encode([
            'success' => true, 
            'message' => $message,
            'data' => [
                'imported_count' => $insertedCount,
                'skipped_count' => count($errors),
                'categories_created' => count($categoriesToCreate)
            ]
        ]);

    } catch (Exception $e) {
        if ($conn->connect_errno === 0) {
            $conn->rollback();
        }
        echo json_encode(['success' => false, 'message' => __('import_fail') . ': ' . $e->getMessage()]);
    }
}

function getProductDetails($conn) {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($product_id === 0) {
        echo json_encode(['success' => false, 'message' => __('invalid_product_id')]);
        return;
    }

    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => __('product_not_found')]);
        return;
    }

    $stmt = $conn->prepare("SELECT cf.field_name, pfv.value 
                            FROM product_field_values pfv
                            JOIN category_fields cf ON pfv.field_id = cf.id
                            WHERE pfv.product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fields = [];
    while ($row = $result->fetch_assoc()) {
        $fields[] = $row;
    }
    $stmt->close();
    $product['custom_fields'] = $fields;

    echo json_encode(['success' => true, 'data' => $product]);
}

function getCategoryFields($conn) {
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    if ($category_id === 0) {
        echo json_encode(['success' => false, 'message' => __('category_id_required')]);
        return;
    }

    $stmt = $conn->prepare("SELECT id, field_name FROM category_fields WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $fields = [];
    while ($row = $result->fetch_assoc()) {
        $fields[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $fields]);
}

function getCategories($conn) {
    $sql = "SELECT c.id, c.name, c.description, 
            GROUP_CONCAT(cf.field_name SEPARATOR ',') as fields
            FROM categories c
            LEFT JOIN category_fields cf ON c.id = cf.category_id
            GROUP BY c.id
            ORDER BY c.name";
    
    $result = $conn->query($sql);

    $categories = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    echo json_encode(['success' => true, 'data' => $categories]);
}

function addCategory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => __('category_name_required_msg')]);
        return;
    }

    $conn->begin_transaction();

    try {
        $description = isset($data['description']) ? $data['description'] : '';
        
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $data['name'], $description);
        $stmt->execute();
        $categoryId = $stmt->insert_id;
        $stmt->close();

        if (!empty($data['fields'])) {
            $stmt = $conn->prepare("INSERT INTO category_fields (category_id, field_name, field_type) VALUES (?, ?, ?)");
            foreach ($data['fields'] as $field) {
                $fieldType = 'text';
                $stmt->bind_param("iss", $categoryId, $field, $fieldType);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => __('category_added_success'), 'id' => $categoryId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('category_add_fail') . ': ' . $e->getMessage()]);
    }
}

function updateCategory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => __('category_name_required_msg')]);
        return;
    }

    $conn->begin_transaction();

    try {
        $description = isset($data['description']) ? $data['description'] : '';
        
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $data['name'], $description, $data['id']);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM category_fields WHERE category_id = ?");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        $stmt->close();

        if (!empty($data['fields'])) {
            $stmt = $conn->prepare("INSERT INTO category_fields (category_id, field_name, field_type) VALUES (?, ?, ?)");
            foreach ($data['fields'] as $field) {
                $fieldType = 'text';
                $stmt->bind_param("iss", $data['id'], $field, $fieldType);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => __('category_updated_success')]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('category_update_fail') . ': ' . $e->getMessage()]);
    }
}

function deleteCategory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => __('category_id_required')]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $data['id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => __('category_deleted_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('category_delete_fail')]);
    }

    $stmt->close();
}

function getCustomers($conn) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;

    $baseSql = "FROM customers WHERE name LIKE ? OR phone LIKE ?";
    $searchTerm = "%{$search}%";
    
    $countSql = "SELECT COUNT(*) as total " . $baseSql;
    $stmt = $conn->prepare($countSql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $total_customers = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $offset = ($page - 1) * $limit;
    $dataSql = "SELECT * " . $baseSql . " ORDER BY name ASC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($dataSql);
    $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $customers, 'total_customers' => $total_customers]);
}

function addCustomer($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => __('customer_name_required')]);
        return;
    }

    $phone = isset($data['phone']) ? $data['phone'] : null;
    $email = isset($data['email']) ? $data['email'] : null;
    $address = isset($data['address']) ? $data['address'] : null;
    $city = isset($data['city']) ? $data['city'] : null;

    $stmt = $conn->prepare("INSERT INTO customers (name, phone, email, address, city) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $data['name'], $phone, $email, $address, $city);

    if ($stmt->execute()) {
        $customerId = $stmt->insert_id;
        echo json_encode(['success' => true, 'message' => __('customer_added_success'), 'id' => $customerId]);
    } else {
        echo json_encode(['success' => false, 'message' => __('customer_add_fail')]);
    }

    $stmt->close();
}

function getCustomerDetails($conn) {
    $customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($customer_id === 0) {
        echo json_encode(['success' => false, 'message' => __('invalid_customer_id')]);
        return;
    }

    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => __('customer_not_found')]);
        return;
    }

    echo json_encode(['success' => true, 'data' => $customer]);
}

function updateCustomer($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => __('customer_id_name_required')]);
        return;
    }

    $phone = isset($data['phone']) ? $data['phone'] : null;
    $email = isset($data['email']) ? $data['email'] : null;
    $address = isset($data['address']) ? $data['address'] : null;
    $city = isset($data['city']) ? $data['city'] : null;

    $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, email = ?, address = ?, city = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $data['name'], $phone, $email, $address, $city, $data['id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => __('customer_updated_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('customer_update_fail')]);
    }

    $stmt->close();
}

function exportCustomersExcel($conn) {
    require_once 'vendor/autoload.php';

    // الحصول على نوع التصدير والبيانات
    $exportType = isset($_GET['exportType']) ? $_GET['exportType'] : 'all_data';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 150;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // بناء شرط البحث
    $baseSql = "FROM customers WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $baseSql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    // بناء الاستعلام
    $dataSql = "SELECT id, name, phone, email, address, city, created_at " . $baseSql . " ORDER BY name ASC";

    // إذا كان التصدير للصفحة الحالية فقط
    if ($exportType === 'current_page') {
        $offset = ($page - 1) * $limit;
        $dataSql .= " LIMIT ? OFFSET ?";
        $types .= 'ii';
        $params[] = $limit;
        $params[] = $offset;
    }

    $stmt = $conn->prepare($dataSql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $stmt->close();

    // إنشاء ملف Excel
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true);
    $sheet->setTitle('قائمة العملاء');

    // العناوين
    $headers = ['#', 'الاسم', 'رقم الهاتف', 'البريد الإلكتروني', 'العنوان', 'المدينة', 'تاريخ الإضافة'];
    $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

    // تنسيق الرأس
    $headerStyle = [
        'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
    ];

    // تطبيق تنسيق الرأس
    foreach ($headers as $index => $header) {
        $col = $columns[$index];
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
    }

    // تعيين عرض الأعمدة
    $sheet->getColumnDimension('A')->setWidth(8);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(18);
    $sheet->getColumnDimension('D')->setWidth(25);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(15);

    // إضافة البيانات
    $row = 2;
    $dataStyle = [
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
        'font' => ['size' => 11, 'color' => ['rgb' => '1F2937']],
    ];

    $alternateRowStyle = array_merge($dataStyle, [
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
    ]);

    foreach ($customers as $index => $customer) {
        $sheet->setCellValue('A' . $row, $index + 1);
        $sheet->setCellValue('B' . $row, $customer['name']);
        $sheet->setCellValue('C' . $row, $customer['phone'] ?: '-');
        $sheet->setCellValue('D' . $row, $customer['email'] ?: '-');
        $sheet->setCellValue('E' . $row, $customer['address'] ?: '-');
        $sheet->setCellValue('F' . $row, $customer['city'] ?: '-');
        $sheet->setCellValue('G' . $row, $customer['created_at'] ? date('Y-m-d', strtotime($customer['created_at'])) : '-');

        // تطبيق التنسيق على الصف (تناوب الألوان)
        $currentStyle = ($index % 2 === 0) ? $alternateRowStyle : $dataStyle;
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($currentStyle);

        $row++;
    }

    // تجميد الصف الأول
    $sheet->freezePane('A2');

    // إعدادات الطباعة
    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
    $sheet->getPageMargins()->setLeft(0.5);
    $sheet->getPageMargins()->setRight(0.5);
    $sheet->getPageMargins()->setTop(0.5);
    $sheet->getPageMargins()->setBottom(0.5);

    // إعدادات الطباعة - تكرار الصف الأول
    $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

    // إضافة معلومات التصدير في الأسفل
    $infoRow = $row + 2;
    $sheet->setCellValue('A' . $infoRow, 'نوع التصدير:');
    $exportTypeText = ($exportType === 'all_data') ? 'جميع البيانات' : 'البيانات المعروضة';
    $sheet->setCellValue('B' . $infoRow, $exportTypeText);
    
    $infoRow++;
    $sheet->setCellValue('A' . $infoRow, 'تاريخ التصدير:');
    $sheet->setCellValue('B' . $infoRow, date('Y-m-d H:i:s'));
    
    $infoRow++;
    $sheet->setCellValue('A' . $infoRow, 'عدد السجلات:');
    $sheet->setCellValue('B' . $infoRow, count($customers));

    // حفظ الملف
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . 'عملاء_' . date('Y-m-d_H-i-s') . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    ob_end_clean();
    $writer->save('php://output');
    exit;
}

function checkout($conn) {
    $day_stmt = $conn->prepare("SELECT id FROM business_days WHERE end_time IS NULL");
    $day_stmt->execute();
    if ($day_stmt->get_result()->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => __('business_day_start_required')]);
        return;
    }
    $day_stmt->close();

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['items']) || !is_array($data['items'])) {
        echo json_encode(['success' => false, 'message' => __('cart_empty')]);
        return;
    }

    $conn->begin_transaction();

    try {
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
        $delivery_cost = isset($data['delivery_cost']) ? (float)$data['delivery_cost'] : 0;
        $delivery_city = isset($data['delivery_city']) ? $data['delivery_city'] : null;
        // SECURITY: Force payment method to 'cash' as per requirements
        $payment_method = 'cash'; 
        
        // SECURITY: Calculate total server-side
        $calculated_subtotal = 0;
        $verified_items = [];

        foreach ($data['items'] as $item) {
            $product_id = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            
            if ($quantity <= 0) continue;

            $stmt = $conn->prepare("SELECT name, price, cost_price, quantity FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows === 0) {
                throw new Exception(__('product_not_found_id') . ": $product_id");
            }
            $product = $res->fetch_assoc();
            $stmt->close();

            if ($product['quantity'] < $quantity) {
                throw new Exception(__('insufficient_stock_for') . ": " . $product['name']);
            }

            $line_total = $product['price'] * $quantity;
            $calculated_subtotal += $line_total;

            $verified_items[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'quantity' => $quantity,
                'price' => $product['price'],
                'cost_price' => $product['cost_price']
            ];
        }

        $discount_amount = isset($data['discount_amount']) ? (float)$data['discount_amount'] : 0;
        // Recalculate total safely
        $total = $calculated_subtotal + $delivery_cost - $discount_amount;
        if ($total < 0) $total = 0;

        $amount_received = isset($data['amount_received']) ? (float)$data['amount_received'] : 0;
        $change_due = $amount_received - $total; // Server-side calculation

        $holiday_name = isHoliday($conn, date('Y-m-d'));
        $is_holiday = $holiday_name ? 1 : 0;

        // Insert Invoice
        $stmt = $conn->prepare("INSERT INTO invoices (customer_id, total, delivery_cost, delivery_city, discount_percent, discount_amount, payment_method, amount_received, change_due, is_holiday, holiday_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $discount_percent = 0; // Simplified, or calculate if needed
        $stmt->bind_param("iddsddsddis", $customer_id, $total, $delivery_cost, $delivery_city, $discount_percent, $discount_amount, $payment_method, $amount_received, $change_due, $is_holiday, $holiday_name);
        
        $stmt->execute();
        $invoiceId = $stmt->insert_id;
        $stmt->close();
        
        // Insert Items & Update Stock
        $stmt_item = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, price, cost_price) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_update = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");

        foreach ($verified_items as $item) {
            $stmt_item->bind_param("iisidd", $invoiceId, $item['id'], $item['name'], $item['quantity'], $item['price'], $item['cost_price']);
            $stmt_item->execute();

            $stmt_update->bind_param("ii", $item['quantity'], $item['id']);
            $stmt_update->execute();
        }
        $stmt_item->close();
        $stmt_update->close();

        $barcode = 'INV' . str_pad($invoiceId, 8, '0', STR_PAD_LEFT);
        $updateStmt = $conn->prepare("UPDATE invoices SET barcode = ? WHERE id = ?");
        $updateStmt->bind_param("si", $barcode, $invoiceId);
        $updateStmt->execute();
        $updateStmt->close();

        $conn->commit();
        create_notification($conn, __('notification_invoice_created') . ": " . $barcode, "new_sale");
        echo json_encode(['success' => true, 'message' => __('invoice_created_success'), 'invoice_id' => $invoiceId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('invoice_creation_fail') . ': ' . $e->getMessage()]);
    }
}

function createInvoice($conn) {
    checkout($conn);
}

function getInvoice($conn) {
    $invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($invoice_id === 0) {
        echo json_encode(['success' => false, 'message' => __('invoice_id_invalid')]);
        return;
    }

    $stmt = $conn->prepare("SELECT i.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address 
                            FROM invoices i 
                            LEFT JOIN customers c ON i.customer_id = c.id 
                            WHERE i.id = ?");
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();
    $stmt->close();

    if (!$invoice) {
        echo json_encode(['success' => false, 'message' => __('invoice_not_found')]);
        return;
    }

    $stmt = $conn->prepare("SELECT ii.*, 
                        COALESCE(ii.product_name, p.name, 'منتج محذوف') as product_name 
                        FROM invoice_items ii 
                        LEFT JOIN products p ON ii.product_id = p.id 
                        WHERE ii.invoice_id = ?");
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
    
    $invoice['items'] = $items;

    echo json_encode(['success' => true, 'data' => $invoice]);
}

function getInvoices($conn) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $searchDate = isset($_GET['searchDate']) && !empty($_GET['searchDate']) ? $_GET['searchDate'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;

    $baseSql = "FROM invoices i 
                LEFT JOIN customers c ON i.customer_id = c.id
                LEFT JOIN refunds r ON i.id = r.invoice_id
                WHERE 1=1";
    
    $params = [];
    $types = '';

    if (!empty($searchDate)) {
        $baseSql .= " AND DATE(i.created_at) = ?";
        $params[] = $searchDate;
        $types .= 's';
    }

    if (!empty($search)) {
        $baseSql .= " AND (i.id LIKE ? OR c.name LIKE ? OR i.barcode LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    $countSql = "SELECT COUNT(DISTINCT i.id) as total " . $baseSql;
    $stmt = $conn->prepare($countSql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total_invoices = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $offset = ($page - 1) * $limit;
    $dataSql = "SELECT DISTINCT i.id, i.total, i.created_at, c.name as customer_name,
                CASE WHEN r.id IS NOT NULL THEN 1 ELSE 0 END as is_refunded "
             . $baseSql 
             . " ORDER BY i.created_at DESC LIMIT ? OFFSET ?";
    
    $dataTypes = $types . 'ii';
    $dataParams = array_merge($params, [$limit, $offset]);

    $stmt = $conn->prepare($dataSql);
    $stmt->bind_param($dataTypes, ...$dataParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = [];
    while ($row = $result->fetch_assoc()) {
        $invoices[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $invoices, 'total_invoices' => $total_invoices]);
}

function getDeliverySettings($conn) {
    $sql = "SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('deliveryInsideCity', 'deliveryOutsideCity')";
    $result = $conn->query($sql);
    
    $settings = [
        'deliveryInsideCity' => '10',
        'deliveryOutsideCity' => '30'
    ];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $settings]);
}

function updateDeliverySettings($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['deliveryInsideCity']) || !isset($data['deliveryOutsideCity'])) {
        echo json_encode(['success' => false, 'message' => __('values_required')]);
        return;
    }
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        $settings = [
            'deliveryInsideCity' => $data['deliveryInsideCity'],
            'deliveryOutsideCity' => $data['deliveryOutsideCity']
        ];
        
        foreach ($settings as $name => $value) {
            $stmt->bind_param("ss", $name, $value);
            $stmt->execute();
        }
        
        $stmt->close();
        $conn->commit();
        
        create_notification($conn, sprintf(__('notification_delivery_settings_updated'), $data['deliveryInsideCity'], $data['deliveryOutsideCity']), "settings_update");
        echo json_encode(['success' => true, 'message' => __('delivery_settings_update_success')]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('settings_update_fail') . ': ' . $e->getMessage()]);
    }
}

function getLowStockProducts($conn) {
    $settings_sql = "SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('low_quantity_alert', 'critical_quantity_alert', 'last_stock_check_notification')";
    $settings_result = $conn->query($settings_sql);
    $quantity_settings = [];
    while ($row = $settings_result->fetch_assoc()) {
        $quantity_settings[$row['setting_name']] = (int)$row['setting_value'];
    }
    $low_alert = $quantity_settings['low_quantity_alert'] ?? 10;
    $critical_alert = $quantity_settings['critical_quantity_alert'] ?? 5;

    $sql = "SELECT id, name, quantity, category_id 
            FROM products 
            WHERE quantity <= ?
            ORDER BY quantity ASC, name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $low_alert);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
    
    $outOfStock = array_filter($products, function($p) { return $p['quantity'] == 0; });
    $critical = array_filter($products, function($p) use ($critical_alert) { return $p['quantity'] > 0 && $p['quantity'] <= $critical_alert; });
    $low = array_filter($products, function($p) use ($critical_alert, $low_alert) { return $p['quantity'] > $critical_alert && $p['quantity'] <= $low_alert; });
    
    $last_check_time = $quantity_settings['last_stock_check_notification'] ?? 0;
    $last_check_date = date('Y-m-d', $last_check_time);
    $today_date = date('Y-m-d');
    $total_low_stock = count($outOfStock) + count($critical) + count($low);
    
    if ($last_check_date !== $today_date && $total_low_stock > 0) {
        create_notification($conn, "يوجد {$total_low_stock} منتجًا على وشك النفاد.", "low_stock");
        $conn->query("INSERT INTO settings (setting_name, setting_value) VALUES ('last_stock_check_notification', '" . time() . "') ON DUPLICATE KEY UPDATE setting_value = '" . time() . "'");
    }

    echo json_encode([
        'success' => true,
        'data' => $products,
        'outOfStock' => array_values($outOfStock),
        'critical' => array_values($critical),
        'low' => array_values($low),
        'count' => count($products),
        'outOfStockCount' => count($outOfStock),
        'criticalCount' => count($critical),
        'lowCount' => count($low)
    ]);
}

function getDashboardStats($conn) {
    try {
        $stats = [];

        // Get current business day
        $day_stmt = $conn->prepare("SELECT start_time FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        $day_stmt->execute();
        $day_result = $day_stmt->get_result();
        $current_day = $day_result->fetch_assoc();
        $day_stmt->close();
        $start_time_filter = $current_day ? "i.created_at >= '" . $current_day['start_time'] . "'" : "1=0";

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        // Use calendar day for today stats, not business day
        $result = $conn->query("
            SELECT 
                COUNT(DISTINCT i.id) as total_orders,
                COALESCE(SUM(i.total), 0) as revenue,
                COALESCE(SUM(ii.quantity * ii.cost_price), 0) as total_cogs,
                COALESCE(SUM(i.delivery_cost), 0) as total_delivery
            FROM invoices i 
            LEFT JOIN invoice_items ii ON i.id = ii.invoice_id 
            WHERE DATE(i.created_at) = '$today'
        ");
        
        $todayData = $result ? $result->fetch_assoc() : ['total_orders' => 0, 'revenue' => 0, 'total_cogs' => 0, 'total_delivery' => 0];
        
        // Fetch today's expenses
        $expRes = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE expense_date = '$today'");
        $todayExpenses = $expRes ? $expRes->fetch_assoc()['total'] : 0;

        // Fetch today's rental payments
        $rentRes = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM rental_payments WHERE payment_date = '$today'");
        $todayRent = $rentRes ? $rentRes->fetch_assoc()['total'] : 0;

        // Fetch current cycle expenses for additional info
        $cycle = get_expense_cycle_dates($conn);
        $cycleStart = $cycle['start'];
        $cycleEnd = $cycle['end'];
        
        $cycleExpRes = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE expense_date BETWEEN '$cycleStart' AND '$cycleEnd'");
        $cycleExpenses = $cycleExpRes ? $cycleExpRes->fetch_assoc()['total'] : 0;

        $cycleRentRes = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM rental_payments WHERE payment_date BETWEEN '$cycleStart' AND '$cycleEnd'");
        $cycleRent = $cycleRentRes ? $cycleRentRes->fetch_assoc()['total'] : 0;

        $totalOtherCosts = floatval($todayExpenses) + floatval($todayRent);

        $stats['todayRevenue'] = floatval($todayData['revenue']);
        $stats['todayCost'] = floatval($todayData['total_cogs']) + floatval($todayData['total_delivery']) + $totalOtherCosts;
        $stats['todayProfit'] = $stats['todayRevenue'] - $stats['todayCost'];
        
        $stats['currentCycleExpenses'] = floatval($cycleExpenses) + floatval($cycleRent);
        $stats['expenseCycleType'] = $cycle['cycle'];
        $stats['todayMargin'] = $stats['todayRevenue'] > 0 ? ($stats['todayProfit'] / $stats['todayRevenue'] * 100) : 0;
        $stats['todayOrders'] = $todayData['total_orders'];
        
        $result = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE DATE(created_at) = '$yesterday'");
        $stats['yesterdayOrders'] = $result ? $result->fetch_assoc()['count'] : 0;
        
        $stats['avgOrderValue'] = $stats['todayOrders'] > 0 ? $stats['todayRevenue'] / $stats['todayOrders'] : 0;
        
        $thisMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));
        
        $result = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM invoices WHERE DATE_FORMAT(created_at, '%Y-%m') = '$thisMonth'");
        $stats['thisMonthRevenue'] = $result ? $result->fetch_assoc()['revenue'] : 0;
        
        $result = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM invoices WHERE DATE_FORMAT(created_at, '%Y-%m') = '$lastMonth'");
        $stats['lastMonthRevenue'] = $result ? $result->fetch_assoc()['revenue'] : 0;
        
        $result = $conn->query("SELECT COUNT(*) as count FROM products");
        $stats['totalProducts'] = $result ? $result->fetch_assoc()['count'] : 0;
        
        $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE quantity <= 10 AND quantity > 0");
        $stats['lowStock'] = $result ? $result->fetch_assoc()['count'] : 0;
        
        $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE quantity = 0");
        $stats['outOfStock'] = $result ? $result->fetch_assoc()['count'] : 0;
        
        $result = $conn->query("SELECT COUNT(*) as count FROM customers");
        $stats['totalCustomers'] = $result ? $result->fetch_assoc()['count'] : 0;
        
        $result = $conn->query("SELECT COUNT(*) as count FROM customers WHERE DATE_FORMAT(created_at, '%Y-%m') = '$thisMonth'");
        $stats['newCustomersThisMonth'] = $result ? $result->fetch_assoc()['count'] : 0;
        
        echo json_encode(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('fetch_stats_error') . ': ' . $e->getMessage()]);
    }
}

function getSalesChart($conn) {
    try {
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;

        $day_stmt = $conn->prepare("SELECT start_time FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        $day_stmt->execute();
        $day_result = $day_stmt->get_result();
        $current_day = $day_result->fetch_assoc();
        $day_stmt->close();

        $where_clause = "created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        if ($current_day) {
            $where_clause = "created_at >= '" . $current_day['start_time'] . "'";
        }
        
        $sql = "SELECT DATE(created_at) as date, 
                       COUNT(*) as orders, 
                       COALESCE(SUM(total), 0) as revenue
                FROM invoices
                WHERE {$where_clause}
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $stmt = $conn->prepare($sql);
        if (!$current_day) {
            $stmt->bind_param("i", $days);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        $stmt->close();
        
        echo json_encode(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('fetch_sales_data_error') . ': ' . $e->getMessage()]);
    }
}

function getTopProducts($conn) {
    try {
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;

        $day_stmt = $conn->prepare("SELECT start_time FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        $day_stmt->execute();
        $day_result = $day_stmt->get_result();
        $current_day = $day_result->fetch_assoc();
        $day_stmt->close();

        $where_clause = "i.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        if ($current_day) {
            $where_clause = "i.created_at >= '" . $current_day['start_time'] . "'";
        }
        
        $sql = "SELECT p.id, p.name, p.quantity as stock,
                       COALESCE(SUM(ii.quantity), 0) as units_sold,
                       COALESCE(SUM(ii.quantity * ii.price), 0) as revenue
                FROM products p
                LEFT JOIN invoice_items ii ON p.id = ii.product_id
                LEFT JOIN invoices i ON ii.invoice_id = i.id
                WHERE {$where_clause} OR i.created_at IS NULL
                GROUP BY p.id
                HAVING units_sold > 0
                ORDER BY units_sold DESC
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        if (!$current_day) {
            $stmt->bind_param("i", $days);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        $stmt->close();
        
        echo json_encode(['success' => true, 'data' => $products]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('fetch_top_products_error') . ': ' . $e->getMessage()]);
    }
}

function getCategorySales($conn) {
    try {
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

        $day_stmt = $conn->prepare("SELECT start_time FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        $day_stmt->execute();
        $day_result = $day_stmt->get_result();
        $current_day = $day_result->fetch_assoc();
        $day_stmt->close();

        $where_clause = "i.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        if ($current_day) {
            $where_clause = "i.created_at >= '" . $current_day['start_time'] . "'";
        }
        
        $sql = "SELECT c.name as category,
                       COALESCE(SUM(ii.quantity * ii.price), 0) as revenue,
                       COALESCE(SUM(ii.quantity), 0) as units_sold
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                LEFT JOIN invoice_items ii ON p.id = ii.product_id
                LEFT JOIN invoices i ON ii.invoice_id = i.id
                WHERE {$where_clause} OR i.created_at IS NULL
                GROUP BY c.id
                HAVING revenue > 0
                ORDER BY revenue DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$current_day) {
            $stmt->bind_param("i", $days);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        $stmt->close();
        
        echo json_encode(['success' => true, 'data' => $categories]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('fetch_category_sales_error') . ': ' . $e->getMessage()]);
    }
}

function getRecentInvoices($conn) {
    try {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

        $day_stmt = $conn->prepare("SELECT start_time FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        $day_stmt->execute();
        $day_result = $day_stmt->get_result();
        $current_day = $day_result->fetch_assoc();
        $day_stmt->close();
        $start_time_filter = $current_day ? "i.created_at >= '" . $current_day['start_time'] . "'" : "1=1";
        
        $sql = "SELECT i.id, i.total, i.created_at,
                       c.name as customer_name
                FROM invoices i
                LEFT JOIN customers c ON i.customer_id = c.id
                WHERE {$start_time_filter}
                ORDER BY i.created_at DESC
                LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $invoices = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $invoices[] = $row;
            }
        }
        $stmt->close();
        
        echo json_encode(['success' => true, 'data' => $invoices]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('fetch_recent_invoices_error') . ': ' . $e->getMessage()]);
    }
}

function getTopCustomers($conn) {
    try {
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

        $day_stmt = $conn->prepare("SELECT start_time FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        $day_stmt->execute();
        $day_result = $day_stmt->get_result();
        $current_day = $day_result->fetch_assoc();
        $day_stmt->close();

        $where_clause = "i.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        if ($current_day) {
            $where_clause = "i.created_at >= '" . $current_day['start_time'] . "'";
        }
        
        $sql = "SELECT c.id, c.name, c.phone,
                       COUNT(i.id) as order_count,
                       COALESCE(SUM(i.total), 0) as total_spent,
                       MAX(i.created_at) as last_purchase
                FROM customers c
                LEFT JOIN invoices i ON c.id = i.customer_id
                WHERE {$where_clause}
                GROUP BY c.id
                HAVING order_count > 0
                ORDER BY total_spent DESC
                LIMIT 5";
        
        $stmt = $conn->prepare($sql);
        if (!$current_day) {
            $stmt->bind_param("i", $days);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $customers = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $customers[] = $row;
            }
        }
        $stmt->close();
        
        echo json_encode(['success' => true, 'data' => $customers]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('fetch_top_customers_error') . ': ' . $e->getMessage()]);
    }
}

function create_notification($conn, $message, $type) {
    $stmt = $conn->prepare("INSERT INTO notifications (message, type) VALUES (?, ?)");
    $stmt->bind_param("ss", $message, $type);
    $stmt->execute();
    $stmt->close();
}

function getHolidays($conn) {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $stmt = $conn->prepare("SELECT * FROM holidays WHERE YEAR(date) = ? ORDER BY date ASC");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $holidays = [];
    while ($row = $result->fetch_assoc()) {
        $holidays[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'data' => $holidays]);
}

function addHoliday($conn) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['name']) || empty($data['date'])) {
        echo json_encode(['success' => false, 'message' => __('name_date_required')]);
        return;
    }
    $stmt = $conn->prepare("INSERT INTO holidays (name, date) VALUES (?, ?)");
    $stmt->bind_param("ss", $data['name'], $data['date']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => __('holiday_added_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('holiday_add_fail') . ': ' . $conn->error]);
    }
    $stmt->close();
}

function updateHoliday($conn) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id']) || empty($data['name']) || empty($data['date'])) {
        echo json_encode(['success' => false, 'message' => __('all_fields_required')]);
        return;
    }
    $stmt = $conn->prepare("UPDATE holidays SET name = ?, date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $data['name'], $data['date'], $data['id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => __('holiday_updated_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('update_fail')]);
    }
    $stmt->close();
}

function deleteHoliday($conn) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => __('holiday_id_required')]);
        return;
    }
    
    // Get holiday date before deletion to update invoices
    $stmtDate = $conn->prepare("SELECT date FROM holidays WHERE id = ?");
    $stmtDate->bind_param("i", $data['id']);
    $stmtDate->execute();
    $hDate = $stmtDate->get_result()->fetch_assoc()['date'] ?? null;
    $stmtDate->close();

    $stmt = $conn->prepare("DELETE FROM holidays WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    if ($stmt->execute()) {
        if ($hDate) {
            // Update invoices for this specific date
            $updateStmt = $conn->prepare("UPDATE invoices SET is_holiday = 0, holiday_name = NULL WHERE DATE(created_at) = ? AND holiday_name IS NOT NULL AND holiday_name NOT LIKE 'عطلة أسبوعية%'");
            $updateStmt->bind_param("s", $hDate);
            $updateStmt->execute();
            $updateStmt->close();
        }
        echo json_encode(['success' => true, 'message' => __('holiday_deleted_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('delete_fail')]);
    }
    $stmt->close();
}

function syncMoroccanHolidays($conn) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
    $results = syncHolidaysInternal($conn, $year, true); // Force sync
    // Also sync next year to be proactive
    syncHolidaysInternal($conn, $year + 1, false);

    if ($results['success']) {
        $today = date('Y-m-d H:i');
        $conn->query("INSERT INTO settings (setting_name, setting_value) VALUES ('last_holiday_sync_date', '$today') ON DUPLICATE KEY UPDATE setting_value = '$today'");
        echo json_encode(['success' => true, 'message' => __('holidays_synced_success'), 'count' => $results['count']]);
    } else {
        echo json_encode(['success' => false, 'message' => $results['message']]);
    }
}

function syncHolidaysInternal($conn, $year, $force = false) {
    // Check if religious holidays for this year were already synced
    // To respect user deletions, we don't re-sync religious holidays if they exist
    $res = $conn->query("SELECT id FROM holidays WHERE YEAR(date) = $year AND name IN ('فاتح محرم', 'عيد المولد النبوي', 'عيد الفطر', 'عيد الأضحى') LIMIT 1");
    $alreadySynced = ($res && $res->num_rows > 0);

    if ($alreadySynced && !$force) {
        // Still update fixed holidays as they are stable
        $fixedHolidays = [
            ['01-01', 'رأس السنة الميلادية'], ['01-11', 'تقديم وثيقة الاستقلال'],
            ['01-14', 'رأس السنة الأمازيغية'], ['05-01', 'عيد الشغل'],
            ['07-30', 'عيد العرش'], ['08-14', 'ذكرى استرجاع إقليم وادي الذهب'],
            ['08-20', 'ذكرى ثورة الملك والشعب'], ['08-21', 'عيد الشباب'],
            ['11-06', 'ذكرى المسيرة الخضراء'], ['11-18', 'عيد الاستقلال']
        ];
        foreach ($fixedHolidays as $h) {
            $date = $year . '-' . $h[0];
            $stmt = $conn->prepare("INSERT IGNORE INTO holidays (name, date) VALUES (?, ?)");
            $stmt->bind_param("ss", $h[1], $date);
            $stmt->execute();
            $stmt->close();
        }
        return ['success' => true, 'count' => 0, 'message' => __('year_already_synced')];
    }

    $fixedHolidays = [
        ['01-01', 'رأس السنة الميلادية'], ['01-11', 'تقديم وثيقة الاستقلال'],
        ['01-14', 'رأس السنة الأمازيغية'], ['05-01', 'عيد الشغل'],
        ['07-30', 'عيد العرش'], ['08-14', 'ذكرى استرجاع إقليم وادي الذهب'],
        ['08-20', 'ذكرى ثورة الملك والشعب'], ['08-21', 'عيد الشباب'],
        ['11-06', 'ذكرى المسيرة الخضراء'], ['11-18', 'عيد الاستقلال']
    ];

    $count = 0;
    foreach ($fixedHolidays as $h) {
        $date = $year . '-' . $h[0];
        $stmt = $conn->prepare("INSERT IGNORE INTO holidays (name, date) VALUES (?, ?)");
        $stmt->bind_param("ss", $h[1], $date);
        $stmt->execute();
        if ($stmt->affected_rows > 0) $count++;
        $stmt->close();
    }

    // Parallelize religious holidays check
    $religiousHolidays = [
        ['01-01', 'فاتح محرم'], ['12-03', 'عيد المولد النبوي'],
        ['13-03', 'عيد المولد النبوي (اليوم الثاني)'], ['01-10', 'عيد الفطر'],
        ['02-10', 'عيد الفطر (اليوم الثاني)'], ['10-12', 'عيد الأضحى'],
        ['11-12', 'عيد الأضحى (اليوم الثاني)']
    ];

    $mh = curl_multi_init();
    $requests = [];
    $hijriYears = [$year - 580, $year - 579, $year - 578];

    foreach ($hijriYears as $hYear) {
        foreach ($religiousHolidays as $rh) {
            $hDate = $rh[0] . '-' . $hYear;
            $url = "https://api.aladhan.com/v1/hToG/" . $hDate;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_multi_add_handle($mh, $ch);
            $requests[] = ['handle' => $ch, 'name' => $rh[1]];
        }
    }

    $active = null;
    do { $mrc = curl_multi_exec($mh, $active); } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($mh) != -1) {
            do { $mrc = curl_multi_exec($mh, $active); } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    $anySuccess = false;
    foreach ($requests as $request) {
        $response = curl_multi_getcontent($request['handle']);
        $info = curl_getinfo($request['handle']);
        
        if ($response && $info['http_code'] == 200) {
            $data = json_decode($response, true);
            if (isset($data['data']['gregorian']['date'])) {
                $anySuccess = true;
                $gDateParts = explode('-', $data['data']['gregorian']['date']);
                $gDate = $gDateParts[2] . '-' . $gDateParts[1] . '-' . $gDateParts[0];
                if (strpos($gDate, (string)$year) === 0) {
                    $stmt = $conn->prepare("INSERT IGNORE INTO holidays (name, date) VALUES (?, ?)");
                    $stmt->bind_param("ss", $request['name'], $gDate);
                    $stmt->execute();
                    if ($stmt->affected_rows > 0) $count++;
                    $stmt->close();
                }
            }
        }
        curl_multi_remove_handle($mh, $request['handle']);
        curl_close($request['handle']);
    }
    curl_multi_close($mh);

    if (!empty($requests) && !$anySuccess) {
        return ['success' => false, 'message' => __('aladhan_api_error')];
    }

    return ['success' => true, 'count' => $count];
}

function checkUpcomingHolidays($conn) {
    // Only check if we haven't checked today
    $today = date('Y-m-d');
    $lastCheckDate = '';
    $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'last_holiday_sync_auto_date'");
    if ($res && $res->num_rows > 0) {
        $lastCheckDate = $res->fetch_assoc()['setting_value'];
    }

    if ($lastCheckDate !== $today) {
        // We attempt a sync for current and next year to be proactive (intelligent sync)
        $currentYear = (int)date('Y');
        $results = syncHolidaysInternal($conn, $currentYear);
        syncHolidaysInternal($conn, $currentYear + 1);
        
        // If successful or if it's just fixed holidays (at least some progress), we mark as checked today
        if ($results['success']) {
            $now = date('Y-m-d H:i');
            $conn->query("INSERT INTO settings (setting_name, setting_value) VALUES ('last_holiday_sync_date', '$now') ON DUPLICATE KEY UPDATE setting_value = '$now'");
            $conn->query("INSERT INTO settings (setting_name, setting_value) VALUES ('last_holiday_sync_auto_date', '$today') ON DUPLICATE KEY UPDATE setting_value = '$today'");
        } else {
            // If failed (likely no internet), we don't update last_holiday_sync_auto_date 
            // so it tries again on next request.
            // But we might want to avoid spamming if internet is down for a long time.
            // For now, let's just create a system notification about the failure if it's the first time today.
            $notifRes = $conn->query("SELECT id FROM notifications WHERE type = 'holiday_sync_failed' AND DATE(created_at) = '$today'");
            if ($notifRes->num_rows == 0) {
                create_notification($conn, "⚠️ فشل التحقق التلقائي من العطلات بسبب عدم توفر اتصال بالإنترنت. يرجى التحقق يدوياً عند توفر الاتصال.", "holiday_sync_failed");
            }
        }
    }
}

function getNotifications($conn) {
    cleanOldNotifications($conn);
    
    $limit = 20; 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $offset = ($page - 1) * $limit;

    $whereClause = "";
    if ($filter === 'unread') {
        $whereClause = "WHERE status = 'unread'";
    } elseif ($filter === 'read') {
        $whereClause = "WHERE status = 'read'";
    }

    $countSql = "SELECT COUNT(*) as count FROM notifications $whereClause";
    $total_result = $conn->query($countSql);
    $total_rows = $total_result->fetch_assoc()['count'];
    $total_pages = ceil($total_rows / $limit);

    $unread_result = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'");
    $unread_count = $unread_result->fetch_assoc()['count'];

    $sql = "SELECT * FROM notifications $whereClause ORDER BY (status = 'unread') DESC, created_at DESC LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success' => true, 
        'data' => $notifications,
        'unread_count' => $unread_count,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => $total_rows
        ]
    ]);
}

function cleanOldNotifications($conn) {
    try {
        $sql = "DELETE FROM notifications WHERE created_at <= (NOW() - INTERVAL 30 DAY)";
        $conn->query($sql);
    } catch (Exception $e) {
        error_log("Error cleaning old notifications: " . $e->getMessage());
    }
}

function markNotificationRead($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? (int)$data['id'] : 0;

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => __('update_fail')]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => __('invalid_id')]);
    }
}

function deleteNotification($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? (int)$data['id'] : 0;

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => __('delete_fail')]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => __('invalid_id')]);
    }
}

function markAllNotificationsRead($conn) {
    if ($conn->query("UPDATE notifications SET status = 'read' WHERE status = 'unread'")) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => __('update_fail')]);
    }
}

function checkExpiringProducts($conn) {
    $check_sql = "SELECT id, name, removed_at 
                  FROM removed_products 
                  WHERE removed_at <= (NOW() - INTERVAL 29 DAY) 
                  AND removed_at > (NOW() - INTERVAL 30 DAY)";
    
    $result = $conn->query($check_sql);
    
    if ($result && $result->num_rows > 0) {
        $expiring_products = [];
        while ($row = $result->fetch_assoc()) {
            $expiring_products[] = $row['name'];
        }
        
        if (count($expiring_products) > 0) {
            $last_check_query = "SELECT setting_value FROM settings WHERE setting_name = 'last_expiry_notification_date'";
            $last_check_result = $conn->query($last_check_query);
            $last_check_date = $last_check_result->num_rows > 0 ? $last_check_result->fetch_assoc()['setting_value'] : '';
            
            $today = date('Y-m-d');
            
            if ($last_check_date !== $today) {
                $product_list = implode('، ', array_slice($expiring_products, 0, 5));
                if (count($expiring_products) > 5) {
                    $product_list .= ' و ' . (count($expiring_products) - 5) . ' منتجات أخرى';
                }
                
                create_notification(
                    $conn, 
                    "⚠️ تحذير: سيتم الحذف النهائي خلال 24 ساعة: " . $product_list, 
                    "product_expiry_warning"
                );
                
                $conn->query("INSERT INTO settings (setting_name, setting_value) 
                             VALUES ('last_expiry_notification_date', '$today') 
                             ON DUPLICATE KEY UPDATE setting_value = '$today'");
            }
        }
    }
}

function checkRentalDue($conn) {
    try {
        $settings_query = "SELECT setting_name, setting_value FROM settings WHERE setting_name LIKE 'rental%'";
        $result = $conn->query($settings_query);
        
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
        
        if (!isset($settings['rentalEnabled']) || $settings['rentalEnabled'] != '1') {
            echo json_encode(['success' => true, 'message' => __('rental_disabled')]);
            return;
        }
        
        $currentMonth = date('Y-m');
        if (isset($settings['rentalPaidMonth']) && $settings['rentalPaidMonth'] === $currentMonth) {
            echo json_encode(['success' => true, 'notification_sent' => false, 'message' => __('rental_paid_this_month'), 'paid_this_month' => true]);
            return;
        }
        
        if (!isset($settings['rentalPaymentDate']) || !isset($settings['rentalType'])) {
            echo json_encode(['success' => false, 'message' => __('rental_settings_incomplete')]);
            return;
        }
        
        $paymentDate = $settings['rentalPaymentDate']; // Y-m-d format
        $rentalType = $settings['rentalType']; // 'monthly' or 'yearly'
        $reminderDays = (int)($settings['rentalReminderDays'] ?? 7);
        $lastNotification = (int)($settings['rentalLastNotification'] ?? 0);
        $currentTime = time();
        
        $lastNotificationDate = date('Y-m-d', $lastNotification);
        $todayDate = date('Y-m-d');
        
        if ($lastNotificationDate === $todayDate) {
            echo json_encode(['success' => true, 'message' => __('already_notified_today')]);
            return;
        }
        
        $paymentTimestamp = strtotime($paymentDate);
        $today = strtotime(date('Y-m-d'));
        
        $daysUntilDue = floor(($paymentTimestamp - $today) / (60 * 60 * 24));
        
        $shouldNotify = false;
        $notificationMessage = '';
        $notificationType = 'rental_reminder';
        
        if ($daysUntilDue > 0 && $daysUntilDue <= $reminderDays) {
            $amount = number_format((float)($settings['rentalAmount'] ?? 0), 2);
            $currency = $settings['currency'] ?? 'MAD';
            
            $notificationMessage = sprintf(__('rental_reminder_msg'), $daysUntilDue, $amount, $currency);
            
            if (isset($settings['rentalLandlordName']) && !empty($settings['rentalLandlordName'])) {
                $notificationMessage .= "\n" . __('rental_landlord') . ": " . $settings['rentalLandlordName'];
            }
            
            $shouldNotify = true;
        }
        elseif ($daysUntilDue == 0) {
            $amount = number_format((float)($settings['rentalAmount'] ?? 0), 2);
            $currency = $settings['currency'] ?? 'MAD';
            
            $notificationMessage = sprintf(__('rental_due_today_msg'), $amount, $currency);
            
            if (isset($settings['rentalLandlordPhone']) && !empty($settings['rentalLandlordPhone'])) {
                $notificationMessage .= "\n" . __('rental_landlord_phone') . ": " . $settings['rentalLandlordPhone'];
            }
            
            $shouldNotify = true;
            $notificationType = 'rental_due_today';
        }
        elseif ($daysUntilDue < 0) {
            $daysOverdue = abs($daysUntilDue);
            $amount = number_format((float)($settings['rentalAmount'] ?? 0), 2);
            $currency = $settings['currency'] ?? 'MAD';
            
            $notificationMessage = sprintf(__('rental_overdue_msg'), $daysOverdue, $amount, $currency);
            
            if (isset($settings['rentalLandlordPhone']) && !empty($settings['rentalLandlordPhone'])) {
                $notificationMessage .= "\n" . __('rental_landlord_phone') . ": " . $settings['rentalLandlordPhone'];
            }
            
            $shouldNotify = true;
            $notificationType = 'rental_overdue';
            
            if ($daysOverdue >= 7) {
                $nextPaymentDate = calculateNextPaymentDate($paymentDate, $rentalType);
                $conn->query("UPDATE settings SET setting_value = '{$nextPaymentDate}' WHERE setting_name = 'rentalPaymentDate'");
                
                $nextDateFormatted = date('Y/m/d', strtotime($nextPaymentDate));
                create_notification($conn, sprintf(__('rental_next_date_updated'), $nextDateFormatted), "rental_auto_update");
            }
        }
        
        if ($shouldNotify) {
            create_notification($conn, $notificationMessage, $notificationType);
            
            $conn->query("UPDATE settings SET setting_value = '{$currentTime}' WHERE setting_name = 'rentalLastNotification'");
            
            echo json_encode([
                'success' => true, 
                'notification_sent' => true,
                'days_until_due' => $daysUntilDue,
                'message' => $notificationMessage
            ]);
        } else {
            echo json_encode([
                'success' => true, 
                'notification_sent' => false,
                'days_until_due' => $daysUntilDue,
                'message' => __('no_notification_needed')
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('error_checking_rental') . ': ' . $e->getMessage()]);
    }
}

function markRentalPaidThisMonth($conn) {
    try {
        $settings_query = "SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('rentalPaymentDate','rentalType','rentalAmount','currency','rentalLandlordName','rentalLandlordPhone','rentalNotes')";
        $result = $conn->query($settings_query);
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
        if (!isset($settings['rentalPaymentDate']) || !isset($settings['rentalType'])) {
            echo json_encode(['success' => false, 'message' => __('rental_settings_incomplete')]);
            return;
        }
        $currentMonth = date('Y-m');
        $nextPaymentDate = calculateNextPaymentDate($settings['rentalPaymentDate'], $settings['rentalType']);
        
        $conn->begin_transaction();
        $conn->query("UPDATE settings SET setting_value = '{$conn->real_escape_string($nextPaymentDate)}' WHERE setting_name = 'rentalPaymentDate'");
        $conn->query("INSERT INTO settings (setting_name, setting_value) VALUES ('rentalPaidMonth', '{$conn->real_escape_string($currentMonth)}') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $conn->query("UPDATE settings SET setting_value = '" . time() . "' WHERE setting_name = 'rentalLastNotification'");
        
        ensureRentalPaymentsTable($conn);
        $stmt = $conn->prepare("INSERT INTO rental_payments (paid_month, payment_date, amount, currency, rental_type, landlord_name, landlord_phone, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $paidMonth = $currentMonth;
        $paymentDate = date('Y-m-d');
        $amount = (float)($settings['rentalAmount'] ?? 0);
        $currency = $settings['currency'] ?? 'MAD';
        $rentalType = $settings['rentalType'] ?? 'monthly';
        $landlordName = $settings['rentalLandlordName'] ?? '';
        $landlordPhone = $settings['rentalLandlordPhone'] ?? '';
        $notes = $settings['rentalNotes'] ?? '';
        $stmt->bind_param('ssdsssss', $paidMonth, $paymentDate, $amount, $currency, $rentalType, $landlordName, $landlordPhone, $notes);
        $stmt->execute();
        $stmt->close();
        
        create_notification($conn, "✅ " . __('rental_paid_success') . ". الموعد القادم: " . date('Y/m/d', strtotime($nextPaymentDate)), "rental_paid");
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => __('rental_paid_success'), 'next_payment_date' => $nextPaymentDate, 'paid_month' => $currentMonth]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('payment_record_error') . ': ' . $e->getMessage()]);
    }
}

function ensureRentalPaymentsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS rental_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        paid_month VARCHAR(7) NOT NULL,
        payment_date DATE NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) NOT NULL,
        rental_type ENUM('monthly','yearly') NOT NULL,
        landlord_name VARCHAR(255),
        landlord_phone VARCHAR(50),
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
}

function getRentalPayments($conn) {
    try {
        ensureRentalPaymentsTable($conn);
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        if ($limit < 1) $limit = 50;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;
        
        $countRes = $conn->query("SELECT COUNT(*) as total FROM rental_payments");
        $total = ($countRes && $countRes->num_rows) ? (int)$countRes->fetch_assoc()['total'] : 0;
        
        $stmt = $conn->prepare("SELECT id, paid_month, payment_date, amount, currency, rental_type, landlord_name, landlord_phone, notes, created_at FROM rental_payments ORDER BY payment_date DESC, id DESC LIMIT ? OFFSET ?");
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'data' => $rows,
            'pagination' => [
                'total' => $total,
                'limit' => $limit,
                'current_page' => $page,
                'total_pages' => $limit ? ceil($total / $limit) : 1
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => __('fetch_payments_error') . ': ' . $e->getMessage()]);
    }
}

function calculateNextPaymentDate($currentDate, $rentalType) {
    $date = new DateTime($currentDate);
    
    if ($rentalType === 'monthly') {
        $date->modify('+1 month');
    } elseif ($rentalType === 'yearly') {
        $date->modify('+1 year');
    }
    
    return $date->format('Y-m-d');
}

function getExpenses($conn) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM expenses ORDER BY expense_date DESC, created_at DESC LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
    $stmt->close();

    $countRes = $conn->query("SELECT COUNT(*) as total FROM expenses");
    $total = $countRes->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'data' => $expenses,
        'pagination' => [
            'total' => $total,
            'limit' => $limit,
            'current_page' => $page,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
}

function addExpense($conn) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['title']) || empty($data['amount']) || empty($data['expense_date'])) {
        echo json_encode(['success' => false, 'message' => __('all_fields_required')]);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO expenses (title, amount, category, expense_date, notes, paid_from_drawer) VALUES (?, ?, ?, ?, ?, ?)");
    $category = $data['category'] ?? 'general';
    $notes = $data['notes'] ?? '';
    $paid_from_drawer = isset($data['paid_from_drawer']) && $data['paid_from_drawer'] ? 1 : 0;
    
    $stmt->bind_param("sdsssi", $data['title'], $data['amount'], $category, $data['expense_date'], $notes, $paid_from_drawer);

    if ($stmt->execute()) {
        $msg = sprintf(__('notification_expense_added'), $data['title'], $data['amount']);
        if ($paid_from_drawer) $msg .= " " . __('deducted_from_drawer');
        create_notification($conn, $msg, "expense_add");
        echo json_encode(['success' => true, 'message' => __('expense_added_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('expense_add_fail')]);
    }
    $stmt->close();
}

function deleteExpense($conn) {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => __('expense_id_required')]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => __('expense_deleted_success')]);
    } else {
        echo json_encode(['success' => false, 'message' => __('delete_fail')]);
    }
    $stmt->close();
}

function updateFirstLogin($conn) {
    $user_id = $_SESSION['id'];
    $stmt = $conn->prepare("UPDATE users SET first_login = TRUE WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => __('update_fail')]);
    }
    $stmt->close();
}

function updateSetting($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['settings']) || !is_array($data['settings'])) {
        echo json_encode(['success' => false, 'message' => __('values_required')]);
        return;
    }
    
    $conn->begin_transaction();
    
    try {
        $stmt = $conn->prepare("INSERT INTO settings (setting_name, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($data['settings'] as $setting) {
            if (!isset($setting['name']) || !isset($setting['value'])) {
                throw new Exception(__('invalid_data'));
            }
            $stmt->bind_param("ss", $setting['name'], $setting['value']);
            $stmt->execute();
        }
        
        $stmt->close();
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => __('settings_updated_success')]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('settings_update_fail') . ': ' . $e->getMessage()]);
    }
}

function send_contact_message($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Sanitize and validate input
    $name = filter_var(trim($data['name'] ?? ''), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $subject = filter_var(trim($data['subject'] ?? ''), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($data['message'] ?? ''), FILTER_SANITIZE_STRING);

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => __('fill_all_fields_correctly')]);
        return;
    }

    // Simulate sending an email
    // In a real application, you would use a library like PHPMailer here.
    $to = 'support@eagleshadow.technology';
    $headers = "From: " . $name . " <" . $email . ">";
    $full_message = "From: " . $name . "\n" . "Email: " . $email . "\n\n" . "Message:\n" . $message;
    
    // mail($to, $subject, $full_message, $headers); // This would be the actual send function

    // For this simulation, we'll just return a success message
    echo json_encode(['success' => true, 'message' => __('contact_message_sent')]);
}

function refundInvoice($conn) {
    // Only admin can refund
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('admin_only')]);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $invoice_id = isset($data['invoice_id']) ? (int)$data['invoice_id'] : 0;
    $reason = isset($data['reason']) ? $data['reason'] : '';

    if ($invoice_id <= 0) {
        echo json_encode(['success' => false, 'message' => __('invoice_id_invalid')]);
        return;
    }

    $conn->begin_transaction();

    try {
        // 1. Check if invoice exists and get details
        $stmt = $conn->prepare("SELECT total FROM invoices WHERE id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            throw new Exception(__('invoice_not_found'));
        }
        $invoice = $res->fetch_assoc();
        $total_amount = $invoice['total'];
        $stmt->close();

        // 2. Check if already refunded
        $stmt = $conn->prepare("SELECT id FROM refunds WHERE invoice_id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception(__('invoice_already_refunded'));
        }
        $stmt->close();

        // 3. Get Invoice Items to restock
        $stmt = $conn->prepare("SELECT product_id, quantity FROM invoice_items WHERE invoice_id = ?");
        $stmt->bind_param("i", $invoice_id);
        $stmt->execute();
        $items_res = $stmt->get_result();
        $items = [];
        while ($row = $items_res->fetch_assoc()) {
            $items[] = $row;
        }
        $stmt->close();

        // 4. Restock Products
        $stmt_update = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        foreach ($items as $item) {
            if ($item['product_id']) { // Check if product wasn't deleted (product_id could be null if set null on delete)
                $stmt_update->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt_update->execute();
            }
        }
        $stmt_update->close();

        // 5. Insert Refund Record
        $items_json = json_encode($items);
        $stmt = $conn->prepare("INSERT INTO refunds (invoice_id, amount, items_json, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $invoice_id, $total_amount, $items_json, $reason);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        create_notification($conn, sprintf(__('notification_invoice_refunded'), $invoice_id, $total_amount), "refund");
        
        echo json_encode(['success' => true, 'message' => __('refund_success')]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => __('refund_fail') . ': ' . $e->getMessage()]);
    }
}

function getRefunds($conn) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    // Base query parts
    $queryBody = "FROM refunds r 
                  LEFT JOIN invoices i ON r.invoice_id = i.id
                  LEFT JOIN customers c ON i.customer_id = c.id";
                  
    $whereClause = " WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $whereClause .= " AND (r.invoice_id LIKE ? OR c.name LIKE ? OR r.reason LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    // Count Total
    $countSql = "SELECT COUNT(r.id) as total " . $queryBody . $whereClause;
    $stmt = $conn->prepare($countSql);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $total_refunds = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    $offset = ($page - 1) * $limit;
    
    // Fetch Data with Items Summary
    $dataSql = "SELECT r.id, r.invoice_id, r.amount, r.reason, r.created_at, 
                       c.name as customer_name,
                       GROUP_CONCAT(CONCAT(ii.product_name, ' (', ii.quantity, ')') SEPARATOR ', ') as items_summary
                " . $queryBody . "
                LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
                " . $whereClause . "
                GROUP BY r.id
                ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
                
    $dataTypes = $types . 'ii';
    $dataParams = array_merge($params, [$limit, $offset]);

    $stmt = $conn->prepare($dataSql);
    $stmt->bind_param($dataTypes, ...$dataParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $refunds = [];
    while ($row = $result->fetch_assoc()) {
        $refunds[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $refunds, 'total_refunds' => $total_refunds]);
}

function performDatabaseBackup($conn) {
    // Get all tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    $sqlScript = "-- Database Backup for Smart Shop\n";
    $sqlScript .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
    $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        // Drop table
        $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";
        
        // Create table
        $row = $conn->query("SHOW CREATE TABLE `$table`")->fetch_row();
        $sqlScript .= $row[1] . ";\n\n";
        
        // Insert data
        $result = $conn->query("SELECT * FROM `$table`");
        $columnCount = $result->field_count;

        while ($row = $result->fetch_row()) {
            $sqlScript .= "INSERT INTO `$table` VALUES(";
            for ($j = 0; $j < $columnCount; $j++) {
                $row[$j] = isset($row[$j]) ? $conn->real_escape_string($row[$j]) : null;
                
                if (isset($row[$j])) {
                    $sqlScript .= '"' . $row[$j] . '"';
                } else {
                    $sqlScript .= 'NULL';
                }
                
                if ($j < ($columnCount - 1)) {
                    $sqlScript .= ',';
                }
            }
            $sqlScript .= ");\n";
        }
        $sqlScript .= "\n";
    }

    $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

    // Save to file
    $backupDir = __DIR__ . '/backups/';
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
    
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backupDir . $filename;
    
    if (file_put_contents($filepath, $sqlScript)) {
        return ['success' => true, 'filename' => $filename, 'path' => $filepath, 'size' => filesize($filepath)];
    } else {
        return ['success' => false, 'message' => __('backup_write_fail')];
    }
}

function checkAutoBackup($conn) {
    $res = $conn->query("SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('backup_enabled', 'backup_frequency', 'last_backup_run')");
    $settings = [];
    if($res) {
        while($r = $res->fetch_assoc()) $settings[$r['setting_name']] = $r['setting_value'];
    }

    if (($settings['backup_enabled'] ?? '0') !== '1') return;

    $freq = $settings['backup_frequency'] ?? 'daily';
    $lastRun = $settings['last_backup_run'] ?? '';
    
    $shouldRun = false;
    $now = time();
    
    if (empty($lastRun)) {
        $shouldRun = true;
    } else {
        $lastTime = strtotime($lastRun);
        $diff = $now - $lastTime;
        
        if ($freq === 'daily' && $diff >= 86400) $shouldRun = true;
        elseif ($freq === 'weekly' && $diff >= 604800) $shouldRun = true;
        elseif ($freq === 'monthly' && $diff >= 2592000) $shouldRun = true;
    }

    if ($shouldRun) {
        // Prevent concurrent runs by updating timestamp immediately (basic lock)
        $nowStr = date('Y-m-d H:i:s');
        $conn->query("INSERT INTO settings (setting_name, setting_value) VALUES ('last_backup_run', '$nowStr') ON DUPLICATE KEY UPDATE setting_value = '$nowStr'");
        
        $result = performDatabaseBackup($conn);
        if ($result['success']) {
            create_notification($conn, __('notification_auto_backup_success'), "system_backup");
        } else {
             create_notification($conn, __('notification_auto_backup_fail'), "system_error");
        }
    }
}

function createBackup($conn) {
    // Permission check
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    
    $result = performDatabaseBackup($conn);
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => __('backup_created_success'), 'filename' => $result['filename']]);
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
}

function getBackups($conn) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    
    $backupDir = __DIR__ . '/backups/';
    $files = [];
    
    if (is_dir($backupDir)) {
        $scanned_files = scandir($backupDir);
        foreach ($scanned_files as $file) {
            if ($file !== '.' && $file !== '..' && $file !== '.htaccess' && $file !== 'index.php' && strpos($file, '.sql') !== false) {
                $path = $backupDir . $file;
                $files[] = [
                    'name' => $file,
                    'size' => filesize($path),
                    'date' => date('Y-m-d H:i:s', filemtime($path))
                ];
            }
        }
    }
    
    // Sort by date desc
    usort($files, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    echo json_encode(['success' => true, 'data' => $files]);
}

function deleteBackup($conn) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $filename = basename($data['filename'] ?? '');
    
    if (empty($filename)) {
        echo json_encode(['success' => false, 'message' => __('filename_required')]);
        return;
    }
    
    $filepath = __DIR__ . '/backups/' . $filename;
    
    if (file_exists($filepath) && strpos($filename, '.sql') !== false) {
        if (unlink($filepath)) {
            echo json_encode(['success' => true, 'message' => __('backup_deleted_success')]);
        } else {
            echo json_encode(['success' => false, 'message' => __('delete_fail')]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => __('file_not_found')]);
    }
}

function downloadBackup($conn) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }
    
    $filename = basename($_GET['filename'] ?? '');
    $filepath = __DIR__ . '/backups/' . $filename;
    
    if (empty($filename) || !file_exists($filepath) || strpos($filename, '.sql') === false) {
        echo json_encode(['success' => false, 'message' => __('file_not_found')]);
        return;
    }
    
    // Clear buffer
    if (ob_get_length()) ob_clean();
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    
    readfile($filepath);
    exit;
}

function restoreBackup($conn) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => __('access_denied')]);
        return;
    }

    $backupDir = __DIR__ . '/backups/';
    $filename = '';

    // Case 1: Uploading a file
    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['backup_file'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'sql') {
            echo json_encode(['success' => false, 'message' => __('sql_file_required')]);
            return;
        }
        $filename = 'restore_' . date('Y-m-d_H-i-s') . '_' . basename($file['name']);
        if (!move_uploaded_file($file['tmp_name'], $backupDir . $filename)) {
            echo json_encode(['success' => false, 'message' => __('file_upload_fail')]);
            return;
        }
    } 
    // Case 2: Using existing file
    else {
        $input = json_decode(file_get_contents('php://input'), true);
        $filename = $input['filename'] ?? $_POST['filename'] ?? '';
    }

    if (empty($filename)) {
        echo json_encode(['success' => false, 'message' => __('no_file_selected')]);
        return;
    }

    $filepath = $backupDir . basename($filename);
    if (!file_exists($filepath)) {
        echo json_encode(['success' => false, 'message' => __('backup_file_not_found')]);
        return;
    }

    // Start Restoration
    // We use a session or file to track progress. Since session might be locked during execution, 
    // we use a temp file for progress tracking.
    $progressFile = sys_get_temp_dir() . '/restore_progress_' . session_id() . '.json';
    file_put_contents($progressFile, json_encode(['percent' => 0, 'status' => __('restore_started')]));

    // Close session to allow polling requests
    session_write_close();

    // Set unlimited time
    set_time_limit(0);
    ignore_user_abort(true);

    try {
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        $handle = fopen($filepath, "r");
        if ($handle) {
            $fileSize = filesize($filepath);
            $bytesRead = 0;
            $query = '';
            
            while (($line = fgets($handle)) !== false) {
                $bytesRead += strlen($line);
                
                // Skip comments
                if (substr(trim($line), 0, 2) == '--' || substr(trim($line), 0, 2) == '/*') {
                    continue;
                }
                
                $query .= $line;
                
                // If line ends with semicolon, execute query
                if (substr(trim($line), -1, 1) == ';') {
                    if (!$conn->query($query)) {
                        // Log error but try to continue or stop?
                        // For database integrity, usually stopping is better, but dumps might have glitches.
                        // We will throw exception.
                        throw new Exception(__('db_error') . ": " . $conn->error);
                    }
                    $query = '';
                    
                    // Update progress every ~1% or roughly
                    $percent = ($bytesRead / $fileSize) * 100;
                    file_put_contents($progressFile, json_encode([
                        'percent' => round($percent, 1), 
                        'status' => __('restoring_data') . ' (' . round($percent) . '%)'
                    ]));
                }
            }
            
            fclose($handle);
        } else {
            throw new Exception(__('read_file_error'));
        }

        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        file_put_contents($progressFile, json_encode(['percent' => 100, 'status' => __('restore_success')]));
        
        // Re-open session to send response (optional, but good practice)
        session_start();
        echo json_encode(['success' => true, 'message' => __('backup_success')]);

    } catch (Exception $e) {
        file_put_contents($progressFile, json_encode(['percent' => 100, 'status' => __('error_occurred')]));
        echo json_encode(['success' => false, 'message' => __('backup_failed') . ': ' . $e->getMessage()]);
    }
}

function getRestoreProgress() {
    // Only need session id
    $progressFile = sys_get_temp_dir() . '/restore_progress_' . session_id() . '.json';
    
    if (file_exists($progressFile)) {
        $data = json_decode(file_get_contents($progressFile), true);
        echo json_encode(['success' => true, 'percent' => $data['percent'], 'status' => $data['status']]);
    } else {
        echo json_encode(['success' => true, 'percent' => 0, 'status' => __('waiting')]);
    }
}

if (ob_get_length()) ob_end_flush();

$conn->close();
?>