<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "smart_shop";

// Connect as the new user
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

if ($conn->query($sql_users) === TRUE) {
    echo "Table 'users' created successfully<br>";
} else {
    echo "Error creating table 'users': " . $conn->error . "<br>";
}

if ($conn->query($sql_products) === TRUE) {
    echo "Table 'products' created successfully<br>";
} else {
    echo "Error creating table 'products': " . $conn->error . "<br>";
}

if ($conn->query($sql_customers) === TRUE) {
    echo "Table 'customers' created successfully<br>";
} else {
    echo "Error creating table 'customers': " . $conn->error . "<br>";
}

if ($conn->query($sql_invoices) === TRUE) {
    echo "Table 'invoices' created successfully<br>";
} else {
    echo "Error creating table 'invoices': " . $conn->error . "<br>";
}

if ($conn->query($sql_invoice_items) === TRUE) {
    echo "Table 'invoice_items' created successfully<br>";
} else {
    echo "Error creating table 'invoice_items': " . $conn->error . "<br>";
}

$conn->close();
?>
