<?php
/**
 * ==========================================================================
 * Simple Router for PHP Built-in Server
 * ==========================================================================
 * 
 * This file handles routing when using: php -S localhost:8000 router.php
 * It also acts as an interceptor to enforce authentication rules.
 * ==========================================================================
 */

// Error handling for required files
// Note: If these fail, we still want to try to route API requests
$configLoaded = false;
$interceptorLoaded = false;

try {
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
        $configLoaded = true;
    }
} catch (Throwable $e) {
    error_log("Router WARNING: Failed to load config.php: " . $e->getMessage());
}

try {
    if (file_exists(__DIR__ . '/includes/interceptor.php')) {
        require_once __DIR__ . '/includes/interceptor.php';
        $interceptorLoaded = true;
    }
} catch (Throwable $e) {
    error_log("Router WARNING: Failed to load interceptor.php: " . $e->getMessage());
}

$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Remove query string for routing (PHP automatically populates $_GET from query string)
$uri = parse_url($request_uri, PHP_URL_PATH);

// Remove base path if running in subdirectory
$base_path = dirname($script_name);
if ($base_path !== '/') {
    $uri = str_replace($base_path, '', $uri);
}

// Early debug logging for ALL requests to verify router is being called
error_log("=== ROUTER CALLED ===");
error_log("REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));
error_log("REQUEST_URI: $request_uri");
error_log("SCRIPT_NAME: $script_name");
error_log("base_path: $base_path");
error_log("final_uri: $uri");

// Debug logging for API routes
if (strpos($uri, '/api/') === 0) {
    error_log("Router Debug: request_uri=$request_uri, script_name=$script_name, base_path=$base_path, final_uri=$uri");
}

    // PRIORITY 1: Check if it's an API route FIRST (before other checks)
    // This ensures API routes are handled before static files or other routes
    if (strpos($uri, '/api/') === 0) {
        // Special test endpoint to verify router is working
        if ($uri === '/api/test') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Router is working!',
                'uri' => $uri,
                'request_uri' => $request_uri,
                '__DIR__' => __DIR__,
                'api_dir_exists' => is_dir(__DIR__ . '/api'),
                'api_files' => is_dir(__DIR__ . '/api') ? array_diff(scandir(__DIR__ . '/api'), ['.', '..']) : []
            ]);
            exit;
        }
        
        // CRITICAL: Log that we're handling an API route
        error_log("=== ROUTER HANDLING API ROUTE: $uri ===");
        // Extract API endpoint (e.g., /api/webill-token -> api/webill-token.php)
        // Handle both /api/endpoint and /api/endpoint.php
        $apiPath = substr($uri, 5); // Remove '/api/' prefix
        
        // Remove any leading slashes that might remain
        $apiPath = ltrim($apiPath, '/');
        
        // Normalize path separators for Windows compatibility
        $apiPath = str_replace('\\', '/', $apiPath);
        
        // If path doesn't end with .php, add it
        if (substr($apiPath, -4) !== '.php') {
            $apiPath = $apiPath . '.php';
        }
        
        // Normalized version for consistent path building
        $apiPathNormalized = str_replace('\\', '/', $apiPath);
    
    // Build file path - use the simplest, most direct approach first
    // PHP's file_exists() handles both forward and backslashes on Windows
    $apiDir = __DIR__ . '/api';
    
    // Ensure API directory exists
    if (!is_dir($apiDir)) {
        error_log("Router ERROR: API directory does not exist: $apiDir");
        $apiFile = null;
    } else {
        // Build the most direct path first - this should work on Windows
        // Use the normalized path we created earlier
        $directPath = $apiDir . '/' . $apiPathNormalized;
        
        // Try the direct path first (most common case)
        if (file_exists($directPath)) {
            $apiFile = $directPath;
        } else {
            // Try with realpath to normalize
            $realPath = realpath($directPath);
            if ($realPath !== false && file_exists($realPath)) {
                $apiFile = $realPath;
            } else {
                // Try alternative path formats
                $possiblePaths = [
                    $apiDir . DIRECTORY_SEPARATOR . $apiPath,
                    realpath($apiDir) . DIRECTORY_SEPARATOR . $apiPath,
                    realpath($apiDir) . '/' . $apiPath,
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $apiFile = $path;
                        break;
                    }
                }
            }
        }
    }
    
    // Debug logging - CRITICAL for troubleshooting
    error_log("=== API ROUTE DEBUG START ===");
    error_log("Router API check (PRIORITY): URI=$uri, apiPath=$apiPath");
    error_log("Router API check: Primary path: " . __DIR__ . '/api/' . $apiPath);
    error_log("Router API check: Direct path exists: " . (file_exists(__DIR__ . '/api/' . $apiPath) ? 'YES' : 'NO'));
    error_log("Router API check: Found file: " . ($apiFile ? $apiFile : 'NONE'));
    error_log("Router API check: __DIR__=" . __DIR__);
    error_log("Router API check: api directory exists: " . (is_dir(__DIR__ . '/api') ? 'YES' : 'NO'));
    if (is_dir(__DIR__ . '/api')) {
        $apiFiles = array_diff(scandir(__DIR__ . '/api'), ['.', '..']);
        error_log("Router API check: api directory contents: " . implode(', ', $apiFiles));
        error_log("Router API check: Looking for: $apiPath");
        error_log("Router API check: File in directory: " . (in_array($apiPath, $apiFiles) ? 'YES' : 'NO'));
    }
    error_log("=== API ROUTE DEBUG END ===");
    
    // Check if API file exists - try direct path check as fallback
    if (!$apiFile || !file_exists($apiFile)) {
        // Last resort: try the most direct path possible with normalized separators
        $directCheck = __DIR__ . '/api/' . $apiPathNormalized;
        if (file_exists($directCheck)) {
            $apiFile = $directCheck;
            error_log("Router: Found file using direct check: $apiFile");
        } else {
            // Try with realpath for absolute path resolution
            $realCheck = realpath(__DIR__ . '/api') . DIRECTORY_SEPARATOR . $apiPathNormalized;
            if ($realCheck && file_exists($realCheck)) {
                $apiFile = $realCheck;
                error_log("Router: Found file using realpath check: $apiFile");
            }
        }
    }
    
    if ($apiFile && file_exists($apiFile)) {
        // API routes require authentication (only if interceptor is loaded)
        if ($interceptorLoaded && function_exists('isLoggedIn') && !isLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            exit;
        }
        
        error_log("Router: Executing API file: $apiFile");
        try {
            require_once $apiFile;
            exit;
        } catch (Throwable $e) {
            error_log("Router ERROR: Exception while executing API file: " . $e->getMessage());
            error_log("Router ERROR: Stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Server error while executing API endpoint',
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // If not found in first check, try direct path
    $directPath = substr($uri, 1); // Remove leading slash: /api/file.php -> api/file.php
    $directPath = str_replace('\\', '/', $directPath);
    $directFilePaths = [
        __DIR__ . '/' . $directPath,
        __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $directPath),
        __DIR__ . '\\' . str_replace('/', '\\', $directPath),
    ];
    
    $directFile = null;
    foreach ($directFilePaths as $path) {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (file_exists($normalized)) {
            $directFile = $normalized;
            break;
        }
        $realPath = realpath($normalized);
        if ($realPath !== false && file_exists($realPath)) {
            $directFile = $realPath;
            break;
        }
    }
    
    if ($directFile && file_exists($directFile)) {
        if ($interceptorLoaded && function_exists('isLoggedIn') && !isLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            exit;
        }
        error_log("Router: Executing direct API file: $directFile");
        try {
            require_once $directFile;
            exit;
        } catch (Throwable $e) {
            error_log("Router ERROR: Exception while executing direct API file: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Server error while executing API endpoint',
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    // If still not found, return JSON 404 with detailed debug info
    $apiDirExists = is_dir(__DIR__ . '/api');
    $apiDirContents = $apiDirExists ? implode(', ', array_diff(scandir(__DIR__ . '/api'), ['.', '..'])) : 'N/A';
    $expectedFile = __DIR__ . '/api/' . $apiPath;
    $fileExists = file_exists($expectedFile);
    
    error_log("Router: API file not found after all checks. URI: $uri");
    error_log("Router: API directory exists: " . ($apiDirExists ? 'YES' : 'NO'));
    error_log("Router: API directory contents: " . $apiDirContents);
    error_log("Router: Expected file path: $expectedFile");
    error_log("Router: Expected file exists: " . ($fileExists ? 'YES' : 'NO'));
    error_log("Router: SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A'));
    error_log("Router: Is router being used: " . (strpos($_SERVER['SCRIPT_NAME'] ?? '', 'router.php') !== false ? 'YES' : 'NO'));
    
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'API endpoint not found',
        'uri' => $uri,
        'hint' => $fileExists ? 'File exists but router may not be handling it correctly. Ensure server is started with: php -S localhost:8000 router.php' : 'File does not exist at expected path.',
        'debug' => [
            'apiPath' => $apiPath,
            'expected_file' => $expectedFile,
            'file_exists' => $fileExists,
            'directPath' => $directPath ?? 'N/A',
            '__DIR__' => __DIR__,
            'api_dir_exists' => $apiDirExists,
            'api_dir_contents' => $apiDirExists ? array_diff(scandir(__DIR__ . '/api'), ['.', '..']) : null,
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
            'router_in_use' => strpos($_SERVER['SCRIPT_NAME'] ?? '', 'router.php') !== false,
        ]
    ]);
    exit;
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
            // Page route: redirect to login if not logged in
            requireLogin();
        }

        require_once $filePath;
        exit;
    }
}

// Check if it's a static file (CSS, JS, images, etc.)
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $uri)) {
    return false; // Let PHP serve the file directly
}


// Allow direct access to data reader files in root (loan-schedule.php only)
// QR collection is now in /api/ folder
$allowedRootFiles = ['/loan-schedule.php'];
if (in_array($uri, $allowedRootFiles, true)) {
    $filePath = __DIR__ . $uri;
    
    // Try multiple path formats
    if (!file_exists($filePath)) {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . ltrim($uri, '/');
    }
    
    if (file_exists($filePath)) {
        // These files require authentication
        if (!isLoggedIn()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            exit;
        }
        
        // Start output buffering and clear any previous output
        if (ob_get_level() > 0) {
            ob_clean();
        }
        ob_start();
        
        // Include the file
        require_once $filePath;
        
        // Get the output and ensure it's JSON
        $output = ob_get_clean();
        
        // If output doesn't start with JSON, there might be an error
        if (!empty($output) && (substr(trim($output), 0, 1) !== '{' && substr(trim($output), 0, 1) !== '[')) {
            // Output might be HTML/error, log it
            error_log("Non-JSON output from {$uri}: " . substr($output, 0, 200));
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Server error: Invalid response format',
                'error' => 'Response was not valid JSON'
            ]);
        } else {
            // Output the response
            echo $output;
        }
        exit;
    }
}

// 404 - Route not found
http_response_code(404);
echo "404 - Page Not Found";
exit;

