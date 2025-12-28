<?php
$page_title = 'نقطة البيع';
$current_page = 'pos.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch currency setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

// Fetch tax settings
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxEnabled'");
$taxEnabled = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '1';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxRate'");
$taxRate = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '20';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxLabel'");
$taxLabel = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'TVA';
?>

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

        <!-- Cart Items -->
        <div id="cart-items" class="flex-1 overflow-y-auto p-4 space-y-3">
            <!-- Cart items will be loaded here -->
        </div>

        <!-- Totals & Checkout -->
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
                <button
                    class="button-secondary bg-white/5 hover:bg-white/10 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">pause</span>
                    تعليق
                </button>
                <button
                    id="clear-cart-btn"
                    class="button-danger bg-red-500/10 hover:bg-red-500/20 text-red-500 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">delete_outline</span>
                    إلغاء
                </button>
            </div>

            <button
                id="checkout-btn"
                class="w-full bg-accent hover:bg-lime-500 text-dark-surface py-4 rounded-xl font-bold text-lg shadow-lg shadow-accent/20 flex items-center justify-center gap-2 transition-all hover:scale-[1.02]">
                <span class="material-icons-round">payments</span>
                دفع (space)
            </button>
        </div>
    </aside>

    <!-- Products Section (Right) -->
    <div class="flex-1 flex flex-col h-full relative">
        <!-- Background Blobs -->
        <div
            class="absolute top-[10%] right-[10%] w-[400px] h-[400px] bg-primary/5 rounded-full blur-[80px] pointer-events-none">
        </div>

        <!-- Header -->
        <header
            class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-6 z-10 shrink-0">
            <div class="flex items-center gap-4 flex-1">
                <a href="dashboard.php"
                    class="p-2 text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-xl transition-colors">
                    <span class="material-icons-round">arrow_forward</span>
                </a>
                <div class="relative flex-1 max-w-md">
                    <span
                        class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                    <input type="text" id="product-search-input" placeholder="بحث عن منتج..."
                        class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                    <button id="scan-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white">
                        <span class="material-icons-round">qr_code_scanner</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-3" id="category-filters">
                <button
                    class="px-4 py-2 bg-primary text-white rounded-xl font-medium text-sm shadow-lg shadow-primary/20">الكل</button>
                <button
                    class="px-4 py-2 bg-white/5 text-gray-400 hover:text-white rounded-xl font-medium text-sm hover:bg-white/10 transition-all">إلكترونيات</button>
                <button
                    class="px-4 py-2 bg-white/5 text-gray-400 hover:text-white rounded-xl font-medium text-sm hover:bg-white/10 transition-all">ملابس</button>
                <button
                    class="px-4 py-2 bg-white/5 text-gray-400 hover:text-white rounded-xl font-medium text-sm hover:bg-white/10 transition-all">إكسسوارات</button>
            </div>
        </header>

        <!-- Products Grid -->
        <div class="flex-1 overflow-y-auto p-6 z-10">
            <div id="products-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <!-- Products will be loaded here -->
            </div>
        </div>
    </div>
</main>

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
                    <input type="text" id="customer-address" placeholder="العنوان" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 col-span-2">
                </div>
                <button type="submit" class="w-full bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all mt-4">إضافة عميل</button>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const productsGrid = document.getElementById('products-grid');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartSubtotal = document.getElementById('cart-subtotal');
    const cartTax = document.getElementById('cart-tax');
    const cartTotal = document.getElementById('cart-total');
    const searchInput = document.getElementById('product-search-input');
    const scanBarcodeBtn = document.getElementById('scan-barcode-btn');
    const barcodeScannerModal = document.getElementById('barcode-scanner-modal');
    const closeBarcodeScannerModalBtn = document.getElementById('close-barcode-scanner-modal');
    const videoElement = document.getElementById('barcode-video');
    const clearCartBtn = document.getElementById('clear-cart-btn');
    const checkoutBtn = document.getElementById('checkout-btn');
    let codeReader;

    const customerModal = document.getElementById('customer-modal');
    const closeCustomerModalBtn = document.getElementById('close-customer-modal');
    const customerSelection = document.getElementById('customer-selection');
    const customerSearchInput = document.getElementById('customer-search');
    const customerList = document.getElementById('customer-list');
    const addCustomerForm = document.getElementById('add-customer-form');
    const customerNameDisplay = document.getElementById('customer-name-display');
    const customerDetailDisplay = document.getElementById('customer-detail-display');
    const customerAvatar = document.getElementById('customer-avatar');

    let cart = [];
    let allProducts = [];
    let selectedCustomer = null;
    
    const taxEnabled = document.getElementById('cart-tax') !== null;
    const taxRateDisplay = document.getElementById('tax-rate-display');
    const taxRate = taxRateDisplay ? parseFloat(taxRateDisplay.textContent) / 100 : 0;
    const currency = cartSubtotal.textContent.split(' ')[1] || 'MAD';

    async function loadProducts() {
        try {
            const response = await fetch(`api.php?action=getProducts`);
            const result = await response.json();
            if (result.success) {
                allProducts = result.data;
                displayProducts(allProducts);
            } else {
                showToast(result.message || 'فشل في تحميل المنتجات', false);
            }
        } catch (error) {
            console.error('خطأ في تحميل المنتجات:', error);
            showToast('حدث خطأ في تحميل المنتجات', false);
        }
    }

    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();
        const filteredProducts = allProducts.filter(product =>
            product.name.toLowerCase().includes(searchTerm) ||
            (product.barcode && product.barcode.includes(searchTerm))
        );
        displayProducts(filteredProducts);
    });

    function displayProducts(products) {
        productsGrid.innerHTML = '';
        if (products.length === 0) {
            productsGrid.innerHTML = '<p class="text-center py-4 text-gray-500 col-span-full">لا توجد منتجات لعرضها.</p>';
            return;
        }
        products.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'bg-dark-surface/50 border border-white/5 rounded-2xl p-4 flex flex-col items-center justify-center text-center hover:border-primary/50 transition-all cursor-pointer';
            productCard.dataset.productId = product.id;
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

    checkoutBtn.addEventListener('click', () => {
        if (cart.length === 0) {
            showToast('السلة فارغة!', false);
            return;
        }
        
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax;
        
        const confirmMessage = `المجموع الفرعي: ${subtotal.toFixed(2)} ${currency}\n` +
            (taxEnabled ? `الضريبة: ${tax.toFixed(2)} ${currency}\n` : '') +
            `الإجمالي: ${total.toFixed(2)} ${currency}\n\n` +
            `هل تريد إتمام عملية الدفع؟`;
        
        if (confirm(confirmMessage)) {
            cart = [];
            updateCart();
            showToast('تم إتمام عملية البيع بنجاح!', true);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.code === 'Space' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            checkoutBtn.click();
        }
    });

    scanBarcodeBtn.addEventListener('click', () => {
        barcodeScannerModal.classList.remove('hidden');
        startBarcodeScanner();
    });

    closeBarcodeScannerModalBtn.addEventListener('click', () => {
        barcodeScannerModal.classList.add('hidden');
        stopBarcodeScanner();
    });

    function startBarcodeScanner() {
        codeReader = new ZXing.BrowserMultiFormatReader();
        codeReader.listVideoInputDevices()
            .then((videoInputDevices) => {
                if (videoInputDevices.length === 0) {
                    showToast('لم يتم العثور على كاميرا', false);
                    return;
                }
                const firstDeviceId = videoInputDevices[0].deviceId;
                codeReader.decodeFromVideoDevice(firstDeviceId, 'barcode-video', (result, err) => {
                    if (result) {
                        searchInput.value = result.text;
                        stopBarcodeScanner();
                        barcodeScannerModal.classList.add('hidden');
                        
                        const product = allProducts.find(p => p.barcode === result.text);
                        if (product) {
                            addProductToCart(product);
                        }
                    }
                    if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.error(err);
                    }
                });
            })
            .catch((err) => {
                console.error(err);
                showToast('فشل في تشغيل الكاميرا', false);
            });
    }

    function stopBarcodeScanner() {
        if (codeReader) {
            codeReader.reset();
        }
    }

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
            } else {
                showToast(result.message || 'فشل في تحميل العملاء', false);
            }
        } catch (error) {
            console.error('خطأ في تحميل العملاء:', error);
            showToast('حدث خطأ في تحميل العملاء', false);
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
            address: document.getElementById('customer-address').value,
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

    loadProducts();
});
</script>

<?php require_once 'src/footer.php'; ?>