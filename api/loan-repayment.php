<?php
/**
 * Loan Repayment API Endpoint
 *
 * NOTE: Adjust the SQL below (table/view name and column names) to match your Oracle schema.
 */
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Require login
requireLogin();

// Get loan ID from query string or POST
$loan_id = $_GET['loan_id'] ?? $_POST['loan_id'] ?? null;

if (!$loan_id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Loan ID is required',
    ]);
    exit;
}

try {
    /**
     * Example query:
     *  - Replace BI_LOAN_REPAYMENT_VIEW with your real table/view
     *  - Replace column names/aliases so they match your database structure
     */
    $sql = "
        SELECT
            LOAN_ID              AS loan_id,
            PAYMENT_DATE         AS payment_date,
            NUMBER_OF_DAYS       AS days_count,
            PRINCIPAL_AMOUNT     AS principal_amount,
            INTEREST_AMOUNT      AS interest_amount,
            TOTAL_AMOUNT         AS total_amount,
            PRINCIPAL_BALANCE    AS principal_balance,
            OTHER_INFO           AS other
        FROM BI_LOAN_REPAYMENT_VIEW
        WHERE LOAN_ID = :loan_id
        ORDER BY PAYMENT_DATE
    ";

    $rows = oracleFetchAll($sql, [':loan_id' => $loan_id]);

    echo json_encode([
        'success' => true,
        'loan_id' => $loan_id,
        'data'    => $rows,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load loan repayment data from Oracle',
        'error'   => $e->getMessage(),
    ]);
}

