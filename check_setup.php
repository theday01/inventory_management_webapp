<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>System Diagnostic</h1>";

// 1. Check PHP Version
echo "<h2>PHP Version</h2>";
echo "Current Version: " . phpversion() . "<br>";

// 2. Check Database Connection
echo "<h2>Database Connection</h2>";
require_once 'db.php';

if (isset($conn) && $conn instanceof mysqli) {
    if ($conn->connect_error) {
        echo "<span style='color:red'>Connection Failed: " . $conn->connect_error . "</span>";
    } else {
        echo "<span style='color:green'>Database Connection Successful!</span><br>";
        echo "Host info: " . $conn->host_info;
    }
} else {
    echo "<span style='color:red'>Connection object not found.</span>";
}

// 3. Check Session
echo "<h2>Session Check</h2>";
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['test'] = 'working';
echo "Session Status: " . (isset($_SESSION['test']) ? "<span style='color:green'>Working</span>" : "<span style='color:red'>Failed</span>");

echo "<h2>Next Steps</h2>";
echo "If you see 'Database Connection Successful', please try loading <a href='login.php'>login.php</a> again.<br>";
echo "If you still see Error 500, please share the error message displayed on that page.";
?>
