<?php
$page_title = 'المنتجات - Smart Shop';
$current_page = 'products.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div
        class="absolute top-0 right-[-10%] w-[500px] h-[500px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">إدارة المنتجات</h2>

        <div class="flex items-center gap-4">
            <button
                class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">add</span>
                <span>منتج جديد</span>
            </button>
        </div>
    </header>

    <!-- Filters & Actions -->
    <div class="p-6 pb-0 flex flex-col md:flex-row gap-4 items-center justify-between relative z-10 shrink-0">
        <div class="flex items-center gap-4 w-full md:w-auto flex-1 max-w-2xl">
            <div class="relative flex-1">
                <span
                    class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
                <input type="text" placeholder="بحث عن اسم المنتج، الباركود..."
                    class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
            </div>

            <div class="relative min-w-[140px]">
                <select
                    class="w-full appearance-none bg-dark/50 border border-white/10 text-white text-right pr-4 pl-8 py-2.5 rounded-xl focus:outline-none focus:border-primary/50 cursor-pointer">
                    <option>جميع الفئات</option>
                    <option>إلكترونيات</option>
                    <option>ملابس</option>
                </select>
                <span
                    class="material-icons-round absolute top-1/2 left-2 -translate-y-1/2 text-gray-400 pointer-events-none">expand_more</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button class="p-2.5 bg-white/5 rounded-xl text-white hover:bg-white/10 transition-colors tooltip"
                title="عرض كجدول">
                <span class="material-icons-round">table_chart</span>
            </button>
            <button
                class="p-2.5 text-gray-400 hover:text-white hover:bg-white/5 rounded-xl transition-colors tooltip"
                title="عرض كبطاقات">
                <span class="material-icons-round">grid_view</span>
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="flex-1 overflow-auto p-6 relative z-10">
        <div
            class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="bg-white/5 border-b border-white/5 text-right">
                        <th class="p-4 text-sm font-medium text-gray-300 w-16">
                            <input type="checkbox"
                                class="rounded border-gray-600 bg-dark text-primary focus:ring-primary">
                        </th>
                        <th class="p-4 text-sm font-medium text-gray-300">المنتج</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الفئة</th>
                        <th class="p-4 text-sm font-medium text-gray-300">السعر</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الكمية</th>
                        <th class="p-4 text-sm font-medium text-gray-300">الحالة</th>
                        <th class="p-4 text-sm font-medium text-gray-300 w-20"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr>
                        <td colspan="7" class="text-center py-4 text-gray-500">
                            No data to display at this time.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php require_once 'src/footer.php'; ?>
