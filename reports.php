<?php
require_once 'src/language.php';
require_once 'session.php';
require_once 'db.php';

$page_title = __('reports_title');
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
        COALESCE(SUM(ii.quantity * ii.cost_price), 0) as total_cogs,
        COALESCE(SUM(ii.quantity), 0) as total_items_sold,
        MAX(i.total) as max_order_value
    FROM invoices i
    LEFT JOIN invoice_items ii ON i.id = ii.invoice_id
    WHERE i.created_at BETWEEN '$sql_start' AND '$sql_end'
";
$metrics_result = $conn->query($sql_metrics)->fetch_assoc();

// 1.1 Refunds
$sql_refunds_total = "SELECT COALESCE(SUM(amount), 0) as total FROM refunds WHERE created_at BETWEEN '$sql_start' AND '$sql_end'";
$total_refunds = $conn->query($sql_refunds_total)->fetch_assoc()['total'];

// 1.2 Expenses during the same period
$sql_expenses_total = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE expense_date BETWEEN '$start_date' AND '$end_date'";
$total_general_expenses = $conn->query($sql_expenses_total)->fetch_assoc()['total'];

$sql_rent_total = "SELECT COALESCE(SUM(amount), 0) as total FROM rental_payments WHERE payment_date BETWEEN '$start_date' AND '$end_date'";
$total_rent = $conn->query($sql_rent_total)->fetch_assoc()['total'];

$total_other_costs = $total_general_expenses + $total_rent;

$total_orders = $metrics_result['total_orders'];
$total_items_sold = $metrics_result['total_items_sold'] ?? 0;
$max_order_value = $metrics_result['max_order_value'] ?? 0;
$total_gross_revenue = $metrics_result['total_revenue'];
$total_net_revenue = $total_gross_revenue - $total_refunds; // Net Revenue = Sales - Refunds
$total_revenue = $total_net_revenue; // Alias for backward compatibility in this file

$total_delivery = $metrics_result['total_delivery'];
$total_cogs = $metrics_result['total_cogs'];

// UPDATED Profit Formula: (Net Revenue) - COGS - Expenses
// Note: We treat Delivery Income as Revenue.
$gross_profit = $total_net_revenue - $total_cogs - $total_other_costs;

$avg_order_value = $total_orders > 0 ? ($total_net_revenue - $total_delivery) / $total_orders : 0;
$profit_margin = $total_net_revenue > 0 ? ($gross_profit / $total_net_revenue) * 100 : 0;

// Total cost including COGS, delivery, and other expenses
$total_cost = $total_cogs + $total_delivery + $total_other_costs;
$profit_markup = $total_cost > 0 ? ($gross_profit / $total_cost) * 100 : 0;

// Fetch cycle configuration
$resCycle = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'expense_cycle'");
$expenseCycleType = ($resCycle && $resCycle->num_rows > 0) ? $resCycle->fetch_assoc()['setting_value'] : 'monthly';

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
        i.created_at,
        i.is_holiday,
        i.holiday_name
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
    $slowest_day_formatted = $slowest_day ? date('Y-m-d', strtotime($slowest_day['sale_date'])) : __('no_data');
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
    $top_city_name = $top_city ? $top_city['city'] : __('no_data');
$top_city_orders = $top_city ? $top_city['order_count'] : 0;
$stmt->close();

// 11. Holiday Sales in selected range
$sql_holiday_stats = "
    SELECT 
        COUNT(*) as holiday_orders,
        COALESCE(SUM(total), 0) as holiday_revenue
    FROM invoices
    WHERE created_at BETWEEN '$sql_start' AND '$sql_end'
    AND is_holiday = 1
";
$holiday_stats = $conn->query($sql_holiday_stats)->fetch_assoc();
$holiday_orders_range = $holiday_stats['holiday_orders'];
$holiday_revenue_range = $holiday_stats['holiday_revenue'];

// 12. Holiday Breakdown in selected range
$sql_holiday_breakdown = "
    SELECT 
        COALESCE(holiday_name, 'عطلة غير محددة') as holiday_name,
        COUNT(*) as orders,
        COALESCE(SUM(total), 0) as revenue
    FROM invoices
    WHERE created_at BETWEEN '$sql_start' AND '$sql_end'
    AND is_holiday = 1
    GROUP BY holiday_name
";
$holiday_breakdown = $conn->query($sql_holiday_breakdown);
if (!$holiday_breakdown) {
    // Fallback to avoid error if query fails
    $holiday_breakdown = (object) ['num_rows' => 0];
}

// 13. Enhanced Holiday Analytics (Comparison)
// Get count of actual holiday days in the date range
$sql_holiday_count = "
    SELECT COUNT(DISTINCT DATE(created_at)) as holiday_days
    FROM invoices
    WHERE created_at BETWEEN '$sql_start' AND '$sql_end'
    AND is_holiday = 1
";
$holiday_days_res = $conn->query($sql_holiday_count);
$actual_holiday_days = $holiday_days_res ? $holiday_days_res->fetch_assoc()['holiday_days'] : 0;

// Get count of regular days in the date range (where sales happened)
$sql_regular_days_count = "
    SELECT COUNT(DISTINCT DATE(created_at)) as regular_days
    FROM invoices
    WHERE created_at BETWEEN '$sql_start' AND '$sql_end'
    AND is_holiday = 0
";
$regular_days_res = $conn->query($sql_regular_days_count);
$actual_regular_days = $regular_days_res ? $regular_days_res->fetch_assoc()['regular_days'] : 0;

$avg_rev_per_holiday = $actual_holiday_days > 0 ? $holiday_revenue_range / $actual_holiday_days : 0;
$regular_revenue = $total_revenue - $holiday_revenue_range;
$avg_rev_per_regular = $actual_regular_days > 0 ? $regular_revenue / $actual_regular_days : 0;

$holiday_performance_index = $avg_rev_per_regular > 0 ? ($avg_rev_per_holiday / $avg_rev_per_regular) * 100 : 0;

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

    /* Dark Mode Date Picker Styling */
    input[type="date"],
    input[type="time"],
    input[type="datetime-local"] {
        color-scheme: dark;
    }

    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator,
    input[type="datetime-local"]::-webkit-calendar-picker-indicator {
        filter: invert(1) brightness(1.1);
        cursor: pointer;
    }

    input[type="date"]::-webkit-outer-spin-button,
    input[type="date"]::-webkit-inner-spin-button,
    input[type="time"]::-webkit-outer-spin-button,
    input[type="time"]::-webkit-inner-spin-button {
        display: none;
    }

    /* Firefox support */
    input[type="date"],
    input[type="time"],
    input[type="datetime-local"] {
        background-color: #1f2937;
        color: #ffffff;
    }

    /* Placeholder text styling */
    input[type="date"]::placeholder,
    input[type="time"]::placeholder,
    input[type="datetime-local"]::placeholder {
        color: #9ca3af;
    }

    /* Focus state styling */
    input[type="date"]:focus,
    input[type="time"]:focus,
    input[type="datetime-local"]:focus {
        background-color: #111827;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
</style>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark transition-all duration-300">
    <div id="holiday-notification" class="hidden bg-blue-500/10 text-blue-400 p-4 text-center border-b border-blue-500/20 no-print">
        <span class="material-icons-round text-sm align-middle mr-1">celebration</span>
        اليوم عطلة رسمية: <span id="holiday-name" class="font-bold"></span>.
    </div>
    
    <header class="bg-dark-surface/50 backdrop-blur-md border-b border-white/5 p-3 md:p-6 sticky top-0 z-20 no-print">
        <!-- Tabs Navigation -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4 border-b border-white/5 pb-4">
            <div class="flex gap-4 w-full md:w-auto overflow-x-auto">
                <button onclick="switchTab('dashboard')" id="tab-btn-dashboard" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-white font-bold transition-all whitespace-nowrap">
                    <span class="material-icons-round text-sm">dashboard</span>
                    <?php echo __('dashboard'); ?>
                </button>
                <button onclick="switchTab('annual-tips')" id="tab-btn-annual-tips" class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-white/5 text-gray-400 hover:text-white font-bold transition-all whitespace-nowrap">
                    <span class="material-icons-round text-sm">lightbulb</span>
                    <?php echo __('annual_tips'); ?>
                </button>
            </div>
            <div id="real-date-time" class="text-gray-400 font-mono text-lg font-bold self-end md:self-auto" dir="ltr"></div>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 tab-specific" id="header-controls-dashboard">
            <div class="flex items-center justify-between w-full md:w-auto">
                <div>
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <span class="material-icons-round text-pink-500">analytics</span>
                        <?php echo __('reports_title'); ?>
                    </h2>
                    <p class="text-sm text-gray-400 mt-1">
                        <?php echo __('viewing_data_from'); ?> <span class="text-white font-bold"><?php echo $start_date; ?></span> <?php echo __('to'); ?> <span class="text-white font-bold"><?php echo $end_date; ?></span>
                    </p>
                </div>
                <button id="toggle-dashboard-filters" class="md:hidden p-2 rounded-lg bg-white/5 text-gray-400 hover:text-white border border-white/10 transition-colors">
                    <span class="material-icons-round">filter_list</span>
                </button>
            </div>

            <div id="dashboard-filters-content" class="hidden md:flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full md:w-auto transition-all duration-300">
            <?php if ($is_day_active): ?>
                <div class="bg-green-500/10 text-green-400 px-4 py-2 rounded-xl text-sm text-center sm:text-start">
                    <?php echo __('active_business_day'); ?>
                </div>
            <?php endif; ?>
            <form method="GET" class="flex flex-wrap items-center gap-3 bg-dark/50 p-2 rounded-xl border border-white/5 shadow-lg justify-center sm:justify-start">
                <div class="flex flex-wrap justify-center gap-1 bg-dark-surface rounded-lg p-1 border border-white/5">
                    <button type="submit" name="range" value="today" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == 'today' ? 'active' : ''; ?>"><?php echo __('today'); ?></button>
                    <button type="submit" name="range" value="yesterday" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == 'yesterday' ? 'active' : ''; ?>"><?php echo __('yesterday'); ?></button>
                    <button type="submit" name="range" value="7days" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == '7days' ? 'active' : ''; ?>"><?php echo __('7_days'); ?></button>
                    <button type="submit" name="range" value="30days" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == '30days' ? 'active' : ''; ?>"><?php echo __('30_days'); ?></button>
                    <button type="submit" name="range" value="this_month" class="date-btn px-3 py-1.5 rounded-md text-xs font-bold text-gray-400 hover:text-white transition-all <?php echo $range == 'this_month' ? 'active' : ''; ?>"><?php echo __('month'); ?></button>
                </div>
                
                <div class="h-8 w-px bg-white/10 hidden sm:block"></div>

                <div class="flex flex-wrap justify-center items-center gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 text-xs"><?php echo __('from'); ?></span>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="bg-dark border border-white/10 text-white text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-primary">
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 text-xs"><?php echo __('to'); ?></span>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="bg-dark border border-white/10 text-white text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-primary">
                    </div>
                    <button type="submit" name="range" value="custom" class="bg-primary hover:bg-primary-hover text-white p-1.5 rounded-lg transition-colors shadow-md">
                        <span class="material-icons-round text-sm block">filter_alt</span>
                    </button>
                </div>
            </form>
            <button id="view-summary-btn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                <?php echo __('view_period_summary'); ?>
            </button>
            <div id="business-day-controls"></div>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-3 md:p-6 scroll-smooth">
        
        <!-- DASHBOARD TAB -->
        <div id="tab-dashboard" class="tab-content block">
        <!-- Welcome & Quick Actions Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-8 mb-8">
            <!-- Welcome Banner -->
            <div class="lg:col-span-2 bg-gradient-to-br from-dark-surface/90 to-dark-surface/50 backdrop-blur-xl border border-white/10 rounded-3xl p-4 md:p-8 relative overflow-hidden animate-enter group hover:border-primary/20 transition-colors duration-500">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-l from-primary/10 to-transparent pointer-events-none opacity-50"></div>
                <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-primary/20 rounded-full blur-[80px] pointer-events-none animate-pulse"></div>
                
                <div class="relative z-10">
                    <h1 class="text-2xl md:text-4xl font-bold text-white mb-2 leading-tight flex flex-col md:flex-row gap-2 md:items-center">
                        <span id="dynamic-greeting" class="flex items-center gap-2"></span> 
                        <span class="gradient-text"><?php echo htmlspecialchars($_SESSION['username']); ?></span> 👋
                    </h1>
                    
                    <p class="text-gray-300 text-lg mb-1 font-medium flex items-center gap-2">
                        <?php echo __('welcome_to'); ?> <span class="text-primary font-bold"><?php echo htmlspecialchars($shopName); ?></span>
                    </p>
                    
                    <p id="dynamic-subtext" class="text-gray-400 text-sm md:text-base max-w-2xl mb-6 font-light italic">
                        <!-- Subtext from JS -->
                    </p>

                    <!-- The stats sentence (Store performance overview...) -->
                    <div class="bg-white/5 border border-white/10 rounded-xl p-3 md:p-4 max-w-fit mb-8 backdrop-blur-md shadow-lg">
                        <p class="text-gray-300 text-sm md:text-base flex items-start md:items-center gap-3 leading-relaxed">
                            <span class="material-icons-round text-primary bg-primary/10 p-1 rounded-lg">analytics</span>
                            <span><?php echo sprintf(__('store_performance_overview'), '<span class="text-white font-bold text-lg px-1" id="today-orders-count-banner">0</span>', '<span class="text-primary font-bold text-lg px-1" id="today-revenue-banner">0</span>'); ?></span>
                        </p>
                    </div>
                    
                    <div class="flex flex-wrap gap-4">
                        <a href="pos.php" class="action-btn group bg-gradient-to-r from-primary to-primary-hover text-white px-6 py-3.5 rounded-xl font-bold shadow-lg shadow-primary/25 flex items-center gap-3 hover:-translate-y-1 hover:shadow-primary/40 transition-all">
                            <span class="material-icons-round transition-transform group-hover:rotate-12">add_shopping_cart</span>
                            <?php echo __('new_sale'); ?>
                        </a>
                        <a href="products.php" class="action-btn group bg-white/5 hover:bg-white/10 text-white px-6 py-3.5 rounded-xl font-bold border border-white/10 flex items-center gap-3 hover:-translate-y-1 hover:border-white/20 transition-all">
                            <span class="material-icons-round text-accent group-hover:scale-110 transition-transform">inventory</span>
                            <?php echo __('products_management'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats / Actions Grid -->
            <div class="grid grid-cols-2 gap-2 md:gap-4 animate-enter delay-100">
                <a href="customers.php" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-primary/30 rounded-2xl p-4 md:p-6 flex flex-col justify-center items-center transition-all stat-card cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <span class="material-icons-round text-purple-500 text-2xl">people</span>
                    </div>
                    <span class="text-white font-bold"><?php echo __('customers'); ?></span>
                    <span class="text-xs text-gray-500 mt-1"><?php echo __('view_history'); ?></span>
                </a>
                <a href="invoices.php" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-accent/30 rounded-2xl p-4 md:p-6 flex flex-col justify-center items-center transition-all stat-card cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-accent/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <span class="material-icons-round text-accent text-2xl">receipt_long</span>
                    </div>
                    <span class="text-white font-bold"><?php echo __('invoices'); ?></span>
                    <span class="text-xs text-gray-500 mt-1"><?php echo __('sales_history'); ?></span>
                </a>
                <a href="settings.php" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-orange-500/30 rounded-2xl p-4 md:p-6 flex flex-col justify-center items-center transition-all stat-card cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-orange-500/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <span class="material-icons-round text-orange-500 text-2xl">settings</span>
                    </div>
                    <span class="text-white font-bold"><?php echo __('settings'); ?></span>
                    <span class="text-xs text-gray-500 mt-1"><?php echo __('system_customization'); ?></span>
                </a>
                <a href="reports.php" class="group bg-dark-surface/60 hover:bg-dark-surface/80 backdrop-blur-md border border-white/5 hover:border-pink-500/30 rounded-2xl p-4 md:p-6 flex flex-col justify-center items-center transition-all stat-card cursor-pointer">
                    <div class="w-12 h-12 rounded-full bg-pink-500/10 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                        <span class="material-icons-round text-pink-500 text-2xl">analytics</span>
                    </div>
                    <span class="text-white font-bold"><?php echo __('reports_title'); ?></span>
                    <span class="text-xs text-gray-500 mt-1"><?php echo __('comprehensive_analysis'); ?></span>
                </a>
            </div>
        </div>

        <div id="metrics-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-blue-500">payments</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1"><?php echo __('net_revenue'); ?></p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo number_format($total_net_revenue, 2); ?> <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <div class="mt-4 flex items-center gap-2">
                    <div class="text-xs text-blue-400 bg-blue-500/10 w-fit px-2 py-1 rounded-full border border-blue-500/10">
                        <span class="material-icons-round text-sm mr-1">receipt</span>
                        <span><?php echo sprintf(__('invoice_count'), number_format($total_orders)); ?></span>
                    </div>
                    <?php if($total_refunds > 0): ?>
                    <div class="text-xs text-red-400 bg-red-500/10 w-fit px-2 py-1 rounded-full border border-red-500/10">
                        <span><?php echo sprintf(__('returns_count'), '-' . number_format($total_refunds)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-green-500">savings</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1"><?php echo __('estimated_net_profit'); ?></p>
                <h3 class="text-3xl font-bold text-green-500 stat-value"><?php echo number_format($gross_profit, 2); ?> <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <div class="mt-4 flex items-center gap-3">
                    <div class="text-xs text-gray-400"><?php echo __('total_cost'); ?>: <span class="text-white"><?php echo number_format($total_cost, 2); ?></span></div>
                    <div class="text-xs text-green-400 bg-green-500/10 px-2 py-1 rounded-full border border-green-500/10"><?php echo __('profit_margin'); ?>: <?php echo number_format($profit_markup, 1); ?>%</div>
                </div>
            </div>

            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-purple-500">shopping_cart</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1"><?php echo __('avg_order_value'); ?></p>
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
                <p class="text-sm text-gray-400 font-medium mb-1"><?php echo __('collected_delivery_costs'); ?></p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo number_format($total_delivery, 2); ?> <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                <p class="text-xs text-gray-500 mt-4"><?php echo __('total_delivery_fees'); ?></p>
            </div>
        </div>

        <!-- Additional Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-red-500">map</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1"><?php echo __('orders_outside_city'); ?></p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo number_format($outside_city_orders); ?></h3>
                <div class="mt-4 flex items-center text-xs text-red-400 bg-red-500/10 w-fit px-2 py-1 rounded-full border border-red-500/10">
                    <span class="material-icons-round text-sm mr-1">location_on</span>
                    <span><?php echo __('main_city'); ?>: <?php echo htmlspecialchars($home_city ?: __('undefined_city')); ?></span>
                </div>
            </div>

            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-cyan-500">location_city</span>
                </div>
                <p class="text-sm text-gray-400 font-medium mb-1"><?php echo __('top_city'); ?></p>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo htmlspecialchars($top_city_name); ?></h3>
                <div class="mt-4 flex items-center text-xs text-cyan-400 bg-cyan-500/10 w-fit px-2 py-1 rounded-full border border-cyan-500/10">
                    <span class="material-icons-round text-sm mr-1">shopping_cart</span>
                    <span><?php echo number_format($top_city_orders); ?> <?php echo __('order_unit'); ?></span>
                </div>
            </div>

            <div class="glass-card p-6 relative overflow-hidden group hover:-translate-y-1 transition-transform <?php echo $holiday_orders_range > 0 ? '' : 'opacity-50'; ?>">
                <div class="absolute top-0 left-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-icons-round text-6xl text-yellow-500">celebration</span>
                </div>
                <div class="flex justify-between items-start mb-1">
                    <p class="text-sm text-gray-400 font-medium"><?php echo __('holiday_sales_analysis'); ?></p>
                    <?php if ($holiday_performance_index > 100): ?>
                        <span class="text-[10px] bg-green-500/20 text-green-400 px-2 py-0.5 rounded-full font-bold">+<?php echo number_format($holiday_performance_index - 100, 1); ?>% <?php echo __('growth'); ?></span>
                    <?php elseif ($holiday_performance_index > 0): ?>
                        <span class="text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full font-bold"><?php echo number_format(100 - $holiday_performance_index, 1); ?>%- <?php echo __('less'); ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="text-3xl font-bold text-white stat-value"><?php echo number_format($holiday_revenue_range, 2); ?> <span class="text-sm text-gray-500 font-normal"><?php echo $currency; ?></span></h3>
                
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="bg-white/5 p-2 rounded-lg border border-white/5">
                        <p class="text-[10px] text-gray-500"><?php echo __('daily_sales_rate_holiday'); ?></p>
                        <p class="text-xs font-bold text-yellow-500"><?php echo number_format($avg_rev_per_holiday, 2); ?></p>
                    </div>
                    <div class="bg-white/5 p-2 rounded-lg border border-white/5">
                        <p class="text-[10px] text-gray-500"><?php echo __('daily_sales_rate_normal'); ?></p>
                        <p class="text-xs font-bold text-gray-300"><?php echo number_format($avg_rev_per_regular, 2); ?></p>
                    </div>
                </div>

                <div class="mt-4 flex items-center text-xs text-yellow-400 bg-yellow-500/10 w-fit px-2 py-1 rounded-full border border-yellow-500/10">
                    <span class="material-icons-round text-sm mr-1">event</span>
                    <span><?php echo sprintf(__('holiday_orders_stats'), number_format($holiday_orders_range), $actual_holiday_days); ?></span>
                </div>

                <?php if ($holiday_orders_range > 0 && $holiday_breakdown->num_rows > 0): ?>
                <div class="mt-4 pt-4 border-t border-white/5 space-y-2">
                    <?php 
                    $holiday_breakdown->data_seek(0);
                    while($item = $holiday_breakdown->fetch_assoc()): 
                    ?>
                    <div class="flex justify-between items-center text-[10px]">
                        <span class="text-gray-400 truncate max-w-[120px]">• <?php echo htmlspecialchars($item['holiday_name']); ?></span>
                        <span class="text-yellow-500/80 font-bold"><?php echo number_format($item['revenue'], 2); ?> <?php echo $currency; ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="charts-grid" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 glass-card p-6 print-break-page">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="material-icons-round text-blue-500">show_chart</span>
                        <?php echo __('sales_orders_analysis'); ?>
                    </h3>
                </div>
                <div class="relative h-80 w-full">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>

            <div class="glass-card p-6 flex flex-col print-break-page">
                <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                    <span class="material-icons-round text-pink-500">pie_chart</span>
                    <?php echo __('sales_by_category'); ?>
                </h3>
                <div class="relative flex-1 flex items-center justify-center h-64">
                    <canvas id="categoryChart"></canvas>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-400"><?php echo __('revenue_distribution'); ?></p>
                </div>
            </div>
        </div>

        <div id="tables-grid" class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <div class="glass-card p-6 print-break-page">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-yellow-500">emoji_events</span>
                    <?php echo __('best_selling_products'); ?>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10 text-gray-400 text-xs uppercase">
                                <th class="pb-3 w-1/2"><?php echo __('product'); ?></th>
                                <th class="pb-3 text-center"><?php echo __('quantity'); ?></th>
                                <th class="pb-3 text-left"><?php echo __('total'); ?></th>
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
                                echo '<tr><td colspan="3" class="text-center py-4 text-gray-500">' . __('no_data') . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 print-break-page">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-red-500">trending_down</span>
                    <?php echo __('least_selling_products'); ?>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10 text-gray-400 text-xs uppercase">
                                <th class="pb-3 w-1/2"><?php echo __('product'); ?></th>
                                <th class="pb-3 text-center"><?php echo __('quantity'); ?></th>
                                <th class="pb-3 text-left"><?php echo __('total'); ?></th>
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
                                echo '<tr><td colspan="3" class="text-center py-4 text-gray-500">' . __('no_data') . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 print-break-page">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-purple-500">people</span>
                    <?php echo __('top_customers'); ?>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10 text-gray-400 text-xs uppercase">
                                <th class="pb-3 w-1/2"><?php echo __('customer'); ?></th>
                                <th class="pb-3 text-center"><?php echo __('order_count'); ?></th>
                                <th class="pb-3 text-left"><?php echo __('total_sales'); ?></th>
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
                                echo '<tr><td colspan="3" class="text-center py-4 text-gray-500">' . __('no_data') . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 print-break-page">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-blue-500">receipt_long</span>
                    <?php echo __('latest_invoices'); ?>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10 text-gray-400 text-xs uppercase">
                                <th class="pb-3 w-1/3"><?php echo __('invoice_number'); ?></th>
                                <th class="pb-3 w-1/3"><?php echo __('customer'); ?></th>
                                <th class="pb-3 text-left"><?php echo __('amount'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-sm">
                            <?php 
                            if ($latest_invoices->num_rows > 0) {
                                while($inv = $latest_invoices->fetch_assoc()) {
                            ?>
                            <tr class="group hover:bg-white/5 transition-colors">
                                <td class="py-3 text-white font-medium flex items-center gap-1">
                                    #<?php echo htmlspecialchars($inv['id']); ?>
                                    <?php if ($inv['is_holiday']): ?>
                                        <span class="material-icons-round text-[14px] text-yellow-500 cursor-help" title="<?php echo htmlspecialchars($inv['holiday_name']); ?>">celebration</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-gray-300"><?php echo htmlspecialchars($inv['customer_name'] ?: __('undefined_customer')); ?></td>
                                <td class="py-3 text-left text-primary font-bold"><?php echo number_format($inv['total'], 2); ?> <span class="text-xs text-gray-500 font-normal"><?php echo $currency; ?></span></td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="3" class="text-center py-4 text-gray-500">' . __('no_data') . '</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card p-6 lg:col-span-4">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-teal-500">dashboard</span>
                    <?php echo __('quick_summary'); ?>
                </h3>
                
                <div class="mt-6 pt-6 border-t border-white/10 page-break-avoid">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500"><?php echo __('best_selling_day'); ?></p>
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
                             <p class="text-xs text-gray-400 mt-1"><?php echo $maxOrdersCount; ?> <?php echo __('order_unit'); ?></p>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500"><?php echo __('worst_selling_day'); ?></p>
                             <?php
                                // If we only have 1 day of data, Best Day == Worst Day. This is confusing.
                                // Logic: If total chart entries (days) <= 1, show '-' for worst day.
                                if (count($chart_labels) <= 1) {
                                    echo '<p class="text-white font-bold mt-1">-</p>';
                                    echo '<p class="text-xs text-gray-400 mt-1">' . __('not_available') . '</p>';
                                } else {
                                    echo '<p class="text-white font-bold mt-1">' . $slowest_day_formatted . '</p>';
                                    echo '<p class="text-xs text-gray-400 mt-1">' . $slowest_day_orders . ' ' . __('order_unit') . '</p>';
                                }
                             ?>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500"><?php echo __('avg_cart_items'); ?></p>
                             <p class="text-[10px] text-gray-600 mb-1 leading-tight"><?php echo __('avg_products_per_order'); ?><br><?php echo __('avg_items_calculation'); ?></p>
                             <?php 
                                $avgItems = $total_orders > 0 ? $total_items_sold / $total_orders : 0;
                             ?>
                             <p class="text-white font-bold mt-1"><?php echo number_format($avgItems, 1); ?></p>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500"><?php echo __('unique_customers_count'); ?></p>
                             <p class="text-[10px] text-gray-600 mb-1 leading-tight"><?php echo __('total_buying_customers'); ?></p>
                             <p class="text-white font-bold mt-1"><?php echo number_format($unique_customers); ?></p>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500"><?php echo __('avg_daily_revenue'); ?></p>
                             <p class="text-[10px] text-gray-600 mb-1 leading-tight"><?php echo __('avg_daily_sales'); ?></p>
                             <p class="text-white font-bold mt-1"><?php echo number_format($avg_daily_revenue, 2); ?> <span class="text-xs text-gray-500"><?php echo $currency; ?></span></p>
                         </div>
                         <div class="bg-dark p-3 rounded-lg border border-white/5 text-center">
                             <p class="text-xs text-gray-500"><?php echo __('avg_daily_orders'); ?></p>
                             <p class="text-[10px] text-gray-600 mb-1 leading-tight"><?php echo __('avg_daily_orders_desc'); ?></p>
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
                    <?php echo __('period_summary_view'); ?>
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-dark rounded-lg border border-white/5">
                        <span class="text-gray-400"><?php echo __('total_sales'); ?></span>
                        <span class="text-green-400 font-bold text-lg"><?php echo number_format($total_revenue, 2); ?> <?php echo $currency; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-dark rounded-lg border border-white/5">
                        <span class="text-gray-400"><?php echo __('total_cogs'); ?></span>
                        <span class="text-red-400 font-bold text-lg"><?php echo number_format($total_cogs, 2); ?> <?php echo $currency; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-dark rounded-lg border border-white/5">
                        <span class="text-gray-400"><?php echo __('total_general_expenses'); ?></span>
                        <span class="text-orange-400 font-bold text-lg"><?php echo number_format($total_other_costs, 2); ?> <?php echo $currency; ?></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-dark rounded-lg border border-white/5">
                        <span class="text-gray-400"><?php echo __('net_profit'); ?></span>
                        <span class="text-primary font-bold text-xl"><?php echo number_format($gross_profit, 2); ?> <?php echo $currency; ?></span>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-white/5 rounded-xl border border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-round text-primary text-sm">update</span>
                        <span class="text-xs text-gray-400"><?php echo __('financial_cycle_system'); ?>:</span>
                    </div>
                    <span class="text-xs font-bold text-white"><?php echo $expenseCycleType === 'bi-monthly' ? __('bi_monthly') : __('monthly'); ?></span>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="glass-card p-6">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-yellow-500">lightbulb</span>
                    <?php echo __('tips'); ?>
                </h3>
                <div class="space-y-4">
                    <?php if ($total_orders > 0 && $days_diff <= 30): ?>    
                    <?php
                    $tips = [];

                    // Tip 1: Based on profit margin
                    if ($profit_margin < 10) {
                        $tips[] = sprintf(__('low_profit_margin_tip'), number_format($profit_margin, 1) . "%");
                    } elseif ($profit_margin > 30) {
                        $tips[] = sprintf(__('good_profit_margin_tip'), number_format($profit_margin, 1) . "%");
                    }

                    // Tip 2: Delivery Cost Analysis (NEW)
                    if ($total_revenue > 0) {
                        $delivery_percentage = ($total_delivery / $total_revenue) * 100;
                        if ($total_delivery == 0 && $outside_city_orders > 0) {
                            $tips[] = __('delivery_revenue_issue_tip');
                        } elseif ($delivery_percentage > 15) {
                            $tips[] = sprintf(__('high_delivery_cost_tip'), number_format($delivery_percentage, 1) . "%");
                        }
                    }

                    // Tip 3: Based on average order value
                    if ($avg_order_value < 50) {
                        $tips[] = __('low_avg_order_tip');
                    } elseif ($avg_order_value > 200) {
                        $tips[] = sprintf(__('good_avg_order_tip'), number_format($avg_order_value, 2) . " " . $currency);
                    }

                    // Tip 4: Based on unique customers
                    if ($unique_customers < 10) {
                        $tips[] = __('low_customer_count_tip');
                    }

                    // If no specific tips
                    if (empty($tips)) {
                        $tips[] = __('balanced_store_tip');
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
                        <p class="text-gray-300 text-sm leading-relaxed"><?php echo __('no_specific_tips'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>  
        </div> <!-- End Dashboard Tab -->

        <!-- ANNUAL TIPS TAB -->
        <div id="tab-annual-tips" class="tab-content hidden space-y-6">
            <!-- Filter Section -->
            <div class="glass-card p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <span class="material-icons-round text-yellow-500">lightbulb</span>
                        <?php echo __('annual_tips'); ?>
                    </h3>
                    <p class="text-gray-400 text-sm mt-1"><?php echo __('annual_tips_tab_desc'); ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <label for="annual-year-select" class="text-gray-400 text-sm"><?php echo __('year_label'); ?>:</label>
                    <select id="annual-year-select" class="bg-dark border border-white/10 text-white rounded-lg px-4 py-2 focus:outline-none focus:border-primary">
                        <!-- Populated via JS -->
                    </select>
                    <button id="analyze-year-btn" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-lg font-bold transition-all shadow-lg shadow-primary/20 flex items-center gap-2">
                        <span class="material-icons-round">analytics</span>
                        <?php echo __('analyze_btn'); ?>
                    </button>
                </div>
            </div>

            <!-- Initial State / Loading -->
            <div id="annual-loading" class="hidden text-center py-20">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent mb-4"></div>
                <p class="text-gray-400"><?php echo __('processing_data'); ?></p>
            </div>

            <!-- Results Container -->
            <div id="annual-results" class="hidden space-y-6">
                
                <!-- Health Score & Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Score Card -->
                    <div class="glass-card p-6 flex flex-col items-center justify-center text-center relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent"></div>
                        <div class="relative z-10 w-full flex flex-col items-center">
                            <h4 class="text-gray-400 text-sm font-bold uppercase tracking-wider mb-4"><?php echo __('financial_health_score'); ?></h4>
                            <div class="relative w-40 h-40 flex items-center justify-center mb-2">
                                <svg class="w-full h-full transform -rotate-90 drop-shadow-2xl">
                                    <circle cx="80" cy="80" r="70" stroke="rgba(255,255,255,0.1)" stroke-width="12" fill="transparent" />
                                    <circle id="score-circle" cx="80" cy="80" r="70" stroke="#10B981" stroke-width="12" fill="transparent" stroke-dasharray="439.8" stroke-dashoffset="439.8" style="transition: stroke-dashoffset 1.5s ease-out;" />
                                </svg>
                                <div class="absolute flex flex-col items-center">
                                    <span id="score-value" class="text-5xl font-bold text-white">0</span>
                                    <span class="text-xs text-gray-500">/ 100</span>
                                </div>
                            </div>
                            <p id="score-verdict" class="mt-2 font-bold text-lg text-white"></p>
                        </div>
                    </div>
                    
                    <!-- Revenue & Profit -->
                    <div class="glass-card p-6 flex flex-col justify-center gap-6 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5">
                            <span class="material-icons-round text-8xl">payments</span>
                        </div>
                        
                        <div class="relative z-10">
                            <div class="flex justify-between items-end mb-1">
                                <p class="text-gray-400 text-xs uppercase font-bold"><?php echo __('total_revenue'); ?></p>
                                <span id="revenue-growth" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-700 text-gray-300"></span>
                            </div>
                            <h3 class="text-3xl font-bold text-white" id="annual-revenue">0</h3>
                        </div>
                        
                        <div class="w-full h-px bg-white/5"></div>

                        <div class="relative z-10">
                            <div class="flex justify-between items-end mb-1">
                                <p class="text-gray-400 text-xs uppercase font-bold"><?php echo __('net_profit'); ?></p>
                                <span id="profit-growth" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-700 text-gray-300"></span>
                            </div>
                            <h3 class="text-3xl font-bold text-green-400" id="annual-profit">0</h3>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="flex-1 bg-gray-700 h-2 rounded-full overflow-hidden">
                                    <div id="margin-bar" class="bg-green-500 h-full" style="width: 0%"></div>
                                </div>
                                <span id="annual-margin" class="text-xs font-bold text-gray-300">0%</span>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1"><?php echo __('profit_margin'); ?></p>
                        </div>
                    </div>

                    <!-- Best/Worst Months -->
                    <div class="glass-card p-6 flex flex-col justify-center">
                        <h4 class="text-white font-bold mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
                            <span class="material-icons-round text-purple-500">calendar_today</span>
                            <?php echo __('seasonality'); ?>
                        </h4>
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-green-500/10 flex items-center justify-center flex-shrink-0">
                                    <span class="material-icons-round text-green-500">trending_up</span>
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-400 text-xs block mb-0.5"><?php echo __('best_month'); ?></span>
                                    <div class="flex justify-between items-end">
                                        <p class="text-white font-bold text-lg" id="best-month-name">-</p>
                                        <p class="text-xs font-mono text-green-400" id="best-month-val">0</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-red-500/10 flex items-center justify-center flex-shrink-0">
                                    <span class="material-icons-round text-red-500">trending_down</span>
                                </div>
                                <div class="flex-1">
                                    <span class="text-gray-400 text-xs block mb-0.5"><?php echo __('worst_month'); ?></span>
                                    <div class="flex justify-between items-end">
                                        <p class="text-white font-bold text-lg" id="worst-month-name">-</p>
                                        <p class="text-xs font-mono text-red-400" id="worst-month-val">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Advice -->
                <div class="glass-card p-6">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <span class="material-icons-round text-blue-400">psychology</span>
                        <?php echo __('smart_recommendations'); ?>
                    </h3>
                    <div id="advice-container" class="grid grid-cols-1 gap-4">
                        <!-- Dynamic Content -->
                    </div>
                </div>
                
                <!-- Top Products Table -->
                <div class="glass-card p-6">
                    <h3 class="text-lg font-bold text-white mb-4"><?php echo __('top_products_annual'); ?></h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-start">
                            <thead>
                                <tr class="text-gray-400 border-b border-white/10">
                                    <th class="pb-2"><?php echo __('product'); ?></th>
                                    <th class="pb-2"><?php echo __('quantity_sold'); ?></th>
                                    <th class="pb-2"><?php echo __('revenue'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="annual-top-products-body" class="divide-y divide-white/5"></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>
</main>

<!-- Start Day Modal -->
<div id="start-day-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-dark-surface rounded-xl p-8 max-w-sm w-full">
        <h2 class="text-xl font-bold text-white mb-4"><?php echo __('start_day_modal_title'); ?></h2>
        <div class="mb-4">
            <label for="opening-balance" class="block text-sm font-medium text-gray-400 mb-2"><?php echo __('opening_balance_label'); ?></label>
            <input type="text" id="opening-balance" class="w-full bg-dark border border-white/10 text-white rounded-lg px-3 py-2" placeholder="<?php echo __('enter_amount_simple'); ?>">
        </div>
        <div class="flex justify-end gap-2">
            <button id="cancel-start-day" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded"><?php echo __('cancel'); ?></button>
            <button id="confirm-start-day" class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded"><?php echo __('start_day_btn'); ?></button>
        </div>
    </div>
</div>

<!-- End Day Modal -->
<div id="end-day-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-dark-surface rounded-xl p-8 max-w-4xl w-full max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-white"><?php echo __('day_summary_title'); ?></h2>
            <button id="calculation-method-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg transition-colors" title="<?php echo __('calculation_method'); ?>">
                <span class="material-icons-round text-sm">help_outline</span>
            </button>
        </div>

        <div id="day-summary" class="text-white">
            <!-- بيانات الملخص -->
            <div id="summary-data"></div>
        </div>

        <div class="flex justify-end mt-4">
            <button id="close-summary" class="bg-primary hover:bg-primary-hover text-white font-bold py-2 px-4 rounded"><?php echo __('close'); ?></button>
        </div>
    </div>
</div>

<!-- Calculation Method Modal -->
<div id="calculation-method-modal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 hidden">
    <div class="bg-dark-surface rounded-xl p-8 max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="material-icons-round text-blue-500">calculate</span>
                <?php echo __('calculation_method_modal_title'); ?>
            </h2>
            <button id="close-calculation-method-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="space-y-3 text-sm text-gray-300">
            <div class="flex items-start gap-2">
                <span class="text-green-400 font-bold">•</span>
                <p><strong class="text-green-400"><?php echo __('total_sales'); ?>:</strong> <?php echo __('calc_total_sales_desc'); ?></p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-red-500 font-bold">•</span>
                <p><strong class="text-red-500"><?php echo __('total_refunds'); ?>:</strong> <?php echo __('calc_refunds_desc'); ?></p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-orange-400 font-bold">•</span>
                <p><strong class="text-orange-400"><?php echo __('total_delivery_fees'); ?>:</strong> <?php echo __('calc_delivery_fees_desc'); ?></p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-red-400 font-bold">•</span>
                <p><strong class="text-red-400"><?php echo __('total_cogs'); ?>:</strong> <?php echo __('calc_cogs_desc'); ?></p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-orange-500 font-bold">•</span>
                <p><strong class="text-orange-500"><?php echo __('total_expenses_all'); ?>:</strong> <?php echo __('calc_total_expenses_desc'); ?></p>
            </div>
            <div class="flex items-start gap-2">
                <span class="text-yellow-400 font-bold">•</span>
                <p><strong class="text-yellow-400"><?php echo __('expected_closing_balance'); ?>:</strong> <?php echo __('calc_expected_closing_balance_desc'); ?></p>
            </div>
            <div class="flex items-start gap-2 bg-green-500/10 p-3 rounded-lg border border-green-500/20">
                <span class="text-green-400 font-bold text-lg">></span>
                <p><strong class="text-green-400 text-lg"><?php echo __('net_profit'); ?>:</strong> <?php echo __('calc_net_profit_desc'); ?></p>
            </div>
            <div class="text-xs text-gray-400 mt-4 bg-dark/30 p-3 rounded-lg">
                <p class="flex items-center gap-2">
                    <span class="material-icons-round text-yellow-500" style="font-size: 16px;">info</span>
                    <span><?php echo __('calc_note'); ?></span>
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
        <div class="info-row"><span>${window.__('invoice_number')}:</span><span>#${String(data.id).padStart(6, '0')}</span></div>
        <div class="info-row"><span>${window.__('date_label')}:</span><span>${formattedDate}</span></div>
        <div class="info-row"><span>${window.__('time')}:</span><span>${formattedTime}</span></div>
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
        
        // Greeting Logic
        function updateGreeting() {
            const hour = new Date().getHours();
            let greetingKey = 'good_morning';
            let messageKey = 'dashboard_message_morning';
            let icon = '🌅'; 

            if (hour >= 12 && hour < 17) {
                greetingKey = 'good_afternoon';
                messageKey = 'dashboard_message_afternoon';
                icon = '☀️'; 
            } else if (hour >= 17) {
                greetingKey = 'good_evening';
                messageKey = 'dashboard_message_evening';
                icon = '🌙'; 
            }

            const greetingText = window.__(greetingKey);
            const messageText = window.__(messageKey);

            const greetingEl = document.getElementById('dynamic-greeting');
            if(greetingEl) greetingEl.innerHTML = `<span class="text-3xl md:text-4xl animate-pulse">${icon}</span> ${greetingText}`;

            const subtextEl = document.getElementById('dynamic-subtext');
            if(subtextEl) subtextEl.textContent = messageText;
        }
        updateGreeting();

        // Real Time Clock
        function updateRealTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { hour12: false });
            const dateString = now.toLocaleDateString('en-CA'); // YYYY-MM-DD
            const container = document.getElementById('real-date-time');
            if (container) {
                container.textContent = dateString + ' ' + timeString;
            }
        }
        setInterval(updateRealTime, 1000);
        updateRealTime();

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
                    const lang = document.documentElement.lang || 'ar';
                    const dir = lang === 'ar' ? 'rtl' : 'ltr';
                    const alignClass = lang === 'ar' ? 'text-right' : 'text-left';

                    let invoicesHtml = '';
                    if (summary.invoices && summary.invoices.length > 0) {
                        invoicesHtml = `
                            <hr class="border-gray-600 my-4">
                            <h3 class="text-lg font-bold text-white mb-3 ${alignClass}">${window.__('invoices_header')} (${summary.invoices.length})</h3>
                            <div class="max-h-96 overflow-y-auto space-y-2">
                        `;
                        summary.invoices.forEach(invoice => {
                            invoicesHtml += `
                                <details class="bg-gray-700 rounded-lg p-3">
                                    <summary class="cursor-pointer text-white font-medium flex justify-between items-center ${alignClass}">
                                        <span>#${invoice.id} - ${invoice.customer_name || window.__('undefined_customer')}</span>
                                        <div class="flex items-center gap-2">
                                            <button onclick="viewInvoice(${invoice.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">${window.__('view_invoice_btn')}</button>
                                            <span class="text-green-400">${formatNumber(invoice.total)} ${currency}</span>
                                        </div>
                                    </summary>
                                    <div class="mt-3 text-gray-300 text-sm space-y-1 ${alignClass}">
                                        <p><strong>${window.__('date_label')}:</strong> ${new Date(invoice.created_at).toLocaleString(document.documentElement.lang === 'ar' ? 'ar' : 'fr')}</p>
                                        <p><strong>${window.__('phone_placeholder')}:</strong> ${invoice.customer_phone || window.__('undefined_customer')}</p>
                                        <p><strong>${window.__('delivery')}:</strong> ${formatNumber(invoice.delivery_cost)} ${currency}</p>
                                        <p><strong>${window.__('product_col')}:</strong> ${invoice.items || window.__('no_products_display')}</p>
                                        ${invoice.is_holiday == 1 ? `<p class="text-yellow-500 font-bold flex items-center gap-1"><span class="material-icons-round text-sm">celebration</span> ${window.__('holiday_notification').replace(/<[^>]*>/g, '')}: ${invoice.holiday_name || window.__('official_holiday')}</p>` : ''}
                                    </div>
                                </details>
                            `;
                        });
                        invoicesHtml += '</div>';
                    }
                        document.getElementById('summary-data').innerHTML = `
                            <div class="space-y-3" dir="${dir}">
                                <p class="${alignClass}"><strong class="text-green-400">${window.__('total_sales')}:</strong> ${formatNumber(summary.total_sales)} ${currency}</p>
                                <p class="${alignClass}"><strong class="text-red-400">${window.__('total_refunds')}:</strong> -${formatNumber(summary.total_refunds)} ${currency}</p>
                                <p class="${alignClass}"><strong class="text-orange-400">${window.__('total_delivery_fees')}:</strong> ${formatNumber(summary.total_delivery)} ${currency}</p>
                                <hr class="border-gray-600 my-3">
                                <p class="${alignClass}"><strong class="text-blue-400">${window.__('opening_balance')}:</strong> ${formatNumber(summary.opening_balance)} ${currency}</p>
                                <p class="${alignClass}"><strong class="text-orange-500">${window.__('drawer_expenses')}:</strong> -${formatNumber(summary.drawer_expenses)} ${currency}</p>
                                <p class="${alignClass}"><strong class="text-yellow-400">${window.__('expected_closing_balance')}:</strong> ${formatNumber(summary.closing_balance)} ${currency}</p>
                                <hr class="border-gray-600 my-3">
                                <p class="${alignClass}"><strong class="text-red-400">${window.__('total_cogs')}:</strong> ${formatNumber(summary.total_cogs)} ${currency}</p>
                                <p class="${alignClass}"><strong class="text-orange-500">${window.__('total_expenses_all')}:</strong> ${formatNumber(summary.total_expenses)} ${currency}</p>
                                <div class="bg-green-500/10 p-3 rounded-lg mt-4 border border-green-500/20">
                                    <p class="${alignClass} text-xl font-bold text-green-400">${window.__('net_profit')}: ${formatNumber(summary.total_profit)} ${currency}</p>
                                </div>
                                <div class="bg-yellow-500/10 p-3 rounded-lg mt-2 border border-yellow-500/20">
                                    <p class="${alignClass} text-lg font-bold text-yellow-500">${window.__('holiday_sales')}: ${formatNumber(summary.holiday_sales)} ${currency} (${summary.holiday_orders} ${window.__('order_unit')})</p>
                                    ${summary.holiday_breakdown && summary.holiday_breakdown.length > 0 ? `
                                        <div class="mt-2 space-y-1 pe-4 ${lang === 'ar' ? 'border-e' : 'border-s'} border-yellow-500/30">
                                            ${summary.holiday_breakdown.map(item => `
                                                <p class="${alignClass} text-sm text-yellow-400/80">• ${item.holiday_name}: ${formatNumber(item.sales)} ${currency} (${item.orders} ${window.__('order_unit')})</p>
                                            `).join('')}
                                        </div>
                                    ` : ''}
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
                        label: '<?php echo sprintf(__('revenue_currency_label'), $currency); ?>',
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
                        label: '<?php echo __('orders_label'); ?>',
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
                            text: '<?php echo __('date_label'); ?>',
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
                            text: '<?php echo sprintf(__('revenue_currency_label'), $currency); ?>',
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
                            text: '<?php echo __('orders_count_label'); ?>',
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
                            ${window.__('end_day_btn')}
                        </button>
                    </div>`;
                document.getElementById('end-day-btn').addEventListener('click', handleEndDay);
            } else {
                businessDayControls.innerHTML = `
                    <button id="start-day-btn" class="pulse-button bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded shadow-lg">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round">play_circle</span>
                            <?php echo __('start_business_day_btn'); ?>
                        </span>
                    </button>`;
                document.getElementById('start-day-btn').addEventListener('click', () => startDayModal.classList.remove('hidden'));
            }
        }

        async function handleStartDay() {
            const opening_balance = openingBalanceInput.value;
            if (!opening_balance) {
                Swal.fire(<?php echo json_encode(__('error')); ?>, <?php echo json_encode(__('enter_opening_balance_error')); ?>, 'error');
                return;
            }

            // التحقق مما إذا كان اليوم يوم عطلة قبل البدء
            try {
                const holidayRes = await fetch('api.php?action=get_holiday_status');
                const holidayData = await holidayRes.json();
                if (holidayData.success && holidayData.is_holiday) {
                    const confirmHoliday = await Swal.fire({
                        title: <?php echo json_encode(__('holiday_warning_title')); ?>,
                        text: <?php echo json_encode(__('holiday_warning_text')); ?>.replace('%s', holidayData.holiday_name),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: <?php echo json_encode(__('confirm_start_work')); ?>,
                        cancelButtonText: <?php echo json_encode(__('undo')); ?>
                    });
                    
                    if (!confirmHoliday.isConfirmed) {
                        return; // إلغاء العملية
                    }
                }
            } catch (e) {
                console.error('Error checking holiday status:', e);
            }
            
            try {
                showLoadingOverlay();
                const response = await fetch('api.php?action=start_day', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ opening_balance })
                });
                
                const result = await response.json();
                hideLoadingOverlay();
                
                if (result.success) {
                    startDayModal.classList.add('hidden');
                    Swal.fire(<?php echo json_encode(__('success_title')); ?>, <?php echo json_encode(__('start_day_success')); ?>, 'success').then(() => {
                        location.reload();
                    });
                } else if (result.code === 'business_day_open_exists') {
                    startDayModal.classList.add('hidden');
                    const { isConfirmed } = await Swal.fire({
                        title: window.__('day_already_open_title'),
                        html: `
                            <p class="mb-4">${window.__('day_already_open_text')}</p>
                            <p>${window.__('extend_day_question_html').replace('%s', '<strong class="text-lg">' + opening_balance + ' ' + currency + '</strong>')}</p>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: window.__('confirm_extend'),
                        cancelButtonText: window.__('cancel')
                    });

                    if (isConfirmed) {
                        showLoadingOverlay();
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
                        hideLoadingOverlay();

                        if (extendResult.success) {
                            Swal.fire(window.__('extended_success_title'), window.__('extended_success_text'), 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(window.__('toast_error'), extendResult.message || window.__('extend_fail'), 'error');
                        }
                    }
                } else if (result.code === 'business_day_closed_exists') {
                    startDayModal.classList.add('hidden');
                    const { isConfirmed } = await Swal.fire({
                        title: window.__('day_closed_title'),
                        html: `
                            <p class="mb-4">${window.__('day_closed_text')}</p>
                            <p>${window.__('reopen_day_question_html').replace('%s', '<strong class="text-lg">' + opening_balance + ' ' + currency + '</strong>')}</p>
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#F59E0B',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: window.__('confirm_reopen'),
                        cancelButtonText: window.__('cancel')
                    });

                    if (isConfirmed) {
                        showLoadingOverlay();
                        const reopenResponse = await fetch('api.php?action=reopen_day', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                day_id: result.day_id,
                                opening_balance: opening_balance
                            })
                        });
                        const reopenResult = await reopenResponse.json();
                        hideLoadingOverlay();

                        if (reopenResult.success) {
                            Swal.fire(window.__('reopen_success_title'), window.__('reopen_success_text'), 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(window.__('toast_error'), reopenResult.message || window.__('reopen_fail'), 'error');
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
                hideLoadingOverlay();
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
            const lang = document.documentElement.lang || 'ar';
            const dir = lang === 'ar' ? 'rtl' : 'ltr';
            const alignClass = lang === 'ar' ? 'text-right' : 'text-left';

            const confirmed = await Swal.fire({
                title: window.__('confirm_end_day_title'),
                html: `<div class="${alignClass}" dir="${dir}">` +
                    '<p class="text-lg font-bold mb-2 text-white">' + window.__('important_warning') + '</p>' +
                    '<p class="mb-4 text-white">' + window.__('confirm_end_day_msg') + '</p>' +
                    '<p class="text-sm text-yellow-300">' + window.__('end_day_irreversible_warning') + '</p>' +
                    '</div>',
                icon: 'warning',
                iconColor: '#F59E0B',
                background: '#1F2937',
                color: '#ffffff',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#4B5563',
                confirmButtonText: window.__('yes_close_it'),
                cancelButtonText: window.__('cancel'),
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    confirmButton: 'px-6 py-2 rounded-lg font-bold',
                    cancelButton: 'px-6 py-2 rounded-lg font-bold',
                    popup: 'border border-white/10 shadow-xl rounded-2xl',
                    title: 'text-white'
                }
            });

            // If user confirms, proceed with ending the day
            if (confirmed.isConfirmed) {
                try {
                    showLoadingOverlay();
                    const response = await fetch('api.php?action=end_day', { method: 'POST' });
                    const result = await response.json();
                    hideLoadingOverlay();
                    
                    if (result.success) {
                        const summary = result.data.summary;
                        document.getElementById('summary-data').innerHTML = `
                            <div class="space-y-3">
                                <p class="text-start"><strong class="text-green-400">${window.__('total_sales')}:</strong> ${formatNumber(summary.total_sales)} ${currency}</p>
                                <p class="text-start"><strong class="text-red-400">${window.__('total_refunds')}:</strong> -${formatNumber(summary.total_refunds)} ${currency}</p>
                                <p class="text-start"><strong class="text-blue-400">${window.__('opening_balance')}:</strong> ${formatNumber(summary.opening_balance)} ${currency}</p>
                                <p class="text-start"><strong class="text-orange-500">${window.__('drawer_expenses')}:</strong> -${formatNumber(summary.drawer_expenses)} ${currency}</p>
                                <p class="text-start"><strong class="text-yellow-400">${window.__('expected_closing_balance')}:</strong> ${formatNumber(summary.closing_balance)} ${currency}</p>
                                <hr class="border-gray-600 my-3">
                                <p class="text-start"><strong class="text-red-400">${window.__('total_cogs')}:</strong> ${formatNumber(summary.total_cogs)} ${currency}</p>
                                <p class="text-start"><strong class="text-orange-500">${window.__('total_expenses_all')}:</strong> ${formatNumber(summary.total_expenses)} ${currency}</p>
                                <p class="text-start text-xl font-bold mt-4 text-green-400">${window.__('net_profit')}: ${formatNumber(summary.total_profit)} ${currency}</p>
                            </div>
                        `;
                        endDayModal.classList.remove('hidden');
                        checkBusinessDayStatus();
                        
                        // Show success message
                        Swal.fire({
                            title: window.__('action_success'),
                            text: window.__('action_success'),
                            icon: 'success',
                            confirmButtonColor: '#10b981',
                            confirmButtonText: window.__('ok')
                        });
                    } else {
                        throw new Error(result.message || window.__('action_failed'));
                    }
                } catch (error) {
                    hideLoadingOverlay();
                    console.error('Error details:', error);
                    Swal.fire({
                        title: window.__('toast_error'),
                        text: error.message || window.__('unexpected_error'),
                        icon: 'error',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: window.__('ok')
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

        async function checkHolidayStatus() {
            try {
                const response = await fetch('api.php?action=get_holiday_status');
                const result = await response.json();
                if (result.success && result.is_holiday) {
                    document.getElementById('holiday-notification').classList.remove('hidden');
                    document.getElementById('holiday-name').textContent = result.holiday_name;
                }
            } catch (error) {
                console.error('Error checking holiday status:', error);
            }
        }
        checkHolidayStatus();

        // Initialize Year Selector
        const yearSelect = document.getElementById('annual-year-select');
        
        async function loadAvailableYears() {
            try {
                const response = await fetch('api.php?action=get_available_years');
                const result = await response.json();
                
                yearSelect.innerHTML = ''; // Clear existing
                
                if (result.success && result.data.length > 0) {
                    result.data.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = `${year - 1} -> ${year}`;
                        yearSelect.appendChild(option);
                    });
                    
                    // Select first one (latest year)
                    yearSelect.selectedIndex = 0;
                } else {
                    const option = document.createElement('option');
                    option.textContent = '<?php echo __('no_data'); ?>';
                    option.disabled = true;
                    yearSelect.appendChild(option);
                }
            } catch (error) {
                console.error('Error loading years:', error);
            }
        }
        
        loadAvailableYears();

        document.getElementById('analyze-year-btn').addEventListener('click', loadAnnualAnalysis);

        // Toggle Dashboard Filters (Mobile)
        document.getElementById('toggle-dashboard-filters').addEventListener('click', function() {
            const content = document.getElementById('dashboard-filters-content');
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                content.classList.add('flex');
            } else {
                content.classList.add('hidden');
                content.classList.remove('flex');
            }
        });
    });

    // Tab Switching Logic
    function switchTab(tabName) {
        // Update Buttons
        document.querySelectorAll('button[id^="tab-btn-"]').forEach(btn => {
            if (btn.id === 'tab-btn-' + tabName) {
                btn.classList.remove('bg-transparent', 'text-gray-400', 'hover:bg-white/5', 'hover:text-white');
                btn.classList.add('bg-primary', 'text-white');
            } else {
                btn.classList.add('bg-transparent', 'text-gray-400', 'hover:bg-white/5', 'hover:text-white');
                btn.classList.remove('bg-primary', 'text-white');
            }
        });

        // Update Content
        document.querySelectorAll('.tab-content').forEach(content => {
            if (content.id === 'tab-' + tabName) {
                content.classList.remove('hidden');
                content.classList.add('block');
            } else {
                content.classList.add('hidden');
                content.classList.remove('block');
            }
        });

        // Toggle Header Controls
        const dashControls = document.getElementById('header-controls-dashboard');
        if (tabName === 'dashboard') {
            dashControls.classList.remove('hidden');
            dashControls.classList.add('flex');
        } else {
            dashControls.classList.add('hidden');
            dashControls.classList.remove('flex');
        }
    }

    async function loadAnnualAnalysis() {
        const year = document.getElementById('annual-year-select').value;
        const resultsDiv = document.getElementById('annual-results');
        const loadingDiv = document.getElementById('annual-loading');
        
        resultsDiv.classList.add('hidden');
        loadingDiv.classList.remove('hidden');

        try {
            const response = await fetch(`api.php?action=get_annual_tips&year=${year}`);
            const result = await response.json();

            loadingDiv.classList.add('hidden');

            if (result.success) {
                renderAnnualAnalysis(result.data);
                resultsDiv.classList.remove('hidden');
            } else {
                Swal.fire({
                    title: 'خطأ',
                    text: result.message,
                    icon: 'error'
                });
            }
        } catch (error) {
            loadingDiv.classList.add('hidden');
            console.error('Analysis error:', error);
            Swal.fire('خطأ', 'حدث خطأ في الاتصال', 'error');
        }
    }

    function renderAnnualAnalysis(data) {
        const stats = data.stats;
        const growth = data.growth;
        const advice = data.advice;
        
        // 1. Score Animation
        const score = Math.round(data.score);
        document.getElementById('score-value').textContent = score;
        
        const circle = document.getElementById('score-circle');
        const radius = 70;
        const circumference = 2 * Math.PI * radius; // ~439.8
        const offset = circumference - (score / 100) * circumference;
        
        // Reset animation
        circle.style.strokeDasharray = `${circumference} ${circumference}`;
        circle.style.strokeDashoffset = circumference;
        
        // Color based on score
        let color = '#EF4444'; // Red
        let verdict = 'ضعيف';
        if (score >= 80) { color = '#10B981'; verdict = 'ممتاز 🌟'; } // Green
        else if (score >= 60) { color = '#3B82F6'; verdict = 'جيد جداً ✅'; } // Blue
        else if (score >= 40) { color = '#F59E0B'; verdict = 'متوسط ⚠️'; } // Yellow
        
        circle.style.stroke = color;
        document.getElementById('score-verdict').textContent = verdict;
        document.getElementById('score-verdict').style.color = color;

        // Trigger animation
        setTimeout(() => {
            circle.style.strokeDashoffset = offset;
        }, 100);

        // 2. Metrics
        document.getElementById('annual-revenue').innerHTML = formatNumber(stats.total_revenue) + ' <span class="text-sm font-normal text-gray-500">' + currency + '</span>';
        document.getElementById('annual-profit').innerHTML = formatNumber(stats.net_profit) + ' <span class="text-sm font-normal text-gray-500">' + currency + '</span>';
        document.getElementById('annual-margin').textContent = stats.profit_margin.toFixed(1) + '%';
        
        // Margin Bar
        const marginBar = document.getElementById('margin-bar');
        marginBar.style.width = Math.min(stats.profit_margin, 100) + '%';
        marginBar.className = 'h-full transition-all duration-1000 ' + (stats.profit_margin < 10 ? 'bg-red-500' : (stats.profit_margin < 25 ? 'bg-yellow-500' : 'bg-green-500'));

        // Growth Badges
        const renderGrowth = (elementId, value) => {
            const el = document.getElementById(elementId);
            if (value > 0) {
                el.textContent = '+' + value.toFixed(1) + '%';
                el.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-500/20 text-green-400';
            } else if (value < 0) {
                el.textContent = value.toFixed(1) + '%';
                el.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-500/20 text-red-400';
            } else {
                el.textContent = '0%';
                el.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-500/20 text-gray-400';
            }
        };
        renderGrowth('revenue-growth', growth.revenue_growth);
        renderGrowth('profit-growth', growth.profit_growth);

        // 3. Seasonality
        const getMonthName = (m) => {
            const months = ['-', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
            return months[m] || '-';
        };
        
        document.getElementById('best-month-name').textContent = getMonthName(data.monthly.best_month.month);
        document.getElementById('best-month-val').textContent = formatNumber(data.monthly.best_month.revenue) + ' ' + currency;
        
        document.getElementById('worst-month-name').textContent = getMonthName(data.monthly.worst_month.month);
        document.getElementById('worst-month-val').textContent = formatNumber(data.monthly.worst_month.revenue) + ' ' + currency;

        // 4. Advice
        const adviceContainer = document.getElementById('advice-container');
        adviceContainer.innerHTML = '';
        
        if (advice.length === 0) {
            adviceContainer.innerHTML = '<div class="text-center text-gray-500 py-4">لا توجد نصائح محددة لهذا العام.</div>';
        } else {
            advice.forEach(tip => {
                let colorClass = 'bg-blue-500/10 border-blue-500/20 text-blue-200';
                let icon = 'info';
                
                if (tip.type === 'success') {
                    colorClass = 'bg-green-500/10 border-green-500/20 text-green-200';
                    icon = 'check_circle';
                } else if (tip.type === 'warning') {
                    colorClass = 'bg-yellow-500/10 border-yellow-500/20 text-yellow-200';
                    icon = 'warning';
                } else if (tip.type === 'danger') {
                    colorClass = 'bg-red-500/10 border-red-500/20 text-red-200';
                    icon = 'error';
                }

                const div = document.createElement('div');
                div.className = `p-4 rounded-xl border ${colorClass} flex gap-4 items-start`;
                div.innerHTML = `
                    <span class="material-icons-round mt-0.5">${icon}</span>
                    <div>
                        <h5 class="font-bold mb-1 text-sm opacity-90">${tip.title}</h5>
                        <p class="text-xs leading-relaxed opacity-80">${tip.text}</p>
                    </div>
                `;
                adviceContainer.appendChild(div);
            });
        }

        // 5. Top Products
        const tbody = document.getElementById('annual-top-products-body');
        tbody.innerHTML = '';
        data.top_products.forEach(p => {
            const tr = document.createElement('tr');
            
            // Safe rendering to prevent XSS
            const tdName = document.createElement('td');
            tdName.className = 'py-3 text-white';
            tdName.textContent = p.name;
            
            const tdQty = document.createElement('td');
            tdQty.className = 'py-3 text-gray-400 font-mono';
            tdQty.textContent = p.qty;
            
            const tdRev = document.createElement('td');
            tdRev.className = 'py-3 text-primary font-bold font-mono';
            tdRev.innerHTML = formatNumber(p.revenue) + ' <span class="text-[10px] text-gray-500">' + currency + '</span>'; // currency is safe (from PHP), formatNumber is safe
            
            tr.appendChild(tdName);
            tr.appendChild(tdQty);
            tr.appendChild(tdRev);
            
            tbody.appendChild(tr);
        });
    }
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
                    <?php echo __('welcome_modal_title'); ?>
                </h2>
                <p class="text-gray-300 text-lg animate-slideUp animation-delay-200">
                    <?php echo __('welcome_modal_subtitle'); ?>
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
                            <h3 class="text-xl font-bold text-primary mb-3"><?php echo __('welcome_intro_title'); ?></h3>
                            <p class="text-gray-300 leading-relaxed">
                                <?php echo __('welcome_intro_text'); ?>
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
                            <h3 class="text-xl font-bold text-accent mb-4"><?php echo __('welcome_features_title'); ?></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">inventory_2</span>
                                    <span class="text-gray-300"><?php echo __('welcome_feature_1'); ?></span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">people</span>
                                    <span class="text-gray-300"><?php echo __('welcome_feature_2'); ?></span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">receipt_long</span>
                                    <span class="text-gray-300"><?php echo __('welcome_feature_3'); ?></span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">analytics</span>
                                    <span class="text-gray-300"><?php echo __('welcome_feature_4'); ?></span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">point_of_sale</span>
                                    <span class="text-gray-300"><?php echo __('welcome_feature_5'); ?></span>
                                </div>
                                <div class="flex items-center gap-3 p-3 bg-dark/30 rounded-lg hover:bg-dark/50 transition-colors">
                                    <span class="material-icons-round text-accent text-lg">settings</span>
                                    <span class="text-gray-300"><?php echo __('welcome_feature_6'); ?></span>
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
                            <h3 class="text-xl font-bold text-yellow-400 mb-3"><?php echo __('welcome_step3_title'); ?></h3>
                            <p class="text-gray-300 leading-relaxed mb-4">
                                <?php echo __('welcome_step3_text'); ?>
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    <?php echo __('welcome_step3_item_1'); ?>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    <?php echo __('welcome_step3_item_2'); ?>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    <?php echo __('welcome_step3_item_3'); ?>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    <?php echo __('welcome_step3_item_4'); ?>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-300">
                                    <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                    <?php echo __('welcome_step3_item_5'); ?>
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
                            <h3 class="text-xl font-bold text-green-400 mb-3"><?php echo __('welcome_custom_title'); ?></h3>
                            <p class="text-gray-300 leading-relaxed mb-4">
                                <?php echo __('welcome_custom_text'); ?>
                            </p>
                            <div class="bg-gradient-to-r from-dark-surface/80 to-dark/50 border border-white/10 rounded-xl p-4">
                                <h4 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                                    <span class="material-icons-round text-green-400">contact_support</span>
                                    <?php echo __('welcome_contact_title'); ?>
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <p class="text-gray-300">
                                        <?php echo __('contact_us'); ?>:
                                        <a href="https://eagleshadow.technology" class="text-primary hover:text-primary-hover underline transition-colors">eagleshadow.technology</a>
                                    </p>
                                    <p class="text-gray-300">
                                        <?php echo __('email'); ?>:
                                        <a href="mailto:support@eagleshadow.technology" class="text-primary hover:text-primary-hover underline transition-colors">support@eagleshadow.technology</a>
                                    </p>
                                    <p class="text-gray-300">
                                        <?php echo __('whatsapp'); ?>:
                                        <a href="https://wa.me/212700979284?text=مرحباً، انا قادم من نظام سمارتشوب واريد تخصيص بعض المميزات لأجل متجري فقط ...." target="_blank" class="text-primary hover:text-primary-hover underline transition-colors">0700979284</a>
                                    </p>
                                    <p class="text-gray-300">
                                        <?php echo __('contact_link'); ?>:
                                        <a href="contact.php" class="text-primary hover:text-primary-hover underline transition-colors"><?php echo __('contact_link'); ?></a>
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
                            <h3 class="text-xl font-bold text-blue-400 mb-3"><?php echo __('welcome_setup_title'); ?></h3>
                            <p class="text-gray-300 leading-relaxed mb-4">
                                <?php echo __('welcome_setup_text'); ?>
                                <span class="text-yellow-400 text-sm font-bold block mt-1"><?php echo __('welcome_setup_note'); ?></span>
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <button id="device-touch" class="flex-1 bg-gradient-to-r from-accent to-accent-hover hover:from-accent-hover hover:to-accent text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-accent/30 flex items-center justify-center gap-3">
                                    <span class="material-icons-round">touch_app</span>
                                    <span><?php echo __('device_touch_btn'); ?></span>
                                </button>
                                <button id="device-desktop" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-gray-600/30 flex items-center justify-center gap-3">
                                    <span class="material-icons-round">computer</span>
                                    <span><?php echo __('device_desktop_btn'); ?></span>
                                </button>
                            </div>
                            <p id="device-feedback" class="text-sm text-center mt-3 text-gray-400 opacity-0 transition-opacity"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 mt-8 pt-6 border-t border-white/10">
                <button id="welcome-close" class="flex-1 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-gray-600/30 flex items-center justify-center gap-2">
                    <span class="material-icons-round">check_circle</span>
                    <?php echo __('welcome_later_btn'); ?>
                </button>
                <a href="settings.php" class="flex-1 bg-gradient-to-r from-primary to-primary-hover hover:from-primary-hover hover:to-primary text-white font-semibold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg hover:shadow-primary/30 text-center flex items-center justify-center gap-2">
                    <span class="material-icons-round">settings</span>
                    <?php echo __('welcome_settings_btn'); ?>
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
        showLoadingOverlay();
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
            hideLoadingOverlay();
            if (data.success) {
                deviceFeedback.textContent = window.__('settings_saved_feedback');
                deviceFeedback.classList.remove('text-red-400');
                deviceFeedback.classList.add('text-green-400');
                deviceFeedback.style.opacity = '1';
                // Disable buttons
                deviceTouchBtn.disabled = true;
                deviceDesktopBtn.disabled = true;
                deviceTouchBtn.classList.add('opacity-50', 'cursor-not-allowed');
                deviceDesktopBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                deviceFeedback.textContent = window.__('settings_save_fail');
                deviceFeedback.classList.remove('text-green-400');
                deviceFeedback.classList.add('text-red-400');
                deviceFeedback.style.opacity = '1';
            }
        })
        .catch(error => {
            hideLoadingOverlay();
            console.error('Error:', error);
            deviceFeedback.textContent = window.__('error_occurred');
            deviceFeedback.classList.remove('text-green-400');
            deviceFeedback.classList.add('text-red-400');
            deviceFeedback.style.opacity = '1';
        });
    }
});
</script>
<?php endif; ?>

<div id="loading-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl p-12 border border-white/10 flex flex-col items-center gap-6">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-transparent border-t-primary border-r-primary rounded-full animate-spin"></div>
            <div class="absolute inset-2 border-4 border-transparent border-b-primary/50 rounded-full animate-spin" style="animation-direction: reverse;"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-white mb-2"><?php echo __('loading'); ?></h3>
            <p id="loading-message" class="text-sm text-gray-400"><?php echo __('please_wait'); ?></p>
        </div>
    </div>
</div>

<script>
    // دوال إدارة شاشة التحميل
    function showLoadingOverlay(message = '<?php echo __('processing'); ?>') {
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
    }

    function hideLoadingOverlay() {
        const loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.classList.add('hidden');
    }
</script>

<?php require_once 'src/footer.php'; ?>