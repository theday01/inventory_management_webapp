<?php
$page_title = 'لوحة التحكم';
$current_page = 'dashboard.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<style>
.stat-card {
    transition: all 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}
.chart-container {
    position: relative;
    height: 300px;
}
.mini-chart {
    height: 60px;
}
</style>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <!-- Background Blobs -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent/5 rounded-full blur-[100px] pointer-events-none"></div>

    <!-- Header -->
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <div class="flex items-center gap-4">
            <h2 class="text-xl font-bold text-white">لوحة التحكم</h2>
            <span class="text-sm text-gray-400" id="current-date"></span>
        </div>

        <div class="flex items-center gap-4">
            <select id="dashboard-period" class="bg-dark/50 border border-white/10 text-white text-sm px-4 py-2 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                <option value="7">آخر 7 أيام</option>
                <option value="30" selected>آخر 30 يوم</option>
                <option value="90">آخر 90 يوم</option>
            </select>
            <a href="pos.php" class="bg-primary hover:bg-primary-hover text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">add</span>
                بيع جديد
            </a>
        </div>
    </header>

    <!-- Content Scrollable -->
    <div class="flex-1 overflow-y-auto p-8 relative z-10" style="max-height: calc(100vh - 5rem);">

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Today's Revenue -->
            <div class="stat-card bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-primary/10 rounded-xl">
                        <span class="material-icons-round text-primary text-2xl">payments</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs" id="revenue-change">
                        <span class="material-icons-round text-sm">trending_up</span>
                        <span>--%</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-white mb-1" id="today-revenue">0</div>
                <div class="text-sm text-gray-400">مبيعات اليوم</div>
                <div class="mt-4 h-12">
                    <canvas id="revenue-mini-chart"></canvas>
                </div>
            </div>

            <!-- Today's Orders -->
            <div class="stat-card bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-accent/10 rounded-xl">
                        <span class="material-icons-round text-accent text-2xl">receipt_long</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs" id="orders-change">
                        <span class="material-icons-round text-sm">trending_up</span>
                        <span>--%</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-white mb-1" id="today-orders">0</div>
                <div class="text-sm text-gray-400">فواتير اليوم</div>
                <div class="text-xs text-gray-500 mt-2">
                    <span class="font-bold text-primary" id="avg-order-value">0</span> متوسط الفاتورة
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="stat-card bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel cursor-pointer" onclick="window.location.href='products.php'">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-orange-500/10 rounded-xl">
                        <span class="material-icons-round text-orange-500 text-2xl">inventory_2</span>
                    </div>
                    <span class="text-xs bg-orange-500/20 text-orange-500 px-2 py-1 rounded-full font-bold">تنبيه</span>
                </div>
                <div class="text-3xl font-bold text-white mb-1" id="low-stock-count">0</div>
                <div class="text-sm text-gray-400">منتجات منخفضة</div>
                <div class="text-xs text-orange-500 mt-2 flex items-center gap-1">
                    <span class="material-icons-round text-sm">warning</span>
                    <span id="out-of-stock-count">0</span> منتج نفذ
                </div>
            </div>

            <!-- This Month Revenue -->
            <div class="stat-card bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-purple-500/10 rounded-xl">
                        <span class="material-icons-round text-purple-500 text-2xl">trending_up</span>
                    </div>
                    <div class="flex items-center gap-1 text-xs" id="month-change">
                        <span class="material-icons-round text-sm">trending_up</span>
                        <span>--%</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-white mb-1" id="month-revenue">0</div>
                <div class="text-sm text-gray-400">مبيعات الشهر</div>
                <div class="text-xs text-gray-500 mt-2">
                    <span id="new-customers">0</span> عميل جديد
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Sales Trend Chart -->
            <div class="lg:col-span-2 bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white">اتجاه المبيعات</h3>
                    <div class="flex gap-2">
                        <button class="chart-type-btn active" data-type="revenue">الإيرادات</button>
                        <button class="chart-type-btn" data-type="orders">الفواتير</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="sales-chart"></canvas>
                </div>
            </div>

            <!-- Category Sales -->
            <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <h3 class="text-lg font-bold text-white mb-6">المبيعات حسب الفئة</h3>
                <div class="chart-container">
                    <canvas id="category-chart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Top Products -->
            <div class="lg:col-span-2 bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white">الأكثر مبيعاً</h3>
                    <a href="products.php" class="text-sm text-primary hover:text-white transition-colors">عرض الكل</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/5">
                                <th class="pb-4 text-sm font-medium text-gray-400">#</th>
                                <th class="pb-4 text-sm font-medium text-gray-400">المنتج</th>
                                <th class="pb-4 text-sm font-medium text-gray-400 text-center">الوحدات</th>
                                <th class="pb-4 text-sm font-medium text-gray-400 text-center">الإيراد</th>
                                <th class="pb-4 text-sm font-medium text-gray-400 text-center">المخزون</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5" id="top-products-table">
                            <tr><td colspan="5" class="text-center py-4 text-gray-500">جاري التحميل...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity & Top Customers -->
            <div class="space-y-6">
                <!-- Top Customers -->
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-white">أفضل العملاء</h3>
                        <a href="customers.php" class="text-sm text-primary hover:text-white transition-colors">عرض الكل</a>
                    </div>
                    <div class="space-y-4" id="top-customers-list">
                        <div class="text-center py-4 text-gray-500">جاري التحميل...</div>
                    </div>
                </div>

                <!-- Recent Invoices -->
                <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-white">آخر الفواتير</h3>
                        <a href="invoices.php" class="text-sm text-primary hover:text-white transition-colors">عرض الكل</a>
                    </div>
                    <div class="space-y-3" id="recent-invoices-list">
                        <div class="text-center py-4 text-gray-500">جاري التحميل...</div>
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
    
    async function loadAllData() {
        showLoading('جاري تحميل البيانات...');
        try {
            await Promise.all([
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
            
            // Today's Orders
            document.getElementById('today-orders').textContent = toEnglishNumbers(stats.todayOrders.toString());
            const ordersChange = stats.yesterdayOrders > 0 ? ((stats.todayOrders - stats.yesterdayOrders) / stats.yesterdayOrders * 100) : 0;
            updateChangeIndicator('orders-change', ordersChange);
            document.getElementById('avg-order-value').textContent = toEnglishNumbers(formatNumber(stats.avgOrderValue)) + ' ' + currency;            
            // Stock Alerts
            document.getElementById('low-stock-count').textContent = toEnglishNumbers(stats.lowStock.toString());
            document.getElementById('out-of-stock-count').textContent = toEnglishNumbers(stats.outOfStock.toString());            
            // This Month
            document.getElementById('month-revenue').textContent = toEnglishNumbers(formatNumber(stats.thisMonthRevenue)) + ' ' + currency;
            const monthChange = stats.lastMonthRevenue > 0 ? ((stats.thisMonthRevenue - stats.lastMonthRevenue) / stats.lastMonthRevenue * 100) : 0;
            updateChangeIndicator('month-change', monthChange);
            document.getElementById('new-customers').textContent = toEnglishNumbers(stats.newCustomersThisMonth.toString());
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
                const date = new Date(d.date).toLocaleDateString('ar-SA', { month: 'short', day: 'numeric' });
                return date.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
            });            
            if (salesChart) {
                salesChart.destroy();
            }
            
            const ctx = document.getElementById('sales-chart').getContext('2d');
            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'الإيرادات',
                        data: salesChartData.revenue,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            callbacks: {
                                label: (context) => formatNumber(context.parsed.y) + ' ' + currency
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: '#9CA3AF' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#9CA3AF' }
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
        revenueMiniChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array(data.length).fill(''),
                datasets: [{
                    data: data,
                    borderColor: '#3B82F6',
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: {
                    y: { display: false },
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
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#9CA3AF', padding: 15, font: { size: 11 } }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            callbacks: {
                                label: (context) => context.label + ': ' + formatNumber(context.parsed) + ' ' + currency
                            }
                        }
                    }
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
                const stockClass = product.stock <= 5 ? 'text-red-500' : product.stock <= 10 ? 'text-orange-500' : 'text-gray-300';
                return `
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="py-3 text-sm text-gray-500">${toEnglishNumbers((index + 1).toString())}</td>
                        <td class="py-3 text-sm text-white font-medium">${product.name}</td>
                        <td class="py-3 text-sm text-center text-primary font-bold">${toEnglishNumbers(product.units_sold.toString())}</td>
                        <td class="py-3 text-sm text-center text-white">${toEnglishNumbers(formatNumber(product.revenue))} ${currency}</td>
                        <td class="py-3 text-sm text-center ${stockClass} font-bold">${toEnglishNumbers(product.stock.toString())}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">لا توجد بيانات</td></tr>';
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
                            <p class="text-xs text-gray-500">${toEnglishNumbers(timeAgo)}</p>
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
        const isPositive = change >= 0;
        
        element.innerHTML = `
            <span class="material-icons-round text-sm">${isPositive ? 'trending_up' : 'trending_down'}</span>
            <span>${toEnglishNumbers(Math.abs(change).toFixed(1))}%</span>
        `;
        element.className = `flex items-center gap-1 text-xs ${isPositive ? 'text-green-500' : 'text-red-500'}`;
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
        const gregorian = date.toLocaleDateString('ar-SA', { month: 'short', day: 'numeric' });
        return gregorian.replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));
    }
    
    // Initial load
    loadAllData();
    
    // Auto refresh every 5 minutes
    setInterval(loadAllData, 300000);
});
</script>

<?php require_once 'src/footer.php'; ?>