<?php
$page_title = 'لوحة التحكم';
$current_page = 'dashboard.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <!-- Background Blobs -->
    <div
        class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[100px] pointer-events-none">
    </div>
    <div
        class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent/5 rounded-full blur-[100px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <div class="flex items-center gap-4">
            <button class="md:hidden p-2 text-gray-400 hover:text-white"><span
                    class="material-icons-round">menu</span></button>
            <h2 class="text-xl font-bold text-white">لوحة التحكم</h2>
        </div>

        <div class="flex items-center gap-6">
            <a href="pos.php"
                class="bg-primary hover:bg-primary-hover text-white px-6 py-2.5 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">add</span>
                بيع جديد
            </a>
        </div>
    </header>

    <!-- Content Scrollable -->
    <div class="flex-1 overflow-y-auto p-8 relative z-10" style="max-height: calc(100vh - 5rem);">

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="text-center py-4 text-gray-500">
                لا توجد أي بيانات لعرضها الآن.
            </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Orders Table -->
            <div
                class="lg:col-span-2 bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-white">آخر العمليات</h3>
                    <a href="invoices.php" class="text-sm text-primary hover:text-white transition-colors">عرض الكل</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-right border-b border-white/5">
                                <th class="pb-4 text-sm font-medium text-gray-400">رقم الفاتورة</th>
                                <th class="pb-4 text-sm font-medium text-gray-400">العميل</th>
                                <th class="pb-4 text-sm font-medium text-gray-400">المبلغ</th>
                                <th class="pb-4 text-sm font-medium text-gray-400">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">
                                    لا توجد أي بيانات لعرضها الآن.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                <h3 class="text-lg font-bold text-white mb-6">الأكثر مبيعاً</h3>
                <div class="space-y-4">
                    <div class="text-center py-4 text-gray-500">
                        لا توجد أي بيانات لعرضها الآن.
                    </div>
                </div>
                <button
                    class="w-full mt-6 py-2.5 border border-white/10 rounded-xl text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white transition-all">
                    عرض التقرير الكامل
                </button>
            </div>
        </div>

    </div>
</main>

<?php require_once 'src/footer.php'; ?>