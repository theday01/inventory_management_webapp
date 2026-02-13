<?php
// =======================================================
// Smart Shop Database Configuration
// =======================================================

// Disable mysqli error reporting to prevent HTML output in API responses
mysqli_report(MYSQLI_REPORT_OFF);

// Enable Error Reporting for Debugging (Temporary - will be overridden by API if needed)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// 1. CONFIGURATION OVERRIDE (Optional)
if (file_exists(__DIR__ . '/config.php')) {
    include __DIR__ . '/config.php';
}

// 2. DEMO MODE SETTING
if (!defined('DEMO_MODE')) {
    define('DEMO_MODE', true);
}

// 3. DATABASE CREDENTIALS
// Default Localhost Settings (XAMPP/WAMP) - used if not overridden in config.php
if (!isset($servername)) $servername = "sql210.byethost24.com";
if (!isset($username))   $username = "b24_41136349";
if (!isset($password))   $password = "SHOP123456789SHOP";
if (!isset($dbname))     $dbname = "b24_41136349_shop";

// =======================================================
// Connection Logic (Optimized for Shared Hosting / InfinityFree)
// =======================================================

try {
    // Create connection (non-persistent)
    // We remove the '@' operator and rely on try-catch with MYSQLI_REPORT_OFF
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error loading character set utf8mb4: " . $conn->error);
    }

} catch (Exception $e) {
    // Check if we are in an API context (expecting JSON)
    // This can be set by the calling script or inferred from headers
    $isApi = defined('IS_API_REQUEST') || 
             (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
             (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

    if ($isApi) {
        // Clean any previous output (e.g., HTML warnings from hosting)
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database Connection Error',
            'error_details' => defined('DEMO_MODE') && DEMO_MODE ? 'Connection Failed (Demo Mode)' : $e->getMessage()
        ]);
        exit;
    } else {
        // Standard HTML error page for browser
        error_log("Database Error: " . $e->getMessage());
        if (defined('DEMO_MODE') && DEMO_MODE) {
            die("Database Connection Failed. Please check config.");
        } else {
            die("Connection failed: " . $e->getMessage());
        }
    }
}
?>
