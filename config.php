<?php
/**
 * Application Configuration
 */

// Application Settings
define('APP_NAME', 'PRIME Micro finance');
define('APP_VERSION', '1.0.0');
// For PHP built-in server (php -S localhost:8000 router.php) use empty BASE_URL so paths are relative to the host.
// If you deploy under a subfolder (e.g. XAMPP: http://localhost/prime-microfinance), change this to that base URL.
define('BASE_URL', '');

// Database Configuration (if needed)
define('DB_HOST', 'localhost');
define('DB_NAME', 'prime_microfinance');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Oracle Database Configuration
define('ORACLE_USERNAME', 'BI');
define('ORACLE_PASSWORD', 'BI');
define('ORACLE_CONNECT_STRING', '192.168.168.2:1521/PRIMEUATO9'); // host:port/service

// Session Configuration
define('SESSION_LIFETIME', 28800); // 8 hours
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Paths
define('ROOT_PATH', __DIR__);
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('INCLUDES_PATH', ROOT_PATH . '/includes');

// Security
define('ENCRYPTION_KEY', 'your-secret-key-change-this-in-production');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

