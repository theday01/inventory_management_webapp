<?php
require_once 'session.php';
require_once 'src/language.php';
$page_title = __('removed_products_title');
$current_page = 'removed_products.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch currency setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';
?>

<style>
    #pagination-container {
        background-color: rgb(13 16 22);
        backdrop-filter: blur(12px);
        border-color: rgba(255, 255, 255, 0.05);
        position: sticky;
        bottom: 0;
        z-index: 20;
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
    <div class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-red-500/5 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Header -->
    <header class="h-auto md:h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex flex-col md:flex-row md:items-center justify-between p-4 md:px-8 relative z-10 shrink-0 gap-4">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="material-icons-round text-red-500">delete_sweep</span>
            <span><?php echo __('removed_products_title'); ?></span>
        </h2>
        <p class="text-sm text-gray-400 justify-center"><?php echo __('removed_products_note'); ?></p>
    </header>

    <!-- Filters & Actions -->
    <div class="p-4 md:p-6 pt-6 flex flex-col gap-4 relative z-10 shrink-0">
        <div id="bulk-actions-bar" class="hidden bg-primary/10 border border-primary/30 rounded-xl p-3 flex items-center justify-between transition-all">
            <span id="selected-count" class="text-white font-bold"></span>
            <div class="flex items-center gap-2">
                <button id="bulk-restore-btn" class="text-green-500 hover:bg-green-500/10 p-2 rounded-lg transition-colors" title="<?php echo __('bulk_restore'); ?>"><span class="material-icons-round">restore_from_trash</span></button>
                <button id="bulk-delete-btn" class="text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition-colors" title="<?php echo __('bulk_permanent_delete'); ?>"><span class="material-icons-round">delete_forever</span></button>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-96">
                <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                <input type="text" id="product-search-input" placeholder="<?php echo __('search_removed_placeholder'); ?>"
                    class="w-full bg-dark/50 border border-white/10 text-white text-start pr-10 pl-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                <button id="scan-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white" title="<?php echo __('scan_product_barcode'); ?>">
                    <span class="material-icons-round">qr_code_scanner</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="flex-1 flex flex-col overflow-hidden p-4 md:p-6 pt-0 relative z-10">
        <div class="flex-1 flex flex-col bg-transparent md:bg-dark-surface/60 md:backdrop-blur-md md:border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <table class="w-full text-right border-collapse block md:table">
                    <thead class="hidden md:table-header-group sticky top-0 bg-white/5 z-10">
                        <tr class="text-start border-b border-white/10">
                            <th class="p-4 w-10"><input type="checkbox" id="select-all-products" class="bg-dark/50 border-white/20 rounded"></th>
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('product'); ?></th>
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('product_image'); ?></th>
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('category'); ?></th>
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('price'); ?></th>
                            <th class="p-4 text-sm font-medium text-gray-300 cursor-pointer sortable-header" data-sort="removed_at"><?php echo __('deleted_at_header'); ?> <span class="sort-icon opacity-30">▼</span></th>
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('permanent_delete_date_header'); ?></th>
                            <th class="p-4 text-sm font-medium text-gray-300"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body" class="block md:table-row-group divide-y divide-white/5 md:divide-white/5">
                        <!-- Products will be loaded here -->
                    </tbody>
                </table>
            </div>
            <div id="pagination-container" class="p-4 bg-dark-surface/60 border-t border-white/5 flex items-center justify-center text-sm text-gray-400 shrink-0 rounded-b-2xl"></div>
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
        const productsPerPage = 200;

        // Show the global loading screen immediately when the page opens
        if (typeof showLoading === 'function') showLoading(__('loading_removed_products'));
        loadProducts();
        fetch('api.php?action=checkExpiringProducts')
            .then(res => res.json())
            .catch(err => console.log('Expiry check error:', err));
        searchInput.addEventListener('input', () => { currentPage = 1; loadProducts(); });

        async function loadProducts() {
            const searchQuery = searchInput.value;

            try {
                showLoading(__('loading_removed_products'));
                const response = await fetch(`api.php?action=getRemovedProducts&search=${searchQuery}&page=${currentPage}&limit=${productsPerPage}&sortBy=${sortBy}&sortOrder=${sortOrder}`);
                const result = await response.json();
                if (result.success) {
                    displayProducts(result.data);
                    renderPagination(result.total_products);
                }
            } catch (error) {
                console.error(__('error_loading_products'), error);
                showToast(__('error_loading_removed_products_toast'), false);
            } finally {
                hideLoading();
            }
        }

        function escapeHtml(text) {
          if (text == null) return '';
          return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
        }

        function displayProducts(products) {
            productsTableBody.innerHTML = '';
            if (products.length === 0) {
                productsTableBody.innerHTML = `<tr class="block md:table-row"><td colspan="8" class="text-center py-8 text-gray-500 block md:table-cell">${__('no_removed_products')}</td></tr>`;
                return;
            }

            const expiring = [];

            function formatRemaining(ms) {
                const days = Math.floor(ms / (24*60*60*1000));
                const hours = Math.floor((ms % (24*60*60*1000)) / (60*60*1000));
                const mins = Math.floor((ms % (60*60*1000)) / (60*1000));
                if (days > 0) return `${days}${__('time_days_short')} ${hours}${__('time_hours_short')}`;
                if (hours > 0) return `${hours}${__('time_hours_short')} ${mins}${__('time_mins_short')}`;
                return `${Math.ceil(ms/60000)}${__('time_mins_short')}`;
            }

            products.forEach(product => {
                const productRow = document.createElement('tr');
                productRow.className = 'block md:table-row bg-dark-surface/40 md:bg-transparent mb-4 md:mb-0 rounded-2xl md:rounded-none border border-white/5 md:border-b hover:bg-white/5 transition-colors group';

                const removedAt = new Date(product.removed_at);
                const removedAtStr = removedAt.toLocaleString('en-GB', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false });
                const ONE_DAY_MS = 24 * 60 * 60 * 1000;
                const THIRTY_DAYS_MS = 30 * ONE_DAY_MS;
                const expiryTimestamp = removedAt.getTime() + THIRTY_DAYS_MS; // 30 days after removal
                const remainingMs = expiryTimestamp - Date.now();

                let expiryBadge = '';
                // Show warning when remaining time is within 1 day (24h)
                if (remainingMs > 0 && remainingMs <= ONE_DAY_MS) {
                    expiryBadge = `<div class="text-xs text-yellow-300 mt-1">${__('will_be_deleted_after').replace('%s', formatRemaining(remainingMs))}</div>`;
                    expiring.push({ name: product.name, remainingMs });
                }

                productRow.innerHTML = `
                    <td class="p-4 md:px-4 md:py-4 block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                        <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('select')}</span>
                        <input type="checkbox" class="product-checkbox bg-dark/50 border-white/20 rounded" data-id="${product.id}">
                    </td>
                    <td class="p-4 md:px-4 md:py-4 text-sm text-gray-300 font-medium block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                        <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('product')}</span>
                        <span>${escapeHtml(product.name)}</span>
                    </td>
                    <td class="p-4 md:px-4 md:py-4 block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                        <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('product_image')}</span>
                        <img src="${escapeHtml(product.image) || 'src/img/default-product.png'}" alt="${escapeHtml(product.name)}" class="w-10 h-10 rounded-md object-cover">
                    </td>
                    <td class="p-4 md:px-4 md:py-4 text-sm text-gray-400 block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                        <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('category')}</span>
                        <span>${escapeHtml(product.category_name) || 'غير مصنّف'}</span>
                    </td>
                    <td class="p-4 md:px-4 md:py-4 text-sm text-gray-300 block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                        <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('price')}</span>
                        <span>${parseFloat(product.price).toFixed(2)} ${'<?php echo $currency; ?>'}</span>
                    </td>
                    <td class="p-4 md:px-4 md:py-4 text-sm text-gray-400 block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                        <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('deleted_at_header')}</span>
                        <div class="text-right md:text-start">
                            ${removedAtStr}
                            ${expiryBadge}
                        </div>
                    </td>
                    <td class="p-4 md:px-4 md:py-4 text-sm text-gray-300 block md:table-cell flex justify-between items-center border-b border-white/5 md:border-0 last:border-0">
                        <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('permanent_delete_date_header')}</span>
                        <span class="text-red-400 font-bold">${new Date(expiryTimestamp).toLocaleString('en-GB', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: false })}</span>
                    </td>
                    <td class="p-4 md:px-4 md:py-4 text-sm block md:table-cell flex justify-between items-center md:justify-start border-b border-white/5 md:border-0 last:border-0 gap-2">
                        <span class="text-gray-400 text-xs font-bold md:hidden uppercase tracking-wider">${window.__('actions')}</span>
                        <div class="flex gap-2">
                            <button class="restore-product-btn p-2 md:p-1.5 text-gray-400 hover:text-green-500 transition-colors bg-white/5 md:bg-transparent rounded-lg md:rounded-none" data-id="${product.id}" title="${__('restore_product_tooltip')}"><span class="material-icons-round text-lg">restore_from_trash</span></button>
                            <button class="delete-product-btn p-2 md:p-1.5 text-gray-400 hover:text-red-500 transition-colors bg-white/5 md:bg-transparent rounded-lg md:rounded-none" data-id="${product.id}" title="${__('permanent_delete_tooltip')}"><span class="material-icons-round text-lg">delete_forever</span></button>
                        </div>
                    </td>
                `;
                productsTableBody.appendChild(productRow);
            });

            if (expiring.length > 0) {
                let msg = `${escapeHtml(expiring[0].name)} (${formatRemaining(expiring[0].remainingMs)})`;
                if (expiring.length > 1) {
                    msg += ' ' + __('and_more_products').replace('%d', expiring.length - 1);
                }
                showToast(__('expiring_soon_alert').replace('%s', msg), 'info');
            }
        }

        function renderPagination(totalProducts) {
            const totalPages = Math.ceil(totalProducts / productsPerPage);
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
                const page = parseInt(btn.dataset.page);
                if (!isNaN(page) && page > 0) {
                    currentPage = page;
                    loadProducts();
                }
            }
        });

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
                document.getElementById('selected-count').textContent = __('selected_products_count').replace('%s', selectedCount);
            } else {
                bar.classList.add('hidden');
            }
        }

        async function restoreProducts(ids) {
            const confirmed = await showConfirmModal(__('restore_products_title'), __('restore_products_confirm_msg').replace('%d', ids.length));
            if (confirmed) {
                try {
                    showLoading(__('restoring_products'));
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
                        showToast(result.message || __('restore_products_failed'), false);
                    }
                } catch (error) {
                    showToast(__('error_occurred'), false);
                } finally {
                    hideLoading();
                }
            }
        }
        
        async function permanentlyDeleteProducts(ids) {
            const confirmed = await showConfirmModal(__('confirm_permanent_delete_title'), __('permanent_delete_confirm_msg').replace('%d', ids.length));
            if (confirmed) {
                try {
                    showLoading(__('permanently_deleting'));
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
                        showToast(result.message || __('permanent_delete_failed'), false);
                    }
                } catch (error) {
                    showToast(__('error_occurred'), false);
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
            <h3 class="text-lg font-bold text-white"><?php echo __('scan_barcode_title'); ?></h3>
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
                showToast(__('scanner_library_unavailable'), false);
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
                showToast(__('camera_error_msg'), false);
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
