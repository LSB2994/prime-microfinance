<?php
/**
 * ==========================================================================
 * QR Collection API Endpoint
 * ==========================================================================
 * 
 * Generates QR code for customer payment using Webill API
 * ==========================================================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';
require_once __DIR__ . '/../includes/webill_api.php';
require_once __DIR__ . '/../includes/console_log.php';

// Set JSON response header
header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

// ==========================================================================
// STEP 1: Receive and parse request
// ==========================================================================
$step1Data = [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'N/A',
    'has_get' => !empty($_GET),
    'has_post' => !empty($_POST)
];
logError('QR Collection - Step 1: Request received', $step1Data);
consoleInfo(['QR Collection - Step 1: Request received', $step1Data]);

// Get parameters from JSON POST body or GET
$jsonInput = file_get_contents('php://input');
$postData = [];
if (!empty($jsonInput)) {
    $postData = json_decode($jsonInput, true) ?? [];
    $step1_1Data = [
        'json_length' => strlen($jsonInput),
        'parsed_data' => $postData
    ];
    logError('QR Collection - Step 1.1: JSON body parsed', $step1_1Data);
    consoleInfo(['QR Collection - Step 1.1: JSON body parsed', $step1_1Data]);
} else {
    $step1_1Data = ['get_params' => $_GET];
    logError('QR Collection - Step 1.1: No JSON body, using GET parameters', $step1_1Data);
    consoleInfo(['QR Collection - Step 1.1: No JSON body, using GET parameters', $step1_1Data]);
}

$payerName = $postData['payer_name'] ?? $_GET['payer_name'] ?? '';
$parentAccountNo = $postData['parent_account_no'] ?? $_GET['parent_account_no'] ?? '';
$paymentType = $postData['payment_type'] ?? $_GET['payment_type'] ?? '0';
$currencyCode = $postData['currency_code'] ?? $_GET['currency_code'] ?? 'USD';
$amount = $postData['amount'] ?? $_GET['amount'] ?? 0;
$remark = $postData['remark'] ?? $_GET['remark'] ?? '';
$khqrName = $postData['khqr_name'] ?? $_GET['khqr_name'] ?? '';
$requestId = $postData['request_id'] ?? $_GET['request_id'] ?? uniqid('req_', true);

// ==========================================================================
// STEP 2: Validate parameters
// ==========================================================================
$step2Data = [
    'payer_name' => $payerName,
    'parent_account_no' => $parentAccountNo,
    'payment_type' => $paymentType,
    'currency_code' => $currencyCode,
    'amount' => $amount,
    'remark' => $remark,
    'khqr_name' => $khqrName,
    'request_id' => $requestId
];
logError('QR Collection - Step 2: Parameters extracted', $step2Data);
consoleInfo(['QR Collection - Step 2: Parameters extracted', $step2Data]);

// Validate required parameters
if (empty($payerName) || empty($parentAccountNo)) {
    logError('QR Collection - Step 2.1: Validation failed', [
        'payer_name_empty' => empty($payerName),
        'parent_account_no_empty' => empty($parentAccountNo)
    ]);
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'payer_name and parent_account_no are required'
    ]);
    exit;
}

logError('QR Collection - Step 2.1: Validation passed');
consoleInfo('QR Collection - Step 2.1: Validation passed');

// ==========================================================================
// STEP 3: Prepare request data for Webill API
// ==========================================================================
$requestData = [
    'payer_name' => $payerName,
    'parent_account_no' => $parentAccountNo,
    'payment_type' => $paymentType,
    'currency_code' => $currencyCode,
    'amount' => (float)$amount,
    'remark' => $remark,
    'khqr_name' => $khqrName,
    'request_id' => $requestId
];

$step3Data = [
    'endpoint' => '/api/wbi/client/v1/qr-collections',
    'method' => 'POST',
    'request_data' => $requestData,
    'full_url' => WEBILL_BASE_URL . '/api/wbi/client/v1/qr-collections'
];
logError('QR Collection - Step 3: Request data prepared for Webill API', $step3Data);
consoleInfo(['QR Collection - Step 3: Request data prepared for Webill API', $step3Data]);

// ==========================================================================
// STEP 4: Call Webill API
// ==========================================================================
logError('QR Collection - Step 4: Calling Webill API...');
consoleInfo('QR Collection - Step 4: Calling Webill API...');
$response = webillApiRequest('/api/wbi/client/v1/qr-collections', 'POST', $requestData);

$step4_1Data = [
    'response_received' => $response !== false,
    'response_structure' => $response ? [
        'has_success' => isset($response['success']),
        'has_data' => isset($response['data']),
        'status_code' => $response['status_code'] ?? 'N/A',
        'response_keys' => $response ? array_keys($response) : []
    ] : 'No response'
];
logError('QR Collection - Step 4.1: Webill API response received', $step4_1Data);
consoleInfo(['QR Collection - Step 4.1: Webill API response received', $step4_1Data]);

// ==========================================================================
// STEP 5: Process Webill API response
// ==========================================================================
if ($response && $response['success'] && isset($response['data'])) {
    $step5Data = [
        'response_data_structure' => [
            'has_data' => isset($response['data']),
            'data_type' => gettype($response['data']),
            'data_keys' => is_array($response['data']) ? array_keys($response['data']) : 'N/A'
        ]
    ];
    logError('QR Collection - Step 5: Processing successful response', $step5Data);
    consoleInfo(['QR Collection - Step 5: Processing successful response', $step5Data]);
    
    // Check if response has data field
    $responseData = $response['data'];
    if (isset($responseData['data'])) {
        // Response structure: { data: { data: {...} } }
        $qrData = $responseData['data'];
        $step5_1Data = [
            'has_khqr_data_base64' => isset($qrData['khqr_data_base64']),
            'qr_data_keys' => array_keys($qrData)
        ];
        logError('QR Collection - Step 5.1: Found nested data structure', $step5_1Data);
        consoleInfo(['QR Collection - Step 5.1: Found nested data structure', $step5_1Data]);
    } else {
        // Response structure: { data: {...} }
        $qrData = $responseData;
        $step5_1Data = [
            'has_khqr_data_base64' => isset($qrData['khqr_data_base64']),
            'qr_data_keys' => array_keys($qrData)
        ];
        logError('QR Collection - Step 5.1: Using direct data structure', $step5_1Data);
        consoleInfo(['QR Collection - Step 5.1: Using direct data structure', $step5_1Data]);
    }
    
    // ==========================================================================
    // STEP 6: Return success response
    // ==========================================================================
    $step6Data = [
        'has_qr_code' => isset($qrData['khqr_data_base64']),
        'qr_code_length' => isset($qrData['khqr_data_base64']) ? strlen($qrData['khqr_data_base64']) : 0
    ];
    logError('QR Collection - Step 6: Returning success response', $step6Data);
    consoleInfo(['QR Collection - Step 6: Returning success response', $step6Data]);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $qrData
    ]);
} else {
    // ==========================================================================
    // STEP 6: Handle error response
    // ==========================================================================
    $errorMessage = 'Unknown error';
    if ($response && isset($response['data'])) {
        if (isset($response['data']['message'])) {
            $errorMessage = $response['data']['message'];
        } elseif (isset($response['data']['status']['message'])) {
            $errorMessage = $response['data']['status']['message'];
        }
    }
    
    $step6ErrorData = [
        'error_message' => $errorMessage,
        'response_structure' => $response ? [
            'success' => $response['success'] ?? 'N/A',
            'status_code' => $response['status_code'] ?? 'N/A',
            'has_data' => isset($response['data']),
            'data_content' => $response['data'] ?? 'N/A'
        ] : 'No response received'
    ];
    logError('QR Collection - Step 6: Returning error response', $step6ErrorData);
    consoleError(['QR Collection - Step 6: Returning error response', $step6ErrorData]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate QR code',
        'error' => $errorMessage
    ]);
}
