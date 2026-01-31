<?php
// generate_test_data.php
// Script to generate fake test data for Smart Shop

require_once 'db.php';

// Configuration
$DAYS_TO_SIMULATE = 30;
$PRODUCTS_COUNT = 50;
$CUSTOMERS_COUNT = 20;
$MAX_DAILY_ORDERS = 15;
$MIN_DAILY_ORDERS = 5;

// Helper Functions
function getRandomName() {
    $firstNames = ['محمد', 'أحمد', 'فاطمة', 'خديجة', 'يوسف', 'عمر', 'علي', 'سارة', 'مريم', 'حسن', 'إبراهيم', 'زينب', 'هدى', 'كريم', 'ليلى'];
    $lastNames = ['العلوي', 'الإدريسي', 'التازي', 'الفاسي', 'المنصوري', 'بنسودة', 'العمراوي', 'الناصري', 'الرحماني', 'الداودي'];
    return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
}

function getRandomPhone() {
    return '06' . rand(10000000, 99999999);
}

function getCategories($conn) {
    $cats = [];
    $result = $conn->query("SELECT id FROM categories");
    while($row = $result->fetch_assoc()) $cats[] = $row['id'];
    return $cats;
}

function getProducts($conn) {
    $prods = [];
    $result = $conn->query("SELECT id, price, cost_price, quantity, name FROM products");
    while($row = $result->fetch_assoc()) $prods[] = $row;
    return $prods;
}

function getCustomers($conn) {
    $custs = [];
    $result = $conn->query("SELECT id FROM customers");
    while($row = $result->fetch_assoc()) $custs[] = $row['id'];
    return $custs;
}

function isHolidayDate($conn, $date) {
    $stmt = $conn->prepare("SELECT name FROM holidays WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row['name'];
    
    // Check weekends (default Sat/Sun or just Sun?)
    // Assuming Sunday is holiday
    /*
    if (date('N', strtotime($date)) == 7) {
        return 'عطلة أسبوعية (الأحد)';
    }
    */
    return false;
}

echo "<h1>Generating Test Data...</h1>";

// 1. Ensure Categories Exist
$categories = getCategories($conn);
if (empty($categories)) {
    echo "Creating Categories...<br>";
    $cats = ['مواد غذائية', 'مشروبات', 'منظفات', 'إلكترونيات', 'ملابس', 'أدوات منزلية'];
    foreach ($cats as $c) {
        $conn->query("INSERT INTO categories (name) VALUES ('$c')");
    }
    $categories = getCategories($conn);
}

// 2. Ensure Products Exist
$products = getProducts($conn);
if (count($products) < $PRODUCTS_COUNT) {
    echo "Creating Products...<br>";
    for ($i = 0; $i < $PRODUCTS_COUNT; $i++) {
        $name = "منتج تجريبي " . ($i + 1);
        $price = rand(10, 500);
        $cost = $price * 0.7;
        $qty = rand(10, 100);
        $cat = $categories[array_rand($categories)];
        $barcode = rand(10000000, 99999999);
        
        $stmt = $conn->prepare("INSERT INTO products (name, price, cost_price, quantity, category_id, barcode) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddiis", $name, $price, $cost, $qty, $cat, $barcode);
        $stmt->execute();
    }
    $products = getProducts($conn);
}

// 3. Ensure Customers Exist
$customers = getCustomers($conn);
if (count($customers) < $CUSTOMERS_COUNT) {
    echo "Creating Customers...<br>";
    for ($i = 0; $i < $CUSTOMERS_COUNT; $i++) {
        $name = getRandomName();
        $phone = getRandomPhone();
        $stmt = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $phone);
        $stmt->execute();
    }
    $customers = getCustomers($conn);
}

// 4. Simulate Activity
echo "Simulating $DAYS_TO_SIMULATE days of activity...<br>";

$startDate = date('Y-m-d', strtotime("-$DAYS_TO_SIMULATE days"));
$endDate = date('Y-m-d'); // Today

$currentDate = $startDate;
while ($currentDate <= $endDate) {
    echo "Processing $currentDate... ";
    
    // Check if data already exists for this day to avoid duplication on re-run
    $check = $conn->query("SELECT id FROM business_days WHERE DATE(start_time) = '$currentDate'");
    if ($check->num_rows > 0) {
        echo "Skipping (Already exists)<br>";
        $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        continue;
    }

    $isHoliday = isHolidayDate($conn, $currentDate);
    
    // Start Business Day
    $openingBalance = rand(500, 2000);
    $startTime = $currentDate . ' ' . sprintf("%02d", rand(8, 10)) . ':00:00';
    
    // Assuming admin user id 1
    $conn->query("INSERT INTO business_days (start_time, opening_balance, user_id) VALUES ('$startTime', $openingBalance, 1)");
    $dayId = $conn->insert_id;
    
    // Variables for closing balance
    $totalSales = 0;
    $totalRefunds = 0;
    $drawerExpenses = 0;
    
    // Generate Invoices
    $numOrders = rand($MIN_DAILY_ORDERS, $MAX_DAILY_ORDERS);
    if ($isHoliday) $numOrders = round($numOrders * 0.5); // Less sales on holidays? Or maybe more? Let's say less for logic variation.
    
    for ($j = 0; $j < $numOrders; $j++) {
        $hour = rand(9, 20);
        $minute = rand(0, 59);
        $invTime = $currentDate . ' ' . sprintf("%02d", $hour) . ':' . sprintf("%02d", $minute) . ':00';
        
        // Items
        $numItems = rand(1, 5);
        $invoiceTotal = 0;
        $invoiceItems = [];
        
        for ($k = 0; $k < $numItems; $k++) {
            $prod = $products[array_rand($products)];
            $qty = rand(1, 3);
            $invoiceTotal += $prod['price'] * $qty;
            $invoiceItems[] = ['product' => $prod, 'qty' => $qty];
        }
        
        $customerId = (rand(0, 10) > 3) ? $customers[array_rand($customers)] : null; // 70% chance of registered customer
        
        $holidayName = $isHoliday ? $isHoliday : null;
        $isHolidayFlag = $isHoliday ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO invoices (customer_id, total, created_at, is_holiday, holiday_name, payment_method, amount_received, change_due) VALUES (?, ?, ?, ?, ?, 'cash', ?, 0)");
        $stmt->bind_param("idsssd", $customerId, $invoiceTotal, $invTime, $isHolidayFlag, $holidayName, $invoiceTotal);
        $stmt->execute();
        $invoiceId = $stmt->insert_id;
        
        // Insert Items
        $stmtItem = $conn->prepare("INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, price, cost_price) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($invoiceItems as $item) {
            $p = $item['product'];
            $q = $item['qty'];
            $stmtItem->bind_param("iisidd", $invoiceId, $p['id'], $p['name'], $q, $p['price'], $p['cost_price']);
            $stmtItem->execute();
            
            // Update stock (decrement)
            $conn->query("UPDATE products SET quantity = quantity - $q WHERE id = " . $p['id']);
        }
        
        $totalSales += $invoiceTotal;
        
        // Chance of Refund (5%)
        if (rand(1, 100) <= 5) {
            $refundAmount = $invoiceTotal; // Full refund for simplicity
            $itemsJson = json_encode($invoiceItems);
            $reason = "استرجاع تجريبي";
            
            $stmtRef = $conn->prepare("INSERT INTO refunds (invoice_id, amount, items_json, reason, created_at) VALUES (?, ?, ?, ?, ?)");
            $refundTime = date('Y-m-d H:i:s', strtotime($invTime . ' +1 hour'));
            $stmtRef->bind_param("idsss", $invoiceId, $refundAmount, $itemsJson, $reason, $refundTime);
            $stmtRef->execute();
            
            $totalRefunds += $refundAmount;
            
            // Restock
            foreach ($invoiceItems as $item) {
                $p = $item['product'];
                $q = $item['qty'];
                $conn->query("UPDATE products SET quantity = quantity + $q WHERE id = " . $p['id']);
            }
        }
    }
    
    // Generate Expenses
    $numExpenses = rand(0, 2);
    for ($e = 0; $e < $numExpenses; $e++) {
        $amount = rand(20, 200);
        $paidFromDrawer = (rand(0, 1) == 1);
        $title = "مصروف " . rand(1, 100);
        $expTime = $currentDate; // Expenses usually by date
        
        $stmtExp = $conn->prepare("INSERT INTO expenses (title, amount, expense_date, paid_from_drawer, created_at) VALUES (?, ?, ?, ?, ?)");
        $createdAt = $currentDate . ' 12:00:00';
        $stmtExp->bind_param("sdsis", $title, $amount, $expTime, $paidFromDrawer, $createdAt);
        $stmtExp->execute();
        
        if ($paidFromDrawer) {
            $drawerExpenses += $amount;
        }
    }
    
    // Close Business Day
    $closingBalance = $openingBalance + $totalSales - $totalRefunds - $drawerExpenses;
    $endTime = $currentDate . ' 21:00:00';
    
    $stmtClose = $conn->prepare("UPDATE business_days SET end_time = ?, closing_balance = ? WHERE id = ?");
    $stmtClose->bind_param("sdi", $endTime, $closingBalance, $dayId);
    $stmtClose->execute();
    
    echo "Done (Sales: $totalSales, Refunds: $totalRefunds)<br>";
    
    $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
}

echo "<h2>Test Data Generation Complete!</h2>";
echo "<a href='index.php'>Go to Dashboard</a>";
?>