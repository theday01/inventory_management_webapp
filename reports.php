<?php
$page_title = 'التقارير والتحليلات';
$current_page = 'reports.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch Currency
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

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
$gross_profit = $total_revenue - $total_cogs; 
$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;
$profit_margin = $total_revenue > 0 ? ($gross_profit / $total_revenue) * 100 : 0;

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

// 6. Slowest Selling Day
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

?>

<style>
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

        /* Full width for all cards in print */
        .glass-card {
            width: 100% !important;
            max-width: none !important;
            margin-bottom: 0 !important;
            border: 1px solid #eee !important;
            box-shadow: none !important;
        }

        /* Page Break Utility */
        .print-break-page {
            break-after: page !important;
            page-break-after: always !important;
            margin-bottom: 0 !important;
        }

        /* Add some spacing for the content on new pages */
        .print-break-page + .glass-card, 
        .print-break-page + div > .glass-card {
            margin-top: 20px !important;
        }

        /* Chart Heights - Adjust for full page/better visibility */
        canvas {
            max-height: 400px !important; /* Taller charts for print */
        }

        /* Header */
        .print-only-header {
            display: block !important;
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
    }

    .print-only-header { display: none; }
</style>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark transition-all duration-300">
    
    <div class="print-only-header">
        <h1 style="font-size: 24px; font-weight: bold;">تقرير المبيعات والتحليلات</h1>
        <p style="font-size: 14px;">الفترة: <?php echo $start_date; ?> إلى <?php echo $end_date; ?></p>
        <p style="font-size: 14px;">تاريخ الاستخراج: <?php echo date('Y-m-d H:i'); ?></p>
    </div>

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
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="bg-dark border border-white/10 text-white text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-primary">
                    <span class="text-gray-500">-</span>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="bg-dark border border-white/10 text-white text-xs rounded-lg px-2 py-1.5 focus:outline-none focus:border-primary">
                    <button type="submit" name="range" value="custom" class="bg-primary hover:bg-primary-hover text-white p-1.5 rounded-lg transition-colors shadow-md">
                        <span class="material-icons-round text-sm block">filter_alt</span>
                    </button>
                </div>
                
                <div class="h-8 w-px bg-white/10"></div>
                
                <button type="button" onclick="window.print()" class="bg-white/5 hover:bg-white/10 text-white p-2 rounded-lg transition-colors border border-white/5" title="طباعة التقرير">
                    <span class="material-icons-round">print</span>
                </button>
            </form>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-6 scroll-smooth">
        
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
                    <div class="text-xs text-gray-400">التكلفة: <span class="text-white"><?php echo number_format($total_cogs, 2); ?></span></div>
                    <div class="text-xs text-green-400 bg-green-500/10 px-2 py-1 rounded-full border border-green-500/10">هامش: <?php echo number_format($profit_margin, 1); ?>%</div>
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

        <div id="tables-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
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

            <div class="glass-card p-6 lg:col-span-2">
                <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-teal-500">payment</span>
                    طرق الدفع
                </h3>
                <div class="space-y-4">
                    <?php 
                    if ($payment_result->num_rows > 0) {
                        while($pay = $payment_result->fetch_assoc()) {
                            $methodName = $pay['payment_method'] === 'cash' ? 'نقد' : ($pay['payment_method'] === 'card' ? 'بطاقة' : $pay['payment_method']);
                            $icon = $pay['payment_method'] === 'cash' ? 'payments' : 'credit_card';
                            $color = $pay['payment_method'] === 'cash' ? 'text-green-500' : 'text-blue-500';
                            $bg = $pay['payment_method'] === 'cash' ? 'bg-green-500/10' : 'bg-blue-500/10';
                    ?>
                    <div class="flex items-center justify-between p-4 rounded-xl border border-white/5 bg-white/5 hover:bg-white/10 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full <?php echo $bg; ?> flex items-center justify-center">
                                <span class="material-icons-round <?php echo $color; ?>"><?php echo $icon; ?></span>
                            </div>
                            <div>
                                <p class="font-bold text-white"><?php echo htmlspecialchars($methodName); ?></p>
                                <p class="text-xs text-gray-400"><?php echo $pay['count']; ?> عملية</p>
                            </div>
                        </div>
                        <div class="text-left">
                            <p class="font-bold text-white text-lg"><?php echo number_format($pay['total'], 2); ?> <span class="text-xs text-gray-500"><?php echo $currency; ?></span></p>
                        </div>
                    </div>
                    <?php 
                        }
                    } else {
                        echo '<p class="text-center text-gray-500 py-4">لا توجد عمليات دفع</p>';
                    }
                    ?>
                </div>
                
                <div class="mt-6 pt-6 border-t border-white/10 page-break-avoid">
                    <h4 class="text-sm font-bold text-gray-400 mb-3">ملخص سريع</h4>
                    <div class="grid grid-cols-3 gap-4">
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
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- Main Sales Chart ---
        const ctxMain = document.getElementById('mainChart').getContext('2d');
        const gradientRevenue = ctxMain.createLinearGradient(0, 0, 0, 300);
        gradientRevenue.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
        gradientRevenue.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        const gradientOrders = ctxMain.createLinearGradient(0, 0, 0, 300);
        gradientOrders.addColorStop(0, 'rgba(16, 185, 129, 0.5)');
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
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'الطلبات',
                        data: <?php echo json_encode($chart_orders); ?>,
                        borderColor: '#10B981',
                        backgroundColor: gradientOrders,
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        labels: { 
                            // Color is set to gray for screen, printing CSS handles contrast usually, 
                            // but ensuring it's dark enough for both is good practice.
                            color: '#9CA3AF', 
                            font: { family: 'Tajawal' } 
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        titleFont: { family: 'Tajawal' },
                        bodyFont: { family: 'Tajawal' }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9CA3AF', font: { family: 'Tajawal' } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: '#9CA3AF' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { display: false },
                        ticks: { color: '#10B981' }
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
                        '#3B82F6', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6', '#6366F1'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: '#9CA3AF', font: { family: 'Tajawal', size: 11 }, padding: 15 }
                    }
                },
                cutout: '70%'
            }
        });
        
        window.addEventListener('beforeprint', () => {
            mainChart.resize();
        });
    });
</script>

<?php require_once 'src/footer.php'; ?>