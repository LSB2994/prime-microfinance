<?php
/**
 * Simple Router for PHP Built-in Server
 * This file handles routing when using: php -S localhost:8000 router.php
 */

require_once 'config.php';

$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove query string
$uri = parse_url($request_uri, PHP_URL_PATH);

// Remove base path if running in subdirectory
$base_path = dirname($script_name);
if ($base_path !== '/') {
    $uri = str_replace($base_path, '', $uri);
}

// Route definitions
$routes = [
    '/' => 'index.php',
    '/index.php' => 'index.php',
    '/home' => 'views/landing.php',
    '/landing' => 'views/landing.php',
    '/login' => 'views/index.php',
    '/dashboard' => 'views/dashboard.php',
    '/dashboard.php' => 'views/dashboard.php',
    '/logout' => 'views/logout.php',
    '/logout.php' => 'views/logout.php',
    '/api/customers' => 'api/customers.php',
    '/api/customers.php' => 'api/customers.php',
    '/api/loan-repayment' => 'api/loan-repayment.php',
    '/api/loan-repayment.php' => 'api/loan-repayment.php',
];

// Check if route exists
if (isset($routes[$uri])) {
    $file = $routes[$uri];
    
    // Check if file exists
    if (file_exists($file)) {
        require_once $file;
        exit;
    }
}

// Check if it's a static file (CSS, JS, images, etc.)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    return false; // Let PHP serve the file directly
}

// 404 - Route not found
http_response_code(404);
echo "404 - Page Not Found";
exit;

