<?php
/**
 * Loan Repayment API Endpoint
 */
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Require login
requireLogin();

// Get loan ID from query string or POST
$loan_id = $_GET['loan_id'] ?? $_POST['loan_id'] ?? null;

if (!$loan_id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Loan ID is required'
    ]);
    exit;
}

// Get repayment data (in production, fetch from database)
$repayments = [
    [
        'payment_date' => '2024-01-15',
        'amount' => 1500.00,
        'status' => 'paid',
        'remarks' => 'On time',
        'other' => '-'
    ],
    [
        'payment_date' => '2024-02-15',
        'amount' => 1500.00,
        'status' => 'paid',
        'remarks' => 'On time',
        'other' => '-'
    ],
    [
        'payment_date' => '2024-03-15',
        'amount' => 1500.00,
        'status' => 'overdue',
        'remarks' => 'Late payment',
        'other' => '-'
    ],
    [
        'payment_date' => '2024-04-15',
        'amount' => 1500.00,
        'status' => 'pending',
        'remarks' => 'Due soon',
        'other' => '-'
    ]
];

echo json_encode([
    'success' => true,
    'loan_id' => $loan_id,
    'data' => $repayments
]);

