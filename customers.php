<?php
$page_title = 'إدارة العملاء';
$current_page = 'customers.php';
require_once 'session.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
require_once 'db.php';
?>

<style>
    /* Pagination Styles Matching removed_products.php */
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

    /* Toast Notification */
    .toast-notification {
        position: fixed;
        bottom: 2rem;
        left: 2rem;
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px rgba(5, 150, 105, 0.3);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 9999;
        animation: slideInUp 0.3s ease-out;
        font-weight: 500;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(1rem);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .toast-notification.remove {
        animation: slideOutDown 0.3s ease-out forwards;
    }

    @keyframes slideOutDown {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(1rem);
        }
    }

    .toast-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
</style>

<main class="flex-1 flex flex-col relative overflow-hidden bg-dark">
    <div class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-primary">groups</span>
            <span>إدارة العملاء</span>
        </h2>
        
        <div class="flex items-center gap-3">
            <button id="export-excel-btn"
                class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 border border-emerald-500/30 px-4 py-2 rounded-xl font-medium flex items-center gap-2 transition-all hover:border-emerald-500/50">
                <span class="material-icons-round text-sm">download</span>
                <span>تصدير Excel</span>
            </button>
            <button id="add-customer-btn"
                class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">add</span>
                <span>إضافة عميل</span>
            </button>
        </div>
    </header>

    <div class="p-6 flex flex-col md:flex-row gap-4 items-center justify-between relative z-10 shrink-0">
        <div class="relative w-full md:w-96">
            <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
            <input type="text" id="customer-search-input" placeholder="بحث عن اسم، هاتف، أو باركود..."
                class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-10 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
            <button type="button" id="camera-scan-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white transition-colors" title="مسح الباركود بالكاميرا">
                <span class="material-icons-round">qr_code_scanner</span>
            </button>
        </div>
        </div>

    <div class="flex-1 flex flex-col overflow-hidden p-6 pt-0 relative z-10">
        <div class="flex-1 flex flex-col bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <table class="w-full">
                    <thead class="sticky top-0 bg-dark-surface/95 backdrop-blur-sm z-10 border-b border-white/5">
                        <tr class="text-right">
                            <th class="p-4 text-sm font-medium text-gray-300">الاسم</th>
                            <th class="p-4 text-sm font-medium text-gray-300">رقم الهاتف</th>
                            <th class="p-4 text-sm font-medium text-gray-300">البريد الإلكتروني</th>
                            <th class="p-4 text-sm font-medium text-gray-300 w-1/4">العنوان</th> 
                            <th class="p-4 text-sm font-medium text-gray-300 text-center w-40">الإجراءات</th> 
                        </tr>
                    </thead>
                    <tbody id="customers-table-body" class="divide-y divide-white/5">
                        </tbody>
                </table>
            </div>
            
            <div id="pagination-container" class="p-4 bg-dark-surface/60 border-t border-white/5 flex items-center justify-center shrink-0">
            </div>
        </div>
    </div>
</main>

<div id="customer-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-lg border border-white/10 m-4 transform transition-all scale-100">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 id="customer-modal-title" class="text-lg font-bold text-white">إضافة عميل جديد</h3>
            <button id="close-customer-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="customer-form">
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="customer-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-400 mb-2">اسم العميل <span class="text-red-500">*</span></label>
                        <input type="text" id="customer-name" name="name" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-colors" required>
                    </div>
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-400 mb-2">رقم الهاتف</label>
                        <input type="text" id="customer-phone" name="phone" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-colors">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-400 mb-2">البريد الإلكتروني</label>
                        <input type="email" id="customer-email" name="email" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-colors">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-400 mb-2">العنوان</label>
                        <textarea id="customer-address" name="address" rows="3" class="w-full bg-dark/50 border border-white/10 text-white px-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-colors"></textarea>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-3">
                <button type="button" class="px-6 py-2 rounded-xl border border-white/10 text-gray-300 hover:bg-white/5 transition-colors" onclick="document.getElementById('close-customer-modal').click()">إلغاء</button>
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ</button>
            </div>
        </form>
    </div>
</div>

<div id="customer-details-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">تفاصيل العميل</h3>
            <button id="close-customer-details-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div id="customer-details-content" class="p-6">
            </div>
    </div>
</div>

<div id="barcode-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">بطاقة العميل (Barcode)</h3>
            <button id="close-barcode-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6 flex flex-col items-center gap-6">
            <div class="bg-white rounded-xl p-6 w-full flex justify-center shadow-inner">
                <svg id="barcode" style="max-width: 100%; height: auto;"></svg>
            </div>
            <div class="text-center w-full">
                <p id="barcode-text" class="text-lg font-mono text-white tracking-widest"></p>
                <p class="text-sm text-gray-400 mt-1">امسح الكود للبحث عن العميل بسرعة</p>
            </div>
            <div class="flex gap-3 w-full">
                <button id="print-barcode-btn" class="bg-dark/50 hover:bg-white/5 border border-white/10 text-white px-4 py-2.5 rounded-xl font-medium flex-1 flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">print</span>
                    <span>طباعة</span>
                </button>
                <button id="download-barcode-btn" class="bg-primary hover:bg-primary-hover text-white px-4 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 flex-1 flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">download</span>
                    <span>تحميل صورة</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="loading-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl p-12 border border-white/10 flex flex-col items-center gap-6">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-transparent border-t-primary border-r-primary rounded-full animate-spin"></div>
            <div class="absolute inset-2 border-4 border-transparent border-b-primary/50 rounded-full animate-spin" style="animation-direction: reverse;"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-white mb-2">جاري التحميل...</h3>
            <p id="loading-message" class="text-sm text-gray-400">يرجى الانتظار قليلاً</p>
        </div>
    </div>
</div>

<div id="export-options-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">خيارات التصدير</h3>
            <button id="close-export-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-gray-300 text-sm">اختر ما تريد تصديره:</p>
            <button id="export-current-page" class="w-full bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/30 px-4 py-3 rounded-xl font-medium flex items-center gap-2 transition-all hover:border-blue-500/50">
                <span class="material-icons-round text-sm">filter_list</span>
                <span>البيانات المعروضة حالياً في الجدول فقط</span>
            </button>
            <button id="export-all-data" class="w-full bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-4 py-3 rounded-xl font-medium flex items-center gap-2 transition-all hover:border-emerald-500/50">
                <span class="material-icons-round text-sm">cloud</span>
                <span>جميع البيانات في النظام <em style="font-size: 12px">(قد يستغرق وقتاً طويلاً)</em></span>
            </button>
        </div>
    </div>
</div>

<div id="camera-scan-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">مسح الباركود</h3>
            <button id="close-camera-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6 flex flex-col items-center gap-4">
            <div class="relative w-full rounded-xl overflow-hidden border border-white/10 bg-black aspect-video">
                <video id="camera-video" class="w-full h-full object-cover"></video>
                <canvas id="camera-canvas" class="hidden"></canvas>
                <div class="absolute inset-0 border-2 border-primary/50 m-10 rounded-lg animate-pulse pointer-events-none"></div>
            </div>
            <div class="w-full text-center">
                <p id="scan-status" class="text-sm text-gray-400">وجه الكاميرا نحو الباركود...</p>
            </div>
            <button id="stop-camera-btn" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 border border-red-500/20 px-6 py-2 rounded-xl font-medium transition-all w-full flex items-center gap-2 justify-center">
                <span class="material-icons-round text-sm">stop_circle</span>
                <span>إيقاف الكاميرا</span>
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
    // Utility function to show toast notifications
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast-notification`;
        
        let icon = '';
        if (type === 'success') {
            icon = 'check_circle';
        } else if (type === 'error') {
            icon = 'error';
        } else if (type === 'info') {
            icon = 'info';
        }

        toast.innerHTML = `
            <div class="toast-icon">
                <span class="material-icons-round">${icon}</span>
            </div>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('remove');
            setTimeout(() => toast.remove(), 300);
        }, 10000);
    }

    // دوال إدارة شاشة التحميل
    function showLoadingOverlay(message = 'جاري معالجة البيانات...') {
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
    }

    function hideLoadingOverlay() {
        const loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.classList.add('hidden');
    }

document.addEventListener('DOMContentLoaded', function () {
    const addCustomerBtn = document.getElementById('add-customer-btn');
    const customerModal = document.getElementById('customer-modal');
    const closeCustomerModalBtn = document.getElementById('close-customer-modal');
    const customerForm = document.getElementById('customer-form');
    const customersTableBody = document.getElementById('customers-table-body');
    const searchInput = document.getElementById('customer-search-input');
    const paginationContainer = document.getElementById('pagination-container');
    const customerDetailsModal = document.getElementById('customer-details-modal');
    const closeCustomerDetailsModalBtn = document.getElementById('close-customer-details-modal');
    const barcodeModal = document.getElementById('barcode-modal');
    const closeBarcodeModalBtn = document.getElementById('close-barcode-modal');
    const downloadBarcodeBtn = document.getElementById('download-barcode-btn');
    const cameraScanBtn = document.getElementById('camera-scan-btn');
    const cameraModal = document.getElementById('camera-scan-modal');
    const closeCameraModalBtn = document.getElementById('close-camera-modal');
    const stopCameraBtn = document.getElementById('stop-camera-btn');
    const cameraVideo = document.getElementById('camera-video');
    const cameraCanvas = document.getElementById('camera-canvas');
    const scanStatus = document.getElementById('scan-status');
    const exportOptionsModal = document.getElementById('export-options-modal');
    const closeExportModalBtn = document.getElementById('close-export-modal');
    const exportCurrentPageBtn = document.getElementById('export-current-page');
    const exportAllDataBtn = document.getElementById('export-all-data');
    const exportExcelBtn = document.getElementById('export-excel-btn');

    let currentPage = 1;
    const customersPerPage = 150;
    let currentBarcodeData = null;
    let cameraStream = null;
    let cameraActive = false;
    let scanningIntervalId = null;

    async function loadCustomers() {
        const searchQuery = searchInput.value;
        try {
            showLoadingOverlay('جاري تحميل البيانات...');
            const response = await fetch(`api.php?action=getCustomers&search=${searchQuery}&page=${currentPage}&limit=${customersPerPage}`);
            const result = await response.json();
            if (result.success) {
                displayCustomers(result.data);
                renderPagination(result.total_customers);
                hideLoadingOverlay();
            } else {
                hideLoadingOverlay();
            }
        } catch (error) {
            console.error('Error loading customers:', error);
            hideLoadingOverlay();
            customersTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-red-400">حدث خطأ في تحميل البيانات</td></tr>';
        }
    }

    function displayCustomers(customers) {
        customersTableBody.innerHTML = '';
        if (customers.length === 0) {
            customersTableBody.innerHTML = '<tr><td colspan="6" class="text-center py-12 text-gray-500">لا يوجد عملاء مطابقين للبحث.</td></tr>';
            return;
        }
        customers.forEach(customer => {
            const customerRow = document.createElement('tr');
            customerRow.className = 'hover:bg-white/5 transition-colors group border-b border-white/5 last:border-0';
            
            customerRow.innerHTML = `
                <td class="p-4">
                    <div class="font-bold text-white text-sm">${customer.name}</div>
                </td>
                
                <td class="p-4 text-sm text-gray-300 font-mono dir-ltr text-right">${customer.phone || '-'}</td>
                
                <td class="p-4 text-sm text-gray-400">${customer.email || '-'}</td>
                
                <td class="p-4 text-sm text-gray-400">
                    <div class="truncate max-w-[250px]" title="${customer.address || ''}">
                        ${customer.address || '-'}
                    </div>
                </td>

                <td class="p-4">
                    <div class="flex items-center justify-center gap-2 opacity-90 group-hover:opacity-100 transition-opacity">
                        <button class="view-barcode-btn w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-all" data-id="${customer.id}" data-name="${customer.name}" data-phone="${customer.phone}" title="عرض الباركود">
                            <span class="material-icons-round text-[18px]">qr_code_2</span>
                        </button>
                        
                        <button class="view-details-btn w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary hover:bg-primary/10 transition-all" data-id="${customer.id}" title="عرض التفاصيل">
                            <span class="material-icons-round text-[18px]">visibility</span>
                        </button>
                        
                        <button class="edit-customer-btn w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-amber-400 hover:bg-amber-400/10 transition-all" data-id="${customer.id}" title="تعديل">
                            <span class="material-icons-round text-[18px]">edit</span>
                        </button>
                    </div>
                </td>
            `;
            customersTableBody.appendChild(customerRow);
        });
    }

    function renderPagination(totalCustomers) {
        const totalPages = Math.ceil(totalCustomers / customersPerPage);
        paginationContainer.innerHTML = '';

        if (totalPages <= 1) return;

        let paginationHTML = `<div class="flex items-center gap-2">`;
        
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
                paginationHTML += `<span class="px-2 py-1 text-gray-500">...</span>`;
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
            const page = parseInt(btn.dataset.page);
            if (!isNaN(page) && page > 0) {
                currentPage = page;
                loadCustomers();
            }
        }
    });

    addCustomerBtn.addEventListener('click', () => {
        customerForm.reset();
        document.getElementById('customer-id').value = '';
        document.getElementById('customer-modal-title').textContent = 'إضافة عميل جديد';
        customerModal.classList.remove('hidden');
    });

    closeCustomerModalBtn.addEventListener('click', () => {
        customerModal.classList.add('hidden');
    });

    customerForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(customerForm);
        const customerData = Object.fromEntries(formData.entries());
        const customerId = customerData.id;

        const url = customerId ? 'api.php?action=updateCustomer' : 'api.php?action=addCustomer';
        
        try {
            showLoadingOverlay(customerId ? 'جاري تحديث بيانات العميل...' : 'جاري إضافة العميل الجديد...');
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(customerData),
            });
            const result = await response.json();
            if (result.success) {
                hideLoadingOverlay();
                customerModal.classList.add('hidden');
                if (!customerId) {
                    const newCustomerId = result.id || result.data?.id;
                    generateBarcode(newCustomerId, customerData.name, customerData.phone);
                } else {
                     // If update, show simple toast or alert
                     // alert('تم تحديث البيانات بنجاح');
                }
                loadCustomers();
            } else {
                hideLoadingOverlay();
                alert(result.message);
            }
        } catch (error) {
            console.error('Error saving customer:', error);
            hideLoadingOverlay();
        }
    });

    customersTableBody.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        if (btn.classList.contains('view-barcode-btn')) {
            const customerId = btn.dataset.id;
            const customerName = btn.dataset.name;
            const customerPhone = btn.dataset.phone;
            generateBarcode(customerId, customerName, customerPhone);
        }

        if (btn.classList.contains('edit-customer-btn')) {
            const customerId = btn.dataset.id;
            const customer = await getCustomerDetails(customerId);
            if (customer) {
                document.getElementById('customer-id').value = customer.id;
                document.getElementById('customer-name').value = customer.name;
                document.getElementById('customer-phone').value = customer.phone;
                document.getElementById('customer-email').value = customer.email;
                document.getElementById('customer-address').value = customer.address;
                document.getElementById('customer-modal-title').textContent = 'تعديل بيانات العميل';
                customerModal.classList.remove('hidden');
            }
        }

        if (btn.classList.contains('view-details-btn')) {
            const customerId = btn.dataset.id;
            const customer = await getCustomerDetails(customerId);
            if (customer) {
                displayCustomerDetails(customer);
                customerDetailsModal.classList.remove('hidden');
            }
        }
    });

    closeCustomerDetailsModalBtn.addEventListener('click', () => {
        customerDetailsModal.classList.add('hidden');
    });

    closeBarcodeModalBtn.addEventListener('click', () => {
        barcodeModal.classList.add('hidden');
    });

    downloadBarcodeBtn.addEventListener('click', () => {
        if (currentBarcodeData) {
            const svg = document.getElementById('barcode');
            const svgData = new XMLSerializer().serializeToString(svg);
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            img.onload = function() {
                const paddingBottom = 15;
                canvas.width = img.width;
                canvas.height = img.height + paddingBottom;
                
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0);
                
                ctx.fillStyle = '#000000';
                ctx.font = 'bold 14px Arial';
                ctx.textAlign = 'center';
                ctx.fillText(currentBarcodeData.data, canvas.width / 2, img.height + 10);
                
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = `barcode-${currentBarcodeData.id}-${currentBarcodeData.name}.png`;
                link.click();
            };
            img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
        }
    });

    const printBarcodeBtn = document.getElementById('print-barcode-btn');
    printBarcodeBtn.addEventListener('click', () => {
        if (currentBarcodeData) {
            const svg = document.getElementById('barcode');
            const svgData = new XMLSerializer().serializeToString(svg);
            const printWindow = window.open('', '', 'height=400,width=600');
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html dir="rtl" lang="ar">
                <head>
                    <meta charset="UTF-8">
                    <title>طباعة الباركود</title>
                    <style>
                        body { font-family: Arial, sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
                        .barcode-container { text-align: center; }
                        .barcode-svg { max-width: 100%; margin-bottom: 5px; }
                        .barcode-text { font-size: 14px; color: #000; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class="barcode-container">
                        <div class="barcode-svg">${svgData}</div>
                        <div class="barcode-text">${currentBarcodeData.data}</div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.onload = function() { printWindow.print(); };
        }
    });

    function generateBarcode(customerId, customerName, customerPhone) {
        if (!customerPhone) {
            alert('الرجاء إدخال رقم الهاتف لإنشاء الباركود');
            return;
        }
        const barcodeData = `${customerName}-${customerPhone}`;
        try {
            JsBarcode("#barcode", barcodeData, {
                format: "CODE128",
                width: 2,
                height: 80,
                displayValue: false,
                margin: 0
            });
            document.getElementById('barcode-text').textContent = barcodeData;
            currentBarcodeData = { id: customerId, name: customerName, data: barcodeData };
            barcodeModal.classList.remove('hidden');
        } catch (error) {
            console.error('Error generating barcode:', error);
            alert('حدث خطأ في إنشاء الباركود');
        }
    }

    async function getCustomerDetails(id) {
        try {
            showLoadingOverlay('جاري جلب التفاصيل...');
            const response = await fetch(`api.php?action=getCustomerDetails&id=${id}`);
            const result = await response.json();
            hideLoadingOverlay();
            return result.success ? result.data : null;
        } catch (error) {
            hideLoadingOverlay();
            console.error('Error fetching customer details:', error);
            return null;
        }
    }

    function displayCustomerDetails(customer) {
        const content = document.getElementById('customer-details-content');
        content.innerHTML = `
            <div class="space-y-4">
                <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">الاسم الكامل</div>
                    <div class="text-lg font-bold text-white">${customer.name}</div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                     <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                        <div class="text-xs text-gray-400 mb-1">الهاتف</div>
                        <div class="text-white font-mono dir-ltr">${customer.phone || '-'}</div>
                    </div>
                    <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                        <div class="text-xs text-gray-400 mb-1">البريد الإلكتروني</div>
                        <div class="text-white truncate">${customer.email || '-'}</div>
                    </div>
                </div>
                 <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                    <div class="text-xs text-gray-400 mb-1">العنوان</div>
                    <div class="text-white leading-relaxed">${customer.address || '-'}</div>
                </div>
            </div>
        `;
    }

    searchInput.addEventListener('input', () => {
        currentPage = 1;
        loadCustomers();
    });

    // Camera logic
    cameraScanBtn.addEventListener('click', async () => {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } 
            });
            cameraVideo.srcObject = cameraStream;
            cameraVideo.play();
            cameraModal.classList.remove('hidden');
            cameraActive = true;
            scanStatus.textContent = 'جاري البحث عن الباركود...';
            startBarcodeScanning();
        } catch (error) {
            console.error('Error accessing camera:', error);
            alert('لم يتمكن من الوصول إلى الكاميرا.');
        }
    });

    closeCameraModalBtn.addEventListener('click', stopCamera);
    stopCameraBtn.addEventListener('click', stopCamera);

    function stopCamera() {
        cameraActive = false;
        if (scanningIntervalId) clearInterval(scanningIntervalId);
        if (cameraStream) cameraStream.getTracks().forEach(track => track.stop());
        cameraModal.classList.add('hidden');
    }

    function startBarcodeScanning() {
        const ctx = cameraCanvas.getContext('2d');
        const canvasWidth = 640;
        const canvasHeight = 480;
        cameraCanvas.width = canvasWidth;
        cameraCanvas.height = canvasHeight;

        scanningIntervalId = setInterval(() => {
            if (!cameraActive) return;
            try {
                ctx.drawImage(cameraVideo, 0, 0, canvasWidth, canvasHeight);
                const imageData = ctx.getImageData(0, 0, canvasWidth, canvasHeight);
                const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });

                if (code) {
                    const parts = code.data.split('-');
                    let query = parts.length > 1 ? parts[parts.length - 1] : code.data;
                    
                    stopCamera();
                    searchInput.value = query.trim();
                    currentPage = 1;
                    loadCustomers();
                }
            } catch (error) { console.error(error); }
        }, 100);
    }

    // Export Logic
    exportExcelBtn.addEventListener('click', () => {
        const customersCount = document.querySelectorAll('#customers-table-body tr').length;
        if (customersCount === 0) {
            showToast('لا يوجد عملاء للتصدير', 'info');
            return;
        }
        exportOptionsModal.classList.remove('hidden');
    });

    closeExportModalBtn.addEventListener('click', () => {
        exportOptionsModal.classList.add('hidden');
    });

    exportCurrentPageBtn.addEventListener('click', async () => {
        await performExport('current_page');
        exportOptionsModal.classList.add('hidden');
    });

    exportAllDataBtn.addEventListener('click', async () => {
        await performExport('all_data');
        exportOptionsModal.classList.add('hidden');
    });

    async function performExport(exportType) {
        try {
            const searchQuery = searchInput.value;
            const customersCount = document.querySelectorAll('#customers-table-body tr').length;

            // إظهار شاشة التحميل
            const loadingMsg = exportType === 'current_page' 
                ? 'جاري تصدير البيانات المعروضة...' 
                : 'جاري تصدير جميع البيانات...';
            showLoadingOverlay(loadingMsg);

            // إخفاء الزر المُطلب
            exportExcelBtn.disabled = true;

            // بناء URL التصدير
            let url = `api.php?action=exportCustomersExcel&exportType=${exportType}`;
            
            // إذا كان التصدير للصفحة الحالية، أضف معاملات pagination
            if (exportType === 'current_page') {
                url += `&page=${currentPage}&limit=${customersPerPage}`;
                if (searchQuery) {
                    url += `&search=${encodeURIComponent(searchQuery)}`;
                }
            }
            
            // إنشاء رابط وتحميل الملف
            const link = document.createElement('a');
            link.href = url;
            link.download = `عملاء_${new Date().getTime()}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // عرض رسالة النجاح وإخفاء التحميل
            setTimeout(() => {
                hideLoadingOverlay();
                const exportTypeText = exportType === 'current_page' ? 'البيانات المعروضة' : 'جميع البيانات';
                showToast(`تم تصدير ${exportTypeText} بنجاح (يجب أن تنتظر قليلا قبل بدء التحميل الفعلي على جهازك)✓`, 'success');
                exportExcelBtn.disabled = false;
            }, 500);

        } catch (error) {
            console.error('Error exporting to Excel:', error);
            hideLoadingOverlay();
            showToast('حدث خطأ في تصدير البيانات', 'error');
            exportExcelBtn.disabled = false;
        }
    }
    // إغلاق المودال عند الضغط خارجه
    exportOptionsModal.addEventListener('click', (e) => {
        if (e.target === exportOptionsModal) {
            exportOptionsModal.classList.add('hidden');
        }
    });

    loadCustomers();
});
</script>
<?php require_once 'src/footer.php'; ?>