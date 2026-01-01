<?php
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
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير صالح']);
        break;
}

function getProducts($conn) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    $sql = "SELECT p.id, p.name, p.price, p.quantity, p.image, p.category_id, p.barcode, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE 1=1";

    if (!empty($search)) {
        $sql .= " AND (p.name LIKE ? OR p.barcode LIKE ?)";
    }
    if ($category_id > 0) {
        $sql .= " AND p.category_id = ?";
    }

    $stmt = $conn->prepare($sql);

    if (!empty($search) && $category_id > 0) {
        $searchTerm = "%{$search}%";
        $stmt->bind_param("ssi", $searchTerm, $searchTerm, $category_id);
    } elseif (!empty($search)) {
        $searchTerm = "%{$search}%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
    } elseif ($category_id > 0) {
        $stmt->bind_param("i", $category_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();

    echo json_encode(['success' => true, 'data' => $products]);
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

function createInvoice($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['items']) || !is_array($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'لا توجد منتجات في الفاتورة']);
        return;
    }

    $conn->begin_transaction();

    try {
        $customer_id = isset($data['customer_id']) ? (int)$data['customer_id'] : null;
        $total = (float)$data['total'];

        $stmt = $conn->prepare("INSERT INTO invoices (customer_id, total) VALUES (?, ?)");
        $stmt->bind_param("id", $customer_id, $total);
        $stmt->execute();
        $invoiceId = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($data['items'] as $item) {
            $product_id = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];
            
            $stmt->bind_param("iiid", $invoiceId, $product_id, $quantity, $price);
            $stmt->execute();

            // Update product quantity
            $updateStmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $updateStmt->bind_param("ii", $quantity, $product_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
        $stmt->close();

        // Generate and update barcode
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
    
    // التعديل 1: إذا لم يتم تحديد تاريخ، نستخدم تاريخ اليوم كافتراضي
    $searchDate = isset($_GET['searchDate']) && !empty($_GET['searchDate']) ? $_GET['searchDate'] : date('Y-m-d');

    $sql = "SELECT DISTINCT i.id, i.total, i.created_at, c.name as customer_name 
            FROM invoices i 
            LEFT JOIN customers c ON i.customer_id = c.id
            LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
            LEFT JOIN products p ON ii.product_id = p.id
            WHERE DATE(i.created_at) = ?"; // التعديل 2: تصفية النتائج بناء على التاريخ دائماً

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

    // التعديل 3: زيادة الحد الأقصى للصفوف إلى 150
    $sql .= " ORDER BY i.created_at DESC LIMIT 150";

    $stmt = $conn->prepare($sql);

    // ربط المعاملات بشكل ديناميكي
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
$conn->close();
?>