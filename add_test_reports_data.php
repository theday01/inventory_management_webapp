<?php
// إضافة بيانات تجريبية لاختبار صفحة التقارير
// يشمل عملاء، منتجات، فواتير مع خصومات وتوصيل بتواريخ متعددة

require_once 'db.php';

// بدء المعاملة لضمان الاتساق
$conn->begin_transaction();

try {
    echo "<h2>إضافة بيانات تجريبية للتقارير...</h2>";

    // ======================================
    // إضافة عملاء تجريبيين
    // ======================================
    echo "<h3>إضافة عملاء تجريبيين...</h3>";

    $customers = [
        ['name' => 'أحمد محمد', 'email' => 'ahmed@example.com', 'phone' => '0612345678', 'address' => 'شارع الحسن الثاني، الرباط', 'city' => 'الرباط'],
        ['name' => 'فاطمة علي', 'email' => 'fatima@example.com', 'phone' => '0623456789', 'address' => 'شارع محمد الخامس، الدار البيضاء', 'city' => 'الدار البيضاء'],
        ['name' => 'محمد حسن', 'email' => 'mohamed@example.com', 'phone' => '0634567890', 'address' => 'شارع الجيش الملكي، مراكش', 'city' => 'مراكش'],
        ['name' => 'سارة أحمد', 'email' => 'sara@example.com', 'phone' => '0645678901', 'address' => 'شارع علال بن عبدالله، فاس', 'city' => 'فاس'],
        ['name' => 'يوسف عمر', 'email' => 'youssef@example.com', 'phone' => '0656789012', 'address' => 'شارع عبد الكريم الخطابي، طنجة', 'city' => 'طنجة'],
        ['name' => 'ليلى خالد', 'email' => 'layla@example.com', 'phone' => '0667890123', 'address' => 'شارع الحسن الثاني، أكادير', 'city' => 'أكادير'],
        ['name' => 'عمر سالم', 'email' => 'omar@example.com', 'phone' => '0678901234', 'address' => 'شارع محمد الخامس، وجدة', 'city' => 'وجدة'],
        ['name' => 'نور حسن', 'email' => 'nour@example.com', 'phone' => '0689012345', 'address' => 'شارع الجيش الملكي، الناظور', 'city' => 'الناظور'],
        ['name' => 'كريم علي', 'email' => 'karim@example.com', 'phone' => '0690123456', 'address' => 'شارع علال بن عبدالله، تطوان', 'city' => 'تطوان'],
        ['name' => 'مريم أحمد', 'email' => 'maryam@example.com', 'phone' => '0601234567', 'address' => 'شارع عبد الكريم الخطابي، الحسيمة', 'city' => 'الحسيمة']
    ];

    $customer_ids = [];
    $stmt_customer = $conn->prepare("INSERT INTO customers (name, email, phone, address, city) VALUES (?, ?, ?, ?, ?)");
    foreach ($customers as $customer) {
        $stmt_customer->bind_param("sssss", $customer['name'], $customer['email'], $customer['phone'], $customer['address'], $customer['city']);
        $stmt_customer->execute();
        $customer_ids[] = $stmt_customer->insert_id;
    }
    $stmt_customer->close();
    echo "<div style='color: green;'>✓ تم إضافة " . count($customers) . " عميل تجريبي.</div>";

    // ======================================
    // إضافة منتجات تجريبية (إذا لم تكن موجودة)
    // ======================================
    echo "<h3>التحقق من المنتجات...</h3>";

    $result = $conn->query("SELECT COUNT(*) as count FROM products");
    $product_count = $result->fetch_assoc()['count'];

    if ($product_count < 10) {
        // إضافة فئة تجريبية إذا لم تكن موجودة
        $conn->query("INSERT IGNORE INTO categories (name, description) VALUES ('منتجات تجريبية', 'منتجات للاختبار')");
        $category_id = $conn->insert_id;
        if ($category_id == 0) {
            $result = $conn->query("SELECT id FROM categories WHERE name = 'منتجات تجريبية'");
            $category_id = $result->fetch_assoc()['id'];
        }

        $products = [
            ['name' => 'منتج تجريبي 1', 'price' => 50.00, 'cost_price' => 30.00, 'quantity' => 100, 'category_id' => $category_id, 'barcode' => 'TEST001'],
            ['name' => 'منتج تجريبي 2', 'price' => 75.00, 'cost_price' => 45.00, 'quantity' => 80, 'category_id' => $category_id, 'barcode' => 'TEST002'],
            ['name' => 'منتج تجريبي 3', 'price' => 100.00, 'cost_price' => 60.00, 'quantity' => 60, 'category_id' => $category_id, 'barcode' => 'TEST003'],
            ['name' => 'منتج تجريبي 4', 'price' => 25.00, 'cost_price' => 15.00, 'quantity' => 200, 'category_id' => $category_id, 'barcode' => 'TEST004'],
            ['name' => 'منتج تجريبي 5', 'price' => 150.00, 'cost_price' => 90.00, 'quantity' => 40, 'category_id' => $category_id, 'barcode' => 'TEST005']
        ];

        $stmt_product = $conn->prepare("INSERT INTO products (name, price, cost_price, quantity, category_id, barcode) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            $stmt_product->bind_param("sddiis", $product['name'], $product['price'], $product['cost_price'], $product['quantity'], $product['category_id'], $product['barcode']);
            $stmt_product->execute();
        }
        $stmt_product->close();
        echo "<div style='color: green;'>✓ تم إضافة " . count($products) . " منتج تجريبي.</div>";
    } else {
        echo "<div style='color: blue;'>ℹ️ توجد منتجات كافية في النظام.</div>";
    }

    // جلب معرفات المنتجات
    $result = $conn->query("SELECT id, name, price FROM products LIMIT 10");
    $product_list = [];
    while ($row = $result->fetch_assoc()) {
        $product_list[] = $row;
    }

    // ======================================
    // إضافة فواتير تجريبية بتواريخ متعددة
    // ======================================
    echo "<h3>إضافة فواتير تجريبية...</h3>";

    // تواريخ من 1/1/2025 إلى اليوم (20/1/2026)
    $dates = [];

    // تواريخ لكل شهر في 2025 (يوم عشوائي في كل شهر)
    for ($month = 1; $month <= 12; $month++) {
        $dates[] = sprintf('2025-%02d-%02d', $month, rand(1, 28));
    }

    // تواريخ يومية في يناير 2026 حتى اليوم
    for ($day = 1; $day <= 20; $day++) {
        $dates[] = sprintf('2026-01-%02d', $day);
    }

    shuffle($dates); // خلط التواريخ للتنويع

    $stmt_invoice = $conn->prepare("INSERT INTO invoices (customer_id, total, discount_percent, discount_amount, delivery_city, delivery_cost, barcode, payment_method, amount_received, change_due, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_item = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");

    $total_invoices = 0;
    $total_items = 0;

    foreach ($dates as $date) {
        // اختيار عميل عشوائي
        $customer_id = $customer_ids[array_rand($customer_ids)];

        // إنشاء فاتورة مع خصومات وتوصيل عشوائي
        $num_items = rand(1, 5);
        $subtotal = 0;
        $items = [];

        for ($i = 0; $i < $num_items; $i++) {
            $product = $product_list[array_rand($product_list)];
            $quantity = rand(1, 10);
            $price = $product['price'];
            $subtotal += $price * $quantity;
            $items[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'quantity' => $quantity,
                'price' => $price
            ];
        }

        // خصم عشوائي
        $discount_percent = rand(0, 20); // 0-20%
        $discount_amount = $subtotal * ($discount_percent / 100);

        // توصيل عشوائي
        $delivery_cities = ['الرباط', 'الدار البيضاء', 'مراكش', 'فاس', 'طنجة', 'أكادير', null];
        $delivery_city = $delivery_cities[array_rand($delivery_cities)];
        $delivery_cost = $delivery_city ? rand(10, 50) : 0;

        $total = $subtotal - $discount_amount + $delivery_cost;

        // دفع
        $payment_methods = ['cash', 'card', 'transfer'];
        $payment_method = $payment_methods[array_rand($payment_methods)];
        $amount_received = $total + rand(0, 50); // قد يكون أكثر
        $change_due = max(0, $amount_received - $total);

        // barcode فريد
        $barcode = 'INV' . date('YmdHis', strtotime($date)) . rand(100, 999);

        // إدراج الفاتورة
        $stmt_invoice->bind_param("idddsdssdds", $customer_id, $total, $discount_percent, $discount_amount, $delivery_city, $delivery_cost, $barcode, $payment_method, $amount_received, $change_due, $date);
        $stmt_invoice->execute();
        $invoice_id = $stmt_invoice->insert_id;
        $total_invoices++;

        // إدراج عناصر الفاتورة
        foreach ($items as $item) {
            $stmt_item->bind_param("iisid", $invoice_id, $item['product_id'], $item['product_name'], $item['quantity'], $item['price']);
            $stmt_item->execute();
            $total_items++;
        }
    }

    $stmt_invoice->close();
    $stmt_item->close();

    echo "<div style='color: green;'>✓ تم إضافة $total_invoices فاتورة مع $total_items عنصر.</div>";

    // ======================================
    // إنهاء المعاملة
    // ======================================
    $conn->commit();

    echo "<br><div style='background: #d4edda; padding: 20px; border: 2px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
    echo "<h2 style='color: #155724; margin-top: 0;'>✅ تم إضافة البيانات التجريبية بنجاح!</h2>";
    echo "<p>يمكنك الآن تجربة صفحة التقارير لرؤية البيانات المتنوعة بتواريخ متعددة.</p>";
    echo "<p><a href='reports.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>الذهاب إلى صفحة التقارير</a></p>";
    echo "</div>";

} catch (Exception $e) {
    $conn->rollback();
    echo "<div style='color: red;'>خطأ: " . $e->getMessage() . "</div>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة بيانات تجريبية - Smart Shop</title>
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