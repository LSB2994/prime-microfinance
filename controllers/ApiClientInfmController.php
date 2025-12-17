<?php
/**
 * ApiClientInfmController
 *
 * MVC-style controller for client_infm backdate API.
 * Route: GET /api/client_infm
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';
require_once __DIR__ . '/../models/ClientInfm.php';

header('Content-Type: application/json');

// Only allow GET for list
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
    ]);
    exit;
}

try {
    $rows = ClientInfm::getAll();

    echo json_encode([
        'success' => true,
        'data'    => $rows,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load client_infm data from Oracle',
        'error'   => $e->getMessage(),
    ]);
}


