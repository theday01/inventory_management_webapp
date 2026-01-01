<?php
require_once 'db.php';

// التحقق من وجود مستخدمين في النظام
$sql = "SELECT id FROM users LIMIT 1";
$result = $conn->query($sql);

// إذا كان هناك مستخدم واحد على الأقل، يجب تسجيل الدخول أولاً للوصول لهذه الصفحة
if ($result && $result->num_rows > 0) {
    session_start();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php?error=" . urlencode("يجب تسجيل الدخول أولاً"));
        exit();
    }
    // التحقق من أن المستخدم admin
    if ($_SESSION['role'] !== 'admin') {
        header("Location: dashboard.php?error=" . urlencode("ليس لديك صلاحية الوصول"));
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        header("Location: register.php?error=" . urlencode("كلمات المرور غير متطابقة"));
        exit();
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin';

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashed_password, $role);

        if ($stmt->execute()) {
            header("Location: login.php?success=" . urlencode("تم إنشاء الحساب بنجاح"));
            exit();
        } else {
            header("Location: register.php?error=" . urlencode("حدث خطأ أثناء إنشاء الحساب"));
            exit();
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب جديد</title>
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
    function showToast(message, isSuccess = true) {
        const toast = document.getElementById('toast-notification');
        const toastContent = document.getElementById('toast-content');
        const toastMessage = document.getElementById('toast-message');
        const toastIcon = document.getElementById('toast-icon');

        toastMessage.textContent = message;
        
        if (isSuccess) {
            toastContent.className = 'flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md bg-green-500 text-white';
            toastIcon.textContent = 'check_circle';
        } else {
            toastContent.className = 'flex items-center gap-3 px-6 py-4 rounded-xl shadow-2xl backdrop-blur-md bg-red-500 text-white';
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
        
        if (urlParams.has('error')) {
            const errorMsg = urlParams.get('error');
            showToast(errorMsg, false);
            
            urlParams.delete('error');
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
            <h1 class="text-3xl font-bold text-white mb-2">إنشاء حساب مسؤول</h1>
            <p class="text-gray-400">مرحباً بك في Smart Shop. قم بإنشاء الحساب الأول ليكون حساب المدير.</p>
        </div>

        <form action="register.php" method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-300 mb-2">اسم المستخدم</label>
                <input type="text" id="username" name="username"
                    class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                    placeholder="أدخل اسم المستخدم" required>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">كلمة المرور</label>
                <input type="password" id="password" name="password"
                    class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                    placeholder="••••••••" required>
            </div>

            <div>
                <label for="password_confirm" class="block text-sm font-medium text-gray-300 mb-2">تأكيد كلمة المرور</label>
                <input type="password" id="password_confirm" name="password_confirm"
                    class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                    placeholder="••••••••" required>
            </div>

            <button type="submit"
                class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-primary/25 text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform hover:-translate-y-0.5">
                إنشاء حساب
            </button>
        </form>
    </div>

</body>

</html>
<?php $conn->close(); ?>