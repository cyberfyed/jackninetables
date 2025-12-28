<?php
session_start();

// Site configuration
define('SITE_NAME', 'Jack Nine Tables');
define('SITE_URL', 'http://localhost/jackninetablesnew');
define('SITE_EMAIL', 'info@jackninetables.com');

// PayPal configuration
define('PAYPAL_CLIENT_ID', 'AeRSvMBrV50JMm36B7ec2d-O8Bq2WiSX8fyRRss_1FljA33fpp-7Dy4yfUatMs8uWdIcNxEvxd67WaDf');
define('PAYPAL_SECRET', 'ELjyFhVba1FH23Q6VhtwSK_HE1-ZpOFbmUD7x2wv2SicBOu-rL9LIGvXkL-uk3CpTJ69tbr6IKQjxX9V');
define('PAYPAL_MODE', 'sandbox'); // Change to 'live' for production
define('DEPOSIT_PERCENTAGE', 25); // 25% deposit required

// Include database
require_once __DIR__ . '/database.php';

// Helper functions
function redirect($url) {
    header("Location: " . SITE_URL . "/$url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isEmailVerified() {
    return isLoggedIn() && ($_SESSION['email_verified'] ?? false);
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Please log in to access this page.';
        redirect('login.php');
    }
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['is_admin'] ?? false);
}

function requireAdmin() {
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to access this page.');
        redirect('login.php');
    }

    if (!isAdmin()) {
        setFlash('error', 'You do not have permission to access this area.');
        redirect('dashboard.php');
    }
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function setFlash($type, $message) {
    $_SESSION["flash_$type"] = $message;
}

function getFlash($type) {
    if (isset($_SESSION["flash_$type"])) {
        $message = $_SESSION["flash_$type"];
        unset($_SESSION["flash_$type"]);
        return $message;
    }
    return null;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function getCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}
