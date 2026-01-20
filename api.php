<?php
// إضافة هذه الأسطر في بداية الملف قبل أي كود آخر
session_start();

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
    echo json_encode(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
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
        getNotifications($conn);
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
    case 'updateShopLogo':
        updateShopLogo($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صالح']);
        break;
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
            sendJsonResponse(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً']);
            return;
        }
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            sendJsonResponse(['success' => false, 'message' => 'غير مصرح لك']);
            return;
        }

        // Get and validate input
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['success' => false, 'message' => 'بيانات غير صالحة']);
            return;
        }
        
        $opening_balance = isset($data['opening_balance']) ? floatval($data['opening_balance']) : 0;
        $force = isset($data['force']) ? (bool)$data['force'] : false;
        $user_id = intval($_SESSION['id']);

        // Check if there's already a business day for today (only if not forcing)
        if (!$force) {
            $today = date('Y-m-d');
            $stmt = $conn->prepare("SELECT id, start_time, end_time FROM business_days WHERE DATE(start_time) = ? ORDER BY start_time DESC LIMIT 1");
            if (!$stmt) {
                sendJsonResponse(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $conn->error]);
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
                        'message' => 'يوجد يوم عمل مفتوح بالفعل.',
                        'details' => "تم العثور على يوم عمل مفتوح بدأ في: " . date('Y-m-d H:i', strtotime($day['start_time'])),
                        'code' => 'business_day_open_exists',
                        'day_id' => $day['id']
                    ]);
                    return;
                } else {
                    // Business day is closed, allow reopening
                    sendJsonResponse([
                        'success' => false,
                        'message' => 'يوجد يوم عمل مغلق لهذا اليوم.',
                        'details' => 'يمكنك إعادة فتح اليوم وتمديده.',
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
            sendJsonResponse(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
            return;
        }
        
        $stmt->bind_param("di", $opening_balance, $user_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            create_notification($conn, "تم بدء يوم عمل جديد برصيد افتتاحي: " . $opening_balance, "business_day_start");
            sendJsonResponse(['success' => true, 'message' => 'تم بدء يوم العمل بنجاح']);
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendJsonResponse(['success' => false, 'message' => 'فشل في بدء يوم العمل: ' . $error]);
        }
        
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function reopen_day($conn) {
    try {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            sendJsonResponse(['success' => false, 'message' => 'غير مصرح لك']);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['success' => false, 'message' => 'بيانات غير صالحة']);
            return;
        }

        $day_id = isset($data['day_id']) ? intval($data['day_id']) : 0;
        $additional_balance = isset($data['opening_balance']) ? floatval($data['opening_balance']) : 0;

        if ($day_id <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'معرف يوم العمل غير صالح']);
            return;
        }

        $stmt = $conn->prepare("UPDATE business_days SET end_time = NULL, closing_balance = NULL, opening_balance = opening_balance + ? WHERE id = ?");
        if (!$stmt) {
            sendJsonResponse(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
            return;
        }

        $stmt->bind_param("di", $additional_balance, $day_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                create_notification($conn, "تم إعادة فتح يوم العمل وتمديده بمبلغ: " . $additional_balance, "business_day_reopen");
                sendJsonResponse(['success' => true, 'message' => 'تم إعادة فتح يوم العمل بنجاح']);
            } else {
                sendJsonResponse(['success' => false, 'message' => 'لم يتم العثور على يوم عمل لإعادة فتحه']);
            }
        } else {
            $error = $stmt->error;
            sendJsonResponse(['success' => false, 'message' => 'فشل في إعادة فتح يوم العمل: ' . $error]);
        }
        $stmt->close();

    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function extend_day($conn) {
    try {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            sendJsonResponse(['success' => false, 'message' => 'غير مصرح لك']);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['success' => false, 'message' => 'بيانات غير صالحة']);
            return;
        }

        $day_id = isset($data['day_id']) ? intval($data['day_id']) : 0;
        $additional_balance = isset($data['opening_balance']) ? floatval($data['opening_balance']) : 0;

        if ($day_id <= 0) {
            sendJsonResponse(['success' => false, 'message' => 'معرف يوم العمل غير صالح']);
            return;
        }

        // Add the additional balance to the existing opening_balance
        $stmt = $conn->prepare("UPDATE business_days SET opening_balance = opening_balance + ? WHERE id = ? AND end_time IS NULL");
        if (!$stmt) {
            sendJsonResponse(['success' => false, 'message' => 'خطأ في إعداد الاستعلام: ' . $conn->error]);
            return;
        }

        $stmt->bind_param("di", $additional_balance, $day_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                 create_notification($conn, "تم تمديد يوم العمل وإضافة مبلغ: " . $additional_balance, "business_day_extend");
                sendJsonResponse(['success' => true, 'message' => 'تم تمديد يوم العمل بنجاح']);
            } else {
                sendJsonResponse(['success' => false, 'message' => 'لم يتم العثور على يوم عمل مفتوح لتمديده']);
            }
        } else {
            $error = $stmt->error;
            sendJsonResponse(['success' => false, 'message' => 'فشل في تمديد يوم العمل: ' . $error]);
        }
        $stmt->close();

    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function get_period_summary($conn) {
    try {
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        $sql_start = $start_date . " 00:00:00";
        $sql_end = $end_date . " 23:59:59";

        // Calculate sales during the period
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total_sales FROM invoices WHERE created_at BETWEEN ? AND ?");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $total_sales = floatval($stmt->get_result()->fetch_assoc()['total_sales']);
        $stmt->close();
        
        // Calculate total cost of goods sold
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(ii.quantity * COALESCE(p.cost_price, 0)), 0) as total_cogs
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            LEFT JOIN products p ON ii.product_id = p.id
            WHERE i.created_at BETWEEN ? AND ?
        ");
        $stmt->bind_param("ss", $sql_start, $sql_end);
        $stmt->execute();
        $total_cogs = floatval($stmt->get_result()->fetch_assoc()['total_cogs']);
        $stmt->close();

        $total_profit = $total_sales - $total_cogs;

        $summary = [
            'total_sales' => $total_sales,
            'total_cogs' => $total_cogs,
            'total_profit' => $total_profit,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        
        sendJsonResponse([
            'success' => true, 
            'message' => 'تم جلب ملخص الفترة بنجاح', 
            'data' => ['summary' => $summary]
        ]);
        
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}


// Also update the end_day function for consistency:
function end_day($conn) {
    try {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            sendJsonResponse(['success' => false, 'message' => 'غير مصرح لك']);
            return;
        }

        $stmt = $conn->prepare("SELECT * FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
        if (!$stmt) {
            sendJsonResponse(['success' => false, 'message' => 'خطأ في قاعدة البيانات']);
            return;
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $day = $result->fetch_assoc();
        $stmt->close();

        if (!$day) {
            sendJsonResponse(['success' => false, 'message' => 'لا يوجد يوم عمل مفتوح لإنهائه']);
            return;
        }

        $day_id = intval($day['id']);
        $start_time = $day['start_time'];

        // Calculate sales during the business day
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total_sales FROM invoices WHERE created_at >= ?");
        $stmt->bind_param("s", $start_time);
        $stmt->execute();
        $total_sales = floatval($stmt->get_result()->fetch_assoc()['total_sales']);
        $stmt->close();
        
        // Calculate total cost of goods sold
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(ii.quantity * p.cost_price), 0) as total_cogs
            FROM invoice_items ii
            JOIN invoices i ON ii.invoice_id = i.id
            JOIN products p ON ii.product_id = p.id
            WHERE i.created_at >= ?
        ");
        $stmt->bind_param("s", $start_time);
        $stmt->execute();
        $total_cogs = floatval($stmt->get_result()->fetch_assoc()['total_cogs']);
        $stmt->close();

        $closing_balance = floatval($day['opening_balance']) + $total_sales;
        $total_profit = $total_sales - $total_cogs;

        $stmt = $conn->prepare("UPDATE business_days SET end_time = NOW(), closing_balance = ? WHERE id = ?");
        $stmt->bind_param("di", $closing_balance, $day_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $summary = [
                'total_sales' => $total_sales,
                'opening_balance' => floatval($day['opening_balance']),
                'closing_balance' => $closing_balance,
                'total_cogs' => $total_cogs,
                'total_profit' => $total_profit
            ];
            create_notification($conn, "تم إنهاء يوم العمل. إجمالي المبيعات: " . $total_sales, "business_day_end");
            sendJsonResponse([
                'success' => true, 
                'message' => 'تم إنهاء يوم العمل بنجاح', 
                'data' => ['summary' => $summary]
            ]);
        } else {
            $error = $stmt->error;
            $stmt->close();
            sendJsonResponse(['success' => false, 'message' => 'فشل في إنهاء يوم العمل: ' . $error]);
        }
        
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()]);
    }
}

function updateShopLogo($conn) {
    // فقط المدير يمكنه تغيير الشعار
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'غير مصرح لك']);
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
                    echo json_encode(['success' => false, 'message' => 'فشل في إنشاء مجلد الرفع']);
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
                    echo json_encode(['success' => true, 'message' => 'تم تحديث الشعار بنجاح', 'logoUrl' => $destUrl]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'فشل في تحديث قاعدة البيانات']);
                }
                $stmt->close();

            } else {
                echo json_encode(['success' => false, 'message' => 'فشل في نقل الملف المرفوع']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'نوع الملف غير مسموح به']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'لم يتم العثور على ملف مرفوع أو حدث خطأ']);
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
            echo json_encode(['success' => false, 'message' => 'فشل في رفع الصورة']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'لم يتم إرسال أي صورة']);
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
            echo json_encode(['success' => false, 'message' => 'لم يتم تحديد منتجات للاستعادة']);
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
             throw new Exception('فشل في استعادة المنتجات: ' . $restore_stmt->error);
        }
        $restore_stmt->close();
        
        // 2. Delete from removed_products
        $delete_sql = "DELETE FROM removed_products WHERE id IN ($placeholders)";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param($types, ...$product_ids);
        if (!$delete_stmt->execute()) {
            throw new Exception('فشل في الحذف من الأرشيف: ' . $delete_stmt->error);
        }
        $restored_count = $delete_stmt->affected_rows;
        $delete_stmt->close();

        $conn->commit();
        if ($restored_count > 0) {
            create_notification($conn, "✅ تمت استعادة {$restored_count} منتج بنجاح إلى المخزون الرئيسي.", "product_restore");
        }
        echo json_encode(['success' => true, 'message' => "تم استعادة {$restored_count} منتج بنجاح"]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'خطأ في استعادة المنتجات: ' . $e->getMessage()]);
    }
}

function permanentlyDeleteProducts($conn) {
     try {
        $data = json_decode(file_get_contents('php://input'), true);
        $product_ids = $data['product_ids'] ?? [];

        if (empty($product_ids)) {
            echo json_encode(['success' => false, 'message' => 'لم يتم تحديد منتجات للحذف النهائي']);
            return;
        }
        $product_ids = array_map('intval', $product_ids);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        $types = str_repeat('i', count($product_ids));
        
        $delete_sql = "DELETE FROM removed_products WHERE id IN ($placeholders)";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param($types, ...$product_ids);
        if (!$delete_stmt->execute()) {
            throw new Exception('فشل في الحذف النهائي: ' . $delete_stmt->error);
        }
        $deleted_count = $delete_stmt->affected_rows;
        $delete_stmt->close();
        
        echo json_encode(['success' => true, 'message' => "تم حذف {$deleted_count} منتج نهائياً"]);
     } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'خطأ في الحذف النهائي: ' . $e->getMessage()]);
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
        echo json_encode(['success' => false, 'message' => 'لم يتم تحديد منتجات']);
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
        echo json_encode(['success' => false, 'message' => 'لا توجد تغييرات لتطبيقها']);
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
        echo json_encode(['success' => true, 'message' => 'تم تحديث المنتجات بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث المنتجات']);
    }
    $stmt->close();
}

function bulkDeleteProducts($conn) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $product_ids = $data['product_ids'] ?? [];

        if (empty($product_ids)) {
            echo json_encode(['success' => false, 'message' => 'لم يتم تحديد منتجات']);
            return;
        }

        $product_ids = array_map('intval', $product_ids);
        $product_ids = array_filter($product_ids, function($id) { return $id > 0; });

        if (empty($product_ids)) {
            echo json_encode(['success' => false, 'message' => 'معرفات المنتجات غير صالحة']);
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
            $msg = "تمت أرشفة {$deleted_count} منتج.";
            if ($deleted_count === 1 && !empty($products_info)) {
                $firstName = $products_info[0]['name'];
                $msg = "تمت أرشفة 1 منتج ({$firstName})، سيتم حذفه نهائيا بعد 30 يوم دون امكانية استرجاعه";
            }
            create_notification($conn, $msg, "product_delete");
        }
        
        $response = [
            'success' => true,
            'message' => "تمت أرشفة {$deleted_count} منتج بنجاح",
            'deleted_count' => $deleted_count
        ];
        
        // معلومات المنتجات المرتبطة بفواتير
        if (!empty($linked_products)) {
            $linked_count = count($linked_products);
            $linked_names = array_map(function($p) { 
                return $p['name'] . " ({$p['invoice_count']} فاتورة)"; 
            }, $linked_products);
            
            $response['linked_info'] = [
                'count' => $linked_count,
                'products' => $linked_names,
                'note' => "تنبيه: {$linked_count} من المنتجات المؤرشفة مرتبطة بفواتير سابقة. الفواتير القديمة ستحتفظ بمعلومات هذه المنتجات."
            ];
        }
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => 'حدث خطأ في أرشفة المنتجات: ' . $e->getMessage()
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
            echo json_encode(['success' => false, 'message' => 'مسار صورة غير صالح']);
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
        create_notification($conn, "تمت إضافة منتج جديد: " . $data['name'], "product_add");
        echo json_encode(['success' => true, 'message' => 'تم إضافة المنتج بنجاح', 'id' => $productId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة المنتج: ' . $e->getMessage()]);
    }
}

function updateProduct($conn) {
    $data = $_POST;
    $productId = isset($data['id']) ? (int)$data['id'] : 0;

    if ($productId === 0) {
        echo json_encode(['success' => false, 'message' => 'معرف المنتج مطلوب']);
        return;
    }

    $imagePath = null;
    if (!empty($data['image_path'])) {
        if (!is_valid_image_path($data['image_path'])) {
            echo json_encode(['success' => false, 'message' => 'مسار صورة غير صالح']);
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
        create_notification($conn, "تم تحديث المنتج: " . $data['name'], "product_update");
        echo json_encode(['success' => true, 'message' => 'تم تحديث المنتج بنجاح']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث المنتج: ' . $e->getMessage()]);
    }
}

function bulkAddProducts($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $products = $data['products'] ?? [];

    if (empty($products)) {
        echo json_encode(['success' => false, 'message' => 'لم يتم إرسال أي منتجات']);
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
                throw new Exception("مسار صورة غير صالح للمنتج: " . htmlspecialchars($name));
            }

            $stmt->execute();
        }
        
        $stmt->close();
        $conn->commit();
        
        create_notification($conn, "تمت إضافة " . count($products) . " منتج جديد بنجاح.", "product_add");
        echo json_encode(['success' => true, 'message' => 'تم إضافة المنتجات بنجاح']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة المنتجات: ' . $e->getMessage()]);
    }
}

function importProducts($conn) {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'لم يتم رفع ملف Excel صالح']);
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
            echo json_encode(['success' => false, 'message' => 'الملف لا يحتوي على أي بيانات']);
            return;
        }

        if (count($rows) < 2) {
            echo json_encode(['success' => false, 'message' => 'الملف يحتوي على عناوين فقط، أضف بعض البيانات']);
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
            echo json_encode(['success' => false, 'message' => 'الملف يفتقر إلى الأعمدة المطلوبة: Name/اسم, Price/سعر, Quantity/كمية. العناوين الموجودة: ' . implode(', ', $headers)]);
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
                $errors[] = "الصف $rowNum: اسم المنتج مطلوب";
                continue;
            }
            if (!is_numeric($price) || $price < 0) {
                $errors[] = "الصف $rowNum: سعر البيع غير صالح";
                continue;
            }
            if (!is_numeric($quantity) || $quantity < 0) {
                $errors[] = "الصف $rowNum: الكمية غير صالحة";
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

        create_notification($conn, "تم استيراد $insertedCount منتج من ملف Excel بنجاح.", "product_add");

        echo json_encode([
            'success' => true, 
            'message' => "تم استيراد $insertedCount منتج بنجاح" . (count($errors) > 0 ? ". تم تجاهل " . count($errors) . " صف بسبب أخطاء" : ""),
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
        echo json_encode(['success' => false, 'message' => 'فشل في استيراد المنتجات: ' . $e->getMessage()]);
    }
}

function getProductDetails($conn) {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($product_id === 0) {
        echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صالح']);
        return;
    }

    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'لم يتم العثور على المنتج']);
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
        echo json_encode(['success' => false, 'message' => 'معرف الفئة غير صالح']);
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
        echo json_encode(['success' => false, 'message' => 'اسم الفئة مطلوب']);
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
        echo json_encode(['success' => true, 'message' => 'تم إضافة الفئة بنجاح', 'id' => $categoryId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة الفئة: ' . $e->getMessage()]);
    }
}

function updateCategory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => 'معرف الفئة والاسم مطلوبان']);
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
        echo json_encode(['success' => true, 'message' => 'تم تحديث الفئة بنجاح']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الفئة: ' . $e->getMessage()]);
    }
}

function deleteCategory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'معرف الفئة مطلوب']);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $data['id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'تم حذف الفئة بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في حذف الفئة']);
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
        echo json_encode(['success' => false, 'message' => 'اسم العميل مطلوب']);
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
        echo json_encode(['success' => true, 'message' => 'تم إضافة العميل بنجاح', 'id' => $customerId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في إضافة العميل']);
    }

    $stmt->close();
}

function getCustomerDetails($conn) {
    $customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($customer_id === 0) {
        echo json_encode(['success' => false, 'message' => 'معرف العميل غير صالح']);
        return;
    }

    $stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    $stmt->close();

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'لم يتم العثور على العميل']);
        return;
    }

    echo json_encode(['success' => true, 'data' => $customer]);
}

function updateCustomer($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => 'معرف العميل والاسم مطلوبان']);
        return;
    }

    $phone = isset($data['phone']) ? $data['phone'] : null;
    $email = isset($data['email']) ? $data['email'] : null;
    $address = isset($data['address']) ? $data['address'] : null;
    $city = isset($data['city']) ? $data['city'] : null;

    $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, email = ?, address = ?, city = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $data['name'], $phone, $email, $address, $city, $data['id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'تم تحديث العميل بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث العميل']);
    }

    $stmt->close();
}

function checkout($conn) {
    $day_stmt = $conn->prepare("SELECT id FROM business_days WHERE end_time IS NULL");
    $day_stmt->execute();
    if ($day_stmt->get_result()->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'يجب بدء يوم عمل جديد أولاً']);
        return;
    }
    $day_stmt->close();

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['items']) || !is_array($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'لا توجد منتجات في السلة']);
        return;
    }

    $conn->begin_transaction();

    try {
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
        $delivery_cost = isset($data['delivery_cost']) ? (float)$data['delivery_cost'] : 0;
        $delivery_city = isset($data['delivery_city']) ? $data['delivery_city'] : null;
        $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'cash';
        $amount_received = isset($data['amount_received']) ? (float)$data['amount_received'] : 0;
        $change_due = isset($data['change_due']) ? (float)$data['change_due'] : 0;

        $total = (float)$data['total'];

        $stmt = $conn->prepare("INSERT INTO invoices (customer_id, total, delivery_cost, delivery_city, discount_percent, discount_amount, payment_method, amount_received, change_due) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $discount_percent = isset($data['discount_percent']) ? (float)$data['discount_percent'] : 0;
        $discount_amount = isset($data['discount_amount']) ? (float)$data['discount_amount'] : 0;
        $stmt->bind_param("iddsdssdd", $customer_id, $total, $delivery_cost, $delivery_city, $discount_percent, $discount_amount, $payment_method, $amount_received, $change_due);
        
        $stmt->execute();
        $invoiceId = $stmt->insert_id;
        $stmt->close();
        
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        foreach ($data['items'] as $item) {
            $product_id = (int)$item['id'];
            $product_name = $item['name'];
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];
            
            // التحقق من الكمية
            $checkStmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
            $checkStmt->bind_param("i", $product_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("المنتج غير موجود");
            }
            
            $currentStock = $result->fetch_assoc()['quantity'];
            $checkStmt->close();
            
            if ($currentStock < $quantity) {
                throw new Exception("الكمية المتوفرة غير كافية للمنتج (متوفر: " . $currentStock . ")");
            }
            
            $stmt->bind_param("iisid", $invoiceId, $product_id, $product_name, $quantity, $price);
            $stmt->execute();

            // تحديث المخزون
            $updateStmt = $conn->prepare("UPDATE products SET quantity = GREATEST(0, quantity - ?) WHERE id = ? AND quantity >= ?");
            $updateStmt->bind_param("iii", $quantity, $product_id, $quantity);
            $updateStmt->execute();
            
            if ($updateStmt->affected_rows === 0) {
                throw new Exception("فشل في تحديث المخزون - الكمية غير كافية");
            }
            $updateStmt->close();
        }
        $stmt->close();

        $barcode = 'INV' . str_pad($invoiceId, 8, '0', STR_PAD_LEFT);
        $updateStmt = $conn->prepare("UPDATE invoices SET barcode = ? WHERE id = ?");
        $updateStmt->bind_param("si", $barcode, $invoiceId);
        $updateStmt->execute();
        $updateStmt->close();

        $conn->commit();
        create_notification($conn, "تم إنشاء فاتورة جديدة برقم: " . $barcode, "new_sale");
        echo json_encode(['success' => true, 'message' => 'تم إنشاء الفاتورة بنجاح', 'invoice_id' => $invoiceId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'فشل في إنشاء الفاتورة: ' . $e->getMessage()]);
    }
}

function createInvoice($conn) {
    checkout($conn);
}

function getInvoice($conn) {
    $invoice_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($invoice_id === 0) {
        echo json_encode(['success' => false, 'message' => 'معرف الفاتورة غير صالح']);
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
        echo json_encode(['success' => false, 'message' => 'لم يتم العثور على الفاتورة']);
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
    $dataSql = "SELECT DISTINCT i.id, i.total, i.created_at, c.name as customer_name "
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
        echo json_encode(['success' => false, 'message' => 'القيم مطلوبة']);
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
        
        create_notification($conn, "تم تحديث إعدادات التوصيل: داخل المدينة {$data['deliveryInsideCity']}، خارج المدينة {$data['deliveryOutsideCity']}", "settings_update");
        echo json_encode(['success' => true, 'message' => 'تم تحديث إعدادات التوصيل بنجاح']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الإعدادات: ' . $e->getMessage()]);
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
        
        $result = $conn->query("
            SELECT 
                COUNT(DISTINCT i.id) as total_orders,
                COALESCE(SUM(i.total), 0) as revenue,
                COALESCE(SUM(ii.quantity * COALESCE(p.cost_price, 0)), 0) as total_cost
            FROM invoices i 
            LEFT JOIN invoice_items ii ON i.id = ii.invoice_id 
            LEFT JOIN products p ON ii.product_id = p.id 
            WHERE {$start_time_filter}
        ");
        
        $todayData = $result ? $result->fetch_assoc() : ['total_orders' => 0, 'revenue' => 0, 'total_cost' => 0];
        
        $stats['todayRevenue'] = $todayData['revenue'];
        $stats['todayCost'] = $todayData['total_cost'];
        $stats['todayProfit'] = $stats['todayRevenue'] - $stats['todayCost'];
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
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب الإحصائيات: ' . $e->getMessage()]);
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
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب بيانات المبيعات: ' . $e->getMessage()]);
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
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب المنتجات الأكثر مبيعاً: ' . $e->getMessage()]);
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
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب مبيعات الفئات: ' . $e->getMessage()]);
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
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب الفواتير الأخيرة: ' . $e->getMessage()]);
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
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب أفضل العملاء: ' . $e->getMessage()]);
    }
}

function create_notification($conn, $message, $type) {
    $stmt = $conn->prepare("INSERT INTO notifications (message, type) VALUES (?, ?)");
    $stmt->bind_param("ss", $message, $type);
    $stmt->execute();
    $stmt->close();
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
            echo json_encode(['success' => false, 'message' => 'فشل التحديث']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'معرف غير صالح']);
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
            echo json_encode(['success' => false, 'message' => 'فشل الحذف']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'معرف غير صالح']);
    }
}

function markAllNotificationsRead($conn) {
    if ($conn->query("UPDATE notifications SET status = 'read' WHERE status = 'unread'")) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الإشعارات']);
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
            echo json_encode(['success' => true, 'message' => 'Rental feature disabled']);
            return;
        }
        
        $currentMonth = date('Y-m');
        if (isset($settings['rentalPaidMonth']) && $settings['rentalPaidMonth'] === $currentMonth) {
            echo json_encode(['success' => true, 'notification_sent' => false, 'message' => 'تم دفع إيجار هذا الشهر', 'paid_this_month' => true]);
            return;
        }
        
        if (!isset($settings['rentalPaymentDate']) || !isset($settings['rentalType'])) {
            echo json_encode(['success' => false, 'message' => 'إعدادات الإيجار غير مكتملة']);
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
            echo json_encode(['success' => true, 'message' => 'Already notified today']);
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
            
            $notificationMessage = "🏠 تذكير: يتبقى {$daysUntilDue} يوم لدفع إيجار المتجر بمبلغ {$amount} {$currency}";
            
            if (isset($settings['rentalLandlordName']) && !empty($settings['rentalLandlordName'])) {
                $notificationMessage .= "\nالمالك: " . $settings['rentalLandlordName'];
            }
            
            $shouldNotify = true;
        }
        elseif ($daysUntilDue == 0) {
            $amount = number_format((float)($settings['rentalAmount'] ?? 0), 2);
            $currency = $settings['currency'] ?? 'MAD';
            
            $notificationMessage = "🚨 تنبيه عاجل: اليوم هو موعد دفع إيجار المتجر بمبلغ {$amount} {$currency}!";
            
            if (isset($settings['rentalLandlordPhone']) && !empty($settings['rentalLandlordPhone'])) {
                $notificationMessage .= "\nهاتف المالك: " . $settings['rentalLandlordPhone'];
            }
            
            $shouldNotify = true;
            $notificationType = 'rental_due_today';
        }
        elseif ($daysUntilDue < 0) {
            $daysOverdue = abs($daysUntilDue);
            $amount = number_format((float)($settings['rentalAmount'] ?? 0), 2);
            $currency = $settings['currency'] ?? 'MAD';
            
            $notificationMessage = "⚠️ تحذير: تأخرت عن دفع الإيجار بـ {$daysOverdue} يوم! المبلغ المستحق: {$amount} {$currency}";
            
            if (isset($settings['rentalLandlordPhone']) && !empty($settings['rentalLandlordPhone'])) {
                $notificationMessage .= "\nهاتف المالك للتواصل: " . $settings['rentalLandlordPhone'];
            }
            
            $shouldNotify = true;
            $notificationType = 'rental_overdue';
            
            if ($daysOverdue >= 7) {
                $nextPaymentDate = calculateNextPaymentDate($paymentDate, $rentalType);
                $conn->query("UPDATE settings SET setting_value = '{$nextPaymentDate}' WHERE setting_name = 'rentalPaymentDate'");
                
                $nextDateFormatted = date('Y/m/d', strtotime($nextPaymentDate));
                create_notification($conn, "تم تحديث موعد الإيجار التالي تلقائياً إلى {$nextDateFormatted}", "rental_auto_update");
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
                'message' => 'No notification needed yet'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error checking rental: ' . $e->getMessage()]);
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
            echo json_encode(['success' => false, 'message' => 'إعدادات الإيجار غير مكتملة']);
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
        
        create_notification($conn, "✅ تم تأكيد دفع إيجار هذا الشهر. الموعد القادم: " . date('Y/m/d', strtotime($nextPaymentDate)), "rental_paid");
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'تم تسجيل دفع الإيجار لهذا الشهر', 'next_payment_date' => $nextPaymentDate, 'paid_month' => $currentMonth]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'خطأ أثناء تسجيل الدفع: ' . $e->getMessage()]);
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
        echo json_encode(['success' => false, 'message' => 'خطأ في جلب سجل المدفوعات: ' . $e->getMessage()]);
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

if (ob_get_length()) ob_end_flush();

$conn->close();
?>