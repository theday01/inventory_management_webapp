<?php
/**
 * ملف إنشاء بيانات تجريبية تاريخية شاملة
 * يضيف بيانات وهمية بتواريخ متعددة منذ 2024 لاختبار صفحة التقارير
 */

require_once 'db.php';

// تعطيل عرض الأخطاء البسيطة
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>إنشاء بيانات تجريبية تاريخية</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 2em; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .step { background: #f8f9fa; border-right: 4px solid #667eea; padding: 20px; margin-bottom: 20px; border-radius: 10px; }
        .step h3 { color: #667eea; margin-bottom: 10px; }
        .success { background: #d4edda; border-color: #28a745; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 15px; }
        .error { background: #f8d7da; border-color: #dc3545; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 15px; }
        .info { background: #d1ecf1; border-color: #17a2b8; color: #0c5460; padding: 15px; border-radius: 10px; margin-bottom: 15px; }
        .progress-bar { width: 100%; height: 30px; background: #e9ecef; border-radius: 15px; overflow: hidden; margin: 20px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th, table td { padding: 12px; text-align: right; border-bottom: 1px solid #dee2e6; }
        table th { background: #f8f9fa; font-weight: bold; }
        .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 10px; margin-top: 20px; transition: transform 0.2s; }
        .button:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
<div class='container'>
    <div class='header'>
        <h1>📊 إنشاء بيانات تجريبية تاريخية شاملة</h1>
        <p>سيتم إضافة بيانات وهمية بتواريخ متعددة منذ 2024 لاختبار صفحة التقارير</p>
    </div>
    <div class='content'>";

ob_start();

// دالة لتوليد تاريخ عشوائي بين تاريخين
function randomDate($start_date, $end_date) {
    $start = strtotime($start_date);
    $end = strtotime($end_date);
    $random_timestamp = mt_rand($start, $end);
    return date('Y-m-d H:i:s', $random_timestamp);
}

// دالة لتوليد اسم عشوائي
function generateRandomName($type = 'customer') {
    $first_names = ['أحمد', 'محمد', 'علي', 'حسن', 'عمر', 'خالد', 'سعد', 'عبدالله', 'يوسف', 'إبراهيم', 'فاطمة', 'مريم', 'عائشة', 'زينب', 'خديجة', 'أسماء', 'سارة', 'نور', 'لينا', 'ريم'];
    $last_names = ['العلي', 'الأحمد', 'المحمد', 'الحسن', 'العمر', 'الخالد', 'السعد', 'العبدالله', 'اليوسف', 'الإبراهيم'];
    $cities = ['الدار البيضاء', 'الرباط', 'مراكش', 'فاس', 'طنجة', 'أكادير', 'مكناس', 'وجدة', 'الجديدة', 'تطوان'];

    if ($type === 'customer') {
        return $first_names[array_rand($first_names)] . ' ' . $last_names[array_rand($last_names)];
    } elseif ($type === 'product') {
        $brands = ['ألفا', 'بيتا', 'دلتا', 'جوم', 'نور', 'سما', 'ريحان', 'العلالي', 'جهينة', 'الشعلان'];
        $types = ['تجريبي', 'مميز', 'جديد', 'عالي الجودة', 'اقتصادي', 'فاخر'];
        return $brands[array_rand($brands)] . ' ' . $types[array_rand($types)] . ' ' . (mt_rand(1, 999));
    }
    return 'غير محدد';
}

// الخطوة 1: إضافة عملاء تجريبيين
echo "<div class='step'>
        <h3>👥 الخطوة 1: إضافة عملاء تجريبيين</h3>
      </div>";

$customers_count = 200;
$customers = [];

for ($i = 0; $i < $customers_count; $i++) {
    $name = generateRandomName('customer');
    $email = 'customer' . ($i + 1) . '@example.com';
    $phone = '06' . str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    $address = 'العنوان ' . ($i + 1);
    $city = ['الدار البيضاء', 'الرباط', 'مراكش', 'فاس', 'طنجة'][mt_rand(0, 4)];
    $created_at = randomDate('2024-01-01', date('Y-m-d'));

    $customers[] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'city' => $city,
        'created_at' => $created_at
    ];
}

$success_customers = 0;
foreach ($customers as $customer) {
    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, address, city, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $customer['name'], $customer['email'], $customer['phone'], $customer['address'], $customer['city'], $customer['created_at']);
    if ($stmt->execute()) {
        $success_customers++;
    }
    $stmt->close();
}

echo "<div class='success'>✅ تم إضافة $success_customers عميل تجريبي</div>";

// الخطوة 2: إضافة منتجات تجريبية
echo "<div class='step'>
        <h3>📦 الخطوة 2: إضافة منتجات تجريبية</h3>
      </div>";

$products_count = 500;
$products = [];
$categories_result = $conn->query("SELECT id FROM categories LIMIT 10");
$category_ids = [];
while ($row = $categories_result->fetch_assoc()) {
    $category_ids[] = $row['id'];
}
if (empty($category_ids)) {
    $category_ids = [1, 2, 3, 4, 5]; // افتراضي
}

for ($i = 0; $i < $products_count; $i++) {
    $name = generateRandomName('product');
    $price = mt_rand(500, 500000) / 100; // 5.00 إلى 5000.00
    $cost_price = $price * 0.7; // تكلفة 70% من السعر
    $quantity = mt_rand(10, 500);
    $category_id = $category_ids[array_rand($category_ids)];
    $barcode = str_pad(mt_rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
    $created_at = randomDate('2024-01-01', date('Y-m-d'));

    $products[] = [
        'name' => $name,
        'price' => $price,
        'cost_price' => $cost_price,
        'quantity' => $quantity,
        'category_id' => $category_id,
        'barcode' => $barcode,
        'created_at' => $created_at
    ];
}

$success_products = 0;
foreach ($products as $product) {
    $stmt = $conn->prepare("INSERT INTO products (name, price, cost_price, quantity, category_id, barcode, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sddiiss", $product['name'], $product['price'], $product['cost_price'], $product['quantity'], $product['category_id'], $product['barcode'], $product['created_at']);
    if ($stmt->execute()) {
        $success_products++;
    }
    $stmt->close();
}

echo "<div class='success'>✅ تم إضافة $success_products منتج تجريبي</div>";

// الخطوة 3: إضافة فواتير تاريخية
echo "<div class='step'>
        <h3>🧾 الخطوة 3: إضافة فواتير تاريخية</h3>
      </div>";

$invoices_count = 2000; // إجمالي الفواتير
$payment_methods = ['cash', 'card', 'bank_transfer'];

$success_invoices = 0;
$success_invoice_items = 0;

echo "<div class='progress-bar'>
        <div class='progress-fill' id='progress' style='width: 0%'>0%</div>
      </div>";

// جلب العملاء والمنتجات من قاعدة البيانات
$customers_db = [];
$customers_result = $conn->query("SELECT id, name FROM customers ORDER BY RAND() LIMIT 200");
while ($row = $customers_result->fetch_assoc()) {
    $customers_db[] = $row;
}

$products_db = [];
$products_result = $conn->query("SELECT id, name, price FROM products WHERE quantity > 0 ORDER BY RAND() LIMIT 500");
while ($row = $products_result->fetch_assoc()) {
    $products_db[] = $row;
}

for ($i = 0; $i < $invoices_count; $i++) {
    $progress = round((($i + 1) / $invoices_count) * 100);

    // اختيار عميل عشوائي من قاعدة البيانات
    $customer = $customers_db[array_rand($customers_db)];
    $customer_id = $customer['id'];

    // اختيار تاريخ عشوائي من 2024 إلى الآن
    $created_at = randomDate('2024-01-01 08:00:00', date('Y-m-d H:i:s'));

    // اختيار طريقة دفع عشوائية
    $payment_method = $payment_methods[array_rand($payment_methods)];

    // إنشاء الفاتورة الأساسية
    $stmt = $conn->prepare("INSERT INTO invoices (customer_id, total, payment_method, created_at) VALUES (?, 0, ?, ?)");
    $stmt->bind_param("iss", $customer_id, $payment_method, $created_at);
    if (!$stmt->execute()) {
        $stmt->close();
        continue;
    }
    $invoice_id = $stmt->insert_id;
    $stmt->close();

    // إضافة عناصر الفاتورة
    $num_items = mt_rand(1, 8);
    $invoice_total = 0;
    $used_products = [];

    for ($j = 0; $j < $num_items; $j++) {
        // اختيار منتج عشوائي من قاعدة البيانات
        $product = $products_db[array_rand($products_db)];
        $product_id = $product['id'];

        if (in_array($product_id, $used_products)) continue;

        $used_products[] = $product_id;
        $quantity = mt_rand(1, 5);
        $price = $product['price'];

        // إضافة عنصر الفاتورة
        $item_stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        $item_stmt->bind_param("iisid", $invoice_id, $product_id, $product['name'], $quantity, $price);
        if ($item_stmt->execute()) {
            $success_invoice_items++;
            $invoice_total += $quantity * $price;
        }
        $item_stmt->close();

        // تحديث كمية المنتج
        $conn->query("UPDATE products SET quantity = quantity - $quantity WHERE id = $product_id");
    }

    // تحديث إجمالي الفاتورة والباركود
    $barcode = 'INV' . str_pad($invoice_id, 8, '0', STR_PAD_LEFT);
    $update_stmt = $conn->prepare("UPDATE invoices SET total = ?, barcode = ? WHERE id = ?");
    $update_stmt->bind_param("dsi", $invoice_total, $barcode, $invoice_id);
    $update_stmt->execute();
    $update_stmt->close();

    $success_invoices++;

    // تحديث شريط التقدم
    echo "<script>document.getElementById('progress').style.width = '{$progress}%'; document.getElementById('progress').textContent = '{$progress}%';</script>";
    flush();
    ob_flush();
}

echo "<div class='success'>✅ تم إضافة $success_invoices فاتورة مع $success_invoice_items عنصر</div>";

// الخطوة 4: إضافة أيام عمل تجريبية
echo "<div class='step'>
        <h3>📅 الخطوة 4: إضافة أيام عمل تجريبية</h3>
      </div>";

$business_days_count = 365; // يوم واحد لكل يوم من السنة الماضية
$success_business_days = 0;

for ($i = 0; $i < $business_days_count; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $start_time = $date . ' 09:00:00';
    $end_time = $date . ' 18:00:00';
    $opening_balance = mt_rand(10000, 500000) / 100; // 100.00 إلى 5000.00
    $closing_balance = $opening_balance + mt_rand(-50000, 100000) / 100; // تغيير عشوائي

    $stmt = $conn->prepare("INSERT INTO business_days (start_time, end_time, opening_balance, closing_balance) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdd", $start_time, $end_time, $opening_balance, $closing_balance);
    if ($stmt->execute()) {
        $success_business_days++;
    }
    $stmt->close();
}

echo "<div class='success'>✅ تم إضافة $success_business_days يوم عمل تجريبي</div>";

// النتائج النهائية
echo "<div class='step'>
        <h3>📊 النتائج النهائية</h3>
        <table>
            <tr><th>العملاء</th><td>$success_customers</td></tr>
            <tr><th>المنتجات</th><td>$success_products</td></tr>
            <tr><th>الفواتير</th><td>$success_invoices</td></tr>
            <tr><th>عناصر الفواتير</th><td>$success_invoice_items</td></tr>
            <tr><th>أيام العمل</th><td>$success_business_days</td></tr>
        </table>
      </div>";

echo "<div class='info'>
        <h3>🎉 تم إكمال إنشاء البيانات التجريبية التاريخية!</h3>
        <p>يمكنك الآن اختبار صفحة التقارير مع بيانات واقعية منذ 2024</p>
      </div>";

echo "<a href='reports.php' class='button'>📊 عرض التقارير</a>
      <a href='index.php' class='button'>🏠 الصفحة الرئيسية</a>";

echo "    </div>
        </div>
    </body>
    </html>";

$conn->close();
?></content>
<parameter name="filePath">c:\xampp\htdocs\smart_shop\generate_historical_test_data.php