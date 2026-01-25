<?php
require_once 'session.php';
require_once 'db.php';

$page_title = 'التقارير والتحليلات';
$current_page = 'reports.php';

require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<style>
/* CSS for Thermal Invoice Modal */
#thermal-invoice-print-area {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    width: 80mm;
    padding: 5mm;
    font-size: 11pt;
    line-height: 1.4;
    background: white;
    color: #000;
    margin: 0 auto;
}
#thermal-invoice-print-area .header {
    text-align: center;
    margin-bottom: 5mm;
    border-bottom: 2px dashed #000;
    padding-bottom: 3mm;
}
#thermal-invoice-print-area .shop-name {
    font-size: 16pt;
    font-weight: bold;
    margin-bottom: 1mm;
}
#thermal-invoice-print-area .shop-info {
    font-size: 9pt;
    color: #333;
    margin: 1mm 0;
}
#thermal-invoice-print-area .invoice-info {
    margin: 3mm 0;
    border-bottom: 1px dashed #000;
    padding-bottom: 2mm;
}
#thermal-invoice-print-area .info-row {
    display: flex;
    justify-content: space-between;
    font-size: 10pt;
    margin: 1mm 0;
}
#thermal-invoice-print-area .customer-section {
    margin: 3mm 0;
    padding: 2mm;
    background: #f5f5f5;
    border-radius: 2mm;
    font-size: 10pt;
}
#thermal-invoice-print-area .items-table {
    width: 100%;
    margin: 3mm 0;
}
#thermal-invoice-print-area .items-header {
    border-top: 2px solid #000;
    border-bottom: 1px solid #000;
    padding: 1mm 0;
    font-weight: bold;
    font-size: 10pt;
}
#thermal-invoice-print-area .item-row {
    border-bottom: 1px dashed #ccc;
    padding: 2mm 0;
    font-size: 10pt;
}
#thermal-invoice-print-area .item-details {
    display: flex;
    justify-content: space-between;
    font-size: 9pt;
}
#thermal-invoice-print-area .totals-section {
    margin: 3mm 0;
    border-top: 2px solid #000;
    padding-top: 2mm;
}
#thermal-invoice-print-area .total-row {
    display: flex;
    justify-content: space-between;
    font-size: 10pt;
    margin: 1mm 0;
}
#thermal-invoice-print-area .grand-total {
    border-top: 2px solid #000;
    padding-top: 2mm;
    font-weight: bold;
    font-size: 12pt;
}
#thermal-invoice-print-area .footer {
    text-align: center;
    margin-top: 5mm;
    font-size: 10pt;
}
</style>

<?php
// Fetch Currency
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

// Fetch shop settings for thermal invoice
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
$shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopPhone'");
$shopPhone = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopAddress'");
$shopAddress = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'deliveryHomeCity'");
$shopCity = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

// Check first login
$user_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT first_login FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$show_welcome = !$user['first_login'];
$stmt->close();

// Fetch Home City
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'deliveryHomeCity'");
$home_city = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

// --- Check for Active Business Day ---
$day_stmt = $conn->prepare("SELECT * FROM business_days WHERE end_time IS NULL ORDER BY start_time DESC LIMIT 1");
$day_stmt->execute();
$day_result = $day_stmt->get_result();
$current_day = $day_result->fetch_assoc();
$day_stmt->close();
$is_day_active = ($current_day !== null);

// --- Date Filter Logic ---
$range = $_GET['range'] ?? '30days';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if ($range == 'custom' && !empty($start_date) && !empty($end_date)) {
    // Custom range is set
} else {
    // Default ranges
    $end_date = date('Y-m-d');
    if ($range == 'today') {
        $start_date = date('Y-m-d');
    } elseif ($range == 'yesterday') {
        $start_date = date('Y-m-d', strtotime('-1 day'));
        $end_date = date('Y-m-d', strtotime('-1 day'));
    } elseif ($range == '7days') {
        $start_date = date('Y-m-d', strtotime('-7 days'));
    } elseif ($range == 'this_month') {
        $start_date = date('Y-m-01');
    } elseif ($range == 'last_month') {
        $start_date = date('Y-m-d', strtotime('first day of last month'));
        $end_date = date('Y-m-d', strtotime('last day of last month'));
    } else {
        // Default 30 days
        $range = '30days';
        $start_date = date('Y-m-d', strtotime('-30 days'));
    }
}
// SQL Time Range
$sql_start = $start_date . " 00:00:00";
$sql_end = $end_date . " 23:59:59";

// --- Data Fetching ---

// 1. Key Metrics
$sql_metrics = "
    SELECT 
        COUNT(DISTINCT i.id) as total_orders,
        COALESCE(SUM(i.total), 0) as total_revenue,
        COALESCE(SUM(i.delivery_cost), 0) as total_delivery,
        COALESCE(SUM(ii.quantity * COALESCE(p.cost_price, 0)), 0) as total_cogs,
        COALESCE(SUM(ii.quantity), 0) as total_items_sold,
        MAX(i.total) as max_order_value
    FROM invoices i
    LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
    LEFT JOIN products p ON ii.product_id = p.id
    WHERE i.created_at BETWEEN '$sql_start' AND '$sql_end'
";
$metrics_result = $conn->query($sql_metrics)->fetch_assoc();

$total_orders = $metrics_result['total_orders'];
$total_items_sold = $metrics_result['total_items_sold'] ?? 0;
$max_order_value = $metrics_result['max_order_value'] ?? 0;
$total_revenue = $metrics_result['total_revenue'];
$total_delivery = $metrics_result['total_delivery'];
$total_cogs = $metrics_result['total_cogs'];
$gross_profit = $total_revenue - $total_delivery - $total_cogs;
$avg_order_value = $total_orders > 0 ? ($total_revenue - $total_delivery) / $total_orders : 0;
$profit_margin = $total_revenue > 0 ? ($gross_profit / $total_revenue) * 100 : 0;

// Total cost including COGS and delivery
$total_cost = $total_cogs + $total_delivery;
$profit_markup = $total_cost > 0 ? ($gross_profit / $total_cost) * 100 : 0;

// Additional metrics for quick summary
$sql_unique_customers = "SELECT COUNT(DISTINCT customer_id) as unique_customers FROM invoices WHERE created_at BETWEEN '$sql_start' AND '$sql_end'";
$unique_result = $conn->query($sql_unique_customers);
$unique_customers = $unique_result ? $unique_result->fetch_assoc()['unique_customers'] : 0;

$days_diff = (strtotime($end_date) - strtotime($start_date)) / (60*60*24) + 1;
$avg_daily_revenue = $days_diff > 0 ? $total_revenue / $days_diff : 0;
$avg_daily_orders = $days_diff > 0 ? $total_orders / $days_diff : 0;

// 2. Sales Over Time (Chart Data)
$sql_chart = "
    SELECT 
        DATE(created_at) as date,
        SUM(total) as revenue,
        COUNT(id) as orders
    FROM invoices
    WHERE created_at BETWEEN '$sql_start' AND '$sql_end'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
";
$chart_result = $conn->query($sql_chart);
$chart_labels = [];
$chart_revenue = [];
$chart_orders = [];
$chart_raw_dates = [];

while($row = $chart_result->fetch_assoc()) {
    $chart_labels[] = date('M d', strtotime($row['date']));
    $chart_raw_dates[] = $row['date'];
    $chart_revenue[] = $row['revenue'];
    $chart_orders[] = $row['orders'];
}

// 3. Top Selling Products
$sql_top_products = "
    SELECT 
        p.name,
        SUM(ii.quantity) as sold_qty,
        SUM(ii.quantity * ii.price) as revenue
    FROM invoice_items ii
    JOIN invoices i ON ii.invoice_id = i.id
    LEFT JOIN products p ON ii.product_id = p.id
    WHERE i.created_at BETWEEN '$sql_start' AND '$sql_end'
    GROUP BY ii.product_id
    ORDER BY sold_qty DESC
    LIMIT 5
";
$top_products = $conn->query($sql_top_products);

// 3.1 Least Selling Products
$sql_least_products = "
    SELECT 
        p.name,
        SUM(ii.quantity) as sold_qty,
        SUM(ii.quantity * ii.price) as revenue
    FROM invoice_items ii
    JOIN invoices i ON ii.invoice_id = i.id
    LEFT JOIN products p ON ii.product_id = p.id
    WHERE i.created_at BETWEEN '$sql_start' AND '$sql_end'
    GROUP BY ii.product_id
    ORDER BY sold_qty ASC, revenue ASC
    LIMIT 5
";
$least_products = $conn->query($sql_least_products);

// 4. Sales By Category
$sql_categories = "
    SELECT 
        COALESCE(c.name, 'غير مصنف') as category_name,
        SUM(ii.quantity * ii.price) as revenue
    FROM invoice_items ii
    JOIN invoices i ON ii.invoice_id = i.id
    LEFT JOIN products p ON ii.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE i.created_at BETWEEN '$sql_start' AND '$sql_end'
    GROUP BY c.id
    ORDER BY revenue DESC
";
$cat_result = $conn->query($sql_categories);
$cat_labels = [];
$cat_data = [];
while($row = $cat_result->fetch_assoc()) {
    $cat_labels[] = $row['category_name'];
    $cat_data[] = $row['revenue'];
}

// 5. Payment Methods
$sql_payments = "
    SELECT 
        payment_method,
        COUNT(*) as count,
        SUM(total) as total
    FROM invoices
    WHERE created_at BETWEEN '$sql_start' AND '$sql_end'
    GROUP BY payment_method
";
$payment_result = $conn->query($sql_payments);

// 6. Top Customers
$sql_top_customers = "
    SELECT 
        c.name,
        COUNT(i.id) as order_count,
        SUM(i.total) as total_sales
    FROM invoices i
    JOIN customers c ON i.customer_id = c.id
    WHERE i.created_at BETWEEN '$sql_start' AND '$sql_end'
    GROUP BY c.id
    ORDER BY total_sales DESC
    LIMIT 5
";
$top_customers = $conn->query($sql_top_customers);

// 7. Latest Invoices
$sql_latest_invoices = "
    SELECT 
        i.id,
        c.name as customer_name,
        i.total,
        i.created_at
    FROM invoices i
    LEFT JOIN customers c ON i.customer_id = c.id
    WHERE i.created_at BETWEEN '$sql_start' AND '$sql_end'
    ORDER BY i.created_at DESC
    LIMIT 5
";
$latest_invoices = $conn->query($sql_latest_invoices);

// 8. Slowest Selling Day
$sql_slowest_day = "
    SELECT 
        DATE(created_at) as sale_date,
        COUNT(*) as order_count,
        SUM(total) as total_sales
    FROM invoices
    WHERE created_at BETWEEN '$sql_start' AND '$sql_end'
    GROUP BY DATE(created_at)
    ORDER BY order_count ASC, total_sales ASC
    LIMIT 1
";
$slowest_day_result = $conn->query($sql_slowest_day);
$slowest_day = $slowest_day_result->fetch_assoc();
$slowest_day_formatted = $slowest_day ? date('Y-m-d', strtotime($slowest_day['sale_date'])) : 'لا توجد بيانات';
$slowest_day_orders = $slowest_day ? $slowest_day['order_count'] : 0;
$slowest_day_sales = $slowest_day ? $slowest_day['total_sales'] : 0;

// 9. Orders Outside Home City
$outside_city_orders = 0;
if (!empty($home_city)) {
    $sql_outside_city_orders = "
        SELECT COUNT(DISTINCT i.id) as outside_city_orders
        FROM invoices i
        LEFT JOIN customers c ON i.customer_id = c.id
        WHERE i.created_at BETWEEN ? AND ?
        AND c.city != ?
    ";
    $stmt = $conn->prepare($sql_outside_city_orders);
    $stmt->bind_param("sss", $sql_start, $sql_end, $home_city);
    $stmt->execute();
    $outside_city_result = $stmt->get_result();
    $outside_city_orders = $outside_city_result ? $outside_city_result->fetch_assoc()['outside_city_orders'] : 0;
    $stmt->close();
}

// 10. Top City by Orders
$sql_top_city = "
    SELECT c.city, COUNT(DISTINCT i.id) as order_count
    FROM invoices i
    LEFT JOIN customers c ON i.customer_id = c.id
    WHERE i.created_at BETWEEN ? AND ?
    AND c.city IS NOT NULL AND c.city != ''
    GROUP BY c.city
    ORDER BY order_count DESC
    LIMIT 1
";
$stmt = $conn->prepare($sql_top_city);
$stmt->bind_param("ss", $sql_start, $sql_end);
$stmt->execute();
$top_city_result = $stmt->get_result();
$top_city = $top_city_result ? $top_city_result->fetch_assoc() : null;
$top_city_name = $top_city ? $top_city['city'] : 'لا توجد بيانات';
$top_city_orders = $top_city ? $top_city['order_count'] : 0;
$stmt->close();

?>

<style>
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .stat-card:hover::before {
        opacity: 1;
    }
    .stat-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        border-color: rgba(59, 130, 246, 0.3);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-enter {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }
    .delay-100 { animation-delay: 100ms; }
    .gradient-text {
        background: linear-gradient(135deg, #3B82F6 0%, #84CC16 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .action-btn {
        transition: all 0.2s;
    }
    .action-btn:active {
        transform: scale(0.95);
    }
    /* Screen Styles */
    .glass-card {
        background: rgba(31, 41, 55, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 1rem;
        transition: transform 0.2s ease;
    }
    
    .date-btn.active {
        background-color: #3B82F6;
        color: white;
        border-color: #3B82F6;
    }

    .stat-value {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* --- PRINT ENHANCEMENTS (LANDSCAPE) --- */
    @media print {
        /* 1. Page Setup: Landscape */
        @page { 
            size: landscape; 
            margin: 5mm; 
        }

        /* 2. Reset Colors & Visibility */
        body, main, .bg-dark {
            background-color: white !important;
            color: black !important;
            background-image: none !important;
        }
        
        aside, nav, .sidebar, header form, .no-print, 
        .material-icons-round, button, input {
            display: none !important;
        }

        /* 3. FIX SCROLLBAR & OVERFLOW */
        /* This is crucial for removing the scrollbar on print */
        html, body, main, .scroll-smooth {
            height: auto !important;
            overflow: visible !important;
            display: block !important;
            position: static !important;
            width: 100% !important;
        }

        /* 4. Card Styling */
        .glass-card {
            background: white !important;
            backdrop-filter: none !important;
            border: 1px solid #ccc !important;
            box-shadow: none !important;
            margin-bottom: 15px !important;
            padding: 15px !important; /* Reduce padding to fit more */
            break-inside: avoid !important;
            page-break-inside: avoid !important;
            color: black !important;
        }

        /* 5. Typography */
        .text-white, .text-gray-400, .text-gray-300, .text-gray-500 {
            color: black !important;
        }
        .text-primary, .text-blue-500, .text-green-500 {
            color: black !important;
            font-weight: bold !important;
        }

        /* 6. Layout: Force Block Structure for Separated Pages */
        .grid {
            display: block !important;
        }

        #metrics-grid {
            display: grid !important; /* Keep metrics grid specific */
            grid-template-columns: repeat(4, 1fr) !important;
            margin-bottom: 30px !important;
        }

        /* Unwrap the container grids */
        #charts-grid, #tables-grid {
            display: block !important;
            margin-bottom: 0 !important;
        }

    }

    /* Pulsing animation for Start Day button */
    @keyframes pulse-glow {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 20px 10px rgba(16, 185, 129, 0);
            transform: scale(1.05);
        }
    }

    .pulse-button {
        animation: pulse-glow 2s infinite;
        position: relative;
    }

    .pulse-button::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 0.5rem;
        background: linear-gradient(45deg, #10b981, #34d399);
        opacity: 0.5;
        z-index: -1;
        animation: pulse-glow 2s infinite;
    }
</style>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark transition-all duration-300">
    
    <header class="bg-dark-surface/50 backdrop-blur-md border-b border-white/5 p-6 sticky top-0 z-20 no-print">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-pink-500">analytics</span>
                    التقارير والتحليلات
                </h2>
                <p class="text-sm text-gray-400 mt-1">
                    عرض البيانات من <span class="text-white font-bold"><?php echo $start_date; ?></span> إلى <span class="text-white font-bold"><?php echo $end_date; ?></span>
                </p>
            </div>

            <div class="flex items-center gap-4">
            <?php if ($is_day_active): ?>
                <div class="bg-green-500/10 text-green-400 px-4 py-2 rounded-xl text-sm">
                    يوم عمل نشط حاليا.
                </div>
            <?php endif; ?>
            <form method="GET" class="flex flex-wrap items-center gap-3 bg-dark/50 p-2 rounded-xl border border-white/5 shadow-lg">
                <div class="flex gap-1 bg-dark-surface rounded-lg p-1 border border-white/5">
                    <button type="submit" name="range" value="today" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == 'today' ? 'active' : ''; ?>">اليوم</button>
                    <button type="submit" name="range" value="yesterday" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == 'yesterday' ? 'active' : ''; ?>">أمس</button>
                    <button type="submit" name="range" value="7days" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == '7days' ? 'active' : ''; ?>">7 أيام</button>
                    <button type="submit" name="range" value="30days" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == '30days' ? 'active' : ''; ?>">30 يوم</button>
                    <button type="submit" name="range" value="this_month" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == 'this_month' ? 'active' : ''; ?>">شهر</button>
                </div>
                
                <div class="h-8 w-px bg-white/10"></div>

                <div class="flex items-center gap-2">
                    <span class="text-gray-400 text-xs">من</span>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="bg-dark border border-white/10 text-white text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-primary">
                    <span class="text-gray-400 text-xs">إلى</span>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="bg-dark border border-white/10 text-white text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-primary">
                    <button type="submit" name="range" value="custom" class="bg-primary hover:bg-primary-hover text-white p-1.5 rounded-lg transition-colors shadow-md">
                        <span class="material-icons-round text-sm block">filter_alt</span>
                    </button>
                </div>
            </form>
            <button id="view-summary-btn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                عرض ملخص الفترة
            </button>
            <div id="business-day-controls"></div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-6 scroll-smooth">
        
        <!-- Welcome & Quick Actions Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Welcome Banner -->
            <div class="lg:col-span-2 bg-gradient-to-br from-dark-surface/80 to-dark-surface/40 backdrop-blur-xl border border-white/10 rounded-3xl p-8 relative overflow-hidden animate-enter">
                <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-l from-primary/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10">
                    <h1 class="text-4xl font-bold text-white mb-3 leading-tight">
                        مرحباً بك في <span class="gradient-text"><?php echo htmlspecialchars($shopName); ?></span> 👋
                    </h1>
                    <p class="text-gray-400 text-lg max-w-2xl">إليك نظرة عامة على أداء متجرك. لديك <span class="text-white font-bold" id="today-orders-count-banner">0</span> طلبات جديدة اليوم بقيمة إجمالية <span class="text-primary font-bold" id="today-revenue-banner">0</span></p>
                    
                    <div class="mt-8 flex gap-4">
                        <a href="pos.php" class="action-btn group bg-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-primary/25 flex items-center gap-3 hover:-translate-y-1 transition-all">
                            <span class="material-icons-round transition-transform">add_shopping_cart</span>
                            بيع جديد
                        </a>
                        <a href="products.php" class="action-btn group bg-white/5 hover:bg-white/10 text-white px-6 py-3 rounded-xl font-bold border border-white/10 flex items-center gap-3 hover:-translate-y-1 transition-all">
                            <span class="material-icons-round text-accent">inventory</span>
                            إدارة المنتجات
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats / Actions Grid -->
            <div class="grid grid-cols-2 gap-4 animate-enter delay-100">
                <a href="customers.php" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-primary/30 rounded-2xl p-6 flex flex-col justify-center items-center transition-all stat-card cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <span class="material-icons-round text-purple-500 text-2xl">people</span>
                    </div>
                    <span class="text-white font-bold">العملاء</span>
                    <span class="text-xs text-gray-500 mt-1">عرض السجل</span>
                </a>
                <a href="invoices.php" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-accent/30 rounded-2xl p-6 flex flex-col justify-center items-center transition-all stat-card cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <span class="material-icons-round text-accent text-2xl">receipt_long</span>
                    </div>
                    <span class="text-white font-bold">الفواتير</span>
                    <span class="text-xs text-gray-500 mt-1">سجل المبيعات</span>
                </a>
                <a href="settings.php" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-orange-500/30 rounded-2xl p-6 flex flex-col justify-center items-center transition-all stat-card cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-orange-500/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <span class="material-icons-round text-orange-500 text-2xl">settings</span>
                    </div>
                    <span class="text-white font-bold">الإعدادات</span>
                    <span class="text-xs text-gray-500 mt-1">تخصيص النظام</span>
                </a>
                <a href="reports.php" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-pink-500/30 rounded-2xl p-6 flex flex-col justify-center items-center transition-all stat-card cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-pink-500/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <span class="material-icons-round text-pink-500 text-2xl">analytics</span>
                    </div>
                    <span class="text-white font-bold">التقارير</span>
                    <span class="text-xs text-gray-500 mt-1">تحليل شامل</span>
                </a>
            </div>
        </div>

        <div id="metrics-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-blue-500">payments</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1">إجمالي المبيعات</p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo number_format($total_revenue, 2); ?> <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <div class="mt-4 flex items-center text-xs text-blue-400 bg-blue-500/10 w-fit px-2 py-1 rounded-full border border-blue-500/10">
                    <span class="material-icons-round text-sm mr-1">receipt</span>
                    <span><?php echo number_format($total_orders); ?> فاتورة</span>
                </div>
            </div>

            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-green-500">savings</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1">صافي الربح التقديري</p>
                <h3 class="text-3xl font-bold text-green-500 stat-value"><?php echo number_format($gross_profit, 2); ?> <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <div class="mt-4 flex items-center gap-3">
                    <div class="text-xs text-gray-400">التكلفة الإجمالية: <span class="text-white"><?php echo number_format($total_cost, 2); ?></span></div>
                    <div class="text-xs text-green-400 bg-green-500/10 px-2 py-1 rounded-full border border-green-500/10">هامش الربح: <?php echo number_format($profit_markup, 1); ?>%</div>
                </div>
            </div>

            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-purple-500">shopping_cart</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1">متوسط قيمة الطلب</p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo number_format($avg_order_value, 2); ?> <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <div class="mt-4 w-full bg-gray-700 h-1.5 rounded-full overflow-hidden">
                    <?php $avg_percentage = $max_order_value > 0 ? ($avg_order_value / $max_order_value) * 100 : 0; ?>
                    <div class="bg-purple-500 h-full" style="width: <?php echo $avg_percentage; ?>%"></div>
                </div>
            </div>

            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-orange-500">local_shipping</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1">تكاليف التوصيل المحصلة</p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo number_format($total_delivery, 2); ?> <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <p class="text-xs text-gray-500 mt-4">إجمالي رسوم التوصيل</p>
            </div>
        </div>

        <!-- Additional Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-red-500">map</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1">عدد الطلبات خارج المدينة</p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo number_format($outside_city_orders); ?></h3>
                <div class="mt-4 flex items-center text-xs text-red-400 bg-red-500/10 w-fit px-2 py-1 rounded-full border border-red-500/10">
                    <span class="material-icons-round text-sm mr-1">location_on</span>
                    <span>المدينة الرئيسية: <?php echo htmlspecialchars($home_city ?: 'غير محددة'); ?></span>
                </div>
            </div>

            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-cyan-500">location_city</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1">المدينة الأكثر طلباً</p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo htmlspecialchars($top_city_name); ?></h3>
                <div class="mt-4 flex items-center text-xs text-cyan-400 bg-cyan-500/10 w-fit px-2 py-1 rounded-full border border-cyan-500/10">
                    <span class="material-icons-round text-sm mr-1">shopping_cart</span>
                    <span><?php echo number_format($top_city_orders); ?> طلب</span>
                </div>
            </div>
        </div>

        <div id="charts-grid" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 glass-card p-6 print-break-page">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-icons-round text-blue-500">show_chart</span>
                        تحليل المبيعات والطلبات
                    </h3>
                </div>
                <div class="relative h-80 w-full">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>

            <div class="glass-card p-6 flex flex-col print-break-page">
                <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                    <span class="material-icons-round text-pink-500">pie_chart</span>
                    المبيعات حسب الفئة
                </h3>
                <div class="relative flex-1 flex items-center justify-center h-64">
                    <canvas id="categoryChart"></canvas>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-400">توزيع الإيرادات</p>
                </div>
            </div>
        </div>

        <div id="tables-grid" class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <div class="glass-card p-6 print-break-page">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-yellow-500">emoji_events</span>
                    المنتجات الأكثر مبيعاً
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10 text-gray-400 text-xs uppercase">
                                <th class="pb-3 w-1/2">المنتج</th>
                                <th class="pb-3 text-center">الكمية</th>
                                <th class="pb-3 text-left">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <?php 
                            if ($top_products->num_rows > 0) {
                                while($prod = $top_products->fetch_assoc()) {
                                    $percentage = $total_revenue > 0 ? ($prod['revenue'] / $total_revenue) * 100 : 0;
                            ?>
                            <tr class="group hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white font-medium">
                                    <div class="truncate max-w-[200px]"><?php echo htmlspecialchars($prod['name']); ?></div>
                                    <div class="w-24 h-1 bg-gray-700 rounded-full mt-1 overflow-hidden no-print">
                                        <div class="h-full bg-yellow-500" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                </td>
                                <td class="py-3 text-center text-gray-300 font-bold"><?php echo number_format($prod['sold_qty']); ?></td>
                                <td class="py-3 text-left text-primary font-bold"><?php echo number_format($prod['revenue'], 2); ?> <span class="text-xs text-gray-500 font-normal"><?php echo $currency; ?></span></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="3" class="text-center py-4 text-gray-500">لا توجد بيانات</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 print-break-page">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-red-500">trending_down</span>
                    المنتجات الأقل مبيعاً
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10 text-gray-400 text-xs uppercase">
                                <th class="pb-3 w-1/2">المنتج</th>
                                <th class="pb-3 text-center">الكمية</th>
                                <th class="pb-3 text-left">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <?php 
                            if ($least_products && $least_products->num_rows > 0) {
                                $least_products->data_seek(0); // Reset pointer
                                while($prod = $least_products->fetch_assoc()) {
                                    $percentage = $total_revenue > 0 ? ($prod['revenue'] / $total_revenue) * 100 : 0;
                            ?>
                            <tr class="group hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white font-medium">
                                    <div class="truncate max-w-[200px]"><?php echo htmlspecialchars($prod['name']); ?></div>
                                    <div class="w-24 h-1 bg-gray-700 rounded-full mt-1 overflow-hidden no-print">
                                        <div class="h-full bg-red-500" style="width: <?php echo $percentage > 0 ? $percentage : 5; ?>%"></div>
                                    </div>
                                </td>
                                <td class="py-3 text-center text-gray-300 font-bold"><?php echo number_format($prod['sold_qty']); ?></td>
                                <td class="py-3 text-left text-primary font-bold"><?php echo number_format($prod['revenue'], 2); ?> <span class="text-xs text-gray-500 font-normal"><?php echo $currency; ?></span></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="3" class="text-center py-4 text-gray-500">لا توجد بيانات</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 print-break-page">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-purple-500">people</span>
                    كبار العملاء
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10 text-gray-400 text-xs uppercase">
                                <th class="pb-3 w-1/2">العميل</th>
                                <th class="pb-3 text-center">عدد الطلبات</th>
                                <th class="pb-3 text-left">إجمالي المبيعات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <?php 
                            if ($top_customers->num_rows > 0) {
                                while($cust = $top_customers->fetch_assoc()) {
                                    $percentage = $total_revenue > 0 ? ($cust['total_sales'] / $total_revenue) * 100 : 0;
                            ?>
                            <tr class="group hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white font-medium">
                                    <div class="truncate max-w-[200px]"><?php echo htmlspecialchars($cust['name']); ?></div>
                                    <div class="w-24 h-1 bg-gray-700 rounded-full mt-1 overflow-hidden no-print">
                                        <div class="h-full bg-purple-500" style="width: <?php echo $percentage > 0 ? $percentage : 5; ?>%"></div>
                                    </div>
                                </td>
                                <td class="py-3 text-center text-gray-300 font-bold"><?php echo number_format($cust['order_count']); ?></td>
                                <td class="py-3 text-left text-primary font-bold"><?php echo number_format($cust['total_sales'], 2); ?> <span class="text-xs text-gray-500 font-normal"><?php echo $currency; ?></span></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="3" class="text-center py-4 text-gray-500">لا توجد بيانات</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 print-break-page">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-blue-500">receipt_long</span>
                    آخر الفواتير
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10 text-gray-400 text-xs uppercase">
                                <th class="pb-3 w-1/3">رقم الفاتورة</th>
                                <th class="pb-3 w-1/3">العميل</th>
                                <th class="pb-3 text-left">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <?php 
                            if ($latest_invoices->num_rows > 0) {
                                while($inv = $latest_invoices->fetch_assoc()) {
                            ?>
                            <tr class="group hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white font-medium">#<?php echo htmlspecialchars($inv['id']); ?></td>
                                <td class="py-3 text-gray-300"><?php echo htmlspecialchars($inv['customer_name'] ?: 'عميل غير محدد'); ?></td>
                                <td class="py-3 text-left text-primary font-bold"><?php echo number_format($inv['total'], 2); ?> <span class="text-xs text-gray-500 font-normal"><?php echo $currency; ?></span></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="3" class="text-center py-4 text-gray-500">لا توجد بيانات</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 lg:col-span-4">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-teal-500">dashboard</span>
                    ملخص سريع
                </h3>
                
                <div class="mt-6 pt-6 border-t border-white/10 page-break-avoid">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500">أعلى يوم مبيعاً</p>
                             <?php 
                                $maxRev = 0;
                                $maxDate = '-';
                                $maxOrdersCount = 0;
                                foreach($chart_revenue as $k => $v) {
                                    if ($v > $maxRev) { 
                                        $maxRev = $v; 
                                        $maxDate = $chart_raw_dates[$k] ?? '-'; 
                                        $maxOrdersCount = $chart_orders[$k] ?? 0;
                                    }
                                }
                             ?>
                             <p class="text-white font-bold mt-1"><?php echo $maxDate; ?></p>
                             <p class="text-xs text-gray-400 mt-1"><?php echo $maxOrdersCount; ?> طلب</p>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500">أقل يوم مبيعاً</p>
                             <?php
                                // If we only have 1 day of data, Best Day == Worst Day. This is confusing.
                                // Logic: If total chart entries (days) <= 1, show '-' for worst day.
                                if (count($chart_labels) <= 1) {
                                    echo '<p class="text-white font-bold mt-1">-</p>';
                                    echo '<p class="text-xs text-gray-400 mt-1">غير متوفر</p>';
                                } else {
                                    echo '<p class="text-white font-bold mt-1">' . $slowest_day_formatted . '</p>';
                                    echo '<p class="text-xs text-gray-400 mt-1">' . $slowest_day_orders . ' طلب</p>';
                                }
                             ?>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500">متوسط عناصر السلة</p>
                             <p class="text-[10px] text-gray-600 mb-1 leading-tight">متوسط عدد المنتجات لكل طلب<br>(إجمالي القطع ÷ عدد الطلبات)</p>
                             <?php 
                                $avgItems = $total_orders > 0 ? $total_items_sold / $total_orders : 0;
                             ?>
                             <p class="text-white font-bold mt-1"><?php echo number_format($avgItems, 1); ?></p>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500">عدد العملاء الفريدين</p>
                             <p class="text-[10px] text-gray-600 mb-1 leading-tight">إجمالي العملاء الذين قاموا بشراء</p>
                             <p class="text-white font-bold mt-1"><?php echo number_format($unique_customers); ?></p>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500">متوسط الإيرادات اليومية</p>
                             <p class="text-[10px] text-gray-600 mb-1 leading-tight">متوسط المبيعات يومياً</p>
                             <p class="text-white font-bold mt-1"><?php echo number_format($avg_daily_revenue, 2); ?> <span class="text-xs text-gray-500"><?php echo $currency; ?></span></p>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500">متوسط الطلبات اليومية</p>
                             <p class="text-[10px] text-gray-600 mb-1 leading-tight">متوسط عدد الطلبات يومياً</p>
                             <p class="text-white font-bold mt-1"><?php echo number_format($avg_daily_orders, 1); ?></p>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Sections: Period Summary and Tips -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <!-- Period Summary Section -->
            <div class="glass-card p-6">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-blue-500">summarize</span>
                    عرض ملخص الفترة
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-dark rounded-lg border border-white/5">
                        <span class="text-gray-400">إجمالي المبيعات</span>
                        <span class="text-green-400 font-bold text-lg"><?php echo number_format($total_revenue, 2); ?> <?php echo $currency; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-dark rounded-lg border border-white/5">
                        <span class="text-gray-400">إجمالي تكلفة البضاعة</span>
                        <span class="text-red-400 font-bold text-lg"><?php echo number_format($total_cogs, 2); ?> <?php echo $currency; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-dark rounded-lg border border-white/5">
                        <span class="text-gray-400">صافي الربح</span>
                        <span class="text-primary font-bold text-xl"><?php echo number_format($gross_profit, 2); ?> <?php echo $currency; ?></span>
                    </div>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="glass-card p-6">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-yellow-500">lightbulb</span>
                    نصائح
                </h3>
                <div class="space-y-4">
                    <?php if ($total_orders > 0 && $days_diff <= 30): ?>    
                    <?php
                    $tips = [];

                    // Tip 1: Based on profit margin
                    if ($profit_margin < 10) {
                        $tips[] = "هامش الربح منخفض (% " . number_format($profit_margin, 1) . "). ركز على زيادة الأسعار أو تقليل التكاليف.";
                    } elseif ($profit_margin > 30) {
                        $tips[] = "هامش الربح جيد (% " . number_format($profit_margin, 1) . "). حافظ على هذا المستوى.";
                    }

                    // Tip 2: Delivery Cost Analysis (NEW)
                    if ($total_revenue > 0) {
                        $delivery_percentage = ($total_delivery / $total_revenue) * 100;
                        if ($total_delivery == 0 && $outside_city_orders > 0) {
                            $tips[] = "لديك طلبات خارجية ولكن إيرادات التوصيل 0. تأكد من إعداد تكاليف التوصيل بشكل صحيح.";
                        } elseif ($delivery_percentage > 15) {
                            $tips[] = "تكاليف التوصيل تشكل نسبة عالية (" . number_format($delivery_percentage, 1) . "%) من الإيرادات. راجع اتفاقياتك مع شركات الشحن.";
                        }
                    }

                    // Tip 3: Based on average order value
                    if ($avg_order_value < 50) {
                        $tips[] = "متوسط قيمة الطلب منخفض. حاول عمل عروض (Bundle) لزيادة حجم السلة.";
                    } elseif ($avg_order_value > 200) {
                        $tips[] = "متوسط قيمة الطلب ممتاز (" . number_format($avg_order_value, 2) . " " . $currency . ").";
                    }

                    // Tip 4: Based on unique customers
                    if ($unique_customers < 10) {
                        $tips[] = "عدد العملاء قليل. ركز على التسويق لجذب عملاء جدد.";
                    }

                    // If no specific tips
                    if (empty($tips)) {
                        $tips[] = "أداء المتجر متوازن. استمر في مراقبة التقارير بانتظام.";
                    }

                    // Display up to 3 tips
                    $display_tips = array_slice($tips, 0, 3);
                    foreach ($display_tips as $tip) {
                        echo '<div class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                                <p class="text-yellow-200 text-sm leading-relaxed">' . $tip . '</p>
                              </div>';
                    }
                    ?>
                    
                    <?php else: ?>
                    <div class="p-4 bg-gray-500/10 border border-gray-500/20 rounded-lg">
                        <p class="text-gray-300 text-sm leading-relaxed">لا توجد توصيات محددة متاحة حالياً بناءً على البيانات المحددة. يرجى التأكد من وجود بيانات كافية أو اختيار فترة زمنية أقصر للحصول على تحليل أكثر دقة (لا تتجاوز 30 يوم)</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>  
    </div>
</main>

<!-- Start Day Modal -->
<div id="start-day-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-dark-surface rounded-xl p-8 max-w-sm w-full">
        <h2 class="text-xl font-bold text-white mb-4">بدء يوم عمل جديد</h2>
        <div class="mb-4">
            <label for="opening-balance" class="block text-sm font-medium text-gray-400 mb-2">الرصيد الافتتاحي</label>
            <input type="text" id="opening-balance" class="w-full bg-dark border border-white/10 text-white rounded-lg px-3 py-2" placeholder="أدخل المبلغ">
        </div>
        <div class="flex justify-end gap-2">
            <button id="cancel-start-day" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded">إلغاء</button>
            <button id="confirm-start-day" class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded">بدء اليوم</button>
        </div>
    </div>
</div>

<!-- End Day Modal -->
<div id="end-day-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-dark-surface rounded-xl p-8 max-w-4xl w-full max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white">ملخص يوم العمل</h2>
            <button id="calculation-method-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg transition-colors" title="طريقة الحساب">
                <span class="material-icons-round text-sm">help_outline</span>
            </button>
        </div>

        <div id="day-summary" class="text-white">
            <!-- بيانات الملخص -->
            <div id="summary-data"></div>
        </div>

        <div class="flex justify-end mt-4">
            <button id="close-summary" class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded">إغلاق</button>
        </div>
    </div>
</div>

<!-- Calculation Method Modal -->
<div id="calculation-method-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-dark-surface rounded-xl p-8 max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="material-icons-round text-blue-500">calculate</span>
                طريقة الحساب
            </h2>
            <button id="close-calculation-method-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="space-y-3 text-sm text-gray-300">
            <div class="flex items-start gap-2">
                <span class="text-green-400 font-bold">•</span>
                <p><strong class="text-green-400">إجمالي المبيعات:</strong> مجموع قيمة جميع الفواتير الصادرة خلال الفترة</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-orange-400 font-bold">•</span>
                <p><strong class="text-orange-400">إجمالي رسوم التوصيل:</strong> مجموع رسوم التوصيل من جميع الطلبات</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-red-400 font-bold">•</span>
                <p><strong class="text-red-400">إجمالي تكلفة البضاعة:</strong> مجموع تكلفة شراء المنتجات المباعة (السعر × الكمية) للجميع الطلبات</p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-yellow-400 font-bold">•</span>
                <p><strong class="text-yellow-400">الرصيد الختامي:</strong> الرصيد الافتتاحي + إجمالي المبيعات</p>
            </div>
            <div class="flex items-start gap-2 bg-green-500/10 p-3 rounded-lg border border-green-500/20">
                <span class="text-green-400 font-bold text-lg">→</span>
                <p><strong class="text-green-400 text-lg">صافي الربح:</strong> إجمالي المبيعات - إجمالي تكلفة البضاعة - إجمالي رسوم التوصيل</p>
            </div>
            <div class="text-xs text-gray-400 mt-4 bg-dark/30 p-3 rounded-lg">
                <p class="flex items-center gap-2">
                    <span class="material-icons-round text-yellow-500" style="font-size: 16px;">info</span>
                    <span>ملاحظة: هذه الحسابات تقديرية بناءً على البيانات المتوفرة في الفترة المحددة</span>
                </p>
            </div>
        </div>
    </div>
</div>
<div id="thermal-invoice-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs mx-auto overflow-hidden flex flex-col" style="max-height: 90vh;">
        <div class="bg-dark-surface border-b border-white/5 p-4 flex items-center justify-between shrink-0">
            <h2 class="text-lg font-bold text-white">طباعة حرارية</h2>
            <button id="close-thermal-invoice-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 bg-white">
            <div id="thermal-invoice-print-area" class="text-black text-xs leading-tight font-mono" dir="rtl">
                <!-- Thermal invoice content will be populated here -->
            </div>
        </div>
        <div class="bg-dark-surface border-t border-white/5 p-4 flex gap-2 shrink-0">
            <button id="print-thermal-btn" class="flex-1 bg-primary hover:bg-primary-hover text-white py-2 px-4 rounded-lg font-bold transition-all flex items-center justify-center gap-2">
                <span class="material-icons-round text-sm">print</span>
                طباعة
            </button>
            <button id="close-thermal-modal-btn" class="bg-gray-600 hover:bg-gray-500 text-white py-2 px-4 rounded-lg font-bold transition-all">
                إغلاق
            </button>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Global variables
    const currency = '<?php echo $currency; ?>';
    const shopName = '<?php echo addslashes($shopName); ?>';
    const shopPhone = '<?php echo addslashes($shopPhone); ?>';
    const shopAddress = '<?php echo addslashes($shopAddress); ?>';
    const shopCity = '<?php echo addslashes($shopCity); ?>';
    let currentInvoiceId = null;

    function toEnglishNumbers(str) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        let result = str.toString();
        for (let i = 0; i < 10; i++) {
            result = result.replace(new RegExp(arabicNumbers[i], 'g'), englishNumbers[i]);
        }
        return result;
    }

    function formatNumber(num) {
        const numValue = parseFloat(num);
        if (isNaN(numValue)) {
            return '0.00';
        }
        return numValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDualDate(dateString) {
        const date = new Date(dateString);
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function generateThermalContent(data) {
        const invoiceDate = new Date(data.date);
        const formattedDate = formatDualDate(invoiceDate);
        const formattedTime = toEnglishNumbers(invoiceDate.toLocaleTimeString('ar-SA', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        }));

        let locationText = '';
        if(shopCity) locationText += shopCity;
        if(shopCity && shopAddress) locationText += '، ';
        if(shopAddress) locationText += shopAddress;

        let thermalContent = `
    <div class="header">
        <div class="shop-name">${shopName}</div>
        ${shopPhone ? `<div class="shop-info">📞 ${shopPhone}</div>` : ''}
        ${locationText ? `<div class="shop-info">📍 ${locationText}</div>` : ''}
    </div>

    <div class="invoice-info">
        <div class="info-row"><span>رقم الفاتورة:</span><span>#${String(data.id).padStart(6, '0')}</span></div>
        <div class="info-row"><span>التاريخ:</span><span>${formattedDate}</span></div>
        <div class="info-row"><span>الوقت:</span><span>${formattedTime}</span></div>
    </div>

    ${data.customer ? `
    <div class="customer-section">
        <div style="font-weight: bold;">العميل: ${data.customer.name}</div>
        ${data.customer.phone ? `<div>${data.customer.phone}</div>` : ''}
    </div>
    ` : `
    <div class="customer-section">
        <div>💵 عميل نقدي</div>
    </div>
    `}

    <div class="items-table">
        <div class="items-header">المنتجات (${data.items.length})</div>`;

        data.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            thermalContent += `
        <div class="item-row">
            <div style="font-weight:bold">${index + 1}. ${item.name}</div>
            <div class="item-details">
                <span>${item.quantity} × ${parseFloat(item.price).toFixed(2)}</span>
                <span style="font-weight: bold;">${itemTotal.toFixed(2)} ${currency}</span>
            </div>
        </div>`;
        });

        thermalContent += `</div>
            <div class="totals-section">
                <div class="total-row"><span>المجموع:</span><span>${data.subtotal.toFixed(2)} ${currency}</span></div>`;

        if (data.delivery > 0) {
            thermalContent += `<div class="total-row"><span>التوصيل:</span><span>${data.delivery.toFixed(2)} ${currency}</span></div>`;
            if (data.deliveryCity) {
            thermalContent += `<div class="total-row" style="font-size: 9pt; color: #666;"><span>مدينة التوصيل:</span><span>${data.deliveryCity}</span></div>`;
        }
    }
    
    if (data.discount_amount && data.discount_amount > 0) {
        thermalContent += `<div class="total-row"><span>الخصم:</span><span>-${data.discount_amount.toFixed(2)} ${currency}</span></div>`;
    }

    thermalContent += `
        <div class="total-row grand-total"><span>الإجمالي:</span><span>${data.total.toFixed(2)} ${currency}</span></div>`;

    const recVal = data.amount_received || 0;
    const chgVal = data.change_due || 0;

    if (recVal > 0) {
        thermalContent += `
        <div class="total-row" style="border-top: 1px dashed #000; margin-top: 2mm; padding-top: 2mm;">
            <span>المبلغ المستلم:</span>
            <span>${parseFloat(recVal).toFixed(2)} ${currency}</span>
        </div>
        <div class="total-row">
            <span>المبلغ الذي تم رده:</span>
            <span>${parseFloat(chgVal).toFixed(2)} ${currency}</span>
        </div>`;
    }

    thermalContent += `
    </div>

    <div style="text-align: center; margin: 5mm 0;">
        <svg id="barcode-thermal-display"></svg>
    </div>

    <div class="footer">
        <div style="font-weight: bold; margin-bottom: 2mm;">🌟 شكراً لثقتكم بنا 🌟</div>
        ${shopName ? `<div>${shopName}</div>` : ''}
    </div>`;

        return thermalContent;
    }

    function displayThermalInvoice(data) {
        const thermalPrintArea = document.getElementById('thermal-invoice-print-area');
        thermalPrintArea.innerHTML = generateThermalContent(data);
        
        // توليد الباركود للعرض
        setTimeout(() => {
            try {
                JsBarcode("#barcode-thermal-display", String(data.id).padStart(6, '0'), {
                    format: "CODE128",
                    width: 1,
                    height: 30,
                    displayValue: false,
                    margin: 0
                });
            } catch (e) {
                console.error('Error generating thermal barcode:', e);
            }
        }, 100);
    }

    async function viewInvoice(invoiceId) {
        try {
            const response = await fetch(`api.php?action=get_invoice_details&invoice_id=${invoiceId}`);
            const result = await response.json();
            
            if (result.success) {
                currentInvoiceId = invoiceId;
                displayThermalInvoice(result.data);
                document.getElementById('thermal-invoice-modal').classList.remove('hidden');
            } else {
                throw new Error(result.message || 'حدث خطأ أثناء جلب الفاتورة');
            }
        } catch (error) {
            console.error('Error viewing invoice:', error);
            Swal.fire({
                title: '!حدث خطأ',
                text: error.message || 'حدث خطأ غير متوقع',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'حسناً'
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        
        // const currency = '<?php echo $currency; ?>';

        async function loadDashboardStats() {
            try {
                const response = await fetch('api.php?action=getDashboardStats');
                const result = await response.json();
                
                if (result.success) {
                    const stats = result.data;
                    
                    const bannerOrders = document.getElementById('today-orders-count-banner');
                    const bannerRevenue = document.getElementById('today-revenue-banner');
                    
                    if(bannerOrders) bannerOrders.textContent = toEnglishNumbers(stats.todayOrders.toString());
                    if(bannerRevenue) bannerRevenue.textContent = toEnglishNumbers(formatNumber(stats.todayRevenue)) + ' ' + currency;
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        }

        async function handleViewSummary() {
            try {
                const startDate = document.querySelector('input[name="start_date"]').value;
                const endDate = document.querySelector('input[name="end_date"]').value;
                const url = `api.php?action=get_period_summary&start_date=${startDate}&end_date=${endDate}`;

                const response = await fetch(url, { method: 'GET' });
                const result = await response.json();
                
                if (result.success) {
                    const summary = result.data.summary;
                    let invoicesHtml = '';
                    if (summary.invoices && summary.invoices.length > 0) {
                        invoicesHtml = `
                            <hr class="border-gray-600 my-4">
                            <h3 class="text-lg font-bold text-white mb-3">الفواتير (${summary.invoices.length})</h3>
                            <div class="max-h-96 overflow-y-auto space-y-2">
                        `;
                        summary.invoices.forEach(invoice => {
                            invoicesHtml += `
                                <details class="bg-gray-700 rounded-lg p-3">
                                    <summary class="cursor-pointer text-white font-medium flex justify-between items-center">
                                        <span>فاتورة #${invoice.id} - ${invoice.customer_name || 'عميل غير محدد'}</span>
                                        <div class="flex items-center gap-2">
                                            <button onclick="viewInvoice(${invoice.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">عرض الفاتورة</button>
                                            <span class="text-green-400">${formatNumber(invoice.total)} ${currency}</span>
                                        </div>
                                    </summary>
                                    <div class="mt-3 text-gray-300 text-sm space-y-1">
                                        <p><strong>التاريخ:</strong> ${new Date(invoice.created_at).toLocaleString('ar')}</p>
                                        <p><strong>الهاتف:</strong> ${invoice.customer_phone || 'غير محدد'}</p>
                                        <p><strong>رسوم التوصيل:</strong> ${formatNumber(invoice.delivery_cost)} ${currency}</p>
                                        <p><strong>المنتجات:</strong> ${invoice.items || 'لا توجد منتجات'}</p>
                                    </div>
                                </details>
                            `;
                        });
                        invoicesHtml += '</div>';
                    }
                        document.getElementById('summary-data').innerHTML = `
                            <div class="space-y-3">
                                <p class="text-right"><strong class="text-green-400">إجمالي المبيعات:</strong> ${formatNumber(summary.total_sales)} ${currency}</p>
                                <p class="text-right"><strong class="text-orange-400">إجمالي رسوم التوصيل:</strong> ${formatNumber(summary.total_delivery)} ${currency}</p>
                                <p class="text-right"><strong class="text-blue-400">الرصيد الافتتاحي:</strong> ${formatNumber(summary.opening_balance)} ${currency}</p>
                                <p class="text-right"><strong class="text-yellow-400">الرصيد الختامي:</strong> ${formatNumber(summary.closing_balance)} ${currency}</p>
                                <hr class="border-gray-600 my-3">
                                <p class="text-right"><strong class="text-red-400">إجمالي تكلفة البضاعة:</strong> ${formatNumber(summary.total_cogs)} ${currency}</p>
                                <div class="bg-green-500/10 p-3 rounded-lg mt-4 border border-green-500/20">
                                    <p class="text-right text-xl font-bold text-green-400">صافي الربح: ${formatNumber(summary.total_profit)} ${currency}</p>
                                </div>
                                ${invoicesHtml}
                            </div>
                        `;
                        endDayModal.classList.remove('hidden');
                        checkBusinessDayStatus();

                } else {
                    throw new Error(result.message || 'حدث خطأ أثناء جلب ملخص اليوم');
                }
            } catch (error) {
                console.error('Error details:', error);
                Swal.fire({
                    title: '!حدث خطأ',
                    text: error.message || 'حدث خطأ غير متوقع',
                    icon: 'error',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'حسناً'
                });
            }
        }

        loadDashboardStats();

        // --- Thermal Invoice Functions ---
        const shopName = '<?php echo addslashes($shopName); ?>';
        const shopPhone = '<?php echo addslashes($shopPhone); ?>';
        const shopAddress = '<?php echo addslashes($shopAddress); ?>';
        const shopCity = '<?php echo addslashes($shopCity); ?>';

        function formatDualDate(dateString) {
            const date = new Date(dateString);
            const day = date.getDate();
            const month = date.getMonth() + 1;
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        async function viewInvoice(invoiceId) {
            try {
                const response = await fetch(`api.php?action=get_invoice_details&invoice_id=${invoiceId}`);
                const result = await response.json();
                
                if (result.success) {
                    displayThermalInvoice(result.data);
                    document.getElementById('thermal-invoice-modal').classList.remove('hidden');
                } else {
                    throw new Error(result.message || 'حدث خطأ أثناء جلب الفاتورة');
                }
            } catch (error) {
                console.error('Error viewing invoice:', error);
                Swal.fire({
                    title: '!حدث خطأ',
                    text: error.message || 'حدث خطأ غير متوقع',
                    icon: 'error',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'حسناً'
                });
            }
        }

        // Thermal modal event listeners
        document.getElementById('close-thermal-invoice-modal').addEventListener('click', () => {
            document.getElementById('thermal-invoice-modal').classList.add('hidden');
        });

        document.getElementById('close-thermal-modal-btn').addEventListener('click', () => {
            document.getElementById('thermal-invoice-modal').classList.add('hidden');
        });

        document.getElementById('print-thermal-btn').addEventListener('click', () => {
            // Get current invoice data from the modal
            const thermalArea = document.getElementById('thermal-invoice-print-area');
            const thermalContent = thermalArea.innerHTML;

            const fullContent = `<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm">
    <title>فاتورة حرارية</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            width: 80mm; padding: 5mm; font-size: 11pt;
            line-height: 1.4; background: white; color: #000;
        }
        .header { text-align: center; margin-bottom: 5mm; border-bottom: 2px dashed #000; padding-bottom: 3mm; }
        .shop-name { font-size: 16pt; font-weight: bold; margin-bottom: 1mm; }
        .shop-info { font-size: 9pt; color: #333; margin: 1mm 0; }
        .invoice-info { margin: 3mm 0; border-bottom: 1px dashed #000; padding-bottom: 2mm; }
        .info-row { display: flex; justify-content: space-between; font-size: 10pt; margin: 1mm 0; }
        .customer-section { margin: 3mm 0; padding: 2mm; background: #f5f5f5; border-radius: 2mm; font-size: 10pt; }
        .items-table { width: 100%; margin: 3mm 0; }
        .items-header { border-top: 2px solid #000; border-bottom: 1px solid #000; padding: 1mm 0; font-weight: bold; font-size: 10pt; }
        .item-row { border-bottom: 1px dashed #ccc; padding: 2mm 0; font-size: 10pt; }
        .item-details { display: flex; justify-content: space-between; font-size: 9pt; }
        .totals-section { margin: 3mm 0; border-top: 2px solid #000; padding-top: 2mm; }
        .total-row { display: flex; justify-content: space-between; font-size: 11pt; margin: 1mm 0; }
        .grand-total { font-size: 14pt; font-weight: bold; border-top: 2px solid #000; padding-top: 2mm; margin-top: 2mm; }
        .footer { text-align: center; margin-top: 5mm; border-top: 2px dashed #000; padding-top: 3mm; font-size: 10pt; }
    </style>
</head>
<body>
    ${thermalContent}
</body>
</html>`;

            const printWindow = window.open('', '_blank', 'width=302,height=600');
            printWindow.document.write(fullContent);
            printWindow.document.close();
            
            // Load JsBarcode for barcode
            const script = printWindow.document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
            script.onload = function() {
                try {
                    // Assuming we have currentInvoiceId, but since it's not global, we need to get it from the content
                    const barcodeElement = printWindow.document.querySelector('#barcode-thermal-display');
                    if (barcodeElement) {
                        // Extract invoice id from the content or use a placeholder
                        JsBarcode(barcodeElement, currentInvoiceId.toString().padStart(6, '0'), {
                            format: "CODE128",
                            width: 1,
                            height: 30,
                            displayValue: false,
                            margin: 0
                        });
                    }
                    printWindow.print();
                    printWindow.close();
                } catch (e) {
                    console.error('Error generating barcode for print:', e);
                    printWindow.print();
                    printWindow.close();
                }
            };
            printWindow.document.head.appendChild(script);
        });

        // --- Main Sales Chart ---
        const ctxMain = document.getElementById('mainChart').getContext('2d');
        const gradientRevenue = ctxMain.createLinearGradient(0, 0, 0, 300);
        gradientRevenue.addColorStop(0, 'rgba(59, 130, 246, 0.6)');
        gradientRevenue.addColorStop(0.5, 'rgba(59, 130, 246, 0.3)');
        gradientRevenue.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        const gradientOrders = ctxMain.createLinearGradient(0, 0, 0, 300);
        gradientOrders.addColorStop(0, 'rgba(16, 185, 129, 0.6)');
        gradientOrders.addColorStop(0.5, 'rgba(16, 185, 129, 0.3)');
        gradientOrders.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        const mainChart = new Chart(ctxMain, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [
                    {
                        label: 'الإيرادات (<?php echo $currency; ?>)',
                        data: <?php echo json_encode($chart_revenue); ?>,
                        borderColor: '#3B82F6',
                        backgroundColor: gradientRevenue,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'الطلبات',
                        data: <?php echo json_encode($chart_orders); ?>,
                        borderColor: '#10B981',
                        backgroundColor: gradientOrders,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        labels: { 
                            color: '#9CA3AF', 
                            font: { family: 'Tajawal', size: 12 },
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.2)',
                        borderWidth: 1,
                        titleFont: { family: 'Tajawal', size: 14, weight: 'bold' },
                        bodyFont: { family: 'Tajawal', size: 12 },
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            title: function(context) {
                                const rawDates = <?php echo json_encode($chart_raw_dates); ?>;
                                const index = context[0].dataIndex;
                                return rawDates[index] || context[0].label;
                            },
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.datasetIndex === 0) {
                                    label += formatNumber(context.parsed.y) + ' <?php echo $currency; ?>';
                                } else {
                                    label += context.parsed.y + ' طلب';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9CA3AF', font: { family: 'Tajawal', size: 11 } },
                        title: {
                            display: true,
                            text: 'التاريخ',
                            color: '#9CA3AF',
                            font: { family: 'Tajawal', size: 12, weight: 'bold' }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: '#9CA3AF', font: { family: 'Tajawal', size: 11 } },
                        title: {
                            display: true,
                            text: 'الإيرادات (<?php echo $currency; ?>)',
                            color: '#3B82F6',
                            font: { family: 'Tajawal', size: 12, weight: 'bold' }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { display: false },
                        ticks: { color: '#10B981', font: { family: 'Tajawal', size: 11 } },
                        title: {
                            display: true,
                            text: 'عدد الطلبات',
                            color: '#10B981',
                            font: { family: 'Tajawal', size: 12, weight: 'bold' }
                        }
                    }
                }
            }
        });

        // --- Category Chart ---
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($cat_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($cat_data); ?>,
                    backgroundColor: [
                        '#3B82F6', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6', '#6366F1', '#EF4444', '#06B6D4'
                    ],
                    borderWidth: 2,
                    borderColor: '#1F2937',
                    hoverOffset: 8,
                    hoverBorderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { 
                            color: '#9CA3AF', 
                            font: { family: 'Tajawal', size: 11 }, 
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.2)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + formatNumber(value) + ' <?php echo $currency; ?> (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
        
        window.addEventListener('beforeprint', () => {
            mainChart.resize();
        });

        const businessDayControls = document.getElementById('business-day-controls');
        const startDayModal = document.getElementById('start-day-modal');
        const endDayModal = document.getElementById('end-day-modal');
        const confirmStartDay = document.getElementById('confirm-start-day');
        const cancelStartDay = document.getElementById('cancel-start-day');
        const closeSummary = document.getElementById('close-summary');
        const openingBalanceInput = document.getElementById('opening-balance');
        const daySummaryContainer = document.getElementById('day-summary');

        // Add input validation for opening balance
        openingBalanceInput.addEventListener('input', function() {
            let value = this.value;
            // Convert Arabic numbers to English
            value = toEnglishNumbers(value);
            // Remove non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');
            this.value = value;
        });

        document.getElementById('view-summary-btn').addEventListener('click', handleViewSummary);
        document.getElementById('calculation-method-btn').addEventListener('click', () => {
            document.getElementById('calculation-method-modal').classList.remove('hidden');
        });
        document.getElementById('close-calculation-method-modal').addEventListener('click', () => {
            document.getElementById('calculation-method-modal').classList.add('hidden');
        });
        document.getElementById('calculation-method-modal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('calculation-method-modal')) {
                document.getElementById('calculation-method-modal').classList.add('hidden');
            }
        });
        
        async function checkBusinessDayStatus() {
            const response = await fetch('api.php?action=get_business_day_status');
            const result = await response.json();

            if (result.success) {
                updateBusinessDayUI(result.data);
            }
        }

        function updateBusinessDayUI(data) {
            const userRole = '<?php echo $_SESSION['role']; ?>';
            const allowedRoles = ['admin', 'manager', 'cashier'];
            
            if (!allowedRoles.includes(userRole)) {
                businessDayControls.innerHTML = '';
                return;
            }
            
            if (data.status === 'open') {
                businessDayControls.innerHTML = `
                    <div class="flex items-center gap-2">
                        <button id="end-day-btn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                            إنهاء اليوم
                        </button>
                    </div>`;
                document.getElementById('end-day-btn').addEventListener('click', handleEndDay);
            } else {
                businessDayControls.innerHTML = `
                    <button id="start-day-btn" class="pulse-button bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow-lg">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round">play_circle</span>
                            بدء يوم عمل جديد
                        </span>
                    </button>`;
                document.getElementById('start-day-btn').addEventListener('click', () => startDayModal.classList.remove('hidden'));
            }
        }

        async function handleStartDay() {
            const opening_balance = openingBalanceInput.value;
            if (!opening_balance) {
                Swal.fire('خطأ', 'الرجاء إدخال الرصيد الافتتاحي', 'error');
                return;
            }
            
            try {
                const response = await fetch('api.php?action=start_day', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ opening_balance })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    startDayModal.classList.add('hidden');
                    Swal.fire('تم بنجاح', 'تم بدء يوم عمل جديد بنجاح', 'success').then(() => {
                        location.reload();
                    });
                } else if (result.code === 'business_day_open_exists') {
                    startDayModal.classList.add('hidden');
                    const { isConfirmed } = await Swal.fire({
                        title: 'يوم عمل مفتوح بالفعل',
                        html: `
                            <p class="mb-4">تم العثور على يوم عمل مفتوح بالفعل.</p>
                            <p>هل تريد تمديد يوم العمل الحالي بإضافة مبلغ <strong class="text-lg">${opening_balance} ${currency}</strong> إلى الرصيد الافتتاحي؟</p>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'نعم، تمديد',
                        cancelButtonText: 'إلغاء'
                    });

                    if (isConfirmed) {
                        // User wants to extend the day
                        const extendResponse = await fetch('api.php?action=extend_day', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                day_id: result.day_id,
                                opening_balance: opening_balance
                            })
                        });
                        const extendResult = await extendResponse.json();
                        if (extendResult.success) {
                            Swal.fire('تم التمديد', 'تم تمديد يوم العمل بنجاح.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('خطأ', extendResult.message || 'فشل في تمديد يوم العمل', 'error');
                        }
                    }
                } else if (result.code === 'business_day_closed_exists') {
                    startDayModal.classList.add('hidden');
                    const { isConfirmed } = await Swal.fire({
                        title: 'يوم عمل مغلق',
                        html: `
                            <p class="mb-4">تم العثور على يوم عمل مغلق لهذا اليوم.</p>
                            <p>هل تريد إعادة فتح اليوم وتمديده بمبلغ <strong class="text-lg">${opening_balance} ${currency}</strong>؟</p>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#F59E0B',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'نعم، إعادة فتح',
                        cancelButtonText: 'إلغاء'
                    });

                    if (isConfirmed) {
                        const reopenResponse = await fetch('api.php?action=reopen_day', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                day_id: result.day_id,
                                opening_balance: opening_balance
                            })
                        });
                        const reopenResult = await reopenResponse.json();
                        if (reopenResult.success) {
                            Swal.fire('تمت إعادة الفتح', 'تم إعادة فتح يوم العمل بنجاح.', 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('خطأ', reopenResult.message || 'فشل في إعادة فتح يوم العمل', 'error');
                        }
                    }
                } else {
                    // Handle other errors
                    Swal.fire({
                        title: 'خطأ',
                        text: result.message || 'حدث خطأ غير متوقع',
                        icon: 'error',
                        confirmButtonText: 'حسناً',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                console.error('Error details:', error);
                Swal.fire({
                    title: 'خطأ في الاتصال',
                    text: 'حدث خطأ أثناء محاولة بدء يوم عمل جديد',
                    icon: 'error',
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#ef4444'
                });
            }
        }

        async function handleEndDay() {
            const confirmed = await Swal.fire({
                title: 'تأكيد إغلاق يوم العمل',
                html: '<div class="text-right">' +
                    '<p class="text-lg font-bold mb-2 text-white">تحذير هام!</p>' +
                    '<p class="mb-4 text-white">هل أنت متأكد من رغبتك في إغلاق يوم العمل؟</p>' +
                    '<p class="text-sm text-yellow-300">⚠️ لا يمكن التراجع عن هذه العملية بعد التنفيذ</p>' +
                    '</div>',
                icon: 'warning',
                iconColor: '#ffffff',
                background: 'rgb(93 0 0)',
                color: '#ffffff',
                showCancelButton: true,
                confirmButtonColor: '#ffffff',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<span style="color: #dc2626; font-weight: bold;">نعم، أغلقه</span>',
                cancelButtonText: '<span style="color: #ffffff;">إلغاء</span>',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    confirmButton: 'px-6 py-2 rounded-lg',
                    cancelButton: 'px-6 py-2 rounded-lg',
                    popup: 'border-4 border-white/30',
                    title: 'text-white'
                }
            });

            // If user confirms, proceed with ending the day
            if (confirmed.isConfirmed) {
                try {
                    const response = await fetch('api.php?action=end_day', { method: 'POST' });
                    const result = await response.json();
                    
                    if (result.success) {
                        const summary = result.data.summary;
                        document.getElementById('summary-data').innerHTML = `
                            <div class="space-y-3">
                                <p class="text-right"><strong class="text-green-400">إجمالي المبيعات:</strong> ${formatNumber(summary.total_sales)} ${currency}</p>
                                <p class="text-right"><strong class="text-blue-400">الرصيد الافتتاحي:</strong> ${formatNumber(summary.opening_balance)} ${currency}</p>
                                <p class="text-right"><strong class="text-yellow-400">الرصيد الختامي:</strong> ${formatNumber(summary.closing_balance)} ${currency}</p>
                                <p class="text-right"><strong class="text-red-400">إجمالي تكلفة البضاعة:</strong> ${formatNumber(summary.total_cogs)} ${currency}</p>
                                <p class="text-right text-xl font-bold mt-4 text-green-400">صافي الربح: ${formatNumber(summary.total_profit)} ${currency}</p>
                            </div>
                        `;
                        endDayModal.classList.remove('hidden');
                        checkBusinessDayStatus();
                        
                        // Show success message
                        Swal.fire({
                            title: '!تمت العملية بنجاح',
                            text: 'تم إغلاق يوم العمل بنجاح',
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'تم'
                        });
                    } else {
                        throw new Error(result.message || 'حدث خطأ أثناء محاولة إغلاق يوم العمل');
                    }
                } catch (error) {
                    console.error('Error details:', error);
                    Swal.fire({
                        title: '!حدث خطأ',
                        text: error.message || 'حدث خطأ غير متوقع',
                        icon: 'error',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'حسناً'
                    });
                }
            }
        }
        
        confirmStartDay.addEventListener('click', handleStartDay);
        cancelStartDay.addEventListener('click', () => startDayModal.classList.add('hidden'));
        closeSummary.addEventListener('click', () => {
            endDayModal.classList.add('hidden');
            location.reload();
        });

        checkBusinessDayStatus();
    });
</script>

<?php if ($show_welcome): ?>
<!-- Welcome Modal -->
<div id="welcome-modal" class="fixed inset-0 bg-gradient-to-br from-black/80 via-dark/90 to-black/80 backdrop-blur-md z-50 flex items-center justify-center p-4 animate-fadeIn">
    <div class="bg-gradient-to-br from-dark-surface via-dark-surface/95 to-dark-surface/90 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden animate-scaleIn">
        <!-- Progress Bar -->
        <div class="w-full bg-dark/50 h-1">
            <div class="bg-gradient-to-r from-primary to-accent h-full w-full animate-pulse"></div>
        </div>

        <div class="p-8 overflow-y-auto max-h-[calc(95vh-4px)] custom-scrollbar">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="relative mx-auto mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-accent to-primary rounded-full flex items-center justify-center mx-auto shadow-2xl animate-bounceIn">
                        <span class="material-icons-round text-4xl text-white animate-pulse">celebration</span>
                    </div>
                    <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full animate-ping"></div>
                </div>
                <h2 class="text-4xl font-bold bg-gradient-to-r from-white via-accent to-primary bg-clip-text text-transparent mb-3 animate-slideUp">
                    مرحباً بك في Smart Shop
                </h2>
                <p class="text-gray-300 text-lg animate-slideUp animation-delay-200">
                    نحن سعداء بانضمامك إلى عائلتنا المتخصصة في إدارة المتاجر
                </p>
            </div>

            <!-- Content Steps -->
            <div class="space-y-6">
                <!-- Step 1: Introduction -->
                <div class="bg-gradient-to-r from-primary/10 to-primary/5 border border-primary/20 rounded-2xl p-6 hover:shadow-xl hover:shadow-primary/10 transition-all duration-500 animate-slideUp animation-delay-400">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-primary/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-icons-round text-2xl text-primary">store</span>
                        </div>
                        <div class="flex-1 text-right">
                            <h3 class="text-xl font-bold text-primary mb-3">شكراً لاختيارك Smart Shop</h3>
                            <p class="text-gray-300 leading-relaxed">
                                نظام Smart Shop هو حل شامل ومتطور لإدارة متجرك بكفاءة واحترافية عالية. يساعدك في إدارة المنتجات، تتبع المخزون، إدارة العملاء، إنشاء الفواتير الاحترافية، وتحليل الأداء المالي بشكل دقيق.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Features -->
                <div class="bg-gradient-to-r from-accent/10 to-accent/5 border border-accent/20 rounded-2xl p-6 hover:shadow-xl hover:shadow-accent/10 transition-all duration-500 animate-slideUp animation-delay-600">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-accent/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-icons-round text-2xl text-accent">rocket_launch</span>
                        </div>
                        <div class="flex-1 text-right">
                            <h3 class="text-xl font-bold text-accent mb-4">ما يمكنك فعله مع النظام</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">inventory_2</span>
                                    <span class="text-gray-300">إدارة شاملة للمنتجات والمخزون</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">people</span>
                                    <span class="text-gray-300">تتبع العملاء وتاريخ المشتريات</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">receipt_long</span>
                                    <span class="text-gray-300">إنشاء فواتير احترافية وتتبع المبيعات</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">analytics</span>
                                    <span class="text-gray-300">تقارير مفصلة وتحليلات مالية</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">point_of_sale</span>
                                    <span class="text-gray-300">نظام نقاط البيع المتطور</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">settings</span>
                                    <span class="text-gray-300">تخصيص النظام حسب احتياجاتك</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Important Setup -->
                <div class="bg-gradient-to-r from-yellow-500/10 to-orange-500/5 border border-yellow-500/20 rounded-2xl p-6 hover:shadow-xl hover:shadow-yellow-500/10 transition-all duration-500 animate-slideUp animation-delay-800">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-yellow-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-icons-round text-2xl text-yellow-400">warning</span>
                        </div>
                        <div class="flex-1 text-right">
                            <h3 class="text-xl font-bold text-yellow-400 mb-3">⚠️ خطوة مهمة قبل البدء</h3>
                            <p class="text-gray-300 leading-relaxed mb-4">
                                للحصول على أفضل أداء وتخصيص النظام حسب احتياجات متجرك، يرجى الذهاب إلى صفحة الإعدادات وتعديل البيانات التالية:
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    العملة المستخدمة في المتجر
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    إعدادات الضرائب والرسوم
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    معلومات التوصيل والمدن
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    إعدادات الإشعارات والتنبيهات
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    شعار المتجر والبيانات الأساسية
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Customization -->
                <div class="bg-gradient-to-r from-green-500/10 to-emerald-500/5 border border-green-500/20 rounded-2xl p-6 hover:shadow-xl hover:shadow-green-500/10 transition-all duration-500 animate-slideUp animation-delay-1000">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-icons-round text-2xl text-green-400">build</span>
                        </div>
                        <div class="flex-1 text-right">
                            <h3 class="text-xl font-bold text-green-400 mb-3">تخصيص مخصص لك</h3>
                            <p class="text-gray-300 leading-relaxed mb-4">
                                يمكننا تعديل أي شيء في النظام بشكل مخصص لك ولمشروعك — فقط تواصل معنا وسنساعدك في تطوير النظام حسب رؤيتك.
                            </p>
                            <div class="bg-gradient-to-r from-dark-surface/80 to-dark/50 border border-white/10 rounded-xl p-4">
                                <h4 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                                    <span class="material-icons-round text-green-400">contact_support</span>
                                    للتواصل معنا
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <p class="text-gray-300">
                                        <span class="text-green-400">🌐</span> الموقع الإلكتروني:
                                        <a href="https://eagleshadow.technology" class="text-primary hover:text-primary-hover underline transition-colors">eagleshadow.technology</a>
                                    </p>
                                    <p class="text-gray-300">
                                        <span class="text-green-400">✉️</span> البريد الإلكتروني:
                                        <a href="mailto:support@eagleshadow.technology" class="text-primary hover:text-primary-hover underline transition-colors">support@eagleshadow.technology</a>
                                    </p>
                                    <p class="text-gray-300">
                                        <span class="text-green-400">📱</span> واتساب:
                                        <a href="https://wa.me/212700979284?text=مرحباً، انا قادم من نظام سمارتشوب واريد تخصيص بعض المميزات لأجل متجري فقط ...." target="_blank" class="text-primary hover:text-primary-hover underline transition-colors">0700979284</a>
                                    </p>
                                    <p class="text-gray-300">
                                        <span class="text-green-400">💬</span> صفحة الدعم:
                                        <a href="contact.php" class="text-primary hover:text-primary-hover underline transition-colors">اذهب إلى صفحة التواصل</a>
                                        <span class="text-xs text-gray-400">(يمكنك الوصول إلى هذه الصفحة في أي وقت عبر الإعدادات)</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Quick Setup -->
                <div class="bg-gradient-to-r from-blue-500/10 to-cyan-500/5 border border-blue-500/20 rounded-2xl p-6 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-500 animate-slideUp animation-delay-1200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-icons-round text-2xl text-blue-400">devices</span>
                        </div>
                        <div class="flex-1 text-right">
                            <h3 class="text-xl font-bold text-blue-400 mb-3">تخصيص سريع للنظام</h3>
                            <p class="text-gray-300 leading-relaxed mb-4">
                                هل جهازك يدعم شاشة اللمس؟
                                <span class="text-yellow-400 text-sm font-bold block mt-1">هذا سيؤثر على بعض الميزات مثل لوحة المفاتيح الافتراضية</span>
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <button id="device-touch" class="flex-1 bg-gradient-to-r from-accent to-accent-hover hover:from-accent-hover hover:to-accent text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-accent/30 flex items-center justify-center gap-3">
                                    <span class="material-icons-round">touch_app</span>
                                    <span>حاسوب بشاشة لمس</span>
                                </button>
                                <button id="device-desktop" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-gray-600/30 flex items-center justify-center gap-3">
                                    <span class="material-icons-round">computer</span>
                                    <span>حاسوب عادي</span>
                                </button>
                            </div>
                            <p id="device-feedback" class="text-sm text-center mt-3 text-gray-400 opacity-0 transition-opacity">تم حفظ الإعداد بنجاح!</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 mt-8 pt-6 border-t border-white/10">
                <button id="welcome-close" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-gray-600/30 flex items-center justify-center gap-2">
                    <span class="material-icons-round">check_circle</span>
                    فهمت، سأذهب لاحقاً
                </button>
                <a href="settings.php" class="flex-1 bg-gradient-to-r from-primary to-primary-hover hover:from-primary-hover hover:to-primary text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-primary/30 text-center flex items-center justify-center gap-2">
                    <span class="material-icons-round">settings</span>
                    اذهب إلى الإعدادات الآن
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes scaleIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}
@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.animate-fadeIn { animation: fadeIn 0.5s ease-out; }
.animate-scaleIn { animation: scaleIn 0.5s ease-out; }
.animate-bounceIn { animation: bounceIn 1s ease-out; }
.animate-slideUp { animation: slideUp 0.6s ease-out forwards; opacity: 0; }
.animation-delay-200 { animation-delay: 0.2s; }
.animation-delay-400 { animation-delay: 0.4s; }
.animation-delay-600 { animation-delay: 0.6s; }
.animation-delay-800 { animation-delay: 0.8s; }
.animation-delay-1000 { animation-delay: 1s; }
.animation-delay-1200 { animation-delay: 1.2s; }

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.3);
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(59, 130, 246, 0.5);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const welcomeModal = document.getElementById('welcome-modal');
    const welcomeClose = document.getElementById('welcome-close');
    const deviceTouchBtn = document.getElementById('device-touch');
    const deviceDesktopBtn = document.getElementById('device-desktop');
    const deviceFeedback = document.getElementById('device-feedback');

    // Close modal and update first_login
    welcomeClose.addEventListener('click', function() {
        fetch('api.php?action=update_first_login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                welcomeModal.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            welcomeModal.style.display = 'none';
        });
    });

    // Device type selection
    deviceTouchBtn.addEventListener('click', () => {
        updateDeviceSetting('touch');
    });

    deviceDesktopBtn.addEventListener('click', () => {
        updateDeviceSetting('desktop');
    });

    function updateDeviceSetting(type) {
        const settings = [
            { name: 'deviceType', value: type }
        ];
        
        if (type === 'touch') {
            settings.push({ name: 'virtualKeyboardEnabled', value: '1' });
        }
        
        fetch('api.php?action=updateSetting', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ settings: settings })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deviceFeedback.textContent = 'تم حفظ الإعدادات بنجاح!';
                deviceFeedback.classList.remove('text-red-400');
                deviceFeedback.classList.add('text-green-400');
                deviceFeedback.style.opacity = '1';
                // Disable buttons
                deviceTouchBtn.disabled = true;
                deviceDesktopBtn.disabled = true;
                deviceTouchBtn.classList.add('opacity-50', 'cursor-not-allowed');
                deviceDesktopBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                deviceFeedback.textContent = 'فشل في حفظ الإعدادات.';
                deviceFeedback.classList.remove('text-green-400');
                deviceFeedback.classList.add('text-red-400');
                deviceFeedback.style.opacity = '1';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            deviceFeedback.textContent = 'حدث خطأ.';
            deviceFeedback.classList.remove('text-green-400');
            deviceFeedback.classList.add('text-red-400');
            deviceFeedback.style.opacity = '1';
        });
    }
});
</script>
<?php endif; ?>

<?php require_once 'src/footer.php'; ?>