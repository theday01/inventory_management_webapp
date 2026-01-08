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
    case 'addProduct':
        addProduct($conn);
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
    // ----------------------------
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صالح']);
        break;
}

function getRemovedProducts($conn) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'removed_at';
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'desc';

    // Auto-cleanup: permanently remove entries older than 30 days
    // This ensures that after 30 days from deletion the product
    // is no longer recoverable and won't appear in the removed list.
    try {
        $conn->query("DELETE FROM removed_products WHERE removed_at <= (NOW() - INTERVAL 30 DAY)");
    } catch (Exception $e) {
        // ignore cleanup errors, proceed to return remaining items
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
        
        // معلومات المنتجات المرتبطة بفواتير (لا تتغير)
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
             . " ORDER BY p.{$sortBy} {$sortOrder} LIMIT ? OFFSET ?";
    
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

function addProduct($conn) {
    $data = $_POST;
    $imagePath = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "src/img/uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        $allowTypes = array('jpg','png','jpeg','gif');
        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $imagePath = $targetFilePath;
            }
        }
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO products (name, price, quantity, category_id, barcode, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiiss", $data['name'], $data['price'], $data['quantity'], $data['category_id'], $data['barcode'], $imagePath);
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
        
        // --- NEW CODE START ---
        $amount_received = isset($data['amount_received']) ? (float)$data['amount_received'] : 0;
        $change_due = isset($data['change_due']) ? (float)$data['change_due'] : 0;
        // --- NEW CODE END ---

        $total = (float)$data['total'];

        // Update Query to include amount_received and change_due
        $stmt = $conn->prepare("INSERT INTO invoices (customer_id, total, delivery_cost, delivery_city, payment_method, amount_received, change_due) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iddssdd", $customer_id, $total, $delivery_cost, $delivery_city, $payment_method, $amount_received, $change_due);
        
        $stmt->execute();
        $invoiceId = $stmt->insert_id;
        $stmt->close();
        
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        foreach ($data['items'] as $item) {
            $product_id = (int)$item['id'];
            $product_name = $item['name'];
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];
            
            // التحقق من الكمية المتوفرة قبل البيع
            $checkStmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
            $checkStmt->bind_param("i", $product_id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("المنتج غير موجود");
            }
            
            $currentStock = $result->fetch_assoc()['quantity'];
            $checkStmt->close();
            
            // منع البيع إذا كانت الكمية غير كافية
            if ($currentStock < $quantity) {
                throw new Exception("الكمية المتوفرة غير كافية للمنتج (متوفر: " . $currentStock . ")");
            }
            
            $stmt->bind_param("iisid", $invoiceId, $product_id, $product_name, $quantity, $price);
            $stmt->execute();

            // تحديث المخزون بطريقة آمنة - منع الكميات السالبة
            $updateStmt = $conn->prepare("UPDATE products SET quantity = GREATEST(0, quantity - ?) WHERE id = ? AND quantity >= ?");
            $updateStmt->bind_param("iii", $quantity, $product_id, $quantity);
            $updateStmt->execute();
            
            // التحقق من نجاح التحديث
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
    // جلب إعدادات تنبيهات الكمية
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
    
    // فحص إذا تم إرسال إشعار اليوم
    $last_check_time = $quantity_settings['last_stock_check_notification'] ?? 0;
    $last_check_date = date('Y-m-d', $last_check_time);
    $today_date = date('Y-m-d');
    $total_low_stock = count($outOfStock) + count($critical) + count($low);
    
    // إرسال إشعار فقط إذا لم يتم إرساله اليوم
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
        
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        $result = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM invoices WHERE DATE(created_at) = '$today'");
        $stats['todayRevenue'] = $result ? $result->fetch_assoc()['revenue'] : 0;
        
        $result = $conn->query("SELECT COALESCE(SUM(total), 0) as revenue FROM invoices WHERE DATE(created_at) = '$yesterday'");
        $stats['yesterdayRevenue'] = $result ? $result->fetch_assoc()['revenue'] : 0;
        
        $result = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE DATE(created_at) = '$today'");
        $stats['todayOrders'] = $result ? $result->fetch_assoc()['count'] : 0;
        
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
        
        $sql = "SELECT DATE(created_at) as date, 
                       COUNT(*) as orders, 
                       COALESCE(SUM(total), 0) as revenue
                FROM invoices
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $days);
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
        
        $sql = "SELECT p.id, p.name, p.quantity as stock,
                       COALESCE(SUM(ii.quantity), 0) as units_sold,
                       COALESCE(SUM(ii.quantity * ii.price), 0) as revenue
                FROM products p
                LEFT JOIN invoice_items ii ON p.id = ii.product_id
                LEFT JOIN invoices i ON ii.invoice_id = i.id
                WHERE i.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) OR i.created_at IS NULL
                GROUP BY p.id
                HAVING units_sold > 0
                ORDER BY units_sold DESC
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $days);
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
        
        $sql = "SELECT c.name as category,
                       COALESCE(SUM(ii.quantity * ii.price), 0) as revenue,
                       COALESCE(SUM(ii.quantity), 0) as units_sold
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                LEFT JOIN invoice_items ii ON p.id = ii.product_id
                LEFT JOIN invoices i ON ii.invoice_id = i.id
                WHERE i.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) OR i.created_at IS NULL
                GROUP BY c.id
                HAVING revenue > 0
                ORDER BY revenue DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $days);
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
        
        $sql = "SELECT i.id, i.total, i.created_at,
                       c.name as customer_name
                FROM invoices i
                LEFT JOIN customers c ON i.customer_id = c.id
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
        
        $sql = "SELECT c.id, c.name, c.phone,
                       COUNT(i.id) as order_count,
                       COALESCE(SUM(i.total), 0) as total_spent,
                       MAX(i.created_at) as last_purchase
                FROM customers c
                LEFT JOIN invoices i ON c.id = i.customer_id
                WHERE i.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                GROUP BY c.id
                HAVING order_count > 0
                ORDER BY total_spent DESC
                LIMIT 5";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $days);
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
    // تنظيف الإشعارات القديمة (أكثر من دقيقة) تلقائياً
    cleanOldNotifications($conn);
    
    $limit = 20; 
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $offset = ($page - 1) * $limit;

    // بناء شرط الاستعلام بناءً على الفلتر
    $whereClause = "";
    if ($filter === 'unread') {
        $whereClause = "WHERE status = 'unread'";
    } elseif ($filter === 'read') {
        $whereClause = "WHERE status = 'read'";
    }

    // جلب العدد الإجمالي للإشعارات (مع الفلتر) لحساب عدد الصفحات
    $countSql = "SELECT COUNT(*) as count FROM notifications $whereClause";
    $total_result = $conn->query($countSql);
    $total_rows = $total_result->fetch_assoc()['count'];
    $total_pages = ceil($total_rows / $limit);

    // جلب عدد الإشعارات غير المقروءة (للعرض في العداد الأحمر دائماً)
    $unread_result = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'");
    $unread_count = $unread_result->fetch_assoc()['count'];

    // جلب الإشعارات للصفحة الحالية مع الفلتر
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
        // حذف الإشعارات التي مضى عليها أكثر من 30 يوم
        $sql = "DELETE FROM notifications WHERE created_at <= (NOW() - INTERVAL 30 DAY)";
        $conn->query($sql);
        
        // يمكنك إضافة log للمراقبة (اختياري)
        error_log("Old notifications cleaned successfully");
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
    // تحديث كل الإشعارات التي حالتها 'unread' لتصبح 'read'
    if ($conn->query("UPDATE notifications SET status = 'read' WHERE status = 'unread'")) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الإشعارات']);
    }
}

function checkExpiringProducts($conn) {
    // Get products that will be auto-deleted in less than 24 hours
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
            // Check if we already sent notification for these products today
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
                
                // Update last notification date
                $conn->query("INSERT INTO settings (setting_name, setting_value) 
                             VALUES ('last_expiry_notification_date', '$today') 
                             ON DUPLICATE KEY UPDATE setting_value = '$today'");
            }
        }
    }
}

function checkRentalDue($conn) {
    try {
        // جلب إعدادات الإيجار
        $settings_query = "SELECT setting_name, setting_value FROM settings WHERE setting_name LIKE 'rental%'";
        $result = $conn->query($settings_query);
        
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
        
        // التحقق من تفعيل الميزة
        if (!isset($settings['rentalEnabled']) || $settings['rentalEnabled'] != '1') {
            echo json_encode(['success' => true, 'message' => 'Rental feature disabled']);
            return;
        }
        
        // منع التذكير إذا تم الدفع لهذا الشهر
        $currentMonth = date('Y-m');
        if (isset($settings['rentalPaidMonth']) && $settings['rentalPaidMonth'] === $currentMonth) {
            echo json_encode(['success' => true, 'notification_sent' => false, 'message' => 'تم دفع إيجار هذا الشهر', 'paid_this_month' => true]);
            return;
        }
        
        // التحقق من وجود تاريخ الدفع ونوعية التأجير
        if (!isset($settings['rentalPaymentDate']) || !isset($settings['rentalType'])) {
            echo json_encode(['success' => false, 'message' => 'إعدادات الإيجار غير مكتملة']);
            return;
        }
        
        $paymentDate = $settings['rentalPaymentDate']; // Y-m-d format
        $rentalType = $settings['rentalType']; // 'monthly' or 'yearly'
        $reminderDays = (int)($settings['rentalReminderDays'] ?? 7);
        $lastNotification = (int)($settings['rentalLastNotification'] ?? 0);
        $currentTime = time();
        
        // منع إرسال إشعارات متكررة - يجب أن يكون آخر إشعار في يوم مختلف
        $lastNotificationDate = date('Y-m-d', $lastNotification);
        $todayDate = date('Y-m-d');
        
        if ($lastNotificationDate === $todayDate) {
            echo json_encode(['success' => true, 'message' => 'Already notified today']);
            return;
        }
        
        // تحويل تاريخ الدفع إلى timestamp
        $paymentTimestamp = strtotime($paymentDate);
        $today = strtotime(date('Y-m-d'));
        
        // حساب الفرق بالأيام
        $daysUntilDue = floor(($paymentTimestamp - $today) / (60 * 60 * 24));
        
        $shouldNotify = false;
        $notificationMessage = '';
        $notificationType = 'rental_reminder';
        
        // 1. إذا كان موعد الدفع خلال أيام التذكير
        if ($daysUntilDue > 0 && $daysUntilDue <= $reminderDays) {
            $amount = number_format((float)($settings['rentalAmount'] ?? 0), 2);
            $currency = $settings['currency'] ?? 'MAD';
            
            $notificationMessage = "🏠 تذكير: يتبقى {$daysUntilDue} يوم لدفع إيجار المتجر بمبلغ {$amount} {$currency}";
            
            if (isset($settings['rentalLandlordName']) && !empty($settings['rentalLandlordName'])) {
                $notificationMessage .= "\nالمالك: " . $settings['rentalLandlordName'];
            }
            
            $shouldNotify = true;
        }
        // 2. إذا كان اليوم هو يوم الاستحقاق بالضبط
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
        // 3. إذا تأخر الدفع (بعد الموعد)
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
            
            // إذا مضى أكثر من 7 أيام على التأخير، حدّث التاريخ للدفع التالي تلقائياً
            if ($daysOverdue >= 7) {
                $nextPaymentDate = calculateNextPaymentDate($paymentDate, $rentalType);
                $conn->query("UPDATE settings SET setting_value = '{$nextPaymentDate}' WHERE setting_name = 'rentalPaymentDate'");
                
                $nextDateFormatted = date('Y/m/d', strtotime($nextPaymentDate));
                create_notification($conn, "تم تحديث موعد الإيجار التالي تلقائياً إلى {$nextDateFormatted}", "rental_auto_update");
            }
        }
        
        // إرسال الإشعار إذا لزم الأمر
        if ($shouldNotify) {
            create_notification($conn, $notificationMessage, $notificationType);
            
            // تحديث وقت آخر إشعار
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
/**
 * حساب تاريخ الدفع التالي بناءً على نوعية التأجير
 */
function calculateNextPaymentDate($currentDate, $rentalType) {
    $date = new DateTime($currentDate);
    
    if ($rentalType === 'monthly') {
        // إضافة شهر واحد
        $date->modify('+1 month');
    } elseif ($rentalType === 'yearly') {
        // إضافة سنة واحدة
        $date->modify('+1 year');
    }
    
    return $date->format('Y-m-d');
}

if (ob_get_length()) ob_end_flush();

$conn->close();
?>
