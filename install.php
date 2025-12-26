<?php
/**
 * Smart Shop - Database Installation Script
 * This script creates the database and all necessary tables
 */

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'smart_shop';

// Create connection without selecting database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "✓ Database created successfully or already exists<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Start creating tables
echo "<h2>Creating tables...</h2>";

// 1. Users table
$sql = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100),
    `phone` VARCHAR(20),
    `role` ENUM('admin', 'manager', 'cashier') DEFAULT 'cashier',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Users table created successfully<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// 2. Categories table
$sql = "CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `name_en` VARCHAR(100),
    `description` TEXT,
    `icon` VARCHAR(50),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Categories table created successfully<br>";
} else {
    echo "Error creating categories table: " . $conn->error . "<br>";
}

// 3. Products table
$sql = "CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `barcode` VARCHAR(100) UNIQUE,
    `sku` VARCHAR(100) UNIQUE,
    `category_id` INT,
    `description` TEXT,
    `purchase_price` DECIMAL(10, 2) DEFAULT 0,
    `selling_price` DECIMAL(10, 2) NOT NULL,
    `quantity` INT DEFAULT 0,
    `min_stock_alert` INT DEFAULT 5,
    `image` VARCHAR(255),
    `status` ENUM('available', 'low_stock', 'out_of_stock') DEFAULT 'available',
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Products table created successfully<br>";
} else {
    echo "Error creating products table: " . $conn->error . "<br>";
}

// 4. Customers table
$sql = "CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) UNIQUE NOT NULL,
    `email` VARCHAR(100),
    `address` TEXT,
    `total_purchases` DECIMAL(10, 2) DEFAULT 0,
    `total_visits` INT DEFAULT 0,
    `notes` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Customers table created successfully<br>";
} else {
    echo "Error creating customers table: " . $conn->error . "<br>";
}

// 5. Invoices table
$sql = "CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(50) UNIQUE NOT NULL,
    `customer_id` INT,
    `user_id` INT,
    `subtotal` DECIMAL(10, 2) NOT NULL,
    `tax_rate` DECIMAL(5, 2) DEFAULT 15.00,
    `tax_amount` DECIMAL(10, 2) NOT NULL,
    `discount` DECIMAL(10, 2) DEFAULT 0,
    `total` DECIMAL(10, 2) NOT NULL,
    `payment_method` ENUM('cash', 'card', 'transfer') DEFAULT 'cash',
    `status` ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Invoices table created successfully<br>";
} else {
    echo "Error creating invoices table: " . $conn->error . "<br>";
}

// 6. Invoice Items table
$sql = "CREATE TABLE IF NOT EXISTS `invoice_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_id` INT NOT NULL,
    `product_id` INT,
    `product_name` VARCHAR(200) NOT NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10, 2) NOT NULL,
    `total_price` DECIMAL(10, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Invoice Items table created successfully<br>";
} else {
    echo "Error creating invoice_items table: " . $conn->error . "<br>";
}

// 7. Settings table
$sql = "CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT,
    `setting_type` VARCHAR(50),
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Settings table created successfully<br>";
} else {
    echo "Error creating settings table: " . $conn->error . "<br>";
}

// 8. Printers table
$sql = "CREATE TABLE IF NOT EXISTS `printers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `is_default` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Printers table created successfully<br>";
} else {
    echo "Error creating printers table: " . $conn->error . "<br>";
}

echo "<h2>Inserting default data...</h2>";

// Insert default admin user (password: admin123)
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) 
        VALUES ('admin', '$password_hash', 'أحمد محمد', 'admin@smartshop.com', 'admin')
        ON DUPLICATE KEY UPDATE `username`='admin'";

if ($conn->query($sql) === TRUE) {
    echo "✓ Default admin user created (username: admin, password: admin123)<br>";
} else {
    echo "Error inserting admin user: " . $conn->error . "<br>";
}

// Insert default categories
$categories = [
    ['إلكترونيات', 'Electronics', 'smartphone'],
    ['ملابس', 'Clothing', 'checkroom'],
    ['إكسسوارات', 'Accessories', 'watch']
];

foreach ($categories as $cat) {
    $sql = "INSERT INTO `categories` (`name`, `name_en`, `icon`) 
            VALUES ('$cat[0]', '$cat[1]', '$cat[2]')
            ON DUPLICATE KEY UPDATE `name`='$cat[0]'";
    $conn->query($sql);
}
echo "✓ Default categories inserted<br>";

// Insert default customer (Walk-in)
$sql = "INSERT INTO `customers` (`name`, `phone`, `email`) 
        VALUES ('عميل نقدي', '0000000000', 'walkin@smartshop.com')
        ON DUPLICATE KEY UPDATE `name`='عميل نقدي'";

if ($conn->query($sql) === TRUE) {
    echo "✓ Default walk-in customer created<br>";
} else {
    echo "Error inserting default customer: " . $conn->error . "<br>";
}

// Insert default settings
$settings = [
    ['shop_name', 'متجر Smart Shop للإلكترونيات', 'text'],
    ['shop_phone', '0512345678', 'text'],
    ['shop_address', 'الرياض، المملكة العربية السعودية', 'text'],
    ['shop_description', 'أفضل متجر لبيع الإلكترونيات الحديثة وملحقاتها', 'text'],
    ['tax_rate', '15', 'number'],
    ['currency', 'ر.س', 'text'],
    ['dark_mode', '1', 'boolean'],
    ['sound_notifications', '1', 'boolean'],
    ['low_stock_alert', '5', 'number']
];

foreach ($settings as $setting) {
    $sql = "INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) 
            VALUES ('$setting[0]', '$setting[1]', '$setting[2]')
            ON DUPLICATE KEY UPDATE `setting_value`='$setting[1]'";
    $conn->query($sql);
}
echo "✓ Default settings inserted<br>";

// Insert default printer
$sql = "INSERT INTO `printers` (`name`, `type`, `description`, `is_default`) 
        VALUES ('Epson TM-T88VI', 'thermal', 'طابعة الإيصالات الحرارية', 1)
        ON DUPLICATE KEY UPDATE `name`='Epson TM-T88VI'";

if ($conn->query($sql) === TRUE) {
    echo "✓ Default printer created<br>";
} else {
    echo "Error inserting default printer: " . $conn->error . "<br>";
}

echo "<h2 style='color: green;'>✓ Installation completed successfully!</h2>";
echo "<p>Database and all tables have been created.</p>";
echo "<p><strong>Default Login Credentials:</strong></p>";
echo "<ul>";
echo "<li>Username: <strong>admin</strong></li>";
echo "<li>Password: <strong>admin123</strong></li>";
echo "</ul>";
echo "<p style='color: red;'><strong>Important:</strong> Please delete this install.php file after installation for security reasons.</p>";
echo "<p><a href='login.html' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 20px;'>Go to Login Page</a></p>";

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Shop - Installation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #0E1116;
            color: #fff;
        }
        h2 {
            color: #3B82F6;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 10px;
        }
        ul {
            background: #1F2937;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #84CC16;
        }
        li {
            margin: 10px 0;
        }
    </style>
</head>
<body>
</body>
</html>