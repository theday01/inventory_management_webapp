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
    case 'getInventoryStats':
        getInventoryStats($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صالح']);
        break;
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

        // التحقق من المنتجات المرتبطة بفواتير
        $in_clause = implode(',', array_fill(0, count($product_ids), '?'));
        $types = str_repeat('i', count($product_ids));
        
        $check_sql = "SELECT DISTINCT p.id, p.name 
                      FROM products p 
                      INNER JOIN invoice_items ii ON p.id = ii.product_id 
                      WHERE p.id IN ($in_clause)";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param($types, ...$product_ids);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        $linked_products = [];
        while ($row = $result->fetch_assoc()) {
            $linked_products[] = $row;
        }
        $check_stmt->close();

        // إذا كانت هناك منتجات مرتبطة بفواتير
        if (!empty($linked_products)) {
            $linked_names = array_map(function($p) { return $p['name']; }, $linked_products);
            $linked_ids = array_map(function($p) { return $p['id']; }, $linked_products);
            
            // استبعاد المنتجات المرتبطة من قائمة الحذف
            $product_ids = array_diff($product_ids, $linked_ids);
            
            $message = 'تحذير: ' . count($linked_products) . ' منتج مرتبط بفواتير ولا يمكن حذفه';
            
            // إذا كانت جميع المنتجات مرتبطة
            if (empty($product_ids)) {
                echo json_encode([
                    'success' => false, 
                    'message' => $message,
                    'linked_products' => $linked_names,
                    'suggestion' => 'يمكنك تعيين الكمية إلى صفر بدلاً من الحذف'
                ]);
                return;
            }
        }

        // حذف المنتجات غير المرتبطة
        if (!empty($product_ids)) {
            $in_clause = implode(',', array_fill(0, count($product_ids), '?'));
            $types = str_repeat('i', count($product_ids));

            $sql = "DELETE FROM products WHERE id IN ($in_clause)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$product_ids);

            if ($stmt->execute()) {
                $deleted_count = $stmt->affected_rows;
                $stmt->close();
                
                $response_message = "تم حذف {$deleted_count} منتج بنجاح";
                if (!empty($linked_products)) {
                    $response_message .= " (تم تجاهل " . count($linked_products) . " منتج مرتبط بفواتير)";
                }
                
                echo json_encode([
                    'success' => true, 
                    'message' => $response_message,
                    'deleted_count' => $deleted_count,
                    'skipped_count' => count($linked_products),
                    'linked_products' => !empty($linked_products) ? array_map(function($p) { return $p['name']; }, $linked_products) : []
                ]);
            } else {
                throw new Exception('فشل في تنفيذ الاستعلام');
            }
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'حدث خطأ في حذف المنتجات',
            'error' => $e->getMessage()
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

    $sql = "SELECT * FROM customers WHERE name LIKE ? OR phone LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%{$search}%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $customers]);
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
        $total = (float)$data['total'];

        $stmt = $conn->prepare("INSERT INTO invoices (customer_id, total, delivery_cost, delivery_city) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idds", $customer_id, $total, $delivery_cost, $delivery_city);
        $stmt->execute();
        $invoiceId = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($data['items'] as $item) {
            $product_id = (int)$item['id'];
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
            
            $stmt->bind_param("iiid", $invoiceId, $product_id, $quantity, $price);
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

    $stmt = $conn->prepare("SELECT ii.*, p.name as product_name 
                            FROM invoice_items ii 
                            JOIN products p ON ii.product_id = p.id 
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
    $searchDate = isset($_GET['searchDate']) && !empty($_GET['searchDate']) ? $_GET['searchDate'] : date('Y-m-d');

    $sql = "SELECT DISTINCT i.id, i.total, i.created_at, c.name as customer_name 
            FROM invoices i 
            LEFT JOIN customers c ON i.customer_id = c.id
            LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
            LEFT JOIN products p ON ii.product_id = p.id
            WHERE DATE(i.created_at) = ?";

    $params = [$searchDate];
    $types = 's';

    if (!empty($search)) {
        $sql .= " AND (i.id LIKE ? OR c.name LIKE ? OR i.barcode LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    $sql .= " ORDER BY i.created_at DESC LIMIT 150";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoices = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
    }
    
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $invoices]);
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
        
        echo json_encode(['success' => true, 'message' => 'تم تحديث إعدادات التوصيل بنجاح']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'فشل في تحديث الإعدادات: ' . $e->getMessage()]);
    }
}

function getLowStockProducts($conn) {
    // جلب إعدادات تنبيهات الكمية
    $settings_sql = "SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('low_quantity_alert', 'critical_quantity_alert')";
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

if (ob_get_length()) ob_end_flush();

$conn->close();
?>