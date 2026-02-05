<?php
require_once 'src/language.php';
$page_title = __('invoices_and_tax');
$current_page = 'invoices.php';
require_once 'session.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopName'");
$shopName = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'Smart Shop';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopPhone'");
$shopPhone = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopAddress'");
$shopAddress = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopCity'");
$shopCity = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';

$locationParts = [];
if (!empty($shopCity)) $locationParts[] = $shopCity;
if (!empty($shopAddress)) $locationParts[] = $shopAddress;
$fullLocation = implode('، ', $locationParts);

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxEnabled'");
$taxEnabled = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '1';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxRate'");
$taxRate = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '20';

$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'taxLabel'");
$taxLabel = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'TVA';
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopLogoUrl'");
$shopLogoUrl = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'invoiceShowLogo'");
$invoiceShowLogo = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '0';
?>

<style>
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
        table-layout: fixed !important;
    }

    thead {
        display: table-header-group !important;
        background: #f3f4f6 !important;
    }

    thead th {
        padding: 8px !important;
        font-weight: bold !important;
        color: #000 !important;
        border: 1px solid #d1d5db !important;
        font-size: 10pt !important;
        white-space: nowrap !important;
    }

    /* تنسيق محدد لأعمدة الجدول */
    thead th:nth-child(1) { text-align: start !important; width: 45% !important; }
    thead th:nth-child(2) { text-align: center !important; width: 15% !important; }
    thead th:nth-child(3) { text-align: center !important; width: 20% !important; }
    thead th:nth-child(4) { text-align: end !important; width: 20% !important; }

    tbody tr {
        page-break-inside: avoid !important;
        border-bottom: 1px solid #e5e7eb !important;
    }

    tbody td {
        padding: 8px !important;
        color: #000 !important;
        font-size: 10pt !important;
        border: 1px solid #e5e7eb !important;
    }

    /* تنسيق محدد لخلايا الجدول */
    tbody td:nth-child(1) { text-align: start !important; }
    tbody td:nth-child(2) { text-align: center !important; }
    tbody td:nth-child(3) { text-align: center !important; }
    tbody td:nth-child(4) { text-align: end !important; }

    /* 11. تحسين صف الكميات */
    tbody td:nth-child(2) span {
        background: #dbeafe !important;
        color: #1e40af !important;
        padding: 2px 6px !important;
        border-radius: 3px !important;
        font-weight: bold !important;
        display: inline-block !important;
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
        text-align: start !important;
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
        text-align: end !important;
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

#pagination-container {
    background-color: rgb(13 16 22);
    backdrop-filter: blur(12px);
    border-color: rgba(255, 255, 255, 0.05);
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0.5rem 0.75rem;
    background-color: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: rgb(209, 213, 219);
    border-radius: 0.625rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled):not(.opacity-50) {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
    color: white;
}

.pagination-btn:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.pagination-btn.bg-primary {
    background-color: var(--color-primary, #059669);
    border-color: var(--color-primary, #059669);
    color: white;
}

.pagination-btn.bg-primary:hover {
    background-color: var(--color-primary-hover, #047857);
}
</style>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Header -->
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white"><?php echo __('invoices_and_tax'); ?></h2>
    </header>

    <div class="flex-1 overflow-y-auto p-8 relative z-10" style="max-height: calc(100vh - 5rem);">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Invoices Content -->
            <div class="lg:col-span-3 space-y-6">
                <section class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <span class="material-icons-round text-primary">receipt</span>
                        <?php echo __('latest_invoices'); ?>
                    </h3>

                    <form id="invoice-search-form" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label for="search-term" class="text-sm font-medium text-gray-300 mb-1 block"><?php echo __('search'); ?></label>
                            <div class="relative">
                                <input type="text" id="search-term" name="search" class="w-full bg-dark-surface/50 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-primary" placeholder="<?php echo __('search_placeholder_invoices'); ?>">
                                <button type="button" id="scan-invoice-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white transition-colors" title="<?php echo __('scan_barcode_modal'); ?>">
                                    <span class="material-icons-round">qr_code_scanner</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="search-date" class="text-sm font-medium text-gray-300 mb-1 block"><?php echo __('date'); ?></label>
                            <input type="date" id="search-date" name="searchDate" class="w-full bg-dark-surface/50 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-primary" style="color-scheme: dark;">
                        </div>
                        <div class="md:col-span-3 flex justify-end gap-2 mt-2">
                            <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-5 py-2 rounded-lg font-bold flex items-center gap-2 transition-all">
                                <span class="material-icons-round">search</span>
                                <span><?php echo __('search'); ?></span>
                            </button>
                            <button type="button" id="clear-search-btn" class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2 rounded-lg font-bold flex items-center gap-2 transition-all">
                                <span class="material-icons-round">clear</span>
                                <span><?php echo __('clear_search'); ?></span>
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="border-b border-white/10">
                                    <th class="p-4 text-sm font-bold text-gray-400"><?php echo __('invoice_number'); ?></th>
                                    <th class="p-4 text-sm font-bold text-gray-400"><?php echo __('date'); ?></th>
                                    <th class="p-4 text-sm font-bold text-gray-400"><?php echo __('customer'); ?></th>
                                    <th class="p-4 text-sm font-bold text-gray-400"><?php echo __('amount'); ?></th>
                                    <th class="p-4 text-sm font-bold text-gray-400"></th>
                                </tr>
                            </thead>
                            <tbody id="invoices-table-body">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">
                                        <?php echo __('loading_data'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- Barcode Scanner Modal for Invoices -->
    <div id="invoice-barcode-scanner-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
        <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
            <div class="p-6 border-b border-white/5 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white"><?php echo __('scan_invoice_barcode'); ?></h3>
                <button id="close-invoice-barcode-scanner-modal" class="text-gray-400 hover:text-white transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="p-6">
                <video id="invoice-barcode-video" class="w-full h-auto rounded-lg"></video>
                <p class="text-xs text-gray-400 mt-3 text-center"><?php echo __('point_camera_invoice'); ?></p>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination-container" class="p-6 pt-2 flex justify-center items-center relative z-10 shrink-0">
        <!-- Pagination will be loaded here -->
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
                        <h3 class="text-2xl font-bold"><?php echo __('invoice_success'); ?></h3>
                        <p class="text-sm opacity-90"><?php echo __('sale_completed'); ?></p>
                    </div>
                </div>
                <button id="close-invoice-modal" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto">
            <div id="invoice-print-area" class="p-8 bg-white text-gray-900" dir="<?php echo get_dir(); ?>">
                            <!-- Header: Invoice Title and Logo -->
                <div class="flex items-center justify-between pb-6 mb-6 border-b-2 border-gray-300">
                    <div>
                        <h1 class="text-4xl font-extrabold text-green-600"><?php echo __('invoice_header'); ?></h1>
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
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-3"><?php echo __('shop_info'); ?></h3>
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
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-3"><?php echo __('client_info'); ?></h3>
                        <div id="customer-info" class="text-sm text-gray-700 space-y-1"></div>
                    </div>
                </div>

                <!-- Two Columns: Issue Date and Invoice Number with Barcode -->
                <!-- Three Columns: Date, Barcode, Invoice Number -->
                <div class="grid grid-cols-3 gap-6 pb-6 mb-6 border-b border-gray-200 invoice-header-grid">
                        <!-- تاريخ الإصدار - يمين -->
                    <div class="text-start">
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-2"><?php echo __('issue_date'); ?></h3>
                        <p class="text-base font-bold text-gray-900" id="invoice-date">-</p>
                        <p class="text-sm text-gray-600" id="invoice-time">-</p>
                    </div>

                    <!-- الباركود - وسط -->
                    <div class="flex flex-col items-center justify-start">
                        <svg id="invoice-barcode" style="max-width: 200px; height: 50px; margin: 0 auto;"></svg>
                    </div>

                    <!-- رقم الفاتورة - يسار -->
                    <div class="text-end">
                        <h3 class="text-xs font-bold text-gray-500 uppercase mb-2"><?php echo __('invoice_no'); ?></h3>
                        <p class="text-2xl font-bold text-gray-900" id="invoice-number">-</p>
                    </div>
                </div>
            
                <div class="mb-6">
                    <div class="rounded-2xl border-2 border-gray-200 overflow-hidden bg-white shadow-sm">
                        <table class="w-full text-sm invoice-items-container">
                            <thead class="bg-gray-100">
                                <tr class="border-b-2 border-gray-300">
                                    <th class="text-start py-3 px-4 font-bold text-gray-800 text-sm uppercase"><?php echo __('product_col'); ?></th>
                                    <th class="text-center py-3 px-4 font-bold text-gray-800 text-sm uppercase"><?php echo __('quantity_col'); ?></th>
                                    <th class="text-center py-3 px-4 font-bold text-gray-800 text-sm uppercase"><?php echo __('price_col'); ?></th>
                                    <th class="text-end py-3 px-4 font-bold text-gray-800 text-sm uppercase"><?php echo __('total_col'); ?></th>
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
                                <span class="text-gray-600 font-semibold"><?php echo __('invoice_subtotal_label'); ?></span>
                                <span class="font-bold text-gray-800 text-base" id="invoice-subtotal">-</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-200" id="invoice-tax-row">
                                <span class="text-gray-600 font-semibold"><span id="invoice-tax-label">TVA</span> (<span id="invoice-tax-rate">20</span>%):</span>
                                <span class="font-bold text-gray-800 text-base" id="invoice-tax-amount">-</span>
                            </div>
                            <div class="flex justify-between items-center text-2xl font-extrabold border-t-4 border-primary/30 pt-4 mt-2">
                                <span class="text-gray-800"><?php echo __('invoice_total_label'); ?></span>
                                <span class="text-primary text-3xl" id="invoice-total">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-8 pt-6 border-t-2 border-gray-300">
                    <p class="font-bold text-gray-800 mb-2" style="font-size: 18px;"><?php echo __('thanks_for_trust'); ?></p>
                    <p class="text-gray-600 italic" style="font-size: 13px;"><?php echo __('happy_to_serve'); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-6 grid grid-cols-2 gap-3 no-print border-t shrink-0">
            <button id="print-invoice-btn" class="bg-primary hover:bg-primary-hover text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">print</span>
                <?php echo __('print_direct'); ?>
            </button>
            <button id="thermal-print-btn" class="bg-purple-600 hover:bg-purple-700 text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">receipt_long</span>
                <?php echo __('thermal_print'); ?>
            </button>
            <button id="download-pdf-btn" class="bg-accent hover:bg-lime-500 text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">picture_as_pdf</span>
                <?php echo __('download_pdf'); ?>
            </button>
            <button id="download-txt-btn" class="bg-gray-700 hover:bg-gray-600 text-white py-3 px-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all text-sm">
                <span class="material-icons-round text-lg">text_snippet</span>
                <?php echo __('download_txt'); ?>
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const invoicesTableBody = document.getElementById('invoices-table-body');
    const invoiceModal = document.getElementById('invoice-modal');
    const closeInvoiceModal = document.getElementById('close-invoice-modal');
    const printInvoiceBtn = document.getElementById('print-invoice-btn');
    const thermalPrintBtn = document.getElementById('thermal-print-btn');
    const downloadPdfBtn = document.getElementById('download-pdf-btn');
    const downloadTxtBtn = document.getElementById('download-txt-btn');
    const invoiceSearchForm = document.getElementById('invoice-search-form');
    const searchTermInput = document.getElementById('search-term');
    const searchDateInput = document.getElementById('search-date');
    const clearSearchBtn = document.getElementById('clear-search-btn');
    const paginationContainer = document.getElementById('pagination-container');

    let currentPage = 1;
    const invoicesPerPage = 200;
    let currentInvoiceData = null;
    const currency = '<?php echo $currency; ?>';
    const taxEnabled = <?php echo $taxEnabled; ?> == 1;
    const taxRate = <?php echo $taxRate; ?> / 100;
    const taxLabel = '<?php echo addslashes($taxLabel); ?>';
    const shopName = '<?php echo addslashes($shopName); ?>';
    const shopPhone = '<?php echo addslashes($shopPhone); ?>';
    const shopAddress = '<?php echo addslashes($shopAddress); ?>';
    const shopCity = '<?php echo addslashes($shopCity); ?>';
    const userRole = '<?php echo $_SESSION['role']; ?>';
    
    // Localization from PHP
    const currentLang = '<?php echo get_locale(); ?>';
    const currentDir = '<?php echo get_dir(); ?>';

    function toEnglishNumbers(str) {
        const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        
        let result = str.toString();
        for (let i = 0; i < 10; i++) {
            result = result.replace(new RegExp(arabicNumbers[i], 'g'), englishNumbers[i]);
        }
        return result;
    }

    function formatDualDate(dateString) {
        const date = new Date(dateString);
        
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

    async function loadInvoices(searchTerm = '', searchDate = '') {
        invoicesTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-gray-500">${window.__('loading_data')}</td></tr>`;
        
        showLoading(window.__('loading_invoices'));
        const params = new URLSearchParams({
            action: 'getInvoices',
            search: searchTerm,
            searchDate: searchDate,
            page: currentPage,
            limit: invoicesPerPage
        });
        
        try {
            const response = await fetch(`api.php?${params.toString()}`);
            const result = await response.json();
            
            if (result.success) {
                displayInvoices(result.data);
                renderPagination(result.total_invoices);
            } else {
                invoicesTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-gray-500">${window.__('failed_loading_invoices')}</td></tr>`;
            }
        } catch (error) {
            console.error('خطأ في تحميل الفواتير:', error);
            invoicesTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-gray-500">${window.__('error_loading')}</td></tr>`;
        } finally {
            hideLoading();
        }
    }

    function renderPagination(totalInvoices) {
        const totalPages = Math.ceil(totalInvoices / invoicesPerPage);
        paginationContainer.innerHTML = '';

        if (totalPages <= 1) return;

        let paginationHTML = `
            <div class="flex items-center gap-2">
        `;
        
        paginationHTML += `<button class="pagination-btn ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}><span class="material-icons-round">chevron_right</span></button>`;

        const pagesToShow = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) pagesToShow.push(i);
        } else {
            if (currentPage <= 4) {
                for (let i = 1; i <= 5; i++) pagesToShow.push(i);
                pagesToShow.push('...');
                pagesToShow.push(totalPages);
            } else if (currentPage >= totalPages - 3) {
                pagesToShow.push(1);
                pagesToShow.push('...');
                for (let i = totalPages - 4; i <= totalPages; i++) pagesToShow.push(i);
            } else {
                pagesToShow.push(1);
                pagesToShow.push('...');
                for (let i = currentPage - 2; i <= currentPage + 2; i++) pagesToShow.push(i);
                pagesToShow.push('...');
                pagesToShow.push(totalPages);
            }
        }

        pagesToShow.forEach(page => {
            if (page === '...') {
                paginationHTML += `<span class="px-2 py-1">...</span>`;
            } else {
                paginationHTML += `<button class="pagination-btn ${page === currentPage ? 'bg-primary text-white' : 'hover:bg-white/10'}" data-page="${page}">${page}</button>`;
            }
        });
        
        paginationHTML += `<button class="pagination-btn ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}><span class="material-icons-round">chevron_left</span></button>`;
        paginationHTML += `</div>`;
        paginationContainer.innerHTML = paginationHTML;
    }

    paginationContainer.addEventListener('click', e => {
        if (e.target.closest('.pagination-btn')) {
            const btn = e.target.closest('.pagination-btn');
            currentPage = parseInt(btn.dataset.page);
            loadInvoices(searchTermInput.value, searchDateInput.value);
        }
    });

    function displayInvoices(invoices) {
        invoicesTableBody.innerHTML = '';
        
        if (invoices.length === 0) {
            invoicesTableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-gray-500">${window.__('no_invoices_found')}</td></tr>`;
            return;
        }

        invoices.forEach(invoice => {
            const row = document.createElement('tr');
            row.className = 'border-b border-white/5 hover:bg-white/5 transition-colors';
            
            const invoiceDate = new Date(invoice.created_at);
            const gregorianDate = invoiceDate.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
            const formattedDate = toEnglishNumbers(gregorianDate);
            
            let actionButtons = `
                <div class="flex items-center gap-2">
                    <button class="view-invoice-btn bg-primary/10 hover:bg-primary/20 text-primary px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2" data-id="${invoice.id}">
                        <span class="material-icons-round text-base">visibility</span>
                        ${window.__('view')}
                    </button>`;

            if (userRole === 'admin') {
                if (parseInt(invoice.is_refunded) === 1) {
                    actionButtons += `
                    <button class="bg-gray-500/10 text-gray-500 px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 cursor-not-allowed opacity-50" disabled title="${window.__('refunded')}">
                        <span class="material-icons-round text-base">assignment_return</span>
                        ${window.__('refunded')}
                    </button>`;
                } else {
                    actionButtons += `
                        <button class="refund-invoice-btn bg-red-500/10 hover:bg-red-500/20 text-red-500 px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2" data-id="${invoice.id}">
                            <span class="material-icons-round text-base">assignment_return</span>
                            ${window.__('refund')}
                        </button>`;
                }
            }
            
            actionButtons += `</div>`;

            row.innerHTML = `
                <td class="p-4 text-sm font-bold text-primary">#${String(invoice.id).padStart(6, '0')}</td>
                <td class="p-4 text-sm text-gray-300">${formattedDate}</td>
                <td class="p-4 text-sm text-gray-300">${invoice.customer_name || window.__('cash_customer')}</td>
                <td class="p-4 text-sm font-bold text-white">${parseFloat(invoice.total).toFixed(2)} ${currency}</td>
                <td class="p-4">
                    ${actionButtons}
                </td>
            `;
            
            invoicesTableBody.appendChild(row);
        });

        document.querySelectorAll('.view-invoice-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const invoiceId = this.dataset.id;
                await viewInvoice(invoiceId);
            });
        });

        document.querySelectorAll('.refund-invoice-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const invoiceId = this.dataset.id;
                await handleRefund(invoiceId);
            });
        });
    }

    async function handleRefund(invoiceId) {
        const { value: reason } = await Swal.fire({
            title: window.__('confirm_refund_title'),
            text: window.__('confirm_refund_message').replace('%s', invoiceId),
            input: 'text',
            inputLabel: window.__('refund_reason_label'),
            inputPlaceholder: window.__('refund_reason_placeholder'),
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: window.__('confirm_refund_btn'),
            cancelButtonText: window.__('cancel')
        });

        if (reason !== undefined) { // If confirmed (reason can be empty string)
            showLoading(window.__('processing_refund'));
            try {
                const response = await fetch('api.php?action=refund_invoice', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ invoice_id: invoiceId, reason: reason })
                });
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire(window.__('done'), result.message, 'success');
                    loadInvoices(); // Reload table
                } else {
                    Swal.fire(window.__('error'), result.message, 'error');
                }
            } catch (error) {
                console.error('Error refunding invoice:', error);
                Swal.fire(window.__('error'), window.__('unexpected_error'), 'error');
            } finally {
                hideLoading();
            }
        }
    }

    async function viewInvoice(invoiceId) {
        try {
            const response = await fetch(`api.php?action=getInvoice&id=${invoiceId}`);
            const result = await response.json();
            
            if (result.success) {
                currentInvoiceData = result.data;
                currentInvoiceData.date = new Date(currentInvoiceData.created_at);
                displayInvoiceDetails(currentInvoiceData);
                invoiceModal.classList.remove('hidden');
            } else {
                showToast(result.message || window.__('failed_loading_invoices'), false);
            }
        } catch (error) {
            console.error('خطأ في تحميل الفواتير:', error);
            showToast(window.__('error_loading'), false);
        }
    }

    function displayInvoiceDetails(data) {
        document.getElementById('invoice-number').textContent = `#${String(data.id).padStart(6, '0')}`;

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

        const invoiceDate = new Date(data.created_at);
        document.getElementById('invoice-date').textContent = formatDualDate(invoiceDate);
        const formattedTime = toEnglishNumbers(invoiceDate.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit', hour12: false }));
        document.getElementById('invoice-time').textContent = formattedTime;

        const customerInfo = document.getElementById('customer-info');
        if (data.customer_name) {
            customerInfo.innerHTML = `
                <p class="font-bold text-base">${data.customer_name}</p>
                ${data.customer_phone ? `<p>${data.customer_phone}</p>` : ''}
                ${data.customer_address ? `<p>${data.customer_address}</p>` : ''}
            `;
        } else {
            customerInfo.innerHTML = `<p class="font-bold">${window.__('cash_customer')}</p><p class="text-gray-500">${window.__('default')}</p>`;
        }
        
        const itemsTable = document.getElementById('invoice-items');
        itemsTable.innerHTML = '';
        
        const itemsCountBadge = document.getElementById('items-count-badge');
        if (data.items.length > 10) {
            itemsCountBadge.textContent = window.__('items_count_badge').replace('%d', data.items.length);
            itemsCountBadge.classList.remove('hidden');
        } else {
            itemsCountBadge.classList.add('hidden');
        }
        
        let subtotal = 0;
        data.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200 invoice-item-row hover:bg-gray-50 transition-colors';
            row.innerHTML = `
                <td class="py-3 px-4 text-gray-800 font-bold text-start">${item.product_name}</td>
                <td class="py-3 px-4 text-center">
                    <span class="inline-block bg-blue-50 text-blue-700 font-bold px-3 py-1 rounded-lg text-sm">
                        ${item.quantity}
                    </span>
                </td>
                <td class="py-3 px-4 text-center text-gray-700 font-semibold">${parseFloat(item.price).toFixed(2)} ${currency}</td>
                <td class="py-3 px-4 text-end">
                    <span class="font-extrabold text-gray-900 text-base">
                        ${itemTotal.toFixed(2)} ${currency}
                    </span>
                </td>
            `;
            itemsTable.appendChild(row);
        });

        // Calculate discount
        const discountAmount = parseFloat(data.discount_amount) || 0;
        const discountPercent = parseFloat(data.discount_percent) || 0;
        const tax = taxEnabled ? subtotal * taxRate : 0;
        
        document.getElementById('invoice-subtotal').textContent = `${subtotal.toFixed(2)} ${currency}`;
        
        if (taxEnabled) {
            document.getElementById('invoice-tax-row').style.display = 'flex';
            document.getElementById('invoice-tax-label').textContent = taxLabel;
            document.getElementById('invoice-tax-rate').textContent = (taxRate * 100).toFixed(0);
            document.getElementById('invoice-tax-amount').textContent = `${tax.toFixed(2)} ${currency}`;
        } else {
            document.getElementById('invoice-tax-row').style.display = 'none';
        }
        
        // Remove existing discount row if present
        const existingDiscountRow = document.getElementById('invoice-discount-row');
        if (existingDiscountRow) existingDiscountRow.remove();
        
        // Add discount row if discount exists
        if (discountAmount > 0) {
            const taxRow = document.getElementById('invoice-tax-row');
            const totalsContainer = taxRow.parentNode;
            const totalRow = totalsContainer.querySelector('.text-2xl.font-extrabold') || totalsContainer.lastElementChild;
            
            const discountRow = document.createElement('div');
            discountRow.id = 'invoice-discount-row';
            discountRow.className = 'flex justify-between items-center py-2 border-b border-gray-200';
            discountRow.innerHTML = `
                <span class="text-gray-600 font-semibold">${window.__('discount')} (<span id="invoice-discount-percent">${discountPercent.toFixed(2)}</span>%):</span>
                <span class="font-bold text-red-500 text-base">-${discountAmount.toFixed(2)} ${currency}</span>
            `;
            totalsContainer.insertBefore(discountRow, totalRow);
        }
        
        document.getElementById('invoice-total').textContent = `${parseFloat(data.total).toFixed(2)} ${currency}`;

        const totalsContainer = document.getElementById('invoice-tax-row').parentNode;
        const existingReceived = document.getElementById('invoice-received-row');
        if (existingReceived) existingReceived.remove();
        const existingChange = document.getElementById('invoice-change-row');
        if (existingChange) existingChange.remove();

        if (data.amount_received > 0) {
            const receivedRow = document.createElement('div');
            receivedRow.id = 'invoice-received-row';
            receivedRow.className = 'flex justify-between items-center text-sm mt-2 pt-2 border-t border-dashed border-gray-300';
            receivedRow.innerHTML = `
                <span class="text-gray-600 font-bold">${window.__('amount_received_label')}</span>
                <span class="font-bold text-gray-800">${parseFloat(data.amount_received).toFixed(2)} ${currency}</span>
            `;
            totalsContainer.appendChild(receivedRow);

            const changeRow = document.createElement('div');
            changeRow.id = 'invoice-change-row';
            changeRow.className = 'flex justify-between items-center text-sm';
            changeRow.innerHTML = `
                <span class="text-gray-600 font-bold">${window.__('change_due_label')}</span>
                <span class="font-bold text-gray-800">${parseFloat(data.change_due).toFixed(2)} ${currency}</span>
            `;
            totalsContainer.appendChild(changeRow);
        }
    }

    closeInvoiceModal.addEventListener('click', () => {
        invoiceModal.classList.add('hidden');
    });

    printInvoiceBtn.addEventListener('click', () => {
        window.print();
    });

    function printThermal() {
        if (!currentInvoiceData) return;

        const invoiceDate = new Date(currentInvoiceData.created_at);
        const formattedDate = formatDualDate(invoiceDate);
        const formattedTime = toEnglishNumbers(invoiceDate.toLocaleTimeString('ar-SA', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        }));

        let locationText = [shopCity, shopAddress].filter(Boolean).join('، ');

        let thermalContent = `<!DOCTYPE html>
<html dir="${currentDir}" lang="${currentLang}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm">
    <title>${window.__('invoice_header')} #${String(currentInvoiceData.id).padStart(6, '0')}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; width: 80mm; padding: 5mm; font-size: 11pt; line-height: 1.4; background: white; color: #000; }
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
        <div class="info-row"><span>${window.__('invoice_number')}:</span><span>#${String(currentInvoiceData.id).padStart(6, '0')}</span></div>
        <div class="info-row"><span>${window.__('date')}:</span><span>${formattedDate}</span></div>
        <div class="info-row"><span>${window.__('time')}:</span><span>${formattedTime}</span></div>
    </div>
    <div class="customer-section">
        <div style="font-weight: bold;">${window.__('customer')}: ${currentInvoiceData.customer_name || window.__('cash_customer')}</div>
        ${currentInvoiceData.customer_phone ? `<div>📞 ${currentInvoiceData.customer_phone}</div>` : ''}
        ${currentInvoiceData.customer_address ? `<div>📍 ${currentInvoiceData.customer_address}</div>` : ''}
    </div>
    <div class="items-table">
        <div class="items-header">${window.__('product_col')} (${currentInvoiceData.items.length})</div>`;

        let subtotal = 0;
        currentInvoiceData.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            thermalContent += `
        <div class="item-row">
            <div style="font-weight:bold">${index + 1}. ${item.product_name}</div>
            <div class="item-details">
                <span>${item.quantity} × ${parseFloat(item.price).toFixed(2)}</span>
                <span style="font-weight: bold;">${itemTotal.toFixed(2)} ${currency}</span>
            </div>
        </div>`;
        });
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const discountAmount = parseFloat(currentInvoiceData.discount_amount) || 0;
        const discountPercent = parseFloat(currentInvoiceData.discount_percent) || 0;

        thermalContent += `</div>
            <div class="totals-section">
                <div class="total-row"><span>${window.__('invoice_subtotal_label')}</span><span>${subtotal.toFixed(2)} ${currency}</span></div>`;

        if (taxEnabled) {
            thermalContent += `<div class="total-row"><span>${taxLabel} (${(taxRate * 100).toFixed(0)}%):</span><span>${tax.toFixed(2)} ${currency}</span></div>`;
        }
        
        // Add discount if applicable
        if (discountAmount > 0) {
            thermalContent += `<div class="total-row"><span>${window.__('discount')} (${discountPercent.toFixed(2)}%):</span><span>-${discountAmount.toFixed(2)} ${currency}</span></div>`;
        }
        
        thermalContent += `<div class="total-row grand-total"><span>${window.__('invoice_total_label')}</span><span>${parseFloat(currentInvoiceData.total).toFixed(2)} ${currency}</span></div>`;

        if (currentInvoiceData.amount_received > 0) {
            thermalContent += `
            <div class="total-row" style="border-top: 1px dashed #000; margin-top: 2mm; padding-top: 2mm;">
                <span>${window.__('amount_received_label')}</span>
                <span>${parseFloat(currentInvoiceData.amount_received).toFixed(2)} ${currency}</span>
            </div>
            <div class="total-row">
                <span>${window.__('change_due_label')}</span>
                <span>${parseFloat(currentInvoiceData.change_due).toFixed(2)} ${currency}</span>
            </div>`;
        }

        thermalContent += `</div>
    <div style="text-align: center; margin: 5mm 0;"><svg id="barcode-thermal"></svg></div>
    <div class="footer">
        <div style="font-weight: bold; margin-bottom: 2mm;">🌟 ${window.__('thanks_for_trust')} 🌟</div>
        <div>${shopName || window.__('smart_shop_system')}</div>
    </div>
</body></html>`;

        const printWindow = window.open('', '_blank', 'width=302,height=600');
        printWindow.document.write(thermalContent);
        printWindow.document.close();
        
        const script = printWindow.document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
        script.onload = function() {
            try {
                printWindow.JsBarcode("#barcode-thermal", String(currentInvoiceData.id).padStart(6, '0'), {
                    format: "CODE128", width: 2, height: 40, displayValue: false, margin: 0
                });
            } catch (e) { console.error(e); }
            
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
            }, 500);
        };
        printWindow.document.head.appendChild(script);
    }

    thermalPrintBtn.addEventListener('click', printThermal);

    downloadPdfBtn.addEventListener('click', async () => {
        const { jsPDF } = window.jspdf;
        
        try {
            showToast(window.__('generating_pdf'), true);
            
            // احفظ خصائص CSS الأصلية قبل التعديل
            const element = document.getElementById('invoice-print-area');
            const originalStyles = {
                maxHeight: element.style.maxHeight,
                overflow: element.style.overflow,
                position: element.style.position
            };
            
            // Initialize itemsOriginalStyles to avoid undefined reference
            let itemsOriginalStyles = null;
            
            // أضف أي عناصر أخرى تحتاج إلى تعديل
            const invoiceItemsContainer = document.querySelector('.invoice-items-container');
            if (invoiceItemsContainer) {
                itemsOriginalStyles = {
                    maxHeight: invoiceItemsContainer.style.maxHeight,
                    overflow: invoiceItemsContainer.style.overflow
                };
                invoiceItemsContainer.style.maxHeight = 'none';
                invoiceItemsContainer.style.overflow = 'visible';
            }
            
            // ضبط العنصر للتصوير
            element.style.maxHeight = 'none';
            element.style.overflow = 'visible';
            element.style.position = 'relative';
            
            const canvas = await html2canvas(element, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true,
                scrollY: 0
            });
            
            // استعادة الخصائص الأصلية
            element.style.maxHeight = originalStyles.maxHeight;
            element.style.overflow = originalStyles.overflow;
            element.style.position = originalStyles.position;
            
            if (invoiceItemsContainer && itemsOriginalStyles) {
                invoiceItemsContainer.style.maxHeight = itemsOriginalStyles.maxHeight;
                invoiceItemsContainer.style.overflow = itemsOriginalStyles.overflow;
            }
            
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const imgWidth = pdfWidth;
            const imgHeight = (canvas.height * pdfWidth) / canvas.width;
            
            // If image is taller than a single PDF page, split it into slices
            if (imgHeight > pdfHeight) {
                const gapMm = 10;
                const topMargin = gapMm / 2;
                const pxPerMm = canvas.width / pdfWidth;
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
                const gapMm = 10;
                const topMargin = gapMm / 2;
                pdf.addImage(imgData, 'PNG', 0, topMargin, imgWidth, imgHeight);
            }
            
            pdf.save(`invoice-${currentInvoiceData.id}.pdf`);
            showToast(window.__('pdf_downloaded'), true);
        } catch (error) {
            console.error('خطأ في تحميل PDF:', error);
            showToast(window.__('pdf_fail'), false);
        }
    });

    downloadTxtBtn.addEventListener('click', () => {
        if (!currentInvoiceData) return;
        
        const invoiceDate = new Date(currentInvoiceData.created_at);
        let txtContent = `${shopName}
${'='.repeat(50)}

${window.__('invoice_number')}: #${String(currentInvoiceData.id).padStart(6, '0')}
${window.__('date')}: ${formatDualDate(invoiceDate)}

`;
        
        if (currentInvoiceData.customer_name) {
            txtContent += `${window.__('customer')}: ${currentInvoiceData.customer_name}\n`;
            if (currentInvoiceData.customer_phone) txtContent += `${window.__('phone_placeholder')}: ${currentInvoiceData.customer_phone}\n`;
            if (currentInvoiceData.customer_address) txtContent += `${window.__('address_placeholder')}: ${currentInvoiceData.customer_address}\n`;
        } 
        
        else {
            txtContent += `${window.__('customer')}: ${window.__('cash_customer')}\n`;
        }
        
        txtContent += `
${'-'.repeat(50)}
${window.__('product_col')} (${currentInvoiceData.items.length} ${window.__('product')}):
${'-'.repeat(50)}

`;
        
        let subtotal = 0;
        currentInvoiceData.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            txtContent += `${index + 1}. ${item.product_name}\n   ${window.__('quantity')}: ${item.quantity} × ${parseFloat(item.price).toFixed(2)} ${currency} = ${itemTotal.toFixed(2)} ${currency}\n\n`;
        });
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const discountAmount = parseFloat(currentInvoiceData.discount_amount) || 0;
        const discountPercent = parseFloat(currentInvoiceData.discount_percent) || 0;
                
        txtContent += `${'-'.repeat(50)}\n${window.__('invoice_subtotal_label')} ${subtotal.toFixed(2)} ${currency}\n`;
        if (taxEnabled) txtContent += `${taxLabel} (${(taxRate * 100).toFixed(0)}%): ${tax.toFixed(2)} ${currency}\n`;
                
        // Add discount if applicable
        if (discountAmount > 0) txtContent += `${window.__('discount')} (${discountPercent.toFixed(2)}%): -${discountAmount.toFixed(2)} ${currency}\n`;
                
        txtContent += `${window.__('invoice_total_label')} ${parseFloat(currentInvoiceData.total).toFixed(2)} ${currency}
`;

        if (currentInvoiceData.amount_received > 0) {
            txtContent += `${window.__('amount_received_label')} ${parseFloat(currentInvoiceData.amount_received).toFixed(2)} ${currency}\n`;
            txtContent += `${window.__('change_due_label')} ${parseFloat(currentInvoiceData.change_due).toFixed(2)} ${currency}\n`;
        }

        txtContent += `${'='.repeat(50)}

${window.__('thanks_for_trust')}

`;
        
        let loc = [shopCity, shopAddress].filter(Boolean).join('، ');
        if (shopName || shopPhone || loc) {
            if (shopName) txtContent += `${shopName}\n`;
            if (shopPhone) txtContent += `${window.__('phone_placeholder')}: ${shopPhone}\n`;
            if (loc) txtContent += `${loc}\n`;
        } else {
            txtContent += `${window.__('developed_by')} حمزة سعدي\nhttps://eagleshadow.technology\n`;
        }
        
        const blob = new Blob([txtContent], { type: 'text/plain;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `invoice-${currentInvoiceData.id}.txt`;
        link.click();
        showToast(window.__('txt_downloaded'), true);
    });

    invoiceSearchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        loadInvoices(searchTermInput.value, searchDateInput.value);
    });

    clearSearchBtn.addEventListener('click', function() {
        invoiceSearchForm.reset();
        currentPage = 1;
        loadInvoices();
    });

    // Barcode scanner logic
    const scanInvoiceBarcodeBtn = document.getElementById('scan-invoice-barcode-btn');
    const invoiceBarcodeScannerModal = document.getElementById('invoice-barcode-scanner-modal');
    const closeInvoiceBarcodeScannerModal = document.getElementById('close-invoice-barcode-scanner-modal');
    const invoiceBarcodeVideo = document.getElementById('invoice-barcode-video');
    
    let invoiceCodeReader;
    
    scanInvoiceBarcodeBtn.addEventListener('click', () => {
        invoiceBarcodeScannerModal.classList.remove('hidden');
        startInvoiceBarcodeScanner();
    });

    closeInvoiceBarcodeScannerModal.addEventListener('click', () => {
        stopInvoiceBarcodeScanner();
    });

    async function startInvoiceBarcodeScanner() {
        if (typeof ZXing === 'undefined') {
            showToast(window.__('scanner_library_error'), false);
            return;
        }
        
        invoiceCodeReader = new ZXing.BrowserMultiFormatReader();
        try {
            const videoInputDevices = await invoiceCodeReader.listVideoInputDevices();
            if (videoInputDevices.length > 0) {
                invoiceCodeReader.decodeFromVideoDevice(videoInputDevices[1].deviceId, 'invoice-barcode-video', (result, err) => {
                    if (result) {
                        searchTermInput.value = result.text;
                        stopInvoiceBarcodeScanner();
                        invoiceSearchForm.dispatchEvent(new Event('submit'));
                        showToast(window.__('barcode_found'), true);
                    }
                    if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.error('Barcode scan error:', err);
                        showToast(window.__('scan_error'), false);
                        stopInvoiceBarcodeScanner();
                    }
                });
            } else {
                showToast(window.__('camera_not_found'), false);
            }
        } catch (error) {
            console.error('Error starting barcode scanner:', error);
            showToast(window.__('scanner_start_fail'), false);
        }
    }

    function stopInvoiceBarcodeScanner() {
        if (invoiceCodeReader) {
            invoiceCodeReader.reset();
        }
        invoiceBarcodeScannerModal.classList.add('hidden');
    }
    
    loadInvoices();
});
</script>

<div id="loading-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl p-12 border border-white/10 flex flex-col items-center gap-6">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-transparent border-t-primary border-r-primary rounded-full animate-spin"></div>
            <div class="absolute inset-2 border-4 border-transparent border-b-primary/50 rounded-full animate-spin" style="animation-direction: reverse;"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-white mb-2"><?php echo __('loading'); ?></h3>
            <p id="loading-message" class="text-sm text-gray-400"><?php echo __('please_wait'); ?></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/index.min.js"></script>

<script>
    // دوال إدارة شاشة التحميل
    function showLoadingOverlay(message = '<?php echo __('processing'); ?>') {
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
    }

    function hideLoadingOverlay() {
        const loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.classList.add('hidden');
    }
</script>

<?php require_once 'src/footer.php'; ?>