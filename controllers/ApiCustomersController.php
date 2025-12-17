<?php
/**
 * ApiCustomersController
 *
 * MVC-style controller for customers list API.
 * Route: GET /api/customers
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';
require_once __DIR__ . '/../models/Customer.php';

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
    $rows = Customer::getAll();

    echo json_encode([
        'success' => true,
        'data'    => $rows,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load customers from Oracle',
        'error'   => $e->getMessage(),
    ]);
}


