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

// SQL to create tables (Existing tables...)
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
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$sql_customers = "CREATE TABLE IF NOT EXISTS customers (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(50),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$sql_invoices = "CREATE TABLE IF NOT EXISTS invoices (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT(6) UNSIGNED,
    total DECIMAL(10, 2) NOT NULL,
    barcode VARCHAR(50),
    payment_method VARCHAR(50) NOT NULL DEFAULT 'cash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
)";

$sql_invoice_items = "CREATE TABLE IF NOT EXISTS invoice_items (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT(6) UNSIGNED,
    product_id INT(6) UNSIGNED,
    product_name VARCHAR(255) NOT NULL,
    quantity INT(6) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
)";

$sql_settings = "CREATE TABLE IF NOT EXISTS settings (
    setting_name VARCHAR(255) PRIMARY KEY,
    setting_value TEXT NOT NULL
)";

$sql_categories = "CREATE TABLE IF NOT EXISTS categories (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
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
)";

$sql_notifications = "CREATE TABLE IF NOT EXISTS notifications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

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
)";

$sql_media_gallery = "CREATE TABLE IF NOT EXISTS media_gallery (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(255) NOT NULL UNIQUE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Execute table creation queries
$tables = [
    'users' => $sql_users,
    'media_gallery' => $sql_media_gallery,
    'products' => $sql_products,
    'removed_products' => $sql_removed_products,
    'customers' => $sql_customers,
    'invoices' => $sql_invoices,
    'invoice_items' => $sql_invoice_items,
    'settings' => $sql_settings,
    'categories' => $sql_categories,
    'category_fields' => $sql_category_fields,
    'product_field_values' => $sql_product_field_values,
    'notifications' => $sql_notifications,
    'rental_payments' => $sql_rental_payments
];

foreach ($tables as $name => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table '$name' created successfully.<br>";
    } else {
        echo "Error creating table '$name': " . $conn->error . "<br>";
    }
}
// إضافة حقل payment_method إلى جدول invoices إذا لم يكن موجوداً
$check_payment_method = $conn->query("SHOW COLUMNS FROM invoices LIKE 'payment_method'");
if ($check_payment_method->num_rows == 0) {
    $sql_alter_invoices_payment = "ALTER TABLE invoices ADD COLUMN payment_method VARCHAR(50) NOT NULL DEFAULT 'cash' AFTER barcode";
    if ($conn->query($sql_alter_invoices_payment) === TRUE) {
        echo "Column 'payment_method' added to invoices table successfully.<br>";
    } else {
        echo "Error adding column 'payment_method' to invoices table: " . $conn->error . "<br>";
    }
}

// Add cost_price column to products table if it doesn't exist
$check_cost_price = $conn->query("SHOW COLUMNS FROM products LIKE 'cost_price'");
if ($check_cost_price->num_rows == 0) {
    $sql_alter_products_cost = "ALTER TABLE products ADD COLUMN cost_price DECIMAL(10, 2) DEFAULT 0 AFTER price";
    if ($conn->query($sql_alter_products_cost) === TRUE) {
        echo "Column 'cost_price' added to products table successfully.<br>";
    } else {
        echo "Error adding column 'cost_price' to products table: " . $conn->error . "<br>";
    }
}

// ========================================
// 1. Virtual Keyboard Settings
// ========================================
echo "<h3>Configuring Virtual Keyboard...</h3>";

$vk_inserts = [
    // Enable/Disable the feature globally
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardEnabled', '0') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    
    // Theme: 'dark', 'light', 'system'
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardTheme', 'system') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    
    // Size: 'small', 'medium', 'large'
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardSize', 'medium') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    
    // Haptic feedback (Vibration)
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardVibrate', '0') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    
    // Auto show on search input focus
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardAutoSearch', '1') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
];

foreach ($vk_inserts as $q) {
    if ($conn->query($q) === TRUE) {
        // Success
    } else {
        echo "Error applying virtual keyboard setting: " . $conn->error . "<br>";
    }
}
echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>✅ Virtual Keyboard settings configured successfully.</div>";


// Insert default currency setting
$sql_insert_currency = "INSERT INTO settings (setting_name, setting_value) VALUES ('currency', 'MAD') ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)";
if ($conn->query($sql_insert_currency) === TRUE) {
    echo "Default currency setting inserted successfully.<br>";
} else {
    echo "Error inserting default currency setting: " . $conn->error . "<br>";
}

// Add foreign key constraint to products table
$sql_fk_products_category = "ALTER TABLE products ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL";

$result = $conn->query("SHOW CREATE TABLE products");
$row = $result->fetch_assoc();
if (strpos($row['Create Table'], 'products_ibfk_') === false) {
    if ($conn->query($sql_fk_products_category) === TRUE) {
        echo "Foreign key constraint added to products table successfully.<br>";
    } else {
        echo "Error adding foreign key constraint to products table: " . $conn->error . "<br>";
    }
}


// Add city column to customers table if it doesn't exist
$check_city = $conn->query("SHOW COLUMNS FROM customers LIKE 'city'");
if ($check_city->num_rows == 0) {
    $sql_alter_customers = "ALTER TABLE customers ADD COLUMN city VARCHAR(100) DEFAULT NULL AFTER address";
    if ($conn->query($sql_alter_customers) === TRUE) {
        echo "Column 'city' added to customers table successfully.<br>";
    } else {
        echo "Error adding column 'city' to customers table: " . $conn->error . "<br>";
    }
}

// Add tax settings
$tax_inserts = [
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxEnabled', '1') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxRate', '20') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('taxLabel', 'TVA') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
];

foreach ($tax_inserts as $q) {
    if ($conn->query($q) === TRUE) {
        // Success
    } else {
        echo "Error applying tax setting: " . $conn->error . "<br>";
    }
}

// Virtual Keyboard default settings
$vk_inserts = [
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardEnabled', '0') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardTheme', 'system') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardSize', 'medium') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardVibrate', '0') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('virtualKeyboardAutoSearch', '1') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
];
foreach ($vk_inserts as $q) {
    if ($conn->query($q) === TRUE) {
        // Success
    } else {
        echo "Error applying virtual keyboard setting: " . $conn->error . "<br>";
    }
}

$logo_settings = [
    "INSERT INTO settings (setting_name, setting_value) VALUES ('shopLogoUrl', '') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('invoiceShowLogo', '0') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
];
foreach ($logo_settings as $q) {
    if ($conn->query($q) !== TRUE) {
        echo "Error applying logo settings: " . $conn->error . "<br>";
    }
}

// Remove deprecated shopDescription setting if exists
$conn->query("DELETE FROM settings WHERE setting_name = 'shopDescription'");

// Verify and display the added settings
$result = $conn->query("SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('taxEnabled', 'taxRate', 'taxLabel')");
if ($result) {
    if ($result->num_rows > 0) {
        echo "<h3>Tax settings</h3>";
        echo "<table border='1' cellpadding='6' style='border-collapse:collapse;'>";
        echo "<tr><th>setting_name</th><th>setting_value</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>" . htmlspecialchars($row['setting_name']) . "</td><td>" . htmlspecialchars($row['setting_value']) . "</td></tr>";
        }
        echo "</table><br>";
    } else {
        echo "Tax settings not found after insert/update.<br>";
    }
} else {
    echo "Error verifying tax settings: " . $conn->error . "<br>";
}

// ========================================
// إدراج الفئات الافتراضية
// ========================================
echo "<h3>إضافة الفئات الافتراضية...</h3>";

// التحقق من وجود فئات مسبقاً
$check_categories = $conn->query("SELECT COUNT(*) as count FROM categories");
$category_count = $check_categories->fetch_assoc()['count'];

if ($category_count == 0) {
    // البيانات الافتراضية للفئات
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
            "category" => "مطاعم / كافيهات / فود ترك",
            "description" => "أصناف قائمة طعام وخدمات تقديم طعام",
            "custom_fields" => "اسم الصنف, حجم/حصة, زمن التحضير (دقيقة), نباتي (نعم/لا), نباتي Strict (نعم/لا), يحتوي مسبب للحساسية, يتطلب مطبخ (نعم/لا), نسبة الضريبة, محطة تجهيز/مطبخ"
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
            "category" => "مجوهرات وساعات",
            "description" => "قطع مجوهرات ثمينة وساعات",
            "custom_fields" => "نوع المعدن, نوع الحجر الكريم, وزن_جرام, قيرات (Carat), ختم/علامة (Hallmark), أبعاد, رقم تسلسلي, ضمان_بالأشهر"
        ],
        [
            "category" => "نظارات شمسية وبصرية",
            "description" => "نظارات شمسية ووصفات طبية وإطارات عدسات",
            "custom_fields" => "هل تحتاج وصفة طبية, مادة الإطار, نوع العدسة, وقاية UV (نعم/لا), حجم الإطار, الماركة"
        ],
        [
            "category" => "أثاث وديكور",
            "description" => "قطع أثاث وقطع ديكور داخلية وخارجية",
            "custom_fields" => "الأبعاد_سم, المادة, اللون, يتطلب تجميع (نعم/لا), وزن_كجم, سعة_تحميل, ضمان_شهور, SKU, حالة العرض (معروض/في المخزن)"
        ],
        [
            "category" => "مستلزمات المطبخ والمنزل",
            "description" => "أدوات منزلية وأجهزة صغيرة",
            "custom_fields" => "رقم الطراز, الأبعاد, المادة, قدرة كهربائية/واط, ضمان_شهور, SKU"
        ],
        [
            "category" => "سجاد وستائر",
            "description" => "سجاد، مفروشات وستائر منزلية",
            "custom_fields" => "المقاس, المادة, ارتفاع الوبر, النمط, تعليمات العناية, SKU"
        ],
        [
            "category" => "إلكترونيات (هواتف، تلفزيون، أجهزة صوت)",
            "description" => "أجهزة إلكترونية استهلاكية وإلكترونيات شخصية",
            "custom_fields" => "الماركة, الموديل, رقم_القطعة/سيريال, IMEI (للهواتف), سعة_تخزين_GB, ذاكرة_RAM_GB, لون, ضمان_شهور, البطارية_مشمولة (نعم/لا), مواصفات_طاقة, نظام_تشغيل"
        ],
        [
            "category" => "كمبيوترات وإكسسوارات",
            "description" => "حواسيب سطحية ومحمولة ومكوّنات وإكسسوارات",
            "custom_fields" => "المعالج, RAM_GB, التخزين_GB, GPU, نظام_تشغيل, رقم_سيريال, ضمان_شهور, ملحقات_مرفقة"
        ],
        [
            "category" => "أدوات ومستلزمات بناء",
            "description" => "أدوات يدوية وكهربائية ومواد بناء",
            "custom_fields" => "رقم_القطعة, نوع_المادة, الطول_م, تغطية_المساحة_م2, الوزن_كجم, درجة_المنتج, مورد"
        ],
        [
            "category" => "حدائق ونباتات",
            "description" => "نباتات زينة، بذور، أحواض ومستلزمات الحدائق",
            "custom_fields" => "نوع_النبات, حجم_الوعاء_سم, احتياجات_الضوء, احتياجات_الري, نبات_سنوي_أم_معمر, موسم_الإزهار, تعليمات_العناية"
        ],
        [
            "category" => "صيدليات و пара-صيدليات",
            "description" => "أدوية ومستلزمات طبية وصحية",
            "custom_fields" => "هل يتطلب وصفة طبية, المادة_الفعالة, شكل_الدواء, التركيز/الجرعة, تاريخ_الانتهاء, رقم_الدفعة, الشركة_المنتجة"
        ],
        [
            "category" => "مكياج ومستلزمات تجميل",
            "description" => "منتجات تجميلية وعطور ومنتجات عناية شخصية",
            "custom_fields" => "المكونات, نوع_الرائحة, حجم_ml, نوع_البشرة, عامل_حماية_SPF, تاريخ_الانتهاء, خالٍ_من_القسوة (نعم/لا), درجة اللون, بلد الصنع"
        ],
        [
            "category" => "صالونات حلاقة وتجهيز (خدمة)",
            "description" => "خدمات حلاقة، تجميل وعناية شخصية تقدم كخدمة",
            "custom_fields" => "اسم_الخدمة, مدة_بالدقائق, دور_الموظف/المختص, يتطلب_حجز (نعم/لا), المواد_المستخدمة, نسبة_الضريبة"
        ],
        [
            "category" => "مكتبات ومستلزماتها",
            "description" => "كتب، مجلات ومواد مكتبية وتعليمية",
            "custom_fields" => "المؤلف, الناشر, ISBN, عدد_الصفحات, اللغة, الطبعة, الموضوع, المستوى_المدرسي, SKU, نوع الورق"
        ],
        [
            "category" => "ألعاب وهوايات",
            "description" => "ألعاب أطفال، ألعاب لوحية ومواد لهوايات مختلفة",
            "custom_fields" => "الفئة_العمرية, هل_تحتاج_بطاريات, عدد_اللاعبين, جانب_تعليمي (نعم/لا), المادة, الشركة_الصانعة"
        ],
        [
            "category" => "آلات موسيقية",
            "description" => "آلات ومستلزمات موسيقية",
            "custom_fields" => "نوع_الآلة, الماركة, الموديل, المقاس, ضمان_شهور, ملحقات_مرفقة"
        ],
        [
            "category" => "كاميرات ومعدات تصوير",
            "description" => "كاميرات، عدسات وإكسسوارات تصوير",
            "custom_fields" => "حجم_المستشعر, دقة_Megapixels, حامل_العدسة, ملحقات_مرفقة, رقم_سيريال, ضمان_شهور"
        ],
        [
            "category" => "محلات حيوانات أليفة ومستلزماتها",
            "description" => "حيوانات أليفة، أعلاف ومستلزمات رعاية",
            "custom_fields" => "النوع_البيولوجي, السلالة, العمر_بالأشهر, الوزن_كجم, متطلبات_الطعام, حالة_التطعيمات"
        ],
        [
            "category" => "قطع غيار سيارات وإكسسوارات",
            "description" => "قطع غيار أصلية أو عامة وملحقات سيارات",
            "custom_fields" => "رقم_القطعة, النماذج_المتوافقة, موضع_التركيب, الشركة_المنتجة, ضمان_شهور, كمية_المخزون"
        ],
        [
            "category" => "سلع مستعملة وتحف",
            "description" => "أغراض وعناصر عتيقة أو مستعملة",
            "custom_fields" => "الحالة, مصدر_القطعة, شهادة_الأصالة (إن وُجدت), الحقبة/العصر, المادة"
        ],
        [
            "category" => "خدمات إصلاح (ساعات/أحذية/أجهزة)",
            "description" => "خدمات تصليح وصيانة تقدم بمقابل",
            "custom_fields" => "نوع_الخدمة, الوقت_المقدَّر_بالأيام, قطع_مطلوبة, تكلفة_القطع, تكلفة_العمل, ضمان_خدمة_بأيام"
        ],
        [
            "category" => "أكشاك وباعة متجولين",
            "description" => "نقاط بيع صغيرة/مؤقتة في الشارع أو الأسواق",
            "custom_fields" => "تحتاج_تراخيص (نعم/لا), ساعات_العمل, رقم_البائع, قائمة_المنتجات"
        ],
        [
            "category" => "مكاتب خدمات (سفر، بنوك، بريد)",
            "description" => "خدمات إدارية وتجارية تُدفع فيها رسوم أو عمولات",
            "custom_fields" => "رمز_الخدمة, الرسوم, المستندات_المطلوبة, زمن_المعالجة, معرف_الموظف/الوكيل"
        ]
    ];

    // إدراج الفئات
    $stmt_category = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt_field = $conn->prepare("INSERT INTO category_fields (category_id, field_name, field_type) VALUES (?, ?, ?)");

    $total_categories = 0;
    $total_fields = 0;

    foreach ($default_categories as $category) {
        // إدراج الفئة
        $stmt_category->bind_param("ss", $category['category'], $category['description']);
        if ($stmt_category->execute()) {
            $category_id = $stmt_category->insert_id;
            $total_categories++;
            
            // إدراج الحقول المخصصة
            if (!empty($category['custom_fields'])) {
                $fields = explode(',', $category['custom_fields']);
                foreach ($fields as $field) {
                    $field = trim($field);
                    if (!empty($field)) {
                        $field_type = 'text';
                        $stmt_field->bind_param("iss", $category_id, $field, $field_type);
                        if ($stmt_field->execute()) {
                            $total_fields++;
                        } else {
                            echo "خطأ في إضافة الحقل '$field': " . $conn->error . "<br>";
                        }
                    }
                }
            }
        } else {
            echo "خطأ في إضافة الفئة '{$category['category']}': " . $conn->error . "<br>";
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

// عرض الفئات المضافة
$result = $conn->query("SELECT c.name, c.description, COUNT(cf.id) as field_count 
                        FROM categories c 
                        LEFT JOIN category_fields cf ON c.id = cf.category_id 
                        GROUP BY c.id 
                        ORDER BY c.name");

if ($result && $result->num_rows > 0) {
    echo "<h3>الفئات في النظام:</h3>";
    echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;'>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'><th>اسم الفئة</th><th>الوصف</th><th>عدد الحقول المخصصة</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $name = htmlspecialchars($row['name']);
        $desc = htmlspecialchars($row['description']);
        $field_count = $row['field_count'];
        
        echo "<tr><td><strong>$name</strong></td><td style='font-size: 0.9em;'>$desc</td><td style='text-align: center;'>$field_count</td></tr>";
    }
    echo "</table>";
    echo "</div>";
}

echo "<br><div style='background: #d1ecf1; padding: 15px; border: 1px solid #bee5eb; border-radius: 5px; margin: 10px 0;'>";
echo "✅ <strong>اكتمل التثبيت بنجاح!</strong><br>";
echo "يمكنك الآن الانتقال إلى صفحة المنتجات لرؤية الفئات المضافة.";
echo "</div>";

$delivery_inserts = [
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryHomeCity', '') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryInsideCity', '10') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('deliveryOutsideCity', '30') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('darkMode', '1') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('shopCity', '') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    
    // إعدادات التنبيهات ونظام المخزون
    "INSERT INTO settings (setting_name, setting_value) VALUES ('stockAlertInterval', '20') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('stockAlertsEnabled', '1') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    
    // القيم الافتراضية لحدود التنبيه (الجديدة)
    "INSERT INTO settings (setting_name, setting_value) VALUES ('low_quantity_alert', '30') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('critical_quantity_alert', '10') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
];

foreach ($delivery_inserts as $q) {
    if ($conn->query($q) === TRUE) {
        // Success
    } else {
        echo "Error applying settings: " . $conn->error . "<br>";
    }
}

// إضافة أعمدة التوصيل للفواتير
$check_delivery_city = $conn->query("SHOW COLUMNS FROM invoices LIKE 'delivery_city'");
if ($check_delivery_city->num_rows == 0) {
    $sql_alter_invoices = "ALTER TABLE invoices 
                          ADD COLUMN delivery_city VARCHAR(100) NULL AFTER total,
                          ADD COLUMN delivery_cost DECIMAL(10, 2) NULL DEFAULT 0 AFTER delivery_city";
    if ($conn->query($sql_alter_invoices) === TRUE) {
        echo "Columns 'delivery_city' and 'delivery_cost' added to invoices table successfully.<br>";
    } else {
        echo "Error adding columns to invoices table: " . $conn->error . "<br>";
    }
}

echo "<h3>Delivery settings added successfully</h3>";

// إضافة حقل barcode إلى جدول invoices إذا لم يكن موجوداً
$check_barcode = $conn->query("SHOW COLUMNS FROM invoices LIKE 'barcode'");
if ($check_barcode->num_rows == 0) {
    $sql_alter_invoices_barcode = "ALTER TABLE invoices ADD COLUMN barcode VARCHAR(50) NULL AFTER total";
    if ($conn->query($sql_alter_invoices_barcode) === TRUE) {
        echo "Column 'barcode' added to invoices table successfully.<br>";
        
        // تحديث الفواتير القديمة بباركود تلقائي
        $update_old_invoices = "UPDATE invoices SET barcode = CONCAT('INV', LPAD(id, 8, '0')) WHERE barcode IS NULL";
        if ($conn->query($update_old_invoices) === TRUE) {
            echo "Old invoices updated with barcodes successfully.<br>";
        }
    } else {
        echo "Error adding column 'barcode' to invoices table: " . $conn->error . "<br>";
    }
}

// تحديث جدول invoice_items لإضافة حقل product_name
$check_product_name = $conn->query("SHOW COLUMNS FROM invoice_items LIKE 'product_name'");
if ($check_product_name->num_rows == 0) {
    $sql_alter_invoice_items = "ALTER TABLE invoice_items ADD COLUMN product_name VARCHAR(255) NOT NULL DEFAULT 'منتج محذوف' AFTER product_id";
    if ($conn->query($sql_alter_invoice_items) === TRUE) {
        echo "Column 'product_name' added to invoice_items table successfully.<br>";
        
        // تحديث الفواتير القديمة بأسماء المنتجات من جدول products
        $update_old_items = "UPDATE invoice_items ii 
                            LEFT JOIN products p ON ii.product_id = p.id 
                            SET ii.product_name = COALESCE(p.name, 'منتج محذوف')";
        if ($conn->query($update_old_items) === TRUE) {
            echo "Old invoice items updated with product names successfully.<br>";
        }
    } else {
        echo "Error adding column 'product_name': " . $conn->error . "<br>";
    }
}

// تعديل FOREIGN KEY لمنع حذف بيانات الفواتير عند حذف المنتج
$conn->query("ALTER TABLE invoice_items DROP FOREIGN KEY invoice_items_ibfk_2");
$conn->query("ALTER TABLE invoice_items ADD CONSTRAINT invoice_items_ibfk_2 
              FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL");
echo "Foreign key constraint updated to prevent data loss on product deletion.<br>";

$check_amount_received = $conn->query("SHOW COLUMNS FROM invoices LIKE 'amount_received'");
if ($check_amount_received->num_rows == 0) {
    $sql_alter_invoices_amounts = "ALTER TABLE invoices 
                                  ADD COLUMN amount_received DECIMAL(10, 2) DEFAULT 0.00 AFTER payment_method,
                                  ADD COLUMN change_due DECIMAL(10, 2) DEFAULT 0.00 AFTER amount_received";
    if ($conn->query($sql_alter_invoices_amounts) === TRUE) {
        echo "Columns 'amount_received' and 'change_due' added to invoices table successfully.<br>";
    } else {
        echo "Error adding amount columns to invoices table: " . $conn->error . "<br>";
    }
}


echo "<h3>تحديث نظام الإيجار إلى النسخة الجديدة...</h3>";

// حذف الإعدادات القديمة
$old_settings = ['rentalDueDay', 'rentalDueMonth', 'rentalDueYear'];
foreach ($old_settings as $setting) {
    $conn->query("DELETE FROM settings WHERE setting_name = '$setting'");
}

// إضافة الإعدادات الجديدة
$rental_settings_v2 = [
    // تاريخ دفع الإيجار (بصيغة Y-m-d)
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalPaymentDate', '" . date('Y-m-01') . "') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // نوعية التأجير: monthly أو yearly
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalType', 'monthly') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    
    // الإعدادات الموجودة مسبقاً (نبقيها)
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalEnabled', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalAmount', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalReminderDays', '7') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalLastNotification', '0') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalLandlordName', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalLandlordPhone', '') ON DUPLICATE KEY UPDATE setting_value = setting_value",
    "INSERT INTO settings (setting_name, setting_value) VALUES ('rentalNotes', '') ON DUPLICATE KEY UPDATE setting_value = setting_value"
];

$success_count = 0;
foreach ($rental_settings_v2 as $query) {
    if ($conn->query($query) === TRUE) {
        $success_count++;
    } else {
        echo "خطأ في تحديث إعداد الإيجار: " . $conn->error . "<br>";
    }
}

// عرض النتيجة
if ($success_count > 0) {
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ تم تحديث نظام الإيجار بنجاح إلى النسخة الجديدة!<br>";
    echo "تم إضافة: نوعية التأجير (شهري/سنوي) وتاريخ دفع الإيجار<br>";
    echo "تم حذف: نظام اختيار اليوم/الشهر/السنة المنفصل";
    echo "</div>";
    
    // عرض الإعدادات الجديدة
    $result = $conn->query("SELECT setting_name, setting_value FROM settings WHERE setting_name LIKE 'rental%' ORDER BY setting_name");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='6' style='border-collapse:collapse; margin-top: 10px;'>";
        echo "<tr><th>الإعداد</th><th>القيمة</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $value = $row['setting_value'];
            if ($row['setting_name'] == 'rentalType') {
                $value = ($value == 'monthly') ? 'شهري' : 'سنوي';
            } elseif ($row['setting_name'] == 'rentalPaymentDate') {
                $value = date('Y/m/d', strtotime($value));
            }
            echo "<tr><td>" . htmlspecialchars($row['setting_name']) . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        echo "</table>";
    }
}

echo "<br><div style='background: #cce5ff; padding: 15px; border: 1px solid #b3d9ff; border-radius: 5px; margin: 10px 0;'>";
echo "📋 <strong>كيف يعمل النظام الجديد:</strong><br>";
echo "<ul style='margin-right: 20px;'>";
echo "<li>اختر تاريخ دفع الإيجار الأول (مثلاً: 2025/01/01)</li>";
echo "<li>اختر نوعية التأجير (شهري أو سنوي)</li>";
echo "<li>سيتم حساب موعد الدفع التالي تلقائياً</li>";
echo "<li>مثال: إذا كان التاريخ 2025/01/01 ونوعية التأجير شهري → الدفع التالي: 2025/02/01</li>";
echo "<li>مثال: إذا كان التاريخ 2025/01/01 ونوعية التأجير سنوي → الدفع التالي: 2026/01/01</li>";
echo "</ul>";
echo "</div>";

$conn->close();
?>
