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

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopLogoUrl'");
$shopLogoUrl = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopCity'");
$shopCity = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';
// Get sound notifications setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'soundNotifications'");
$soundEnabled = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '0';
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopLogoUrl'");
$shopLogoUrl = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'invoiceShowLogo'");
$invoiceShowLogo = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '0';

$locationParts = [];
if (!empty($shopCity)) $locationParts[] = $shopCity;
if (!empty($shopAddress)) $locationParts[] = $shopAddress;
$fullLocation = implode('، ', $locationParts);

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'deliveryInsideCity'");
$deliveryInsideCity = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '10';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'deliveryOutsideCity'");
$deliveryOutsideCity = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '30';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'deliveryHomeCity'");
$deliveryHomeCity = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'low_quantity_alert'");
$lowAlert = ($result && $result->num_rows > 0) ? (int)$result->fetch_assoc()['setting_value'] : 10;

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'critical_quantity_alert'");
$criticalAlert = ($result && $result->num_rows > 0) ? (int)$result->fetch_assoc()['setting_value'] : 5;
?>

<style>
    /* Radio button custom styling */
    input[type="radio"]:checked + div {
        color: #3B82F6;
    }

    /* Light mode delivery type cards */
    html:not(.dark) label:has(input[name="delivery-type"]) {
        background-color: #F9FAFB !important;
        border-color: #E5E7EB !important;
    }

    html:not(.dark) label:has(input[name="delivery-type"]:checked) {
        background-color: rgba(59, 130, 246, 0.1) !important;
        border-color: #3B82F6 !important;
    }

    html:not(.dark) label:has(input[name="delivery-type"]) .text-white {
        color: #111827 !important;
    }

    html:not(.dark) label:has(input[name="delivery-type"]) .text-gray-400 {
        color: #6B7280 !important;
    }

    /* Custom Confirmation Modal Styling */
    #custom-confirm-modal .bg-dark-surface {
        background: rgba(31, 41, 55, 0.95);
    }

    html:not(.dark) #custom-confirm-modal .bg-dark-surface {
        background: rgba(255, 255, 255, 0.98);
        border-color: #E5E7EB !important;
    }

    html:not(.dark) #custom-confirm-modal .text-white {
        color: #111827 !important;
    }

    html:not(.dark) #custom-confirm-modal .bg-blue-500\/20 {
        background-color: rgba(59, 130, 246, 0.1) !important;
    }

    html:not(.dark) #custom-confirm-modal .text-blue-400 {
        color: #3B82F6 !important;
    }

    html:not(.dark) #custom-confirm-modal #confirm-cancel-btn {
        background-color: #F3F4F6;
        color: #374151;
    }

    html:not(.dark) #custom-confirm-modal #confirm-cancel-btn:hover {
        background-color: #E5E7EB;
    }

    #custom-confirm-modal:not(.hidden) {
        animation: fadeIn 0.2s ease-out;
    }

    #custom-confirm-modal:not(.hidden) > div {
        animation: scaleIn 0.2s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes scaleIn {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

@media print {
    /* 1. إعدادات الصفحة الأساسية */
    @page { 
        size: A4 portrait;
        margin: 10mm;
    }

    html, body {
        height: auto !important;
        overflow: visible !important;
        background-color: white !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* 2. إخفاء كل شيء في البداية */
    body * {
        visibility: hidden;
    }

    /* 3. إظهار نافذة الفاتورة ومحتوياتها */
    #invoice-modal, 
    #invoice-modal * {
        visibility: visible;
    }

    /* 4. ضبط موضع النافذة لتأخذ كامل الورقة */
    #invoice-modal {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: auto !important;
        min-height: 100% !important;
        overflow: visible !important;
        display: block !important;
        background: white !important;
        z-index: 9999 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* 5. إلغاء التمرير والارتفاع الثابت */
    #invoice-modal .overflow-y-auto,
    #invoice-modal .max-h-96,
    .invoice-items-scrollable,
    .modal-content,
    #invoice-modal > div,
    #invoice-print-area {
        max-height: none !important;
        height: auto !important;
        overflow: visible !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    /* 6. إخفاء العناصر غير المرغوبة */
    .no-print, 
    button, 
    #close-invoice-modal,
    .bg-gradient-to-r,
    footer,
    #invoice-modal .bg-gray-50,
    .no-print * {
        display: none !important;
    }

    /* 7. تحسين مظهر منطقة الطباعة */
    #invoice-print-area {
        padding: 15mm !important;
        background: white !important;
        color: black !important;
        font-size: 11pt !important;
        line-height: 1.5 !important;
    }

    /* 8. تحسين Header الفاتورة */
    #invoice-print-area > div:first-child {
        border-bottom: 3px solid #000 !important;
        padding-bottom: 10px !important;
        margin-bottom: 15px !important;
    }

    #invoice-print-area h1 {
        color: #059669 !important;
        font-size: 28pt !important;
        font-weight: bold !important;
        margin: 0 !important;
    }

    /* 9. تحسين معلومات المحل والعميل */
    #invoice-print-area .grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 15px !important;
        margin-bottom: 15px !important;
    }

    #invoice-print-area h3 {
        font-size: 10pt !important;
        font-weight: bold !important;
        color: #666 !important;
        text-transform: uppercase !important;
        margin-bottom: 8px !important;
    }

    /* 10. تحسين الجداول */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 15px 0 !important;
    }

    thead {
        display: table-header-group !important;
        background: linear-gradient(to left, #f3f4f6, #e5e7eb) !important;
    }

    thead th {
        padding: 10px !important;
        font-weight: bold !important;
        color: #000 !important;
        border: 2px solid #d1d5db !important;
        text-align: center !important;
        font-size: 10pt !important;
    }

    tbody tr {
        page-break-inside: avoid !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    tbody td {
        padding: 8px !important;
        color: #000 !important;
        font-size: 10pt !important;
    }

    /* 11. تحسين صف الكميات */
    tbody td:nth-child(2) {
        text-align: center !important;
    }

    tbody td:nth-child(2) span {
        background: #dbeafe !important;
        color: #1e40af !important;
        padding: 4px 10px !important;
        border-radius: 5px !important;
        font-weight: bold !important;
    }
    /* ضمان توزيع ثلاثي الأعمدة في رأس الفاتورة أثناء الطباعة */
    .invoice-header-grid {
        display: grid !important;
        grid-template-columns: 33.333% 33.333% 33.333% !important;
        gap: 0 !important;
        align-items: start !important;
        margin-bottom: 20px !important;
        page-break-inside: avoid !important;
        width: 100% !important;
    }

    .invoice-header-grid > div {
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        min-height: 80px !important;
    }

    /* تنسيق عمود التاريخ (يمين) */
    .invoice-header-grid > div:first-child {
        text-align: right !important;
        grid-column: 1 !important;
        padding: 0 10px !important;
    }

    /* تنسيق عمود الباركود (وسط) */
    .invoice-header-grid > div:nth-child(2) {
        text-align: center !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: flex-start !important;
        grid-column: 2 !important;
        padding: 0 10px !important;
    }

    /* تنسيق عمود رقم الفاتورة (يسار) */
    .invoice-header-grid > div:last-child {
        text-align: left !important;
        grid-column: 3 !important;
        padding: 0 10px !important;
    }

    /* إزالة أي float أو positioning قد يتعارض */
    .invoice-header-grid > div * {
        float: none !important;
        position: static !important;
    }

    /* تحسين حجم الباركود في الطباعة */
    #invoice-barcode {
        width: 100% !important;
        max-width: 200px !important;
        height: 50px !important;
        margin: 8px auto !important;
        display: block !important;
    }

    /* تحسين عناوين الأقسام الثلاثة */
    .invoice-header-grid h3 {
        font-size: 9pt !important;
        font-weight: bold !important;
        text-transform: uppercase !important;
        color: #666 !important;
        margin-bottom: 8px !important;
    }

    /* تحسين التاريخ */
    .invoice-header-grid #invoice-date {
        font-size: 11pt !important;
        font-weight: bold !important;
        color: #000 !important;
    }

    .invoice-header-grid #invoice-time {
        font-size: 10pt !important;
        color: #666 !important;
        margin-top: 3px !important;
    }

    /* تحسين رقم الفاتورة */
    .invoice-header-grid #invoice-number {
        font-size: 18pt !important;
        font-weight: bold !important;
        color: #000 !important;
    }

    /* تحسين مظهر البطاقات في الرأس */
    .invoice-header-grid .bg-gray-50 {
        padding: 10px !important;
        border: 1px solid #ddd !important;
        background: #f9fafb !important;
    }
    /* 12. تحسين قسم المجاميع */
    #invoice-print-area > div:last-child > div:last-child > div {
        border: 2px solid #d1d5db !important;
        background: linear-gradient(to bottom right, #f9fafb, white) !important;
        padding: 15px !important;
        border-radius: 10px !important;
    }

    /* 13. تحسين الإجمالي النهائي */
    #invoice-print-area .grand-total,
    #invoice-print-area div[class*="text-2xl"],
    #invoice-print-area div[class*="text-3xl"] {
        font-size: 18pt !important;
        font-weight: bold !important;
        color: #3b82f6 !important;
        border-top: 3px solid #3b82f6 !important;
        padding-top: 10px !important;
        margin-top: 10px !important;
    }

    /* 14. تحسين الباركود */
    #invoice-barcode {
        max-width: 200px !important;
        height: 50px !important;
        margin: 10px auto !important;
        display: block !important;
    }

    /* 15. ضمان أن النصوص سوداء بالكامل */
    * {
        color: black !important;
        text-shadow: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* 16. استثناءات الألوان المهمة */
    h1 {
        color: #059669 !important;
    }

    #invoice-total {
        color: #3b82f6 !important;
    }

    /* 17. تحسين Footer */
    #invoice-print-area > div:last-child {
        border-top: 2px dashed #000 !important;
        padding-top: 15px !important;
        margin-top: 20px !important;
        text-align: center !important;
        font-size: 10pt !important;
    }

    /* 18. تحسين Logo */
    img[alt="Logo"] {
        max-width: 60px !important;
        max-height: 60px !important;
        border: 2px solid #e5e7eb !important;
    }

    /* 19. إصلاح مشكلة القص في الصفحات المتعددة */
    .invoice-items-container,
    tbody {
        page-break-inside: auto !important;
    }

    tr {
        page-break-inside: avoid !important;
        page-break-after: auto !important;
    }

    /* 20. تحسين التواريخ */
    #invoice-date,
    #invoice-time {
        font-weight: bold !important;
        color: #000 !important;
        font-size: 11pt !important;
    }

    /* 21. تحسين بطاقات المعلومات */
    .bg-gray-50,
    div[class*="bg-gray"] {
        background: #f9fafb !important;
        border: 1px solid #d1d5db !important;
        padding: 10px !important;
    }

    /* 22. إصلاح المسافات */
    #invoice-print-area > * {
        margin-bottom: 10px !important;
    }

    /* 23. تحسين رقم الفاتورة */
    #invoice-number {
        font-size: 20pt !important;
        font-weight: bold !important;
        color: #000 !important;
    }

    /* 24. إخفاء عناصر الديليفري غير الضرورية */
    #invoice-delivery-row:empty,
    #invoice-delivery-city-row:empty {
        display: none !important;
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
/* تنسيقات المنتجات المنتهية في Dark Mode */
.dark .bg-red-900\/20 {
    background-color: rgba(127, 29, 29, 0.2) !important;
}

.dark .border-red-500\/30 {
    border-color: rgba(239, 68, 68, 0.3) !important;
}

/* تنسيقات المنتجات المنتهية في Light Mode */
html:not(.dark) .bg-red-900\/20 {
    background-color: rgba(254, 202, 202, 0.6) !important;
}

html:not(.dark) .border-red-500\/30 {
    border-color: rgba(239, 68, 68, 0.5) !important;
}

html:not(.dark) .text-red-400 {
    color: #DC2626 !important;
}

html:not(.dark) .text-gray-500 {
    color: #6B7280 !important;
}

html:not(.dark) .text-gray-600 {
    color: #4B5563 !important;
}
/* تنسيقات قسم التوصيل في Light Mode */
html:not(.dark) .bg-white\/5 {
    background-color: rgba(0, 0, 0, 0.03) !important;
}

html:not(.dark) .border-white\/5 {
    border-color: rgba(0, 0, 0, 0.1) !important;
}

html:not(.dark) .border-white\/10 {
    border-color: rgba(0, 0, 0, 0.15) !important;
}

html:not(.dark) .bg-dark\/30 {
    background-color: rgba(243, 244, 246, 0.8) !important;
}

html:not(.dark) .bg-dark\/50 {
    background-color: rgba(229, 231, 235, 0.9) !important;
}

html:not(.dark) .hover\:bg-dark\/50:hover {
    background-color: rgba(229, 231, 235, 0.9) !important;
}

/* نصوص قسم التوصيل في Light Mode */
html:not(.dark) #delivery-options .text-white {
    color: #111827 !important;
}

html:not(.dark) #delivery-options .font-bold {
    color: #1F2937 !important;
}

html:not(.dark) #delivery-options .text-gray-400 {
    color: #6B7280 !important;
}

/* زر التبديل (Toggle) في Light Mode */
html:not(.dark) .bg-gray-600 {
    background-color: #D1D5DB !important;
}

html:not(.dark) .peer-checked\:bg-primary:checked {
    background-color: #3B82F6 !important;
}

/* الخيارات المحددة في Light Mode */
html:not(.dark) .has-\[\:checked\]\:border-primary:has(:checked) {
    border-color: #3B82F6 !important;
    background-color: rgba(59, 130, 246, 0.08) !important;
}

html:not(.dark) .has-\[\:checked\]\:bg-primary\/10:has(:checked) {
    background-color: rgba(59, 130, 246, 0.08) !important;
}

/* تحسين الـ border في الخيارات */
html:not(.dark) #delivery-options label {
    border: 2px solid #E5E7EB !important;
}

html:not(.dark) #delivery-options label:hover {
    border-color: #3B82F6 !important;
    background-color: rgba(59, 130, 246, 0.05) !important;
}

html:not(.dark) #delivery-options label:has(:checked) {
    border-color: #3B82F6 !important;
    background-color: rgba(59, 130, 246, 0.12) !important;
}

/* ألوان الكمية في Light Mode */
html:not(.dark) .text-green-500 {
    color: #10B981 !important;
}

html:not(.dark) .text-yellow-500 {
    color: #F59E0B !important;
}

html:not(.dark) .text-orange-500 {
    color: #F97316 !important;
}

html:not(.dark) .text-red-500 {
    color: #EF4444 !important;
}
#pagination-container{
        background-color: rgb(13 16 22);
    }
    header{
        background-color: #171d27;
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
            <div class="mb-4 bg-white/5 p-3 rounded-xl border border-white/5">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">التوصيل</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="delivery-toggle" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
                <div id="delivery-options" class="hidden mt-3 space-y-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-2 flex items-center gap-1">
                            <span class="material-icons-round text-xs">route</span>
                            نوع التوصيل
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="relative flex items-center justify-center p-3 rounded-lg border-2 cursor-pointer transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/10 border-white/10 hover:border-primary/30">
                                <input type="radio" name="delivery-type" value="inside" class="sr-only peer">
                                <div class="text-center">
                                    <span class="material-icons-round text-primary text-lg block mb-1">home</span>
                                    <span class="text-xs font-bold text-white block">داخل المدينة</span>
                                    <span class="text-xs text-gray-400 block"><?php echo $deliveryInsideCity; ?> <?php echo $currency; ?></span>
                                </div>
                            </label>
                            <label class="relative flex items-center justify-center p-3 rounded-lg border-2 cursor-pointer transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/10 border-white/10 hover:border-primary/30">
                                <input type="radio" name="delivery-type" value="outside" class="sr-only peer">
                                <div class="text-center">
                                    <span class="material-icons-round text-orange-500 text-lg block mb-1">location_on</span>
                                    <span class="text-xs font-bold text-white block">خارج المدينة</span>
                                    <span class="text-xs text-gray-400 block"><?php echo $deliveryOutsideCity; ?> <?php echo $currency; ?></span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- حقل اسم المدينة -->
                    <div id="city-name-container">
                        <label class="block text-xs text-gray-400 mb-1.5 flex items-center gap-1">
                            <span class="material-icons-round text-xs">location_city</span>
                            اسم المدينة
                        </label>
                        <input type="text" id="delivery-city-input" readonly
                            placeholder="اختر نوع التوصيل أولاً..."
                            class="w-full bg-dark/50 border border-white/10 text-white text-right px-3 py-2 rounded-lg text-sm focus:outline-none focus:border-primary/50 transition-all">
                        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1" id="delivery-cost-info">
                            <span class="material-icons-round text-xs">info</span>
                            <span>اختر نوع التوصيل</span>
                        </p>
                    </div>
                </div>
            </div>

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
                
                <div id="cart-delivery-row" class="flex justify-between text-sm text-gray-400 hidden">
                    <span>التوصيل</span>
                    <span id="cart-delivery-amount">0 <?php echo $currency; ?></span>
                </div>

                <div class="flex justify-between text-lg font-bold text-white pt-2 border-t border-white/5">
                    <span>الإجمالي</span>
                    <span id="cart-total" class="text-primary">0 <?php echo $currency; ?></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <button id="clear-cart-btn" class="button-danger bg-red-500/10 hover:bg-red-500/20 text-red-500 py-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">delete_outline</span>
                    إلغاء
                </button>

                <button id="checkout-btn" class="w-full bg-accent hover:bg-lime-500 text-dark-surface py-4 rounded-xl font-bold text-lg shadow-lg shadow-accent/20 flex items-center justify-center gap-2 transition-all hover:scale-[1.02]">
                    <span class="material-icons-round">payments</span>
                    دفع
                </button>
            </div>
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
        <div id="pagination-container" class="sticky bottom-0 p-6 pt-2 flex justify-center items-center z-20 ">
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
                            <!-- Header: Invoice Title and Logo -->
                <div class="flex items-center justify-between pb-6 mb-6 border-b-2 border-gray-300">
                    <div>
                        <h1 class="text-4xl font-extrabold text-green-600">فاتورة</h1>
                    </div>
                    <?php if (!empty($shopLogoUrl)): ?>
                        <img src="<?php echo htmlspecialchars($shopLogoUrl); ?>" alt="Logo" class="w-16 h-16 rounded-full border border-gray-200 object-contain bg-white">
                    <?php else: ?>
                        <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center text-primary">
                            <span class="material-icons-round text-3xl">store</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Two Columns: Shop Info and Client Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 mb-6 border-b border-gray-200">
                    <!-- Your Information (Shop Info) -->
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-3">معلومات المحل</h3>
                        <div class="text-sm text-gray-700 space-y-1">
                            <p class="font-bold text-base"><?php echo htmlspecialchars($shopName); ?></p>
                            <?php if ($shopPhone): ?>
                                <p><?php echo htmlspecialchars($shopPhone); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($fullLocation)): ?>
                                <p><?php echo htmlspecialchars($fullLocation); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Client Information -->
                    <div>
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-3">معلومات العميل</h3>
                        <div id="customer-info" class="text-sm text-gray-700 space-y-1"></div>
                    </div>
                </div>

                <!-- Two Columns: Issue Date and Invoice Number with Barcode -->
                <!-- Three Columns: Date, Barcode, Invoice Number -->
                <div class="grid grid-cols-3 gap-6 pb-6 mb-6 border-b border-gray-200 invoice-header-grid">
                        <!-- تاريخ الإصدار - يمين -->
                    <div class="text-right">
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">تاريخ الإصدار</h3>
                        <p class="text-base font-bold text-gray-900" id="invoice-date">-</p>
                        <p class="text-sm text-gray-600" id="invoice-time">-</p>
                    </div>

                    <!-- الباركود - وسط -->
                    <div class="flex flex-col items-center justify-start">
                        <svg id="invoice-barcode" style="max-width: 200px; height: 50px; margin: 0 auto;"></svg>
                    </div>

                    <!-- رقم الفاتورة - يسار -->
                    <div class="text-left">
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">رقم الفاتورة</h3>
                        <p class="text-2xl font-bold text-gray-900" id="invoice-number">-</p>
                    </div>
                </div>
            
                <div class="mb-6">
                    <div class="rounded-2xl border-2 border-gray-200 overflow-hidden bg-white shadow-sm">
                        <table class="w-full text-sm invoice-items-container">
                            <thead class="bg-gradient-to-l from-gray-50 to-gray-100">
                                <tr class="border-b-2 border-gray-300">
                                    <th class="text-right py-4 px-4 font-extrabold text-gray-800 text-xs uppercase tracking-wide">المنتج</th>
                                    <th class="text-center py-4 px-4 font-extrabold text-gray-800 text-xs uppercase tracking-wide">الكمية</th>
                                    <th class="text-center py-4 px-4 font-extrabold text-gray-800 text-xs uppercase tracking-wide">السعر</th>
                                    <th class="text-left py-4 px-4 font-extrabold text-gray-800 text-xs uppercase tracking-wide">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody id="invoice-items"></tbody>
                        </table>
                    </div>
                    <div id="items-count-badge" class="text-xs text-gray-500 mt-3 text-center hidden"></div>
                </div>

                <div class="pt-6">
                    <div class="flex justify-end">
                        <div class="w-full md:w-96 space-y-3 text-sm rounded-2xl border-2 border-gray-300 p-6 bg-gradient-to-br from-gray-50 to-white shadow-lg">
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600 font-semibold">المجموع الفرعي:</span>
                                <span class="font-bold text-gray-800 text-base" id="invoice-subtotal">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200" id="invoice-tax-row">
                                <span class="text-gray-600 font-semibold"><span id="invoice-tax-label">TVA</span> (<span id="invoice-tax-rate">20</span>%):</span>
                                <span class="font-bold text-gray-800 text-base" id="invoice-tax-amount">-</span>
                            </div>
                            <div class="flex justify-between items-center text-2xl font-extrabold border-t-4 border-primary/30 pt-4 mt-2">
                                <span class="text-gray-800">الإجمالي:</span>
                                <span class="text-primary text-3xl" id="invoice-total">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-8 pt-6 border-t-2 border-gray-300">
                    <p class="font-bold text-gray-800 mb-2" style="font-size: 18px;">شكراً لثقتكم بنا</p>
                    <p class="text-gray-600 italic" style="font-size: 13px;">نسعد بخدمتكم دائماً ونتطلع لزيارتكم القادمة</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-6 grid grid-cols-2 gap-3 no-print border-t shrink-0">
            <button id="print-invoice-btn" class="bg-primary hover:bg-primary-hover text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">print</span>
                طباعة مباشرة
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

<!-- Quantity Modal -->
<div id="quantity-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-sm border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">تعديل الكمية</h3>
            <button id="close-quantity-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-400 mb-4" id="quantity-product-name"></p>
            <input type="number" id="quantity-input" min="1" class="w-full bg-dark/50 border border-white/10 text-white text-center text-2xl font-bold py-4 rounded-xl focus:outline-none focus:border-primary/50" value="1">
        </div>
        <div class="p-6 border-t border-white/5 grid grid-cols-2 gap-3">
            <button id="cancel-quantity-btn" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 py-3 rounded-xl font-bold transition-all">
                إلغاء
            </button>
            <button id="confirm-quantity-btn" class="bg-primary hover:bg-primary-hover text-white py-3 rounded-xl font-bold transition-all">
                تأكيد
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

<!-- Custom Confirmation Modal -->
<div id="custom-confirm-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-md border border-white/10 m-4 transform transition-all">
        <div class="p-6 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-500/20 flex items-center justify-center">
                <span class="material-icons-round text-blue-400 text-4xl">help_outline</span>
            </div>
            <p id="confirm-message" class="text-white text-lg mb-6"></p>
            <div class="flex gap-3 justify-center">
                <button id="confirm-cancel-btn" class="px-6 py-2.5 rounded-lg bg-gray-700 hover:bg-gray-600 text-white font-medium transition-all duration-200 min-w-[100px]">
                    إلغاء
                </button>
                <button id="confirm-ok-btn" class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium transition-all duration-200 min-w-[100px]">
                    موافق
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="payment-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-lg border border-white/10 m-4 transform transition-all">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">طريقة الدفع</h3>
            <button id="close-payment-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-6">
                <p class="text-gray-400 text-center mb-2">المبلغ الإجمالي</p>
                <p id="payment-total-amount" class="text-white text-4xl font-bold text-center">0.00 <?php echo $currency; ?></p>
            </div>


            <div id="cash-payment-details" class="space-y-4">
                <div>
                    <label for="amount-received" class="block text-sm text-gray-400 mb-2">المبلغ المستلم</label>
                    <input type="number" id="amount-received" placeholder="أدخل المبلغ..." class="w-full bg-dark/50 border border-white/10 text-white text-center text-xl font-bold py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                </div>
                <div class="bg-white/5 p-4 rounded-xl text-center">
                    <p class="text-sm text-gray-400">الباقي</p>
                    <p id="change-due" class="text-2xl font-bold text-accent">0.00 <?php echo $currency; ?></p>
                </div>
            </div>
        </div>
        <div class="p-6 border-t border-white/5 grid grid-cols-2 gap-3">
             <button id="cancel-payment-btn" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 py-3 rounded-xl font-bold transition-all">
                إلغاء
            </button>
            <button id="confirm-payment-btn" class="bg-accent hover:bg-lime-500 text-dark-surface py-3 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                 <span class="material-icons-round">check_circle</span>
                تأكيد الدفع
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/index.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    const soundNotificationsEnabled = <?php echo ($soundEnabled == '1') ? 'true' : 'false'; ?>;
    
    // Custom Confirmation Modal Function
    function showConfirm(message) {
        return new Promise((resolve) => {
            const modal = document.getElementById('custom-confirm-modal');
            const messageEl = document.getElementById('confirm-message');
            const okBtn = document.getElementById('confirm-ok-btn');
            const cancelBtn = document.getElementById('confirm-cancel-btn');
            
            messageEl.textContent = message;
            modal.classList.remove('hidden');
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            
            const handleOk = () => {
                cleanup();
                resolve(true);
            };
            
            const handleCancel = () => {
                cleanup();
                resolve(false);
            };
            
            const handleKeydown = (e) => {
                if (e.key === 'Enter') {
                    handleOk();
                } else if (e.key === 'Escape') {
                    handleCancel();
                }
            };
            
            const cleanup = () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                okBtn.removeEventListener('click', handleOk);
                cancelBtn.removeEventListener('click', handleCancel);
                document.removeEventListener('keydown', handleKeydown);
            };
            
            okBtn.addEventListener('click', handleOk);
            cancelBtn.addEventListener('click', handleCancel);
            document.addEventListener('keydown', handleKeydown);
            
            // Focus OK button for accessibility
            setTimeout(() => okBtn.focus(), 100);
        });
    }
    
    // دالة تشغيل صوت النجاح الجميل
    function playSuccessSound() {
        if (!soundNotificationsEnabled) return;
        
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const masterGain = audioContext.createGain();
            masterGain.connect(audioContext.destination);
            masterGain.gain.setValueAtTime(0.3, audioContext.currentTime);
            
            // النغمة الأولى - دو (C)
            const osc1 = audioContext.createOscillator();
            const gain1 = audioContext.createGain();
            osc1.connect(gain1);
            gain1.connect(masterGain);
            osc1.frequency.value = 523.25; // C5
            osc1.type = 'sine';
            gain1.gain.setValueAtTime(0.4, audioContext.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            osc1.start(audioContext.currentTime);
            osc1.stop(audioContext.currentTime + 0.3);
            
            // النغمة الثانية - مي (E)
            const osc2 = audioContext.createOscillator();
            const gain2 = audioContext.createGain();
            osc2.connect(gain2);
            gain2.connect(masterGain);
            osc2.frequency.value = 659.25; // E5
            osc2.type = 'sine';
            gain2.gain.setValueAtTime(0, audioContext.currentTime + 0.15);
            gain2.gain.setValueAtTime(0.4, audioContext.currentTime + 0.16);
            gain2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.46);
            osc2.start(audioContext.currentTime + 0.15);
            osc2.stop(audioContext.currentTime + 0.46);
            
            // النغمة الثالثة - صول (G)
            const osc3 = audioContext.createOscillator();
            const gain3 = audioContext.createGain();
            osc3.connect(gain3);
            gain3.connect(masterGain);
            osc3.frequency.value = 783.99; // G5
            osc3.type = 'sine';
            gain3.gain.setValueAtTime(0, audioContext.currentTime + 0.3);
            gain3.gain.setValueAtTime(0.5, audioContext.currentTime + 0.31);
            gain3.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.8);
            osc3.start(audioContext.currentTime + 0.3);
            osc3.stop(audioContext.currentTime + 0.8);
            
            console.log('✅ تم تشغيل صوت النجاح');
        } catch (error) {
            console.error('خطأ في تشغيل الصوت:', error);
        }
    }

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

    const quantityModal = document.getElementById('quantity-modal');
    const closeQuantityModal = document.getElementById('close-quantity-modal');
    const quantityInput = document.getElementById('quantity-input');
    const quantityProductName = document.getElementById('quantity-product-name');
    const confirmQuantityBtn = document.getElementById('confirm-quantity-btn');
    const cancelQuantityBtn = document.getElementById('cancel-quantity-btn');
    let editingProductId = null;

    // Delivery Elements
    const deliveryToggle = document.getElementById('delivery-toggle');
    const deliveryOptionsDiv = document.getElementById('delivery-options');
    const deliveryCityInput = document.getElementById('delivery-city-input');
    const deliveryCostInfo = document.getElementById('delivery-cost-info');
    const cartDeliveryRow = document.getElementById('cart-delivery-row');
    const cartDeliveryAmount = document.getElementById('cart-delivery-amount');    
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

    const deliveryInsideCost = <?php echo $deliveryInsideCity; ?>;
    const deliveryOutsideCost = <?php echo $deliveryOutsideCity; ?>;
    const homeCity = '<?php echo addslashes(!empty($deliveryHomeCity) ? $deliveryHomeCity : $deliveryHomeCity); ?>';

    const lowAlert = <?php echo $lowAlert; ?>;       // الكمية المنخفضة (أصفر)
    const criticalAlert = <?php echo $criticalAlert; ?>; // الكمية الحرجة (أحمر)

    let cart = [];
    let allProducts = [];
    let selectedCustomer = null;
    let currentInvoiceData = null;
    let deliveryCost = 0;
    let currentPage = 1;
    const productsPerPage = 500;
    
    const taxEnabled = <?php echo $taxEnabled; ?> == 1;
    const taxRate = <?php echo $taxRate; ?> / 100;
    const taxLabel = '<?php echo addslashes($taxLabel); ?>';
    const shopLogoUrl = "<?php echo htmlspecialchars($shopLogoUrl); ?>";
    const currency = '<?php echo $currency; ?>';
    const shopName = '<?php echo addslashes($shopName); ?>';
    const shopPhone = '<?php echo addslashes($shopPhone); ?>';
    const shopAddress = '<?php echo addslashes($shopAddress); ?>';
    const shopCity = '<?php echo addslashes($shopCity); ?>'; // [جديد]
    const soundNotificationsEnabled = <?php echo $soundEnabled; ?> == 1;

    // ==========================================
    // كود تفعيل البحث بالباركود (كاميرا + يدوي)
    // ==========================================

    const barcodeScannerModal = document.getElementById('barcode-scanner-modal');
    const closeBarcodeScannerModal = document.getElementById('close-barcode-scanner-modal');
    const scanBarcodeBtn = document.getElementById('scan-barcode-btn');
    let codeReader = null;

    // صوت عند المسح الناجح
    const beepSound = new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU'); // صوت قصير بسيط

    // 1. تشغيل الكاميرا عند الضغط على زر المسح
    scanBarcodeBtn.addEventListener('click', () => {
        barcodeScannerModal.classList.remove('hidden');
        startScanning();
    });

    // 2. إغلاق نافذة المسح
    closeBarcodeScannerModal.addEventListener('click', stopScanning);

    // دالة بدء المسح باستخدام ZXing
    async function startScanning() {
        try {
            codeReader = new ZXing.BrowserMultiFormatReader();
            const videoInputDevices = await codeReader.listVideoInputDevices();
            
            // محاولة اختيار الكاميرا الخلفية إن وجدت
            const selectedDeviceId = videoInputDevices.find(device => device.label.toLowerCase().includes('back'))?.deviceId || videoInputDevices[0].deviceId;

            codeReader.decodeFromVideoDevice(selectedDeviceId, 'barcode-video', (result, err) => {
                if (result) {
                    handleScannedCode(result.text);
                    stopScanning();
                }
            });
        } catch (err) {
            console.error(err);
            alert('لا يمكن الوصول للكاميرا. تأكد من السماح بالوصول للكاميرا في المتصفح.');
            barcodeScannerModal.classList.add('hidden');
        }
    }

    // دالة إيقاف المسح
    function stopScanning() {
        if (codeReader) {
            codeReader.reset();
        }
        barcodeScannerModal.classList.add('hidden');
    }

    // 3. معالجة الكود الممسوح (سواء بالكاميرا أو القارئ اليدوي)
    function handleScannedCode(code) {
        // تشغيل صوت
        beepSound.play().catch(e => {});

        // وضع الكود في خانة البحث
        searchInput.value = code;
        
        // البحث عن تطابق تام لإضافته للسلة فوراً
        const exactMatch = allProducts.find(p => p.barcode === code);
        
        if (exactMatch) {
            // إذا وجدنا المنتج، نضيفه للسلة مباشرة
            addProductToCart(exactMatch);
            searchInput.value = ''; // تفريغ البحث للاستعداد للمنتج التالي
            applyFilters(); // إعادة تعيين الشبكة
            showToast(`تم إضافة "${exactMatch.name}" للسلة`, true);
        } else {
            // إذا لم نجد تطابق تام، نقوم بفلترة المنتجات لعرض النتائج المشابهة
            applyFilters();
            showToast('لم يتم العثور على منتج بهذا الباركود', false);
        }
    }

    // 4. تحسين البحث ليعمل مع قارئ الباركود اليدوي (USB Scanner)
    // أجهزة الباركود عادة ما تضغط "Enter" بعد قراءة الكود
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && searchInput.value.trim() !== '') {
            e.preventDefault(); // منع إعادة تحميل الصفحة
            handleScannedCode(searchInput.value.trim());
        }
    });

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
        const searchTerm = searchInput.value;
        const categoryId = categoryFilter.value;

        try {
            showLoading('جاري تحميل المنتجات...');
            const response = await fetch(`api.php?action=getProducts&search=${searchTerm}&category_id=${categoryId}&page=${currentPage}&limit=${productsPerPage}`);
            const result = await response.json();
            if (result.success) {
                allProducts = result.data;
                displayProducts(result.data);
                renderPagination(result.total_products);
            }
        } catch (error) {
            console.error('خطأ في تحميل المنتجات:', error);
            showToast('حدث خطأ في تحميل المنتجات', false);
        } finally {
            hideLoading();
        }
    }

    function applyFilters() {
        currentPage = 1;
        loadProducts();
    }

    searchInput.addEventListener('input', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);

    function renderPagination(totalProducts) {
        const paginationContainer = document.getElementById('pagination-container');
        const totalPages = Math.ceil(totalProducts / productsPerPage);
        paginationContainer.innerHTML = '';

        if (totalPages <= 1) return;

        let paginationHTML = `
            <div class="flex items-center gap-2">
                <span class="text-sm">صفحة ${currentPage} من ${totalPages}</span>
            </div>
            <div class="flex items-center gap-1">`;
        
        paginationHTML += `<button class="pagination-btn ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}><span class="material-icons-round">chevron_right</span></button>`;

        const pagesToShow = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) pagesToShow.push(i);
        } else {
            pagesToShow.push(1);
            if (currentPage > 3) pagesToShow.push('...');
            let start = Math.max(2, currentPage - 1);
            let end = Math.min(totalPages - 1, currentPage + 1);
            for (let i = start; i <= end; i++) pagesToShow.push(i);
            if (currentPage < totalPages - 2) pagesToShow.push('...');
            pagesToShow.push(totalPages);
        }

        pagesToShow.forEach(page => {
            if (page === '...') {
                paginationHTML += `<span class="pagination-dots">...</span>`;
            } else if (page === currentPage) {
                paginationHTML += `<button class="pagination-btn bg-primary text-white" data-page="${page}">${page}</button>`;
            } else {
                paginationHTML += `<button class="pagination-btn" data-page="${page}">${page}</button>`;
            }
        });
        
        paginationHTML += `<button class="pagination-btn ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}><span class="material-icons-round">chevron_left</span></button>`;
        paginationHTML += `</div>`;
        paginationContainer.innerHTML = paginationHTML;
    }

    document.getElementById('pagination-container').addEventListener('click', e => {
        if (e.target.closest('.pagination-btn')) {
            const btn = e.target.closest('.pagination-btn');
            currentPage = parseInt(btn.dataset.page);
            loadProducts();
        }
    });

    function displayProducts(products) {
        productsGrid.innerHTML = '';
        if (products.length === 0) {
            productsGrid.innerHTML = '<p class="text-center py-4 text-gray-500 col-span-full">لا توجد منتجات لعرضها.</p>';
            return;
        }
        products.forEach(product => {
            const quantity = parseInt(product.quantity);
            const isOutOfStock = quantity === 0;
            
            const productCard = document.createElement('div');
            
            // تهيئة المتغيرات للكلاسات والأيقونات
            let cardClasses = 'rounded-2xl p-4 flex flex-col items-center justify-center text-center transition-all relative overflow-hidden group';
            let quantityClass = '';
            let quantityIcon = '';
            let statusBadge = '';

            // منطق تحديد الألوان بناءً على الإعدادات الجديدة
            if (isOutOfStock) {
                // منتج منتهي (رمادي/أحمر باهت)
                cardClasses += ' bg-dark-surface/30 border-2 border-gray-700 opacity-80 cursor-not-allowed grayscale hover:grayscale-0 transition-all';
                quantityClass = 'text-gray-500 font-bold';
                quantityIcon = 'block';
                statusBadge = `
                    <div class="absolute top-2 right-2 bg-gray-600 text-white text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1 shadow-lg z-10">
                        <span class="material-icons-round text-[12px]">block</span>
                        <span>نفذ</span>
                    </div>`;
            } else if (quantity <= criticalAlert) {
                // كمية حرجة (أحمر)
                cardClasses += ' bg-red-900/10 border border-red-500/50 hover:bg-red-900/20 hover:border-red-500 cursor-pointer hover:scale-105 shadow-lg shadow-red-900/10';
                quantityClass = 'text-red-500 font-bold animate-pulse'; // وميض خفيف للفت الانتباه
                quantityIcon = 'error';
                statusBadge = `
                    <div class="absolute top-2 right-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1 shadow-lg z-10">
                        <span class="material-icons-round text-[12px]">priority_high</span>
                        <span>حرج</span>
                    </div>`;
            } else if (quantity <= lowAlert) {
                // كمية منخفضة (أصفر)
                cardClasses += ' bg-yellow-500/5 border border-yellow-500/30 hover:bg-yellow-500/10 hover:border-yellow-500/60 cursor-pointer hover:scale-105';
                quantityClass = 'text-yellow-500 font-bold';
                quantityIcon = 'warning';
                statusBadge = `
                    <div class="absolute top-2 right-2 bg-yellow-600/20 text-yellow-500 border border-yellow-500/30 text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1 z-10">
                        <span class="material-icons-round text-[12px]">low_priority</span>
                        <span>منخفض</span>
                    </div>`;
            } else {
                // كمية جيدة (أخضر/عادي)
                cardClasses += ' bg-dark-surface/50 border border-white/5 hover:border-primary/50 cursor-pointer hover:scale-105 hover:shadow-lg hover:shadow-primary/5';
                quantityClass = 'text-green-500 font-medium';
                quantityIcon = 'check_circle';
            }
            
            productCard.className = cardClasses;
            
            productCard.innerHTML = `
                ${statusBadge}
                <div class="relative w-24 h-24 mb-4">
                    <img src="${product.image || 'src/img/default-product.png'}" alt="${product.name}" 
                        class="w-full h-full object-cover rounded-xl shadow-md group-hover:shadow-xl transition-all duration-300">
                </div>
                
                <div class="text-lg font-bold truncate w-full px-2 ${isOutOfStock ? 'text-gray-500 line-through decoration-2' : 'text-white'}">
                    ${product.name}
                </div>
                
                <div class="text-sm font-bold mt-1 ${isOutOfStock ? 'text-gray-600' : 'text-primary-300'}">
                    ${parseFloat(product.price).toFixed(2)} ${currency}
                </div>
                
                <div class="flex items-center justify-center gap-1.5 mt-3 py-1 px-3 rounded-lg bg-dark/30 ${quantityClass}">
                    <span class="material-icons-round text-sm">${quantityIcon}</span>
                    <span class="text-xs">المخزون: ${quantity}</span>
                </div>
            `;
            
            if (!isOutOfStock) {
                productCard.addEventListener('click', () => addProductToCart(product));
            } else {
                productCard.addEventListener('click', () => {
                    showToast(`⚠️ المنتج "${product.name}" غير متوفر حالياً`, false);
                    if (soundNotificationsEnabled) {
                        // صوت خطأ خفيف
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = 150;
                        osc.type = 'sawtooth';
                        gain.gain.setValueAtTime(0.1, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.2);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.2);
                    }
                });
            }
            
            productsGrid.appendChild(productCard);
        });
    }
    
    function addProductToCart(product) {
        const stockAvailable = parseInt(product.quantity);
        if (stockAvailable === 0) {
            showToast('⚠️ عذراً، هذا المنتج نفذت كميته تماماً!', false);
            return;
        }
        
        const existingProduct = cart.find(item => item.id === product.id);
        
        const currentCartQuantity = existingProduct ? existingProduct.quantity : 0;

        if (currentCartQuantity + 1 > stockAvailable) {
            showToast(`نأسف، نفذت كمية هذا المنتج من المخزون! (متوفر: ${stockAvailable})`, false);
            return; 
        }

        if (existingProduct) {
            existingProduct.quantity++;
        } else {
            cart.push({ ...product, quantity: 1, stock: stockAvailable });
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
                        <span class="text-white font-bold min-w-[30px] text-center cursor-pointer hover:text-primary transition-colors" data-id="${item.id}" data-action="edit">${item.quantity}</span>
                        <button class="quantity-btn w-8 h-8 bg-primary/20 text-primary rounded-lg hover:bg-primary/30 transition-colors" data-id="${item.id}" data-action="increase">+</button>
                        <button class="w-8 h-8 bg-red-500/10 text-red-500 rounded-lg hover:bg-red-500/20 transition-colors flex items-center justify-center ml-2" data-id="${item.id}" data-action="delete" title="حذف المنتج">
                            <span class="material-icons-round text-sm">delete</span>
                        </button>
                    </div>
                `;
                cartItemsContainer.appendChild(cartItem);
            });
        }
        updateTotals();
    }

    function updateDeliveryState() {
        if (deliveryToggle.checked) {
            deliveryOptionsDiv.classList.remove('hidden');
            cartDeliveryRow.classList.remove('hidden');
        } else {
            deliveryOptionsDiv.classList.add('hidden');
            cartDeliveryRow.classList.add('hidden');
            deliveryCost = 0;
            // إعادة تعيين الحقول
            const deliveryTypeRadios = document.querySelectorAll('input[name="delivery-type"]');
            deliveryTypeRadios.forEach(radio => radio.checked = false);
            deliveryCityInput.value = '';
            deliveryCityInput.readOnly = true;
            deliveryCostInfo.innerHTML = `
                <span class="material-icons-round text-xs">info</span>
                <span>اختر نوع التوصيل</span>
            `;
            deliveryCostInfo.className = 'text-xs text-gray-500 mt-1 flex items-center gap-1';
        }
        updateTotals();
    }

    function handleDeliveryTypeChange() {
        const selectedType = document.querySelector('input[name="delivery-type"]:checked');
        
        if (!selectedType) {
            deliveryCityInput.value = '';
            deliveryCityInput.readOnly = true;
            deliveryCityInput.placeholder = 'اختر نوع التوصيل أولاً...';
            deliveryCost = 0;
            deliveryCostInfo.innerHTML = `
                <span class="material-icons-round text-xs">info</span>
                <span>اختر نوع التوصيل</span>
            `;
            deliveryCostInfo.className = 'text-xs text-gray-500 mt-1 flex items-center gap-1';
            updateTotals();
            return;
        }
        
        const type = selectedType.value;
        
        if (type === 'inside') {
            // داخل المدينة
            if (!homeCity) {
                showToast('⚠️ لم يتم تحديد مدينة المحل في الإعدادات', false);
                deliveryCityInput.value = '';
                deliveryCityInput.readOnly = true;
                deliveryCost = 0;
            } else {
                deliveryCityInput.value = homeCity;
                deliveryCityInput.readOnly = true;
                deliveryCost = deliveryInsideCost;
                deliveryCostInfo.innerHTML = `
                    <span class="material-icons-round text-xs">check_circle</span>
                    <span>داخل المدينة: ${deliveryInsideCost} ${currency}</span>
                `;
                deliveryCostInfo.className = 'text-xs text-green-500 mt-1 flex items-center gap-1';
            }
        } else if (type === 'outside') {
            // خارج المدينة
            deliveryCityInput.value = '';
            deliveryCityInput.readOnly = false;
            deliveryCityInput.placeholder = 'أدخل اسم المدينة...';
            deliveryCityInput.focus();
            deliveryCost = deliveryOutsideCost;
            deliveryCostInfo.innerHTML = `
                <span class="material-icons-round text-xs">location_on</span>
                <span>خارج المدينة: ${deliveryOutsideCost} ${currency}</span>
            `;
            deliveryCostInfo.className = 'text-xs text-orange-500 mt-1 flex items-center gap-1';
        }
        
        updateTotals();
    }

    function calculateDeliveryCost() {
        const cityName = deliveryCityInput.value.trim();
        
        if (!cityName) {
            deliveryCost = 0;
            deliveryCostInfo.innerHTML = `
                <span class="material-icons-round text-xs">info</span>
                <span>سيتم حساب التكلفة تلقائياً</span>
            `;
            deliveryCostInfo.className = 'text-xs text-gray-500 mt-1 flex items-center gap-1';
        } else {
            // التحقق من وجود مدينة المحل
            if (!homeCity) {
                deliveryCost = deliveryOutsideCost;
                deliveryCostInfo.innerHTML = `
                    <span class="material-icons-round text-xs">warning</span>
                    <span>لم يتم تحديد مدينة المحل، سيتم احتساب خارج المدينة: ${deliveryOutsideCost} ${currency}</span>
                `;
                deliveryCostInfo.className = 'text-xs text-yellow-500 mt-1 flex items-center gap-1';
            } else {
                // تنظيف وتوحيد اسم المدينة للمقارنة
                const normalizedInput = cityName.toLowerCase().trim();
                const normalizedHome = homeCity.toLowerCase().trim();
                
                if (normalizedInput === normalizedHome) {
                    // داخل المدينة
                    deliveryCost = deliveryInsideCost;
                    deliveryCostInfo.innerHTML = `
                        <span class="material-icons-round text-xs">check_circle</span>
                        <span>داخل المدينة: ${deliveryInsideCost} ${currency}</span>
                    `;
                    deliveryCostInfo.className = 'text-xs text-green-500 mt-1 flex items-center gap-1';
                } else {
                    // خارج المدينة
                    deliveryCost = deliveryOutsideCost;
                    deliveryCostInfo.innerHTML = `
                        <span class="material-icons-round text-xs">location_on</span>
                        <span>خارج المدينة: ${deliveryOutsideCost} ${currency}</span>
                    `;
                    deliveryCostInfo.className = 'text-xs text-orange-500 mt-1 flex items-center gap-1';
                }
            }
        }
        
        updateTotals();
    }

    deliveryToggle.addEventListener('change', updateDeliveryState);

    document.addEventListener('change', function(e) {
        if (e.target.name === 'delivery-type') {
            handleDeliveryTypeChange();
        }
    });

    deliveryCityInput.addEventListener('input', calculateDeliveryCost);

    function updateTotals() {
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax + deliveryCost;

        cartSubtotal.textContent = `${subtotal.toFixed(2)} ${currency}`;
        if (cartTax) {
            cartTax.textContent = `${tax.toFixed(2)} ${currency}`;
        }
        
        cartDeliveryAmount.textContent = `${deliveryCost.toFixed(2)} ${currency}`;
        cartTotal.textContent = `${total.toFixed(2)} ${currency}`;
    }

    cartItemsContainer.addEventListener('click', function (e) {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        
        const id = target.dataset.id;
        const action = target.dataset.action;
        const item = cart.find(product => product.id == id);

        if (!item) return;

        if (action === 'edit') {
            editingProductId = id;
            quantityProductName.textContent = item.name;
            quantityInput.value = item.quantity;
            quantityModal.classList.remove('hidden');
            quantityInput.focus();
            quantityInput.select();
        } else if (action === 'increase') {
            // التحقق قبل الزيادة
            if (item.quantity + 1 > item.stock) {
                showToast(`الكمية المتوفرة في المخزون هي ${item.stock} فقط`, false);
                return;
            }
            item.quantity++;
            updateCart();
        } else if (action === 'decrease') {
            item.quantity--;
            if (item.quantity === 0) {
                cart = cart.filter(product => product.id != id);
            }
            updateCart();
        } else if (action === 'delete') {
            showConfirm(`هل تريد حذف "${item.name}" من السلة؟`).then(confirmed => {
                if (confirmed) {
                    cart = cart.filter(product => product.id != id);
                    updateCart();
                    showToast('تم حذف المنتج من السلة', true);
                }
            });
        }
    });

    closeQuantityModal.addEventListener('click', () => {
        quantityModal.classList.add('hidden');
        editingProductId = null;
    });

    cancelQuantityBtn.addEventListener('click', () => {
        quantityModal.classList.add('hidden');
        editingProductId = null;
    });

    confirmQuantityBtn.addEventListener('click', () => {
        const newQuantity = parseInt(quantityInput.value);
        if (newQuantity > 0 && editingProductId) {
            const item = cart.find(product => product.id == editingProductId);
            if (item) {
                // التحقق من المخزون
                if (newQuantity > item.stock) {
                    showToast(`لا يمكنك طلب أكثر من ${item.stock} قطع`, false);
                    return; // إيقاف العملية
                }
                
                item.quantity = newQuantity;
                updateCart();
                showToast('تم تحديث الكمية بنجاح', true);
            }
        }
        quantityModal.classList.add('hidden');
        editingProductId = null;
    });

    quantityInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            confirmQuantityBtn.click();
        }
    });

    // إغلاق عند الضغط خارج النافذة
    quantityModal.addEventListener('click', (e) => {
        if (e.target === quantityModal) {
            quantityModal.classList.add('hidden');
            editingProductId = null;
        }
    });

    clearCartBtn.addEventListener('click', async () => {
        if (cart.length > 0 && await showConfirm('هل أنت متأكد من إلغاء السلة؟')) {
            cart = [];
            updateCart();
            showToast('تم إلغاء السلة', true);
        }
    });

    async function processCheckout(paymentData) {
        if (cart.length === 0) {
            showToast('السلة فارغة!', false);
            return;
        }

        let deliveryCity = null;
        let deliveryCostValue = 0;
        
        if (deliveryToggle.checked) {
            // التحقق من اختيار نوع التوصيل
            const selectedType = document.querySelector('input[name="delivery-type"]:checked');
            if (!selectedType) {
                showToast('⚠️ الرجاء اختيار نوع التوصيل (داخل/خارج المدينة)', false);
                return;
            }
            
            const cityInput = document.getElementById('delivery-city-input');
            
            // التحقق من إدخال اسم المدينة
            if (!cityInput || !cityInput.value.trim()) {
                if (selectedType.value === 'inside') {
                    showToast('⚠️ لم يتم تحديد مدينة المحل في الإعدادات', false);
                } else {
                    showToast('⚠️ الرجاء إدخال اسم المدينة للتوصيل', false);
                    cityInput.focus();
                }
                return;
            }
            
            deliveryCity = cityInput.value.trim();
            deliveryCostValue = deliveryCost;
        }

        // حساب المجاميع قبل إرسال البيانات
        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax + deliveryCostValue;

        try {
            showLoading('جاري معالجة عملية البيع...');
            const response = await fetch('api.php?action=checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    customer_id: selectedCustomer ? selectedCustomer.id : null,
                    total: total,
                    delivery_cost: deliveryCostValue,
                    delivery_city: deliveryCity,
                    items: cart,
                    payment_method: paymentData.paymentMethod,
                    amount_received: paymentData.amountReceived,
                    change_due: paymentData.changeDue
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
                    delivery: deliveryCostValue,
                    deliveryCity: deliveryCity,
                    total: total,
                    date: new Date(),
                    // --- NEW FIELDS ---
                    amountReceived: paymentData.amountReceived,
                    changeDue: paymentData.changeDue
                };
                
                displayInvoice(currentInvoiceData);
                playSuccessSound();
                
                // تفريغ السلة وإعادة تعيين البيانات
                cart = [];
                selectedCustomer = null;
                
                // Reset Delivery
                deliveryToggle.checked = false;
                if (deliveryCityInput) deliveryCityInput.value = '';
                updateDeliveryState();
                
                customerNameDisplay.textContent = 'عميل نقدي';
                customerDetailDisplay.textContent = 'افتراضي';
                customerAvatar.textContent = 'A';
                updateCart();
                
                // تحديث المنتجات فوراً بعد البيع الناجح
                loadProducts();
                
                invoiceModal.classList.remove('hidden');
            } else {
                showToast(result.message || 'فشل في إنشاء الفاتورة', false);
            }

        } catch (error) {
            console.error('خطأ في إنشاء الفاتورة:', error);
            showToast('حدث خطأ أثناء إنشاء الفاتورة', false);
        } finally {
            hideLoading();
        }
    }
    checkoutBtn.addEventListener('click', () => {
        if (cart.length === 0) {
            showToast('السلة فارغة!', false);
            return;
        }

        const paymentModal = document.getElementById('payment-modal');
        const paymentTotalAmount = document.getElementById('payment-total-amount');
        const amountReceivedInput = document.getElementById('amount-received');
        const changeDueDisplay = document.getElementById('change-due');
        const cashPaymentDetails = document.getElementById('cash-payment-details');
        const confirmPaymentBtn = document.getElementById('confirm-payment-btn');
        const cancelPaymentBtn = document.getElementById('cancel-payment-btn');
        const closePaymentModalBtn = document.getElementById('close-payment-modal');

        const subtotal = cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax + deliveryCost;

        paymentTotalAmount.textContent = `${total.toFixed(2)} ${currency}`;

        const calculateChange = () => {
            const received = parseFloat(amountReceivedInput.value) || 0;
            const change = received - total;
            if (change >= 0) {
                changeDueDisplay.textContent = `${change.toFixed(2)} ${currency}`;
            } else {
                changeDueDisplay.textContent = '0.00 '  + currency;
            }
        };

        amountReceivedInput.addEventListener('input', calculateChange);

        const closeModal = () => {
            paymentModal.classList.add('hidden');
        };

        cancelPaymentBtn.addEventListener('click', closeModal);
        closePaymentModalBtn.addEventListener('click', closeModal);

        confirmPaymentBtn.onclick = () => {
            const paymentMethod = 'cash';
            const amountReceived = parseFloat(amountReceivedInput.value) || 0;

            if (paymentMethod === 'cash' && amountReceived < total) {
                showToast('المبلغ المستلم أقل من الإجمالي', false);
                return;
            }

            closeModal();
            processCheckout({
                paymentMethod: paymentMethod,
                amountReceived: amountReceived,
                changeDue: amountReceived - total
            });
        };
        
        amountReceivedInput.value = '';
        calculateChange();

        paymentModal.classList.remove('hidden');
        amountReceivedInput.focus();
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

        const formattedTime = data.date.toLocaleTimeString('ar-SA', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        });
        document.getElementById('invoice-time').textContent = toEnglishNumbers(formattedTime);

        const customerInfo = document.getElementById('customer-info');
        if (data.customer) {
            customerInfo.innerHTML = `
                <p class="font-bold text-base">${data.customer.name}</p>
                ${data.customer.phone ? `<p>${data.customer.phone}</p>` : ''}
                ${data.customer.email ? `<p>${data.customer.email}</p>` : ''}
            `;
        } else {
            customerInfo.innerHTML = '<p class="font-bold">عميل نقدي</p><p class="text-gray-500">افتراضي</p>';
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
            row.className = 'border-b border-gray-200 invoice-item-row hover:bg-gray-50 transition-colors';
            row.innerHTML = `
                <td class="py-3 px-4 text-gray-800 font-bold">${item.name}</td>
                <td class="py-3 px-4 text-center">
                    <span class="inline-block bg-blue-50 text-blue-700 font-bold px-3 py-1 rounded-lg text-sm">
                        ${item.quantity}
                    </span>
                </td>
                <td class="py-3 px-4 text-center text-gray-700 font-semibold">${parseFloat(item.price).toFixed(2)} ${currency}</td>
                <td class="py-3 px-4 text-left">
                    <span class="font-extrabold text-gray-900 text-base">
                        ${(item.price * item.quantity).toFixed(2)} ${currency}
                    </span>
                </td>
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

        const existingDeliveryRow = document.getElementById('invoice-delivery-row');
        if (existingDeliveryRow) existingDeliveryRow.remove();
        const existingDeliveryCityRow = document.getElementById('invoice-delivery-city-row');
        if (existingDeliveryCityRow) existingDeliveryCityRow.remove();

        if (data.delivery > 0) {
            const taxRow = document.getElementById('invoice-tax-row');
            const totalsContainer = taxRow.parentNode;
            const totalRow = totalsContainer.querySelector('.text-lg.font-bold.border-t-2') || totalsContainer.lastElementChild;
            
            // إضافة سطر التوصيل
            const deliveryRow = document.createElement('div');
            deliveryRow.id = 'invoice-delivery-row';
            deliveryRow.className = 'flex justify-between';
            deliveryRow.innerHTML = `
                <span class="text-gray-600">التوصيل:</span>
                <span class="font-medium">${data.delivery.toFixed(2)} ${currency}</span>
            `;
            totalsContainer.insertBefore(deliveryRow, totalRow);
            
            // إضافة مدينة التوصيل أسفل ثمن التوصيل
            if (data.deliveryCity) {
                const deliveryCityRow = document.createElement('div');
                deliveryCityRow.id = 'invoice-delivery-city-row';
                deliveryCityRow.className = 'flex justify-between text-sm';
                deliveryCityRow.innerHTML = `
                    <span class="text-gray-500">مدينة التوصيل:</span>
                    <span class="text-gray-600">${data.deliveryCity}</span>
                `;
                totalsContainer.insertBefore(deliveryCityRow, totalRow);
            }
        }

        document.getElementById('invoice-total').textContent = `${data.total.toFixed(2)} ${currency}`;

        // --- NEW CODE START: Add Amount Received and Change Due BELOW Total ---
        const taxRow = document.getElementById('invoice-tax-row');
        const totalsContainer = taxRow.parentNode;

        // Remove old rows if they exist (to prevent duplicates on re-open)
        const existingReceived = document.getElementById('invoice-received-row');
        if (existingReceived) existingReceived.remove();
        const existingChange = document.getElementById('invoice-change-row');
        if (existingChange) existingChange.remove();

        // Only show if amount received is greater than 0
        if (data.amount_received > 0 || (currentInvoiceData && currentInvoiceData.amountReceived > 0)) {
             // We check both data.amount_received (from DB) and data.amountReceived (from JS object during checkout)
             const recVal = data.amount_received || data.amountReceived || 0;
             const chgVal = data.change_due || data.changeDue || 0;

            const receivedRow = document.createElement('div');
            receivedRow.id = 'invoice-received-row';
            receivedRow.className = 'flex justify-between text-sm mt-2 pt-2 border-t border-dashed border-gray-300';
            receivedRow.innerHTML = `
                <span class="text-gray-600 font-bold">المبلغ المستلم:</span>
                <span class="font-bold text-gray-800">${parseFloat(recVal).toFixed(2)} ${currency}</span>
            `;
            totalsContainer.appendChild(receivedRow);

            const changeRow = document.createElement('div');
            changeRow.id = 'invoice-change-row';
            changeRow.className = 'flex justify-between text-sm';
            changeRow.innerHTML = `
                <span class="text-gray-600 font-bold">المبلغ الذي تم رده:</span>
                <span class="font-bold text-gray-800">${parseFloat(chgVal).toFixed(2)} ${currency}</span>
            `;
            totalsContainer.appendChild(changeRow);
        }
        // --- NEW CODE END ---
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

        // --- تصحيح: معالجة النص قبل فتح القالب النصي ---
        let locationText = '';
        if(shopCity) locationText += shopCity;
        if(shopCity && shopAddress) locationText += '، ';
        if(shopAddress) locationText += shopAddress;
        // ---------------------------------------------

        let thermalContent = `<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm">
    <title>فاتورة حرارية #${String(currentInvoiceData.id).padStart(6, '0')}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            width: 80mm; padding: 5mm; font-size: 11pt;
            line-height: 1.4; background: white; color: #000;
        }
        .header { text-align: center; margin-bottom: 5mm; border-bottom: 2px dashed #000; padding-bottom: 3mm; }
        .shop-name { font-size: 16pt; font-weight: bold; margin-bottom: 1mm; }
        .shop-info { font-size: 9pt; color: #333; margin: 1mm 0; }
        .invoice-info { margin: 3mm 0; border-bottom: 1px dashed #000; padding-bottom: 2mm; }
        .info-row { display: flex; justify-content: space-between; font-size: 10pt; margin: 1mm 0; }
        .customer-section { margin: 3mm 0; padding: 2mm; background: #f5f5f5; border-radius: 2mm; font-size: 10pt; }
        .items-table { width: 100%; margin: 3mm 0; }
        .items-header { border-top: 2px solid #000; border-bottom: 1px solid #000; padding: 1mm 0; font-weight: bold; font-size: 10pt; }
        .item-row { border-bottom: 1px dashed #ccc; padding: 2mm 0; font-size: 10pt; }
        .item-details { display: flex; justify-content: space-between; font-size: 9pt; }
        .totals-section { margin: 3mm 0; border-top: 2px solid #000; padding-top: 2mm; }
        .total-row { display: flex; justify-content: space-between; font-size: 11pt; margin: 1mm 0; }
        .grand-total { font-size: 14pt; font-weight: bold; border-top: 2px solid #000; padding-top: 2mm; margin-top: 2mm; }
        .footer { text-align: center; margin-top: 5mm; border-top: 2px dashed #000; padding-top: 3mm; font-size: 10pt; }
    </style>
</head>
<body>
    <div class="header">
        <div class="shop-name">${shopName}</div>
        ${shopPhone ? `<div class="shop-info">📞 ${shopPhone}</div>` : ''}
        ${locationText ? `<div class="shop-info">📍 ${locationText}</div>` : ''}
    </div>

    <div class="invoice-info">
        <div class="info-row"><span>رقم الفاتورة:</span><span>#${String(currentInvoiceData.id).padStart(6, '0')}</span></div>
        <div class="info-row"><span>التاريخ:</span><span>${formattedDate}</span></div>
        <div class="info-row"><span>الوقت:</span><span>${formattedTime}</span></div>
    </div>

    ${currentInvoiceData.customer ? `
    <div class="customer-section">
        <div style="font-weight: bold;">العميل: ${currentInvoiceData.customer.name}</div>
        ${currentInvoiceData.customer.phone ? `<div>${currentInvoiceData.customer.phone}</div>` : ''}
    </div>
    ` : `
    <div class="customer-section">
        <div>💵 عميل نقدي</div>
    </div>
    `}

    <div class="items-table">
        <div class="items-header">المنتجات (${currentInvoiceData.items.length})</div>
`;

        currentInvoiceData.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            thermalContent += `
        <div class="item-row">
            <div style="font-weight:bold">${index + 1}. ${item.name}</div>
            <div class="item-details">
                <span>${item.quantity} × ${parseFloat(item.price).toFixed(2)}</span>
                <span style="font-weight: bold;">${itemTotal.toFixed(2)} ${currency}</span>
            </div>
        </div>`;
        });

        thermalContent += `</div>
            <div class="totals-section">
                <div class="total-row"><span>المجموع:</span><span>${currentInvoiceData.subtotal.toFixed(2)} ${currency}</span></div>`;

        if (taxEnabled) {
            thermalContent += `<div class="total-row"><span>${taxLabel} (${(taxRate * 100).toFixed(0)}%):</span><span>${currentInvoiceData.tax.toFixed(2)} ${currency}</span></div>`;
        }
        if (currentInvoiceData.delivery > 0) {
            thermalContent += `<div class="total-row"><span>التوصيل:</span><span>${currentInvoiceData.delivery.toFixed(2)} ${currency}</span></div>`;
            if (currentInvoiceData.deliveryCity) {
            thermalContent += `<div class="total-row" style="font-size: 9pt; color: #666;"><span>مدينة التوصيل:</span><span>${currentInvoiceData.deliveryCity}</span></div>`;
        }
    }

    thermalContent += `
        <div class="total-row grand-total"><span>الإجمالي:</span><span>${currentInvoiceData.total.toFixed(2)} ${currency}</span></div>`;

    // --- NEW CODE START ---
    const recVal = currentInvoiceData.amount_received || currentInvoiceData.amountReceived || 0;
    const chgVal = currentInvoiceData.change_due || currentInvoiceData.changeDue || 0;

    if (recVal > 0) {
        thermalContent += `
        <div class="total-row" style="border-top: 1px dashed #000; margin-top: 2mm; padding-top: 2mm;">
            <span>المبلغ المستلم:</span>
            <span>${parseFloat(recVal).toFixed(2)} ${currency}</span>
        </div>
        <div class="total-row">
            <span>المبلغ الذي تم رده:</span>
            <span>${parseFloat(chgVal).toFixed(2)} ${currency}</span>
        </div>`;
    }
    // --- NEW CODE END ---

    thermalContent += `
    </div>

    <div style="text-align: center; margin: 5mm 0;">
        <svg id="barcode-thermal"></svg>
    </div>

    <div class="footer">
        <div style="font-weight: bold; margin-bottom: 2mm;">🌟 شكراً لثقتكم بنا 🌟</div>
        ${shopName ? `<div>${shopName}</div>` : ''}
        ${!shopName ? '<div>نظام Smart Shop</div>' : ''}
    </div>
</body>
</html>`;

        const printWindow = window.open('', '_blank', 'width=302,height=600');
        printWindow.document.write(thermalContent);
        printWindow.document.close();
        
        // إصلاح الباركود في الطباعة الحرارية
        const script = printWindow.document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
        script.onload = function() {
            try {
                // نستخدم دالة JsBarcode داخل النافذة الجديدة
                printWindow.JsBarcode("#barcode-thermal", String(currentInvoiceData.id).padStart(6, '0'), {
                    format: "CODE128",
                    width: 2,
                    height: 40,
                    displayValue: false,
                    margin: 0
                });
            } catch (e) { console.error(e); }
            
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
            }, 500);
        };
        printWindow.document.head.appendChild(script);
    }

    closeInvoiceModal.addEventListener('click', () => {
        invoiceModal.classList.add('hidden');
        // تحديث المنتجات فوراً بعد إغلاق الفاتورة
        loadProducts();
        showToast('تم تحديث قائمة المنتجات', true);
    });

    printInvoiceBtn.addEventListener('click', () => {
        window.print();
        // تحديث المنتجات بعد الطباعة
        setTimeout(() => {
            invoiceModal.classList.add('hidden');
            loadProducts();
        }, 1000);
    });

    thermalPrintBtn.addEventListener('click', () => {
        printThermal();
        // تحديث المنتجات بعد الطباعة الحرارية
        setTimeout(() => {
            invoiceModal.classList.add('hidden');
            loadProducts();
        }, 1000);
    });

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
            
            // If image is taller than a single PDF page, split it into slices
            // and add a small gap between pages (top + bottom) for readability.
            if (imgHeight > pdfHeight) {
                const gapMm = 10; // total gap between pages in mm
                const topMargin = gapMm / 2; // top margin on each page in mm

                const pxPerMm = canvas.width / pdfWidth; // canvas px per mm
                const sliceHeightPx = Math.floor((pdfHeight - gapMm) * pxPerMm);

                let remainingHeightPx = canvas.height;
                let pageIndex = 0;

                while (remainingHeightPx > 0) {
                    const sy = pageIndex * sliceHeightPx;
                    const sh = Math.min(sliceHeightPx, remainingHeightPx);

                    const tmpCanvas = document.createElement('canvas');
                    tmpCanvas.width = canvas.width;
                    tmpCanvas.height = sh;
                    const tmpCtx = tmpCanvas.getContext('2d');
                    tmpCtx.fillStyle = '#ffffff';
                    tmpCtx.fillRect(0, 0, tmpCanvas.width, tmpCanvas.height);
                    tmpCtx.drawImage(canvas, 0, sy, canvas.width, sh, 0, 0, canvas.width, sh);

                    const imgDataPage = tmpCanvas.toDataURL('image/png');
                    const pageImgHeightMm = (sh * pdfWidth) / canvas.width;

                    if (pageIndex > 0) pdf.addPage();

                    pdf.addImage(imgDataPage, 'PNG', 0, topMargin, pdfWidth, pageImgHeightMm);

                    remainingHeightPx -= sh;
                    pageIndex++;
                }
            } else {
                // For single-page content, place it with a small top margin as well
                const gapMm = 10;
                const topMargin = gapMm / 2;
                pdf.addImage(imgData, 'PNG', 0, topMargin, imgWidth, imgHeight);
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

        if (currentInvoiceData.delivery > 0) {
            txtContent += `التوصيل: ${currentInvoiceData.delivery.toFixed(2)} ${currency}\n`;
            // ... inside downloadTxtBtn event listener ...
            if (currentInvoiceData.deliveryCity) {
                txtContent += `مدينة التوصيل: ${currentInvoiceData.deliveryCity}\n`;
            }
        }

        txtContent += `الإجمالي: ${currentInvoiceData.total.toFixed(2)} ${currency}\n`;

        // --- NEW CODE START ---
        const recVal = currentInvoiceData.amount_received || currentInvoiceData.amountReceived || 0;
        const chgVal = currentInvoiceData.change_due || currentInvoiceData.changeDue || 0;
        
        if (recVal > 0) {
            txtContent += `المبلغ المستلم: ${parseFloat(recVal).toFixed(2)} ${currency}\n`;
            txtContent += `المبلغ الذي تم رده: ${parseFloat(chgVal).toFixed(2)} ${currency}\n`;
        }
        // --- NEW CODE END ---

        txtContent += `${'='.repeat(50)}\n\n`;
        txtContent += `${'='.repeat(50)}\n\n`;
        txtContent += `شكرا لثقتكم بنا\n\n`;
        
        if (shopName || shopPhone || shopAddress || shopCity) {
            if (shopName) txtContent += `${shopName}\n`;
            if (shopPhone) txtContent += `هاتف: ${shopPhone}\n`;

            // منطق دمج المدينة والعنوان
            let loc = [];
            if(shopCity) loc.push(shopCity);
            if(shopAddress) loc.push(shopAddress);
            if(loc.length > 0) txtContent += `${loc.join('، ')}\n`;
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
