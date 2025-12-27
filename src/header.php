<?php require_once 'session.php'; ?>
<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Smart Shop'; ?></title>
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
<?php
$show_success = isset($_GET['saved']) && $_GET['saved'] == 'true';
if ($show_success):
?>
    <div id="successMessage" class="fixed top-5 right-5 bg-lime-500 text-dark-surface px-6 py-3 rounded-xl shadow-lg z-[9999] flex items-center gap-3 transform opacity-0 -translate-y-10 transition-all duration-300 ease-out">
        <span class="material-icons-round">check_circle</span>
        <span class="font-bold">تم حفظ التغييرات بنجاح!</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('successMessage');
            if (message) {
                setTimeout(() => {
                    message.classList.remove('opacity-0');
                    message.classList.remove('-translate-y-10');
                }, 100);

                setTimeout(() => {
                    message.classList.add('opacity-0');
                    message.classList.add('-translate-y-10');
                }, 3000);

                if (window.history.replaceState) {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('saved');
                    window.history.replaceState({ path: url.href }, '', url.href);
                }
            }
        });
    </script>
<?php endif; ?>
<?php
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
if (!empty($error_message)):
?>
    <div id="errorMessage" class="fixed top-5 right-5 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg z-[9999] flex items-center gap-3 transform opacity-0 -translate-y-10 transition-all duration-300 ease-out">
        <span class="material-icons-round">error</span>
        <span class="font-bold"><?php echo $error_message; ?></span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const message = document.getElementById('errorMessage');
            if (message) {
                setTimeout(() => {
                    message.classList.remove('opacity-0');
                    message.classList.remove('-translate-y-10');
                }, 100);

                setTimeout(() => {
                    message.classList.add('opacity-0');
                    message.classList.add('-translate-y-10');
                }, 4000); // Keep error message visible a bit longer

                if (window.history.replaceState) {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('error');
                    window.history.replaceState({ path: url.href }, '', url.href);
                }
            }
        });
    </script>
<?php endif; ?>
