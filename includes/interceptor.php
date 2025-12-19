<?php
/**
 * ==========================================================================
 * Helper / Interceptor Functions
 * ==========================================================================
 * 
 * Authentication, authorization, and utility functions for the application.
 * Moved from includes/functions.php
 * ==========================================================================
 */

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Require login - redirect to login page if not logged in
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login');
    }
}

/**
 * Get base URL with optional path
 * 
 * @param string $path Optional path to append to base URL
 * @return string Full URL
 */
function baseUrl($path = '') {
    return BASE_URL . $path;
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    // If URL starts with /, prepend BASE_URL
    if (strpos($url, '/') === 0) {
        $url = BASE_URL . $url;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Sanitize input data
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Flash message system
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

/**
 * Get and clear flash message
 * 
 * @return array|null Array with 'type' and 'message' keys, or null if no message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['type' => $type, 'message' => $message];
    }
    return null;
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Format date string
 * 
 * @param string $date Date string to format
 * @param string $format Date format (default: 'Y-m-d')
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

/**
 * Generate CSRF token for form protection
 * 
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if token is valid, false otherwise
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate email format
 * 
 * @param string $email Email address to validate
 * @return bool True if valid email, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * 
 * @param string $password Password to validate
 * @param int $minLength Minimum password length (default: 6)
 * @return bool True if password meets requirements, false otherwise
 */
function validatePassword($password, $minLength = 6) {
    if (strlen($password) < $minLength) {
        return false;
    }
    return true;
}

/**
 * Log error to file
 * 
 * @param string $message Error message
 * @param array $context Additional context data
 * @return void
 */
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/app-errors.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    $logMessage = "[{$timestamp}] {$message}{$contextStr}\n";
    
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Get client IP address
 * 
 * @return string Client IP address or 'unknown'
 */
function getClientIp() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}


