<?php
/**
 * ==========================================================================
 * Console Logging Utility
 * ==========================================================================
 * 
 * Provides console logging functionality for debugging.
 * Supports both server-side (PHP) and client-side (JavaScript) logging.
 * ==========================================================================
 */

/**
 * Console log for server-side (PHP)
 * Outputs to error log and optionally to browser console via JavaScript
 * 
 * @param mixed $data Data to log (can be string, array, object)
 * @param string $level Log level: 'log', 'info', 'warn', 'error', 'debug'
 * @param bool $outputToBrowser If true, also outputs JavaScript console.log
 * @return void
 */
function consoleLog($data, $level = 'log', $outputToBrowser = false)
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = '';
    
    // Format the data
    if (is_array($data) || is_object($data)) {
        $logMessage = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        $logMessage = (string)$data;
    }
    
    // Server-side logging
    $logPrefix = "[{$timestamp}] [{$level}]";
    error_log("{$logPrefix} {$logMessage}");
    
    // Browser console logging (if enabled)
    if ($outputToBrowser && !headers_sent()) {
        $jsData = json_encode($logMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $jsLevel = strtolower($level);
        
        echo "<script>";
        echo "console.{$jsLevel}({$jsData});";
        echo "</script>";
    }
}

/**
 * Console log with automatic browser output in development mode
 * 
 * @param mixed $data Data to log
 * @param string $level Log level
 * @return void
 */
function console($data, $level = 'log')
{
    $isEnabled = defined('CONSOLE_LOG_ENABLED') ? CONSOLE_LOG_ENABLED : (defined('APP_ENV') && APP_ENV !== 'production');
    consoleLog($data, $level, $isEnabled);
}

/**
 * Console info - informational message
 * 
 * @param mixed $data Data to log
 * @return void
 */
function consoleInfo($data)
{
    console($data, 'info');
}

/**
 * Console warn - warning message
 * 
 * @param mixed $data Data to log
 * @return void
 */
function consoleWarn($data)
{
    console($data, 'warn');
}

/**
 * Console error - error message
 * 
 * @param mixed $data Data to log
 * @return void
 */
function consoleError($data)
{
    console($data, 'error');
}

/**
 * Console debug - debug message (only in development)
 * 
 * @param mixed $data Data to log
 * @return void
 */
function consoleDebug($data)
{
    $isEnabled = defined('CONSOLE_LOG_ENABLED') ? CONSOLE_LOG_ENABLED : (defined('APP_ENV') && APP_ENV !== 'production');
    if ($isEnabled) {
        console($data, 'debug');
    }
}

/**
 * Log API request details
 * 
 * @param string $method HTTP method
 * @param string $url Request URL
 * @param array $data Request data
 * @param array $headers Request headers (sensitive data will be masked)
 * @return void
 */
function consoleLogApiRequest($method, $url, $data = [], $headers = [])
{
    // Mask sensitive headers
    $maskedHeaders = $headers;
    foreach ($maskedHeaders as $key => $value) {
        if (stripos($key, 'authorization') !== false || stripos($key, 'token') !== false) {
            $maskedHeaders[$key] = substr($value, 0, 20) . '...';
        }
    }
    
    consoleDebug([
        'type' => 'API_REQUEST',
        'method' => $method,
        'url' => $url,
        'data' => $data,
        'headers' => $maskedHeaders,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Log API response details
 * 
 * @param int $statusCode HTTP status code
 * @param mixed $response Response data
 * @param string $url Request URL
 * @return void
 */
function consoleLogApiResponse($statusCode, $response, $url = '')
{
    $level = ($statusCode >= 200 && $statusCode < 300) ? 'info' : 'error';
    
    console([
        'type' => 'API_RESPONSE',
        'status_code' => $statusCode,
        'url' => $url,
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ], $level);
}

/**
 * Group console logs (for browser console)
 * 
 * @param string $label Group label
 * @param callable $callback Function to execute within the group
 * @return void
 */
function consoleGroup($label, $callback)
{
    if (defined('APP_ENV') && APP_ENV !== 'production' && !headers_sent()) {
        echo "<script>console.group('{$label}');</script>";
        if (is_callable($callback)) {
            $callback();
        }
        echo "<script>console.groupEnd();</script>";
    } else {
        if (is_callable($callback)) {
            $callback();
        }
    }
}

