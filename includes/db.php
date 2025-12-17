<?php
/**
 * Oracle database helper functions
 */

require_once __DIR__ . '/../config.php';

/**
 * Get (and reuse) a single Oracle connection.
 *
 * @return resource
 * @throws Exception
 */
function getOracleConnection()
{
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    if (!function_exists('oci_connect')) {
        throw new Exception('OCI8 extension is not enabled in PHP. Please enable oci8 in php.ini.');
    }

    $username = ORACLE_USERNAME;
    $password = ORACLE_PASSWORD;
    $connectString = ORACLE_CONNECT_STRING;

    $conn = @oci_connect($username, $password, $connectString, 'AL32UTF8');

    if (!$conn) {
        $e = oci_error();
        throw new Exception('Failed to connect to Oracle: ' . ($e['message'] ?? 'Unknown error'));
    }

    return $conn;
}

/**
 * Execute a SELECT query and return all rows as an array of associative arrays.
 *
 * @param string $sql
 * @param array $params
 * @return array
 * @throws Exception
 */
function oracleFetchAll(string $sql, array $params = []): array
{
    $conn = getOracleConnection();

    $stid = oci_parse($conn, $sql);
    if (!$stid) {
        $e = oci_error($conn);
        throw new Exception('Failed to prepare Oracle statement: ' . ($e['message'] ?? 'Unknown error'));
    }

    foreach ($params as $name => $value) {
        // Ensure parameter names start with colon
        $paramName = $name[0] === ':' ? $name : ':' . $name;
        oci_bind_by_name($stid, $paramName, $params[$name]);
    }

    $r = oci_execute($stid);
    if (!$r) {
        $e = oci_error($stid);
        throw new Exception('Failed to execute Oracle query: ' . ($e['message'] ?? 'Unknown error'));
    }

    $rows = [];
    // Use OCI_RETURN_LOBS so LOB columns (e.g. CLOB) are returned as strings
    while (($row = oci_fetch_array($stid, OCI_ASSOC | OCI_RETURN_LOBS)) !== false) {
        // Normalize keys to lower-case for easier use in PHP/JSON
        $normalized = [];
        foreach ($row as $key => $val) {
            $normalized[strtolower($key)] = $val;
        }
        $rows[] = $normalized;
    }

    oci_free_statement($stid);

    return $rows;
}


