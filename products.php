<?php
$page_title = 'المنتجات';
$current_page = 'products.php';
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

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div
        class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">إدارة المخزون</h2>

        <div class="flex items-center gap-4">
            <button id="add-product-btn"
                class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">add</span>
                <span>منتج جديد</span>
            </button>
            <button id="manage-categories-btn"
                class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-xl font-bold shadow-lg flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">category</span>
                <span>إدارة الفئات</span>
            </button>
            <button id="export-csv-btn"
                class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-xl font-bold shadow-lg flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">download</span>
                <span>تصدير CSV</span>
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
                <button id="print-labels-btn" class="text-white hover:bg-white/10 p-2 rounded-lg transition-colors" title="طباعة ملصقات الباركود"><span class="material-icons-round">print</span></button>
                <button id="bulk-edit-btn" class="text-white hover:bg-white/10 p-2 rounded-lg transition-colors" title="تعديل جماعي"><span class="material-icons-round">edit</span></button>
                <button id="bulk-delete-btn" class="text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition-colors" title="حذف جماعي"><span class="material-icons-round">delete</span></button>
            </div>
        </div>
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-4 w-full flex-1 max-w-4xl">
                <div class="relative w-full md:w-96">
                    <span
                    class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                <input type="text" id="product-search-input" placeholder="بحث عن اسم المنتج، الباركود..."
                    class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                <button id="scan-barcode-btn" class="absolute top-1/2 left-3 -translate-y-1/2 text-gray-400 hover:text-white">
                    <span class="material-icons-round">qr_code_scanner</span>
                </button>
            </div>

            <div class="relative min-w-[200px]">
                <select id="product-category-filter"
                    class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                    <option value="">جميع الفئات</option>
                </select>
                <span
                    class="material-icons-round absolute top-1/2 left-2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
            <div class="relative min-w-[200px]">
                <select id="stock-status-filter"
                    class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                    <option value="">كل المخزون</option>
                    <option value="out_of_stock">منتهي</option>
                    <option value="low_stock">منخفض</option>
                    <option value="critical_stock">حرج</option>
                </select>
                <span
                    class="material-icons-round absolute top-1/2 left-2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
        </div>
    </div>
    <!-- Products Table -->
    <div class="flex-1 overflow-auto p-6 relative z-10">
        <div
            class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 text-right">
                        <th class="p-4 w-10"><input type="checkbox" id="select-all-products" class="bg-dark/50 border-white/20 rounded"></th>
                        <th class="p-4 text-sm font-medium text-gray-300 cursor-pointer sortable-header" data-sort="name">المنتج <span class="sort-icon opacity-30">▲</span></th>
                        <th class="p-4 text-sm font-medium text-gray-300">الصورة</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الفئة</th>
                        <th class="p-4 text-sm font-medium text-gray-300 cursor-pointer sortable-header" data-sort="price">السعر <span class="sort-icon opacity-30">▲</span></th>
                        <th class="p-4 text-sm font-medium text-gray-300 cursor-pointer sortable-header" data-sort="quantity">الكمية <span class="sort-icon opacity-30">▲</span></th>
                        <th class="p-4 text-sm font-medium text-gray-300">تفاصيل</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="divide-y divide-white/5">
                    <!-- Products will be loaded here -->
                </tbody>
            </table>
            <!-- Pagination and info -->
            <div id="pagination-container" class="p-4 bg-dark-surface/60 border-t border-white/5 flex items-center justify-between text-sm text-gray-400">
            </div>
        </div>
    </div>

    <!-- Loading Screen -->
    <div id="stock-check-loading" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[60] hidden flex items-center justify-center">
        <div class="bg-dark-surface rounded-2xl p-8 flex flex-col items-center gap-4 border border-white/10">
            <div class="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p class="text-white font-bold text-lg">جاري فحص المخزون...</p>
        </div>
    </div>

    <!-- Stock Check Modal -->
    <div id="stock-check-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-dark-surface rounded-2xl shadow-2xl w-full max-w-3xl border border-white/10 max-h-[90vh] flex flex-col">
            <div class="p-6 border-b border-white/5 flex justify-between items-center shrink-0">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="material-icons-round text-yellow-500">inventory</span>
                    تقرير المخزون المنخفض
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
                    تصدير التقرير
                </button>
                <button id="close-stock-modal-btn" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded-xl font-bold transition-all">
                    إغلاق
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
                        <h3 class="text-xl font-bold text-white">تم الحذف بنجاح</h3>
                        <p class="text-sm text-gray-400" id="delete-summary">تم حذف المنتجات المحددة</p>
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
                                <p class="text-sm text-gray-400 mb-1">إجمالي المحذوفة</p>
                                <p class="text-3xl font-bold text-green-500" id="total-deleted">0</p>
                            </div>
                            <span class="material-icons-round text-green-500 text-4xl opacity-20">inventory_2</span>
                        </div>
                    </div>
                    
                    <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400 mb-1">مرتبطة بفواتير</p>
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
                            <h4 class="text-blue-500 font-bold mb-1">ملاحظة مهمة</h4>
                            <p class="text-sm text-gray-300 leading-relaxed" id="linked-note-text"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-6 border-t border-white/5 flex justify-end shrink-0">
                <button id="close-delete-modal-btn" class="bg-primary hover:bg-primary-hover text-white px-6 py-2.5 rounded-xl font-bold transition-all hover:-translate-y-0.5 shadow-lg shadow-primary/20">
                    فهمت، شكراً
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
    </style>
</main>

<!-- Bulk Edit Modal -->
<div id="bulk-edit-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">تعديل جماعي للمنتجات</h3>
            <button id="close-bulk-edit-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="bulk-edit-form">
            <div class="p-6">
                <p class="text-gray-300 mb-4">اترك الحقول فارغة لعدم تغييرها.</p>
                <div class="space-y-4">
                    <div>
                        <label for="bulk-edit-category" class="block text-sm font-medium text-gray-300 mb-2">الفئة</label>
                        <select id="bulk-edit-category" name="category_id" class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                            <option value="">-- عدم التغيير --</option>
                            <!-- Categories will be loaded here -->
                        </select>
                    </div>
                    <div>
                        <label for="bulk-edit-price" class="block text-sm font-medium text-gray-300 mb-2">السعر</label>
                        <input type="number" id="bulk-edit-price" name="price" step="0.01" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" placeholder="اترك فارغاً لعدم التغيير">
                    </div>
                    <div>
                        <label for="bulk-edit-quantity" class="block text-sm font-medium text-gray-300 mb-2">الكمية</label>
                        <input type="number" id="bulk-edit-quantity" name="quantity" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" placeholder="اترك فارغاً لعدم التغيير">
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold">تطبيق التغييرات</button>
            </div>
        </form>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-lg border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 id="product-modal-title" class="text-lg font-bold text-white">إضافة منتج جديد</h3>
            <button id="close-product-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <form id="product-form" enctype="multipart/form-data">
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <input type="hidden" id="product-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="product-name" class="block text-sm font-medium text-gray-300 mb-2">اسم المنتج</label>
                        <input type="text" id="product-name" name="name" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4">
                        <label for="product-category" class="block text-sm font-medium text-gray-300 mb-2">الفئة</label>
                        <select id="product-category" name="category_id" class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                            <option value="">اختر فئة</option>
                            <!-- Categories will be loaded here -->
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="product-price" class="block text-sm font-medium text-gray-300 mb-2">السعر</label>
                        <input type="number" id="product-price" name="price" step="0.01" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4">
                        <label for="product-quantity" class="block text-sm font-medium text-gray-300 mb-2">الكمية</label>
                        <input type="number" id="product-quantity" name="quantity" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50" required>
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="product-barcode" class="block text-sm font-medium text-gray-300 mb-2">الباركود</label>
                        <input type="text" id="product-barcode" name="barcode" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                    <div class="mb-4 col-span-2">
                        <label for="product-image" class="block text-sm font-medium text-gray-300 mb-2">صورة المنتج</label>
                        <input type="file" id="product-image" name="image" class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50">
                    </div>
                </div>

                <div id="custom-fields-container" class="my-4 space-y-4">
                    <!-- Custom fields will be loaded here -->
                </div>
            </div>
            <div class="p-6 border-t border-white/5 flex justify-end gap-4">
                <button type="submit" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ المنتج</button>
            </div>
        </form>
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

<!-- Product Details Modal -->
<div id="product-details-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-md border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">تفاصيل المنتج</h3>
            <button id="close-product-details-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div id="product-details-content" class="p-6 max-h-[70vh] overflow-y-auto">
            <!-- Details will be loaded here -->
        </div>
    </div>
</div>

<!-- Category Management Modal -->
<div id="category-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-lg w-full max-w-4xl border border-white/10 m-4">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <h3 class="text-lg font-bold text-white">إدارة الفئات</h3>
            <button id="close-category-modal" class="text-gray-400 hover:text-white transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>
        <div class="p-6 max-h-[75vh] overflow-y-auto">
            <form id="category-form">
                <input type="hidden" id="category-id" name="id">
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="category-name" class="block text-sm font-medium text-gray-300 mb-2">اسم الفئة *</label>
                        <input type="text" id="category-name" name="name"
                            class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                            required>
                    </div>
                    <div>
                        <label for="category-description" class="block text-sm font-medium text-gray-300 mb-2">الوصف</label>
                        <textarea id="category-description" name="description" rows="2"
                            class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                            placeholder="وصف مختصر للفئة..."></textarea>
                    </div>
                    <div>
                        <label for="category-fields" class="block text-sm font-medium text-gray-300 mb-2">حقول مخصصة (مفصولة بفاصلة)</label>
                        <textarea id="category-fields" name="fields" rows="3"
                            class="w-full bg-dark/50 border border-white/10 text-white pr-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50"
                            placeholder="مثال: الحجم, اللون, المادة, الوزن"></textarea>
                        <p class="text-xs text-gray-500 mt-1">افصل بين الحقول بالفاصلة (,) أو الفاصلة العربية (،)</p>
                    </div>
                </div>
                <div class="flex justify-end gap-4">
                    <button type="button" id="cancel-category-edit" class="text-gray-400 hover:text-white px-4 py-2 rounded-xl transition-colors hidden">إلغاء التعديل</button>
                    <button type="submit"
                        class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all">حفظ الفئة</button>
                </div>
            </form>
            <hr class="border-white/10 my-6">
            <div>
                <h4 class="text-md font-bold text-white mb-4">الفئات الحالية (<?php echo "30"; ?> فئة)</h4>
                <div id="category-list" class="space-y-2 max-h-96 overflow-y-auto">
                    <!-- Categories will be loaded here via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest/umd/index.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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
    const productsPerPage = 10;

    loadProducts();
    loadCategoriesIntoFilter();
    loadStats();
    searchInput.addEventListener('input', () => { currentPage = 1; loadProducts(); });
    categoryFilter.addEventListener('change', () => { currentPage = 1; loadProducts(); });
    stockStatusFilter.addEventListener('change', () => { currentPage = 1; loadProducts(); });

    async function loadProducts() {
        const searchQuery = searchInput.value;
        const categoryId = categoryFilter.value;
        const stockStatus = stockStatusFilter.value;

        try {
            showLoading('جاري تحميل المنتجات...');
            const response = await fetch(`api.php?action=getProducts&search=${searchQuery}&category_id=${categoryId}&stock_status=${stockStatus}&page=${currentPage}&limit=${productsPerPage}&sortBy=${sortBy}&sortOrder=${sortOrder}`);
            const result = await response.json();
            if (result.success) {
                const lowAlert = <?php echo $low_alert; ?>;
                const criticalAlert = <?php echo $critical_alert; ?>;
                displayProducts(result.data, lowAlert, criticalAlert);
                renderPagination(result.total_products);
            }
        } catch (error) {
            console.error('خطأ في تحميل المنتجات:', error);
            showToast('حدث خطأ في تحميل المنتجات', false);
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
                            <p class="text-gray-400 text-sm">إجمالي المنتجات</p>
                            <p class="text-white text-xl font-bold">${stats.total_products}</p>
                        </div>
                    </div>
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                        <div class="bg-green-500/10 p-3 rounded-xl"><span class="material-icons-round text-green-500 text-2xl">attach_money</span></div>
                        <div>
                            <p class="text-gray-400 text-sm">قيمة المخزون</p>
                            <p class="text-white text-xl font-bold">${parseFloat(stats.total_stock_value).toFixed(2)}</p>
                        </div>
                    </div>
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                        <div class="bg-red-500/10 p-3 rounded-xl"><span class="material-icons-round text-red-500 text-2xl">highlight_off</span></div>
                        <div>
                            <p class="text-gray-400 text-sm">نفذ من المخزون</p>
                            <p class="text-white text-xl font-bold">${stats.out_of_stock}</p>
                        </div>
                    </div>
                    <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-4 flex items-center gap-4">
                        <div class="bg-yellow-500/10 p-3 rounded-xl"><span class="material-icons-round text-yellow-500 text-2xl">warning</span></div>
                        <div>
                            <p class="text-gray-400 text-sm">مخزون منخفض</p>
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
            productsTableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-gray-500">لا توجد منتجات لعرضها.</td></tr>';
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
                quantityBadge = '<span class="inline-flex items-center gap-1 text-xs bg-gray-500/20 text-gray-400 px-2 py-1 rounded-full ml-2"><span class="material-icons-round text-xs">block</span>منتهي</span>';
            } else if (qty <= criticalAlert) {
                rowClass += ' bg-red-900/20 hover:bg-red-900/30'; 
                quantityClass = 'text-red-400 font-bold';
                quantityBadge = `<span class="inline-flex items-center gap-1 text-xs bg-red-500/20 text-red-400 px-2 py-1 rounded-full ml-2"><span class="material-icons-round text-xs">error</span>حرج (${qty}/${criticalAlert})</span>`;
            } else if (qty <= lowAlert) {
                rowClass += ' bg-orange-900/20 hover:bg-orange-900/30';
                quantityClass = 'text-orange-400 font-bold';
                quantityBadge = `<span class="inline-flex items-center gap-1 text-xs bg-orange-500/20 text-orange-400 px-2 py-1 rounded-full ml-2"><span class="material-icons-round text-xs">warning</span>منخفض (${qty}/${lowAlert})</span>`;
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
                <td class="p-4 text-sm text-gray-300">${product.category_name || 'غير مصنّف'}</td>
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

    paginationContainer.addEventListener('click', e => {
        if (e.target.closest('.pagination-btn')) {
            const btn = e.target.closest('.pagination-btn');
            currentPage = parseInt(btn.dataset.page);
            loadProducts();
        }
    });

    const exportCsvBtn = document.getElementById('export-csv-btn');
    exportCsvBtn.addEventListener('click', async () => {
        try {
            showLoading('جاري تصدير البيانات...');
            const response = await fetch('api.php?action=getProducts&limit=9999');
            const result = await response.json();
            if (result.success) {
                const products = result.data;
                let csvContent = "data:text/csv;charset=utf-8,\uFEFF";
                csvContent += "ID,Name,Category,Price,Quantity,Barcode\n";
                products.forEach(p => {
                    csvContent += `${p.id},"${p.name}","${p.category_name || ''}",${p.price},${p.quantity},"${p.barcode || ''}"\n`;
                });
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "products.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                showToast('تم تصدير المنتجات بنجاح', true);
            } else {
                showToast('فشل في تصدير المنتجات', false);
            }
        } catch (error) {
            console.error('خطأ في تصدير CSV:', error);
            showToast('حدث خطأ في تصدير CSV', false);
        } finally {
            hideLoading();
        }
    });

    const printLabelsBtn = document.getElementById('print-labels-btn');
    printLabelsBtn.addEventListener('click', async () => {
        const selectedIds = getSelectedProductIds();
        if (selectedIds.length === 0) {
            showToast('الرجاء تحديد منتجات لطباعة ملصقاتها', false);
            return;
        }

        try {
            showLoading('جاري تحضير الملصقات...');
            const response = await fetch(`api.php?action=getProducts&ids=${selectedIds.join(',')}`);
            const result = await response.json();
            if (result.success) {
                const products = result.data;
                const printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>طباعة الملصقات</title>');
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
                showToast('فشل في تحضير الملصقات', false);
            }
        } catch (error) {
            console.error('خطأ في طباعة الملصقات:', error);
            showToast('حدث خطأ في طباعة الملصقات', false);
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
            selectedCount.textContent = `${selectedIds.length} منتجات محددة`;
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
        bulkEditCategorySelect.innerHTML = '<option value="">-- عدم التغيير --</option>';
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
            showLoading('جاري تحديث المنتجات...');
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
                showToast(result.message || 'تم تحديث المنتجات بنجاح', true);
            } else {
                showToast(result.message || 'فشل في تحديث المنتجات', false);
            }
        } catch (error) {
            console.error('خطأ في التحديث الجماعي:', error);
            showToast('حدث خطأ في التحديث الجماعي', false);
        } finally {
            hideLoading();
        }
    });

    bulkDeleteBtn.addEventListener('click', async () => {
        const selectedIds = getSelectedProductIds();
        
        if (selectedIds.length === 0) {
            showToast('الرجاء تحديد منتجات للحذف', false);
            return;
        }
        
        const confirmed = await showConfirmModal(
            'حذف جماعي',
            `هل أنت متأكد من حذف ${selectedIds.length} منتج؟\n\n⚠️ تنبيه: إذا كانت هذه المنتجات مرتبطة بفواتير، سيتم حذفها من قائمة المنتجات ولكن الفواتير القديمة ستحتفظ بمعلوماتها.`
        );
        
        if (confirmed) {
            try {
                showLoading('جاري حذف المنتجات...');
                
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
                    showToast(result.message || 'فشل في حذف المنتجات', false);
                }
            } catch (error) {
                console.error('خطأ في الحذف الجماعي:', error);
                showToast('حدث خطأ: ' + error.message, false);
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
        deleteSummary.textContent = `تم حذف ${result.deleted_count} منتج من قاعدة البيانات`;
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
                            <span>مرتبط بـ ${productInfo.match(/\((\d+)/)?.[1] || '0'} فاتورة</span>
                        </p>
                    </div>
                    <span class="text-xs bg-orange-500/20 text-orange-400 px-2 py-1 rounded font-bold">محذوف</span>
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
                <p class="text-white font-bold mb-1">تم الحذف بنجاح</p>
                <p class="text-sm text-gray-400">جميع المنتجات المحددة لم تكن مرتبطة بأي فواتير</p>
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
    });

    closeProductDetailsModalBtn.addEventListener('click', () => {
        productDetailsModal.classList.add('hidden');
    });

    async function getProductDetails(id) {
        try {
            const response = await fetch(`api.php?action=getProductDetails&id=${id}`);
            const result = await response.json();
            if (!result.success) {
                showToast(result.message || 'فشل في تحميل تفاصيل المنتج', false);
            }
            return result.success ? result.data : null;
        } catch (error) {
            console.error('خطأ في تحميل تفاصيل المنتج:', error);
            showToast('حدث خطأ في تحميل تفاصيل المنتج', false);
            return null;
        }
    }

    function displayProductDetails(product) {
        let fieldsHtml = product.custom_fields.map(field => `
            <div class="flex justify-between py-1">
                <span class="font-medium text-gray-400">${field.field_name}:</span>
                <span class="text-white">${field.value}</span>
            </div>
        `).join('');

        productDetailsContent.innerHTML = `
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="font-medium text-gray-400">الاسم:</span><span class="text-white">${product.name}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">الفئة:</span><span class="text-white">${product.category_name}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">السعر:</span><span class="text-white">${product.price}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">الكمية:</span><span class="text-white">${product.quantity}</span></div>
                <div class="flex justify-between"><span class="font-medium text-gray-400">الباركود:</span><span class="text-white">${product.barcode || 'N/A'}</span></div>
                ${fieldsHtml ? '<hr class="border-white/10 my-3"><h4 class="text-md font-bold text-white pt-2 mb-2">حقول مخصصة</h4>' + fieldsHtml : '<hr class="border-white/10 my-3"><p class="text-gray-500">لا توجد حقول مخصصة.</p>'}
            </div>
        `;
    }

    addProductBtn.addEventListener('click', async () => {
        await loadCategoriesIntoSelect();
        productModal.classList.remove('hidden');
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
            title.textContent = 'حقول مخصصة للفئة (اختيارية)';
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

        try {
            showLoading('جاري حفظ المنتج...');
            const response = await fetch('api.php?action=addProduct', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();
            if (result.success) {
                productModal.classList.add('hidden');
                productForm.reset();
                customFieldsContainer.innerHTML = '';
                loadProducts();
                showToast(result.message || 'تم إضافة المنتج بنجاح', true);
            } else {
                showToast(result.message || 'فشل في إضافة المنتج', false);
            }
        } catch (error) {
            console.error('خطأ في إضافة المنتج:', error);
            showToast('حدث خطأ في إضافة المنتج', false);
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
            showToast('حدث خطأ في تحميل الفئات', false);
        }
        return [];
    }

    async function loadCategoriesIntoSelect() {
        const categories = await loadCategories();
        productCategorySelect.innerHTML = '<option value="">اختر فئة</option>';
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
        categoryFilter.innerHTML = '<option value="">جميع الفئات</option>';
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
            categoryList.innerHTML = '<p class="text-gray-500">لا توجد فئات حالياً.</p>';
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
                        ${fieldsCount > 0 ? `<span class="text-xs text-gray-500 bg-white/5 px-2 py-0.5 rounded">${fieldsCount} حقل</span>` : ''}
                    </div>
                    ${category.description ? `<p class="text-xs text-gray-400 mb-2">${category.description}</p>` : ''}
                    ${fieldsCount > 0 ? `
                        <p class="text-xs text-gray-500">
                            <strong>الحقول:</strong> ${fieldsPreview}${hasMoreFields ? '...' : ''}
                        </p>
                    ` : '<p class="text-xs text-gray-500">لا توجد حقول مخصصة</p>'}
                </div>
                <div class="flex gap-2 mr-4">
                    <button class="edit-category-btn p-2 text-gray-400 hover:text-primary transition-colors" 
                        data-id="${category.id}" 
                        data-name="${category.name}" 
                        data-description="${category.description || ''}"
                        data-fields="${category.fields || ''}"
                        title="تعديل">
                        <span class="material-icons-round text-lg">edit</span>
                    </button>
                    <button class="delete-category-btn p-2 text-gray-400 hover:text-red-500 transition-colors" 
                        data-id="${category.id}"
                        title="حذف">
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
            showLoading('جاري حفظ الفئة...');
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
                showToast(result.message || 'تم حفظ الفئة بنجاح', true);
            } else {
                showToast(result.message || 'فشل في حفظ الفئة', false);
            }
        } catch (error) {
            console.error('خطأ في حفظ الفئة:', error);
            showToast('حدث خطأ في حفظ الفئة', false);
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
                'حذف الفئة',
                'هل أنت متأكد من حذف هذه الفئة؟ سيتم حذف جميع الحقول المخصصة المرتبطة بها.'
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
            showLoading('جاري حذف الفئة...');
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
                showToast(result.message || 'تم حذف الفئة بنجاح', true);
            } else {
                showToast(result.message || 'فشل في حذف الفئة', false);
            }
        } catch (error) {
            console.error('خطأ في حذف الفئة:', error);
            showToast('حدث خطأ في حذف الفئة', false);
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
                    showToast('لم يتم العثور على كاميرا', false);
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
                showToast('فشل في تشغيل الكاميرا', false);
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
        <span>فحص المخزون</span>
    `;
    checkStockBtn.className = 'bg-yellow-600 hover:bg-yellow-500 text-white px-4 py-2 rounded-xl font-bold shadow-lg flex items-center gap-2 transition-all hover:-translate-y-0.5';
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
                    showToast('✅ جميع المنتجات بكميات جيدة', true);
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
                                    <div class="text-sm text-gray-400 mt-1">منتهي (0)</div>
                                </div>
                                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-center">
                                    <div class="text-3xl font-bold text-red-500">${critical.length}</div>
                                    <div class="text-sm text-red-400 mt-1">حرج (1-5)</div>
                                </div>
                                <div class="bg-orange-500/10 border border-orange-500/30 rounded-xl p-4 text-center">
                                    <div class="text-3xl font-bold text-orange-500">${low.length}</div>
                                    <div class="text-sm text-orange-400 mt-1">منخفض (6-10)</div>
                                </div>
                                <div class="bg-primary/10 border border-primary/30 rounded-xl p-4 text-center">
                                    <div class="text-3xl font-bold text-primary">${totalIssues}</div>
                                    <div class="text-sm text-gray-400 mt-1">الإجمالي</div>
                                </div>
                            </div>
                    `;
                    
                    // المنتجات المنتهية
                    if (outOfStock.length > 0) {
                        content += `
                            <div>
                                <h4 class="text-lg font-bold text-gray-400 mb-3 flex items-center gap-2">
                                    <span class="material-icons-round">block</span>
                                    منتجات منتهية تماماً (كمية = 0)
                                </h4>
                                <div class="space-y-2">
                        `;
                        
                        outOfStock.forEach(product => {
                            content += `
                                <div class="bg-gray-900/30 border border-gray-500/40 rounded-lg p-4 flex justify-between items-center hover:bg-gray-900/40 transition-colors">
                                    <div>
                                        <div class="font-bold text-white flex items-center gap-2">
                                            ${product.name}
                                            <span class="text-xs bg-gray-500/20 text-gray-400 px-2 py-0.5 rounded">نفذت الكمية</span>
                                        </div>
                                        <div class="text-sm text-gray-500 mt-1">يجب طلب مخزون جديد فوراً</div>
                                    </div>
                                    <div class="text-2xl font-bold text-gray-500">0</div>
                                </div>
                            `;
                        });
                        
                        content += `
                                </div>
                            </div>
                        `;
                    }
                    
                    // المنتجات الحرجة
                    if (critical.length > 0) {
                        content += `
                            <div>
                                <h4 class="text-lg font-bold text-red-500 mb-3 flex items-center gap-2">
                                    <span class="material-icons-round">error</span>
                                    منتجات حرجة (كمية 1-5)
                                </h4>
                                <div class="space-y-2">
                        `;
                        
                        critical.forEach(product => {
                            content += `
                                <div class="bg-red-900/20 border border-red-500/30 rounded-lg p-4 flex justify-between items-center hover:bg-red-900/30 transition-colors">
                                    <div>
                                        <div class="font-bold text-white">${product.name}</div>
                                        <div class="text-sm text-gray-400">الكمية المتبقية</div>
                                    </div>
                                    <div class="text-2xl font-bold text-red-500">${product.quantity}</div>
                                </div>
                            `;
                        });
                        
                        content += `
                                </div>
                            </div>
                        `;
                    }
                    
                    // المنتجات المنخفضة
                    if (low.length > 0) {
                        content += `
                            <div>
                                <h4 class="text-lg font-bold text-orange-500 mb-3 flex items-center gap-2">
                                    <span class="material-icons-round">warning</span>
                                    منتجات منخفضة (كمية 6-10)
                                </h4>
                                <div class="space-y-2">
                        `;
                        
                        low.forEach(product => {
                            content += `
                                <div class="bg-orange-900/20 border border-orange-500/30 rounded-lg p-4 flex justify-between items-center hover:bg-orange-900/30 transition-colors">
                                    <div>
                                        <div class="font-bold text-white">${product.name}</div>
                                        <div class="text-sm text-gray-400">الكمية المتبقية</div>
                                    </div>
                                    <div class="text-2xl font-bold text-orange-500">${product.quantity}</div>
                                </div>
                            `;
                        });
                        
                        content += `
                                </div>
                            </div>
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
            showToast('حدث خطأ في فحص المخزون', false);
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
                showToast('لا توجد بيانات للتصدير', false);
                return;
            }
            
            const currency = '<?php echo $currency; ?>';
            const now = new Date();
            const dateStr = now.toLocaleDateString('fr-FR');
            const timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
            
            let txtContent = `تقرير المخزون الشامل\n`;
            txtContent += `التاريخ: ${dateStr}\n`;
            txtContent += `الوقت: ${timeStr}\n`;
            txtContent += `${'='.repeat(60)}\n\n`;
            
            const outOfStock = window.stockReportData.outOfStock || [];
            const critical = window.stockReportData.critical || [];
            const low = window.stockReportData.low || [];
            const totalIssues = outOfStock.length + critical.length + low.length;
            
            txtContent += `📊 ملخص الحالة:\n`;
            txtContent += `   - منتجات منتهية (0): ${outOfStock.length}\n`;
            txtContent += `   - منتجات حرجة (1-5): ${critical.length}\n`;
            txtContent += `   - منتجات منخفضة (6-10): ${low.length}\n`;
            txtContent += `   - إجمالي المشاكل: ${totalIssues}\n\n`;
            
            // المنتجات المنتهية
            if (outOfStock.length > 0) {
                txtContent += `${'='.repeat(60)}\n`;
                txtContent += `⛔ منتجات منتهية تماماً (كمية = 0):\n`;
                txtContent += `${'-'.repeat(60)}\n`;
                txtContent += `⚠️ هذه المنتجات نفذت بالكامل وتحتاج طلب مخزون فوري!\n\n`;
                outOfStock.forEach((p, i) => {
                    txtContent += `${i + 1}. ${p.name}\n`;
                    txtContent += `   الكمية: 0 (نفذت)\n`;
                    txtContent += `   الحالة: يجب الطلب فوراً\n\n`;
                });
            }
            
            // المنتجات الحرجة
            if (critical.length > 0) {
                txtContent += `${'='.repeat(60)}\n`;
                txtContent += `🔴 منتجات حرجة (كمية 1-5):\n`;
                txtContent += `${'-'.repeat(60)}\n`;
                critical.forEach((p, i) => {
                    txtContent += `${i + 1}. ${p.name}\n`;
                    txtContent += `   الكمية: ${p.quantity}\n\n`;
                });
            }
            
            // المنتجات المنخفضة
            if (low.length > 0) {
                txtContent += `${'='.repeat(60)}\n`;
                txtContent += `🟡 منتجات منخفضة (كمية 6-10):\n`;
                txtContent += `${'-'.repeat(60)}\n`;
                low.forEach((p, i) => {
                    txtContent += `${i + 1}. ${p.name}\n`;
                    txtContent += `   الكمية: ${p.quantity}\n\n`;
                });
            }
            
            txtContent += `${'='.repeat(60)}\n`;
            txtContent += `📋 توصيات:\n`;
            txtContent += `${'-'.repeat(60)}\n`;
            if (outOfStock.length > 0) {
                txtContent += `• أولوية قصوى: طلب المنتجات المنتهية (${outOfStock.length} منتج)\n`;
            }
            if (critical.length > 0) {
                txtContent += `• أولوية عالية: إعادة تخزين المنتجات الحرجة (${critical.length} منتج)\n`;
            }
            if (low.length > 0) {
                txtContent += `• أولوية متوسطة: مراقبة المنتجات المنخفضة (${low.length} منتج)\n`;
            }
            
            const blob = new Blob([txtContent], { type: 'text/plain;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `stock-report-${now.getTime()}.txt`;
            link.click();
            
            showToast('تم تصدير التقرير بنجاح', true);
        });
    }
    // إغلاق عند النقر خارج Modal
    stockModal?.addEventListener('click', (e) => {
        if (e.target === stockModal) {
            stockModal.classList.add('hidden');
        }
    });
</script>
<?php require_once 'src/footer.php'; ?>