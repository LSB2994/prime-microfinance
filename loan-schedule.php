<?php
/**
 * ==========================================================================
 * Loan Schedule Data Reader (Database Query - NOT an API)
 * ==========================================================================
 * 
 * Reads loan repayment schedule data from database for a given ACNO
 * This is just a database query, not an API endpoint
 * ==========================================================================
 */

// Start output buffering to prevent any HTML output
ob_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/interceptor.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

// Clear any output and set JSON response header
ob_clean();
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

// Get ACNO from query parameter
$acno = $_GET['acno'] ?? '';

if (empty($acno)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ACNO parameter is required'
    ]);
    exit;
}

$scheduleRows = [];
$scheduleError = null;
$summary = [
    'totalDays' => 0,
    'totalPrincipal' => 0,
    'totalInterest' => 0,
    'totalAmount' => 0,
    'initialBalance' => 0
];

try {
    // Read schedule data from database
    $sql = "SELECT 
                DUENO,
                DAYNAME,
                DUEDATE,
                PERIOD,
                PRINCIPAL,
                INTEREST,
                TOTALAMOUNT,
                BL
            FROM VW_CREDIT_SCHEDULE
            WHERE ACNO = :acno
            ORDER BY DUENO";
    
    $scheduleRows = oracleFetchAll($sql, ['acno' => $acno]);
    
    // Calculate summary totals
    foreach ($scheduleRows as $row) {
        if (isset($row['period']) && is_numeric($row['period'])) {
            $summary['totalDays'] += (int)$row['period'];
        }
        if (isset($row['principal']) && is_numeric($row['principal'])) {
            $summary['totalPrincipal'] += (float)$row['principal'];
        }
        if (isset($row['interest']) && is_numeric($row['interest'])) {
            $summary['totalInterest'] += (float)$row['interest'];
        }
        if (isset($row['totalamount']) && is_numeric($row['totalamount'])) {
            $summary['totalAmount'] += (float)$row['totalamount'];
        }
    }
    
    // Get initial balance (first row's BL + first PRINCIPAL)
    if (!empty($scheduleRows) && isset($scheduleRows[0]['bl']) && is_numeric($scheduleRows[0]['bl'])) {
        $firstPrincipal = isset($scheduleRows[0]['principal']) && is_numeric($scheduleRows[0]['principal']) 
            ? (float)$scheduleRows[0]['principal'] 
            : 0;
        $firstBl = (float)$scheduleRows[0]['bl'];
        $summary['initialBalance'] = $firstBl + $firstPrincipal;
    }
    
    // Format dates and numbers for JSON response
    $formattedRows = [];
    foreach ($scheduleRows as $row) {
        $formattedRows[] = [
            'dueno' => $row['dueno'] ?? '',
            'dayname' => $row['dayname'] ?? '',
            'duedate' => formatScheduleDateForJson($row['duedate'] ?? ''),
            'period' => $row['period'] ?? '',
            'principal' => $row['principal'] ?? '',
            'interest' => $row['interest'] ?? '',
            'totalamount' => $row['totalamount'] ?? '',
            'bl' => $row['bl'] ?? ''
        ];
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'schedule' => $formattedRows,
            'summary' => $summary
        ]
    ]);
    
} catch (Exception $e) {
    $scheduleError = $e->getMessage();
    logError('Failed to fetch loan schedule', [
        'error' => $e->getMessage(),
        'acno' => $acno,
        'file' => __FILE__,
        'line' => $e->getLine()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch loan schedule',
        'error' => $scheduleError
    ]);
}

/**
 * Format date for JSON response (DD-MM-YY format)
 */
function formatScheduleDateForJson($dateString) {
    if (empty($dateString) || $dateString === null || $dateString === '') {
        return '';
    }
    try {
        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return $dateString;
        }
        $day = str_pad(date('d', $timestamp), 2, '0', STR_PAD_LEFT);
        $month = str_pad(date('m', $timestamp), 2, '0', STR_PAD_LEFT);
        $year = substr(date('Y', $timestamp), -2);
        return "{$day}-{$month}-{$year}";
    } catch (Exception $e) {
        return $dateString;
    }
}
