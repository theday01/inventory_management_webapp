<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>العملاء - Smart Shop</title>
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
                class="flex items-center gap-3 px-3 lg:px-4 py-3 bg-primary/10 text-primary rounded-xl transition-all group">
                <span class="material-icons-round">people</span>
                <span class="font-medium hidden lg:block">العملاء</span>
            </a>
            <a href="settings.php"
                class="flex items-center gap-3 px-3 lg:px-4 py-3 text-gray-400 hover:bg-white/5 hover:text-white rounded-xl transition-all group">
                <span class="material-icons-round">settings</span>
                <span class="font-medium hidden lg:block">الإعدادات</span>
            </a>
        </nav>
    </aside>

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
</body>

</html>