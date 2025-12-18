<?php
/**
 * Test database connection for different environments
 * 
 * Usage:
 *   php scripts/test-db-connection.php                    # Test current environment
 *   APP_ENV=development php scripts/test-db-connection.php
 *   APP_ENV=uat php scripts/test-db-connection.php
 *   APP_ENV=production php scripts/test-db-connection.php
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Database Connection Test ===\n\n";
echo "Environment: " . APP_ENV . "\n";
echo "Database Host: " . ORACLE_CONNECT_STRING . "\n";
echo "Username: " . ORACLE_USERNAME . "\n";
echo "\n";

try {
    // Test connection
    $conn = getOracleConnection();
    
    if ($conn) {
        echo "✓ STATUS: SUCCESS\n";
        echo "✓ MESSAGE: Oracle connection established successfully\n\n";
        
        // Test a simple query
        try {
            $testQuery = "SELECT SYSDATE FROM DUAL";
            $result = oracleFetchAll($testQuery);
            
            if (!empty($result)) {
                echo "✓ Query Test: SUCCESS\n";
                echo "  Server Date/Time: " . ($result[0]['sysdate'] ?? 'N/A') . "\n";
            }
        } catch (Exception $e) {
            echo "⚠ Query Test: FAILED\n";
            echo "  Error: " . $e->getMessage() . "\n";
        }
        
        // Test access to the main view
        try {
            $viewTest = "SELECT COUNT(*) as total FROM BI.CTM_INFOR_V1";
            $viewResult = oracleFetchAll($viewTest);
            
            if (!empty($viewResult)) {
                echo "✓ View Access Test: SUCCESS\n";
                echo "  Total Records in CTM_INFOR_V1: " . ($viewResult[0]['total'] ?? 'N/A') . "\n";
            }
        } catch (Exception $e) {
            echo "⚠ View Access Test: FAILED\n";
            echo "  Error: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "✗ STATUS: FAILED\n";
        echo "✗ MESSAGE: Connection returned null\n";
    }
    
} catch (Exception $e) {
    echo "✗ STATUS: FAILED\n";
    echo "✗ MESSAGE: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Troubleshooting:\n";
    echo "1. Check if OCI8 extension is enabled: php -m | grep oci8\n";
    echo "2. Verify network connectivity to: " . ORACLE_CONNECT_STRING . "\n";
    echo "3. Check firewall rules\n";
    echo "4. Verify database credentials\n";
    echo "5. Check Oracle TNS configuration if needed\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";

