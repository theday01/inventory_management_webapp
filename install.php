<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smart_shop";

// Connect to MySQL server without specifying a database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    echo "Database '$dbname' created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// SQL to create tables
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT(6) NOT NULL,
    category_id INT(6) UNSIGNED,
    barcode VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$sql_customers = "CREATE TABLE IF NOT EXISTS customers (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(50),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$sql_invoices = "CREATE TABLE IF NOT EXISTS invoices (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT(6) UNSIGNED,
    total DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
)";

$sql_invoice_items = "CREATE TABLE IF NOT EXISTS invoice_items (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(6) UNSIGNED,
    product_id INT(6) UNSIGNED,
    quantity INT(6) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)";

$sql_settings = "CREATE TABLE IF NOT EXISTS settings (
    setting_name VARCHAR(255) PRIMARY KEY,
    setting_value TEXT NOT NULL
)";

$sql_categories = "CREATE TABLE IF NOT EXISTS categories (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_category_fields = "CREATE TABLE IF NOT EXISTS category_fields (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT(6) UNSIGNED NOT NULL,
    field_name VARCHAR(255) NOT NULL,
    field_type VARCHAR(50) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
)";

$sql_product_field_values = "CREATE TABLE IF NOT EXISTS product_field_values (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT(6) UNSIGNED NOT NULL,
    field_id INT(6) UNSIGNED NOT NULL,
    value TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES category_fields(id) ON DELETE CASCADE
)";


// Execute table creation queries
$tables = [
    'users' => $sql_users,
    'products' => $sql_products,
    'customers' => $sql_customers,
    'invoices' => $sql_invoices,
    'invoice_items' => $sql_invoice_items,
    'settings' => $sql_settings,
    'categories' => $sql_categories,
    'category_fields' => $sql_category_fields,
    'product_field_values' => $sql_product_field_values,
];

foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table '$name' created successfully.<br>";
    } else {
        echo "Error creating table '$name': " . $conn->error . "<br>";
    }
}

// Add foreign key constraint to products table
$sql_fk_products_category = "ALTER TABLE products ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL";

if ($conn->query($sql_fk_products_category) === TRUE) {
    echo "Foreign key constraint added to products table successfully.<br>";
} else {
    // Check if the foreign key already exists to avoid error on re-run
    if ($conn->errno != 1060 && $conn->errno != 1826) { // 1060 for duplicate column, 1826 for duplicate foreign key
        $result = $conn->query("SHOW CREATE TABLE products");
        $row = $result->fetch_assoc();
        if (strpos($row['Create Table'], 'CONSTRAINT') === false || strpos($row['Create Table'], 'products_ibfk_1') === false) {
             echo "Error adding foreign key constraint to products table: " . $conn->error . "<br>";
        }
    }
}

$conn->close();
?>
