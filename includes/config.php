<?php
// Database Configuration
define('DB_HOST', 'YOUR_MYSQLHOST');
define('DB_USER', 'YOUR_MYSQLUSER');
define('DB_PASS', 'YOUR_MYSQLPASSWORD');
define('DB_NAME', 'YOUR_MYSQLDATABASE');
define('DB_PORT', YOUR_MYSQLPORT);

// Fine per day in currency
define('FINE_PER_DAY', 2.00);
// Loan duration in days
define('LOAN_DAYS', 14);
// Site Configuration
define('SITE_NAME', 'LibraNova');
define('SITE_URL', '/');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false, 
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Helper: Check if logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
// Helper: Check if admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}
// Helper: Redirect
function redirect($url) {
    header("Location: $url");
    exit();
}
// Helper: Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}
// Helper: Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
