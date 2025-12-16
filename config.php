<?php
/**
 * Application Configuration
 */

// Application Settings
define('APP_NAME', 'PRIME Micro finance');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/kosign/prime-microfinance');

// Database Configuration (if needed)      
define('DB_HOST', 'localhost');
define('DB_NAME', 'prime_microfinance');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
session_set_cookie_params(SESSION_LIFETIME);

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

