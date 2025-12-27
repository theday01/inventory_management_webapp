<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفواتير والضريبة - Smart Shop</title>
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

<body class="bg-dark text-white font-sans h-screen flex overflow-hidden">

    <!-- Sidebar -->
    <aside
        class="w-20 lg:w-64 bg-dark-surface/80 backdrop-blur-xl border-l border-white/5 flex flex-col z-50 transition-all duration-300">
        <div class="h-20 flex items-center justify-center border-b border-white/5">
            <h1
                class="text-2xl font-bold bg-gradient-to-r from-primary to-accent bg-clip-text text-transparent hidden lg:block">
                Smart Shop</h1>
            <span class="material-icons-round text-primary text-3xl lg:hidden">storefront</span>
        </div>

        <nav class="flex-1 overflow-y-auto py-6 space-y-2 px-2 lg:px-4">
            <a href="dashboard.php"
                class="flex items-center gap-3 px-3 lg:px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all group">
                <span class="material-icons-round">dashboard</span>
                <span class="font-medium hidden lg:block">لوحة التحكم</span>
            </a>
            <a href="pos.php"
                class="flex items-center gap-3 px-3 lg:px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all group">
                <span class="material-icons-round">point_of_sale</span>
                <span class="font-medium hidden lg:block">نقطة البيع</span>
            </a>
            <a href="products.php"
                class="flex items-center gap-3 px-3 lg:px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all group">
                <span class="material-icons-round">inventory_2</span>
                <span class="font-medium hidden lg:block">المنتجات</span>
            </a>
            <a href="customers.php"
                class="flex items-center gap-3 px-3 lg:px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all group">
                <span class="material-icons-round">people</span>
                <span class="font-medium hidden lg:block">العملاء</span>
            </a>
            <a href="settings.php"
                class="flex items-center gap-3 px-3 lg:px-4 py-3 bg-primary/10 text-primary rounded-xl transition-all group">
                <span class="material-icons-round">settings</span>
                <span class="font-medium hidden lg:block">الإعدادات</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col relative overflow-hidden">
        <div
            class="absolute top-0 left-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-[120px] pointer-events-none">
        </div>

        <!-- Header -->
        <header
            class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
            <h2 class="text-xl font-bold text-white">الفواتير والضريبة</h2>
            <div class="flex items-center gap-4">
                <button
                    class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5">
                    حفظ التغييرات
                </button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Settings Menu -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl glass-panel overflow-hidden">
                        <nav class="flex flex-col">
                            <a href="settings.php"
                                class="px-6 py-4 flex items-center gap-3 text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-r-2 border-transparent">
                                <span class="material-icons-round">store</span>
                                <span class="font-bold">إعدادات المتجر</span>
                            </a>
                            <a href="invoices.php"
                                class="px-6 py-4 flex items-center gap-3 bg-primary/10 text-primary border-r-2 border-primary">
                                <span class="material-icons-round">receipt</span>
                                <span class="font-bold">الفواتير والضريبة</span>
                            </a>
                            <a href="users.php"
                                class="px-6 py-4 flex items-center gap-3 text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-r-2 border-transparent">
                                <span class="material-icons-round">group</span>
                                <span class="font-bold">المستخدمين</span>
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Invoices Content -->
                <div class="lg:col-span-2 space-y-6">
                    <section
                        class="bg-dark-surface/60 backdrop-blur-md border border-white/5 rounded-2xl p-6 glass-panel">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <span class="material-icons-round text-primary">receipt</span>
                            الفواتير
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-right">
                                <thead>
                                    <tr class="border-b border-white/10">
                                        <th class="p-4 text-sm font-bold text-gray-400">رقم الفاتورة</th>
                                        <th class="p-4 text-sm font-bold text-gray-400">التاريخ</th>
                                        <th class="p-4 text-sm font-bold text-gray-400">العميل</th>
                                        <th class="p-4 text-sm font-bold text-gray-400">المبلغ</th>
                                        <th class="p-4 text-sm font-bold text-gray-400"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-gray-500">
                                            No data to display at this time.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <!-- Invoice Modal -->
        <div id="invoiceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden" onclick="closeInvoiceModal()">
            <div class="bg-dark-surface rounded-2xl w-full max-w-lg mx-4" onclick="event.stopPropagation()">
                <div class="p-6 border-b border-white/10 flex justify-between items-center">
                    <h3 class="text-lg font-bold">تفاصيل الفاتورة</h3>
                    <button onclick="closeInvoiceModal()" class="text-gray-400 hover:text-white">
                        <span class="material-icons-round">close</span>
                    </button>
                </div>
                <div class="p-6 text-center" id="invoiceContent">
                    <!-- Dynamic content will be injected here -->
                </div>
                <div class="p-6 bg-dark/50 rounded-b-2xl flex justify-end">
                     <button onclick="printInvoice()" class="bg-primary hover:bg-primary-hover text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                        <span class="material-icons-round">print</span>
                        طباعة
                    </button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>