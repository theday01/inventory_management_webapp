<?php
require_once 'session.php';
require_once 'db.php';
require_once __DIR__ . '/language.php';

// Get shop name setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
$shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'Smart Shop';

// Get dark mode setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'darkMode'");
$darkMode = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '1';
$isDark = ($darkMode == '1');

// Get stock alert interval setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'stockAlertInterval'");
$stockAlertInterval = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '20';
?>

<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" dir="<?php echo get_dir(); ?>" class="<?php echo $isDark ? 'dark' : ''; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Smart Shop'; ?></title>
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
                        light: {
                            DEFAULT: '#FFFFFF',
                            surface: '#F9FAFB',
                            glass: 'rgba(255, 255, 255, 0.7)',
                            border: '#E5E7EB'
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
    <style>
        .glass-panel {
            background-color: rgba(31, 41, 55, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .dark .glass-panel {
            background-color: rgba(31, 41, 55, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        html:not(.dark) .glass-panel {
            background-color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Toggle Switch Styling */
        .toggle-checkbox {
            position: absolute;
            opacity: 0;
        }
        
        .toggle-checkbox + .toggle-label {
            display: block;
            position: relative;
            cursor: pointer;
            outline: none;
            user-select: none;
        }
        
        /* Dark Mode Toggle */
        .dark .toggle-label {
            background-color: #374151;
        }
        
        .dark .toggle-checkbox:checked + .toggle-label {
            background-color: #3B82F6;
        }
        
        .dark .toggle-checkbox + .toggle-label:before {
            background-color: #1F2937;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }
        
        .dark .toggle-checkbox:checked + .toggle-label:before {
            background-color: #FFFFFF;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        /* Light Mode Toggle */
        html:not(.dark) .toggle-label {
            background-color: #D1D5DB !important;
        }
        
        html:not(.dark) .toggle-checkbox:checked + .toggle-label {
            background-color: #3B82F6 !important;
        }
        
        html:not(.dark) .toggle-checkbox + .toggle-label:before {
            background-color: #FFFFFF !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15) !important;
        }
        
        html:not(.dark) .toggle-checkbox:checked + .toggle-label:before {
            background-color: #FFFFFF !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
        }
        
        /* Toggle Animation */
        .toggle-checkbox + .toggle-label:before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .toggle-checkbox:checked + .toggle-label:before {
            left: 26px;
        }

        /* Light mode specific styles */
        html:not(.dark) {
            background-color: #F3F4F6;
        }

        html:not(.dark) body {
            background-color: #F3F4F6;
            color: #111827;
        }

        /* Override dark mode text colors in light mode */
        html:not(.dark) .text-white {
            color: #111827 !important;
        }

        html:not(.dark) .text-gray-300,
        html:not(.dark) .text-gray-400,
        html:not(.dark) .text-gray-500 {
            color: #6B7280 !important;
        }

        html:not(.dark) .bg-dark,
        html:not(.dark) .bg-dark-surface {
            background-color: #FFFFFF !important;
        }

        html:not(.dark) .border-white\/5,
        html:not(.dark) .border-white\/10 {
            border-color: rgba(0, 0, 0, 0.1) !important;
        }

        /* Light mode input styles */
        html:not(.dark) input,
        html:not(.dark) textarea,
        html:not(.dark) select {
            background-color: #FFFFFF !important;
            border-color: #D1D5DB !important;
            color: #111827 !important;
        }

        html:not(.dark) input::placeholder,
        html:not(.dark) textarea::placeholder {
            color: #9CA3AF !important;
        }

        /* Light mode button styles */
        html:not(.dark) .bg-dark\/50 {
            background-color: #F9FAFB !important;
        }

        html:not(.dark) .bg-white\/5,
        html:not(.dark) .bg-white\/10 {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }

        html:not(.dark) .hover\:bg-white\/5:hover,
        html:not(.dark) .hover\:bg-white\/10:hover {
            background-color: rgba(0, 0, 0, 0.1) !important;
        }

        /* Light mode table styles */
        html:not(.dark) table tbody tr {
            border-color: rgba(0, 0, 0, 0.1) !important;
        }

        html:not(.dark) table thead tr {
            background-color: #F9FAFB !important;
            border-color: rgba(0, 0, 0, 0.1) !important;
        }

        /* Light mode sidebar */
        html:not(.dark) aside {
            background-color: #FFFFFF !important;
            border-color: rgba(0, 0, 0, 0.1) !important;
        }

        /* Light mode header */
        html:not(.dark) header {
            background-color: rgba(255, 255, 255, 0.8) !important;
            border-color: rgba(0, 0, 0, 0.1) !important;
        }

        /* Light mode cards */
        html:not(.dark) .bg-dark-surface\/50,
        html:not(.dark) .bg-dark-surface\/60 {
            background-color: #FFFFFF !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Light mode modals */
        html:not(.dark) .bg-dark-surface {
            background-color: #FFFFFF !important;
        }

        /* Blobs in light mode */
        html:not(.dark) .bg-primary\/5,
        html:not(.dark) .bg-primary\/20,
        html:not(.dark) .bg-accent\/5,
        html:not(.dark) .bg-accent\/10 {
            opacity: 0.3;
        }

        /* Scrollbar Styling - Dark Mode */
        .dark ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .dark ::-webkit-scrollbar-track {
            background: rgba(31, 41, 55, 0.5);
            border-radius: 10px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.8);
        }

        /* Scrollbar Styling - Light Mode */
        html:not(.dark) ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        html:not(.dark) ::-webkit-scrollbar-track {
            background: #F3F4F6;
            border-radius: 10px;
        }

        html:not(.dark) ::-webkit-scrollbar-thumb {
            background: #3B82F6;
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        html:not(.dark) ::-webkit-scrollbar-thumb:hover {
            background: #2563EB;
        }

        /* Firefox Scrollbar - Dark Mode */
        .dark * {
            scrollbar-width: thin;
            scrollbar-color: rgba(59, 130, 246, 0.5) rgba(31, 41, 55, 0.5);
        }

        /* Firefox Scrollbar - Light Mode */
        html:not(.dark) * {
            scrollbar-width: thin;
            scrollbar-color: #3B82F6 #F3F4F6;
        }

        /* Light Mode Button Styles */
        html:not(.dark) .bg-primary {
            background-color: #FFFFFF !important;
            color: #3B82F6 !important;
            border: 2px solid #3B82F6 !important;
        }

        html:not(.dark) .bg-primary:hover,
        html:not(.dark) .hover\:bg-primary-hover:hover {
            background-color: #3B82F6 !important;
            color: #FFFFFF !important;
            border-color: #3B82F6 !important;
        }

        html:not(.dark) .bg-gray-700,
        html:not(.dark) .bg-gray-600 {
            background-color: #FFFFFF !important;
            color: #6B7280 !important;
            border: 2px solid #D1D5DB !important;
        }

        html:not(.dark) .bg-gray-700:hover,
        html:not(.dark) .bg-gray-600:hover {
            background-color: #F3F4F6 !important;
            color: #374151 !important;
            border-color: #9CA3AF !important;
        }

        html:not(.dark) .bg-accent {
            background-color: #FFFFFF !important;
            color: #84CC16 !important;
            border: 2px solid #84CC16 !important;
        }

        html:not(.dark) .bg-accent:hover,
        html:not(.dark) .hover\:bg-lime-500:hover {
            background-color: #84CC16 !important;
            color: #FFFFFF !important;
            border-color: #84CC16 !important;
        }

        html:not(.dark) .bg-red-500,
        html:not(.dark) .bg-red-500\/10 {
            background-color: #FFFFFF !important;
            color: #EF4444 !important;
            border: 2px solid #EF4444 !important;
        }

        html:not(.dark) .bg-red-500:hover,
        html:not(.dark) .hover\:bg-red-500\/20:hover {
            background-color: #EF4444 !important;
            color: #FFFFFF !important;
            border-color: #EF4444 !important;
        }

        html:not(.dark) .bg-purple-600 {
            background-color: #FFFFFF !important;
            color: #9333EA !important;
            border: 2px solid #9333EA !important;
        }

        html:not(.dark) .bg-purple-600:hover {
            background-color: #9333EA !important;
            color: #FFFFFF !important;
            border-color: #9333EA !important;
        }

        html:not(.dark) .bg-primary\/10 {
            background-color: #FFFFFF !important;
            color: #3B82F6 !important;
            border: 2px solid #E5E7EB !important;
        }

        html:not(.dark) .bg-primary\/10:hover {
            background-color: #F3F4F6 !important;
            color: #2563EB !important;
            border-color: #3B82F6 !important;
        }

        /* Light mode shadow adjustments for buttons */
        html:not(.dark) .shadow-lg {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        }

        html:not(.dark) .shadow-primary\/20 {
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2), 0 2px 4px -1px rgba(59, 130, 246, 0.1) !important;
        }

        /* Ensure button text is always visible */
        html:not(.dark) button span,
        html:not(.dark) a span {
            color: inherit !important;
        }

        html:not(.dark) .text-white {
            color: inherit !important;
        }

        /* Light mode icon colors in buttons */
        html:not(.dark) .bg-primary .material-icons-round {
            color: #3B82F6 !important;
        }

        html:not(.dark) .bg-primary:hover .material-icons-round {
            color: #FFFFFF !important;
        }

        html:not(.dark) .bg-gray-700 .material-icons-round,
        html:not(.dark) .bg-gray-600 .material-icons-round {
            color: #6B7280 !important;
        }

        html:not(.dark) .bg-gray-700:hover .material-icons-round,
        html:not(.dark) .bg-gray-600:hover .material-icons-round {
            color: #374151 !important;
        }

        html:not(.dark) .bg-accent .material-icons-round {
            color: #84CC16 !important;
        }

        html:not(.dark) .bg-accent:hover .material-icons-round {
            color: #FFFFFF !important;
        }

        html:not(.dark) .bg-red-500 .material-icons-round,
        html:not(.dark) .bg-red-500\/10 .material-icons-round {
            color: #EF4444 !important;
        }

        html:not(.dark) .bg-red-500:hover .material-icons-round {
            color: #FFFFFF !important;
        }

        html:not(.dark) .bg-purple-600 .material-icons-round {
            color: #9333EA !important;
        }

        html:not(.dark) .bg-purple-600:hover .material-icons-round {
            color: #FFFFFF !important;
        }

        /* Light mode - specific button text colors */
        html:not(.dark) button.bg-primary,
        html:not(.dark) a.bg-primary {
            font-weight: 700;
        }

        /* Light mode form buttons */
        html:not(.dark) button[type="submit"],
        html:not(.dark) button[type="button"] {
            font-weight: 600;
            transition: all 0.2s ease;
        }

        /* Light mode - ensure link buttons work correctly */
        html:not(.dark) a.bg-primary,
        html:not(.dark) a.bg-accent,
        html:not(.dark) a.bg-gray-700 {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        /* Light mode - Search and action buttons */
        html:not(.dark) #search-submit-btn,
        html:not(.dark) #clear-search-btn,
        html:not(.dark) button[type="submit"].bg-primary,
        html:not(.dark) button[type="button"].bg-gray-600 {
            border-width: 2px;
            border-style: solid;
        }

        /* Light Mode - Product Table Row Colors for Low Stock */
        html:not(.dark) .bg-red-900\/20 {
            background-color: rgba(254, 202, 202, 0.8) !important;
        }

        html:not(.dark) .bg-red-900\/30,
        html:not(.dark) .bg-red-900\/20:hover {
            background-color: rgba(252, 165, 165, 0.9) !important;
        }

        html:not(.dark) .bg-orange-900\/20 {
            background-color: rgba(254, 215, 170, 0.8) !important;
        }

        html:not(.dark) .bg-orange-900\/30,
        html:not(.dark) .bg-orange-900\/20:hover {
            background-color: rgba(253, 186, 116, 0.9) !important;
        }

        /* Light Mode - Low Stock Quantity Text Colors */
        html:not(.dark) .text-red-400 {
            color: #DC2626 !important;
        }

        html:not(.dark) .text-orange-400 {
            color: #EA580C !important;
        }
        
        /* Light Mode - Product Table Row Colors for Out of Stock */
        html:not(.dark) .bg-gray-900\/40 {
            background-color: rgba(229, 231, 235, 0.8) !important;
        }

        html:not(.dark) .bg-gray-900\/50,
        html:not(.dark) .bg-gray-900\/40:hover {
            background-color: rgba(209, 213, 219, 0.9) !important;
        }

        /* Light Mode - Out of Stock Text Color */
        html:not(.dark) .text-gray-500 {
            color: #6B7280 !important;
        }

        /* Light Mode - Status Badges */
        html:not(.dark) .bg-gray-500\/20 {
            background-color: rgba(107, 114, 128, 0.2) !important;
        }

        html:not(.dark) .text-gray-400 {
            color: #9CA3AF !important;
        }

        html:not(.dark) .bg-red-500\/20 {
            background-color: rgba(239, 68, 68, 0.2) !important;
        }

        html:not(.dark) .bg-orange-500\/20 {
            background-color: rgba(249, 115, 22, 0.2) !important;
        }

        html:not(.dark) .bg-yellow-500\/20 {
            background-color: rgba(234, 179, 8, 0.2) !important;
        }

        html:not(.dark) .bg-yellow-900\/10 {
            background-color: rgba(250, 204, 21, 0.15) !important;
        }

        html:not(.dark) .bg-yellow-900\/20,
        html:not(.dark) .bg-yellow-900\/10:hover {
            background-color: rgba(250, 204, 21, 0.25) !important;
        }

        /* Light Mode - Stock Modal Backgrounds */
        html:not(.dark) .bg-gray-500\/10 {
            background-color: rgba(156, 163, 175, 0.15) !important;
        }

        html:not(.dark) .border-gray-500\/30 {
            border-color: rgba(107, 114, 128, 0.3) !important;
        }

        html:not(.dark) .border-gray-500\/40 {
            border-color: rgba(107, 114, 128, 0.4) !important;
        }

        /* Global Loading Screen */
        #loading-screen {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        #loading-screen.active {
            opacity: 1;
            pointer-events: all;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-left-color: #3B82F6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            margin-top: 20px;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }
    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>

<body class="font-sans min-h-screen <?php echo $isDark ? 'bg-dark text-white' : 'bg-gray-100 text-gray-900'; ?>">

    <!-- نظام الرسائل الموحد -->
    <div id="toast-notification"
        class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[9999] transition-all duration-300 ease-out opacity-0 -translate-y-10 pointer-events-none">
        <div id="toast-content" class="flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md">
            <span id="toast-icon" class="material-icons-round text-2xl"></span>
            <span id="toast-message" class="font-bold text-lg"></span>
        </div>
    </div>

    <!-- Global Loading Screen -->
    <div id="loading-screen">
        <div class="spinner"></div>
        <p class="loading-text" id="loading-message"><?php echo __('processing'); ?></p>
    </div>

    <script>
        window.translations = <?php echo json_encode($translations); ?>;
        window.__ = function(key) {
            return window.translations[key] || key;
        };

        window.showToast = function(message, type) {
            // Backward compatibility: handle boolean input
            if (typeof type === 'boolean') {
                type = type ? 'success' : 'error';
            }
            
            // Smart detection for error messages if type is not explicitly provided
            if (typeof type === 'undefined') {
                const lowerMsg = String(message).toLowerCase();
                const errorKeywords = [
                    'error', 'fail', 'wrong', 'denied', 'unauthorized', // English
                    'خطأ', 'فشل', 'مشكلة', 'تنبيه', 'عذراً', 'غير مصرح', 'مرفوض', 'تعذر', 'نفذت' // Arabic
                ];
                // Default to 'success', but switch to 'error' if keyword found
                type = errorKeywords.some(keyword => lowerMsg.includes(keyword)) ? 'error' : 'success';
            }

            const toast = document.getElementById('toast-notification');
            const toastContent = document.getElementById('toast-content');
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = document.getElementById('toast-icon');

            toastMessage.textContent = message;

            // Reset base classes
            let baseClasses = 'flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md text-white border';

            if (type === 'success') {
                toastContent.className = `${baseClasses} bg-emerald-600 border-emerald-400/30`;
                toastIcon.textContent = 'check_circle';
            } else if (type === 'error') {
                toastContent.className = `${baseClasses} bg-rose-600 border-rose-400/30`;
                toastIcon.textContent = 'error';
            } else if (type === 'info') {
                // Orange/Yellow gradient for info/warning
                toastContent.className = `${baseClasses} bg-gradient-to-r from-orange-500 to-yellow-500 border-orange-400/30`;
                toastIcon.textContent = 'info';
            } else {
                // Default fallback (success)
                toastContent.className = `${baseClasses} bg-emerald-600 border-emerald-400/30`;
                toastIcon.textContent = 'check_circle';
            }

            toast.classList.remove('opacity-0', '-translate-y-10', 'pointer-events-none');
            toast.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');

            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', '-translate-y-10');
                setTimeout(() => {
                    toast.classList.add('pointer-events-none');
                }, 300);
            }, 5000);
        }

        // Global Loading Functions
        window.showLoading = function(message = null) {
            if (!message) message = window.__('processing');
            const loadingScreen = document.getElementById('loading-screen');
            const loadingMessage = document.getElementById('loading-message');
            if (loadingScreen && loadingMessage) {
                loadingMessage.textContent = message;
                loadingScreen.classList.add('active');
            }
        };

        window.hideLoading = function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                loadingScreen.classList.remove('active');
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('success')) {
                const successMsg = urlParams.get('success');
                showToast(successMsg, true);

                urlParams.delete('success');
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, '', newUrl);
            }

            if (urlParams.has('error')) {
                const errorMsg = urlParams.get('error');
                showToast(errorMsg, false);

                urlParams.delete('error');
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, '', newUrl);
            }
        });
    </script>

    <script>
        // نظام التذكير بالمنتجات منخفضة المخزون مع إشعارات Windows

        (function() {
            let isCheckingStock = false;
            let stockNotificationPermission = 'default';
            let lastStockCheckDate = '';
            
            // طلب إذن الإشعارات
            async function requestStockNotificationPermission() {
                if ('Notification' in window && stockNotificationPermission !== 'granted') {
                    try {
                        stockNotificationPermission = await Notification.requestPermission();
                    } catch (error) {
                        console.error('❌ خطأ في طلب إذن إشعارات المخزون:', error);
                    }
                }
            }
            
            // إرسال إشعار Windows
            function sendStockNotification(message, count) {
                if ('Notification' in window && stockNotificationPermission === 'granted') {
                    try {
                        const notification = new Notification('⚠️ ' + window.__('stock_alert'), {
                            body: message,
                            icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="75" font-size="75">📦</text></svg>',
                            tag: 'stock-alert',
                            requireInteraction: count > 10,
                            dir: '<?php echo get_dir(); ?>',
                            lang: '<?php echo get_locale(); ?>'
                        });
                        
                        notification.onclick = function() {
                            window.focus();
                            notification.close();
                            if (!window.location.pathname.includes('products.php')) {
                                window.location.href = 'products.php?stock_status=low_stock';
                            }
                        };
                        
                        return notification;
                    } catch (error) {
                        console.error('❌ خطأ في إرسال إشعار المخزون:', error);
                    }
                }
                return null;
            }
            
            // فحص المخزون المنخفض
            async function checkLowStock() {
                if (isCheckingStock) return;
                const throttleKey = 'stock_notify_last_ts';
                const now = Date.now();
                const lastTs = parseInt(localStorage.getItem(throttleKey) || '0', 10);
                const fifteenMinutes = 15 * 60 * 1000;
                if (lastTs && (now - lastTs) < fifteenMinutes) return;
                
                isCheckingStock = true;
                
                try {
                    const response = await fetch('api.php?action=getLowStockProducts');
                    const result = await response.json();
                    
                    if (result.success && result.count > 0) {
                        const message = window.__('low_stock_msg').replace('%d', result.count);
                        localStorage.setItem(throttleKey, String(Date.now()));
                        
                        // عرض Toast
                        if (typeof showToast === 'function') {
                            showToast(message, false);
                        }
                        
                        // إرسال إشعار Windows
                        sendStockNotification(message, result.count);
                    }
                } catch (error) {
                    console.error('❌ خطأ في فحص المخزون:', error);
                } finally {
                    isCheckingStock = false;
                }
            }
            
            // التحقق عند تحميل الصفحة فقط
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', async () => {
                    await requestStockNotificationPermission();
                    setTimeout(checkLowStock, 10000); // بعد 10 ثوان من التحميل
                });
            } else {
                requestStockNotificationPermission().then(() => {
                    setTimeout(checkLowStock, 10000);
                });
            }
            
            // دالة لتفعيل إشعارات المخزون يدوياً
            window.enableStockNotifications = async function() {
                await requestStockNotificationPermission();
                if (stockNotificationPermission === 'granted') {
                    // إعادة تعيين التاريخ للسماح بالفحص
                    lastStockCheckDate = '';
                    showToast('✅ ' + window.__('stock_notif_enabled'), true);
                    checkLowStock();
                } else if (stockNotificationPermission === 'denied') {
                    showToast('❌ ' + window.__('notif_perm_denied'), false);
                }
            };
            
            console.log('📦 نظام إشعارات المخزون - فحص يومي واحد فقط');
        })();
        
        (function() {
            let isCheckingRental = false;
            const RENTAL_CHECK_INTERVAL = 21600000; // كل 6 ساعات (بالميلي ثانية)
            let rentalNotificationPermission = 'default';
            let lastCheckDate = '';
            
            // طلب إذن الإشعارات
            async function requestRentalNotificationPermission() {
                if ('Notification' in window && rentalNotificationPermission !== 'granted') {
                    try {
                        rentalNotificationPermission = await Notification.requestPermission();
                        console.log('✅ حالة إذن إشعارات الإيجار:', rentalNotificationPermission);
                    } catch (error) {
                        console.error('❌ خطأ في طلب إذن إشعارات الإيجار:', error);
                    }
                }
            }
            
            // إرسال إشعار Windows للإيجار
            function sendRentalNotification(message, isUrgent = false) {
                if ('Notification' in window && rentalNotificationPermission === 'granted') {
                    try {
                        const icon = isUrgent ? '🚨' : '🏠';
                        const title = isUrgent ? window.__('rental_urgent_alert') : window.__('rental_reminder');
                        
                        const notification = new Notification(title, {
                            body: message,
                            icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="75" font-size="75">' + icon + '</text></svg>',
                            badge: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="75" font-size="75">🏠</text></svg>',
                            tag: 'rental-reminder',
                            requireInteraction: isUrgent,
                            vibrate: isUrgent ? [300, 100, 300, 100, 300] : [200, 100, 200],
                            dir: '<?php echo get_dir(); ?>',
                            lang: '<?php echo get_locale(); ?>'
                        });
                        
                        notification.onclick = function() {
                            window.focus();
                            notification.close();
                            if (!window.location.pathname.includes('settings.php')) {
                                window.location.href = 'settings.php';
                            }
                        };
                        
                        return notification;
                    } catch (error) {
                        console.error('❌ خطأ في إرسال إشعار الإيجار:', error);
                    }
                }
                return null;
            }
            
            // فحص موعد الإيجار
            async function checkRentalDue() {
                if (isCheckingRental) return;
                const dayKey = 'rental_notify_day';
                const countKey = 'rental_notify_count';
                const lastKey = 'rental_notify_last_ts';
                const now = Date.now();
                const today = new Date().toDateString();
                const storedDay = localStorage.getItem(dayKey) || '';
                let count = parseInt(localStorage.getItem(countKey) || '0', 10);
                if (storedDay !== today) { localStorage.setItem(dayKey, today); count = 0; localStorage.setItem(countKey, '0'); }
                const lastTs = parseInt(localStorage.getItem(lastKey) || '0', 10);
                const fiveHours = 5 * 60 * 60 * 1000;
                if (count >= 2) return;
                if (lastTs && (now - lastTs) < fiveHours) return;
                
                isCheckingRental = true;
                
                try {
                    const response = await fetch('api.php?action=checkRentalDue');
                    const result = await response.json();
                    
                    if (result.success && result.notification_sent) {
                        const daysUntilDue = result.days_until_due || 0;
                        const isUrgent = daysUntilDue <= 1;
                        localStorage.setItem(countKey, String(count + 1));
                        localStorage.setItem(lastKey, String(Date.now()));
                        
                        // عرض Toast في الواجهة
                        if (typeof showToast === 'function') {
                            showToast(result.message, !isUrgent);
                        }
                        
                        // إرسال إشعار Windows
                        sendRentalNotification(result.message, isUrgent);
                        
                        // تشغيل صوت تنبيه إذا كان مفعلاً
                        const soundEnabled = <?php 
                            $result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'soundNotifications'");
                            echo ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '0';
                        ?>;
                        
                        if (soundEnabled == 1) {
                            playRentalNotificationSound(isUrgent);
                        }
                        
                        console.log('📅 حالة الإيجار:', {
                            أيام_متبقية: daysUntilDue,
                            رسالة: result.message,
                            عاجل: isUrgent
                        });
                    }
                } catch (error) {
                    console.error('❌ خطأ في التحقق من الإيجار:', error);
                } finally {
                    isCheckingRental = false;
                }
            }
            
            // تشغيل صوت تنبيه مخصص للإيجار
            function playRentalNotificationSound(isUrgent) {
                try {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    if (isUrgent) {
                        // صوت تنبيه عاجل (متكرر وأعلى)
                        oscillator.frequency.value = 1200;
                        oscillator.type = 'square';
                        
                        gainNode.gain.setValueAtTime(0.4, audioContext.currentTime);
                        
                        for (let i = 0; i < 3; i++) {
                            gainNode.gain.setValueAtTime(0.4, audioContext.currentTime + (i * 0.3));
                            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime + (i * 0.3) + 0.15);
                        }
                        
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1);
                        
                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 1);
                    } else {
                        const osc1 = audioContext.createOscillator();
                        const g1 = audioContext.createGain();
                        osc1.type = 'sine';
                        osc1.frequency.value = 660;
                        g1.gain.setValueAtTime(0.0, audioContext.currentTime);
                        g1.gain.linearRampToValueAtTime(0.15, audioContext.currentTime + 0.02);
                        g1.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.6);
                        osc1.connect(g1); g1.connect(audioContext.destination);
                        osc1.start(audioContext.currentTime); osc1.stop(audioContext.currentTime + 0.6);
                        
                        const osc2 = audioContext.createOscillator();
                        const g2 = audioContext.createGain();
                        osc2.type = 'sine';
                        osc2.frequency.value = 880;
                        g2.gain.setValueAtTime(0.0, audioContext.currentTime + 0.35);
                        g2.gain.linearRampToValueAtTime(0.12, audioContext.currentTime + 0.38);
                        g2.gain.exponentialRampToValueAtTime(0.001, audioContext.currentTime + 0.95);
                        osc2.connect(g2); g2.connect(audioContext.destination);
                        osc2.start(audioContext.currentTime + 0.35); osc2.stop(audioContext.currentTime + 0.95);
                    }
                } catch (error) {
                    console.error('❌ خطأ في تشغيل الصوت:', error);
                }
            }
            
            // التحقق عند تحميل الصفحة فقط (لا يوجد فحص دوري)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', async () => {
                    await requestRentalNotificationPermission();
                    setTimeout(checkRentalDue, 5000);
                });
            } else {
                requestRentalNotificationPermission().then(() => {
                    setTimeout(checkRentalDue, 5000);
                });
            }
            
            // تم إلغاء الفحص الدوري لتقليل الإزعاج
            // يتم الفحص فقط عند تحميل الصفحة
            
            // دالة عامة لتفعيل إشعارات الإيجار يدوياً
            window.enableRentalNotifications = async function() {
                await requestRentalNotificationPermission();
                if (rentalNotificationPermission === 'granted') {
                    showToast('✅ ' + window.__('rental_notif_enabled'), true);
                    checkRentalDue();
                } else if (rentalNotificationPermission === 'denied') {
                    showToast('❌ ' + window.__('rental_notif_denied'), false);
                }
            };
            
            // دالة لفحص الإيجار يدوياً
            window.checkRentalNow = function() {
                showToast(window.__('checking_rental'), true);
                checkRentalDue();
            };
            
            // معلومات للمطورين
            console.log('🏠 نظام تذكير الإيجار v2.0 تم تحميله');
            console.log('📋 الميزات الجديدة:');
            console.log('  ✅ دعم التأجير الشهري والسنوي');
            console.log('  ✅ حساب تلقائي لموعد الدفع التالي');
            console.log('  ✅ إشعارات Windows متقدمة');
            console.log('  ✅ أصوات تنبيه مخصصة');
            console.log('');
            console.log('📞 الأوامر المتاحة:');
            console.log('  window.enableRentalNotifications() - تفعيل الإشعارات');
            console.log('  window.checkRentalNow() - فحص الإيجار الآن');
        })();


        // دالة عرض Modal التأكيد المخصص
        window.showConfirmModal = function(title, message, onConfirm, onCancel = null) {
            return new Promise((resolve) => {
                const modal = document.getElementById('global-confirm-modal');
                const titleEl = document.getElementById('global-confirm-title');
                const textEl = document.getElementById('global-confirm-text');
                const confirmBtn = document.getElementById('global-confirm-btn');
                const cancelBtn = document.getElementById('global-cancel-btn');
                
                if (!modal) {
                    console.error('Modal not found');
                    resolve(false);
                    return;
                }
                
                titleEl.textContent = title;
                textEl.textContent = message;
                
                modal.classList.remove('hidden');
                
                const handleConfirm = () => {
                    modal.classList.add('hidden');
                    cleanup();
                    if (onConfirm) onConfirm();
                    resolve(true);
                };
                
                const handleCancel = () => {
                    modal.classList.add('hidden');
                    cleanup();
                    if (onCancel) onCancel();
                    resolve(false);
                };
                
                const cleanup = () => {
                    confirmBtn.removeEventListener('click', handleConfirm);
                    cancelBtn.removeEventListener('click', handleCancel);
                };
                
                confirmBtn.addEventListener('click', handleConfirm);
                cancelBtn.addEventListener('click', handleCancel);
                
                // إغلاق عند النقر خارج Modal
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        handleCancel();
                    }
                });
            });
        };

    </script>

    <div class="flex h-screen overflow-hidden">

<!-- Global Confirm Modal - أضف هذا في نهاية header.php قبل </body> -->
<div id="global-confirm-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-md border border-white/10 animate-scale-in">
        <div class="p-6 border-b border-white/5">
            <h3 id="global-confirm-title" class="text-xl font-bold text-white flex items-center gap-2">
                <span class="material-icons-round text-yellow-500">warning</span>
                <?php echo __('confirm_action'); ?>
            </h3>
        </div>
        
        <div class="p-6">
            <p id="global-confirm-text" class="text-gray-300 text-lg"></p>
        </div>
        
        <div class="p-6 border-t border-white/5 flex justify-end gap-3">
            <button id="global-cancel-btn" 
                    class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2.5 rounded-xl font-bold transition-all hover:-translate-y-0.5">
                <?php echo __('cancel'); ?>
            </button>
            <button id="global-confirm-btn" 
                    class="bg-red-500 hover:bg-red-600 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-red-500/20 transition-all hover:-translate-y-0.5">
                <?php echo __('confirm'); ?>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes scale-in {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-scale-in {
    animation: scale-in 0.2s ease-out;
}
</style>