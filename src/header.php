<?php
require_once 'session.php';
require_once 'db.php';

// Get dark mode setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'darkMode'");
$darkMode = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '1';
$isDark = ($darkMode == '1');

// Get stock alert interval setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'stockAlertInterval'");
$stockAlertInterval = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '20';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="<?php echo $isDark ? 'dark' : ''; ?>">

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
    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
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

    <script>
        function showToast(message, isSuccess = true) {
            const toast = document.getElementById('toast-notification');
            const toastContent = document.getElementById('toast-content');
            const toastMessage = document.getElementById('toast-message');
            const toastIcon = document.getElementById('toast-icon');

            toastMessage.textContent = message;

            if (isSuccess) {
                toastContent.className =
                    'flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md bg-green-500 text-white';
                toastIcon.textContent = 'check_circle';
            } else {
                toastContent.className =
                    'flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md bg-red-500 text-white';
                toastIcon.textContent = 'error';
            }

            toast.classList.remove('opacity-0', '-translate-y-10', 'pointer-events-none');
            toast.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');

            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', '-translate-y-10');
                setTimeout(() => {
                    toast.classList.add('pointer-events-none');
                }, 300);
            }, 3000);
        }

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
            const NOTIFICATION_INTERVAL = 5 * 60 * 1000; // 5 دقائق
            let notificationPermission = 'default';
            
            // طلب إذن الإشعارات
            async function requestNotificationPermission() {
                if ('Notification' in window) {
                    try {
                        notificationPermission = await Notification.requestPermission();
                        console.log('✅ حالة إذن الإشعارات:', notificationPermission);
                    } catch (error) {
                        console.error('❌ خطأ في طلب إذن الإشعارات:', error);
                    }
                }
            }
            
            // إرسال إشعار Windows
            function sendWindowsNotification(title, body, icon = '⚠️') {
                if ('Notification' in window && notificationPermission === 'granted') {
                    try {
                        const notification = new Notification(title, {
                            body: body,
                            icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="75" font-size="75">' + icon + '</text></svg>',
                            badge: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><text y="75" font-size="75">🏪</text></svg>',
                            tag: 'low-stock-alert',
                            requireInteraction: true,
                            vibrate: [200, 100, 200],
                            dir: 'rtl',
                            lang: 'ar'
                        });
                        
                        notification.onclick = function() {
                            window.focus();
                            notification.close();
                            if (!window.location.pathname.includes('products.php')) {
                                window.location.href = 'products.php';
                            }
                        };
                        
                        return notification;
                    } catch (error) {
                        console.error('❌ خطأ في إرسال الإشعار:', error);
                    }
                }
                return null;
            }
            
            async function checkLowStock() {
                if (isCheckingStock) return;
                isCheckingStock = true;
                
                try {
                    const response = await fetch('api.php?action=getLowStockProducts');
                    const result = await response.json();
                    
                    if (result.success && result.data.length > 0) {
                        const lastNotification = localStorage.getItem('lastLowStockNotification');
                        const now = Date.now();
                        
                        if (!lastNotification || (now - parseInt(lastNotification)) > 300000) {
                            showLowStockAlert(result);
                            localStorage.setItem('lastLowStockNotification', now.toString());
                        }
                    }
                } catch (error) {
                    console.error('❌ خطأ في التحقق من المخزون:', error);
                } finally {
                    isCheckingStock = false;
                }
            }
            
            function showLowStockAlert(data) {
                const outOfStockCount = data.outOfStockCount || 0;
                const criticalCount = data.criticalCount || 0;
                const lowCount = data.lowCount || 0;
                const totalCount = data.count || 0;
                
                let message = '';
                let notificationTitle = 'تنبيه المخزون';
                let notificationBody = '';
                let notificationIcon = '📦';
                let isUrgent = false;
                
                if (outOfStockCount > 0) {
                    message = `🚫 تحذير عاجل: ${outOfStockCount} منتج نفذت كميته!`;
                    notificationTitle = '🚫 تحذير عاجل - مخزون منتهي!';
                    notificationBody = `${outOfStockCount} منتج نفذت كميته تماماً.\nيجب طلب مخزون فوراً!`;
                    notificationIcon = '🚫';
                    isUrgent = true;
                    
                    if (data.outOfStock && data.outOfStock.length > 0) {
                        const products = data.outOfStock.slice(0, 3).map(p => p.name).join('، ');
                        notificationBody += `\n\n📦 منتجات منتهية:\n${products}`;
                        if (data.outOfStock.length > 3) {
                            notificationBody += ` و${data.outOfStock.length - 3} منتج آخر`;
                        }
                    }
                } 
                else if (criticalCount > 0) {
                    message = `⚠️ تحذير: ${criticalCount} منتج على وشك النفاذ!`;
                    notificationTitle = '⚠️ تحذير - مخزون حرج!';
                    notificationBody = `${criticalCount} منتج بكمية حرجة (1-5 قطع).\nيجب إعادة التخزين قريباً.`;
                    notificationIcon = '⚠️';
                    isUrgent = true;
                    
                    if (data.critical && data.critical.length > 0) {
                        const products = data.critical.slice(0, 3).map(p => `${p.name} (${p.quantity})`).join('، ');
                        notificationBody += `\n\n⚠️ منتجات حرجة:\n${products}`;
                        if (data.critical.length > 3) {
                            notificationBody += ` و${data.critical.length - 3} منتج آخر`;
                        }
                    }
                } 
                else if (lowCount > 0) {
                    message = `📦 تنبيه: ${lowCount} منتج بكمية منخفضة`;
                    notificationTitle = '📦 تنبيه - مخزون منخفض';
                    notificationBody = `${lowCount} منتج بكمية منخفضة (6-10 قطع).\nراقب هذه المنتجات.`;
                    notificationIcon = '📦';
                }
                
                if (message) {
                    showToast(message, !isUrgent);
                    sendWindowsNotification(notificationTitle, notificationBody, notificationIcon);
                    
                    const soundEnabled = <?php 
                        $result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'soundNotifications'");
                        echo ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '0';
                    ?>;
                    
                    if (soundEnabled == 1) {
                        playNotificationSound(isUrgent);
                    }
                    
                    console.log('📊 حالة المخزون:', {
                        منتهية: outOfStockCount,
                        حرجة: criticalCount,
                        منخفضة: lowCount,
                        الإجمالي: totalCount
                    });
                }
            }
            
            function playNotificationSound(isUrgent) {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                if (isUrgent) {
                    oscillator.frequency.value = 1000;
                    oscillator.type = 'square';
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.setValueAtTime(0.1, audioContext.currentTime + 0.1);
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime + 0.2);
                    gainNode.gain.setValueAtTime(0.1, audioContext.currentTime + 0.3);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.6);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.6);
                } else {
                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.5);
                }
            }
            
            // التحقق عند تحميل الصفحة
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', async () => {
                    await requestNotificationPermission();
                    checkLowStock();
                });
            } else {
                requestNotificationPermission().then(() => checkLowStock());
            }
            
            setTimeout(checkLowStock, 10000);
            setInterval(checkLowStock, NOTIFICATION_INTERVAL);
            
            // دالة عامة لتفعيل الإشعارات
            window.enableStockNotifications = async function() {
                await requestNotificationPermission();
                if (notificationPermission === 'granted') {
                    showToast('✅ تم تفعيل إشعارات Windows بنجاح', true);
                    checkLowStock();
                } else if (notificationPermission === 'denied') {
                    showToast('❌ تم رفض إذن الإشعارات. يرجى تفعيلها من إعدادات المتصفح', false);
                }
            };
        })();
    </script>

    <div class="flex h-screen overflow-hidden">