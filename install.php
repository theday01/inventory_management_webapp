<?php
// Database connection details
$servername = "127.0.0.1";
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
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql_create_db) === TRUE) {
    echo "Database '$dbname' created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Set charset
$conn->set_charset("utf8mb4");

echo "<h2>Starting Installation...</h2>";

// SQL to create tables
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier',
    first_login BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_security_questions = "CREATE TABLE IF NOT EXISTS security_questions (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    question TEXT NOT NULL,
    answer VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_categories = "CREATE TABLE IF NOT EXISTS categories (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_products = "CREATE TABLE IF NOT EXISTS products (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    cost_price DECIMAL(10, 2) DEFAULT 0,
    quantity INT(6) NOT NULL,
    category_id INT(6) UNSIGNED,
    barcode VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_customers = "CREATE TABLE IF NOT EXISTS customers (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// UPDATED: Refunds Table
$sql_refunds = "CREATE TABLE IF NOT EXISTS refunds (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(6) UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    items_json TEXT,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_invoices = "CREATE TABLE IF NOT EXISTS invoices (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT(6) UNSIGNED,
    total DECIMAL(10, 2) NOT NULL,
    discount_percent DECIMAL(5, 2) DEFAULT 0.00,
    discount_amount DECIMAL(10, 2) DEFAULT 0.00,
    delivery_city VARCHAR(100) NULL,
    delivery_cost DECIMAL(10, 2) NULL DEFAULT 0,
    barcode VARCHAR(50),
    payment_method VARCHAR(50) NOT NULL DEFAULT 'cash',
    amount_received DECIMAL(10, 2) DEFAULT 0.00,
    change_due DECIMAL(10, 2) DEFAULT 0.00,
    is_holiday BOOLEAN DEFAULT FALSE,
    holiday_name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_invoice_items = "CREATE TABLE IF NOT EXISTS invoice_items (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(6) UNSIGNED,
    product_id INT(6) UNSIGNED,
    product_name VARCHAR(255) NOT NULL DEFAULT 'منتج محذوف',
    quantity INT(6) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    cost_price DECIMAL(10, 2) DEFAULT 0,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_settings = "CREATE TABLE IF NOT EXISTS settings (
    setting_name VARCHAR(255) PRIMARY KEY,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_category_fields = "CREATE TABLE IF NOT EXISTS category_fields (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT(6) UNSIGNED NOT NULL,
    field_name VARCHAR(255) NOT NULL,
    field_type VARCHAR(50) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_product_field_values = "CREATE TABLE IF NOT EXISTS product_field_values (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT(6) UNSIGNED NOT NULL,
    field_id INT(6) UNSIGNED NOT NULL,
    value TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES category_fields(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_removed_products = "CREATE TABLE IF NOT EXISTS removed_products (
    id INT(6) UNSIGNED NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT(6) NOT NULL,
    category_id INT(6) UNSIGNED,
    barcode VARCHAR(255),
    image VARCHAR(255),
    created_at TIMESTAMP NULL,
    removed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_notifications = "CREATE TABLE IF NOT EXISTS notifications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_rental_payments = "CREATE TABLE IF NOT EXISTS rental_payments (
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

$sql_media_gallery = "CREATE TABLE IF NOT EXISTS media_gallery (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(255) NOT NULL UNIQUE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_holidays = "CREATE TABLE IF NOT EXISTS holidays (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    date DATE NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_expenses = "CREATE TABLE IF NOT EXISTS expenses (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) DEFAULT 'general',
    expense_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// UPDATED: business_days table with nullable user_id and better foreign key constraint
$sql_business_days = "CREATE TABLE IF NOT EXISTS business_days (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    start_time DATETIME NOT NULL,
    end_time DATETIME NULL,
    opening_balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    closing_balance DECIMAL(10, 2) NULL,
    user_id INT(6) UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Execute table creation queries in proper order
$tables = [
    'users' => $sql_users,
    'security_questions' => $sql_security_questions,
    'categories' => $sql_categories,
    'products' => $sql_products,
    'customers' => $sql_customers,
    'invoices' => $sql_invoices,
    'invoice_items' => $sql_invoice_items,
    'settings' => $sql_settings,
    'category_fields' => $sql_category_fields,
    'product_field_values' => $sql_product_field_values,
    'removed_products' => $sql_removed_products,
    'notifications' => $sql_notifications,
    'rental_payments' => $sql_rental_payments,
    'media_gallery' => $sql_media_gallery,
    'holidays' => $sql_holidays,
    'expenses' => $sql_expenses,
    'refunds' => $sql_refunds,
    'business_days' => $sql_business_days
];

foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<div style='color: green;'>✓ Table '$name' created successfully.</div>";
    } else {
        echo "<div style='color: red;'>✗ Error creating table '$name': " . $conn->error . "</div>";
    }
}

// Add first_login column if it doesn't exist
$check_column_sql = "SHOW COLUMNS FROM `users` LIKE 'first_login'";
$result = $conn->query($check_column_sql);
if ($result && $result->num_rows == 0) {
    $alter_users = "ALTER TABLE users ADD COLUMN first_login BOOLEAN DEFAULT FALSE";
    if ($conn->query($alter_users) === TRUE) {
        echo "<div style='color: green;'>✓ Column 'first_login' successfully added to users table.</div>";
    } else {
        echo "<div style='color: red;'>✗ Error adding column 'first_login': " . $conn->error . "</div>";
    }
} else {
    echo "<div style='color: #fff3cd;'>ℹ️ Column 'first_login' already exists. Skipping.</div>";
}

// Add is_holiday column to invoices if it doesn't exist
$check_invoice_column_sql = "SHOW COLUMNS FROM `invoices` LIKE 'is_holiday'";
$result_invoice = $conn->query($check_invoice_column_sql);
if ($result_invoice && $result_invoice->num_rows == 0) {
    $alter_invoices = "ALTER TABLE invoices ADD COLUMN is_holiday BOOLEAN DEFAULT FALSE";
    if ($conn->query($alter_invoices) === TRUE) {
        echo "<div style='color: green;'>✓ Column 'is_holiday' successfully added to invoices table.</div>";
    } else {
        echo "<div style='color: red;'>✗ Error adding column 'is_holiday': " . $conn->error . "</div>";
    }
} else {
    echo "<div style='color: #fff3cd;'>ℹ️ Column 'is_holiday' already exists in invoices table. Skipping.</div>";
}

// Add holiday_name column to invoices if it doesn't exist
$check_invoice_name_column_sql = "SHOW COLUMNS FROM `invoices` LIKE 'holiday_name'";
$result_invoice_name = $conn->query($check_invoice_name_column_sql);
if ($result_invoice_name && $result_invoice_name->num_rows == 0) {
    $alter_invoices_name = "ALTER TABLE invoices ADD COLUMN holiday_name VARCHAR(255) DEFAULT NULL";
    if ($conn->query($alter_invoices_name) === TRUE) {
        echo "<div style='color: green;'>✓ Column 'holiday_name' successfully added to invoices table.</div>";
    } else {
        echo "<div style='color: red;'>✗ Error adding column 'holiday_name': " . $conn->error . "</div>";
    }
} else {
    echo "<div style='color: #fff3cd;'>ℹ️ Column 'holiday_name' already exists in invoices table. Skipping.</div>";
}

// Add cost_price column to invoice_items if it doesn't exist
$check_ii_cost_sql = "SHOW COLUMNS FROM `invoice_items` LIKE 'cost_price'";
$result_ii_cost = $conn->query($check_ii_cost_sql);
if ($result_ii_cost && $result_ii_cost->num_rows == 0) {
    $alter_ii = "ALTER TABLE invoice_items ADD COLUMN cost_price DECIMAL(10, 2) DEFAULT 0";
    if ($conn->query($alter_ii) === TRUE) {
        echo "<div style='color: green;'>✓ Column 'cost_price' successfully added to invoice_items table.</div>";
    } else {
        echo "<div style='color: red;'>✗ Error adding column 'cost_price': " . $conn->error . "</div>";
    }
} else {
    echo "<div style='color: #fff3cd;'>ℹ️ Column 'cost_price' already exists in invoice_items table. Skipping.</div>";
}

// Add paid_from_drawer column to expenses if it doesn't exist
$check_exp_drawer = "SHOW COLUMNS FROM `expenses` LIKE 'paid_from_drawer'";
$result_exp_drawer = $conn->query($check_exp_drawer);
if ($result_exp_drawer && $result_exp_drawer->num_rows == 0) {
    $alter_exp = "ALTER TABLE expenses ADD COLUMN paid_from_drawer BOOLEAN DEFAULT FALSE";
    if ($conn->query($alter_exp) === TRUE) {
        echo "<div style='color: green;'>✓ Column 'paid_from_drawer' successfully added to expenses table.</div>";
    } else {
        echo "<div style='color: red;'>✗ Error adding column 'paid_from_drawer': " . $conn->error . "</div>";
    }
} else {
    echo "<div style='color: #fff3cd;'>ℹ️ Column 'paid_from_drawer' already exists in expenses table. Skipping.</div>";
}

// Add is_active column to holidays if it doesn't exist
$check_holidays_active = "SHOW COLUMNS FROM `holidays` LIKE 'is_active'";
$result_holidays_active = $conn->query($check_holidays_active);
if ($result_holidays_active && $result_holidays_active->num_rows == 0) {
    $alter_holidays = "ALTER TABLE holidays ADD COLUMN is_active BOOLEAN DEFAULT TRUE";
    if ($conn->query($alter_holidays) === TRUE) {
        echo "<div style='color: green;'>✓ Column 'is_active' successfully added to holidays table.</div>";
    } else {
        echo "<div style='color: red;'>✗ Error adding column 'is_active': " . $conn->error . "</div>";
    }
} else {
    echo "<div style='color: #fff3cd;'>ℹ️ Column 'is_active' already exists in holidays table. Skipping.</div>";
}

// ======================================
// INSERT DEFAULT SETTINGS
// ======================================
echo "<h3>Configuring Default Settings...</h3>";

$default_settings = [
    // Basic Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('system_language', 'ar') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('currency', 'MAD') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('darkMode', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('shopCity', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('work_days_enabled', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('holidays_enabled', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('work_days', 'monday,tuesday,wednesday,thursday,friday,saturday') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Delivery Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryHomeCity', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryInsideCity', '10') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryOutsideCity', '30') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('enable_delivery', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Stock Alert Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('low_quantity_alert', '30') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('critical_quantity_alert', '10') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('stockAlertsEnabled', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('stockAlertInterval', '20') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Tax Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxEnabled', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxRate', '20') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxLabel', 'TVA') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('enable_discount', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Virtual Keyboard Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardEnabled', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardTheme', 'system') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardSize', 'medium') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardVibrate', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardAutoSearch', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Logo Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('shopLogoUrl', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('shopFavicon', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('invoiceShowLogo', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Print Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('printMode', 'normal') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('thermalPrinterWidth', '58mm') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('thermalPrinterCopies', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('expense_cycle', 'monthly') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('expense_cycle_last_change', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Backup Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('backup_enabled', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('backup_frequency', 'daily') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('last_backup_run', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",

    // End of Day Reminder Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('day_start_time', '05:00') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('day_end_time', '00:00') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('end_day_reminder_enabled', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",

    // Rental Settings (NEW SYSTEM) - Only added when user saves
    // No default values forced during installation
];

$settings_success = 0;
foreach ($default_settings as $query) {
    if ($conn->query($query) === TRUE) {
        $settings_success++;
    } else {
        echo "<div style='color: orange;'>Warning: " . $conn->error . "</div>";
    }
}

echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
echo "✅ Successfully configured $settings_success settings.";
echo "</div>";

// ======================================
// ADD DEFAULT CATEGORIES (REMOVED)
// ======================================
// echo "<h3>Adding Default Categories...</h3>";
// Default categories installation removed as per user request.
// Categories should be created manually.

// ======================================
// ADD DEFAULT HOLIDAYS
// ======================================
echo "<h3>Adding Default Holidays...</h3>";

$check_holidays = $conn->query("SELECT COUNT(*) as count FROM holidays");
$holiday_count = $check_holidays->fetch_assoc()['count'];

if ($holiday_count == 0) {
    $default_holidays = [
        ['2025-01-01', 'رأس السنة الميلادية'],
        ['2025-01-11', 'تقديم وثيقة الاستقلال'],
        ['2025-05-01', 'عيد الشغل'],
        ['2025-07-30', 'عيد العرش'],
        ['2025-08-14', 'ذكرى استرجاع إقليم وادي الذهب'],
        ['2025-08-20', 'ذكرى ثورة الملك والشعب'],
        ['2025-08-21', 'عيد الشباب'],
        ['2025-11-06', 'ذكرى المسيرة الخضراء'],
        ['2025-11-18', 'عيد الاستقلال'],
        ['2024-11-06', 'ذكرى المسيرة الخضراء'],
        ['2024-11-18', 'عيد الاستقلال'],
    ];

    $stmt_holiday = $conn->prepare("INSERT IGNORE INTO holidays (date, name) VALUES (?, ?)");
    $total_holidays = 0;

    foreach ($default_holidays as $holiday) {
        $stmt_holiday->bind_param("ss", $holiday[0], $holiday[1]);
        if ($stmt_holiday->execute()) {
            if ($conn->affected_rows > 0) {
                $total_holidays++;
            }
        }
    }
    $stmt_holiday->close();

    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ تم إضافة <strong>$total_holidays</strong> عطلة رسمية بنجاح!";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeeba; border-radius: 5px; margin: 10px 0;'>";
    echo "ℹ️ توجد بالفعل عطلات في النظام ($holiday_count عطلة). لم يتم إضافة العطلات الافتراضية.";
    echo "</div>";
}

// ======================================
// FINAL SUMMARY
// ======================================
echo "<br><div style='background: #d1ecf1; padding: 20px; border: 2px solid #bee5eb; border-radius: 5px; margin: 20px 0;'>";
echo "<h2 style='color: #0c5460; margin-top: 0;'>✅ Installation Complete!</h2>";
echo "<hr>";
echo "<h3>What's Next?</h3>";
echo "<ul>";
echo "<li>✓ Database and tables created successfully</li>";
echo "<li>✓ System settings configured</li>";
echo "<li>✓ Business days system ready (supports nullable user_id)</li>";
echo "</ul>";
echo "<hr>";
echo "<p><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Login Page</a></p>";
echo "</div>";

// Display current users
$result = $conn->query("SELECT id, username, role FROM users");
if ($result && $result->num_rows > 0) {
    echo "<h3>Current Users in System:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Username</th><th>Role</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Shop - Installation Complete</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h2, h3 {
            color: #333;
        }
    </style>
</head>
<body>
</body>
</html>