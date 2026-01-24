<?php
require_once 'db.php';

// التحقق من وجود مستخدمين في النظام
$sql = "SELECT id FROM users LIMIT 1";
$result = $conn->query($sql);

// تعطيل الصفحة بعد تسجيل الحساب الأول
if ($result && $result->num_rows > 0) {
    header("HTTP/1.0 403 Forbidden");
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl" class="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>الصفحة غير متوفرة</title>
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
        <!-- خلفية متحركة -->
        <div class="absolute inset-0 bg-gradient-to-br from-dark via-dark-surface to-dark opacity-80"></div>
        <div class="absolute inset-0">
            <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-primary/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-accent/5 rounded-full blur-3xl animate-pulse delay-1000"></div>
        </div>

        <div class="relative z-10 w-full max-w-md">
            <div class="glass-panel rounded-2xl p-8 text-center shadow-2xl">
                <div class="mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-red-500/20 rounded-full mb-4">
                        <span class="material-icons-round text-4xl text-red-400">lock</span>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">الصفحة غير متوفرة</h1>
                    <p class="text-gray-300 leading-relaxed">
                        هذه الصفحة لم تعد قابلة للوصول. تواصل مع صاحب المتجر ليضيفك إلى المستخدمين الجدد.
                    </p>
                </div>

                <div class="flex gap-3">
                    <a href="login.php" class="flex-1 bg-primary hover:bg-primary-hover text-white font-bold py-3 px-6 rounded-xl transition-all duration-300 transform hover:-translate-y-0.5 shadow-lg shadow-primary/25">
                        <span class="material-icons-round text-lg mr-2">login</span>
                        تسجيل الدخول
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// إذا كان هناك مستخدم واحد على الأقل، يجب تسجيل الدخول أولاً للوصول لهذه الصفحة
if ($result && $result->num_rows > 0) {
    session_start();
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: login.php?error=" . urlencode("يجب تسجيل الدخول أولاً"));
        exit();
    }
    // التحقق من أن المستخدم admin
    if ($_SESSION['role'] !== 'admin') {
        header("Location: login.php?error=" . urlencode("ليس لديك صلاحية الوصول"));
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
            $user_id = $conn->insert_id;
            
            // حفظ أسئلة الأمان
            $stmt_security = $conn->prepare("INSERT INTO security_questions (user_id, question, answer) VALUES (?, ?, ?)");
            
            // حفظ الأسئلة الثلاثة
            for ($i = 1; $i <= 3; $i++) {
                $question_key = 'security_question_' . $i;
                $answer_key = 'security_answer_' . $i;
                
                if (isset($_POST[$question_key]) && isset($_POST[$answer_key])) {
                    $question = $_POST[$question_key];
                    $answer = strtolower(trim($_POST[$answer_key]));
                    
                    $stmt_security->bind_param("iss", $user_id, $question, $answer);
                    $stmt_security->execute();
                }
            }
            $stmt_security->close();
            
            // إنشاء إشعار التسجيل الناجح
            $notification_message = "تم إنشاء حساب جديد باسم '{$username}' بصلاحيات '{$role}'";
            $notification_type = "user_registration";
            
            $notif_stmt = $conn->prepare("INSERT INTO notifications (message, type, status) VALUES (?, ?, 'unread')");
            $notif_stmt->bind_param("ss", $notification_message, $notification_type);
            $notif_stmt->execute();
            $notif_stmt->close();

            $stmt->close();
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

    // نقل دالة togglePassword خارج DOMContentLoaded لتكون متاحة عالمياً
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

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        if (urlParams.has('error')) {
            const errorMsg = urlParams.get('error');
            showToast(errorMsg, false);
            
            urlParams.delete('error');
            const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
            window.history.replaceState({}, '', newUrl);
        }

        // ===== نظام إدارة أسئلة الأمان =====
        const selectElements = [
            document.getElementById('security_question_1'),
            document.getElementById('security_question_2'),
            document.getElementById('security_question_3')
        ];

        // حفظ الخيارات الأصلية
        const originalOptions = selectElements.map(select => {
            return Array.from(select.options).map(option => ({
                value: option.value,
                text: option.text
            }));
        });

        // دالة لتحديث الخيارات المتاحة
        function updateAvailableOptions() {
            const selectedValues = selectElements.map(select => select.value).filter(val => val !== '');

            selectElements.forEach((select, index) => {
                const currentValue = select.value;
                
                // إعادة تعيين جميع الخيارات من الأصل
                select.innerHTML = '';
                
                // إضافة الخيار الفارغ
                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.text = 'اختر السؤال...';
                select.appendChild(emptyOption);

                // إضافة الخيارات المتاحة
                originalOptions[index].slice(1).forEach(option => {
                    // تجاهل الخيار إذا تم اختياره في select آخر
                    if (!selectedValues.includes(option.value) || option.value === currentValue) {
                        const newOption = document.createElement('option');
                        newOption.value = option.value;
                        newOption.text = option.text;
                        if (option.value === currentValue) {
                            newOption.selected = true;
                        }
                        select.appendChild(newOption);
                    }
                });
            });
        }

        // إضافة event listeners
        selectElements.forEach((select, index) => {
            select.addEventListener('change', updateAvailableOptions);
        });
    });
</script>
    <div
        class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-primary/20 rounded-full blur-[120px] pointer-events-none">
    </div>
    <div
        class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-accent/10 rounded-full blur-[120px] pointer-events-none">
    </div>

    <div
        class="w-full max-w-6xl bg-dark-surface/50 backdrop-blur-xl border border-white/5 rounded-2xl shadow-2xl p-12 relative z-10 glass-panel">

        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-3">إنشاء حساب مسؤول</h1>
            <p class="text-gray-400 text-lg">مرحباً بك في Smart Shop. قم بإنشاء الحساب الأول ليكون حساب المدير.</p>
        </div>

        <form action="register.php" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- الجانب الأيمن: المعلومات الأساسية -->
            <div class="lg:col-span-1 space-y-8">
                <div>
                    <h3 class="text-lg font-bold text-white mb-8 pb-4 border-b border-gray-600">
                        <span class="material-icons-round text-2xl align-middle">person</span>
                        <span class="align-middle mr-2">بيانات الحساب</span>
                    </h3>

                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-300 mb-3">اسم المستخدم</label>
                        <input type="text" id="username" name="username"
                            class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-lg px-4 py-4 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 text-base"
                            placeholder="أدخل اسم المستخدم" required>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-300 mb-3">كلمة المرور</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-lg px-4 py-4 pl-12 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 text-base"
                            placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword('password', 'togglePasswordIcon')" 
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                            <span id="togglePasswordIcon" class="material-icons-round">visibility</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">كلمة المرور يجب أن تكون قوية وسهلة التذكر</p>
                </div>

                <div>
                    <label for="password_confirm" class="block text-sm font-semibold text-gray-300 mb-3">تأكيد كلمة المرور</label>
                    <div class="relative">
                        <input type="password" id="password_confirm" name="password_confirm"
                            class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-lg px-4 py-4 pl-12 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300 text-base"
                            placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword('password_confirm', 'toggleConfirmPasswordIcon')" 
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                            <span id="toggleConfirmPasswordIcon" class="material-icons-round">visibility</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- الجانب الأوسط والأيسر: أسئلة الأمان -->
            <div class="lg:col-span-2 space-y-8">
                <h3 class="text-lg font-bold text-white mb-8 pb-4 border-b border-gray-600">
                    <span class="material-icons-round text-2xl align-middle">security</span>
                    <span class="align-middle mr-2">أسئلة الأمان (لاستعادة كلمة المرور)</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- سؤال الأمان 1 -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex items-center justify-center w-7 h-7 bg-primary/20 rounded-full text-sm font-bold text-primary">1</span>
                            <label for="security_question_1" class="text-sm font-semibold text-gray-300">السؤال الأول</label>
                        </div>
                        <select id="security_question_1" name="security_question_1" 
                            class="w-full bg-dark/50 border border-dark-border text-white text-right rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" required>
                            <option value="">اختر السؤال...</option>
                            <option value="ما اسم والدتك؟">ما اسم والدتك؟</option>
                            <option value="ما هي مدينة ميلادك؟">ما هي مدينة ميلادك؟</option>
                            <option value="ما هو اسم حيوانك الأليف؟">ما هو اسم حيوانك الأليف؟</option>
                            <option value="ما هي أول مدرسة التحقت بها؟">ما هي أول مدرسة التحقت بها؟</option>
                            <option value="ما هو عنوان بيتك الأول؟">ما هو عنوان بيتك الأول؟</option>
                        </select>
                        <input type="text" id="security_answer_1" name="security_answer_1"
                            class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                            placeholder="أجب على السؤال" required>
                    </div>

                    <!-- سؤال الأمان 2 -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="flex items-center justify-center w-7 h-7 bg-primary/20 rounded-full text-sm font-bold text-primary">2</span>
                            <label for="security_question_2" class="text-sm font-semibold text-gray-300">السؤال الثاني</label>
                        </div>
                        <select id="security_question_2" name="security_question_2" 
                            class="w-full bg-dark/50 border border-dark-border text-white text-right rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" required>
                            <option value="">اختر السؤال...</option>
                            <option value="ما اسم والدتك؟">ما اسم والدتك؟</option>
                            <option value="ما هي مدينة ميلادك؟">ما هي مدينة ميلادك؟</option>
                            <option value="ما هو اسم حيوانك الأليف؟">ما هو اسم حيوانك الأليف؟</option>
                            <option value="ما هي أول مدرسة التحقت بها؟">ما هي أول مدرسة التحقت بها؟</option>
                            <option value="ما هو عنوان بيتك الأول؟">ما هو عنوان بيتك الأول؟</option>
                        </select>
                        <input type="text" id="security_answer_2" name="security_answer_2"
                            class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                            placeholder="أجب على السؤال" required>
                    </div>
                </div>

                <!-- سؤال الأمان 3 (كامل العرض) -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="flex items-center justify-center w-7 h-7 bg-primary/20 rounded-full text-sm font-bold text-primary">3</span>
                        <label for="security_question_3" class="text-sm font-semibold text-gray-300">السؤال الثالث</label>
                    </div>
                    <select id="security_question_3" name="security_question_3" 
                        class="w-full bg-dark/50 border border-dark-border text-white text-right rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300" required>
                        <option value="">اختر السؤال...</option>
                        <option value="ما اسم والدتك؟">ما اسم والدتك؟</option>
                        <option value="ما هي مدينة ميلادك؟">ما هي مدينة ميلادك؟</option>
                        <option value="ما هو اسم حيوانك الأليف؟">ما هو اسم حيوانك الأليف؟</option>
                        <option value="ما هي أول مدرسة التحقت بها؟">ما هي أول مدرسة التحقت بها؟</option>
                        <option value="ما هو عنوان بيتك الأول؟">ما هو عنوان بيتك الأول؟</option>
                    </select>
                    <input type="text" id="security_answer_3" name="security_answer_3"
                        class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                        placeholder="أجب على السؤال" required>
                </div>
            </div>

            <button type="submit" class="lg:col-span-3 py-4 px-8 border border-transparent rounded-lg shadow-lg shadow-primary/25 text-base font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <span class="material-icons-round">check_circle</span>
                إنشاء حساب
            </button>
        </form>
    </div>

</body>

</html>
<?php $conn->close(); ?>