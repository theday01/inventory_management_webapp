<?php
// =======================================================
// إنشاء مستخدم Admin في قاعدة البيانات
// =======================================================
// قم بتشغيل هذا الملف مرة واحدة فقط لإنشاء مستخدم admin
// ثم احذفه من السيرفر لأسباب أمنية

// تضمين ملف قاعدة البيانات
require_once 'db.php';

// التحقق من نجاح الاتصال بقاعدة البيانات
if (!isset($conn) || $conn->connect_error) {
    die("❌ فشل الاتصال بقاعدة البيانات. تأكد من تحديث ملف config.php أولاً!");
}

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>إنشاء مستخدم Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
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
        .info {
            background: #d1ecf1;
            padding: 20px;
            border: 2px solid #bee5eb;
            border-radius: 8px;
            margin: 20px 0;
            color: #0c5460;
        }
        .credentials {
            background: #fff;
            padding: 15px;
            border: 2px solid #28a745;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 18px;
        }
        .btn {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 5px;
            font-weight: bold;
        }
        .btn-danger {
            background: #dc3545;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>";

echo "<h1>🔐 إنشاء مستخدم Admin</h1>";

// التحقق من وجود جدول users
$check_table = $conn->query("SHOW TABLES LIKE 'users'");
if ($check_table->num_rows == 0) {
    echo "<div class='error'>";
    echo "<h2>❌ خطأ: جدول المستخدمين غير موجود!</h2>";
    echo "<p><strong>يجب عليك تشغيل ملف install.php أولاً لإنشاء الجداول.</strong></p>";
    echo "<p><a href='install.php' class='btn'>تشغيل install.php</a></p>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

// التحقق من وجود مستخدم admin
$check_admin = $conn->query("SELECT id FROM users WHERE username = 'admin'");

if ($check_admin->num_rows > 0) {
    echo "<div class='info'>";
    echo "<h2>ℹ️ المستخدم admin موجود بالفعل</h2>";
    echo "<p>يوجد بالفعل مستخدم بالاسم <code>admin</code> في قاعدة البيانات.</p>";
    echo "<h3>خيارات:</h3>";
    echo "<ol>";
    echo "<li><strong>إعادة تعيين كلمة المرور:</strong> سيتم تحديث كلمة المرور إلى <code>123456</code></li>";
    echo "<li><strong>حذف وإعادة إنشاء:</strong> سيتم حذف المستخدم الحالي وإنشاء واحد جديد</li>";
    echo "</ol>";
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<input type='hidden' name='action' value='reset_password'>";
    echo "<button type='submit' class='btn'>إعادة تعيين كلمة المرور</button>";
    echo "</form>";
    echo "<form method='POST' style='margin: 20px 0;'>";
    echo "<input type='hidden' name='action' value='recreate'>";
    echo "<button type='submit' class='btn btn-danger' onclick='return confirm(\"هل أنت متأكد من حذف المستخدم الحالي؟\")'>حذف وإعادة إنشاء</button>";
    echo "</form>";
    echo "</div>";
} else {
    echo "<div class='info'>";
    echo "<p>لا يوجد مستخدم admin في قاعدة البيانات. سيتم إنشاء واحد الآن...</p>";
    echo "</div>";
    $_POST['action'] = 'create_new';
}

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // بيانات المستخدم الافتراضي
    $username = 'admin';
    $password = '123456';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';
    
    if ($action == 'reset_password') {
        // إعادة تعيين كلمة المرور فقط
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $stmt->bind_param("s", $password_hash);
        
        if ($stmt->execute()) {
            echo "<div class='success'>";
            echo "<h2>✅ تم إعادة تعيين كلمة المرور بنجاح!</h2>";
            echo "<div class='credentials'>";
            echo "<p><strong>اسم المستخدم:</strong> <code>admin</code></p>";
            echo "<p><strong>كلمة المرور:</strong> <code>123456</code></p>";
            echo "</div>";
            echo "<p><a href='login.php' class='btn'>الذهاب لصفحة تسجيل الدخول</a></p>";
            echo "<p style='color: red; margin-top: 20px;'><strong>⚠️ تحذير أمني:</strong> احذف هذا الملف (<code>create_admin.php</code>) من السيرفر فوراً!</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h2>❌ فشل تحديث كلمة المرور</h2>";
            echo "<p>الخطأ: " . $conn->error . "</p>";
            echo "</div>";
        }
        $stmt->close();
        
    } elseif ($action == 'recreate') {
        // حذف المستخدم الحالي
        $conn->query("DELETE FROM users WHERE username = 'admin'");
        
        // إنشاء مستخدم جديد
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, first_login) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $username, $password_hash, $role);
        
        if ($stmt->execute()) {
            echo "<div class='success'>";
            echo "<h2>✅ تم إنشاء مستخدم Admin جديد بنجاح!</h2>";
            echo "<div class='credentials'>";
            echo "<p><strong>اسم المستخدم:</strong> <code>admin</code></p>";
            echo "<p><strong>كلمة المرور:</strong> <code>123456</code></p>";
            echo "<p><strong>الصلاحية:</strong> <code>admin</code></p>";
            echo "</div>";
            echo "<p><a href='login.php' class='btn'>الذهاب لصفحة تسجيل الدخول</a></p>";
            echo "<p style='color: red; margin-top: 20px;'><strong>⚠️ تحذير أمني:</strong> احذف هذا الملف (<code>create_admin.php</code>) من السيرفر فوراً!</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h2>❌ فشل إنشاء المستخدم</h2>";
            echo "<p>الخطأ: " . $conn->error . "</p>";
            echo "</div>";
        }
        $stmt->close();
        
    } elseif ($action == 'create_new') {
        // إنشاء مستخدم جديد
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, first_login) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $username, $password_hash, $role);
        
        if ($stmt->execute()) {
            echo "<div class='success'>";
            echo "<h2>✅ تم إنشاء مستخدم Admin بنجاح!</h2>";
            echo "<div class='credentials'>";
            echo "<p><strong>اسم المستخدم:</strong> <code>admin</code></p>";
            echo "<p><strong>كلمة المرور:</strong> <code>123456</code></p>";
            echo "<p><strong>الصلاحية:</strong> <code>admin</code></p>";
            echo "</div>";
            echo "<p><a href='login.php' class='btn'>الذهاب لصفحة تسجيل الدخول</a></p>";
            echo "<p style='color: red; margin-top: 20px;'><strong>⚠️ تحذير أمني:</strong> احذف هذا الملف (<code>create_admin.php</code>) من السيرفر فوراً!</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h2>❌ فشل إنشاء المستخدم</h2>";
            echo "<p>الخطأ: " . $conn->error . "</p>";
            echo "</div>";
        }
        $stmt->close();
    }
}

// عرض المستخدمين الحاليين
$result = $conn->query("SELECT id, username, role, created_at FROM users");
if ($result && $result->num_rows > 0) {
    echo "<div class='info'>";
    echo "<h3>👥 المستخدمون الحاليون في النظام:</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width: 100%; background: white;'>";
    echo "<tr style='background: #007bff; color: white;'><th>ID</th><th>اسم المستخدم</th><th>الصلاحية</th><th>تاريخ الإنشاء</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

$conn->close();

echo "</body></html>";
?>
