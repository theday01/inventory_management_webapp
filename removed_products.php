<?php
$page_title = 'المنتجات المحذوفة';
$current_page = 'removed_products.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch currency setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-red-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Header -->
    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-red-500">delete_sweep</span>
            <span>المنتجات المحذوفة</span>
        </h2>
        <p class="text-sm text-gray-400">ملاحظة: بعد مرور تلاثين يومًا (30 يوم) على حذف المنتج سيتم حذفه نهائيًا من قاعدة البيانات ولا يمكن استعادته. سيتم عرض تنبيه قبل يوم واحد من الحذف النهائي</p>
    </header>

    <!-- Filters & Actions -->
    <div class="p-6 pt-6 flex flex-col gap-4 relative z-10 shrink-0">
        <div id="bulk-actions-bar" class="hidden bg-primary/10 border border-primary/30 rounded-xl p-3 flex items-center justify-between transition-all">
            <span id="selected-count" class="text-white font-bold"></span>
            <div class="flex items-center gap-2">
                <button id="bulk-restore-btn" class="text-green-500 hover:bg-green-500/10 p-2 rounded-lg transition-colors" title="استعادة جماعية"><span class="material-icons-round">restore_from_trash</span></button>
                <button id="bulk-delete-btn" class="text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition-colors" title="حذف نهائي جماعي"><span class="material-icons-round">delete_forever</span></button>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-96">
                <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                <input type="text" id="product-search-input" placeholder="بحث عن اسم المنتج، الباركود..."
                    class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                <button id="scan-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white" title="مسح باركود المنتج">
                    <span class="material-icons-round">qr_code_scanner</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="flex-1 overflow-auto p-6 pt-0 relative z-10">
        <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 text-right">
                        <th class="p-4 w-10"><input type="checkbox" id="select-all-products" class="bg-dark/50 border-white/20 rounded"></th>
                        <th class="p-4 text-sm font-medium text-gray-300">المنتج</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الصورة</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الفئة</th>
                        <th class="p-4 text-sm font-medium text-gray-300">السعر</th>
                        <th class="p-4 text-sm font-medium text-gray-300 cursor-pointer sortable-header" data-sort="removed_at">تاريخ الحذف <span class="sort-icon opacity-30">▼</span></th>
                        <th class="p-4 text-sm font-medium text-gray-300">تاريخ الحذف النهائي</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="divide-y divide-white/5">
                    <!-- Products will be loaded here -->
                </tbody>
            </table>
            <div id="pagination-container" class="p-4 bg-dark-surface/60 border-t border-white/5 flex items-center justify-between text-sm text-gray-400"></div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const productsTableBody = document.getElementById('products-table-body');
        const searchInput = document.getElementById('product-search-input');
        const paginationContainer = document.getElementById('pagination-container');
        const selectAllCheckbox = document.getElementById('select-all-products');
        
        let currentPage = 1;
        let sortBy = 'removed_at';
        let sortOrder = 'desc';
        const productsPerPage = 300;

        // Show the global loading screen immediately when the page opens
        if (typeof showLoading === 'function') showLoading('جاري تحميل المنتجات المحذوفة...');
        loadProducts();
        searchInput.addEventListener('input', () => { currentPage = 1; loadProducts(); });

        async function loadProducts() {
            const searchQuery = searchInput.value;

            try {
                showLoading('جاري تحميل المنتجات المحذوفة...');
                const response = await fetch(`api.php?action=getRemovedProducts&search=${searchQuery}&page=${currentPage}&limit=${productsPerPage}&sortBy=${sortBy}&sortOrder=${sortOrder}`);
                const result = await response.json();
                if (result.success) {
                    displayProducts(result.data);
                    renderPagination(result.total_products);
                }
            } catch (error) {
                console.error('خطأ في تحميل المنتجات:', error);
                showToast('حدث خطأ في تحميل المنتجات المحذوفة', false);
            } finally {
                hideLoading();
            }
        }

        function displayProducts(products) {
            productsTableBody.innerHTML = '';
            if (products.length === 0) {
                productsTableBody.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-gray-500">لا توجد منتجات محذوفة لعرضها.</td></tr>';
                return;
            }

            const expiring = [];

            products.forEach(product => {
                const productRow = document.createElement('tr');
                productRow.className = 'hover:bg-white/5 transition-colors';

                const removedAt = new Date(product.removed_at);
                const removedAtStr = removedAt.toLocaleString('en-GB', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false });
                const ONE_DAY_MS = 24 * 60 * 60 * 1000;
                const THIRTY_DAYS_MS = 30 * ONE_DAY_MS;
                const expiryTimestamp = removedAt.getTime() + THIRTY_DAYS_MS; // 30 days after removal
                const remainingMs = expiryTimestamp - Date.now();

                function formatRemaining(ms) {
                    const days = Math.floor(ms / (24*60*60*1000));
                    const hours = Math.floor((ms % (24*60*60*1000)) / (60*60*1000));
                    const mins = Math.floor((ms % (60*60*1000)) / (60*1000));
                    if (days > 0) return `${days}d ${hours}h`;
                    if (hours > 0) return `${hours}h ${mins}m`;
                    return `${Math.ceil(ms/60000)}m`;
                }

                let expiryBadge = '';
                // Show warning when remaining time is within 1 day (24h)
                if (remainingMs > 0 && remainingMs <= ONE_DAY_MS) {
                    expiryBadge = `<div class="text-xs text-yellow-300">سيحذف نهائيًا بعد ${formatRemaining(remainingMs)}</div>`;
                    expiring.push({ name: product.name, remainingMs });
                }

                productRow.innerHTML = `
                    <td class="p-4"><input type="checkbox" class="product-checkbox bg-dark/50 border-white/20 rounded" data-id="${product.id}"></td>
                    <td class="p-4 text-sm text-gray-300 font-medium">${product.name}</td>
                    <td class="p-4"><img src="${product.image || 'src/img/default-product.png'}" alt="${product.name}" class="w-10 h-10 rounded-md object-cover"></td>
                    <td class="p-4 text-sm text-gray-400">${product.category_name || 'غير مصنّف'}</td>
                    <td class="p-4 text-sm text-gray-300">${parseFloat(product.price).toFixed(2)} ${'<?php echo $currency; ?>'}</td>
                    <td class="p-4 text-sm text-gray-400">${removedAtStr}${expiryBadge}</td>
                    <td class="p-4 text-sm text-gray-300"><span class="text-red-400 font-bold">${new Date(expiryTimestamp).toLocaleString('en-GB', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false })}</span></td>
                    <td class="p-4 text-sm">
                        <button class="restore-product-btn p-1.5 text-gray-400 hover:text-green-500 transition-colors" data-id="${product.id}" title="استعادة المنتج"><span class="material-icons-round text-lg">restore_from_trash</span></button>
                        <button class="delete-product-btn p-1.5 text-gray-400 hover:text-red-500 transition-colors" data-id="${product.id}" title="حذف نهائي"><span class="material-icons-round text-lg">delete_forever</span></button>
                    </td>
                `;
                productsTableBody.appendChild(productRow);
            });

            if (expiring.length > 0) {
                const msgs = expiring.map(e => `${e.name} (${formatRemaining(e.remainingMs)})`);
                showToast(`تنبيه: ${msgs.join('، ')} سيتم حذفها نهائيًا خلال أقل من 24 ساعة.`, false);
            }
        }

        function renderPagination(totalProducts) {
            const totalPages = Math.ceil(totalProducts / productsPerPage);
            paginationContainer.innerHTML = '';

            if (totalPages <= 1) return;

            let paginationHTML = `
                <div class="flex items-center gap-2">
                    <span class="text-sm">صفحة ${currentPage} من ${totalPages}</span>
                </div>
                <div class="flex items-center gap-1">`;
            
            // Previous Button
            paginationHTML += `<button class="pagination-btn ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}><span class="material-icons-round">chevron_right</span></button>`;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationHTML += `<button class="pagination-btn bg-primary text-white" data-page="${i}">${i}</button>`;
                } else {
                    paginationHTML += `<button class="pagination-btn" data-page="${i}">${i}</button>`;
                }
            }
            
            // Next Button
            paginationHTML += `<button class="pagination-btn ${currentPage === totalPages ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}><span class="material-icons-round">chevron_left</span></button>`;

            paginationHTML += `</div>`;
            paginationContainer.innerHTML = paginationHTML;
        }

        // Event listeners for single-product actions
        productsTableBody.addEventListener('click', e => {
            if (e.target.closest('.restore-product-btn')) {
                const id = e.target.closest('.restore-product-btn').dataset.id;
                restoreProducts([id]);
            }
            if (e.target.closest('.delete-product-btn')) {
                const id = e.target.closest('.delete-product-btn').dataset.id;
                permanentlyDeleteProducts([id]);
            }
        });

        // Bulk actions
        selectAllCheckbox.addEventListener('change', () => {
            document.querySelectorAll('.product-checkbox').forEach(checkbox => checkbox.checked = selectAllCheckbox.checked);
            updateBulkActionsBar();
        });
        productsTableBody.addEventListener('change', e => {
            if (e.target.classList.contains('product-checkbox')) updateBulkActionsBar();
        });

        document.getElementById('bulk-restore-btn').addEventListener('click', () => {
            const selectedIds = getSelectedProductIds();
            if (selectedIds.length > 0) restoreProducts(selectedIds);
        });
        document.getElementById('bulk-delete-btn').addEventListener('click', () => {
            const selectedIds = getSelectedProductIds();
            if (selectedIds.length > 0) permanentlyDeleteProducts(selectedIds);
        });

        function getSelectedProductIds() {
            return Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.dataset.id);
        }

        function updateBulkActionsBar() {
            const selectedCount = getSelectedProductIds().length;
            const bar = document.getElementById('bulk-actions-bar');
            if (selectedCount > 0) {
                bar.classList.remove('hidden');
                document.getElementById('selected-count').textContent = `${selectedCount} منتجات محددة`;
            } else {
                bar.classList.add('hidden');
            }
        }

        async function restoreProducts(ids) {
            const confirmed = await showConfirmModal('استعادة المنتجات', `هل أنت متأكد من استعادة ${ids.length} منتج؟`);
            if (confirmed) {
                try {
                    showLoading('جاري استعادة المنتجات...');
                    const response = await fetch('api.php?action=restoreProducts', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_ids: ids })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showToast(result.message, true);
                        loadProducts();
                    } else {
                        showToast(result.message || 'فشل في استعادة المنتجات', false);
                    }
                } catch (error) {
                    showToast('حدث خطأ', false);
                } finally {
                    hideLoading();
                }
            }
        }
        
        async function permanentlyDeleteProducts(ids) {
            const confirmed = await showConfirmModal('حذف نهائي', `هل أنت متأكد من الحذف النهائي لـ ${ids.length} منتج؟ هذا الإجراء لا يمكن التراجع عنه.`);
            if (confirmed) {
                try {
                    showLoading('جاري الحذف النهائي...');
                    const response = await fetch('api.php?action=permanentlyDeleteProducts', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_ids: ids })
                    });
                    const result = await response.json();
                    if (result.success) {
                        showToast(result.message, true);
                        loadProducts();
                    } else {
                        showToast(result.message || 'فشل في الحذف', false);
                    }
                } catch (error) {
                    showToast('حدث خطأ', false);
                } finally {
                    hideLoading();
                }
            }
        }
    });
</script>

<?php require_once 'src/footer.php'; ?>

<!-- Barcode Scanner Modal (for removed products search) -->
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
    (function(){
        let codeReader = null;
        const scanBtn = document.getElementById('scan-barcode-btn');
        const scannerModal = document.getElementById('barcode-scanner-modal');
        const closeScannerBtn = document.getElementById('close-barcode-scanner-modal');
        const videoElem = document.getElementById('barcode-video');
        const searchInput = document.getElementById('product-search-input');

        function openScanner() {
            if (!window.ZXing) {
                showToast('مكتبة المسح غير متاحة', false);
                return;
            }
            scannerModal.classList.remove('hidden');
            codeReader = new ZXing.BrowserMultiFormatReader();
            codeReader.decodeFromVideoDevice(null, videoElem, (result, err) => {
                if (result) {
                    // put barcode into search and load
                    searchInput.value = result.text || result.getText && result.getText();
                    hideScanner();
                    loadProducts();
                }
            }).catch(err => {
                console.error('Scanner error', err);
                showToast('لم يتم تفعيل الكاميرا أو حدث خطأ في المسح', false);
                hideScanner();
            });
        }

        function hideScanner() {
            try {
                if (codeReader) {
                    codeReader.reset();
                    codeReader = null;
                }
            } catch (e) { /* ignore */ }
            scannerModal.classList.add('hidden');
        }

        if (scanBtn) scanBtn.addEventListener('click', (e) => { e.preventDefault(); openScanner(); });
        if (closeScannerBtn) closeScannerBtn.addEventListener('click', (e) => { e.preventDefault(); hideScanner(); });

        // Close modal on outside click or ESC
        scannerModal.addEventListener('click', (e) => { if (e.target === scannerModal) hideScanner(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideScanner(); });
    })();
</script>
