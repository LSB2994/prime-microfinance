<?php
/**
 * ==========================================================================
 * Webill Token API Endpoint
 * ==========================================================================
 * 
 * Example endpoint to retrieve Webill access token.
 * This demonstrates how to use the Webill API helper functions.
 * ==========================================================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';
require_once __DIR__ . '/../includes/webill_api.php';
require_once __DIR__ . '/../includes/console_log.php';

// Set JSON response header
header('Content-Type: application/json');

// Check authentication (optional - remove if this should be public)
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// Get token
consoleInfo('Requesting Webill access token...');
$tokenData = getWebillAccessToken();

if ($tokenData) {
    consoleInfo('Webill access token retrieved successfully');
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'access_token' => $tokenData['access_token'],
            'token_type' => $tokenData['token_type'],
            'expires_in' => $tokenData['expires_in']
        ]
    ]);
} else {
    consoleError('Failed to retrieve Webill access token');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve access token'
    ]);
}

