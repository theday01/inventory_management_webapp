<?php
require_once 'db.php';

$reset_step = 1; // الخطوة الأولى: إدخال اسم المستخدم
$username = '';
$user_id = '';
$security_questions = [];
$user_exists = false;
$questions_answered = false;
$reset_success = false;

// الخطوة 1: البحث عن اسم المستخدم
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'search_user') {
        $username = trim($_POST['username']);
        
        if (empty($username)) {
            $error = "الرجاء إدخال اسم المستخدم";
        } else {
            // البحث عن المستخدم
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_id = $user['id'];
                $user_exists = true;
                $reset_step = 2;

                // الحصول على أسئلة الأمان
                $stmt_questions = $conn->prepare("SELECT id, question FROM security_questions WHERE user_id = ? LIMIT 3");
                $stmt_questions->bind_param("i", $user_id);
                $stmt_questions->execute();
                $questions_result = $stmt_questions->get_result();
                
                while ($row = $questions_result->fetch_assoc()) {
                    $security_questions[] = $row;
                }
                $stmt_questions->close();

                if (count($security_questions) == 0) {
                    $error = "لم تتم إعادة الأسئلة الأمنية لهذا الحساب. الرجاء التواصل مع المسؤول.";
                }
            } else {
                $error = "اسم المستخدم غير موجود في النظام";
            }
            $stmt->close();
        }
    } 
    elseif ($action == 'verify_answers') {
        $user_id = $_POST['user_id'];
        $correct_answers = 0;

        // التحقق من الأجوبة
        for ($i = 0; $i < 3; $i++) {
            if (isset($_POST['answer_' . $i])) {
                $question_id = $_POST['question_id_' . $i];
                $provided_answer = strtolower(trim($_POST['answer_' . $i]));

                // الحصول على الإجابة الصحيحة
                $stmt_check = $conn->prepare("SELECT answer FROM security_questions WHERE id = ? AND user_id = ?");
                $stmt_check->bind_param("ii", $question_id, $user_id);
                $stmt_check->execute();
                $check_result = $stmt_check->get_result();

                if ($check_result->num_rows > 0) {
                    $row = $check_result->fetch_assoc();
                    if ($row['answer'] === $provided_answer) {
                        $correct_answers++;
                    }
                }
                $stmt_check->close();
            }
        }

        // يجب الإجابة على سؤالين على الأقل بشكل صحيح
        if ($correct_answers >= 2) {
            $questions_answered = true;
            $reset_step = 3;
        } else {
            $error = "الإجابات غير صحيحة. يجب الإجابة على سؤالين على الأقل بشكل صحيح.";
            $reset_step = 2;
            $user_id = $_POST['user_id'];

            // إعادة تحميل الأسئلة
            $stmt_questions = $conn->prepare("SELECT id, question FROM security_questions WHERE user_id = ? LIMIT 3");
            $stmt_questions->bind_param("i", $user_id);
            $stmt_questions->execute();
            $questions_result = $stmt_questions->get_result();
            
            while ($row = $questions_result->fetch_assoc()) {
                $security_questions[] = $row;
            }
            $stmt_questions->close();

            // الحصول على اسم المستخدم
            $stmt_user = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $user_result = $stmt_user->get_result();
            $user_data = $user_result->fetch_assoc();
            $username = $user_data['username'];
            $stmt_user->close();
        }
    }
    elseif ($action == 'reset_password') {
        if (defined('DEMO_MODE') && DEMO_MODE) {
            $error = "هاته الخاصية غير متاحة في العرض التجريبي";
        } else {
            $user_id = $_POST['user_id'];
            $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($new_password) || empty($confirm_password)) {
            $error = "الرجاء إدخال كلمة المرور الجديدة";
        } elseif ($new_password !== $confirm_password) {
            $error = "كلمات المرور غير متطابقة";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // تحديث كلمة المرور
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt_update->execute()) {
                $reset_success = true;
                $reset_step = 4;

                // إنشاء إشعار بتغيير كلمة المرور
                $notification_message = "تم إعادة تعيين كلمة المرور بنجاح";
                $notification_type = "password_reset";
                
                $notif_stmt = $conn->prepare("INSERT INTO notifications (message, type, status) VALUES (?, ?, 'unread')");
                $notif_stmt->bind_param("ss", $notification_message, $notification_type);
                $notif_stmt->execute();
                $notif_stmt->close();
            } else {
                $error = "حدث خطأ أثناء تحديث كلمة المرور";
            }
            $stmt_update->close();
        }
        }
    }
}

// Get shop favicon
$result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'shopFavicon'");
$shopFavicon = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['setting_value'] : '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($shopFavicon)): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($shopFavicon); ?>">
    <?php endif; ?>
    <title>استعادة كلمة المرور</title>
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
    const isDemo = <?php echo (defined('DEMO_MODE') && DEMO_MODE) ? 'true' : 'false'; ?>;
    const demoRestrictionMsg = "هاته الخاصية غير متاحة في العرض التجريبي";

    function checkDemo(e) {
        if (isDemo) {
            if (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
            showToast(demoRestrictionMsg, false);
            return true;
        }
        return false;
    }

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

    <div
        class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-primary/20 rounded-full blur-[120px] pointer-events-none">
    </div>
    <div
        class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-accent/10 rounded-full blur-[120px] pointer-events-none">
    </div>

    <div
        class="w-full max-w-2xl bg-dark-surface/50 backdrop-blur-xl border border-white/5 rounded-2xl shadow-2xl p-8 relative z-10 glass-panel">

        <!-- الخطوة 1: إدخال اسم المستخدم -->
        <?php if ($reset_step == 1): ?>
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/20 rounded-full mb-4">
                    <span class="material-icons-round text-3xl text-primary">lock_reset</span>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">استعادة كلمة المرور</h1>
                <p class="text-gray-400">ابدأ بإدخال اسم المستخدم الخاص بك</p>
            </div>

            <?php if (isset($error)): ?>
                <script>setTimeout(() => showToast("<?php echo addslashes($error); ?>", false), 100);</script>
            <?php endif; ?>

            <form action="password_reset.php" method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">اسم المستخدم</label>
                    <input type="text" id="username" name="username"
                        class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                        placeholder="أدخل اسم المستخدم" required>
                </div>

                <button type="submit" name="action" value="search_user"
                    class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-primary/25 text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform hover:-translate-y-0.5">
                    متابعة
                </button>

                <div class="text-center">
                    <p class="text-gray-400">هل تريد <a href="login.php" class="text-primary hover:text-primary-hover">تسجيل الدخول</a>؟</p>
                </div>
            </form>

        <!-- الخطوة 2: الإجابة على أسئلة الأمان -->
        <?php elseif ($reset_step == 2): ?>
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-500/20 rounded-full mb-4">
                    <span class="material-icons-round text-3xl text-yellow-400">help</span>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">التحقق من أسئلة الأمان</h1>
                <p class="text-gray-400">أجب على أسئلة الأمان لاستعادة حسابك (يجب الإجابة على سؤالين على الأقل بشكل صحيح)</p>
            </div>

            <?php if (isset($error)): ?>
                <script>setTimeout(() => showToast("<?php echo addslashes($error); ?>", false), 100);</script>
            <?php endif; ?>

            <form action="password_reset.php" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="verify_answers">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <?php foreach ($security_questions as $index => $q): ?>
                    <div class="p-4 bg-dark/50 border border-dark-border rounded-xl">
                        <label class="block text-sm font-medium text-gray-300 mb-3">
                            <span class="text-primary">السؤال <?php echo $index + 1; ?>:</span> <?php echo htmlspecialchars($q['question']); ?>
                        </label>
                        <input type="hidden" name="question_id_<?php echo $index; ?>" value="<?php echo $q['id']; ?>">
                        <input type="text" name="answer_<?php echo $index; ?>"
                            class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                            placeholder="أدخل إجابتك" required>
                    </div>
                <?php endforeach; ?>

                <button type="submit"
                    class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-primary/25 text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform hover:-translate-y-0.5">
                    التحقق من الإجابات
                </button>

                <div class="text-center">
                    <p class="text-gray-400">أريد <a href="password_reset.php" class="text-primary hover:text-primary-hover">المحاولة باسم مستخدم آخر</a></p>
                </div>
            </form>

        <!-- الخطوة 3: إدخال كلمة المرور الجديدة -->
        <?php elseif ($reset_step == 3): ?>
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-500/20 rounded-full mb-4">
                    <span class="material-icons-round text-3xl text-green-400">verified</span>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">تم التحقق بنجاح!</h1>
                <p class="text-gray-400">الآن قم بإدخال كلمة المرور الجديدة</p>
            </div>

            <?php if (isset($error)): ?>
                <script>setTimeout(() => showToast("<?php echo addslashes($error); ?>", false), 100);</script>
            <?php endif; ?>

            <form action="password_reset.php" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-300 mb-2">كلمة المرور الجديدة</label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password"
                            class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 pl-12 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                            placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword('new_password', 'toggleNewPasswordIcon')" 
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                            <span id="toggleNewPasswordIcon" class="material-icons-round">visibility</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-2">تأكيد كلمة المرور</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="w-full bg-dark/50 border border-dark-border text-white text-right placeholder-gray-500 rounded-xl px-4 py-3 pl-12 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-300"
                            placeholder="••••••••" required>
                        <button type="button" onclick="togglePassword('confirm_password', 'toggleConfirmPasswordIcon')" 
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                            <span id="toggleConfirmPasswordIcon" class="material-icons-round">visibility</span>
                        </button>
                    </div>
                </div>

                <button type="submit" onclick="return !checkDemo(event)"
                    class="<?php echo (defined('DEMO_MODE') && DEMO_MODE) ? 'opacity-50 cursor-not-allowed ' : ''; ?>w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-primary/25 text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform hover:-translate-y-0.5">
                    تحديث كلمة المرور
                </button>

                <div class="text-center">
                    <p class="text-gray-400">أريد <a href="password_reset.php" class="text-primary hover:text-primary-hover">البدء من جديد</a></p>
                </div>
            </form>

        <!-- الخطوة 4: نجاح الاستعادة -->
        <?php elseif ($reset_step == 4 && $reset_success): ?>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500/20 rounded-full mb-6">
                    <span class="material-icons-round text-5xl text-green-400">check_circle</span>
                </div>
                <h1 class="text-3xl font-bold text-white mb-3">تم تحديث كلمة المرور بنجاح!</h1>
                <p class="text-gray-400 mb-8">يمكنك الآن تسجيل الدخول باستخدام كلمة المرور الجديدة</p>
                
                <a href="login.php"
                    class="inline-flex justify-center py-3.5 px-8 border border-transparent rounded-xl shadow-lg shadow-primary/25 text-sm font-bold text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 transform hover:-translate-y-0.5">
                    <span class="material-icons-round text-lg ml-2">login</span>
                    تسجيل الدخول
                </a>
            </div>
            <script>setTimeout(() => showToast("تم تحديث كلمة المرور بنجاح!", true), 100);</script>
        <?php endif; ?>

    </div>

</body>

</html>
<?php $conn->close(); ?>
