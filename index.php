<?php
// This file can be used to handle initial setup or redirection.
// For now, we'll just redirect to the login page.
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