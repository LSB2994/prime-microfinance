<?php
/**
 * Oracle database helper functions (PDO OCI version)
 */

require_once __DIR__ . '/../config.php';

/**
 * Get (and reuse) a single Oracle PDO connection.
 *
 * @return PDO
 * @throws Exception
 */
function getOracleConnection()
{
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    if (!class_exists('PDO')) {
        throw new Exception('PDO extension is not enabled in PHP. Please enable PDO in php.ini.');
    }

    $username = ORACLE_USERNAME;
    $password = ORACLE_PASSWORD;
    $connectString = ORACLE_CONNECT_STRING; // e.g. 192.168.168.2:1521/PRIMEUATO9

    // Build PDO OCI DSN
    $dsn = 'oci:dbname=//' . $connectString . ';charset=AL32UTF8';

    try {
        $conn = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        throw new Exception('Failed to connect to Oracle via PDO: ' . $e->getMessage());
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

    $stmt = $conn->prepare($sql);

    foreach ($params as $name => $value) {
        // Ensure parameter names start with colon
        $paramName = $name[0] === ':' ? $name : ':' . $name;
        $stmt->bindValue($paramName, $value);
    }

    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normalize keys to lower-case for easier use in PHP/JSON
    $normalizedRows = [];
    foreach ($rows as $row) {
        $normalized = [];
        foreach ($row as $key => $val) {
            $normalized[strtolower($key)] = $val;
        }
        $normalizedRows[] = $normalized;
    }

    return $normalizedRows;
}


