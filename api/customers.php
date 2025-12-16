<?php
/**
 * Customers API Endpoint
 */
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Require login for API access
requireLogin();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all customers
        $customers = [
            [
                'id' => 1,
                'name' => 'Olivia Rhye',
                'handle' => '@olivia',
                'loan_id' => '123456789000',
                'email' => 'olivia@untitled.com',
                'account_number' => '1253826438200',
                'status' => 'overdue'
            ],
            [
                'id' => 2,
                'name' => 'John Doe',
                'handle' => '@johndoe',
                'loan_id' => '987654321000',
                'email' => 'john@example.com',
                'account_number' => '9876543210000',
                'status' => 'pending'
            ],
            [
                'id' => 3,
                'name' => 'Jane Smith',
                'handle' => '@janesmith',
                'loan_id' => '456789123000',
                'email' => 'jane@example.com',
                'account_number' => '4567891230000',
                'status' => 'progress'
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $customers
        ]);
        break;
        
    case 'POST':
        // Create new customer
        $data = json_decode(file_get_contents('php://input'), true);
        
        // In production, validate and save to database
        echo json_encode([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $data
        ]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
        break;
}

