<?php
$page_title = 'لوحة التحكم';
$current_page = 'dashboard.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

// جلب إعدادات يوم العمل التلقائي
$auto_day_management = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'auto_day_management'")->fetch_assoc()['setting_value'] ?? '0';
$auto_open_time = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'auto_open_time'")->fetch_assoc()['setting_value'] ?? '09:00';
$auto_close_time = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'auto_close_time'")->fetch_assoc()['setting_value'] ?? '18:00';
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
.chart-container {
    position: relative;
    height: 350px;
    width: 100%;
}
.mini-chart {
    height: 50px;
    width: 100%;
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
.delay-200 { animation-delay: 200ms; }
.delay-300 { animation-delay: 300ms; }
.gradient-text {
    background: linear-gradient(135deg, #3B82F6 0%, #84CC16 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.glass-panel-hover:hover {
    background: rgba(31, 41, 55, 0.8);
    border-color: rgba(255, 255, 255, 0.1);
}
.action-btn {
    transition: all 0.2s;
}
.action-btn:active {
    transform: scale(0.95);
}
</style>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <!-- Background Blobs -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary/10 rounded-full blur-[120px] pointer-events-none animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-accent/5 rounded-full blur-[120px] pointer-events-none animate-pulse" style="animation-delay: 2s;"></div>

    <!-- Business Day Status Bar -->
    <div id="business-day-bar" class="bg-dark-surface/90 backdrop-blur-xl border-b border-white/5 py-2 px-6 relative z-20 shrink-0 shadow-lg">
        <div class="flex items-center justify-between max-w-[1920px] mx-auto w-full">
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2 px-3 py-1 bg-white/5 rounded-lg border border-white/5">
                    <span class="material-icons-round text-lg" id="day-status-icon">schedule</span>
                    <span class="text-sm font-bold" id="day-status-text">جاري التحميل...</span>
                </div>
                <div class="text-sm text-gray-400 font-medium" id="day-date-text"></div>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-6 px-4 py-1.5 bg-dark/50 rounded-lg border border-white/5" id="day-metrics" style="display: none;">
                    <span class="text-sm text-gray-300">المبيعات: <span class="text-primary font-bold text-base mr-1" id="current-day-sales">0</span></span>
                    <div class="w-px h-4 bg-white/10"></div>
                    <span class="text-sm text-gray-300">الفواتير: <span class="text-accent font-bold text-base mr-1" id="current-day-invoices">0</span></span>
                </div>
                <button id="open-day-btn" class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-500 hover:to-green-400 text-white px-5 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-all shadow-lg shadow-green-500/20" style="display: none;">
                    <span class="material-icons-round text-lg">lock_open</span>
                    فتح يوم العمل
                </button>
                <button id="close-day-btn" class="bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white px-5 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-all shadow-lg shadow-red-500/20" style="display: none;">
                    <span class="material-icons-round text-lg">lock</span>
                    إغلاق يوم العمل
                </button>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <div class="flex items-center gap-4">
            <div class="p-2 bg-primary/10 rounded-xl">
                <span class="material-icons-round text-primary text-xl">dashboard</span>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">لوحة التحكم</h2>
                <span class="text-xs text-gray-400 block mt-0.5" id="current-date"></span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="flex bg-dark/50 p-1 rounded-xl border border-white/10">
                <select id="dashboard-period" class="bg-transparent text-white text-sm px-4 py-1.5 rounded-lg focus:outline-none cursor-pointer border-none font-medium">
                    <option value="7">آخر 7 أيام</option>
                    <option value="30" selected>آخر 30 يوم</option>
                    <option value="90">آخر 90 يوم</option>
                </select>
            </div>
        </div>
    </header>

    <!-- Content Scrollable -->
    <div class="flex-1 overflow-y-auto p-8 relative z-10 scroll-smooth" style="max-height: calc(100vh - 8rem);">
        <div class="max-w-[1920px] mx-auto space-y-8 pb-12">
            
            <!-- Welcome & Quick Actions Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-4">
                <!-- Welcome Banner -->
                <div class="lg:col-span-2 bg-gradient-to-br from-dark-surface/80 to-dark-surface/40 backdrop-blur-xl border border-white/10 rounded-3xl p-8 relative overflow-hidden animate-enter">
                    <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-l from-primary/5 to-transparent pointer-events-none"></div>
                    <div class="relative z-10">
                        <h1 class="text-4xl font-bold text-white mb-3 leading-tight">
                            مرحباً بك في <span class="gradient-text"><?php echo htmlspecialchars($shopName); ?></span> 👋
                        </h1>
                        <p class="text-gray-400 text-lg max-w-2xl">إليك نظرة عامة على أداء متجرك. لديك <span class="text-white font-bold" id="today-orders-count-banner">0</span> طلبات جديدة اليوم بقيمة إجمالية <span class="text-primary font-bold" id="today-revenue-banner">0</span>.</p>
                        
                        <div class="mt-8 flex gap-4">
                            <a href="pos.php" class="action-btn group bg-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-primary/25 flex items-center gap-3 hover:-translate-y-1 transition-all">
                                <span class="material-icons-round group-hover:rotate-90 transition-transform">add</span>
                                بيع جديد
                            </a>
                            <a href="products.php" class="action-btn group bg-white/5 hover:bg-white/10 text-white px-6 py-3 rounded-xl font-bold border border-white/10 flex items-center gap-3 hover:-translate-y-1 transition-all">
                                <span class="material-icons-round text-accent">inventory_2</span>
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

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 animate-enter delay-200">
                <!-- Today's Revenue -->
                <div class="stat-card bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-gray-400 text-sm font-medium">مبيعات اليوم</div>
                        <div class="flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-white/5" id="revenue-change">
                            <span class="material-icons-round text-sm">trending_up</span>
                            <span>--%</span>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-white mb-4" id="today-revenue">0</div>
                    <div class="h-10 relative overflow-hidden rounded-lg">
                        <canvas id="revenue-mini-chart"></canvas>
                    </div>
                </div>

                <!-- Today's Orders -->
                <div class="stat-card bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-accent/10 rounded-xl">
                            <span class="material-icons-round text-accent text-2xl">shopping_cart</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <span class="font-bold text-white" id="today-orders">0</span> طلب
                        </div>
                    </div>
                    <div class="text-sm text-gray-400 mb-1">متوسط قيمة الطلب</div>
                    <div class="text-2xl font-bold text-white" id="avg-order-value">0</div>
                    <div class="w-full bg-white/5 rounded-full h-1.5 mt-4">
                        <div class="bg-accent h-1.5 rounded-full" style="width: 65%"></div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="stat-card bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 cursor-pointer hover:bg-dark-surface/80" onclick="window.location.href='products.php?stock_status=low_stock'">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-orange-500/10 rounded-xl">
                            <span class="material-icons-round text-orange-500 text-2xl">inventory_2</span>
                        </div>
                        <div class="bg-orange-500/20 text-orange-500 px-3 py-1 rounded-full text-xs font-bold animate-pulse">تنبيه</div>
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <div class="text-3xl font-bold text-white mb-1" id="low-stock-count">0</div>
                            <div class="text-sm text-gray-400">منتجات منخفضة</div>
                        </div>
                        <div class="text-right">
                             <div class="text-xl font-bold text-red-500" id="out-of-stock-count">0</div>
                             <div class="text-xs text-gray-500">نفذت الكمية</div>
                        </div>
                    </div>
                </div>

                <!-- Daily Profit -->
                <div class="stat-card bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-green-500/10 rounded-xl">
                            <span class="material-icons-round text-green-500 text-2xl">savings</span>
                        </div>
                        <span id="profit-status" class="text-xs font-bold px-2 py-1 rounded-full bg-green-500/20 text-green-500">ربح</span>
                    </div>
                    <div class="text-3xl font-bold text-white mb-1" id="daily-profit">0</div>
                    <div class="text-sm text-gray-400">صافي الربح اليومي</div>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 border-t border-white/5 pt-3">
                         <span>الهامش: <span class="text-white font-bold" id="profit-margin">0%</span></span>
                         <span>التكلفة: <span class="text-white font-bold" id="daily-cost">0</span></span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-enter delay-300">
                <!-- Sales Trend Chart -->
                <div class="lg:col-span-2 bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl p-6 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-white">تحليل المبيعات</h3>
                            <p class="text-sm text-gray-400 mt-1">مقارنة الأداء خلال الفترة المحددة</p>
                        </div>
                        <div class="flex bg-dark/50 p-1 rounded-xl border border-white/5">
                            <button class="chart-type-btn active" data-type="revenue">الإيرادات</button>
                            <button class="chart-type-btn" data-type="orders">الطلبات</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="sales-chart"></canvas>
                    </div>
                </div>

                <!-- Category Sales -->
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl p-6 flex flex-col">
                    <h3 class="text-lg font-bold text-white mb-2">أداء الفئات</h3>
                    <p class="text-sm text-gray-400 mb-6">توزيع المبيعات حسب التصنيف</p>
                    <div class="chart-container flex-1">
                        <canvas id="category-chart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-enter delay-300">
                <!-- Top Products Table -->
                <div class="lg:col-span-2 bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                             <span class="material-icons-round text-amber-500">star</span>
                             الأكثر مبيعاً
                        </h3>
                        <a href="products.php" class="text-sm text-primary hover:text-white transition-colors">عرض الكل</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-right border-b border-white/5">
                                    <th class="pb-4 text-xs font-medium text-gray-500 uppercase tracking-wider">المنتج</th>
                                    <th class="pb-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">الوحدات</th>
                                    <th class="pb-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">الإيراد</th>
                                    <th class="pb-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">الحالة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5" id="top-products-table">
                                <tr><td colspan="4" class="text-center py-8 text-gray-500">جاري التحميل...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Lists (Recent Invoices & Top Customers) -->
                <div class="space-y-6">
                    <!-- Recent Invoices -->
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-white">آخر الفواتير</h3>
                            <a href="invoices.php" class="text-sm text-primary hover:text-white transition-colors">عرض الكل</a>
                        </div>
                        <div class="space-y-4" id="recent-invoices-list">
                            <div class="text-center py-8 text-gray-500">جاري التحميل...</div>
                        </div>
                    </div>

                    <!-- Top Customers -->
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-3xl p-6">
                         <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-white">كبار العملاء</h3>
                            <a href="customers.php" class="text-sm text-primary hover:text-white transition-colors">عرض الكل</a>
                        </div>
                        <div class="space-y-4" id="top-customers-list">
                            <div class="text-center py-8 text-gray-500">جاري التحميل...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.chart-type-btn {
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #9CA3AF;
    background-color: transparent;
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.2s;
    cursor: pointer;
}
.chart-type-btn:hover {
    color: #FFFFFF;
    background-color: rgba(255, 255, 255, 0.05);
}
.chart-type-btn.active {
    color: #3B82F6;
    background-color: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function toEnglishNumbers(str) {
        const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        let result = str.toString();
        for (let i = 0; i < 10; i++) {
            result = result.replace(new RegExp(arabicNumbers[i], 'g'), englishNumbers[i]);
        }
        return result;
    }
    const currency = '<?php echo $currency; ?>';
    let currentPeriod = 30;
    let salesChart, categoryChart, revenueMiniChart;
    let salesChartData = { revenue: [], orders: [] };
    
    // Display current date
    // Display current date - Dual Calendar
    function formatDualDate(date) {
        const gregorianDate = date.toLocaleDateString('ar-SA', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const hijriDate = date.toLocaleDateString('ar-SA-u-ca-islamic', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // تحويل الأرقام من عربي إلى إنجليزي
        const englishNums = gregorianDate.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
        const hijriEnglishNums = hijriDate.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
        
        return `${englishNums} الموافق لـ ${hijriEnglishNums}`;
    }

    document.getElementById('current-date').textContent = formatDualDate(new Date());    
    // Period selector
    document.getElementById('dashboard-period').addEventListener('change', function() {
        currentPeriod = parseInt(this.value);
        loadAllData();
    });
    
    // Chart type buttons
    document.querySelectorAll('.chart-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            updateSalesChart(this.dataset.type);
        });
    });
    
    // Business Day Buttons
    document.getElementById('open-day-btn').addEventListener('click', openDay);
    document.getElementById('close-day-btn').addEventListener('click', closeDay);
    
    async function loadAllData() {
        showLoading('جاري تحميل البيانات...');
        try {
            await Promise.all([
                loadCurrentDayStatus(),
                loadDashboardStats(),
                loadSalesChart(),
                loadTopProducts(),
                loadCategorySales(),
                loadRecentInvoices(),
                loadTopCustomers()
            ]);
        } catch (error) {
            console.error('Error loading dashboard:', error);
            showToast('حدث خطأ في تحميل البيانات', false);
        } finally {
            hideLoading();
        }
    }
    
    async function loadDashboardStats() {
        const response = await fetch('api.php?action=getDashboardStats');
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            document.getElementById('today-revenue').textContent = toEnglishNumbers(formatNumber(stats.todayRevenue)) + ' ' + currency;
            const revenueChange = stats.yesterdayRevenue > 0 ? ((stats.todayRevenue - stats.yesterdayRevenue) / stats.yesterdayRevenue * 100) : 0;
            updateChangeIndicator('revenue-change', revenueChange);
            
            // Update Banner
            const bannerOrders = document.getElementById('today-orders-count-banner');
            const bannerRevenue = document.getElementById('today-revenue-banner');
            if(bannerOrders) bannerOrders.textContent = toEnglishNumbers(stats.todayOrders.toString());
            if(bannerRevenue) bannerRevenue.textContent = toEnglishNumbers(formatNumber(stats.todayRevenue)) + ' ' + currency;

            // Today's Orders
            document.getElementById('today-orders').textContent = toEnglishNumbers(stats.todayOrders.toString());
            if(document.getElementById('avg-order-value')) {
                document.getElementById('avg-order-value').textContent = toEnglishNumbers(formatNumber(stats.avgOrderValue)) + ' ' + currency;
            }
            
            // Stock Alerts
            document.getElementById('low-stock-count').textContent = toEnglishNumbers(stats.lowStock.toString());
            document.getElementById('out-of-stock-count').textContent = toEnglishNumbers(stats.outOfStock.toString());            
            
            // Update Daily Profit Card
            const dailyProfitEl = document.getElementById('daily-profit');
            const dailyCostEl = document.getElementById('daily-cost');
            const profitMarginEl = document.getElementById('profit-margin');
            
            if(dailyProfitEl) dailyProfitEl.textContent = toEnglishNumbers(formatNumber(stats.todayProfit || 0)) + ' ' + currency;
            if(dailyCostEl) dailyCostEl.textContent = toEnglishNumbers(formatNumber(stats.todayCost || 0));
            if(profitMarginEl) profitMarginEl.textContent = toEnglishNumbers(formatNumber(stats.todayMargin || 0)) + '%';
            
            // Update Profit Status Badge
            const statusBadge = document.getElementById('profit-status');
            if(statusBadge) {
                const isProfit = (stats.todayProfit || 0) >= 0;
                statusBadge.textContent = isProfit ? 'ربح' : 'خسارة';
                statusBadge.className = `text-xs font-bold px-2 py-1 rounded-full ${isProfit ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500'}`;
            }
        }
    }
    
    async function loadSalesChart() {
        const response = await fetch(`api.php?action=getSalesChart&days=${currentPeriod}`);
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            
            // Store data for switching between views
            salesChartData.revenue = data.map(d => parseFloat(d.revenue));
            salesChartData.orders = data.map(d => parseInt(d.orders));
            const labels = data.map(d => {
                const date = new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                return date;
            });            
            if (salesChart) {
                salesChart.destroy();
            }
            
            const ctx = document.getElementById('sales-chart').getContext('2d');
            
            // Gradient Generation
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // Start color
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)'); // End color

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'الإيرادات',
                        data: salesChartData.revenue,
                        borderColor: '#3B82F6',
                        backgroundColor: gradient,
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#1F2937',
                        pointBorderColor: '#3B82F6',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(31, 41, 55, 0.9)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            titleFont: { family: 'Tajawal', size: 13 },
                            bodyFont: { family: 'Tajawal', size: 13 },
                            callbacks: {
                                label: (context) => formatNumber(context.parsed.y) + ' ' + currency
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { 
                                color: '#9CA3AF',
                                font: { family: 'Tajawal' },
                                callback: function(value) { return formatNumber(value); } 
                            },
                            border: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#9CA3AF', font: { family: 'Tajawal' } },
                            border: { display: false }
                        }
                    }
                }
            });
            
            // Mini chart for revenue card
            createRevenueMiniChart(salesChartData.revenue.slice(-7));
        }
    }
    
    function updateSalesChart(type) {
        if (!salesChart) return;
        
        salesChart.data.datasets[0].data = salesChartData[type];
        salesChart.data.datasets[0].label = type === 'revenue' ? 'الإيرادات' : 'الفواتير';
        
        const ctx = document.getElementById('sales-chart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        
        if (type === 'revenue') {
             gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
             gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
             salesChart.data.datasets[0].borderColor = '#3B82F6';
             salesChart.data.datasets[0].pointBorderColor = '#3B82F6';
        } else {
             gradient.addColorStop(0, 'rgba(132, 204, 22, 0.5)');
             gradient.addColorStop(1, 'rgba(132, 204, 22, 0.0)');
             salesChart.data.datasets[0].borderColor = '#84CC16';
             salesChart.data.datasets[0].pointBorderColor = '#84CC16';
        }
        salesChart.data.datasets[0].backgroundColor = gradient;

        salesChart.options.plugins.tooltip.callbacks.label = type === 'revenue' 
            ? (context) => formatNumber(context.parsed.y) + ' ' + currency
            : (context) => context.parsed.y + ' فاتورة';
        salesChart.update();
    }
    
    function createRevenueMiniChart(data) {
        if (revenueMiniChart) {
            revenueMiniChart.destroy();
        }
        
        const ctx = document.getElementById('revenue-mini-chart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 50);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        revenueMiniChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array(data.length).fill(''),
                datasets: [{
                    data: data,
                    borderColor: '#3B82F6',
                    borderWidth: 2,
                    backgroundColor: gradient,
                    tension: 0.4,
                    pointRadius: 0,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    y: { display: false, min: Math.min(...data) * 0.9 }, // Dynamic min to show curve better
                    x: { display: false }
                }
            }
        });
    }
    
    async function loadCategorySales() {
        const response = await fetch(`api.php?action=getCategorySales&days=${currentPeriod}`);
        const result = await response.json();
        
        if (result.success && result.data.length > 0) {
            const data = result.data.slice(0, 5);
            
            if (categoryChart) {
                categoryChart.destroy();
            }
            
            const ctx = document.getElementById('category-chart').getContext('2d');
            categoryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(d => d.category),
                    datasets: [{
                        data: data.map(d => parseFloat(d.revenue)),
                        backgroundColor: [
                            '#3B82F6',
                            '#84CC16',
                            '#F59E0B',
                            '#EF4444',
                            '#8B5CF6'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { 
                                color: '#9CA3AF', 
                                padding: 15, 
                                font: { family: 'Tajawal', size: 11 },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(31, 41, 55, 0.9)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            titleFont: { family: 'Tajawal' },
                            bodyFont: { family: 'Tajawal' },
                            callbacks: {
                                label: (context) => context.label + ': ' + formatNumber(context.parsed) + ' ' + currency
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    }
    
    async function loadTopProducts() {
        const response = await fetch(`api.php?action=getTopProducts&days=${currentPeriod}`);
        const result = await response.json();
        
        const tbody = document.getElementById('top-products-table');
        
        if (result.success && result.data.length > 0) {
            tbody.innerHTML = result.data.map((product, index) => {
                let stockClass = 'text-gray-300';
                let statusBadge = '<span class="px-2 py-1 rounded bg-green-500/10 text-green-500 text-xs font-bold">متوفر</span>';
                
                if(product.stock <= 5) {
                    stockClass = 'text-red-500';
                    statusBadge = '<span class="px-2 py-1 rounded bg-red-500/10 text-red-500 text-xs font-bold">منخفض</span>';
                } else if(product.stock <= 10) {
                     stockClass = 'text-orange-500';
                    statusBadge = '<span class="px-2 py-1 rounded bg-orange-500/10 text-orange-500 text-xs font-bold">تحذير</span>';
                }

                return `
                    <tr class="hover:bg-white/5 transition-colors group">
                        <td class="py-4 text-sm text-white font-medium flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-gray-400 group-hover:bg-primary/20 group-hover:text-primary transition-colors">
                                ${index + 1}
                            </div>
                            ${product.name}
                        </td>
                        <td class="py-4 text-sm text-center text-gray-300 font-bold">${toEnglishNumbers(product.units_sold.toString())}</td>
                        <td class="py-4 text-sm text-center text-primary font-bold">${toEnglishNumbers(formatNumber(product.revenue))} <span class="text-xs font-normal text-gray-500">${currency}</span></td>
                        <td class="py-4 text-center">${statusBadge}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-500">لا توجد بيانات</td></tr>';
        }
    }
    
    async function loadRecentInvoices() {
        const response = await fetch('api.php?action=getRecentInvoices&limit=5');
        const result = await response.json();
        
        const container = document.getElementById('recent-invoices-list');
        
        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map(invoice => {
                const timeAgo = getTimeAgo(new Date(invoice.created_at));
                return `
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl hover:bg-white/10 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary/20 flex items-center justify-center">
                                <span class="material-icons-round text-primary text-sm">receipt</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">#${toEnglishNumbers(String(invoice.id).padStart(6, '0'))}</p>
                                <p class="text-xs text-gray-400">${invoice.customer_name || 'عميل نقدي'}</p>
                            </div>
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-primary">${toEnglishNumbers(formatNumber(invoice.total))} ${currency}</p>
                            <p class="text-xs text-gray-500">${timeAgo}</p>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="text-center py-4 text-gray-500">لا توجد فواتير</div>';
        }
    }
    
    async function loadTopCustomers() {
        const response = await fetch(`api.php?action=getTopCustomers&days=${currentPeriod}`);
        const result = await response.json();
        
        const container = document.getElementById('top-customers-list');
        
        if (result.success && result.data.length > 0) {
            container.innerHTML = result.data.map((customer, index) => `
                <div class="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center text-white font-bold text-lg">
                            ${customer.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">${customer.name}</p>
                            <p class="text-xs text-gray-400">${toEnglishNumbers(customer.order_count.toString())} طلب</p>
                        </div>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-primary">${toEnglishNumbers(formatNumber(customer.total_spent))} ${currency}</p>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="text-center py-4 text-gray-500">لا توجد بيانات</div>';
        }
    }
    
    function updateChangeIndicator(elementId, change) {
        const element = document.getElementById(elementId);
        if(!element) return;
        
        const isPositive = change >= 0;
        
        element.innerHTML = `
            <span class="material-icons-round text-sm transform ${isPositive ? '' : 'rotate-180'}">trending_up</span>
            <span>${toEnglishNumbers(Math.abs(change).toFixed(1))}%</span>
        `;
        element.className = `flex items-center gap-1 text-xs px-2 py-1 rounded-full bg-white/5 ${isPositive ? 'text-green-500' : 'text-red-500'}`;
    }
    
    function formatNumber(num) {
        const formatted = parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return formatted;
    }
    
    function getTimeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        
        if (seconds < 60) return 'الآن';
        if (seconds < 3600) return Math.floor(seconds / 60) + ' دقيقة';
        if (seconds < 86400) return Math.floor(seconds / 3600) + ' ساعة';
        
        // إذا كان أكثر من يوم، اعرض التاريخ كاملاً
        const gregorian = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        return gregorian;
    }
    
    // ========================================
    // Daily Tracking Functions
    // ========================================
    
    async function loadCurrentDayStatus() {
        try {
            const response = await fetch('api.php?action=getCurrentDayStatus');
            const result = await response.json();
            
            if (result.success) {
                const status = result.status;
                const statusIcon = document.getElementById('day-status-icon');
                const statusText = document.getElementById('day-status-text');
                const dayDateText = document.getElementById('day-date-text');
                const openBtn = document.getElementById('open-day-btn');
                const closeBtn = document.getElementById('close-day-btn');
                const dayMetrics = document.getElementById('day-metrics');
                
                if (status === 'open') {
                    statusIcon.textContent = 'lock_open';
                    statusIcon.className = 'material-icons-round text-sm text-green-500';
                    statusText.textContent = 'يوم العمل مفتوح';
                    statusText.className = 'text-sm font-bold text-green-500';
                    
                    if (result.data) {
                        dayDateText.textContent = result.data.business_date;
                        document.getElementById('current-day-sales').textContent = toEnglishNumbers(formatNumber(result.data.current_sales)) + ' ' + currency;
                        document.getElementById('current-day-invoices').textContent = toEnglishNumbers(result.data.current_invoices.toString());
                        dayMetrics.style.display = 'block';
                    }
                    
                    openBtn.style.display = 'none';
                    closeBtn.style.display = 'flex';
                } else {
                    statusIcon.textContent = 'lock';
                    statusIcon.className = 'material-icons-round text-sm text-gray-500';
                    statusText.textContent = 'يوم العمل مغلق';
                    statusText.className = 'text-sm font-bold text-gray-500';
                    dayDateText.textContent = '';
                    dayMetrics.style.display = 'none';
                    
                    openBtn.style.display = 'flex';
                    closeBtn.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error loading day status:', error);
        }
    }
    
    async function openDay(auto = false) {
        if (!auto && !confirm('هل تريد فتح يوم عمل جديد؟\n\nسيتم تسجيل المخزون الحالي كأساس لليوم.')) {
            return;
        }
        
        showLoading('جاري فتح يوم العمل...');
        try {
            const response = await fetch('api.php?action=openBusinessDay', { method: 'POST' });
            const result = await response.json();
            
            if (result.success) {
                showToast('تم فتح يوم العمل بنجاح', true);
                await loadAllData();
            } else {
                showToast(result.message || 'فشل فتح يوم العمل', false);
            }
        } catch (error) {
            console.error('Error opening day:', error);
            showToast('حدث خطأ في فتح يوم العمل', false);
        } finally {
            hideLoading();
        }
    }
    
    async function closeDay(auto = false) {
        if (!auto && !confirm('هل تريد إغلاق يوم العمل؟\n\nسيتم حساب جميع المبيعات والأرباح والمخزون وحفظها في السجل اليومي.\n\nهذا الإجراء لا يمكن التراجع عنه.')) {
            return;
        }
        
        showLoading('جاري إغلاق يوم العمل...');
        try {
            const response = await fetch('api.php?action=closeBusinessDay', { method: 'POST' });
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                let message = `تم إغلاق يوم العمل بنجاح\n\n`;
                message += `التاريخ: ${data.business_date}\n`;
                message += `المبيعات: ${formatNumber(data.total_sales)} ${currency}\n`;
                message += `الفواتير: ${data.total_invoices}\n`;
                message += `الربح: ${formatNumber(data.gross_profit)} ${currency}\n`;
                message += `هامش الربح: ${formatNumber(data.profit_margin)}%`;
                
                alert(message);
                await loadAllData();
            } else {
                showToast(result.message || 'فشل إغلاق يوم العمل', false);
            }
        } catch (error) {
            console.error('Error closing day:', error);
            showToast('حدث خطأ في إغلاق يوم العمل', false);
        } finally {
            hideLoading();
        }
    }
    
    function updateDailyProfitCard(profit, cost, margin) {
        const profitElement = document.getElementById('daily-profit');
        const costElement = document.getElementById('daily-cost');
        const marginElement = document.getElementById('profit-margin');
        const statusBadge = document.getElementById('profit-status');
        
        const isProfit = profit >= 0;
        const profitColor = isProfit ? 'text-green-500' : 'text-red-500';
        
        profitElement.innerHTML = `<span class="${profitColor}">${toEnglishNumbers(formatNumber(Math.abs(profit)))} ${currency}</span>`;
        costElement.textContent = toEnglishNumbers(formatNumber(cost)) + ' ' + currency;
        marginElement.textContent = toEnglishNumbers(formatNumber(margin)) + '%';
        marginElement.className = `font-bold ${profitColor}`;
        
        statusBadge.textContent = isProfit ? 'ربح' : 'خسارة';
        statusBadge.className = `text-xs px-2 py-1 rounded-full font-bold ${isProfit ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500'}`;
    }
    
    // Initial load
    loadAllData();
    
    // Auto open/close day logic
    const autoDayManagement = '<?php echo $auto_day_management; ?>' === '1';
    const autoOpenTime = '<?php echo $auto_open_time; ?>';
    const autoCloseTime = '<?php echo $auto_close_time; ?>';
    
    function checkAutoDayActions() {
        if (!autoDayManagement) return;
        
        const now = new Date();
        const currentTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        
        // Check auto open
        if (currentTime === autoOpenTime) {
            // Check if day is already open
            fetch('api.php?action=getCurrentDayStatus')
                .then(response => response.json())
                .then(result => {
                    if (result.success && !result.data.is_open) {
                        console.log('Auto opening business day at', autoOpenTime);
                        openDay(true);
                    }
                })
                .catch(error => console.error('Error checking day status for auto open:', error));
        }
        
        // Check auto close
        if (currentTime === autoCloseTime) {
            // Check if day is open
            fetch('api.php?action=getCurrentDayStatus')
                .then(response => response.json())
                .then(result => {
                    if (result.success && result.data.is_open) {
                        console.log('Auto closing business day at', autoCloseTime);
                        closeDay(true);
                    }
                })
                .catch(error => console.error('Error checking day status for auto close:', error));
        }
    }
    
    // Check every minute
    setInterval(checkAutoDayActions, 60000);
    
    // Check immediately on load
    checkAutoDayActions();
    
    // Auto refresh every 5 minutes
    setInterval(loadAllData, 300000);
});
</script>

<?php require_once 'src/footer.php'; ?>