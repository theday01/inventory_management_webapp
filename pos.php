<?php
$page_title = 'نقطة البيع - Smart Shop';
$current_page = 'pos.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <!-- Products Section (Right) -->
    <div class="flex-1 flex flex-col h-full relative">
        <!-- Background Blobs -->
        <div
            class="absolute top-[10%] right-[10%] w-[400px] h-[400px] bg-primary/5 rounded-full blur-[80px] pointer-events-none">
        </div>

        <!-- Header -->
        <header
            class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-6 z-10 shrink-0">
            <div class="flex items-center gap-4 flex-1">
                <a href="dashboard.php"
                    class="p-2 text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-xl transition-colors">
                    <span class="material-icons-round">arrow_forward</span>
                </a>
                <div class="relative flex-1 max-w-md">
                    <span
                        class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                    <input type="text" placeholder="بحث عن منتج..."
                        class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    class="px-4 py-2 bg-primary text-white rounded-xl font-medium text-sm shadow-lg shadow-primary/20">الكل</button>
                <button
                    class="px-4 py-2 bg-white/5 text-gray-400 hover:text-white rounded-xl font-medium text-sm hover:bg-white/10 transition-all">إلكترونيات</button>
                <button
                    class="px-4 py-2 bg-white/5 text-gray-400 hover:text-white rounded-xl font-medium text-sm hover:bg-white/10 transition-all">ملابس</button>
                <button
                    class="px-4 py-2 bg-white/5 text-gray-400 hover:text-white rounded-xl font-medium text-sm hover:bg-white/10 transition-all">إكسسوارات</button>
            </div>
        </header>

        <!-- Products Grid -->
        <div class="flex-1 overflow-y-auto p-6 z-10">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <div class="text-center py-4 text-gray-500 col-span-full">
                    No data to display at this time.
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Sidebar (Left) -->
    <aside class="w-96 bg-dark-surface border-r border-white/5 flex flex-col z-20 shadow-2xl">
        <div class="p-6 border-b border-white/5">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xl font-bold text-white">سلة المشتريات</h2>
            </div>
            <div
                class="flex items-center gap-2 mt-4 bg-white/5 p-3 rounded-xl cursor-pointer hover:bg-white/10 transition-colors">
                <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-xs">A</div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-white">عميل نقدي</p>
                    <p class="text-xs text-gray-400">افتراضي</p>
                </div>
                <span class="material-icons-round text-gray-400">arrow_drop_down</span>
            </div>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <div class="text-center py-4 text-gray-500">
                No data to display at this time.
            </div>
        </div>

        <!-- Totals & Checkout -->
        <div class="p-6 bg-dark-surface border-t border-white/5">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm text-gray-400">
                    <span>المجموع الفرعي</span>
                    <span>0 ر.س</span>
                </div>
                <div class="flex justify-between text-sm text-gray-400">
                    <span>الضريبة (15%)</span>
                    <span>0 ر.س</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-white pt-2 border-t border-white/5">
                    <span>الإجمالي</span>
                    <span class="text-primary">0 ر.س</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <button
                    class="button-secondary bg-white/5 hover:bg-white/10 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">pause</span>
                    تعليق
                </button>
                <button
                    class="button-danger bg-red-500/10 hover:bg-red-500/20 text-red-500 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition-all">
                    <span class="material-icons-round text-sm">delete_outline</span>
                    إلغاء
                </button>
            </div>

            <button
                class="w-full bg-accent hover:bg-lime-500 text-dark-surface py-4 rounded-xl font-bold text-lg shadow-lg shadow-accent/20 flex items-center justify-center gap-2 transition-all hover:scale-[1.02]">
                <span class="material-icons-round">payments</span>
                دفع (space)
            </button>
        </div>
    </aside>
</main>

<?php require_once 'src/footer.php'; ?>
