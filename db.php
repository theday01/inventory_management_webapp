<?php
// =======================================================
// Smart Shop Database Configuration
// =======================================================

// Enable Error Reporting for Debugging (Temporary)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. CONFIGURATION OVERRIDE (Optional)
// If you want to keep your credentials separate from the code (recommended for git),
// create a file named 'config.php' in the same directory and define variables there.
// This is loaded first to allow overriding constants and variables.
if (file_exists(__DIR__ . '/config.php')) {
    include __DIR__ . '/config.php';
}

// 2. DEMO MODE SETTING
// Set to true to enable "Demo Mode" (Restricts sensitive actions)
// Set to false for production use
if (!defined('DEMO_MODE')) {
    define('DEMO_MODE', true);
}

// 3. DATABASE CREDENTIALS
// Default Localhost Settings (XAMPP/WAMP) - used if not overridden in config.php
if (!isset($servername)) $servername = "sql210.infinityfree.com";
if (!isset($username))   $username = "if0_38514101";
if (!isset($password))   $password = "zsMLehRDSMzn";
if (!isset($dbname))     $dbname = "if0_38514101_shop";

// InfinityFree / Shared Hosting Settings (Example)
/*
To use on InfinityFree, edit the variables above or create config.php with:
<?php
$servername = "sqlXXX.infinityfree.com";
$username = "if0_XXXXXXXX";
$password = "YourPassword";
$dbname = "if0_XXXXXXXX_smart_shop";
*/

// =======================================================
// Connection Logic (Do not edit below this line)
// =======================================================

// Create connection
try {
    // Suppress errors to avoid leaking credentials
    $conn = @new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        // In production, log this to a file instead of showing it
        error_log("Connection failed: " . $conn->connect_error);
        if (defined('DEMO_MODE') && DEMO_MODE) {
            die("Database Connection Failed. Please check config.");
        } else {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
