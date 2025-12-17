<?php
require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    // Only test the Oracle connection and log simple status
    getOracleConnection();

    echo "STATUS: SUCCESS\n";
    echo "MESSAGE: Oracle connection OK\n";
} catch (Exception $e) {
    http_response_code(500);
    echo "STATUS: FAIL\n";
    echo "MESSAGE: " . $e->getMessage() . "\n";
}