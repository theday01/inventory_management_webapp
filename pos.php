<?php
$page_title = 'نقطة البيع';
$current_page = 'pos.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch settings
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxEnabled'");
$taxEnabled = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '1';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxRate'");
$taxRate = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '20';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxLabel'");
$taxLabel = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'TVA';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
$shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'Smart Shop';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopPhone'");
$shopPhone = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopAddress'");
$shopAddress = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';
?>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #invoice-print-area, #invoice-print-area * {
        visibility: visible;
    }
    #invoice-print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<!-- Main Content -->
<main class="flex-1 flex flex-row-reverse relative overflow-hidden">
    <!-- Cart Sidebar (Left) -->
    <aside class="w-96 bg-dark-surface border-r border-white/5 flex flex-col z-20 shadow-2xl">
        <div class="p-6 border-b border-white/5">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xl font-bold text-white">سلة المشتريات</h2>
            </div>
            <div id="customer-selection" class="flex items-center gap-2 mt-4 bg-white/5 p-3 rounded-xl cursor-pointer hover:bg-white/10 transition-colors">
                <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-xs" id="customer-avatar">A</div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-white" id="customer-name-display">عميل نقدي</p>
                    <p class="text-xs text-gray-400" id="customer-detail-display">افتراضي</p>
                </div>
                <span class="material-icons-round text-gray-400">arrow_drop_down</span>
            </div>
        </div>

        <div id="cart-items" class="flex-1 overflow-y-auto p-4 space-y-3"></div>

        <div class="p-6 bg-dark-surface border-t border-white/5">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm text-gray-400">
                    <span>المجموع الفرعي</span>
                    <span id="cart-subtotal">0 <?php echo $currency; ?></span>
                </div>
                <?php if ($taxEnabled == '1'): ?>
                <div class="flex justify-between text-sm text-gray-400">
                    <span><?php echo htmlspecialchars($taxLabel); ?> (<span id="tax-rate-display"><?php echo $taxRate; ?></span>%)</span>
                    <span id="cart-tax">0 <?php echo $currency; ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between text-lg font-bold text-white pt-2 border-t border-white/5">
                    <span>الإجمالي</span>
                    <span id="cart-total" class="text-primary">0 <?php echo $currency; ?></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <button class="button-secondary bg-white/5 hover:bg-white/10 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">pause</span>
                    تعليق
                </button>
                <button id="clear-cart-btn" class="button-danger bg-red-500/10 hover:bg-red-500/20 text-red-500 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">delete_outline</span>
                    إلغاء
                </button>
            </div>

            <button id="checkout-btn" class="w-full bg-accent hover:bg-lime-500 text-dark-surface py-4 rounded-xl font-bold text-lg shadow-lg shadow-accent/20 flex items-center justify-center gap-2 transition-all hover:scale-[1.02]">
                <span class="material-icons-round">payments</span>
                دفع (space)
            </button>
        </div>
    </aside>

    <!-- Products Section (Right) -->
    <div class="flex-1 flex flex-col h-full relative">
        <div class="absolute top-[10%] right-[10%] w-[400px] h-[400px] bg-primary/5 rounded-full blur-[80px] pointer-events-none"></div>

        <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-6 z-10 shrink-0">
            <div class="flex items-center gap-4 flex-1">
                <a href="dashboard.php" class="p-2 text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-xl transition-colors">
                    <span class="material-icons-round">arrow_forward</span>
                </a>
                <div class="relative flex-1 max-w-md">
                    <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                    <input type="text" id="product-search-input" placeholder="بحث عن منتج..." class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                    <button id="scan-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white">
                        <span class="material-icons-round">qr_code_scanner</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label for="category-filter" class="text-sm text-gray-400">الفئة:</label>
                <div class="relative min-w-[200px]">
                    <select id="category-filter" class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                        <option value="">جميع الفئات</option>
                    </select>
                    <span class="material-icons-round absolute top-1/2 left-2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 z-10">
            <div id="products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"></div>
        </div>
    </div>
</main>

<!-- Invoice Modal -->
<div id="invoice-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-auto overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-primary to-accent p-6 text-white no-print">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="material-icons-round text-3xl">receipt_long</span>
                    <div>
                        <h3 class="text-2xl font-bold">فاتورة ناجحة!</h3>
                        <p class="text-sm opacity-90">تم إتمام عملية البيع بنجاح</p>
                    </div>
                </div>
                <button id="close-invoice-modal" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
        </div>

        <!-- Invoice Content -->
        <div id="invoice-print-area" class="p-8 bg-white text-gray-900">
            <!-- Shop Header -->
            <div class="text-center border-b-2 border-gray-300 pb-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($shopName); ?></h1>
                <?php if ($shopPhone): ?>
                    <p class="text-sm text-gray-600">هاتف: <?php echo htmlspecialchars($shopPhone); ?></p>
                <?php endif; ?>
                <?php if ($shopAddress): ?>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($shopAddress); ?></p>
                <?php endif; ?>
            </div>

            <!-- Invoice Info -->
            <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
                <div>
                    <p class="text-gray-600 mb-1">رقم الفاتورة</p>
                    <p class="font-bold text-lg" id="invoice-number">-</p>
                </div>
                <div class="text-left">
                    <p class="text-gray-600 mb-1">التاريخ</p>
                    <p class="font-bold" id="invoice-date">-</p>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-gray-900 mb-2">معلومات العميل</h3>
                <div id="customer-info" class="text-sm text-gray-700"></div>
            </div>

            <!-- Items Table -->
            <table class="w-full mb-6 text-sm">
                <thead>
                    <tr class="border-b-2 border-gray-300">
                        <th class="text-right py-3 font-bold">#</th>
                        <th class="text-right py-3 font-bold">المنتج</th>
                        <th class="text-center py-3 font-bold">الكمية</th>
                        <th class="text-center py-3 font-bold">السعر</th>
                        <th class="text-left py-3 font-bold">الإجمالي</th>
                    </tr>
                </thead>
                <tbody id="invoice-items"></tbody>
            </table>

            <!-- Totals -->
            <div class="border-t-2 border-gray-300 pt-4">
                <div class="flex justify-end">
                    <div class="w-64 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">المجموع الفرعي:</span>
                            <span class="font-medium" id="invoice-subtotal">-</span>
                        </div>
                        <div class="flex justify-between" id="invoice-tax-row">
                            <span class="text-gray-600"><span id="invoice-tax-label">TVA</span> (<span id="invoice-tax-rate">20</span>%):</span>
                            <span class="font-medium" id="invoice-tax-amount">-</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold border-t-2 border-gray-300 pt-2">
                            <span>الإجمالي:</span>
                            <span class="text-primary" id="invoice-total">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 pt-6 border-t border-gray-200 text-xs text-gray-500">
                <p class="font-semibold text-gray-700 mb-2">شكرا لثقتكم بنا</p>
                <?php if (!empty($shopName) || !empty($shopPhone) || !empty($shopAddress)): ?>
                    <div class="mt-2 text-gray-600">
                        <?php if (!empty($shopName)): ?>
                            <p><?php echo htmlspecialchars($shopName); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($shopPhone)): ?>
                            <p>هاتف: <?php echo htmlspecialchars($shopPhone); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($shopAddress)): ?>
                            <p><?php echo htmlspecialchars($shopAddress); ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="mt-2">
                        <p class="text-gray-600">تم تصميم وتطوير النظام من طرف حمزة سعدي 2025</p>
                        <p class="text-gray-600 mt-1">الموقع الإلكتروني: <a href="https://eagleshadow.technology" class="text-primary hover:underline">https://eagleshadow.technology</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-gray-50 p-6 flex gap-3 no-print border-t">
            <button id="print-invoice-btn" class="flex-1 bg-primary hover:bg-primary-hover text-white py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                <span class="material-icons-round">print</span>
                طباعة
            </button>
            <button id="download-pdf-btn" class="flex-1 bg-accent hover:bg-lime-500 text-white py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                <span class="material-icons-round">picture_as_pdf</span>
                PDF تحميل
            </button>
            <button id="download-txt-btn" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white py-3 px-6 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                <span class="material-icons-round">text_snippet</span>
                TXT تحميل
            </button>
        </div>
    </div>
</div>

<!-- Customer Modal -->
<div id="customer-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">اختر عميل</h3>
            <button id="close-customer-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6">
            <input type="text" id="customer-search" placeholder="بحث عن عميل..." class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 mb-4">
            <div id="customer-list" class="max-h-60 overflow-y-auto"></div>
        </div>
        <div class="p-6 border-t border-white/5">
            <h3 class="text-lg font-bold text-white mb-4">أو أضف عميل جديد</h3>
            <form id="add-customer-form">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" id="customer-name" placeholder="الاسم" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    <input type="text" id="customer-phone" placeholder="الهاتف" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    <input type="email" id="customer-email" placeholder="البريد الإلكتروني" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 col-span-2">
                </div>
                <button type="submit" class="w-full bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all mt-4">
                    إضافة عميل
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div id="barcode-scanner-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">مسح الباركود</h3>
            <button id="close-barcode-scanner-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6">
            <video id="barcode-video" class="w-full h-auto rounded-lg"></video>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/index.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productsGrid = document.getElementById('products-grid');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartSubtotal = document.getElementById('cart-subtotal');
    const cartTax = document.getElementById('cart-tax');
    const cartTotal = document.getElementById('cart-total');
    const searchInput = document.getElementById('product-search-input');
    const checkoutBtn = document.getElementById('checkout-btn');
    const categoryFilter = document.getElementById('category-filter');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    
    const customerModal = document.getElementById('customer-modal');
    const closeCustomerModalBtn = document.getElementById('close-customer-modal');
    const customerSelection = document.getElementById('customer-selection');
    const customerSearchInput = document.getElementById('customer-search');
    const customerList = document.getElementById('customer-list');
    const addCustomerForm = document.getElementById('add-customer-form');
    const customerNameDisplay = document.getElementById('customer-name-display');
    const customerDetailDisplay = document.getElementById('customer-detail-display');
    const customerAvatar = document.getElementById('customer-avatar');

    const invoiceModal = document.getElementById('invoice-modal');
    const closeInvoiceModal = document.getElementById('close-invoice-modal');
    const printInvoiceBtn = document.getElementById('print-invoice-btn');
    const downloadPdfBtn = document.getElementById('download-pdf-btn');
    const downloadTxtBtn = document.getElementById('download-txt-btn');

    let cart = [];
    let allProducts = [];
    let selectedCustomer = null;
    let currentInvoiceData = null;
    
    const taxEnabled = <?php echo $taxEnabled; ?> == 1;
    const taxRate = <?php echo $taxRate; ?> / 100;
    const taxLabel = '<?php echo addslashes($taxLabel); ?>';
    const currency = '<?php echo $currency; ?>';
    const shopName = '<?php echo addslashes($shopName); ?>';

    // دالة لتحويل الأرقام العربية إلى أرقام إنجليزية
    function toEnglishNumbers(str) {
        const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        let result = str.toString();
        for (let i = 0; i < 10; i++) {
            result = result.replace(new RegExp(arabicNumbers[i], 'g'), englishNumbers[i]);
        }
        return result;
    }

    // دالة لتنسيق التاريخ (ميلادي وهجري)
    function formatDualDate(date) {
        // التاريخ الميلادي
        const gregorianDate = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
        
        // التاريخ الهجري
        const hijriDate = date.toLocaleDateString('ar-SA-u-ca-islamic', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // تحويل الأرقام إلى إنجليزية
        const hijriDateEng = toEnglishNumbers(hijriDate);
        
        return `${gregorianDate} - ${hijriDateEng}`;
    }

    async function loadCategories() {
        try {
            const response = await fetch('api.php?action=getCategories');
            const result = await response.json();
            if (result.success) {
                categoryFilter.innerHTML = '<option value="">جميع الفئات</option>';
                result.data.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categoryFilter.appendChild(option);
                });
            }
        } catch (error) {
            console.error('خطأ في تحميل الفئات:', error);
        }
    }

    async function loadProducts() {
        try {
            const response = await fetch('api.php?action=getProducts');
            const result = await response.json();
            if (result.success) {
                allProducts = result.data;
                applyFilters();
            }
        } catch (error) {
            console.error('خطأ في تحميل المنتجات:', error);
        }
    }

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const categoryId = categoryFilter.value;
        
        let filteredProducts = allProducts;
        
        if (categoryId) {
            filteredProducts = filteredProducts.filter(product => product.category_id == categoryId);
        }
        
        if (searchTerm) {
            filteredProducts = filteredProducts.filter(product =>
                product.name.toLowerCase().includes(searchTerm) ||
                (product.barcode && product.barcode.includes(searchTerm))
            );
        }
        
        displayProducts(filteredProducts);
    }

    searchInput.addEventListener('input', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);

    function displayProducts(products) {
        productsGrid.innerHTML = '';
        if (products.length === 0) {
            productsGrid.innerHTML = '<p class="text-center py-4 text-gray-500 col-span-full">لا توجد منتجات لعرضها.</p>';
            return;
        }
        products.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'bg-dark-surface/50 border border-white/5 rounded-2xl p-4 flex flex-col items-center justify-center text-center hover:border-primary/50 transition-all cursor-pointer';
            productCard.innerHTML = `
                <img src="${product.image || 'src/img/default-product.png'}" alt="${product.name}" class="w-24 h-24 object-cover rounded-lg mb-4">
                <div class="text-lg font-bold text-white">${product.name}</div>
                <div class="text-sm text-gray-400">${product.price} ${currency}</div>
            `;
            productCard.addEventListener('click', () => addProductToCart(product));
            productsGrid.appendChild(productCard);
        });
    }

    function addProductToCart(product) {
        const existingProduct = cart.find(item => item.id === product.id);
        if (existingProduct) {
            existingProduct.quantity++;
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        updateCart();
    }

    function updateCart() {
        cartItemsContainer.innerHTML = '';
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = '<p class="text-center py-4 text-gray-500">سلة المشتريات فارغة.</p>';
        } else {
            cart.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.className = 'flex items-center justify-between bg-white/5 p-3 rounded-xl';
                cartItem.innerHTML = `
                    <div class="flex-1">
                        <p class="text-sm font-bold text-white">${item.name}</p>
                        <p class="text-xs text-gray-400">${item.price} ${currency}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="quantity-btn w-8 h-8 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-colors" data-id="${item.id}" data-action="decrease">-</button>
                        <span class="text-white font-bold min-w-[30px] text-center">${item.quantity}</span>
                        <button class="quantity-btn w-8 h-8 bg-primary/20 text-primary rounded-lg hover:bg-primary/30 transition-colors" data-id="${item.id}" data-action="increase">+</button>
                    </div>
                `;
                cartItemsContainer.appendChild(cartItem);
            });
        }
        updateTotals();
    }

    function updateTotals() {
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax;

        cartSubtotal.textContent = `${subtotal.toFixed(2)} ${currency}`;
        if (cartTax) {
            cartTax.textContent = `${tax.toFixed(2)} ${currency}`;
        }
        cartTotal.textContent = `${total.toFixed(2)} ${currency}`;
    }

    cartItemsContainer.addEventListener('click', function (e) {
        if (e.target.classList.contains('quantity-btn')) {
            const id = e.target.dataset.id;
            const action = e.target.dataset.action;
            const item = cart.find(product => product.id == id);

            if (item) {
                if (action === 'increase') {
                    item.quantity++;
                } else if (action === 'decrease') {
                    item.quantity--;
                    if (item.quantity === 0) {
                        cart = cart.filter(product => product.id != id);
                    }
                }
                updateCart();
            }
        }
    });

    clearCartBtn.addEventListener('click', () => {
        if (cart.length > 0 && confirm('هل أنت متأكد من إلغاء السلة؟')) {
            cart = [];
            updateCart();
            showToast('تم إلغاء السلة', true);
        }
    });

    checkoutBtn.addEventListener('click', async () => {
        if (cart.length === 0) {
            showToast('السلة فارغة!', false);
            return;
        }
        
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax;
        
        try {
            const response = await fetch('api.php?action=createInvoice', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    customer_id: selectedCustomer ? selectedCustomer.id : null,
                    total: total,
                    items: cart
                }),
            });
            
            const result = await response.json();
            if (result.success) {
                currentInvoiceData = {
                    id: result.invoice_id,
                    customer: selectedCustomer,
                    items: cart,
                    subtotal: subtotal,
                    tax: tax,
                    total: total,
                    date: new Date()
                };
                
                displayInvoice(currentInvoiceData);
                cart = [];
                selectedCustomer = null;
                customerNameDisplay.textContent = 'عميل نقدي';
                customerDetailDisplay.textContent = 'افتراضي';
                customerAvatar.textContent = 'A';
                updateCart();
                invoiceModal.classList.remove('hidden');
            } else {
                showToast(result.message || 'فشل في إنشاء الفاتورة', false);
            }
        } catch (error) {
            console.error('خطأ في إنشاء الفاتورة:', error);
            showToast('حدث خطأ أثناء إنشاء الفاتورة', false);
        }
    });

    function displayInvoice(data) {
        document.getElementById('invoice-number').textContent = `#${String(data.id).padStart(6, '0')}`;
        document.getElementById('invoice-date').textContent = formatDualDate(data.date);
        
        const customerInfo = document.getElementById('customer-info');
        if (data.customer) {
            customerInfo.innerHTML = `
                <p><strong>الاسم:</strong> ${data.customer.name}</p>
                ${data.customer.phone ? `<p><strong>الهاتف:</strong> ${data.customer.phone}</p>` : ''}
                ${data.customer.email ? `<p><strong>البريد:</strong> ${data.customer.email}</p>` : ''}
            `;
        } else {
            customerInfo.innerHTML = '<p>عميل نقدي</p>';
        }
        
        const itemsTable = document.getElementById('invoice-items');
        itemsTable.innerHTML = '';
        data.items.forEach((item, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200';
            row.innerHTML = `
                <td class="py-2">${index + 1}</td>
                <td class="py-2">${item.name}</td>
                <td class="py-2 text-center">${item.quantity}</td>
                <td class="py-2 text-center">${item.price} ${currency}</td>
                <td class="py-2 text-left font-medium">${(item.price * item.quantity).toFixed(2)} ${currency}</td>
            `;
            itemsTable.appendChild(row);
        });
        
        document.getElementById('invoice-subtotal').textContent = `${data.subtotal.toFixed(2)} ${currency}`;
        
        if (taxEnabled) {
            document.getElementById('invoice-tax-row').style.display = 'flex';
            document.getElementById('invoice-tax-label').textContent = taxLabel;
            document.getElementById('invoice-tax-rate').textContent = (taxRate * 100).toFixed(0);
            document.getElementById('invoice-tax-amount').textContent = `${data.tax.toFixed(2)} ${currency}`;
        } else {
            document.getElementById('invoice-tax-row').style.display = 'none';
        }
        
        document.getElementById('invoice-total').textContent = `${data.total.toFixed(2)} ${currency}`;
    }

    closeInvoiceModal.addEventListener('click', () => {
        invoiceModal.classList.add('hidden');
    });

    printInvoiceBtn.addEventListener('click', () => {
        window.print();
    });

    downloadPdfBtn.addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        const element = document.getElementById('invoice-print-area');
        
        try {
            const canvas = await html2canvas(element, {
                scale: 2,
                backgroundColor: '#ffffff'
            });
            
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
            
            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            pdf.save(`invoice-${currentInvoiceData.id}.pdf`);
            
            showToast('تم تحميل الفاتورة بصيغة PDF', true);
        } catch (error) {
            console.error('خطأ في تحميل PDF:', error);
            showToast('فشل في تحميل PDF', false);
        }
    });

    downloadTxtBtn.addEventListener('click', () => {
        if (!currentInvoiceData) return;
        
        let txtContent = `${shopName}\n`;
        txtContent += `${'='.repeat(50)}\n\n`;
        txtContent += `رقم الفاتورة: #${String(currentInvoiceData.id).padStart(6, '0')}\n`;
        txtContent += `التاريخ: ${formatDualDate(currentInvoiceData.date)}\n\n`;
        
        if (currentInvoiceData.customer) {
            txtContent += `العميل: ${currentInvoiceData.customer.name}\n`;
            if (currentInvoiceData.customer.phone) {
                txtContent += `الهاتف: ${currentInvoiceData.customer.phone}\n`;
            }
        } else {
            txtContent += `العميل: عميل نقدي\n`;
        }
        
        txtContent += `\n${'-'.repeat(50)}\n`;
        txtContent += `المنتجات:\n`;
        txtContent += `${'-'.repeat(50)}\n\n`;
        
        currentInvoiceData.items.forEach((item, index) => {
            txtContent += `${index + 1}. ${item.name}\n`;
            txtContent += `   الكمية: ${item.quantity} × ${item.price} ${currency} = ${(item.price * item.quantity).toFixed(2)} ${currency}\n\n`;
        });
        
        txtContent += `${'-'.repeat(50)}\n`;
        txtContent += `المجموع الفرعي: ${currentInvoiceData.subtotal.toFixed(2)} ${currency}\n`;
        
        if (taxEnabled) {
            txtContent += `${taxLabel} (${(taxRate * 100).toFixed(0)}%): ${currentInvoiceData.tax.toFixed(2)} ${currency}\n`;
        }
        
        txtContent += `الإجمالي: ${currentInvoiceData.total.toFixed(2)} ${currency}\n`;
        txtContent += `${'='.repeat(50)}\n\n`;
        
        // الفوتر المحدث
        txtContent += `شكرا لثقتكم بنا\n\n`;
        
        // التحقق من وجود بيانات المتجر
        const shopPhone = '<?php echo addslashes($shopPhone); ?>';
        const shopAddress = '<?php echo addslashes($shopAddress); ?>';
        
        if (shopName || shopPhone || shopAddress) {
            if (shopName) txtContent += `${shopName}\n`;
            if (shopPhone) txtContent += `هاتف: ${shopPhone}\n`;
            if (shopAddress) txtContent += `${shopAddress}\n`;
        } else {
            txtContent += `تم تصميم وتطوير النظام من طرف حمزة سعدي 2025\n`;
            txtContent += `الموقع الإلكتروني: https://eagleshadow.technology\n`;
        }
        
        const blob = new Blob([txtContent], { type: 'text/plain;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `invoice-${currentInvoiceData.id}.txt`;
        link.click();
        
        showToast('تم تحميل الفاتورة بصيغة TXT', true);
    });
    
    // Customer modal functionality
    customerSelection.addEventListener('click', () => {
        customerModal.classList.remove('hidden');
        loadCustomers();
    });

    closeCustomerModalBtn.addEventListener('click', () => {
        customerModal.classList.add('hidden');
    });

    customerSearchInput.addEventListener('input', () => {
        loadCustomers(customerSearchInput.value);
    });

    async function loadCustomers(search = '') {
        try {
            const response = await fetch(`api.php?action=getCustomers&search=${search}`);
            const result = await response.json();
            if (result.success) {
                displayCustomers(result.data);
            }
        } catch (error) {
            console.error('خطأ في تحميل العملاء:', error);
        }
    }

    function displayCustomers(customers) {
        customerList.innerHTML = '';
        if (customers.length === 0) {
            customerList.innerHTML = '<p class="text-gray-500">لم يتم العثور على عملاء.</p>';
            return;
        }
        customers.forEach(customer => {
            const customerElement = document.createElement('div');
            customerElement.className = 'p-3 hover:bg-white/10 rounded-lg cursor-pointer transition-colors flex items-center gap-3';
            customerElement.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                    ${customer.name.charAt(0).toUpperCase()}
                </div>
                <div>
                    <p class="text-white font-bold">${customer.name}</p>
                    <p class="text-xs text-gray-400">${customer.phone || customer.email || 'لا توجد معلومات'}</p>
                </div>
            `;
            customerElement.addEventListener('click', () => selectCustomer(customer));
            customerList.appendChild(customerElement);
        });
    }

    function selectCustomer(customer) {
        selectedCustomer = customer;
        customerNameDisplay.textContent = customer.name;
        customerDetailDisplay.textContent = customer.phone || customer.email || 'لا توجد تفاصيل';
        customerAvatar.textContent = customer.name.charAt(0).toUpperCase();
        customerModal.classList.add('hidden');
        showToast('تم اختيار العميل: ' + customer.name, true);
    }

    addCustomerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const newCustomer = {
            name: document.getElementById('customer-name').value,
            phone: document.getElementById('customer-phone').value,
            email: document.getElementById('customer-email').value,
        };

        if (!newCustomer.name) {
            showToast('الرجاء إدخال اسم العميل', false);
            return;
        }

        try {
            const response = await fetch('api.php?action=addCustomer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(newCustomer),
            });
            const result = await response.json();
            if (result.success) {
                newCustomer.id = result.id;
                selectCustomer(newCustomer);
                addCustomerForm.reset();
                showToast(result.message || 'تم إضافة العميل بنجاح', true);
            } else {
                showToast(result.message || 'فشل في إضافة العميل', false);
            }
        } catch (error) {
            console.error('خطأ في إضافة العميل:', error);
            showToast('حدث خطأ أثناء إضافة العميل', false);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.code === 'Space' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            checkoutBtn.click();
        }
    });

    loadCategories();
    loadProducts();
});

</script>

<?php require_once 'src/footer.php'; ?>