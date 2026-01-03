<?php
require_once 'db.php';

echo "<h2>إصلاح قيود الحذف في قاعدة البيانات</h2>";

// 1. التحقق من وجود القيد الخارجي
$check_fk = $conn->query("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = 'smart_shop' 
    AND TABLE_NAME = 'invoice_items' 
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
");

if ($check_fk && $check_fk->num_rows > 0) {
    echo "<p style='color: orange;'>⚠️ تم العثور على قيود خارجية في جدول invoice_items</p>";
    
    while ($row = $check_fk->fetch_assoc()) {
        $constraint_name = $row['CONSTRAINT_NAME'];
        echo "<p>جاري حذف القيد: <strong>$constraint_name</strong></p>";
        
        // حذف القيد الخارجي
        $drop_fk = $conn->query("ALTER TABLE invoice_items DROP FOREIGN KEY `$constraint_name`");
        
        if ($drop_fk) {
            echo "<p style='color: green;'>✅ تم حذف القيد بنجاح</p>";
        } else {
            echo "<p style='color: red;'>❌ فشل حذف القيد: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color: green;'>✅ لا توجد قيود خارجية تحتاج للحذف</p>";
}

// 2. التحقق من النتيجة
$verify = $conn->query("
    SELECT CONSTRAINT_NAME 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = 'smart_shop' 
    AND TABLE_NAME = 'invoice_items' 
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
");

if ($verify && $verify->num_rows == 0) {
    echo "<h3 style='color: green;'>✅ تم الإصلاح بنجاح! يمكنك الآن حذف المنتجات بحرية</h3>";
} else {
    echo "<h3 style='color: red;'>❌ لم يتم الإصلاح بالكامل، جرب مرة أخرى</h3>";
}

$conn->close();
?>

<hr>
<p><a href="products.php">← العودة لصفحة المنتجات</a></p>