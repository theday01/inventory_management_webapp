<?php
require_once 'session.php';
require_once 'db.php';
require_once 'src/language.php';

// Force dark mode for a premium look
$darkMode = '1';

// Get shop settings
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
$shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'Smart Shop';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopLogoUrl'");
$shopLogoUrl = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

$page_title = __('customer_display_title') ?? 'شاشة العميل';
$dir = get_dir();
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" dir="<?php echo $dir; ?>" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <?php if (!empty($shopLogoUrl)): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($shopLogoUrl); ?>">
    <?php endif; ?>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            DEFAULT: '#0f172a',
                            surface: '#1e293b',
                            card: '#334155',
                            border: 'rgba(255,255,255,0.1)'
                        },
                        primary: {
                            DEFAULT: '#3b82f6',
                            hover: '#2563eb',
                            glow: 'rgba(59, 130, 246, 0.5)'
                        },
                        accent: {
                            DEFAULT: '#10b981',
                            glow: 'rgba(16, 185, 129, 0.5)'
                        }
                    },
                    fontFamily: {
                        sans: ['Tajawal', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-in': 'slideIn 0.5s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        slideIn: {
                            '0%': { opacity: '0', transform: 'translateX(-20px)' },
                            '100%': { opacity: '1', transform: 'translateX(0)' },
                        }
                    }
                },
            },
        }
    </script>

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #0f172a;
            color: white;
            overflow: hidden;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }

        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .item-row {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .item-row:first-child {
            background: rgba(59, 130, 246, 0.1);
            border-left: 4px solid #3b82f6;
        }

        .gradient-text {
            background: linear-gradient(to right, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="h-screen w-screen flex flex-col relative selection:bg-primary selection:text-white">

    <!-- Background Elements -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-20%] left-[-10%] w-[600px] h-[600px] bg-primary/20 rounded-full blur-[120px] opacity-30 animate-pulse-slow"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[600px] h-[600px] bg-accent/20 rounded-full blur-[120px] opacity-30 animate-pulse-slow" style="animation-delay: 2s;"></div>
    </div>

    <!-- 1. IDLE / WELCOME SCREEN -->
    <div id="welcome-screen" class="absolute inset-0 z-50 flex flex-col items-center justify-center transition-all duration-700 bg-dark/95 backdrop-blur-xl">
        <div class="relative animate-float">
            <?php if (!empty($shopLogoUrl)): ?>
                <img src="<?php echo htmlspecialchars($shopLogoUrl); ?>" alt="Logo" class="w-48 h-48 md:w-64 md:h-64 object-contain rounded-full border-4 border-white/10 bg-white p-2 shadow-[0_0_60px_rgba(59,130,246,0.5)]">
            <?php else: ?>
                <div class="w-48 h-48 md:w-64 md:h-64 rounded-full border-4 border-white/10 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center shadow-[0_0_60px_rgba(59,130,246,0.3)] relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-tr from-primary/20 via-transparent to-accent/20 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                    <!-- Modern Store Icon SVG -->
                    <svg class="w-24 h-24 md:w-32 md:h-32 text-gray-300 drop-shadow-[0_0_15px_rgba(255,255,255,0.3)] transform transition-transform duration-700 group-hover:scale-110" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 21H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5 21V7L3 5L5 3H19L21 5L19 7V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 10C9 10 9.5 12 12 12C14.5 12 15 10 15 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path opacity="0.5" d="M12 21V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path opacity="0.5" d="M10 21H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            <?php endif; ?>
        </div>
        
        <h1 class="mt-12 text-5xl md:text-7xl font-bold text-center tracking-tight">
            <span class="gradient-text"><?php echo __('cd_welcome_title') ?? 'أهلاً وسهلاً بكم'; ?></span>
        </h1>
        
        <p class="mt-6 text-2xl text-gray-400 font-light tracking-wide">
            <?php echo __('cd_welcome_subtitle') ?? 'نسعد بخدمتكم دائماً'; ?>
        </p>

        <div class="mt-16 flex items-center gap-3 px-6 py-3 rounded-full bg-white/5 border border-white/10">
            <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-sm font-mono text-gray-400"><?php echo __('waiting_for_items'); ?></span>
        </div>
    </div>

    <!-- 2. MAIN ACTIVE INTERFACE -->
    <div id="main-interface" class="relative z-10 w-full h-full flex flex-col lg:flex-row opacity-0 transition-all duration-500 transform scale-95 hidden">
        
        <!-- LEFT PANEL: CART ITEMS -->
        <div class="w-full lg:w-[65%] flex-1 lg:h-full flex flex-col border-e border-white/10 bg-dark/50 backdrop-blur-md relative overflow-hidden">
            
            <!-- Header -->
            <header class="h-16 lg:h-24 px-4 lg:px-8 flex items-center justify-between border-b border-white/10 bg-dark/50 shrink-0">
                <div class="flex items-center gap-3 lg:gap-4">
                    <?php if (!empty($shopLogoUrl)): ?>
                        <img src="<?php echo htmlspecialchars($shopLogoUrl); ?>" class="w-8 h-8 lg:w-12 lg:h-12 rounded-lg bg-white p-1 object-contain">
                    <?php else: ?>
                        <div class="w-8 h-8 lg:w-12 lg:h-12 rounded-lg bg-primary/20 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-sm lg:text-base">store</span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h2 class="text-sm lg:text-xl font-bold text-white truncate max-w-[150px] lg:max-w-none"><?php echo htmlspecialchars($shopName); ?></h2>
                        <div class="hidden lg:flex items-center gap-2 text-gray-400 text-sm">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span><?php echo __('open_customer_screen'); ?></span>
                        </div>
                    </div>
                </div>
                <!-- Clock -->
                <div class="text-right">
                    <div id="clock-time" class="text-lg lg:text-3xl font-bold font-mono text-white">00:00</div>
                    <div id="clock-date" class="text-xs lg:text-sm text-gray-400">---</div>
                </div>
            </header>

            <!-- Column Headers (Hidden on Mobile) -->
            <div class="hidden lg:grid px-8 py-4 bg-white/5 border-b border-white/5 grid-cols-12 gap-4 text-gray-400 font-bold text-sm uppercase tracking-wider shrink-0">
                <div class="col-span-6"><?php echo __('cd_product'); ?></div>
                <div class="col-span-2 text-center"><?php echo __('cd_price'); ?></div>
                <div class="col-span-2 text-center"><?php echo __('cd_quantity'); ?></div>
                <div class="col-span-2 text-end"><?php echo __('cd_total'); ?></div>
            </div>

            <!-- Items List -->
            <div id="cart-items-container" class="flex-1 overflow-y-auto p-2 lg:p-4 space-y-2 pb-32 lg:pb-4">
                <!-- Items will be injected here via JS -->
            </div>
            
            <!-- Items Footer Count -->
            <div class="h-8 lg:h-12 border-t border-white/10 bg-dark/50 flex items-center justify-between px-4 lg:px-8 text-gray-400 text-xs lg:text-sm shrink-0">
                <span class="hidden lg:inline"><?php echo __('smart_shop_system'); ?></span>
                <span id="items-count">0 Items</span>
            </div>
        </div>

        <!-- RIGHT PANEL: TOTALS & SUMMARY -->
        <div class="w-full lg:w-[35%] h-auto lg:h-full bg-dark-surface flex flex-col shadow-[0_-10px_40px_rgba(0,0,0,0.5)] lg:shadow-2xl relative z-20 shrink-0 absolute bottom-0 lg:static border-t lg:border-t-0 lg:border-s border-white/10 rounded-t-3xl lg:rounded-none">
            
            <!-- Gradient Top Border (Desktop only or adjusted) -->
            <div class="h-1 w-full bg-gradient-to-r from-primary to-accent absolute top-0 left-0 lg:relative"></div>

            <!-- Expand Handle (Mobile) -->
            <div class="w-12 h-1 bg-white/20 rounded-full mx-auto mt-3 mb-1 lg:hidden"></div>

            <!-- Totals Section -->
            <div class="flex-1 p-4 lg:p-8 flex flex-col justify-center space-y-2 lg:space-y-6">
                
                <!-- Subtotal (Hidden on very small screens if needed, or kept compact) -->
                <div class="flex justify-between items-end text-sm lg:text-lg">
                    <span class="text-gray-400"><?php echo __('cd_subtotal'); ?></span>
                    <span id="display-subtotal" class="font-mono font-bold text-white">0.00</span>
                </div>

                <!-- Tax -->
                <div id="row-tax" class="flex justify-between items-end hidden text-sm lg:text-lg">
                    <span class="text-gray-400"><?php echo __('cd_tax'); ?></span>
                    <span id="display-tax" class="font-mono text-gray-300">0.00</span>
                </div>

                <!-- Discount -->
                <div id="row-discount" class="flex justify-between items-end hidden text-red-400 text-sm lg:text-lg">
                    <span class="flex items-center gap-2">
                        <span class="material-icons-round text-sm lg:text-base">local_offer</span>
                        <?php echo __('cd_discount'); ?>
                    </span>
                    <span id="display-discount" class="font-mono font-bold">-0.00</span>
                </div>

                <!-- Delivery -->
                <div id="row-delivery" class="flex justify-between items-end hidden text-blue-400 text-sm lg:text-lg">
                    <span class="flex items-center gap-2">
                        <span class="material-icons-round text-sm lg:text-base">local_shipping</span>
                        <?php echo __('cd_delivery'); ?>
                    </span>
                    <span id="display-delivery" class="font-mono font-bold">0.00</span>
                </div>

                <div class="h-px bg-white/10 my-2 lg:my-4"></div>

                <!-- Grand Total -->
                <div class="bg-primary/10 rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-primary/20 text-center transform transition-all lg:hover:scale-105 duration-300">
                    <span class="block text-primary-200 text-sm lg:text-xl font-medium mb-1 lg:mb-2 uppercase tracking-widest"><?php echo __('cd_total'); ?></span>
                    <div class="flex items-baseline justify-center gap-1 lg:gap-2 text-primary">
                        <span id="display-total" class="text-5xl lg:text-7xl font-extrabold tracking-tighter drop-shadow-lg">0.00</span>
                        <span class="text-lg lg:text-2xl font-bold"><?php echo $currency; ?></span>
                    </div>
                </div>

            </div>

            <!-- Footer Message (Hidden on mobile to save space) -->
            <div class="hidden lg:block p-6 bg-dark/30 text-center border-t border-white/5">
                <p class="text-gray-400 text-lg font-medium leading-relaxed">
                    <?php echo __('thank_you_visit'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- 3. SUCCESS / CHANGE DUE OVERLAY -->
    <div id="success-overlay" class="absolute inset-0 z-[60] bg-dark/95 backdrop-blur-xl flex flex-col items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="w-full max-w-2xl text-center transform scale-90 transition-transform duration-300" id="success-content">
            <div class="w-32 h-32 mx-auto bg-green-500 rounded-full flex items-center justify-center mb-8 shadow-[0_0_50px_rgba(16,185,129,0.5)]">
                <span class="material-icons-round text-7xl text-white">check</span>
            </div>
            
            <h2 class="text-4xl font-bold text-white mb-2"><?php echo __('cd_transaction_success'); ?></h2>
            
            <div class="mt-12 bg-white/5 rounded-3xl p-10 border border-white/10">
                <p class="text-gray-400 text-xl mb-4 uppercase tracking-widest"><?php echo __('cd_change_due'); ?></p>
                <div class="text-8xl font-extrabold text-green-400 tracking-tighter mb-2">
                    <span id="success-change">0.00</span>
                    <span class="text-4xl text-gray-500"><?php echo $currency; ?></span>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-4 text-left px-12">
                <div class="bg-white/5 p-4 rounded-xl">
                    <p class="text-gray-400 text-sm"><?php echo __('cd_total'); ?></p>
                    <p class="text-xl font-bold text-white" id="success-total">0.00</p>
                </div>
                <div class="bg-white/5 p-4 rounded-xl">
                    <p class="text-gray-400 text-sm"><?php echo __('cd_paid_amount'); ?></p>
                    <p class="text-xl font-bold text-white" id="success-paid">0.00</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const currency = '<?php echo $currency; ?>';
        const defaultLogo = 'src/img/default-product.png'; 
        
        // DOM Elements
        const welcomeScreen = document.getElementById('welcome-screen');
        const mainInterface = document.getElementById('main-interface');
        const successOverlay = document.getElementById('success-overlay');
        const cartContainer = document.getElementById('cart-items-container');
        
        // Broadcast Channel
        const channel = new BroadcastChannel('pos_display');

        // State
        let currentCart = [];
        let idleTimer = null;
        let isTransactionSuccess = false;

        // --- Helper: Escape HTML to prevent XSS ---
        function escapeHtml(text) {
            if (!text) return text;
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // --- Clock Functionality ---
        function updateClock() {
            const now = new Date();
            document.getElementById('clock-time').textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
            document.getElementById('clock-date').textContent = now.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'short' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- View Management ---
        function setView(view) {
            if (view === 'welcome') {
                welcomeScreen.classList.remove('opacity-0', 'hidden');
                mainInterface.classList.add('opacity-0', 'scale-95', 'hidden');
                mainInterface.classList.remove('flex');
                successOverlay.classList.add('hidden', 'opacity-0');
            } else if (view === 'cart') {
                welcomeScreen.classList.add('opacity-0', 'hidden');
                mainInterface.classList.remove('hidden', 'opacity-0', 'scale-95');
                mainInterface.classList.add('flex');
                successOverlay.classList.add('hidden', 'opacity-0');
            } else if (view === 'success') {
                successOverlay.classList.remove('hidden');
                // Small delay to allow display:block to apply before opacity transition
                setTimeout(() => {
                    successOverlay.classList.remove('opacity-0');
                    document.getElementById('success-content').classList.remove('scale-90');
                    document.getElementById('success-content').classList.add('scale-100');
                }, 10);
            }
        }

        // --- Format Currency ---
        function formatMoney(amount) {
            return parseFloat(amount).toFixed(2);
        }

        // --- Update Display Logic ---
        function updateDisplay(data) {
            const cart = data.cart || [];
            const totals = data.totals || {};

            // If empty, show welcome screen
            if (cart.length === 0) {
                if (isTransactionSuccess) return; // Don't interrupt success screen
                setView('welcome');
                return;
            }

            // New items added, cancel success screen
            isTransactionSuccess = false;
            setView('cart');

            // --- Render Items ---
            cartContainer.innerHTML = '';
            
            // Reverse loop to show newest on top
            [...cart].reverse().forEach((item, index) => {
                const total = item.price * item.quantity;
                const isNew = index === 0; 
                const safeName = escapeHtml(item.name);
                const safeBarcode = escapeHtml(item.barcode);

                const div = document.createElement('div');
                // Adjusted grid/flex for responsive layout
                div.className = `item-row flex lg:grid lg:grid-cols-12 gap-3 lg:gap-4 items-center p-3 lg:p-4 rounded-xl border border-white/5 bg-white/5 ${isNew ? 'animate-slide-in' : ''}`;
                
                const imgUrl = item.image ? item.image : defaultLogo;
                
                div.innerHTML = `
                    <div class="flex-1 lg:col-span-6 flex items-center gap-3 lg:gap-4 overflow-hidden">
                        <div class="w-10 h-10 lg:w-14 lg:h-14 rounded-lg bg-white p-1 shrink-0 overflow-hidden">
                            <img src="${imgUrl}" class="w-full h-full object-contain" onerror="this.src='${defaultLogo}'">
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-sm lg:text-lg text-white truncate leading-tight">${safeName}</h3>
                            <div class="flex items-center gap-2 lg:hidden mt-1">
                                <span class="text-xs font-mono text-gray-400">${formatMoney(item.price)}</span>
                                <span class="text-[10px] text-gray-600">x</span>
                                <span class="text-xs font-bold text-white bg-white/10 px-1.5 rounded">${item.quantity}</span>
                            </div>
                            ${safeBarcode ? `<span class="hidden lg:block text-xs font-mono text-gray-500">${safeBarcode}</span>` : ''}
                        </div>
                    </div>
                    
                    <div class="hidden lg:block lg:col-span-2 text-center font-mono text-lg text-gray-300">
                        ${formatMoney(item.price)}
                    </div>
                    
                    <div class="hidden lg:block lg:col-span-2 text-center">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-dark text-white font-bold border border-white/10 shadow-inner">
                            ${item.quantity}
                        </span>
                    </div>
                    
                    <div class="shrink-0 lg:col-span-2 text-end">
                        <span class="font-bold text-base lg:text-xl text-primary font-mono">${formatMoney(total)}</span>
                        <span class="text-[10px] lg:text-xs text-gray-500 ml-0.5 lg:ml-1">${currency}</span>
                    </div>
                `;
                cartContainer.appendChild(div);
            });

            document.getElementById('items-count').textContent = cart.length + ' Items';

            // --- Render Totals ---
            document.getElementById('display-subtotal').textContent = formatMoney(totals.subtotal);
            document.getElementById('display-total').textContent = formatMoney(totals.total);

            // Conditional Rows
            toggleRow('row-tax', totals.tax, 'display-tax');
            toggleRow('row-discount', totals.discount, 'display-discount', true);
            toggleRow('row-delivery', totals.delivery, 'display-delivery');
        }

        function toggleRow(rowId, amount, displayId, isNegative = false) {
            const row = document.getElementById(rowId);
            const display = document.getElementById(displayId);
            if (amount && parseFloat(amount) > 0) {
                row.classList.remove('hidden');
                display.textContent = (isNegative ? '-' : '') + formatMoney(amount);
            } else {
                row.classList.add('hidden');
            }
        }

        // --- Success Screen ---
        function showSuccess(data) {
            isTransactionSuccess = true;
            const total = parseFloat(data.totals?.total || 0);
            const change = parseFloat(data.change_due || 0);
            
            // Assume paid is total + change
            const paid = total + change;

            document.getElementById('success-change').textContent = formatMoney(change);
            if (data.totals && data.totals.total) {
                document.getElementById('success-total').textContent = formatMoney(data.totals.total);
                document.getElementById('success-paid').textContent = formatMoney(paid);
            }
            
            setView('success');

            // Auto revert to welcome after 15 seconds
            clearTimeout(idleTimer);
            idleTimer = setTimeout(() => {
                isTransactionSuccess = false;
                setView('welcome');
            }, 15000);
        }

        // --- Event Listener ---
        channel.onmessage = (event) => {
            const data = event.data;
            console.log('Received:', data.action, data);

            if (data.action === 'update_cart') {
                updateDisplay(data);
            } else if (data.action === 'checkout_complete') {
                // If we get checkout_complete, we should show the success screen.
                // Note: processCheckout in pos.php broadcasts 'checkout_complete' with { change_due: ... }
                // but it might NOT pass the full `totals` object if it wasn't explicitly added in pos.php call.
                // Let's re-check pos.php logic.
                // In pos.php: broadcastCartUpdate('checkout_complete', { change_due: paymentData.changeDue });
                // broadcastCartUpdate adds `totals` to the payload automatically. So we are safe.
                showSuccess(data);
            } else if (data.action === 'clear_cart') {
                setView('welcome');
            }
        };

        // Initial State
        setView('welcome');

    </script>
</body>
</html>
