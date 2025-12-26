<?php require_once 'session.php'; ?>
<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - Smart Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            DEFAULT: '#0E1116',
                            surface: '#1F2937',
                            glass: 'rgba(14, 17, 22, 0.7)',
                        },
                        primary: {
                            DEFAULT: '#3B82F6',
                            hover: '#2563EB',
                        },
                        accent: {
                            DEFAULT: '#84CC16',
                        }
                    },
                    fontFamily: {
                        sans: ['Tajawal', 'sans-serif'],
                    },
                },
            },
        }
    </script>
    <style>
        .glass-panel {
            background-color: rgba(31, 41, 55, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body class="bg-dark text-white font-sans min-h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-dark-surface/80 backdrop-blur-xl border-l border-white/5 flex flex-col hidden md:flex z-50">
        <div class="h-20 flex items-center justify-center border-b border-white/5">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent">Smart
                Shop</h1>
        </div>

        <nav class="flex-1 overflow-y-auto py-6 space-y-2 px-4">
            <a href="dashboard.php"
                class="flex items-center gap-3 px-4 py-3 bg-primary/10 text-primary rounded-xl transition-all">
                <span class="material-icons-round">dashboard</span>
                <span class="font-medium">لوحة التحكم</span>
            </a>
            <a href="pos.php"
                class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all">
                <span class="material-icons-round">point_of_sale</span>
                <span class="font-medium">نقطة البيع</span>
            </a>
            <a href="products.php"
                class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all">
                <span class="material-icons-round">inventory_2</span>
                <span class="font-medium">المنتجات</span>
            </a>
            <a href="customers.php"
                class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all">
                <span class="material-icons-round">people</span>
                <span class="font-medium">العملاء</span>
            </a>
            <a href="invoices.php"
                class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all">
                <span class="material-icons-round">receipt_long</span>
                <span class="font-medium">الفواتير</span>
            </a>
            <div class="pt-4 mt-4 border-t border-white/5">
                <a href="settings.php"
                    class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all">
                    <span class="material-icons-round">settings</span>
                    <span class="font-medium">الإعدادات</span>
                </a>
            </div>
        </nav>

        <div class="p-4 border-t border-white/5">
            <a href="logout.php"
                class="flex items-center gap-3 px-4 py-3 text-red-400 hover:bg-red-500/10 rounded-xl transition-all">
                <span class="material-icons-round">logout</span>
                <span class="font-medium">تسجيل الخروج</span>
            </a>
        </div>
    </aside>

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
            class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10">
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

        <!-- Content Srcollable -->
        <div class="flex-1 overflow-y-auto p-8 relative z-10">

            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="text-center py-4 text-gray-500">
                    No data to display at this time.
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
                                        No data to display at this time.
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
                            No data to display at this time.
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

</body>

</html>