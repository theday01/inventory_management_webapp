<?php
require_once 'src/language.php';
$page_title = __('products_page_title');
$current_page = 'products.php';
require_once 'session.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';

// Fetch currency setting
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'currency'");
$currency = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : 'MAD';

// جلب إعدادات تنبيهات الكمية
$settings_sql = "SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('low_quantity_alert', 'critical_quantity_alert')";
$settings_result = $conn->query($settings_sql);
$quantity_settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $quantity_settings[$row['setting_name']] = (int)$row['setting_value'];
}
$low_alert = $quantity_settings['low_quantity_alert'] ?? 10;
$critical_alert = $quantity_settings['critical_quantity_alert'] ?? 5;
?>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-auto">
    <div
        class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 sticky top-0 z-30 shrink-0">
        <h2 class="text-xl font-bold text-white"><?php echo __('products_page_title'); ?></h2>

        <div class="flex items-center gap-4">
            <button id="add-product-btn"
                class="bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-rose-500/30 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">add</span>
                <span><?php echo __('new_product'); ?></span>
            </button>
            <button id="bulk-add-btn"
                class="bg-gradient-to-r from-violet-500/20 to-purple-500/20 hover:from-violet-500/30 hover:to-purple-500/30 text-violet-300 border border-violet-500/40 px-4 py-2 rounded-xl font-bold shadow-sm flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">playlist_add</span>
                <span><?php echo __('bulk_add_products'); ?></span>
            </button>
            <button id="manage-categories-btn"
                class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 hover:from-blue-500/30 hover:to-cyan-500/30 text-blue-300 border border-blue-500/40 px-4 py-2 rounded-xl font-bold shadow-sm flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">category</span>
                <span><?php echo __('manage_categories'); ?></span>
            </button>
            <button id="export-csv-btn"
                class="bg-gradient-to-r from-orange-500/20 to-amber-500/20 hover:from-orange-500/30 hover:to-amber-500/30 text-orange-300 border border-orange-500/40 px-4 py-2 rounded-xl font-bold shadow-sm flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">download</span>
                <span><?php echo __('export_excel'); ?></span>
            </button>
            <button id="import-excel-btn"
                class="bg-gradient-to-r from-teal-500/20 to-green-500/20 hover:from-teal-500/30 hover:to-green-500/30 text-teal-300 border border-teal-500/40 px-4 py-2 rounded-xl font-bold shadow-sm flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">upload_file</span>
                <span><?php echo __('import_excel'); ?></span>
            </button>
        </div>
    </header>

    <!-- Stats -->
    <div id="stats-bar" class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10 shrink-0">
        <!-- Stats will be loaded here -->
    </div>

    <!-- Filters & Actions -->
    <div class="p-6 pt-0 flex flex-col gap-4 relative z-10 shrink-0">
        <div id="bulk-actions-bar" class="hidden bg-primary/10 border border-primary/30 rounded-xl p-3 flex items-center justify-between transition-all">
            <span id="selected-count" class="text-white font-bold"></span>
            <div class="flex items-center gap-2">
                <button id="print-labels-btn" class="text-white hover:bg-white/10 p-2 rounded-lg transition-colors" title="<?php echo __('print_barcode_labels'); ?>"><span class="material-icons-round">print</span></button>
                <button id="bulk-edit-btn" class="text-white hover:bg-white/10 p-2 rounded-lg transition-colors" title="<?php echo __('bulk_edit'); ?>"><span class="material-icons-round">edit</span></button>
                <button id="bulk-delete-btn" class="text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition-colors" title="<?php echo __('bulk_delete'); ?>"><span class="material-icons-round">delete</span></button>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-4 w-full flex-1 max-w-4xl">
                <div class="relative w-full md:w-96">
                    <span
                    class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                <input type="text" id="product-search-input" placeholder="<?php echo __('search_products_placeholder'); ?>"
                    class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                <button id="scan-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white">
                    <span class="material-icons-round">qr_code_scanner</span>
                </button>
            </div>

            <div class="relative min-w-[200px]">
                <select id="product-category-filter"
                    class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                    <option value=""><?php echo __('all_categories'); ?></option>
                </select>
                <span
                    class="material-icons-round absolute top-1/2 left-2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
            <div class="relative min-w-[200px]">
                <select id="stock-status-filter"
                    class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                    <option value=""><?php echo __('all_stock'); ?></option>
                    <option value="out_of_stock"><?php echo __('stock_status_out'); ?></option>
                    <option value="low_stock"><?php echo __('stock_status_low'); ?></option>
                    <option value="critical_stock"><?php echo __('stock_status_critical'); ?></option>
                </select>
                <span
                    class="material-icons-round absolute top-1/2 left-2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
        </div>
    </div>
    <!-- Products Table -->
    <div class="flex-1 flex flex-col p-6 pt-0 relative z-10">
        <div class="flex-1 flex flex-col bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <div class="flex-1 overflow-y-auto">
                <table class="w-full min-w-full table-auto">
                    <thead>
                        <tr class="text-right">
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 w-10"><input type="checkbox" id="select-all-products" class="bg-dark/50 border-white/20 rounded"></th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300 cursor-pointer sortable-header" data-sort="name"><?php echo __('product'); ?> <span class="sort-icon opacity-30">▲</span></th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300"><?php echo __('product_image'); ?></th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300"><?php echo __('category'); ?></th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300 cursor-pointer sortable-header" data-sort="price"><?php echo __('selling_price'); ?> <span class="sort-icon opacity-30">▲</span></th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300 cursor-pointer sortable-header" data-sort="quantity"><?php echo __('quantity'); ?> <span class="sort-icon opacity-30">▲</span></th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300"><?php echo __('details'); ?></th>
                            <th class="sticky top-0 bg-dark-surface/80 backdrop-blur-sm p-4 text-sm font-medium text-gray-300"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body" class="divide-y divide-white/5">
                        <!-- Products will be loaded here -->
                    </tbody>
                </table>
            </div>
            <!-- Pagination removed from table container to be placed below the page for consistent layout -->
        </div>
    </div>

    <!-- Loading Screen -->
    <div id="stock-check-loading" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[60] hidden flex items-center justify-center">
        <div class="bg-dark-surface rounded-2xl p-8 flex flex-col items-center gap-4 border border-white/10">
            <div class="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p class="text-white font-bold text-lg"><?php echo __('checking_stock'); ?></p>
        </div>
    </div>

    <!-- Export Loading Screen -->
    <div id="export-loading" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[60] hidden flex items-center justify-center">
        <div class="bg-dark-surface rounded-2xl p-8 flex flex-col items-center gap-4 border border-white/10">
            <div class="w-16 h-16 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-white font-bold text-lg"><?php echo __('exporting_excel'); ?></p>
            <p class="text-gray-400 text-sm"><?php echo __('export_wait_msg'); ?></p>
        </div>
    </div>

    <!-- Stock Check Modal -->
    <div id="stock-check-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-3xl border border-white/10 max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-white/5 flex justify-between items-center shrink-0">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-yellow-500">inventory</span>
                    <?php echo __('low_stock_report'); ?>
                </h3>
                <button id="close-stock-modal" class="text-gray-400 hover:text-white transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            
            <div id="stock-modal-content" class="flex-1 overflow-y-auto p-6">
                <!-- سيتم ملؤه ديناميكياً -->
            </div>
            
            <div class="p-6 border-t border-white/5 flex justify-end gap-3 shrink-0">
                <button id="export-stock-report" class="bg-primary/10 hover:bg-primary/20 text-primary px-6 py-2 rounded-xl font-bold transition-all flex items-center gap-2">
                    <span class="material-icons-round text-sm">download</span>
                    <?php echo __('export_report_txt'); ?>
                </button>
                <button id="close-stock-modal-btn" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded-xl font-bold transition-all">
                    <?php echo __('close'); ?>
                </button>
            </div>
        </div>
    </div>
    <!-- Delete Success Modal -->
    <div id="delete-success-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-4">
        <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-2xl border border-white/10 max-h-[90vh] flex flex-col animate-scale-in">
            <div class="p-6 border-b border-white/5 flex justify-between items-center shrink-0 bg-gradient-to-r from-green-500/10 to-green-600/10">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center">
                        <span class="material-icons-round text-green-500 text-2xl">check_circle</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white"><?php echo __('deleted_successfully_msg'); ?></h3>
                        <p class="text-sm text-gray-400" id="delete-summary"><?php echo __('selected_products_deleted'); ?></p>
                    </div>
                </div>
                <button id="close-delete-modal" class="text-gray-400 hover:text-white transition-colors p-2 hover:bg-white/5 rounded-lg">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6">
                <!-- إحصائيات الحذف -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400 mb-1"><?php echo __('total_deleted'); ?></p>
                                <p class="text-3xl font-bold text-green-500" id="total-deleted">0</p>
                            </div>
                            <span class="material-icons-round text-green-500 text-4xl opacity-20">inventory_2</span>
                        </div>
                    </div>
                    
                    <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400 mb-1"><?php echo __('linked_to_invoices'); ?></p>
                                <p class="text-3xl font-bold text-orange-500" id="linked-deleted">0</p>
                            </div>
                            <span class="material-icons-round text-orange-500 text-4xl opacity-20">receipt_long</span>
                        </div>
                    </div>
                </div>

                <!-- قائمة المنتجات المحذوفة -->
                <div id="deleted-products-list" class="space-y-3">
                    <!-- سيتم ملؤها ديناميكياً -->
                </div>

                <!-- ملاحظة مهمة -->
                <div id="linked-note" class="mt-6 bg-blue-500/10 border border-blue-500/30 rounded-xl p-4 hidden">
                    <div class="flex items-start gap-3">
                        <span class="material-icons-round text-blue-500 text-xl mt-0.5">info</span>
                        <div class="flex-1">
                            <h4 class="text-blue-500 font-bold mb-1"><?php echo __('important_note'); ?></h4>
                            <p class="text-sm text-gray-300 leading-relaxed" id="linked-note-text"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 border-t border-white/5 flex justify-end shrink-0">
                <button id="close-delete-modal-btn" class="bg-primary hover:bg-primary-hover text-white px-6 py-2.5 rounded-xl font-bold transition-all hover:-translate-y-0.5 shadow-lg shadow-primary/20">
                    <?php echo __('understood_thanks'); ?>
                </button>
            </div>
        </div>
    </div>

    <style>
    @keyframes scale-in {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-scale-in {
        animation: scale-in 0.3s ease-out;
    }

    /* Light mode adjustments */
    html:not(.dark) #delete-success-modal .bg-dark-surface {
        background-color: #FFFFFF !important;
    }

    html:not(.dark) #delete-success-modal .text-white {
        color: #111827 !important;
    }

    html:not(.dark) #delete-success-modal .text-gray-400 {
        color: #6B7280 !important;
    }

    html:not(.dark) #delete-success-modal .text-gray-300 {
        color: #4B5563 !important;
    }

    html:not(.dark) #delete-success-modal .border-white\/5,
    html:not(.dark) #delete-success-modal .border-white\/10 {
        border-color: rgba(0, 0, 0, 0.1) !important;
    }

    html:not(.dark) #delete-success-modal .bg-white\/5 {
        background-color: rgba(0, 0, 0, 0.05) !important;
    }

    /* Light mode adjustments for import modal */
    html:not(.dark) #import-excel-modal .bg-dark-surface {
        background-color: #FFFFFF !important;
    }

    html:not(.dark) #import-excel-modal .text-white {
        color: #111827 !important;
    }

    html:not(.dark) #import-excel-modal .text-gray-300 {
        color: #6B7280 !important;
    }

    html:not(.dark) #import-excel-modal .text-gray-400 {
        color: #9CA3AF !important;
    }

    html:not(.dark) #import-excel-modal .border-white\/5,
    html:not(.dark) #import-excel-modal .border-white\/10 {
        border-color: rgba(0, 0, 0, 0.1) !important;
    }

    html:not(.dark) #import-excel-modal .bg-dark\/50 {
        background-color: rgba(0, 0, 0, 0.05) !important;
    }

    html:not(.dark) #import-excel-modal .bg-blue-500\/10 {
        background-color: rgba(59, 130, 246, 0.1) !important;
    }

    html:not(.dark) #import-excel-modal .border-blue-500\/30 {
        border-color: rgba(59, 130, 246, 0.3) !important;
    }

    html:not(.dark) #import-excel-modal .text-blue-400 {
        color: #3B82F6 !important;
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

    header{
        background-color: #171d27;
    }
    </style>

    <!-- Pagination (moved here from inside the table for a fixed bottom placement) -->
    <div id="pagination-container" class="sticky bottom-0 p-6 pt-2 flex justify-center items-center z-20 ">
        <!-- Pagination will be loaded here -->
    </div>
</main>

<!-- Bulk Edit Modal -->
<div id="bulk-edit-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white"><?php echo __('bulk_edit_products_title'); ?></h3>
            <button id="close-bulk-edit-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="bulk-edit-form">
            <div class="p-6">
                <p class="text-gray-300 mb-4"><?php echo __('leave_empty_to_keep'); ?></p>
                <div class="space-y-4">
                    <div>
                        <label for="bulk-edit-category" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('category'); ?></label>
                        <select id="bulk-edit-category" name="category_id" class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                            <option value=""><?php echo __('no_change'); ?></option>
                            <!-- Categories will be loaded here -->
                        </select>
                    </div>
                    <div>
                        <label for="bulk-edit-price" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('selling_price'); ?></label>
                        <input type="text" id="bulk-edit-price" name="price" step="0.01" inputmode="numeric" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" placeholder="<?php echo __('leave_empty_to_keep'); ?>">
                    </div>
                    <div>
                        <label for="bulk-edit-quantity" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('quantity'); ?></label>
                        <input type="text" id="bulk-edit-quantity" name="quantity" inputmode="numeric" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" placeholder="<?php echo __('leave_empty_to_keep'); ?>">
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold"><?php echo __('apply_changes'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 id="product-modal-title" class="text-lg font-bold text-white"><?php echo __('add_new_product_title'); ?></h3>
            <button id="close-product-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="product-form" enctype="multipart/form-data">
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="product-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="product-name" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('product_name'); ?></label>
                        <input type="text" id="product-name" name="name" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4">
                        <label for="product-category" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('category'); ?></label>
                        <select id="product-category" name="category_id" class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                            <option value=""><?php echo __('select_category'); ?></option>
                            <!-- Categories will be loaded here -->
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="product-price" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('selling_price'); ?></label>
                        <input type="text" id="product-price" name="price" step="0.01" inputmode="numeric" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4">
                        <label for="product-cost-price" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('cost_price'); ?></label>
                        <input type="text" id="product-cost-price" name="cost_price" step="0.01" inputmode="numeric" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" placeholder="0.00">
                    </div>
                    <div class="mb-4">
                        <label for="product-quantity" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('quantity'); ?></label>
                        <input type="text" id="product-quantity" name="quantity" inputmode="numeric" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="product-barcode" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('barcode'); ?></label>
                        <input type="text" id="product-barcode" name="barcode" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="product-image" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('product_image_label'); ?></label>
                        <div class="flex gap-2">
                            <input type="file" id="product-image" name="image" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                            <button type="button" id="select-from-gallery-btn" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl font-bold">...</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="selected-image-path" name="image_path">


                <div id="custom-fields-container" class="my-4 space-y-4">
                    <!-- Custom fields will be loaded here -->
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all"><?php echo __('save_product'); ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Image Gallery Modal -->
<!-- Image Gallery Modal -->
<div id="image-gallery-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-4xl border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white"><?php echo __('select_image_from_gallery'); ?></h3>
            <button id="close-gallery-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6 max-h-[70vh] overflow-y-auto">
            <div id="image-gallery-grid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- Images will be loaded here -->
            </div>
        </div>
        <div class="p-6 border-t border-white/5 flex justify-end gap-4">
            <button id="select-image-btn" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold"><?php echo __('select_image'); ?></button>
        </div>
    </div>
</div>

<!-- Bulk Add Modal -->
<div id="bulk-add-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-4xl border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white"><?php echo __('bulk_add_products_title'); ?></h3>
            <button id="close-bulk-add-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="bulk-add-form">
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 gap-4">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/10">
                                <th class="p-2 text-sm font-medium text-gray-300"><?php echo __('product_name'); ?></th>
                                <th class="p-2 text-sm font-medium text-gray-300"><?php echo __('category'); ?></th>
                                <th class="p-2 text-sm font-medium text-gray-300"><?php echo __('selling_price'); ?></th>
                                <th class="p-2 text-sm font-medium text-gray-300"><?php echo __('cost_price'); ?></th>
                                <th class="p-2 text-sm font-medium text-gray-300"><?php echo __('quantity'); ?></th>
                                <th class="p-2 text-sm font-medium text-gray-300"><?php echo __('barcode'); ?></th>
                                <th class="p-2 text-sm font-medium text-gray-300"><?php echo __('product_image_label'); ?></th>
                                <th class="p-2 text-sm font-medium text-gray-300"></th>
                            </tr>
                        </thead>
                        <tbody id="bulk-add-table-body">
                            <!-- Rows will be added dynamically here -->
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <button type="button" id="add-bulk-row" class="text-primary hover:text-primary-hover font-bold flex items-center gap-2">
                        <span class="material-icons-round text-sm">add</span>
                        <span><?php echo __('add_new_row'); ?></span>
                    </button>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all"><?php echo __('save_all_products'); ?></button>
            </div>
        </form>
    </div>
</div>


<!-- Import Excel Modal -->
<div id="import-excel-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-2xl border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white"><?php echo __('import_products_excel_title'); ?></h3>
            <button id="close-import-excel-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="import-excel-form" enctype="multipart/form-data">
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('select_excel_file'); ?></label>
                        <input type="file" id="excel-file" name="excel_file" accept=".xlsx,.xls" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4">
                        <h4 class="text-sm font-bold text-blue-400 mb-2"><?php echo __('required_file_format'); ?></h4>
                        <p class="text-xs text-gray-300 mb-2"><?php echo __('file_columns_desc'); ?></p>
                        <ul class="text-xs text-gray-400 space-y-1">
                            <li>• <strong>Name</strong> - <?php echo __('product_name'); ?> (<?php echo __('required'); ?>)</li>
                            <li>• <strong>Price</strong> - <?php echo __('selling_price'); ?> (<?php echo __('required'); ?>)</li>
                            <li>• <strong>Quantity</strong> - <?php echo __('quantity'); ?> (<?php echo __('required'); ?>)</li>
                            <li>• <strong>Barcode</strong> - <?php echo __('barcode'); ?> (<?php echo __('optional'); ?>)</li>
                            <li>• <strong>Category</strong> - <?php echo __('category'); ?> (<?php echo __('optional'); ?>)</li>
                            <li>• <strong>Cost Price</strong> - <?php echo __('cost_price'); ?> (<?php echo __('optional'); ?>)</li>
                            <li>• <strong>Image</strong> - <?php echo __('product_image_label'); ?> (<?php echo __('optional'); ?>)</li>
                        </ul>
                        <div class="mt-3 pt-3 border-t border-blue-500/20">
                            <p class="text-xs text-gray-300 mb-2">💡 <strong><?php echo __('tips'); ?>:</strong> <?php echo __('download_excel_template'); ?>.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="skip-duplicates" name="skip_duplicates" class="rounded border-white/10 bg-dark/50">
                        <label for="skip-duplicates" class="text-sm text-gray-300"><?php echo __('skip_duplicates'); ?></label>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="button" id="download-template-in-modal-btn" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded-xl font-bold transition-all flex items-center gap-2">
                    <span class="material-icons-round text-sm">download</span>
                    <?php echo __('download_excel_template'); ?>
                </button>
                <button type="button" id="preview-import-btn" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded-xl font-bold transition-all flex items-center gap-2">
                    <span class="material-icons-round text-sm">visibility</span>
                    <?php echo __('preview_data'); ?>
                </button>
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all flex items-center gap-2">
                    <span class="material-icons-round text-sm">upload</span>
                    <?php echo __('import_products_btn'); ?>
                </button>
            </div>
        </form>
    </div>
</div>


<!-- Barcode Scanner Modal -->
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

<!-- Product Details Modal -->
<div id="product-details-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-6xl border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white"><?php echo __('product_details_title'); ?></h3>
            <button id="close-product-details-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div id="product-details-content" class="p-6 max-h-[70vh] overflow-y-auto grid grid-cols-1 md:grid-cols-2 gap-8">
            <div id="product-barcode-section" class="border-l border-white/10 pl-8 flex flex-col items-center justify-center">
                <!-- Barcode will be rendered here -->
            </div>
            <div id="product-details-info">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Category Management Modal -->
<div id="category-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-4xl border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white"><?php echo __('manage_categories_title'); ?></h3>
            <button id="close-category-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6 max-h-[75vh] overflow-y-auto">
            <form id="category-form">
                <input type="hidden" id="category-id" name="id">
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="category-name" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('category_name_required'); ?></label>
                        <input type="text" id="category-name" name="name"
                            class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                            required>
                    </div>
                    <div>
                        <label for="category-description" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('category_description'); ?></label>
                        <textarea id="category-description" name="description" rows="2"
                            class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                            placeholder="<?php echo __('category_desc_placeholder'); ?>"></textarea>
                    </div>
                    <div>
                        <label for="category-fields" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('custom_fields_comma'); ?></label>
                        <textarea id="category-fields" name="fields" rows="3"
                            class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                            placeholder="<?php echo __('custom_fields_example'); ?>"></textarea>
                        <p class="text-xs text-gray-500 mt-1"><?php echo __('comma_separator_hint'); ?></p>
                    </div>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" id="cancel-category-edit" class="text-gray-400 hover:text-white px-4 py-2 rounded-xl transition-colors hidden"><?php echo __('cancel_edit'); ?></button>
                    <button type="submit"
                        class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all"><?php echo __('save_category'); ?></button>
                </div>
            </form>
            <hr class="border-white/10 my-6">
            <div>
                <h4 class="text-md font-bold text-white mb-4"><?php echo __('current_categories'); ?> (<?php echo "30"; ?>)</h4>
                <div id="category-list" class="space-y-2 max-h-96 overflow-y-auto">
                    <!-- Categories will be loaded here via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Options Modal -->
<div id="export-options-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span class="material-icons-round text-green-500">download</span>
                <?php echo __('export_options_title'); ?>
            </h3>
            <button id="close-export-options-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6">
            <p class="text-gray-300 mb-6"><?php echo __('export_type_select'); ?></p>
            <div class="space-y-3">
                <button id="export-current-page" class="w-full bg-primary/10 hover:bg-primary/20 border border-primary/30 text-primary p-4 rounded-xl transition-all flex items-center gap-3">
                    <span class="material-icons-round text-primary">table_view</span>
                    <div class="text-right">
                        <div class="font-bold"><?php echo __('export_current_page'); ?></div>
                        <div class="text-sm text-gray-400"><?php echo __('export_current_page_desc'); ?></div>
                    </div>
                </button>
                <button id="export-all-products" class="w-full bg-green-500/10 hover:bg-green-500/20 border border-green-500/30 text-green-400 p-4 rounded-xl transition-all flex items-center gap-3">
                    <span class="material-icons-round text-green-500">inventory_2</span>
                    <div class="text-right">
                        <div class="font-bold"><?php echo __('export_all_products'); ?></div>
                        <div class="text-sm text-gray-400"><?php echo __('export_all_products_desc'); ?></div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // التحقق من وجود رسالة محفوظة في sessionStorage وعرضها
        const savedMessage = sessionStorage.getItem('toastMessage');
        const savedType = sessionStorage.getItem('toastType');
        if (savedMessage && window.showToast) {
            // انتظار قليل للتأكد من تحميل الصفحة بالكامل
            setTimeout(() => {
                showToast(savedMessage, savedType === 'success');
                // حذف الرسالة بعد عرضها
                sessionStorage.removeItem('toastMessage');
                sessionStorage.removeItem('toastType');
            }, 500);
        }

    window.loadProducts = loadProducts;
    window.loadStats = loadStats;
    const manageCategoriesBtn = document.getElementById('manage-categories-btn');
    const categoryModal = document.getElementById('category-modal');
    const closeCategoryModalBtn = document.getElementById('close-category-modal');
    const categoryForm = document.getElementById('category-form');
    const categoryList = document.getElementById('category-list');
    const categoryIdInput = document.getElementById('category-id');
    const categoryNameInput = document.getElementById('category-name');
    const categoryDescriptionInput = document.getElementById('category-description');
    const categoryFieldsInput = document.getElementById('category-fields');
    const cancelCategoryEditBtn = document.getElementById('cancel-category-edit');

    // Numeric input validation
    const bulkEditPriceInput = document.getElementById('bulk-edit-price');
    const bulkEditQuantityInput = document.getElementById('bulk-edit-quantity');
    const productPriceInput = document.getElementById('product-price');
    const productCostPriceInput = document.getElementById('product-cost-price');
    const productQuantityInput = document.getElementById('product-quantity');

    [bulkEditPriceInput, bulkEditQuantityInput, productPriceInput, productCostPriceInput, productQuantityInput].forEach(input => {
        if (input) {
            input.addEventListener('keydown', function(e) {
                const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                if (allowedKeys.includes(e.key)) return;
                if (!/[0-9٠-٩.]/.test(e.key)) {
                    e.preventDefault();
                }
            });
            input.addEventListener('input', function() {
                let value = this.value;
                value = toEnglishNumbers(value);
                value = value.replace(/[^0-9.]/g, '');
                this.value = value;
            });
        }
    });

    const exportOptionsModal = document.getElementById('export-options-modal');
    const closeExportOptionsModalBtn = document.getElementById('close-export-options-modal');
    const exportCurrentPageBtn = document.getElementById('export-current-page');
    const exportAllProductsBtn = document.getElementById('export-all-products');

    const addProductBtn = document.getElementById('add-product-btn');
    const productModal = document.getElementById('product-modal');
    const closeProductModalBtn = document.getElementById('close-product-modal');
    const productForm = document.getElementById('product-form');
    const productCategorySelect = document.getElementById('product-category');
    const customFieldsContainer = document.getElementById('custom-fields-container');
    const productsTableBody = document.getElementById('products-table-body');
    const searchInput = document.getElementById('product-search-input');
    const categoryFilter = document.getElementById('product-category-filter');
    const stockStatusFilter = document.getElementById('stock-status-filter');
    const paginationContainer = document.getElementById('pagination-container');
    const selectAllCheckbox = document.getElementById('select-all-products');

    let currentPage = 1;
    let sortBy = 'name';
    let sortOrder = 'asc';
    const productsPerPage = 300;

    window.reloadProductsAndStats = async function() {
        await loadProducts();
        await loadStats();
    };  

    window.reloadProductsAndStats();
    loadCategoriesIntoFilter();
    searchInput.addEventListener('input', () => { currentPage = 1; loadProducts(); });
    categoryFilter.addEventListener('change', () => { currentPage = 1; loadProducts(); });
    stockStatusFilter.addEventListener('change', () => { currentPage = 1; loadProducts(); });

    async function loadProducts() {
        const searchQuery = searchInput.value;
        const categoryId = categoryFilter.value;
        const stockStatus = stockStatusFilter.value;

        try {
            showLoading(__('loading_products'));
            const response = await fetch(`api.php?action=getProducts&search=${searchQuery}&category_id=${categoryId}&stock_status=${stockStatus}&page=${currentPage}&limit=${productsPerPage}&sortBy=${sortBy}&sortOrder=${sortOrder}`);
            const result = await response.json();
            if (result.success) {
                displayProducts(result.data, <?php echo $low_alert; ?>, <?php echo $critical_alert; ?>);
                renderPagination(result.total_products);
            }
        } catch (error) {
            console.error('خطأ في تحميل المنتجات:', error);
            showToast(__('loading_error'), false);
        } finally {
            hideLoading();
        }
    }

    async function loadStats() {
        try {
            const response = await fetch('api.php?action=getInventoryStats');
            const result = await response.json();
            if (result.success) {
                const stats = result.data;
                const statsBar = document.getElementById('stats-bar');
                statsBar.innerHTML = `
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                        <div class="bg-primary/10 p-3 rounded-xl"><span class="material-icons-round text-primary text-2xl">inventory_2</span></div>
                        <div>
                            <p class="text-gray-400 text-sm">${__('total_products')}</p>
                            <p class="text-white text-xl font-bold">${stats.total_products}</p>
                        </div>
                    </div>
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                        <div class="bg-green-500/10 p-3 rounded-xl"><span class="material-icons-round text-green-500 text-2xl">attach_money</span></div>
                        <div>
                            <p class="text-gray-400 text-sm">${__('stock_value')}</p>
                            <p class="text-white text-xl font-bold">${parseFloat(stats.total_stock_value).toFixed(2)}</p>
                        </div>
                    </div>
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                        <div class="bg-red-500/10 p-3 rounded-xl"><span class="material-icons-round text-red-500 text-2xl">highlight_off</span></div>
                        <div>
                            <p class="text-gray-400 text-sm">${__('out_of_stock_count')}</p>
                            <p class="text-white text-xl font-bold">${stats.out_of_stock}</p>
                        </div>
                    </div>
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                        <div class="bg-yellow-500/10 p-3 rounded-xl"><span class="material-icons-round text-yellow-500 text-2xl">warning</span></div>
                        <div>
                            <p class="text-gray-400 text-sm">${__('low_stock_count')}</p>
                            <p class="text-white text-xl font-bold">${stats.low_stock}</p>
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('خطأ في تحميل الإحصائيات:', error);
        }
    }

    function displayProducts(products, lowAlert, criticalAlert) {
        productsTableBody.innerHTML = '';
        if (products.length === 0) {
            productsTableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-gray-500">${__('no_products_display')}</td></tr>`;
            return;
        }

        products.forEach(product => {
            const productRow = document.createElement('tr');
            
            const qty = parseInt(product.quantity);
            let rowClass = 'transition-colors border-b border-white/5';
            let quantityClass = 'text-gray-300';
            let quantityBadge = '';

            if (qty === 0) {
                rowClass += ' bg-gray-900/40 hover:bg-gray-900/50'; 
                quantityClass = 'text-gray-500 font-bold';
                quantityBadge = `<span class="inline-flex items-center gap-1 text-xs bg-gray-500/20 text-gray-400 px-2 py-1 rounded-full ml-2"><span class="material-icons-round text-xs">block</span>${__('stock_status_out')}</span>`;
            } else if (qty <= criticalAlert) {
                rowClass += ' bg-red-900/20 hover:bg-red-900/30'; 
                quantityClass = 'text-red-400 font-bold';
                quantityBadge = `<span class="inline-flex items-center gap-1 text-xs bg-red-500/20 text-red-400 px-2 py-1 rounded-full ml-2"><span class="material-icons-round text-xs">error</span>${__('stock_status_critical')} (${qty}/${criticalAlert})</span>`;
            } else if (qty <= lowAlert) {
                rowClass += ' bg-orange-900/20 hover:bg-orange-900/30';
                quantityClass = 'text-orange-400 font-bold';
                quantityBadge = `<span class="inline-flex items-center gap-1 text-xs bg-orange-500/20 text-orange-400 px-2 py-1 rounded-full ml-2"><span class="material-icons-round text-xs">warning</span>${__('stock_status_low')} (${qty}/${lowAlert})</span>`;
            } else {
                rowClass += ' bg-transparent hover:bg-white/5';
            }

            productRow.className = rowClass;

            productRow.innerHTML = `
                <td class="p-4"><input type="checkbox" class="product-checkbox bg-dark/50 border-white/20 rounded" data-id="${product.id}"></td>
                <td class="p-4 text-sm text-gray-300 font-medium">${product.name}</td>
                <td class="p-4 text-sm text-gray-300">
                    <img src="${product.image || 'src/img/default-product.png'}" alt="${product.name}" class="w-10 h-10 rounded-md object-cover">
                </td>
                <td class="p-4 text-sm text-gray-300">${product.category_name || '-'}</td>
                <td class="p-4 text-sm text-gray-300">${parseFloat(product.price).toFixed(2)}</td>
                <td class="p-4 text-sm ${quantityClass}">
                    <div class="flex items-center">
                        ${qty}
                        ${quantityBadge}
                    </div>
                </td>
                <td class="p-4 text-sm text-gray-300">
                    <button class="view-details-btn p-1.5 text-gray-400 hover:text-primary transition-colors" data-id="${product.id}">
                        <span class="material-icons-round text-lg">visibility</span>
                    </button>
                </td>
                <td class="p-4 text-sm text-gray-300">
                    <button class="edit-product-btn p-1.5 text-gray-400 hover:text-yellow-500 transition-colors" data-id="${product.id}"><span class="material-icons-round text-lg">edit</span></button>
                    <button class="delete-product-btn p-1.5 text-gray-400 hover:text-red-500 transition-colors" data-id="${product.id}"><span class="material-icons-round text-lg">delete</span></button>
                </td>
            `;
            productsTableBody.appendChild(productRow);
        });
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
            currentPage = parseInt(btn.dataset.page);
            loadProducts();
        }
    });
    
    async function confirmAndDelete(productId) {
        const confirmed = await showConfirmModal(
            __('confirm_delete_product_title'),
            __('confirm_delete_product_msg')
        );
        
        if (confirmed) {
            try {
                showLoading(__('deleting_products'));
                const response = await fetch('api.php?action=bulkDeleteProducts', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_ids: [productId] })
                });
                const result = await response.json();
                if (result.success) {
                    loadProducts();
                    showToast(__('product_deleted_success'), true);
                } else {
                    showToast(result.message || __('product_delete_fail'), false);
                }
            } catch (error) {
                console.error('خطأ في الحذف:', error);
                showToast(__('delete_error'), false);
            } finally {
                hideLoading();
            }
        }
    }

    const exportCsvBtn = document.getElementById('export-csv-btn');
    exportCsvBtn.addEventListener('click', () => {
        exportOptionsModal.classList.remove('hidden');
    });

    closeExportOptionsModalBtn.addEventListener('click', () => {
        exportOptionsModal.classList.add('hidden');
    });

    exportCurrentPageBtn.addEventListener('click', () => {
        performExport(true);
    });

    exportAllProductsBtn.addEventListener('click', () => {
        performExport(false);
    });

    async function performExport(isCurrentPage) {
        try {
            // إظهار شاشة التحميل المخصصة للتصدير
            document.getElementById('export-loading').classList.remove('hidden');
            exportOptionsModal.classList.add('hidden');
            
            let url = 'api.php?action=exportProductsExcel';
            
            if (isCurrentPage) {
                // تصدير الصفحة الحالية مع الفلاتر
                const searchQuery = searchInput.value;
                const categoryId = categoryFilter.value;
                const stockStatus = stockStatusFilter.value;
                url += `&search=${encodeURIComponent(searchQuery)}&category_id=${categoryId}&stock_status=${stockStatus}&page=${currentPage}&limit=${productsPerPage}&sortBy=${sortBy}&sortOrder=${sortOrder}`;
            }
            // لكل المنتجات، لا نضيف أي فلاتر
            
            // إنشاء رابط مؤقت وتحميله
            const link = document.createElement('a');
            link.href = url;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showToast(__('report_exported_success'), true);
            
            // إضافة تأخير قصير قبل إخفاء التحميل لتجنب الإغلاق الفوري
            setTimeout(() => {
                document.getElementById('export-loading').classList.add('hidden');
            }, 1500);
        } catch (error) {
            console.error('خطأ في تصدير Excel:', error);
            showToast(__('export_error'), false);
            document.getElementById('export-loading').classList.add('hidden');
        }
    }

    const printLabelsBtn = document.getElementById('print-labels-btn');
    printLabelsBtn.addEventListener('click', async () => {
        const selectedIds = getSelectedProductIds();
        if (selectedIds.length === 0) {
            showToast(__('select_products_print'), false);
            return;
        }

        try {
            showLoading(__('preparing_labels'));
            const response = await fetch(`api.php?action=getProducts&ids=${selectedIds.join(',')}`);
            const result = await response.json();
            if (result.success) {
                const products = result.data;
                const printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>' + __('print_barcode_labels') + '</title>');
                printWindow.document.write('<style>body { font-family: sans-serif; text-align: center; } .label { display: inline-block; border: 1px solid #000; padding: 10px; margin: 5px; } svg { height: 50px; } </style>');
                printWindow.document.write('</head><body>');
                products.forEach(p => {
                    if (p.barcode) {
                        printWindow.document.write(`<div class="label"><div>${p.name}</div><svg id="barcode-${p.id}"></svg></div>`);
                    }
                });
                printWindow.document.write('</body></html>');
                printWindow.document.close();

                products.forEach(p => {
                    if (p.barcode) {
                        JsBarcode(printWindow.document.getElementById(`barcode-${p.id}`), p.barcode, {
                            format: "CODE128",
                            displayValue: true,
                            fontSize: 14,
                            margin: 10,
                            height: 40
                        });
                    }
                });

                printWindow.print();
            } else {
                showToast(__('failed_prepare_labels'), false);
            }
        } catch (error) {
            console.error('خطأ في طباعة الملصقات:', error);
            showToast(__('failed_prepare_labels'), false);
        } finally {
            hideLoading();
        }
    });

    document.querySelectorAll('.sortable-header').forEach(header => {
        header.addEventListener('click', () => {
            const sortField = header.dataset.sort;
            if (sortBy === sortField) {
                sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy = sortField;
                sortOrder = 'asc';
            }
            document.querySelectorAll('.sort-icon').forEach(icon => icon.classList.add('opacity-30'));
            const currentIcon = header.querySelector('.sort-icon');
            currentIcon.classList.remove('opacity-30');
            currentIcon.textContent = sortOrder === 'asc' ? '▲' : '▼';
            loadProducts();
        });
    });

    selectAllCheckbox.addEventListener('change', () => {
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateBulkActionsBar();
    });

    productsTableBody.addEventListener('change', e => {
        if (e.target.classList.contains('product-checkbox')) {
            updateBulkActionsBar();
        }
    });

    function getSelectedProductIds() {
        return Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.dataset.id);
    }

    function updateBulkActionsBar() {
        const selectedIds = getSelectedProductIds();
        const bulkActionsBar = document.getElementById('bulk-actions-bar');
        const selectedCount = document.getElementById('selected-count');

        if (selectedIds.length > 0) {
            bulkActionsBar.classList.remove('hidden');
            selectedCount.textContent = `${selectedIds.length} ${__('selected_count')}`;
        } else {
            bulkActionsBar.classList.add('hidden');
        }
    }

    const bulkEditBtn = document.getElementById('bulk-edit-btn');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const bulkEditModal = document.getElementById('bulk-edit-modal');
    const closeBulkEditModalBtn = document.getElementById('close-bulk-edit-modal');
    const bulkEditForm = document.getElementById('bulk-edit-form');
    const bulkEditCategorySelect = document.getElementById('bulk-edit-category');

    bulkEditBtn.addEventListener('click', async () => {
        const categories = await loadCategories();
        bulkEditCategorySelect.innerHTML = `<option value="">${__('no_change')}</option>`;
        if (categories) {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                bulkEditCategorySelect.appendChild(option);
            });
        }
        bulkEditModal.classList.remove('hidden');
    });

    closeBulkEditModalBtn.addEventListener('click', () => {
        bulkEditModal.classList.add('hidden');
    });

    bulkEditForm.addEventListener('submit', async e => {
        e.preventDefault();
        const selectedIds = getSelectedProductIds();
        const formData = new FormData(bulkEditForm);
        const data = {
            product_ids: selectedIds,
            category_id: formData.get('category_id'),
            price: formData.get('price'),
            quantity: formData.get('quantity')
        };

        try {
            showLoading(__('updating_products'));
            const response = await fetch('api.php?action=bulkUpdateProducts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            if (result.success) {
                bulkEditModal.classList.add('hidden');
                bulkEditForm.reset();
                loadProducts();
                showToast(result.message || __('bulk_update_success'), true);
            } else {
                showToast(result.message || __('bulk_update_fail'), false);
            }
        } catch (error) {
            console.error('خطأ في التحديث الجماعي:', error);
            showToast(__('bulk_update_fail'), false);
        } finally {
            hideLoading();
        }
    });

    bulkDeleteBtn.addEventListener('click', async () => {
        const selectedIds = getSelectedProductIds();
        
        if (selectedIds.length === 0) {
            showToast(__('select_products_delete'), false);
            return;
        }
        
        const confirmed = await showConfirmModal(
            __('confirm_bulk_delete_title'),
            __('confirm_bulk_delete_msg').replace('%d', selectedIds.length)
        );
        
        if (confirmed) {
            try {
                showLoading(__('deleting_products'));
                
                const response = await fetch('api.php?action=bulkDeleteProducts', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_ids: selectedIds })
                });
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Response is not JSON:', text);
                    throw new Error('الاستجابة ليست بصيغة JSON صحيحة');
                }
                
                const result = await response.json();
                
                if (result.success) {
                    // إعادة تحميل البيانات
                    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
                    selectAllCheckbox.checked = false;
                    updateBulkActionsBar();
                    
                    await loadProducts();
                    await loadStats();
                    
                    // عرض Modal التفاصيل
                    showDeleteSuccessModal(result);
                    
                } else {
                    showToast(result.message || __('product_delete_fail'), false);
                }
            } catch (error) {
                console.error('خطأ في الحذف الجماعي:', error);
                showToast(__('delete_error') + ': ' + error.message, false);
            } finally {
                hideLoading();
            }
        }
    });

    // دالة عرض Modal النجاح
    function showDeleteSuccessModal(result) {
        const modal = document.getElementById('delete-success-modal');
        const deleteSummary = document.getElementById('delete-summary');
        const totalDeleted = document.getElementById('total-deleted');
        const linkedDeleted = document.getElementById('linked-deleted');
        const deletedProductsList = document.getElementById('deleted-products-list');
        const linkedNote = document.getElementById('linked-note');
        const linkedNoteText = document.getElementById('linked-note-text');
        
        // تحديث الإحصائيات
        deleteSummary.textContent = `${__('deleted_successfully_msg')} ${result.deleted_count}`;
        totalDeleted.textContent = result.deleted_count;
        
        const linkedCount = result.linked_info ? result.linked_info.count : 0;
        linkedDeleted.textContent = linkedCount;
        
        // مسح القائمة السابقة
        deletedProductsList.innerHTML = '';
        
        // بناء قائمة المنتجات
        if (result.linked_info && result.linked_info.products) {
            // عرض المنتجات المرتبطة بفواتير
            result.linked_info.products.forEach((productInfo, index) => {
                const productCard = document.createElement('div');
                productCard.className = 'bg-orange-500/10 border border-orange-500/30 rounded-xl p-4 flex items-center gap-4 hover:bg-orange-500/20 transition-colors';
                
                productCard.innerHTML = `
                    <div class="w-10 h-10 rounded-full bg-orange-500/20 flex items-center justify-center shrink-0">
                        <span class="material-icons-round text-orange-500">warning</span>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-white mb-1">${productInfo.split(' (')[0]}</p>
                        <p class="text-xs text-gray-400 flex items-center gap-1">
                            <span class="material-icons-round text-xs">receipt_long</span>
                            <span>${__('linked_to_invoices')} ${productInfo.match(/\((\d+)/)?.[1] || '0'}</span>
                        </p>
                    </div>
                    <span class="text-xs bg-orange-500/20 text-orange-400 px-2 py-1 rounded font-bold">${__('deleted_products')}</span>
                `;
                
                deletedProductsList.appendChild(productCard);
            });
            
            // عرض الملاحظة
            linkedNote.classList.remove('hidden');
            linkedNoteText.textContent = result.linked_info.note;
        } else {
            // لا توجد منتجات مرتبطة
            const noLinkedCard = document.createElement('div');
            noLinkedCard.className = 'bg-green-500/10 border border-green-500/30 rounded-xl p-6 text-center';
            noLinkedCard.innerHTML = `
                <span class="material-icons-round text-green-500 text-5xl mb-3">check_circle</span>
                <p class="text-white font-bold mb-1">${__('deleted_successfully_msg')}</p>
                <p class="text-sm text-gray-400">${__('selected_products_deleted')}</p>
            `;
            deletedProductsList.appendChild(noLinkedCard);
            
            linkedNote.classList.add('hidden');
        }
        
        // عرض Modal
        modal.classList.remove('hidden');
    }

    // معالجات إغلاق Modal
    document.getElementById('close-delete-modal').addEventListener('click', () => {
        document.getElementById('delete-success-modal').classList.add('hidden');
    });

    document.getElementById('close-delete-modal-btn').addEventListener('click', () => {
        document.getElementById('delete-success-modal').classList.add('hidden');
    });

    // إغلاق عند الضغط خارج Modal
    document.getElementById('delete-success-modal').addEventListener('click', (e) => {
        if (e.target.id === 'delete-success-modal') {
            document.getElementById('delete-success-modal').classList.add('hidden');
        }
    });

    // إغلاق بزر Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('delete-success-modal');
            if (!modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        }
    });

    const productDetailsModal = document.getElementById('product-details-modal');
    const closeProductDetailsModalBtn = document.getElementById('close-product-details-modal');
    const productDetailsContent = document.getElementById('product-details-content');

    productsTableBody.addEventListener('click', async (e) => {
        if (e.target.closest('.view-details-btn')) {
            const btn = e.target.closest('.view-details-btn');
            const productId = btn.dataset.id;
            const product = await getProductDetails(productId);
            if (product) {
                displayProductDetails(product);
                productDetailsModal.classList.remove('hidden');
            }
        }
        
        if (e.target.closest('.edit-product-btn')) {
            const btn = e.target.closest('.edit-product-btn');
            const productId = btn.dataset.id;
            await openEditModal(productId);
        }
        
        if (e.target.closest('.delete-product-btn')) {
            const btn = e.target.closest('.delete-product-btn');
            const productId = btn.dataset.id;
            // You would typically show a confirmation dialog before deleting
            console.log(`Delete product with ID: ${productId}`);
            await confirmAndDelete(productId);
        }
    });

    closeProductDetailsModalBtn.addEventListener('click', () => {
        productDetailsModal.classList.add('hidden');
    });

    async function getProductDetails(id) {
        try {
            const response = await fetch(`api.php?action=getProductDetails&id=${id}`);
            const result = await response.json();
            if (!result.success) {
                showToast(result.message || __('failed_load_product_details'), false);
            }
            return result.success ? result.data : null;
        } catch (error) {
            console.error('خطأ في تحميل تفاصيل المنتج:', error);
            showToast(__('error_loading_product_details'), false);
            return null;
        }
    }

    function displayProductDetails(product) {
        const productDetailsInfo = document.getElementById('product-details-info');
        const productBarcodeSection = document.getElementById('product-barcode-section');

        let fieldsHtml = product.custom_fields.map(field => `
            <div class="flex justify-between py-1">
                <span class="font-medium text-gray-400">${field.field_name}:</span>
                <span class="text-white">${field.value}</span>
            </div>
        `).join('');

        productDetailsInfo.innerHTML = `
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="font-medium text-gray-400">${__('product_name')}:</span><span class="text-white">${product.name}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">${__('category')}:</span><span class="text-white">${product.category_name}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">${__('selling_price')}:</span><span class="text-white">${product.price}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">${__('cost_price')}:</span><span class="text-white">${product.cost_price || '0.00'}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">${__('quantity')}:</span><span class="text-white">${product.quantity}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">${__('barcode')}:</span><span class="text-white">${product.barcode || 'N/A'}</span></div>
                ${fieldsHtml ? '<hr class="border-white/10 my-3"><h4 class="text-md font-bold text-white pt-2 mb-2">' + __('custom_fields_optional') + '</h4>' + fieldsHtml : '<hr class="border-white/10 my-3"><p class="text-gray-500">' + __('no_custom_fields') + '</p>'}
            </div>
        `;

        if (product.barcode) {
            productBarcodeSection.innerHTML = `
                <svg id="barcode-svg"></svg>
                <div class="flex gap-4 mt-4">
                    <button id="download-barcode-pdf" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold flex items-center gap-2">
                        <span class="material-icons-round text-sm">picture_as_pdf</span>
                        <span>${__('download_pdf')}</span>
                    </button>
                    <button id="print-barcode" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl font-bold flex items-center gap-2">
                        <span class="material-icons-round text-sm">print</span>
                        <span>${__('print_btn')}</span>
                    </button>
                </div>
            `;

            JsBarcode("#barcode-svg", product.barcode, {
                format: "CODE128",
                displayValue: true,
                fontSize: 18,
                textMargin: 0,
                background: '#ffffff',
                lineColor: '#000000',
                width: 2,
                height: 100,
                fontOptions: "bold"
            });
            
            document.getElementById('download-barcode-pdf').addEventListener('click', () => {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();
                const svgElement = document.getElementById('barcode-svg');
                const svgData = new XMLSerializer().serializeToString(svgElement);
                const canvas = document.createElement("canvas");
                const ctx = canvas.getContext("2d");
                const img = new Image();
                img.onload = () => {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0);
                    const dataUrl = canvas.toDataURL("image/png");
                    doc.addImage(dataUrl, 'PNG', 15, 40, 180, 72);
                    doc.save(`${product.name}-barcode.pdf`);
                };
                img.src = "data:image/svg+xml;base64," + btoa(svgData);
            });

            document.getElementById('print-barcode').addEventListener('click', () => {
                const svgElement = document.getElementById('barcode-svg');
                const svgData = new XMLSerializer().serializeToString(svgElement);
                const printWindow = window.open('', '', 'height=400,width=800');
                printWindow.document.write('<html><head><title>' + __('scan_barcode_title') + '</title></head><body>');
                printWindow.document.write(svgData);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.print();
            });

        } else {
            productBarcodeSection.innerHTML = `<p class="text-gray-500">${__('no_barcode_product')}</p>`;
        }
    }
    async function openEditModal(productId) {
    try {
        showLoading(__('loading_product_data'));
        const product = await getProductDetails(productId);
        if (product) {
            // Reset form and set modal title
            productForm.reset();
            customFieldsContainer.innerHTML = '';
            document.getElementById('product-modal-title').textContent = __('edit_product_title');

            // Populate main form fields
            document.getElementById('product-id').value = product.id;
            document.getElementById('product-name').value = product.name;
            document.getElementById('product-price').value = product.price;
            document.getElementById('product-quantity').value = product.quantity;
            document.getElementById('product-cost-price').value = product.cost_price || '';
            document.getElementById('product-barcode').value = product.barcode || '';
            
            // Load categories and set the correct one
            await loadCategoriesIntoSelect();
            document.getElementById('product-category').value = product.category_id;
            
            // Load and display custom fields synchronously
            if (product.category_id) {
                const fields = await getCategoryFields(product.category_id);
                displayCustomFields(fields);

                // Now that fields are in the DOM, populate them
                if (product.custom_fields && Array.isArray(product.custom_fields)) {
                    product.custom_fields.forEach(savedField => {
                        const fieldInput = document.getElementById(`custom-field-${savedField.id}`);
                        if (fieldInput) {
                            fieldInput.value = savedField.value;
                        }
                    });
                }
            }
            
            productModal.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error opening edit modal:', error);
        showToast(__('error_loading_product_details'), false);
    } finally {
        hideLoading();
    }
}
    addProductBtn.addEventListener('click', async () => {
        productForm.reset();
        document.getElementById('product-modal-title').textContent = __('add_new_product_title');
        document.getElementById('product-id').value = '';
        customFieldsContainer.innerHTML = '';
        await loadCategoriesIntoSelect();
        productModal.classList.remove('hidden');
    });

    const bulkAddBtn = document.getElementById('bulk-add-btn');
    const bulkAddModal = document.getElementById('bulk-add-modal');
    const closeBulkAddModalBtn = document.getElementById('close-bulk-add-modal');
    const addBulkRowBtn = document.getElementById('add-bulk-row');
    const bulkAddTableBody = document.getElementById('bulk-add-table-body');
    let categoriesCache = [];
    let imagesCache = [];

    async function addBulkRow() {
        if (categoriesCache.length === 0) {
            categoriesCache = await loadCategories();
        }

        const row = document.createElement('tr');
        row.className = 'border-b border-white/5';
        let categoryOptions = `<option value="">${__('select_category')}</option>`;
        if (categoriesCache) {
            categoriesCache.forEach(category => {
                categoryOptions += `<option value="${category.id}">${category.name}</option>`;
            });
        }

        row.innerHTML = `
            <td class="p-2"><input type="text" name="name[]" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2 rounded-xl focus:outline-none focus:border-primary/50" required></td>
            <td class="p-2">
                <select name="category_id[]" class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                    ${categoryOptions}
                </select>
            </td>
            <td class="p-2"><input type="text" name="price[]" step="0.01" inputmode="numeric" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2 rounded-xl focus:outline-none focus:border-primary/50" required></td>
            <td class="p-2"><input type="text" name="cost_price[]" step="0.01" inputmode="numeric" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2 rounded-xl focus:outline-none focus:border-primary/50" placeholder="0.00"></td>
            <td class="p-2"><input type="text" name="quantity[]" inputmode="numeric" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2 rounded-xl focus:outline-none focus:border-primary/50" required></td>
            <td class="p-2"><input type="text" name="barcode[]" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2 rounded-xl focus:outline-none focus:border-primary/50"></td>
            <td class="p-2">
                <input type="hidden" name="image_path[]" class="bulk-image-path">
                <div class="flex items-center gap-1">
                    <span class="bulk-image-display text-xs text-gray-400 truncate flex-1" title="${__('no_image_selected')}">${__('no_image_selected')}...</span>
                    <button type="button" class="bulk-upload-btn p-1.5 text-gray-400 hover:text-primary transition-colors" title="${__('product_image_label')}"><span class="material-icons-round text-lg">upload</span></button>
                    <button type="button" class="bulk-gallery-btn p-1.5 text-gray-400 hover:text-primary transition-colors" title="${__('select_image_from_gallery')}"><span class="material-icons-round text-lg">photo_library</span></button>
                </div>
                <input type="file" class="hidden-file-input" accept="image/*" style="display:none;">
            </td>
            <td class="p-2">
                <button type="button" class="remove-bulk-row text-red-500 hover:text-red-400">
                    <span class="material-icons-round text-lg">delete</span>
                </button>
            </td>
        `;
        bulkAddTableBody.appendChild(row);

        // Add validation to numeric inputs in the new row
        const numericInputs = row.querySelectorAll('input[name="price[]"], input[name="cost_price[]"], input[name="quantity[]"]');
        numericInputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
                if (allowedKeys.includes(e.key)) return;
                if (!/[0-9٠-٩.]/.test(e.key)) {
                    e.preventDefault();
                }
            });
            input.addEventListener('input', function() {
                let value = this.value;
                value = toEnglishNumbers(value);
                value = value.replace(/[^0-9.]/g, '');
                this.value = value;
            });
        });
    }

    bulkAddBtn.addEventListener('click', () => {
        bulkAddModal.classList.remove('hidden');
        if (bulkAddTableBody.rows.length === 0) {
            addBulkRow();
        }
    });

    closeBulkAddModalBtn.addEventListener('click', () => {
        bulkAddModal.classList.add('hidden');
    });

    addBulkRowBtn.addEventListener('click', addBulkRow);

    let bulkImageTarget = null; // To store the row elements for the gallery

    bulkAddTableBody.addEventListener('click', e => {
        if (e.target.closest('.remove-bulk-row')) {
            e.target.closest('tr').remove();
        }
        if (e.target.closest('.bulk-upload-btn')) {
            const row = e.target.closest('tr');
            const fileInput = row.querySelector('.hidden-file-input');
            fileInput.click();
        }
        if (e.target.closest('.bulk-gallery-btn')) {
            const row = e.target.closest('tr');
            bulkImageTarget = {
                pathInput: row.querySelector('.bulk-image-path'),
                displaySpan: row.querySelector('.bulk-image-display')
            };
            imageGalleryModal.classList.remove('hidden');
            populateGallery();
        }
    });

    bulkAddTableBody.addEventListener('change', async e => {
        if (e.target.classList.contains('hidden-file-input')) {
            const fileInput = e.target;
            const row = fileInput.closest('tr');
            const pathInput = row.querySelector('.bulk-image-path');
            const displaySpan = row.querySelector('.bulk-image-display');
            const file = fileInput.files[0];

            if (file) {
                const formData = new FormData();
                formData.append('image', file);

                try {
                    displaySpan.textContent = __('uploading');
                    const response = await fetch('api.php?action=uploadImage', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.success && result.filePath) {
                        pathInput.value = result.filePath;
                        const fileName = result.filePath.split('/').pop();
                        displaySpan.textContent = fileName;
                        displaySpan.title = result.filePath;
                        showToast(__('image_upload_success'), true);
                    } else {
                        displaySpan.textContent = __('upload_failed_text');
                        showToast(result.message || __('image_upload_fail'), false);
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    displaySpan.textContent = __('error');
                    showToast(__('upload_error'), false);
                } finally {
                    fileInput.value = '';
                }
            }
        }
    });

    const bulkAddForm = document.getElementById('bulk-add-form');
    bulkAddForm.addEventListener('submit', async e => {
        e.preventDefault();
        const formData = new FormData(bulkAddForm);
        const products = [];
        const names = formData.getAll('name[]');
        const category_ids = formData.getAll('category_id[]');
        const prices = formData.getAll('price[]');
        const cost_prices = formData.getAll('cost_price[]');
        const quantities = formData.getAll('quantity[]');
        const barcodes = formData.getAll('barcode[]');
        const image_paths = formData.getAll('image_path[]');

        for (let i = 0; i < names.length; i++) {
            products.push({
                name: names[i],
                category_id: category_ids[i],
                price: prices[i],
                cost_price: cost_prices[i],
                quantity: quantities[i],
                barcode: barcodes[i],
                image_path: image_paths[i]
            });
        }

        try {
            showLoading(__('adding_products'));
            const response = await fetch('api.php?action=bulkAddProducts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ products })
            });
            const result = await response.json();
            if (result.success) {
                bulkAddModal.classList.add('hidden');
                bulkAddForm.reset();
                bulkAddTableBody.innerHTML = '';
                addBulkRow();
                loadProducts();
                showToast(result.message || __('bulk_add_success'), true);
            } else {
                showToast(result.message || __('bulk_add_fail'), false);
            }
        } catch (error) {
            console.error('خطأ في الإضافة الجماعية:', error);
            showToast(__('bulk_add_fail'), false);
        } finally {
            hideLoading();
        }
    });

    const selectFromGalleryBtn = document.getElementById('select-from-gallery-btn');
    const imageGalleryModal = document.getElementById('image-gallery-modal');
    const closeGalleryModalBtn = document.getElementById('close-gallery-modal');
    const imageGalleryGrid = document.getElementById('image-gallery-grid');
    const selectImageBtn = document.getElementById('select-image-btn');
    let selectedImage = null;

    async function populateGallery() {
        try {
            const response = await fetch('api.php?action=getUploadedImages');
            const result = await response.json();
            if (result.success) {
                imageGalleryGrid.innerHTML = '';
                result.data.forEach(image => {
                    const imgElement = document.createElement('img');
                    imgElement.src = image.file_path;
                    imgElement.dataset.path = image.file_path;
                    imgElement.className = 'w-full h-32 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-primary';
                    imgElement.addEventListener('click', () => {
                        if (selectedImage) {
                            selectedImage.classList.remove('border-primary');
                        }
                        imgElement.classList.add('border-primary');
                        selectedImage = imgElement;
                    });
                    imageGalleryGrid.appendChild(imgElement);
                });
            }
        } catch (error) {
            console.error('Error populating gallery:', error);
            showToast('Failed to load image gallery', false);
        }
    }

    selectFromGalleryBtn.addEventListener('click', () => {
        imageGalleryModal.classList.remove('hidden');
        populateGallery();
    });

    closeGalleryModalBtn.addEventListener('click', () => {
        imageGalleryModal.classList.add('hidden');
        if (bulkImageTarget) {
            bulkImageTarget = null; // Reset on close
        }
    });

    selectImageBtn.addEventListener('click', () => {
        if (selectedImage) {
            if (bulkImageTarget) {
                // We are selecting for a bulk add row
                bulkImageTarget.pathInput.value = selectedImage.dataset.path;
                const fileName = selectedImage.dataset.path.split('/').pop();
                bulkImageTarget.displaySpan.textContent = fileName;
                bulkImageTarget.displaySpan.title = selectedImage.dataset.path;
                bulkImageTarget = null; // Reset the target
            } else {
                // Original behavior: selecting for the single product modal
                document.getElementById('selected-image-path').value = selectedImage.dataset.path;
            }
            imageGalleryModal.classList.add('hidden');
            if (selectedImage) {
                selectedImage.classList.remove('border-primary');
            }
            selectedImage = null; // Reset selection
        } else {
            showToast(__('select_image_first'), false);
        }
    });

    closeProductModalBtn.addEventListener('click', () => {
        productModal.classList.add('hidden');
    });

    productCategorySelect.addEventListener('change', async (e) => {
        const categoryId = e.target.value;
        if (categoryId) {
            const fields = await getCategoryFields(categoryId);
            displayCustomFields(fields);
        } else {
            customFieldsContainer.innerHTML = '';
        }
    });

    async function getCategoryFields(categoryId) {
        try {
            const response = await fetch(`api.php?action=getCategoryFields&category_id=${categoryId}`);
            const result = await response.json();
            return result.success ? result.data : [];
        } catch (error) {
            console.error('خطأ في تحميل حقول الفئة:', error);
            return [];
        }
    }

    function displayCustomFields(fields) {
        customFieldsContainer.innerHTML = '';
        if (fields.length > 0) {
            const title = document.createElement('h4');
            title.className = 'text-sm font-bold text-white mb-3 pt-2 border-t border-white/10';
            title.textContent = __('custom_fields_optional');
            customFieldsContainer.appendChild(title);
        }
        
        fields.forEach(field => {
            const fieldEl = document.createElement('div');
            fieldEl.innerHTML = `
                <label for="custom-field-${field.id}" class="block text-sm font-medium text-gray-300 mb-2">${field.field_name}</label>
                <input type="text" id="custom-field-${field.id}" name="custom_fields[${field.id}]" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
            `;
            customFieldsContainer.appendChild(fieldEl);
        });
    }
    
    productForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(productForm);
        
        const customFields = [];
        for (const [key, value] of formData.entries()) {
            if (key.startsWith('custom_fields')) {
                const fieldId = key.match(/\[(\d+)\]/)[1];
                customFields.push({ id: fieldId, value });
            }
        }
        formData.append('fields', JSON.stringify(customFields));

        const productId = document.getElementById('product-id').value;
        const action = productId ? 'updateProduct' : 'addProduct';
        const successMessage = productId ? __('product_updated_success') : __('product_added_success');
        const errorMessage = productId ? __('product_update_fail') : __('product_add_fail');

        try {
            showLoading(__('saving_product'));
            const response = await fetch(`api.php?action=${action}`, {
                method: 'POST',
                body: formData,
            });

            const responseText = await response.text();
            try {
                const result = JSON.parse(responseText);
                if (result.success) {
                    productModal.classList.add('hidden');
                    productForm.reset();
                    customFieldsContainer.innerHTML = '';
                    loadProducts(); // Reload products to show changes
                    showToast(result.message || successMessage, true);
                } else {
                    console.error('API Error:', result.message);
                    showToast(result.message || errorMessage, false);
                }
            } catch (e) {
                console.error("Failed to parse JSON response. Server response:", responseText);
                showToast('An invalid response was received from the server.', false);
            }
        } catch (error) {
            console.error(`خطأ في ${productId ? 'تحديث' : 'إضافة'} المنتج:`, error);
            showToast(productId ? __('product_update_fail') : __('product_add_fail'), false);
        } finally {
            hideLoading();
        }
    });

    manageCategoriesBtn.addEventListener('click', () => {
        categoryModal.classList.remove('hidden');
        loadCategories();
    });

    closeCategoryModalBtn.addEventListener('click', () => {
        categoryModal.classList.add('hidden');
    });

    async function loadCategories() {
        try {
            const response = await fetch('api.php?action=getCategories');
            const result = await response.json();
            if (result.success) {
                displayCategories(result.data);
                return result.data;
            }
        } catch (error) {
            console.error('خطأ في تحميل الفئات:', error);
            showToast(__('category_load_error'), false);
        }
        return [];
    }

    async function loadCategoriesIntoSelect() {
        const categories = await loadCategories();
        productCategorySelect.innerHTML = `<option value="">${__('select_category')}</option>`;
        if(categories) {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                productCategorySelect.appendChild(option);
            });
        }
        productCategorySelect.setAttribute('data-loaded', 'true');
    }

    async function loadCategoriesIntoFilter() {
        const categories = await loadCategories();
        categoryFilter.innerHTML = `<option value="">${__('all_categories')}</option>`;
        if(categories) {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categoryFilter.appendChild(option);
            });
        }
    }

    function displayCategories(categories) {
        categoryList.innerHTML = '';
        if (categories.length === 0) {
            categoryList.innerHTML = `<p class="text-gray-500">${__('no_categories_now')}</p>`;
            return;
        }

        categories.forEach(category => {
            const categoryEl = document.createElement('div');
            categoryEl.className = 'flex justify-between items-start bg-dark/50 p-4 rounded-lg border border-white/5 hover:border-primary/30 transition-colors';
            
            const fieldsArray = category.fields ? category.fields.split(',') : [];
            const fieldsCount = fieldsArray.length;
            const fieldsPreview = fieldsArray.slice(0, 5).join(', ');
            const hasMoreFields = fieldsCount > 5;
            
            categoryEl.innerHTML = `
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-icons-round text-primary text-sm">category</span>
                        <span class="font-bold text-white">${category.name}</span>
                        ${fieldsCount > 0 ? `<span class="text-xs text-gray-500 bg-white/5 px-2 py-0.5 rounded">${fieldsCount} ${__('fields_count').replace('%d', '')}</span>` : ''}
                    </div>
                    ${category.description ? `<p class="text-xs text-gray-400 mb-2">${category.description}</p>` : ''}
                    ${fieldsCount > 0 ? `
                        <p class="text-xs text-gray-500">
                            <strong>${__('fields_label')}</strong> ${fieldsPreview}${hasMoreFields ? '...' : ''}
                        </p>
                    ` : `<p class="text-xs text-gray-500">${__('no_custom_fields_cat')}</p>`}
                </div>
                <div class="flex gap-2 mr-4">
                    <button class="edit-category-btn p-2 text-gray-400 hover:text-primary transition-colors" 
                        data-id="${category.id}" 
                        data-name="${category.name}" 
                        data-description="${category.description || ''}"
                        data-fields="${category.fields || ''}"
                        title="${__('edit_cat_tooltip')}">
                        <span class="material-icons-round text-lg">edit</span>
                    </button>
                    <button class="delete-category-btn p-2 text-gray-400 hover:text-red-500 transition-colors" 
                        data-id="${category.id}"
                        title="${__('delete_cat_tooltip')}">
                        <span class="material-icons-round text-lg">delete</span>
                    </button>
                </div>
            `;
            
            categoryList.appendChild(categoryEl);
        });
    }

    categoryForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const id = categoryIdInput.value;
        const name = categoryNameInput.value;
        const description = categoryDescriptionInput.value;
        const fieldsText = categoryFieldsInput.value;
        const fields = fieldsText.split(/,|،/).map(s => s.trim()).filter(Boolean);

        const url = id ? 'api.php?action=updateCategory' : 'api.php?action=addCategory';

        try {
            showLoading(__('saving_category'));
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, name, description, fields }),
            });
            const result = await response.json();
            if (result.success) {
                resetForm();
                loadCategories();
                loadCategoriesIntoSelect();
                loadCategoriesIntoFilter();
                showToast(result.message || __('category_saved_success'), true);
            } else {
                showToast(result.message || __('category_save_fail'), false);
            }
        } catch (error) {
            console.error('خطأ في حفظ الفئة:', error);
            showToast(__('category_save_fail'), false);
        } finally {
            hideLoading();
        }
    });

    categoryList.addEventListener('click', async function (e) {
        if (e.target.closest('.edit-category-btn')) {
            const btn = e.target.closest('.edit-category-btn');
            categoryIdInput.value = btn.dataset.id;
            categoryNameInput.value = btn.dataset.name;
            categoryDescriptionInput.value = btn.dataset.description || '';
            categoryFieldsInput.value = btn.dataset.fields || '';
            cancelCategoryEditBtn.classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        
        if (e.target.closest('.delete-category-btn')) {
            const btn = e.target.closest('.delete-category-btn');
            const id = btn.dataset.id;
            
            const confirmed = await showConfirmModal(
                __('confirm_delete_category_title'),
                __('confirm_delete_category_msg')
            );
            
            if (confirmed) {
                deleteCategory(id);
            }
        }
    });
    
    cancelCategoryEditBtn.addEventListener('click', resetForm);

    function resetForm() {
        categoryForm.reset();
        categoryIdInput.value = '';
        categoryDescriptionInput.value = '';
        cancelCategoryEditBtn.classList.add('hidden');
    }

    async function deleteCategory(id) {
        try {
            showLoading(__('deleting_category'));
            const response = await fetch('api.php?action=deleteCategory', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            });
            const result = await response.json();
            if (result.success) {
                loadCategories();
                loadCategoriesIntoSelect();
                loadCategoriesIntoFilter();
                showToast(result.message || __('category_deleted_success'), true);
            } else {
                showToast(result.message || __('category_delete_fail'), false);
            }
        } catch (error) {
            console.error('خطأ في حذف الفئة:', error);
            showToast(__('category_delete_fail'), false);
        } finally {
            hideLoading();
        }
    }

    // Barcode Scanner
    const scanBarcodeBtn = document.getElementById('scan-barcode-btn');
    const barcodeScannerModal = document.getElementById('barcode-scanner-modal');
    const closeBarcodeScannerModalBtn = document.getElementById('close-barcode-scanner-modal');
    const videoElement = document.getElementById('barcode-video');
    let codeReader;

    scanBarcodeBtn.addEventListener('click', () => {
        barcodeScannerModal.classList.remove('hidden');
        startBarcodeScanner();
    });

    closeBarcodeScannerModalBtn.addEventListener('click', () => {
        barcodeScannerModal.classList.add('hidden');
        stopBarcodeScanner();
    });

    function startBarcodeScanner() {
        codeReader = new ZXing.BrowserMultiFormatReader();
        codeReader.listVideoInputDevices()
            .then((videoInputDevices) => {
                if (videoInputDevices.length === 0) {
                    showToast(__('no_camera_found'), false);
                    return;
                }
                const firstDeviceId = videoInputDevices[1].deviceId;
                codeReader.decodeFromVideoDevice(firstDeviceId, 'barcode-video', (result, err) => {
                    if (result) {
                        searchInput.value = result.text;
                        stopBarcodeScanner();
                        barcodeScannerModal.classList.add('hidden');
                        loadProducts();
                    }
                    if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.error(err);
                    }
                });
            })
            .catch((err) => {
                console.error(err);
                showToast(__('camera_fail'), false);
            });
    }

    function stopBarcodeScanner() {
        if (codeReader) {
            codeReader.reset();
        }
    }
    // في products.php - استبدال كود زر "فحص المخزون"

    // إضافة زر يدوي للتحقق من المخزون المنخفض
    const checkStockBtn = document.createElement('button');
    checkStockBtn.innerHTML = `
        <span class="material-icons-round text-sm">inventory</span>
        <span>${__('check_low_stock_btn')}</span>
    `;
    checkStockBtn.className = 'bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 px-4 py-2 rounded-xl font-bold shadow-sm flex items-center gap-2 transition-all hover:-translate-y-0.5';
    checkStockBtn.onclick = async function() {
        const loadingScreen = document.getElementById('stock-check-loading');
        const stockModal = document.getElementById('stock-check-modal');
        const modalContent = document.getElementById('stock-modal-content');
        
        try {
            // إظهار شاشة التحميل
            loadingScreen.classList.remove('hidden');
            
            const response = await fetch('api.php?action=getLowStockProducts');
            const result = await response.json();
            
            // إخفاء شاشة التحميل
            loadingScreen.classList.add('hidden');
            
            if (result.success) {
                const totalIssues = result.outOfStockCount + result.criticalCount + result.lowCount;
                
                if (totalIssues === 0) {
                    showToast('✅ ' + __('all_products_good_stock'), true);
                } else {
                    const outOfStock = result.outOfStock || [];
                    const critical = result.critical || [];
                    const low = result.low || [];
                    
                    // بناء محتوى الـ Modal
                    let content = `
                        <div class="space-y-6">
                            <!-- إحصائيات سريعة -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-gray-500/10 border border-gray-500/30 rounded-xl p-4 text-center">
                                    <div class="text-3xl font-bold text-gray-400">${outOfStock.length}</div>
                                    <div class="text-sm text-gray-400 mt-1">${__('stock_status_out')} (0)</div>
                                </div>
                                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-center">
                                    <div class="text-3xl font-bold text-red-500">${critical.length}</div>
                                    <div class="text-sm text-red-400 mt-1">${__('stock_status_critical')} (1-5)</div>
                                </div>
                                <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-4 text-center">
                                    <div class="text-3xl font-bold text-orange-500">${low.length}</div>
                                    <div class="text-sm text-orange-400 mt-1">${__('stock_status_low')} (6-10)</div>
                                </div>
                                <div class="bg-primary/10 border border-primary/30 rounded-xl p-4 text-center">
                                    <div class="text-3xl font-bold text-primary">${totalIssues}</div>
                                    <div class="text-sm text-gray-400 mt-1">${__('total')}</div>
                                </div>
                            </div>
                    `;
                    
                    // المنتجات المنتهية (تفاصيل قابلة للطي)
                    if (outOfStock.length > 0) {
                        content += `
                            <details class="bg-gray-800/30 border border-gray-500/40 rounded-xl p-4">
                                <summary class="flex items-center justify-between cursor-pointer list-none">
                                    <div class="flex items-center gap-2">
                                        <span class="material-icons-round text-gray-400">block</span>
                                        <h4 class="text-lg font-bold text-gray-400">${__('products_out_of_stock_zero')}</h4>
                                    </div>
                                    <span class="text-sm text-gray-400">${outOfStock.length}</span>
                                </summary>
                                <div class="mt-4 space-y-2">
                        `;

                        outOfStock.forEach(product => {
                            content += `
                                <div class="bg-gray-900/30 border border-gray-500/40 rounded-lg p-4 flex justify-between items-center hover:bg-gray-900/40 transition-colors">
                                    <div>
                                        <div class="font-bold text-white flex items-center gap-2">
                                            ${product.name}
                                            <span class="text-xs bg-gray-500/20 text-gray-400 px-2 py-0.5 rounded">${__('out_of_stock')}</span>
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">${__('reorder_immediately')}</div>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-500">0</div>
                                </div>
                            `;
                        });

                        content += `
                                </div>
                            </details>
                        `;
                    }

                    // المنتجات الحرجة (تفاصيل قابلة للطي)
                    if (critical.length > 0) {
                        content += `
                            <details class="bg-red-900/10 border border-red-500/30 rounded-xl p-4 mt-4">
                                <summary class="flex items-center justify-between cursor-pointer list-none">
                                    <div class="flex items-center gap-2">
                                        <span class="material-icons-round text-red-500">error</span>
                                        <h4 class="text-lg font-bold text-red-500">${__('products_critical_stock')}</h4>
                                    </div>
                                    <span class="text-sm text-red-500">${critical.length}</span>
                                </summary>
                                <div class="mt-4 space-y-2">
                        `;

                        critical.forEach(product => {
                            content += `
                                <div class="bg-red-900/20 border border-red-500/30 rounded-lg p-4 flex justify-between items-center hover:bg-red-900/30 transition-colors">
                                    <div>
                                        <div class="font-bold text-white">${product.name}</div>
                                        <div class="text-sm text-gray-400">${__('remaining_quantity')}</div>
                                    </div>
                                    <div class="text-2xl font-bold text-red-500">${product.quantity}</div>
                                </div>
                            `;
                        });

                        content += `
                                </div>
                            </details>
                        `;
                    }

                    // المنتجات المنخفضة (تفاصيل قابلة للطي)
                    if (low.length > 0) {
                        content += `
                            <details class="bg-orange-900/10 border border-orange-500/30 rounded-xl p-4 mt-4">
                                <summary class="flex items-center justify-between cursor-pointer list-none">
                                    <div class="flex items-center gap-2">
                                        <span class="material-icons-round text-orange-500">warning</span>
                                        <h4 class="text-lg font-bold text-orange-500">${__('products_low_stock')}</h4>
                                    </div>
                                    <span class="text-sm text-orange-500">${low.length}</span>
                                </summary>
                                <div class="mt-4 space-y-2">
                        `;

                        low.forEach(product => {
                            content += `
                                <div class="bg-orange-900/20 border border-orange-500/30 rounded-lg p-4 flex justify-between items-center hover:bg-orange-900/30 transition-colors">
                                    <div>
                                        <div class="font-bold text-white">${product.name}</div>
                                        <div class="text-sm text-gray-400">${__('remaining_quantity')}</div>
                                    </div>
                                    <div class="text-2xl font-bold text-orange-500">${product.quantity}</div>
                                </div>
                            `;
                        });

                        content += `
                                </div>
                            </details>
                        `;
                    }
                    
                    content += `</div>`;
                    
                    modalContent.innerHTML = content;
                    stockModal.classList.remove('hidden');
                    
                    // حفظ البيانات للتصدير
                    window.stockReportData = result;
                }
            }
        } catch (error) {
            loadingScreen.classList.add('hidden');
            console.error('خطأ:', error);
            showToast(__('stock_check_error'), false);
        }
    };

    // إضافة الزر بجانب زر "إدارة الفئات"
    document.getElementById('manage-categories-btn').insertAdjacentElement('afterend', checkStockBtn);

});
    // Modal Controls
    const closeStockModalBtn = document.getElementById('close-stock-modal-btn');
    const closeStockModal = document.getElementById('close-stock-modal');
    const stockModal = document.getElementById('stock-check-modal');
    const exportStockReport = document.getElementById('export-stock-report');

    if (closeStockModalBtn) {
        closeStockModalBtn.addEventListener('click', () => {
            stockModal.classList.add('hidden');
        });
    }

    if (closeStockModal) {
        closeStockModal.addEventListener('click', () => {
            stockModal.classList.add('hidden');
        });
    }

    // في products.php - استبدال كود زر "تصدير التقرير"

    if (exportStockReport) {
        exportStockReport.addEventListener('click', () => {
            if (!window.stockReportData) {
                showToast(__('no_data_export'), false);
                return;
            }
            
            const currency = '<?php echo $currency; ?>';
            const now = new Date();
            const dateStr = now.toLocaleDateString('fr-FR');
            const timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            
            let txtContent = `${__('comprehensive_stock_report')}\n`;
            txtContent += `${__('date')}: ${dateStr}\n`;
            txtContent += `${__('time')}: ${timeStr}\n`;
            txtContent += `${'='.repeat(60)}\n\n`;
            
            const outOfStock = window.stockReportData.outOfStock || [];
            const critical = window.stockReportData.critical || [];
            const low = window.stockReportData.low || [];
            const totalIssues = outOfStock.length + critical.length + low.length;
            
            txtContent += `📊 ${__('status_summary')}\n`;
            txtContent += `   - ${__('out_of_stock_products')} (0): ${outOfStock.length}\n`;
            txtContent += `   - ${__('critical_stock_products')} (1-5): ${critical.length}\n`;
            txtContent += `   - ${__('low_stock_products')} (6-10): ${low.length}\n`;
            txtContent += `   - ${__('total_issues')}: ${totalIssues}\n\n`;
            
            // المنتجات المنتهية
            if (outOfStock.length > 0) {
                txtContent += `${'='.repeat(60)}\n`;
                txtContent += `⛔ ${__('products_out_of_stock_zero')}:\n`;
                txtContent += `${'-'.repeat(60)}\n`;
                txtContent += `⚠️ ${__('reorder_immediately')}!\n\n`;
                outOfStock.forEach((p, i) => {
                    txtContent += `${i + 1}. ${p.name}\n`;
                    txtContent += `   ${__('quantity')}: 0 (${__('stock_status_out')})\n`;
                    txtContent += `   ${__('actions')}: ${__('reorder_immediately')}\n\n`;
                });
            }
            
            // المنتجات الحرجة
            if (critical.length > 0) {
                txtContent += `${'='.repeat(60)}\n`;
                txtContent += `🔴 ${__('products_critical_stock')}:\n`;
                txtContent += `${'-'.repeat(60)}\n`;
                critical.forEach((p, i) => {
                    txtContent += `${i + 1}. ${p.name}\n`;
                    txtContent += `   ${__('quantity')}: ${p.quantity}\n\n`;
                });
            }
            
            // المنتجات المنخفضة
            if (low.length > 0) {
                txtContent += `${'='.repeat(60)}\n`;
                txtContent += `🟡 ${__('products_low_stock')}:\n`;
                txtContent += `${'-'.repeat(60)}\n`;
                low.forEach((p, i) => {
                    txtContent += `${i + 1}. ${p.name}\n`;
                    txtContent += `   ${__('quantity')}: ${p.quantity}\n\n`;
                });
            }
            
            txtContent += `${'='.repeat(60)}\n`;
            txtContent += `📋 ${__('recommendations')}\n`;
            txtContent += `${'-'.repeat(60)}\n`;
            if (outOfStock.length > 0) {
                txtContent += `• ${__('priority_high_reorder')} (${outOfStock.length} ${__('product')})\n`;
            }
            if (critical.length > 0) {
                txtContent += `• ${__('priority_medium_restock')} (${critical.length} ${__('product')})\n`;
            }
            if (low.length > 0) {
                txtContent += `• ${__('priority_low_monitor')} (${low.length} ${__('product')})\n`;
            }
            
            const blob = new Blob([txtContent], { type: 'text/plain;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `stock-report-${now.getTime()}.txt`;
            link.click();
            
            showToast(__('report_exported_success'), true);
        });
    }
    // إغلاق عند النقر خارج Modal
    stockModal?.addEventListener('click', (e) => {
        if (e.target === stockModal) {
            stockModal.classList.add('hidden');
        }
    });

    // Import Excel functionality
    const importExcelBtn = document.getElementById('import-excel-btn');
    const importExcelModal = document.getElementById('import-excel-modal');
    const closeImportExcelModalBtn = document.getElementById('close-import-excel-modal');
    const importExcelForm = document.getElementById('import-excel-form');
    const previewImportBtn = document.getElementById('preview-import-btn');

    importExcelBtn.addEventListener('click', () => {
        importExcelModal.classList.remove('hidden');
    });

    closeImportExcelModalBtn.addEventListener('click', () => {
        importExcelModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    importExcelModal.addEventListener('click', (e) => {
        if (e.target === importExcelModal) {
            importExcelModal.classList.add('hidden');
        }
    });

    previewImportBtn.addEventListener('click', async () => {
        const fileInput = document.getElementById('excel-file');
        if (!fileInput.files[0]) {
            showToast(__('select_excel_first'), false);
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);
        formData.append('preview', 'true');

        try {
            showLoadingOverlay(__('previewing_data'));
            const response = await fetch('api.php?action=importProducts', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                // Show preview modal or alert with data summary
                let previewText = `${__('rows_found_in_file').replace('%d', result.data.total_rows)}\n\n`;
                if (result.data.valid_products > 0) {
                    previewText += `✅ ${__('valid_products')} ${result.data.valid_products}\n`;
                }
                if (result.data.errors.length > 0) {
                    previewText += `❌ ${__('errors')} ${result.data.errors.length}\n`;
                    result.data.errors.slice(0, 5).forEach((error, i) => {
                        previewText += `  ${i + 1}. ${error}\n`;
                    });
                    if (result.data.errors.length > 5) {
                        previewText += `  ${__('and_more_errors').replace('%d', result.data.errors.length - 5)}\n`;
                    }
                }
                alert(previewText);
            } else {
                showToast(result.message || __('preview_fail'), false);
            }
        } catch (error) {
            console.error('Error previewing import:', error);
            showToast(__('preview_fail'), false);
        } finally {
            hideLoadingOverlay();
        }
    });

    document.getElementById('download-template-in-modal-btn').addEventListener('click', () => {
        window.open('generate_excel_template.php', '_blank');
    });

    importExcelForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fileInput = document.getElementById('excel-file');
        if (!fileInput.files[0]) {
            showToast(__('select_excel_first'), false);
            return;
        }

        const formData = new FormData(importExcelForm);

        try {
            showLoadingOverlay(__('importing_products'));
            const response = await fetch('api.php?action=importProducts', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                // إنشاء رسالة نجاح مخصصة مع عدد المنتجات المضافة
                let successMessage = __('products_imported_success');
                if (result.data && result.data.imported_count !== undefined) {
                    successMessage += ` - ${__('product_added_success')} (${result.data.imported_count})`;
                    if (result.data.skipped_count && result.data.skipped_count > 0) {
                        successMessage += `، ${__('duplicates_skipped').replace('%d', result.data.skipped_count)}`;
                    }
                }
                // حفظ الرسالة في sessionStorage بدلاً من عرضها مباشرة
                sessionStorage.setItem('toastMessage', successMessage);
                sessionStorage.setItem('toastType', 'success');
                importExcelModal.classList.add('hidden');
                importExcelForm.reset();
                // Reload the page to refresh all data
                window.location.reload();
            } else {
                showToast(result.message || __('import_fail'), false);
            }
        } catch (error) {
            console.error('Error importing products:', error);
            showToast(__('import_fail'), false);
        } finally {
            hideLoading();
        }
    });
</script>

<!-- Loading Overlay -->
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
