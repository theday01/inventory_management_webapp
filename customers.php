<?php
$page_title = 'العملاء - Smart Shop';
$current_page = 'customers.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<!-- Main Content -->
<main class="flex-1 flex flex-col relative overflow-hidden">
    <div
        class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-accent/5 rounded-full blur-[120px] pointer-events-none">
    </div>

    <!-- Header -->
    <header
        class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <h2 class="text-xl font-bold text-white">إدارة العملاء</h2>
        <div class="flex items-center gap-4">
            <button
                class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 flex items-center gap-2 transition-all hover:-translate-y-0.5">
                <span class="material-icons-round text-sm">person_add</span>
                <span>عميل جديد</span>
            </button>
        </div>
    </header>

    <!-- Search & Filter -->
    <div class="p-6 pb-0 flex gap-4 items-center relative z-10 shrink-0">
        <div class="relative flex-1 max-w-xl">
            <span class="material-icons-round absolute top-1/2 right-3 -translate-y-1/2 text-gray-400">search</span>
            <input type="text" placeholder="بحث باسم العميل أو رقم الهاتف..."
                class="w-full bg-dark/50 border border-white/10 text-white text-right pr-10 pl-4 py-3 rounded-xl focus:outline-none focus:border-primary/50 transition-all">
        </div>
    </div>

    <!-- Customers Grid -->
    <div class="flex-1 overflow-y-auto p-6 z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div class="text-center py-4 text-gray-500 col-span-full">
                No data to display at this time.
            </div>
        </div>
    </div>

</main>

<?php require_once 'src/footer.php'; ?>
