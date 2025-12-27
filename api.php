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
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getProducts($conn) {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    $sql = "SELECT p.id, p.name, p.price, p.quantity, c.name as category_name 
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
    $data = json_decode(file_get_contents('php://input'), true);

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO products (name, price, quantity, category_id, barcode) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiis", $data['name'], $data['price'], $data['quantity'], $data['category_id'], $data['barcode']);
        $stmt->execute();
        $productId = $stmt->insert_id;
        $stmt->close();

        if (!empty($data['fields'])) {
            $stmt = $conn->prepare("INSERT INTO product_field_values (product_id, field_id, value) VALUES (?, ?, ?)");
            foreach ($data['fields'] as $field) {
                $stmt->bind_param("iis", $productId, $field['id'], $field['value']);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Product added successfully', 'id' => $productId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to add product: ' . $e->getMessage()]);
    }
}

function getProductDetails($conn) {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($product_id === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }

    // Fetch basic product info
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    // Fetch custom fields
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
        echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
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
    $sql = "SELECT c.id, c.name, GROUP_CONCAT(cf.field_name SEPARATOR ',') as fields
            FROM categories c
            LEFT JOIN category_fields cf ON c.id = cf.category_id
            GROUP BY c.id";
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
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        return;
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $data['name']);
        $stmt->execute();
        $categoryId = $stmt->insert_id;
        $stmt->close();

        if (!empty($data['fields'])) {
            $stmt = $conn->prepare("INSERT INTO category_fields (category_id, field_name, field_type) VALUES (?, ?, ?)");
            foreach ($data['fields'] as $field) {
                // For now, all fields are text. This can be expanded later.
                $fieldType = 'text';
                $stmt->bind_param("iss", $categoryId, $field, $fieldType);
                $stmt->execute();
            }
            $stmt->close();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Category added successfully', 'id' => $categoryId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to add category: ' . $e->getMessage()]);
    }
}

function updateCategory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['name'])) {
        echo json_encode(['success' => false, 'message' => 'Category ID and name are required']);
        return;
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $data['name'], $data['id']);
        $stmt->execute();
        $stmt->close();

        // Delete existing fields and re-add them
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
        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update category: ' . $e->getMessage()]);
    }
}

function deleteCategory($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $data['id']);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
    }

    $stmt->close();
}

$conn->close();
?>
