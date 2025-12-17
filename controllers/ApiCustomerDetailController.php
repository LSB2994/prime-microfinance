<?php
/**
 * ApiCustomerDetailController
 *
 * MVC-style controller for single customer detail API.
 * Routes: GET /api/customer-detail?acno=...
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';
require_once __DIR__ . '/../models/Customer.php';

header('Content-Type: application/json');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
    ]);
    exit;
}

$acno = $_GET['acno'] ?? $_GET['loan_id'] ?? null;

if (!$acno) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Account number (acno) is required',
    ]);
    exit;
}

try {
    $customer = Customer::getByAcno($acno);

    if (!$customer) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found',
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'acno'    => $acno,
        'data'    => [$customer],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load customer detail from Oracle',
        'error'   => $e->getMessage(),
    ]);
}


