<?php
session_start();

// إذا كان المستخدم مسجل دخول، يذهب للوحة التحكم
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit();
}

// إذا لم يكن مسجل دخول، يذهب لصفحة تسجيل الدخول
header("Location: login.php");
exit();

?>
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0;url=login.php">
    <title>Redirecting...</title>
</head>

<body>
    <script>
        window.location.href = "login.php";
    </script>
</body>

</html>