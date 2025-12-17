<?php
/**
 * Simple Router for PHP Built-in Server
 * This file handles routing when using: php -S localhost:8000 router.php
 * It also acts as an interceptor to enforce authentication rules.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/interceptor.php';

$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove query string
$uri = parse_url($request_uri, PHP_URL_PATH);

// Remove base path if running in subdirectory
$base_path = dirname($script_name);
if ($base_path !== '/') {
    $uri = str_replace($base_path, '', $uri);
}

// Route definitions (only clean URLs, MVC-style)
$routes = [
    '/' => 'index.php',
    '/login' => 'views/login.php',
    // No registration feature
    '/customer' => 'views/customer.php',
    '/logout' => 'views/logout.php',
];

// Check if route exists
if (isset($routes[$uri])) {
    $file = $routes[$uri];

    // Resolve to absolute path relative to this router file
    $filePath = __DIR__ . '/' . ltrim($file, '/');

    // Check if file exists
    if (file_exists($filePath)) {
        // Interceptor: enforce auth for protected routes
        $publicRoutes = ['/', '/login', '/registration', '/register'];

        if (!in_array($uri, $publicRoutes, true)) {
            if (strpos($uri, '/api/') === 0) {
                // API route: return JSON 401 if not logged in
                if (!isLoggedIn()) {
                    header('Content-Type: application/json');
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Unauthorized',
                    ]);
                    exit;
                }
            } else {
                // Page route: redirect to login if not logged in
                requireLogin();
            }
        }

        require_once $filePath;
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

