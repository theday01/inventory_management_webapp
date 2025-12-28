<?php
$page_title = 'نقطة البيع';
$current_page = 'pos.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

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
    
    .invoice-items-container {
        page-break-inside: auto;
    }
    
    .invoice-item-row {
        page-break-inside: avoid;
    }
}

.invoice-modal-content {
    max-height: 80vh;
    overflow-y: auto;
}

.invoice-items-scrollable {
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
}

.invoice-items-scrollable::-webkit-scrollbar {
    width: 6px;
}

.invoice-items-scrollable::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.invoice-items-scrollable::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.invoice-items-scrollable::-webkit-scrollbar-thumb:hover {
    background: #555;
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
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-auto overflow-hidden flex flex-col" style="max-height: 90vh;">
        <div class="bg-gradient-to-r from-primary to-accent p-6 text-white no-print shrink-0">
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

        <div class="flex-1 overflow-y-auto">
            <div id="invoice-print-area" class="p-8 bg-white text-gray-900">
                <div class="text-center border-b-2 border-gray-300 pb-6 mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($shopName); ?></h1>
                    <?php if ($shopPhone): ?>
                        <p class="text-sm text-gray-600">هاتف: <?php echo htmlspecialchars($shopPhone); ?></p>
                    <?php endif; ?>
                    <?php if ($shopAddress): ?>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($shopAddress); ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
                    <div>
                        <p class="text-gray-600 mb-1">رقم الفاتورة</p>
                        <p class="font-bold text-lg" id="invoice-number">-</p>
                        <!-- باركود الفاتورة -->
                        <svg id="invoice-barcode" class="mt-2"></svg>
                    </div>
                    <div class="text-left">
                        <p class="text-gray-600 mb-1">التاريخ</p>
                        <p class="font-bold" id="invoice-date">-</p>
                        <p class="text-gray-600 text-xs mt-1">الوقت: <span class="font-medium text-gray-900" id="invoice-time">-</span></p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-gray-900 mb-2">معلومات العميل</h3>
                    <div id="customer-info" class="text-sm text-gray-700"></div>
                </div>

                <div class="mb-6">
                    <div class="invoice-items-scrollable">
                        <table class="w-full text-sm invoice-items-container">
                            <thead class="sticky top-0 bg-white">
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
                    </div>
                    <div id="items-count-badge" class="text-xs text-gray-500 mt-2 text-center hidden"></div>
                </div>

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

                <div class="text-center mt-8 pt-6 border-t border-gray-200 text-xs text-gray-500">
                    <p class="font-semibold text-gray-700 mb-3" style="font-size: 14px;">شكرا لثقتكم بنا</p>
                    <?php if (!empty($shopName) || !empty($shopPhone) || !empty($shopAddress)): ?>
                        <div class="mt-3 text-gray-600 space-y-1">
                            <?php if (!empty($shopName)): ?>
                                <p class="font-medium"><?php echo htmlspecialchars($shopName); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($shopPhone)): ?>
                                <p>هاتف: <?php echo htmlspecialchars($shopPhone); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($shopAddress)): ?>
                                <p><?php echo htmlspecialchars($shopAddress); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="mt-3 space-y-1">
                            <p class="text-gray-600">تم تصميم وتطوير النظام من طرف حمزة سعدي 2025</p>
                            <p class="text-gray-600">الموقع الإلكتروني: <span class="text-blue-600">https://eagleshadow.technology</span></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-6 grid grid-cols-2 gap-3 no-print border-t shrink-0">
            <button id="print-invoice-btn" class="bg-primary hover:bg-primary-hover text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">print</span>
                طباعة عادية
            </button>
            <button id="thermal-print-btn" class="bg-purple-600 hover:bg-purple-700 text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">receipt_long</span>
                طباعة حرارية
            </button>
            <button id="download-pdf-btn" class="bg-accent hover:bg-lime-500 text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">picture_as_pdf</span>
                تحميل PDF
            </button>
            <button id="download-txt-btn" class="bg-gray-700 hover:bg-gray-600 text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">text_snippet</span>
                تحميل TXT
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

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
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
    const thermalPrintBtn = document.getElementById('thermal-print-btn');
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
    const shopPhone = '<?php echo addslashes($shopPhone); ?>';
    const shopAddress = '<?php echo addslashes($shopAddress); ?>';

    function toEnglishNumbers(str) {
        const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        let result = str.toString();
        for (let i = 0; i < 10; i++) {
            result = result.replace(new RegExp(arabicNumbers[i], 'g'), englishNumbers[i]);
        }
        return result;
    }

    function formatDualDate(date) {
        const gregorianDate = date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
        
        const hijriDate = date.toLocaleDateString('ar-SA-u-ca-islamic', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
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
    
        // توليد الباركود
        try {
            JsBarcode("#invoice-barcode", String(data.id).padStart(6, '0'), {
                format: "CODE128",
                width: 1,
                height: 40,
                displayValue: false,
                margin: 0
            });
        } catch (e) {
            console.error('Error generating barcode:', e);
        }
    
        document.getElementById('invoice-date').textContent = formatDualDate(data.date);

        // إضافة الوقت
        const formattedTime = data.date.toLocaleTimeString('ar-SA', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        });
        document.getElementById('invoice-time').textContent = toEnglishNumbers(formattedTime);

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
        
        const itemsCountBadge = document.getElementById('items-count-badge');
        if (data.items.length > 10) {
            itemsCountBadge.textContent = `إجمالي ${data.items.length} منتج في هذه الفاتورة`;
            itemsCountBadge.classList.remove('hidden');
        } else {
            itemsCountBadge.classList.add('hidden');
        }
        
        data.items.forEach((item, index) => {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200 invoice-item-row';
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

    // دالة الطباعة الحرارية
    function printThermal() {
        if (!currentInvoiceData) return;

        const invoiceDate = currentInvoiceData.date;
        const formattedDate = formatDualDate(invoiceDate);
        const formattedTime = toEnglishNumbers(invoiceDate.toLocaleTimeString('ar-SA', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        }));
        let thermalContent = `
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm">
    <title>فاتورة حرارية #${String(currentInvoiceData.id).padStart(6, '0')}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            width: 80mm;
            padding: 10mm 5mm;
            font-size: 11pt;
            line-height: 1.4;
            background: white;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 8mm;
            border-bottom: 2px dashed #000;
            padding-bottom: 5mm;
        }
        .shop-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 2mm;
        }
        .shop-info {
            font-size: 9pt;
            color: #333;
            margin: 1mm 0;
        }
        .invoice-info {
            margin: 5mm 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2mm 0;
            font-size: 10pt;
        }
        .info-label {
            font-weight: bold;
        }
        .customer-section {
            margin: 5mm 0;
            padding: 3mm;
            background: #f5f5f5;
            border-radius: 2mm;
            font-size: 10pt;
        }
        .items-table {
            width: 100%;
            margin: 5mm 0;
            border-collapse: collapse;
        }
        .items-header {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            padding: 2mm 0;
            font-weight: bold;
            font-size: 10pt;
        }
        .item-row {
            border-bottom: 1px dashed #ccc;
            padding: 2mm 0;
            font-size: 10pt;
        }
        .item-name {
            font-weight: bold;
            margin-bottom: 1mm;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            color: #555;
            font-size: 9pt;
        }
        .totals-section {
            margin: 5mm 0;
            border-top: 2px solid #000;
            padding-top: 3mm;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 2mm 0;
            font-size: 11pt;
        }
        .total-row.grand-total {
            font-size: 14pt;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 3mm;
            margin-top: 3mm;
        }
        .footer {
            text-align: center;
            margin-top: 8mm;
            border-top: 2px dashed #000;
            padding-top: 5mm;
            font-size: 10pt;
        }
        .thank-you {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 3mm;
        }
        .barcode {
            margin: 5mm 0;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 24pt;
            letter-spacing: 2mm;
        }
        @media print {
            body {
                width: 80mm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="shop-name">${shopName}</div>
        ${shopPhone ? `<div class="shop-info">📞 ${shopPhone}</div>` : ''}
        ${shopAddress ? `<div class="shop-info">📍 ${shopAddress}</div>` : ''}
    </div>

    <div class="invoice-info">
        <div class="info-row">
            <span class="info-label">رقم الفاتورة:</span>
            <span>#${String(currentInvoiceData.id).padStart(6, '0')}</span>
        </div>
        <div class="info-row">
            <span class="info-label">التاريخ:</span>
            <span>${formattedDate}</span>
        </div>
        <div class="info-row">
            <span class="info-label">الوقت:</span>
            <span>${formattedTime}</span>
        </div>
    </div>

    ${currentInvoiceData.customer ? `
    <div class="customer-section">
        <div style="font-weight: bold; margin-bottom: 2mm;">معلومات العميل:</div>
        <div>📝 ${currentInvoiceData.customer.name}</div>
        ${currentInvoiceData.customer.phone ? `<div>📞 ${currentInvoiceData.customer.phone}</div>` : ''}
    </div>
    ` : '<div class="customer-section"><div>💵 عميل نقدي</div></div>'}

    <div class="items-table">
        <div class="items-header">
            المنتجات (${currentInvoiceData.items.length})
        </div>
`;

        currentInvoiceData.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            thermalContent += `
        <div class="item-row">
            <div class="item-name">${index + 1}. ${item.name}</div>
            <div class="item-details">
                <span>${item.quantity} × ${parseFloat(item.price).toFixed(2)} ${currency}</span>
                <span style="font-weight: bold;">${itemTotal.toFixed(2)} ${currency}</span>
            </div>
        </div>
`;
        });

        thermalContent += `
    </div>

    <div class="totals-section">
        <div class="total-row">
            <span>المجموع الفرعي:</span>
            <span>${currentInvoiceData.subtotal.toFixed(2)} ${currency}</span>
        </div>
`;

        if (taxEnabled) {
            thermalContent += `
        <div class="total-row">
            <span>${taxLabel} (${(taxRate * 100).toFixed(0)}%):</span>
            <span>${currentInvoiceData.tax.toFixed(2)} ${currency}</span>
        </div>
`;
        }

        thermalContent += `
        <div class="total-row grand-total">
            <span>الإجمالي:</span>
            <span>${currentInvoiceData.total.toFixed(2)} ${currency}</span>
        </div>
    </div>

    <div style="text-align: center; margin: 5mm 0;">
        <canvas id="thermal-barcode"></canvas>
    </div>

    <div class="footer">
        <div class="thank-you">🌟 شكراً لثقتكم بنا 🌟</div>
        ${shopName ? `<div>${shopName}</div>` : ''}
        ${shopPhone ? `<div>هاتف: ${shopPhone}</div>` : ''}
        ${!shopName && !shopPhone ? '<div>تم التطوير بواسطة حمزة سعدي 2025</div>' : ''}
    </div>
</body>
</html>
`;

        const printWindow = window.open('', '_blank', 'width=302,height=500');
        printWindow.document.write(thermalContent);
        printWindow.document.close();
        
        // إضافة مكتبة JsBarcode للنافذة الجديدة
        const script = printWindow.document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
        script.onload = function() {
            try {
                JsBarcode(printWindow.document.getElementById('thermal-barcode'), String(currentInvoiceData.id).padStart(6, '0'), {
                    format: "CODE128",
                    width: 2,
                    height: 50,
                    displayValue: false
                });
            } catch (e) {
                console.error('Barcode error:', e);
            }
            
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
            }, 500);
        };
        printWindow.document.head.appendChild(script);
    }

    closeInvoiceModal.addEventListener('click', () => {
        invoiceModal.classList.add('hidden');
    });

    printInvoiceBtn.addEventListener('click', () => {
        window.print();
    });

    thermalPrintBtn.addEventListener('click', printThermal);

    downloadPdfBtn.addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        
        try {
            showToast('جاري إنشاء ملف PDF...', true);
            
            const scrollableDiv = document.querySelector('.invoice-items-scrollable');
            const originalMaxHeight = scrollableDiv.style.maxHeight;
            scrollableDiv.style.maxHeight = 'none';
            scrollableDiv.style.overflow = 'visible';
            
            const element = document.getElementById('invoice-print-area');
            
            const canvas = await html2canvas(element, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true
            });
            
            scrollableDiv.style.maxHeight = originalMaxHeight;
            scrollableDiv.style.overflow = 'auto';
            
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const imgWidth = pdfWidth;
            const imgHeight = (canvas.height * pdfWidth) / canvas.width;
            
            if (imgHeight > pdfHeight) {
                let heightLeft = imgHeight;
                let position = 0;
                let page = 0;
                
                while (heightLeft > 0) {
                    if (page > 0) {
                        pdf.addPage();
                    }
                    
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pdfHeight;
                    position -= pdfHeight;
                    page++;
                }
            } else {
                pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
            }
            
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
        txtContent += `المنتجات (${currentInvoiceData.items.length} منتج):\n`;
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
        txtContent += `شكرا لثقتكم بنا\n\n`;
        
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