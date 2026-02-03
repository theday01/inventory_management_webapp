<?php
require_once 'session.php';
require_once 'db.php';
require_once 'src/language.php';

// Force dark mode for a cleaner display
$isDark = true;
$darkMode = '1';

// Get shop settings for display
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
$shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'Smart Shop';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopLogoUrl'");
$shopLogoUrl = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopFavicon'");
$shopFavicon = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

$page_title = __('customer_display_title') ?? 'شاشة العميل';
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" dir="<?php echo get_dir(); ?>" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($shopFavicon)): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($shopFavicon); ?>">
    <?php endif; ?>
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            DEFAULT: '#0E1116',
                            surface: '#1F2937',
                            glass: 'rgba(14, 17, 22, 0.7)',
                            border: '#374151'
                        },
                        primary: {
                            DEFAULT: '#3B82F6',
                            hover: '#2563EB',
                        },
                        accent: {
                            DEFAULT: '#84CC16',
                        }
                    },
                    fontFamily: {
                        sans: ['Tajawal', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #0E1116;
            color: white;
            overflow: hidden;
        }
        
        .glass-panel {
            background-color: rgba(31, 41, 55, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(31, 41, 55, 0.5);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 10px;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-dark text-white h-screen flex flex-col">

    <!-- Header -->
    <header class="h-20 bg-dark-surface/80 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 shrink-0">
        <div class="flex items-center gap-4">
            <?php if (!empty($shopLogoUrl)): ?>
                <img src="<?php echo htmlspecialchars($shopLogoUrl); ?>" alt="Logo" class="w-12 h-12 rounded-full border border-white/10 object-contain bg-white">
            <?php else: ?>
                <div class="w-12 h-12 bg-primary/20 rounded-full flex items-center justify-center text-primary border border-white/10">
                    <span class="material-icons-round text-2xl">store</span>
                </div>
            <?php endif; ?>
            <h1 class="text-2xl font-bold tracking-wide"><?php echo htmlspecialchars($shopName); ?></h1>
        </div>
        <div class="flex items-center gap-2 text-gray-400">
            <span class="material-icons-round text-xl">access_time</span>
            <span id="current-time" class="text-xl font-mono font-bold">--:--</span>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex overflow-hidden relative">
        <!-- Background Effects -->
        <div class="absolute top-[-20%] right-[-10%] w-[500px] h-[500px] bg-primary/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-[-20%] left-[-10%] w-[500px] h-[500px] bg-accent/10 rounded-full blur-[100px] pointer-events-none"></div>

        <!-- Empty State (Welcome Screen) -->
        <div id="welcome-screen" class="absolute inset-0 flex flex-col items-center justify-center z-20 bg-dark transition-opacity duration-500">
            <div class="text-center animate-fade-in">
                <?php if (!empty($shopLogoUrl)): ?>
                    <img src="<?php echo htmlspecialchars($shopLogoUrl); ?>" alt="Logo" class="w-48 h-48 mx-auto mb-8 rounded-full border-4 border-white/10 object-contain bg-white shadow-2xl shadow-primary/20">
                <?php else: ?>
                    <div class="w-40 h-40 bg-primary/20 rounded-full flex items-center justify-center text-primary border-4 border-white/10 mx-auto mb-8 shadow-2xl shadow-primary/20">
                        <span class="material-icons-round text-7xl">store</span>
                    </div>
                <?php endif; ?>
                <h2 class="text-5xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">
                    <?php echo __('welcome_message') ?? 'مرحباً بكم'; ?>
                </h2>
                <p class="text-xl text-gray-400"><?php echo __('waiting_for_items') ?? 'بانتظار إضافة المنتجات...'; ?></p>
            </div>
            
            <!-- Change Due (if payment processed) -->
            <div id="change-due-container" class="hidden mt-8 p-8 bg-dark-surface/80 backdrop-blur-xl border border-accent/30 rounded-3xl text-center animate-fade-in shadow-2xl shadow-accent/10 transform scale-110">
                <span class="block text-accent font-bold text-2xl mb-2"><?php echo __('change_due') ?? 'الباقي للعميل'; ?></span>
                <div class="text-6xl font-extrabold text-white tracking-tight">
                    <span id="display-change">0.00</span>
                    <span class="text-2xl text-gray-400 ml-2 align-top"><?php echo $currency; ?></span>
                </div>
            </div>
        </div>

        <!-- Active Cart Layout -->
        <div id="active-cart-screen" class="w-full h-full flex opacity-0 transition-opacity duration-500 z-10 hidden">
            
            <!-- Product List (Left/Right depending on RTL) -->
            <div class="flex-1 flex flex-col border-e border-white/5 bg-dark/50 backdrop-blur-sm">
                <div class="p-6 border-b border-white/5 bg-white/5">
                    <div class="grid grid-cols-12 gap-4 text-gray-400 font-bold text-lg">
                        <div class="col-span-6"><?php echo __('product') ?? 'المنتج'; ?></div>
                        <div class="col-span-2 text-center"><?php echo __('price') ?? 'السعر'; ?></div>
                        <div class="col-span-2 text-center"><?php echo __('quantity') ?? 'الكمية'; ?></div>
                        <div class="col-span-2 text-end"><?php echo __('total') ?? 'الإجمالي'; ?></div>
                    </div>
                </div>
                
                <div id="cart-items-list" class="flex-1 overflow-y-auto p-4 space-y-3">
                    <!-- Items will be injected here -->
                </div>
            </div>

            <!-- Totals Sidebar -->
            <div class="w-[400px] bg-dark-surface border-s border-white/5 flex flex-col shadow-2xl z-30">
                <div class="flex-1 p-8 space-y-6 flex flex-col justify-center">
                    
                    <!-- Subtotal -->
                    <div class="flex justify-between items-center text-gray-400 text-xl">
                        <span><?php echo __('subtotal') ?? 'المجموع الفرعي'; ?></span>
                        <span id="display-subtotal" class="font-mono">0.00</span>
                    </div>

                    <!-- Tax -->
                    <div id="tax-row" class="flex justify-between items-center text-gray-400 text-xl hidden">
                        <span><?php echo __('tax') ?? 'الضريبة'; ?></span>
                        <span id="display-tax" class="font-mono">0.00</span>
                    </div>

                    <!-- Delivery -->
                    <div id="delivery-row" class="flex justify-between items-center text-gray-400 text-xl hidden">
                        <span><?php echo __('delivery') ?? 'التوصيل'; ?></span>
                        <span id="display-delivery" class="font-mono text-white">0.00</span>
                    </div>

                    <!-- Discount -->
                    <div id="discount-row" class="flex justify-between items-center text-red-400 text-xl hidden">
                        <span><?php echo __('discount') ?? 'الخصم'; ?></span>
                        <span id="display-discount" class="font-mono font-bold">-0.00</span>
                    </div>

                    <div class="h-px bg-white/10 my-4"></div>

                    <!-- Total -->
                    <div class="text-center">
                        <span class="block text-gray-400 text-2xl mb-2"><?php echo __('total_amount') ?? 'الإجمالي النهائي'; ?></span>
                        <div class="text-6xl font-extrabold text-primary tracking-tight">
                            <span id="display-total">0.00</span>
                            <span class="text-3xl text-gray-400 ml-2 align-top"><?php echo $currency; ?></span>
                        </div>
                    </div>
                    
                </div>

                <!-- Footer Promo/Ad Space -->
                <div class="h-32 bg-gradient-to-r from-primary/10 to-accent/10 border-t border-white/5 flex items-center justify-center p-4">
                    <p class="text-center text-gray-300 text-lg font-medium">
                        <?php echo __('thank_you_visit') ?? 'شكراً لزيارتكم! نتمنى لكم يوماً سعيداً'; ?>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        const currency = '<?php echo $currency; ?>';
        const channel = new BroadcastChannel('pos_display');
        
        const welcomeScreen = document.getElementById('welcome-screen');
        const activeCartScreen = document.getElementById('active-cart-screen');
        const cartItemsList = document.getElementById('cart-items-list');
        
        // Elements for totals
        const displaySubtotal = document.getElementById('display-subtotal');
        const displayTax = document.getElementById('display-tax');
        const displayDelivery = document.getElementById('display-delivery');
        const displayDiscount = document.getElementById('display-discount');
        const displayTotal = document.getElementById('display-total');
        const displayChange = document.getElementById('display-change');
        
        const taxRow = document.getElementById('tax-row');
        const deliveryRow = document.getElementById('delivery-row');
        const discountRow = document.getElementById('discount-row');
        const changeDueContainer = document.getElementById('change-due-container');
        
        let isShowingChange = false;

        // Clock
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: false 
            });
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Listen for messages from POS
        channel.onmessage = (event) => {
            const data = event.data;
            
            if (data.action === 'update_cart') {
                updateDisplay(data);
            } else if (data.action === 'checkout_complete') {
                isShowingChange = true;
                showChangeDue(data.change_due);
                
                // Hide active screen to show welcome screen background with change due overlay
                activeCartScreen.classList.add('hidden', 'opacity-0');
                activeCartScreen.classList.remove('flex');
                welcomeScreen.classList.remove('hidden', 'opacity-0');

                // Reset to welcome screen (hide change) after delay
                setTimeout(() => {
                    isShowingChange = false;
                    changeDueContainer.classList.add('hidden');
                }, 15000); // Show change for 15 seconds then reset
            } else if (data.action === 'clear_cart') {
                isShowingChange = false;
                showWelcomeScreen();
            }
        };

        function showWelcomeScreen() {
            if (isShowingChange) return; // Don't interrupt change display
            welcomeScreen.classList.remove('hidden', 'opacity-0');
            activeCartScreen.classList.add('hidden', 'opacity-0');
            activeCartScreen.classList.remove('flex');
            changeDueContainer.classList.add('hidden');
        }

        function showActiveScreen() {
            welcomeScreen.classList.add('hidden', 'opacity-0');
            activeCartScreen.classList.remove('hidden', 'opacity-0');
            activeCartScreen.classList.add('flex');
        }
        
        function showChangeDue(amount) {
            if (amount > 0) {
                displayChange.textContent = parseFloat(amount).toFixed(2);
                changeDueContainer.classList.remove('hidden');
            }
        }

        function escapeHtml(text) {
            if (text == null) return '';
            return text.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function updateDisplay(data) {
            const cart = data.cart || [];
            const totals = data.totals || {};

            if (cart.length === 0) {
                showWelcomeScreen();
                return;
            }
            
            // New items added, stop showing change
            isShowingChange = false;

            showActiveScreen();
            changeDueContainer.classList.add('hidden'); // Hide change due when cart is active

            // Render Items
            cartItemsList.innerHTML = '';
            cart.forEach(item => {
                const total = (parseFloat(item.price) * parseInt(item.quantity)).toFixed(2);
                const itemEl = document.createElement('div');
                itemEl.className = 'grid grid-cols-12 gap-4 items-center bg-white/5 p-4 rounded-xl border border-white/5 animate-fade-in mb-2';
                itemEl.innerHTML = `
                    <div class="col-span-6">
                        <h3 class="font-bold text-xl truncate">${escapeHtml(item.name)}</h3>
                        ${item.barcode ? `<span class="text-xs text-gray-500 font-mono">${escapeHtml(item.barcode)}</span>` : ''}
                    </div>
                    <div class="col-span-2 text-center font-mono text-lg text-gray-300">
                        ${parseFloat(item.price).toFixed(2)}
                    </div>
                    <div class="col-span-2 text-center">
                        <span class="bg-primary/20 text-primary font-bold px-3 py-1 rounded-lg text-xl min-w-[3rem] inline-block">
                            ${item.quantity}
                        </span>
                    </div>
                    <div class="col-span-2 text-end font-bold text-xl text-white font-mono">
                        ${total} <span class="text-xs text-gray-500">${currency}</span>
                    </div>
                `;
                cartItemsList.prepend(itemEl); // Add newest to top
            });

            // Update Totals
            displaySubtotal.textContent = `${parseFloat(totals.subtotal || 0).toFixed(2)} ${currency}`;
            
            if (totals.tax > 0) {
                taxRow.classList.remove('hidden');
                displayTax.textContent = `${parseFloat(totals.tax).toFixed(2)} ${currency}`;
            } else {
                taxRow.classList.add('hidden');
            }

            if (totals.delivery > 0) {
                deliveryRow.classList.remove('hidden');
                displayDelivery.textContent = `${parseFloat(totals.delivery).toFixed(2)} ${currency}`;
            } else {
                deliveryRow.classList.add('hidden');
            }

            if (totals.discount > 0) {
                discountRow.classList.remove('hidden');
                displayDiscount.textContent = `-${parseFloat(totals.discount).toFixed(2)} ${currency}`;
            } else {
                discountRow.classList.add('hidden');
            }

            displayTotal.textContent = parseFloat(totals.total || 0).toFixed(2);
        }
    </script>
</body>
</html>
