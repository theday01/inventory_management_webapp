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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_customers = "CREATE TABLE IF NOT EXISTS customers (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$sql_invoice_items = "CREATE TABLE IF NOT EXISTS invoice_items (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(6) UNSIGNED,
    product_id INT(6) UNSIGNED,
    product_name VARCHAR(255) NOT NULL DEFAULT 'منتج محذوف',
    quantity INT(6) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
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
    'business_days' => $sql_business_days
];

foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<div style='color: green;'>✓ Table '$name' created successfully.</div>";
    } else {
        echo "<div style='color: red;'>✗ Error creating table '$name': " . $conn->error . "</div>";
    }
}

// ======================================
// INSERT DEFAULT SETTINGS
// ======================================
echo "<h3>Configuring Default Settings...</h3>";

$default_settings = [
    // Basic Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('currency', 'MAD') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('darkMode', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('shopCity', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Delivery Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryHomeCity', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryInsideCity', '10') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryOutsideCity', '30') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Stock Alert Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('low_quantity_alert', '30') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('critical_quantity_alert', '10') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('stockAlertsEnabled', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('stockAlertInterval', '20') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Tax Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxEnabled', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxRate', '20') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxLabel', 'TVA') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Virtual Keyboard Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardEnabled', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardTheme', 'system') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardSize', 'medium') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardVibrate', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardAutoSearch', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Logo Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('shopLogoUrl', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('invoiceShowLogo', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // Print Settings
    "INSERT INTO settings (setting_name, setting_value) VALUES ('printMode', 'normal') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('thermalPrinterWidth', '58mm') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('thermalPrinterCopies', '1') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
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
// ADD DEFAULT CATEGORIES
// ======================================
echo "<h3>Adding Default Categories...</h3>";

$check_categories = $conn->query("SELECT COUNT(*) as count FROM categories");
$category_count = $check_categories->fetch_assoc()['count'];

if ($category_count == 0) {
    $default_categories = [
        [
            "category" => "بقالة / سوبرماركت",
            "description" => "سلع غذائية ومستلزمات منزلية تُباع بالوحدة أو بالوزن",
            "custom_fields" => "باركود, الماركة, الوزن/الكمية, وحدة القياس, حجم العبوة, تاريخ الانتهاء, تاريخ الانتاج, شهادة حلال, المورد, سعر الشراء, نسبة الضريبة, كمية المخزون, حد إعادة الطلب, موقع التخزين"
        ],
        [
            "category" => "مخبز / معجنات",
            "description" => "مخبوزات طازجة ومجمدة",
            "custom_fields" => "تاريخ الخَبز, تاريخ الانتهاء, الوزن, حجم الحصة, المكونات, مجمَّد (نعم/لا), خاضع للضريبة, مورد, درجة حفظ (حرارة)"
        ],
        [
            "category" => "ملحمة / محل سمك",
            "description" => "لحوم وأسماك طازجة أو مجمدة تُباع بالقطع أو بالوزن",
            "custom_fields" => "نوع القطعة, الوزن, طازج أم مجمَّد, المصدر/المنشأ, تاريخ الصيد/التعبئة, تاريخ الانتهاء, مورد, درجة حفظ"
        ],
        [
            "category" => "حلويات / شوكولاتة",
            "description" => "حلويات معبأة ومنتجات شوكولاتة",
            "custom_fields" => "نسبة الكاكاو, معلومات الحساسية, الوزن, عدد القطع, تاريخ الانتهاء, المكونات, سعرات لكل حصة"
        ],
        [
            "category" => "منتجات ألبان",
            "description" => "حليب، أجبان، زبادي ومنتجات مشتقة",
            "custom_fields" => "نسبة الدسم, مبستر (نعم/لا), تاريخ الانتهاء, حجم العبوة, درجة الحفظ, خالي من اللاكتوز (نعم/لا), الماركة, مورد"
        ],
        [
            "category" => "ملابس جاهزة (رجال، نساء، أطفال، رضع)",
            "description" => "ملابس جاهزة للبيع بالتجزئة",
            "custom_fields" => "مقاس, لون, خامة/مادة, مخصص للجنس (رجالي/نسائي/أطفال/رضع), الماركة, رمز الصنف (SKU), الموسم, قابل للإرجاع (نعم/لا), تعليمات الغسيل, كمية المخزون, الفئة العمرية"
        ],
        [
            "category" => "أحذية وشنط",
            "description" => "أحذية وحقائب وإكسسوارات جلدية/نسيجية",
            "custom_fields" => "مقاس_EU, مقاس_US, لون, مادة, مخصص للجنس, الماركة, SKU, مقاوم للماء (نعم/لا), كمية المخزون"
        ],
        [
            "category" => "إلكترونيات (هواتف، تلفزيون، أجهزة صوت)",
            "description" => "أجهزة إلكترونية استهلاكية وإلكترونيات شخصية",
            "custom_fields" => "الماركة, الموديل, رقم_القطعة/سيريال, IMEI (للهواتف), سعة_تخزين_GB, ذاكرة_RAM_GB, لون, ضمان_شهور, البطارية_مشمولة (نعم/لا), مواصفات_طاقة, نظام_تشغيل"
        ]
    ];

    $stmt_category = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt_field = $conn->prepare("INSERT INTO category_fields (category_id, field_name, field_type) VALUES (?, ?, ?)");

    $total_categories = 0;
    $total_fields = 0;

    foreach ($default_categories as $category) {
        $stmt_category->bind_param("ss", $category['category'], $category['description']);
        if ($stmt_category->execute()) {
            $category_id = $stmt_category->insert_id;
            $total_categories++;
            
            if (!empty($category['custom_fields'])) {
                $fields = explode(',', $category['custom_fields']);
                foreach ($fields as $field) {
                    $field = trim($field);
                    if (!empty($field)) {
                        $field_type = 'text';
                        $stmt_field->bind_param("iss", $category_id, $field, $field_type);
                        if ($stmt_field->execute()) {
                            $total_fields++;
                        }
                    }
                }
            }
        }
    }

    $stmt_category->close();
    $stmt_field->close();

    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ تم إضافة <strong>$total_categories</strong> فئة تجارية مع <strong>$total_fields</strong> حقل مخصص بنجاح!";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeeba; border-radius: 5px; margin: 10px 0;'>";
    echo "ℹ️ توجد بالفعل فئات في النظام ($category_count فئة). لم يتم إضافة الفئات الافتراضية.";
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
echo "<li>✓ Default categories added</li>";
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