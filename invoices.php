<?php
$page_title = 'الفواتير والضريبة';
$current_page = 'invoices.php';
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
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Header -->
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">الفواتير والضريبة</h2>
    </header>

    <div class="flex-1 overflow-y-auto p-8 relative z-10" style="max-height: calc(100vh - 5rem);">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Invoices Content -->
            <div class="lg:col-span-3 space-y-6">
                <section class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                        <span class="material-icons-round text-primary">receipt</span>
                        الفواتير الأخيرة
                    </h3>

                    <form id="invoice-search-form" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label for="search-term" class="text-sm font-medium text-gray-300 mb-1 block">بحث</label>
                            <div class="relative">
                                <input type="text" id="search-term" name="search" class="w-full bg-dark-surface/50 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-primary" placeholder="رقم الفاتورة, اسم العميل, باركود...">
                                <button type="button" id="scan-invoice-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white transition-colors" title="مسح باركود">
                                    <span class="material-icons-round">qr_code_scanner</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="search-date" class="text-sm font-medium text-gray-300 mb-1 block">التاريخ</label>
                            <input type="date" id="search-date" name="searchDate" class="w-full bg-dark-surface/50 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-primary" style="color-scheme: dark;">
                        </div>
                        <div class="md:col-span-3 flex justify-end gap-2 mt-2">
                            <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-5 py-2 rounded-lg font-bold flex items-center gap-2 transition-all">
                                <span class="material-icons-round">search</span>
                                <span>بحث</span>
                            </button>
                            <button type="button" id="clear-search-btn" class="bg-gray-600 hover:bg-gray-500 text-white px-5 py-2 rounded-lg font-bold flex items-center gap-2 transition-all">
                                <span class="material-icons-round">clear</span>
                                <span>مسح</span>
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="border-b border-white/10">
                                    <th class="p-4 text-sm font-bold text-gray-400">رقم الفاتورة</th>
                                    <th class="p-4 text-sm font-bold text-gray-400">التاريخ</th>
                                    <th class="p-4 text-sm font-bold text-gray-400">العميل</th>
                                    <th class="p-4 text-sm font-bold text-gray-400">المبلغ</th>
                                    <th class="p-4 text-sm font-bold text-gray-400"></th>
                                </tr>
                            </thead>
                            <tbody id="invoices-table-body">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">
                                        جاري تحميل البيانات...
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
                <h3 class="text-lg font-bold text-white">مسح باركود الفاتورة</h3>
                <button id="close-invoice-barcode-scanner-modal" class="text-gray-400 hover:text-white transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="p-6">
                <video id="invoice-barcode-video" class="w-full h-auto rounded-lg"></video>
                <p class="text-xs text-gray-400 mt-3 text-center">وجّه الكاميرا نحو باركود الفاتورة</p>
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
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-primary to-accent p-6 text-white no-print shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="material-icons-round text-3xl">receipt_long</span>
                    <div>
                        <h3 class="text-2xl font-bold">عرض الفاتورة</h3>
                        <p class="text-sm opacity-90">تفاصيل الفاتورة الكاملة</p>
                    </div>
                </div>
                <button id="close-invoice-modal" class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="flex-1 overflow-y-auto">
            <div id="invoice-print-area" class="p-8 bg-white text-gray-900">
                <!-- Shop Header -->
                <div class="text-center border-b-2 border-gray-300 pb-6 mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($shopName); ?></h1>
                    <?php if ($shopPhone): ?>
                        <p class="text-sm text-gray-600">هاتف: <?php echo htmlspecialchars($shopPhone); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($fullLocation)): ?>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($fullLocation); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Invoice Info -->
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

                <!-- Customer Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-gray-900 mb-2">معلومات العميل</h3>
                    <div id="customer-info" class="text-sm text-gray-700"></div>
                </div>

                <!-- Items Table -->
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
                    <p class="font-semibold text-gray-700 mb-3" style="font-size: 14px;">شكراً لثقتكم بنا</p>
                    <?php if (!empty($shopName) || !empty($shopPhone) || !empty($shopAddress)): ?>
                        <div class="mt-3 text-gray-600 space-y-1">
                            <?php if (!empty($shopName)): ?>
                                <p class="font-medium"><?php echo htmlspecialchars($shopName); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($shopPhone)): ?>
                                <p>هاتف: <?php echo htmlspecialchars($shopPhone); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($fullLocation)): ?>
                                <p><?php echo htmlspecialchars($fullLocation); ?></p>
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

        <!-- Action Buttons -->
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
    const invoicesPerPage = 500;
    let currentInvoiceData = null;
    const currency = '<?php echo $currency; ?>';
    const taxEnabled = <?php echo $taxEnabled; ?> == 1;
    const taxRate = <?php echo $taxRate; ?> / 100;
    const taxLabel = '<?php echo addslashes($taxLabel); ?>';
    const shopName = '<?php echo addslashes($shopName); ?>';
    const shopPhone = '<?php echo addslashes($shopPhone); ?>';
    const shopAddress = '<?php echo addslashes($shopAddress); ?>';
    const shopCity = '<?php echo addslashes($shopCity); ?>';

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
        invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">جاري تحميل البيانات...</td></tr>';
        
        showLoading('جاري تحميل الفواتير...');
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
                invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">فشل في تحميل الفواتير</td></tr>';
            }
        } catch (error) {
            console.error('خطأ في تحميل الفواتير:', error);
            invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">حدث خطأ في التحميل</td></tr>';
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
            invoicesTableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">لم يتم العثور على فواتير تطابق البحث</td></tr>';
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
            
            row.innerHTML = `
                <td class="p-4 text-sm font-bold text-primary">#${String(invoice.id).padStart(6, '0')}</td>
                <td class="p-4 text-sm text-gray-300">${formattedDate}</td>
                <td class="p-4 text-sm text-gray-300">${invoice.customer_name || 'عميل نقدي'}</td>
                <td class="p-4 text-sm font-bold text-white">${parseFloat(invoice.total).toFixed(2)} ${currency}</td>
                <td class="p-4">
                    <button class="view-invoice-btn bg-primary/10 hover:bg-primary/20 text-primary px-4 py-2 rounded-lg text-sm font-bold transition-all" data-id="${invoice.id}">
                        عرض
                    </button>
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
    }

    async function viewInvoice(invoiceId) {
        try {
            const response = await fetch(`api.php?action=getInvoice&id=${invoiceId}`);
            const result = await response.json();
            
            if (result.success) {
                currentInvoiceData = result.data;
                // تحويل created_at إلى Date object
                currentInvoiceData.created_at = new Date(currentInvoiceData.created_at);
                displayInvoiceDetails(currentInvoiceData);
                invoiceModal.classList.remove('hidden');
            } else {
                showToast(result.message || 'فشل في تحميل الفاتورة', false);
            }
        } catch (error) {
            console.error('خطأ في تحميل الفاتورة:', error);
            showToast('حدث خطأ في تحميل الفاتورة', false);
        }
    }

    function displayInvoiceDetails(invoice) {
        document.getElementById('invoice-number').textContent = `#${String(invoice.id).padStart(6, '0')}`;

        // توليد الباركود
        try {
            JsBarcode("#invoice-barcode", String(invoice.id).padStart(6, '0'), {
                format: "CODE128",
                width: 1,
                height: 40,
                displayValue: false,
                margin: 0
            });
        } catch (e) {
            console.error('Error generating barcode:', e);
        }

        document.getElementById('invoice-date').textContent = formatDualDate(invoice.created_at);

        // إضافة الوقت
        const invoiceDate = new Date(invoice.created_at);
        const formattedTime = invoiceDate.toLocaleTimeString('ar-SA', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: false 
        });
        document.getElementById('invoice-time').textContent = toEnglishNumbers(formattedTime);

        const customerInfo = document.getElementById('customer-info');
        if (invoice.customer_name) {
            customerInfo.innerHTML = `
                <p><strong>الاسم:</strong> ${invoice.customer_name}</p>
                ${invoice.customer_phone ? `<p><strong>الهاتف:</strong> ${invoice.customer_phone}</p>` : ''}
                ${invoice.customer_email ? `<p><strong>البريد:</strong> ${invoice.customer_email}</p>` : ''}
                ${invoice.customer_address ? `<p><strong>العنوان:</strong> ${invoice.customer_address}</p>` : ''}
            `;
        } else {
            customerInfo.innerHTML = '<p>عميل نقدي</p>';
        }
        
        const itemsTable = document.getElementById('invoice-items');
        itemsTable.innerHTML = '';
        
        const itemsCountBadge = document.getElementById('items-count-badge');
        if (invoice.items.length > 10) {
            itemsCountBadge.textContent = `إجمالي ${invoice.items.length} منتج في هذه الفاتورة`;
            itemsCountBadge.classList.remove('hidden');
        } else {
            itemsCountBadge.classList.add('hidden');
        }
        
        let subtotal = 0;
        invoice.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-200 invoice-item-row';
            row.innerHTML = `
                <td class="py-2">${index + 1}</td>
                <td class="py-2">${item.product_name}</td>
                <td class="py-2 text-center">${item.quantity}</td>
                <td class="py-2 text-center">${parseFloat(item.price).toFixed(2)} ${currency}</td>
                <td class="py-2 text-left font-medium">${itemTotal.toFixed(2)} ${currency}</td>
            `;
            itemsTable.appendChild(row);
        });
        
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax;
        
        document.getElementById('invoice-subtotal').textContent = `${subtotal.toFixed(2)} ${currency}`;
        
        if (taxEnabled) {
            document.getElementById('invoice-tax-row').style.display = 'flex';
            document.getElementById('invoice-tax-label').textContent = taxLabel;
            document.getElementById('invoice-tax-rate').textContent = (taxRate * 100).toFixed(0);
            document.getElementById('invoice-tax-amount').textContent = `${tax.toFixed(2)} ${currency}`;
        } else {
            document.getElementById('invoice-tax-row').style.display = 'none';
        }

        const existingDeliveryRow = document.getElementById('invoice-delivery-row');
        if (existingDeliveryRow) existingDeliveryRow.remove();
        const existingDeliveryCityRow = document.getElementById('invoice-delivery-city-row');
        if (existingDeliveryCityRow) existingDeliveryCityRow.remove();

        // إضافة صفوف التوصيل إذا كان موجوداً
        if (invoice.delivery_cost > 0) {
            const taxRow = document.getElementById('invoice-tax-row');
            const totalsContainer = taxRow.parentNode;
            const totalRow = totalsContainer.querySelector('.text-lg.font-bold.border-t-2') || totalsContainer.lastElementChild;
            
            // إضافة سطر التوصيل
            const deliveryRow = document.createElement('div');
            deliveryRow.id = 'invoice-delivery-row';
            deliveryRow.className = 'flex justify-between';
            deliveryRow.innerHTML = `
                <span class="text-gray-600">التوصيل:</span>
                <span class="font-medium">${parseFloat(invoice.delivery_cost).toFixed(2)} ${currency}</span>
            `;
            totalsContainer.insertBefore(deliveryRow, totalRow);
            
            // إضافة مدينة التوصيل أسفل ثمن التوصيل
            if (invoice.delivery_city) {
                const deliveryCityRow = document.createElement('div');
                deliveryCityRow.id = 'invoice-delivery-city-row';
                deliveryCityRow.className = 'flex justify-between text-sm';
                deliveryCityRow.innerHTML = `
                    <span class="text-gray-500">مدينة التوصيل:</span>
                    <span class="text-gray-600">${invoice.delivery_city}</span>
                `;
                totalsContainer.insertBefore(deliveryCityRow, totalRow);
            }
        }

        document.getElementById('invoice-total').textContent = `${total.toFixed(2)} ${currency}`;
    }

    closeInvoiceModal.addEventListener('click', () => {
        invoiceModal.classList.add('hidden');
    });

    printInvoiceBtn.addEventListener('click', () => {
        window.print();
    });

// دالة الطباعة الحرارية - invoices.php
    function printThermal() {
        if (!currentInvoiceData) return;

        const invoiceDate = new Date(currentInvoiceData.created_at);
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

    ${currentInvoiceData.customer_name ? `
    <div class="customer-section">
        <div style="font-weight: bold;">العميل: ${currentInvoiceData.customer_name}</div>
        ${currentInvoiceData.customer_phone ? `<div>📞 ${currentInvoiceData.customer_phone}</div>` : ''}
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
            <div style="font-weight:bold">${index + 1}. ${item.product_name}</div>
            <div class="item-details">
                <span>${item.quantity} × ${parseFloat(item.price).toFixed(2)}</span>
                <span style="font-weight: bold;">${itemTotal.toFixed(2)} ${currency}</span>
            </div>
        </div>`;
        });

        const subtotal = currentInvoiceData.items.reduce((acc, item) => acc + (item.price * item.quantity), 0);
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const deliveryCost = parseFloat(currentInvoiceData.delivery_cost || currentInvoiceData.delivery || 0);
        const total = subtotal + tax + deliveryCost;

        thermalContent += `</div>
            <div class="totals-section">
                <div class="total-row"><span>المجموع:</span><span>${subtotal.toFixed(2)} ${currency}</span></div>`;

        if (taxEnabled) {
            thermalContent += `<div class="total-row"><span>${taxLabel} (${(taxRate * 100).toFixed(0)}%):</span><span>${tax.toFixed(2)} ${currency}</span></div>`;
        }

        if (deliveryCost > 0) {
            thermalContent += `<div class="total-row"><span>التوصيل:</span><span>${deliveryCost.toFixed(2)} ${currency}</span></div>`;
            if (currentInvoiceData.delivery_city) {
                thermalContent += `<div class="total-row" style="font-size: 9pt; color: #666;"><span>مدينة التوصيل:</span><span>${currentInvoiceData.delivery_city}</span></div>`;
            }
        }

        thermalContent += `
            <div class="total-row grand-total"><span>الإجمالي:</span><span>${total.toFixed(2)} ${currency}</span></div>
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
        
        const invoiceDate = new Date(currentInvoiceData.created_at);
        
        let txtContent = `${shopName}\n`;
        txtContent += `${'='.repeat(50)}\n\n`;
        txtContent += `رقم الفاتورة: #${String(currentInvoiceData.id).padStart(6, '0')}\n`;
        txtContent += `التاريخ: ${formatDualDate(invoiceDate)}\n\n`;
        
        if (currentInvoiceData.customer_name) {
            txtContent += `العميل: ${currentInvoiceData.customer_name}\n`;
            if (currentInvoiceData.customer_phone) {
                txtContent += `الهاتف: ${currentInvoiceData.customer_phone}\n`;
            }
        } else {
            txtContent += `العميل: عميل نقدي\n`;
        }
        
        txtContent += `\n${'-'.repeat(50)}\n`;
        txtContent += `المنتجات (${currentInvoiceData.items.length} منتج):\n`;
        txtContent += `${'-'.repeat(50)}\n\n`;
        
        let subtotal = 0;
        currentInvoiceData.items.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            txtContent += `${index + 1}. ${item.product_name}\n`;
            txtContent += `   الكمية: ${item.quantity} × ${parseFloat(item.price).toFixed(2)} ${currency} = ${itemTotal.toFixed(2)} ${currency}\n\n`;
        });
        
        const tax = taxEnabled ? subtotal * taxRate : 0;
        const total = subtotal + tax;
        
        txtContent += `${'-'.repeat(50)}\n`;
        txtContent += `المجموع الفرعي: ${subtotal.toFixed(2)} ${currency}\n`;
        
        if (taxEnabled) {
            txtContent += `${taxLabel} (${(taxRate * 100).toFixed(0)}%): ${tax.toFixed(2)} ${currency}\n`;
        }
        
        txtContent += `الإجمالي: ${total.toFixed(2)} ${currency}\n`;
        txtContent += `${'='.repeat(50)}\n\n`;
        txtContent += `شكراً لثقتكم بنا\n\n`;
        
        if (shopName || shopPhone || shopAddress) {
            let loc = [];
            if(shopCity) loc.push(shopCity);
            if(shopAddress) loc.push(shopAddress);
            if(loc.length > 0) txtContent += `${loc.join('، ')}\n`;
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

    invoiceSearchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadInvoices(searchTermInput.value, searchDateInput.value);
    });

    clearSearchBtn.addEventListener('click', function() {
        invoiceSearchForm.reset();
        loadInvoices();
    });
    
    loadInvoices();
});
// ==========================================
// نظام مسح الباركود للفواتير
// ==========================================

const scanInvoiceBarcodeBtn = document.getElementById('scan-invoice-barcode-btn');
const invoiceBarcodeScannerModal = document.getElementById('invoice-barcode-scanner-modal');
const closeInvoiceBarcodeScannerModal = document.getElementById('close-invoice-barcode-scanner-modal');
const searchTermInput = document.getElementById('search-term');
let invoiceCodeReader = null;

// صوت عند المسح الناجح
const beepSound = new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU');

// 1. تشغيل الكاميرا عند الضغط على زر المسح
if (scanInvoiceBarcodeBtn) {
    scanInvoiceBarcodeBtn.addEventListener('click', () => {
        invoiceBarcodeScannerModal.classList.remove('hidden');
        startInvoiceBarcodeScanning();
    });
}

// 2. إغلاق نافذة المسح
if (closeInvoiceBarcodeScannerModal) {
    closeInvoiceBarcodeScannerModal.addEventListener('click', stopInvoiceBarcodeScanning);
}

// دالة بدء المسح باستخدام ZXing
async function startInvoiceBarcodeScanning() {
    try {
        invoiceCodeReader = new ZXing.BrowserMultiFormatReader();
        const videoInputDevices = await invoiceCodeReader.listVideoInputDevices();
        
        if (videoInputDevices.length === 0) {
            showToast('لم يتم العثور على كاميرا', false);
            stopInvoiceBarcodeScanning();
            return;
        }
        
        // محاولة اختيار الكاميرا الخلفية إن وجدت
        const selectedDeviceId = videoInputDevices.find(device => 
            device.label.toLowerCase().includes('back')
        )?.deviceId || videoInputDevices[0].deviceId;

        invoiceCodeReader.decodeFromVideoDevice(selectedDeviceId, 'invoice-barcode-video', (result, err) => {
            if (result) {
                handleInvoiceScannedCode(result.text);
                stopInvoiceBarcodeScanning();
            }
        });
    } catch (err) {
        console.error('خطأ في تشغيل الكاميرا:', err);
        showToast('لا يمكن الوصول للكاميرا. تأكد من السماح بالوصول للكاميرا في المتصفح.', false);
        invoiceBarcodeScannerModal.classList.add('hidden');
    }
}

// دالة إيقاف المسح
function stopInvoiceBarcodeScanning() {
    if (invoiceCodeReader) {
        invoiceCodeReader.reset();
        invoiceCodeReader = null;
    }
    invoiceBarcodeScannerModal.classList.add('hidden');
}

// 3. معالجة الكود الممسوح
function handleInvoiceScannedCode(code) {
    // تشغيل صوت
    beepSound.play().catch(e => {});
    
    // وضع الكود في خانة البحث
    searchTermInput.value = code;
    
    // تنفيذ البحث تلقائياً
    const searchForm = document.getElementById('invoice-search-form');
    if (searchForm) {
        searchForm.dispatchEvent(new Event('submit'));
    }
    
    showToast(`تم مسح الباركود: ${code}`, true);
}

// 4. دعم قارئ الباركود اليدوي (USB Scanner)
searchTermInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && searchTermInput.value.trim() !== '') {
        e.preventDefault();
        handleInvoiceScannedCode(searchTermInput.value.trim());
    }
});

// إغلاق Modal عند النقر خارجها
invoiceBarcodeScannerModal?.addEventListener('click', (e) => {
    if (e.target === invoiceBarcodeScannerModal) {
        stopInvoiceBarcodeScanning();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/index.min.js"></script>

<?php require_once 'src/footer.php'; ?>