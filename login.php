<?php
session_start();
require_once 'db.php';
require_once 'src/language.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];

            // إنشاء إشعار تسجيل الدخول
            $notification_message = "قام المستخدم '{$username}' بتسجيل الدخول إلى النظام";
            $notification_type = "user_login";
            
            $notif_stmt = $conn->prepare("INSERT INTO notifications (message, type, status) VALUES (?, ?, 'unread')");
            $notif_stmt->bind_param("ss", $notification_message, $notification_type);
            $notif_stmt->execute();
            $notif_stmt->close();

            $stmt->close();
            header("location: reports.php");
            exit;
        } else {
            $login_err = __('invalid_password');
        }
    } else {
        $login_err = __('invalid_credentials');
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="<?php echo get_locale(); ?>" dir="<?php echo get_dir(); ?>" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('login_title'); ?></title>
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

<body class="bg-dark text-white font-sans min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

<!-- نظام الرسائل الموحد -->
<div id="toast-notification" class="fixed top-5 left-1/2 transform -translate-x-1/2 z-[9999] transition-all duration-300 ease-out opacity-0 -translate-y-10 pointer-events-none">
    <div id="toast-content" class="flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md">
        <span id="toast-icon" class="material-icons-round text-2xl"></span>
        <span id="toast-message" class="font-bold text-lg"></span>
    </div>
</div>

<script>
    function togglePassword(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            toggleIcon.textContent = 'visibility';
        }
    }
</script>

<script>
    window.translations = <?php echo json_encode($translations); ?>;
    window.__ = function(key) {
        return window.translations[key] || key;
    };

    function showToast(message, isSuccess) {
        // Smart detection for error messages if isSuccess is not explicitly provided
        if (typeof isSuccess === 'undefined') {
            const lowerMsg = String(message).toLowerCase();
            const errorKeywords = [
                'error', 'fail', 'wrong', 'denied', 'unauthorized', // English
                'خطأ', 'فشل', 'مشكلة', 'تنبيه', 'عذراً', 'غير مصرح', 'مرفوض', 'تعذر', 'نفذت' // Arabic
            ];
            // Default to true (success), but switch to false (error) if keyword found
            isSuccess = !errorKeywords.some(keyword => lowerMsg.includes(keyword));
        }

        const toast = document.getElementById('toast-notification');
        const toastContent = document.getElementById('toast-content');
        const toastMessage = document.getElementById('toast-message');
        const toastIcon = document.getElementById('toast-icon');

        toastMessage.textContent = message;
        
        if (isSuccess) {
            toastContent.className = 'flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md bg-emerald-600 text-white border border-emerald-400/30';
            toastIcon.textContent = 'check_circle';
        } else {
            toastContent.className = 'flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md bg-rose-600 text-white border border-rose-400/30';
            toastIcon.textContent = 'error';
        }

        toast.classList.remove('opacity-0', '-translate-y-10', 'pointer-events-none');
        toast.classList.add('opacity-100', 'translate-y-0', 'pointer-events-auto');

        setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', '-translate-y-10');
            setTimeout(() => {
                toast.classList.add('pointer-events-none');
            }, 300);
        }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('success')) {
            const successMsg = urlParams.get('success');
            showToast(successMsg, true);
            
            urlParams.delete('success');
            const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
            window.history.replaceState({}, '', newUrl);
        }
    });
</script>

    <div
        class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-primary/20 rounded-full blur-[120px] pointer-events-none">
    </div>
    <div
        class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-accent/10 rounded-full blur-[120px] pointer-events-none">
    </div>

    <div
        class="w-full max-w-md bg-dark-surface/50 backdrop-blur-xl border border-white/5 rounded-2xl shadow-2xl p-8 relative z-10 glass-panel">

        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-white mb-2"><?php echo __('welcome_back'); ?></h1>
            <p class="text-gray-400"><?php echo __('login_subtitle'); ?></p>
        </div>

        <form action="login.php" method="POST" class="space-y-6">
            <?php 
            if(!empty($login_err)){
                echo '<script>setTimeout(() => showToast("' . addslashes($login_err) . '", false), 100);</script>';
            }        
            ?>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('username'); ?></label>
                <input type="text" id="username" name="username"
                    class="w-full bg-dark/50 border border-dark-border text-white text-start placeholder-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                    placeholder="<?php echo __('enter_username'); ?>">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2"><?php echo __('password'); ?></label>
                <div class="relative">
                    <input type="password" id="password" name="password"
                        class="w-full bg-dark/50 border border-dark-border text-white text-start placeholder-gray-500 rounded-xl px-4 py-3 pl-12 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePassword('password', 'togglePasswordIcon')" 
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                        <span id="togglePasswordIcon" class="material-icons-round">visibility</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                        class="h-4 w-4 text-primary bg-dark border-gray-600 rounded focus:ring-primary cursor-pointer">
                    <label for="remember-me"
                        class="mr-2 block text-sm text-gray-400 cursor-pointer select-none"><?php echo __('remember_me'); ?></label>
                </div>
                <div class="text-sm">
                    <a href="password_reset.php" class="font-medium text-primary hover:text-primary-hover transition-colors"><?php echo __('forgot_password'); ?></a>
                </div>
            </div>

            <button type="submit"
                class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-primary/25 text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform hover:-translate-y-0.5">
                <?php echo __('login_btn'); ?>
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500"><?php echo __('system_version'); ?></p>
            <!-- Language Switcher -->
            <div class="flex items-center justify-center gap-4 mt-4">
                <?php
                $currentParams = $_GET;
                $currentParams['lang'] = 'ar';
                $arLink = '?' . http_build_query($currentParams);
                $currentParams['lang'] = 'fr';
                $frLink = '?' . http_build_query($currentParams);
                ?>
               <a href="<?php echo htmlspecialchars($arLink); ?>" class="text-sm font-bold <?php echo get_locale() === 'ar' ? 'text-primary' : 'text-gray-500 hover:text-gray-300'; ?>"><?php echo __('arabic'); ?></a>
               <a href="<?php echo htmlspecialchars($frLink); ?>" class="text-sm font-bold <?php echo get_locale() === 'fr' ? 'text-primary' : 'text-gray-500 hover:text-gray-300'; ?>"><?php echo __('french'); ?></a>
            </div>
        </div>
    </div>

</body>

</html>