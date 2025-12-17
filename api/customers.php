<?php
/**
 * Customers API Endpoint
 *
 * NOTE: Adjust the SQL below (table/view name and column names) to match your Oracle schema.
 */
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

// Require login for API access
requireLogin();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            /**
             * Example query:
             *  - Replace BI_CUSTOMERS_VIEW with your real table or view
             *  - Replace column names/aliases so they match your database structure
             */
            $sql = "
                SELECT
                    CUSTOMER_ID      AS id,
                    CUSTOMER_NAME    AS name,
                    CUSTOMER_HANDLE  AS handle,
                    LOAN_ID          AS loan_id,
                    EMAIL            AS email,
                    ACCOUNT_NUMBER   AS account_number,
                    STATUS           AS status
                FROM BI_CUSTOMERS_VIEW
                FETCH FIRST 100 ROWS ONLY
            ";

            $rows = oracleFetchAll($sql);

            echo json_encode([
                'success' => true,
                'data' => $rows,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to load customers from Oracle',
                'error'   => $e->getMessage(),
            ]);
        }
        break;

    case 'POST':
        // In a real app, you would insert into Oracle here (using INSERT ...).
        $data = json_decode(file_get_contents('php://input'), true);

        echo json_encode([
            'success' => true,
            'message' => 'Demo only: implement INSERT into Oracle here if needed',
            'data'    => $data,
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed',
        ]);
        break;
}

