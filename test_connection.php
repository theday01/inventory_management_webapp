<?php
// =======================================================
// اختبار الاتصال بقاعدة البيانات
// =======================================================
// قم بتشغيل هذا الملف للتحقق من صحة بيانات الاتصال
// ثم احذفه من السيرفر لأسباب أمنية

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>اختبار الاتصال بقاعدة البيانات</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
            direction: rtl;
        }
        .success {
            background: #d4edda;
            padding: 20px;
            border: 2px solid #c3e6cb;
            border-radius: 8px;
            margin: 20px 0;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            padding: 20px;
            border: 2px solid #f5c6cb;
            border-radius: 8px;
            margin: 20px 0;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            padding: 20px;
            border: 2px solid #ffeeba;
            border-radius: 8px;
            margin: 20px 0;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            padding: 20px;
            border: 2px solid #bee5eb;
            border-radius: 8px;
            margin: 20px 0;
            color: #0c5460;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 5px;
            font-weight: bold;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #c7254e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin: 15px 0;
        }
        th, td {
            padding: 12px;
            text-align: right;
            border: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        .step {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-right: 4px solid #007bff;
            border-radius: 4px;
        }
    </style>
</head>
<body>";

echo "<h1>🔍 اختبار الاتصال بقاعدة البيانات - Byet Hosting</h1>";

// الخطوة 1: قراءة بيانات الاتصال
echo "<div class='step'>";
echo "<h2>الخطوة 1: قراءة بيانات الاتصال</h2>";

// التحقق من وجود ملف config.php
if (file_exists(__DIR__ . '/config.php')) {
    echo "<div class='success'>";
    echo "✅ تم العثور على ملف <code>config.php</code>";
    echo "</div>";
    include __DIR__ . '/config.php';
} else {
    echo "<div class='warning'>";
    echo "⚠️ لم يتم العثور على ملف <code>config.php</code>. سيتم استخدام البيانات الافتراضية من <code>db.php</code>";
    echo "</div>";
}

// تضمين ملف db.php للحصول على بيانات الاتصال
if (file_exists(__DIR__ . '/db.php')) {
    // قراءة المحتوى بدون تنفيذ الاتصال
    $db_content = file_get_contents(__DIR__ . '/db.php');
    
    // استخراج البيانات يدوياً من المحتوى
    preg_match('/\$servername\s*=\s*["\']([^"\']+)["\']/', $db_content, $server_match);
    preg_match('/\$username\s*=\s*["\']([^"\']+)["\']/', $db_content, $user_match);
    preg_match('/\$dbname\s*=\s*["\']([^"\']+)["\']/', $db_content, $db_match);
    
    if (empty($servername)) $servername = isset($server_match[1]) ? $server_match[1] : '';
    if (empty($username)) $username = isset($user_match[1]) ? $user_match[1] : '';
    if (empty($dbname)) $dbname = isset($db_match[1]) ? $db_match[1] : '';
}

// عرض بيانات الاتصال (بدون كلمة المرور)
echo "<table>";
echo "<tr><th>البيان</th><th>القيمة</th></tr>";
echo "<tr><td>Server Hostname</td><td><code>" . htmlspecialchars($servername ?? 'غير محدد') . "</code></td></tr>";
echo "<tr><td>Database Username</td><td><code>" . htmlspecialchars($username ?? 'غير محدد') . "</code></td></tr>";
echo "<tr><td>Database Name</td><td><code>" . htmlspecialchars($dbname ?? 'غير محدد') . "</code></td></tr>";
echo "<tr><td>Password</td><td><code>" . (isset($password) && !empty($password) ? '●●●●●●●●' : 'غير محدد') . "</code></td></tr>";
echo "</table>";
echo "</div>";

// الخطوة 2: اختبار الاتصال
echo "<div class='step'>";
echo "<h2>الخطوة 2: اختبار الاتصال بالسيرفر</h2>";

if (!isset($servername) || !isset($username) || !isset($password) || !isset($dbname)) {
    echo "<div class='error'>";
    echo "❌ بيانات الاتصال غير مكتملة. يرجى التأكد من إنشاء ملف <code>config.php</code> وتعبئة جميع البيانات.";
    echo "</div>";
} else {
    // محاولة الاتصال بالسيرفر
    mysqli_report(MYSQLI_REPORT_OFF);
    $test_conn = new mysqli($servername, $username, $password);
    
    if ($test_conn->connect_error) {
        echo "<div class='error'>";
        echo "<h3>❌ فشل الاتصال بالسيرفر</h3>";
        echo "<p><strong>رمز الخطأ:</strong> " . $test_conn->connect_errno . "</p>";
        echo "<p><strong>رسالة الخطأ:</strong> " . $test_conn->connect_error . "</p>";
        echo "<hr>";
        echo "<h4>الأسباب المحتملة:</h4>";
        echo "<ul>";
        echo "<li>اسم السيرفر (Hostname) غير صحيح</li>";
        echo "<li>اسم المستخدم أو كلمة المرور غير صحيحة</li>";
        echo "<li>السيرفر غير متاح حالياً</li>";
        echo "<li>قد يكون هناك قيود على الاتصالات من IP معين</li>";
        echo "</ul>";
        echo "<hr>";
        echo "<h4>📝 كيفية الحصول على البيانات الصحيحة من Byet:</h4>";
        echo "<ol>";
        echo "<li>سجل دخول إلى لوحة تحكم Byet (Vista Panel)</li>";
        echo "<li>اذهب إلى قسم <strong>MySQL Databases</strong></li>";
        echo "<li>انسخ البيانات التالية:";
        echo "<ul>";
        echo "<li><strong>MySQL Hostname:</strong> (مثل sql000.byethost.com أو sql001.byethost.com)</li>";
        echo "<li><strong>MySQL Username:</strong> (يبدأ عادة بـ b00_)</li>";
        echo "<li><strong>Database Name:</strong> (يبدأ عادة بـ b00_)</li>";
        echo "<li><strong>Password:</strong> كلمة المرور التي أنشأتها</li>";
        echo "</ul></li>";
        echo "<li>احفظ هذه البيانات في ملف <code>config.php</code></li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='success'>";
        echo "✅ نجح الاتصال بسيرفر MySQL!";
        echo "</div>";
        
        // الخطوة 3: اختبار قاعدة البيانات
        echo "</div>";
        echo "<div class='step'>";
        echo "<h2>الخطوة 3: اختبار قاعدة البيانات</h2>";
        
        if ($test_conn->select_db($dbname)) {
            echo "<div class='success'>";
            echo "✅ نجح الاتصال بقاعدة البيانات <code>$dbname</code>!";
            echo "</div>";
            
            // الخطوة 4: فحص الجداول
            echo "</div>";
            echo "<div class='step'>";
            echo "<h2>الخطوة 4: فحص الجداول</h2>";
            
            $result = $test_conn->query("SHOW TABLES");
            if ($result && $result->num_rows > 0) {
                echo "<div class='success'>";
                echo "<h3>✅ تم العثور على " . $result->num_rows . " جدول</h3>";
                echo "<table>";
                echo "<tr><th>#</th><th>اسم الجدول</th></tr>";
                $count = 1;
                while ($row = $result->fetch_array()) {
                    echo "<tr><td>$count</td><td><code>" . htmlspecialchars($row[0]) . "</code></td></tr>";
                    $count++;
                }
                echo "</table>";
                echo "</div>";
                
                // فحص جدول users
                $check_users = $test_conn->query("SHOW TABLES LIKE 'users'");
                if ($check_users && $check_users->num_rows > 0) {
                    echo "<div class='success'>";
                    echo "<h3>✅ جدول المستخدمين موجود</h3>";
                    
                    // عد المستخدمين
                    $count_users = $test_conn->query("SELECT COUNT(*) as total FROM users");
                    if ($count_users) {
                        $total = $count_users->fetch_assoc()['total'];
                        echo "<p>عدد المستخدمين: <strong>$total</strong></p>";
                        
                        if ($total == 0) {
                            echo "<div class='warning'>";
                            echo "<p>⚠️ لا يوجد مستخدمين في قاعدة البيانات</p>";
                            echo "<p><a href='create_admin.php' class='btn'>إنشاء مستخدم Admin</a></p>";
                            echo "</div>";
                        } else {
                            // عرض المستخدمين
                            $users = $test_conn->query("SELECT id, username, role FROM users LIMIT 5");
                            if ($users && $users->num_rows > 0) {
                                echo "<table>";
                                echo "<tr><th>ID</th><th>اسم المستخدم</th><th>الصلاحية</th></tr>";
                                while ($user = $users->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                                    echo "<td><code>" . htmlspecialchars($user['username']) . "</code></td>";
                                    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                            }
                        }
                    }
                    echo "</div>";
                } else {
                    echo "<div class='error'>";
                    echo "<h3>❌ جدول المستخدمين غير موجود</h3>";
                    echo "<p>يجب عليك تشغيل ملف <code>install.php</code> أولاً لإنشاء الجداول</p>";
                    echo "<p><a href='install.php' class='btn'>تشغيل install.php</a></p>";
                    echo "</div>";
                }
            } else {
                echo "<div class='warning'>";
                echo "<h3>⚠️ قاعدة البيانات فارغة</h3>";
                echo "<p>لا توجد جداول في قاعدة البيانات. يجب تشغيل <code>install.php</code></p>";
                echo "<p><a href='install.php' class='btn'>تشغيل install.php</a></p>";
                echo "</div>";
            }
        } else {
            echo "<div class='error'>";
            echo "<h3>❌ فشل الوصول إلى قاعدة البيانات</h3>";
            echo "<p><strong>رسالة الخطأ:</strong> " . $test_conn->error . "</p>";
            echo "<p>تأكد من أن اسم قاعدة البيانات صحيح في ملف <code>config.php</code></p>";
            echo "</div>";
        }
        
        $test_conn->close();
    }
}
echo "</div>";

// الخطوات التالية
echo "<div class='info'>";
echo "<h2>📋 الخطوات التالية:</h2>";
echo "<ol>";
echo "<li>تأكد من صحة بيانات الاتصال في ملف <code>config.php</code></li>";
echo "<li>إذا لم تكن قاعدة البيانات موجودة، قم بتشغيل <code>install.php</code></li>";
echo "<li>إذا لم يكن هناك مستخدم admin، قم بتشغيل <code>create_admin.php</code></li>";
echo "<li>بعد نجاح جميع الخطوات، احذف الملفات التالية من السيرفر:";
echo "<ul>";
echo "<li><code>test_connection.php</code> (هذا الملف)</li>";
echo "<li><code>create_admin.php</code></li>";
echo "<li><code>install.php</code> (اختياري)</li>";
echo "</ul></li>";
echo "<li>جرب تسجيل الدخول من <a href='login.php' class='btn'>صفحة تسجيل الدخول</a></li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>
