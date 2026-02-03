<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default language
$lang = 'ar';

// Check cookie
if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], ['ar', 'fr'])) {
    $lang = $_COOKIE['lang'];
}

// Check GET parameter (to switch language) and update cookie
if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'fr'])) {
    $lang = $_GET['lang'];
    setcookie('lang', $lang, time() + (86400 * 30), "/"); // 30 days
    
    // Redirect to remove query param to avoid sticking to it on refresh
    // But we need to keep other query params if any.
    // For simplicity, let's just set the variable. 
    // The user might refresh, but the cookie will take precedence if we don't clear GET.
    // Actually, if GET is present, it overrides cookie in this logic.
    // So if user clicks "French", GET=fr. Page loads in French. Cookie set to French.
    // Next navigation, no GET, Cookie is French.
}

// Load translations
$translations = [];
$langFile = __DIR__ . '/../lang/' . $lang . '.php';

if (file_exists($langFile)) {
    $translations = require $langFile;
}

function reload_language($conn) {
    global $lang, $translations;
    // Only if user hasn't set a preference via cookie or GET
    if (!isset($_COOKIE['lang']) && !isset($_GET['lang'])) {
         $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'system_language'");
         if ($res && $res->num_rows > 0) {
            $sysLang = $res->fetch_assoc()['setting_value'];
            if ($sysLang && in_array($sysLang, ['ar', 'fr']) && $sysLang !== $lang) {
                $lang = $sysLang;
                $langFile = __DIR__ . '/../lang/' . $lang . '.php';
                if (file_exists($langFile)) {
                    $translations = require $langFile;
                }
            }
         }
    }
}

// Try to run it immediately if $conn exists (e.g. in api.php)
if (isset($conn) && $conn instanceof mysqli) {
    reload_language($conn);
}

function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

function get_locale() {
    global $lang;
    return $lang;
}

function get_dir() {
    global $lang;
    return $lang === 'ar' ? 'rtl' : 'ltr';
}
?>
