<?php
session_start();
require_once 'db.php';

// Handle login form submission
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
            // Password is correct, start a new session
            $_SESSION['loggedin'] = true;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];

            // Redirect to dashboard
            header("location: dashboard.php");
            exit;
        } else {
            // Display an error message if password is not valid
            $login_err = "كلمة المرور غير صحيحة.";
        }
    } else {
        // Display an error message if username doesn't exist
        $login_err = "كلمة المرور أو اسم المستخدم غير صحيح";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - Smart Shop</title>
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body class="bg-dark text-white font-sans min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background Decoration -->
    <div
        class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-primary/20 rounded-full blur-[120px] pointer-events-none">
    </div>
    <div
        class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-accent/10 rounded-full blur-[120px] pointer-events-none">
    </div>

    <div
        class="w-full max-w-md bg-dark-surface/50 backdrop-blur-xl border border-white/5 rounded-2xl shadow-2xl p-8 relative z-10 glass-panel">

        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-white mb-2">مرحباً بك مجدداً 👋</h1>
            <p class="text-gray-400">سجّل دخولك لإدارة متاجرك</p>
        </div>

        <form action="login.php" method="POST" class="space-y-6">
            <?php 
            if(!empty($login_err)){
                echo '<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-200 dark:text-red-800" role="alert">' . $login_err . '</div>';
            }        
            ?>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-300 mb-2">اسم المستخدم</label>
                <input type="text" id="username" name="username"
                    class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                    placeholder="أدخل اسم المستخدم">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">كلمة المرور</label>
                <input type="password" id="password" name="password"
                    class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                        class="h-4 w-4 text-primary bg-dark border-gray-600 rounded focus:ring-primary cursor-pointer">
                    <label for="remember-me"
                        class="mr-2 block text-sm text-gray-400 cursor-pointer select-none">تذكرني</label>
                </div>
                <div class="text-sm">
                    <a href="#" class="font-medium text-primary hover:text-primary-hover transition-colors">نسيت كلمة
                        المرور؟</a>
                </div>
            </div>

            <button type="submit"
                class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-primary/25 text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform hover:-translate-y-0.5">
                ت سجيل الدخول
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">نظام إدارة المتاجر الذكي الإصدار 1.0</p>
        </div>
    </div>

</body>

</html>