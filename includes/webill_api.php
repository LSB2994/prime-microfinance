<?php
/**
 * ==========================================================================
 * Webill API Helper Functions
 * ==========================================================================
 * 
 * Functions for interacting with Webill API, including authentication
 * and token management.
 * ==========================================================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/console_log.php';

/**
 * Get Webill API access token
 * 
 * Retrieves an access token from Webill API. Tokens are cached in session
 * to avoid unnecessary API calls. Token expires in 7200 seconds (2 hours).
 * 
 * @param bool $forceRefresh Force refresh of token even if cached
 * @return array|false Returns array with 'access_token', 'token_type', 'expires_in' on success, false on failure
 */
function getWebillAccessToken($forceRefresh = false)
{
    // Check if we have a valid cached token
    if (!$forceRefresh && isset($_SESSION['webill_token'])) {
        $tokenData = $_SESSION['webill_token'];
        $expiresAt = $_SESSION['webill_token_expires_at'] ?? 0;
        
        // Check if token is still valid (with 60 second buffer)
        if ($expiresAt > (time() + 60)) {
            return $tokenData;
        }
    }

    // Prepare request
    $url = WEBILL_BASE_URL . '/api/wbi/client/v1/auth/token';
    $data = [
        'client_id' => WEBILL_CLIENT_ID,
        'client_secret' => WEBILL_CLIENT_SECRET
    ];
    
    // Log request
    consoleLogApiRequest('POST', $url, ['client_id' => WEBILL_CLIENT_ID, 'client_secret' => '***']);

    // Initialize cURL
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: */*'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);

    // Handle cURL errors
    if ($response === false || !empty($error)) {
        $errorData = [
            'error' => $error,
            'url' => $url
        ];
        consoleError(['Webill API token request failed', $errorData]);
        logError('Webill API token request failed', $errorData);
        return false;
    }

    // Parse response
    $result = json_decode($response, true);
    
    // Log response
    consoleLogApiResponse($httpCode, $result, $url);

    // Check if request was successful
    if ($httpCode !== 200 || !isset($result['data']['access_token'])) {
        $errorData = [
            'http_code' => $httpCode,
            'response' => $result
        ];
        consoleError(['Webill API token request returned error', $errorData]);
        logError('Webill API token request returned error', $errorData);
        return false;
    }

    // Cache token in session
    $tokenData = [
        'access_token' => $result['data']['access_token'],
        'token_type' => $result['data']['token_type'] ?? 'Bearer',
        'expires_in' => $result['data']['expires_in'] ?? 7200
    ];

    $_SESSION['webill_token'] = $tokenData;
    $_SESSION['webill_token_expires_at'] = time() + ($tokenData['expires_in'] ?? 7200);

    // Log successful token retrieval
    consoleInfo([
        'message' => 'Webill access token retrieved successfully',
        'expires_in' => $tokenData['expires_in'],
        'token_type' => $tokenData['token_type']
    ]);

    return $tokenData;
}

/**
 * Make authenticated request to Webill API
 * 
 * @param string $endpoint API endpoint (e.g., '/api/wbi/client/v1/...')
 * @param string $method HTTP method (GET, POST, PUT, DELETE)
 * @param array $data Request data (for POST/PUT requests)
 * @param array $headers Additional headers
 * @param bool $forceTokenRefresh Force token refresh
 * @return array|false Returns decoded JSON response on success, false on failure
 */
function webillApiRequest($endpoint, $method = 'GET', $data = [], $headers = [], $forceTokenRefresh = false)
{
    // Get access token
    $tokenData = getWebillAccessToken($forceTokenRefresh);
    
    if (!$tokenData) {
        return false;
    }

    // Build full URL
    $url = WEBILL_BASE_URL . $endpoint;
    
    // Prepare headers
    $requestHeaders = array_merge([
        'Authorization: ' . $tokenData['token_type'] . ' ' . $tokenData['access_token'],
        'Content-Type: application/json',
        'Accept: application/json'
    ], $headers);
    
    // Log API request
    consoleLogApiRequest($method, $url, $data, $requestHeaders);

    // Initialize cURL
    $ch = curl_init();
    
    $curlOptions = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $requestHeaders,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ];

    // Set method-specific options
    switch (strtoupper($method)) {
        case 'POST':
            $curlOptions[CURLOPT_POST] = true;
            if (!empty($data)) {
                $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
            }
            break;
            
        case 'PUT':
            $curlOptions[CURLOPT_CUSTOMREQUEST] = 'PUT';
            if (!empty($data)) {
                $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
            }
            break;
            
        case 'DELETE':
            $curlOptions[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            break;
            
        case 'GET':
        default:
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
                $curlOptions[CURLOPT_URL] = $url;
            }
            break;
    }

    curl_setopt_array($ch, $curlOptions);

    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);

    // Handle cURL errors
    if ($response === false || !empty($error)) {
        $errorData = [
            'error' => $error,
            'url' => $url,
            'method' => $method
        ];
        consoleError(['Webill API request failed', $errorData]);
        logError('Webill API request failed', $errorData);
        return false;
    }

    // Parse response
    $result = json_decode($response, true);
    
    // Log API response
    consoleLogApiResponse($httpCode, $result, $url);

    // Handle 401 Unauthorized - token might be expired, try refreshing
    if ($httpCode === 401 && !$forceTokenRefresh) {
        consoleWarn('Webill API returned 401, refreshing token and retrying...');
        // Retry with refreshed token
        return webillApiRequest($endpoint, $method, $data, $headers, true);
    }

    // Log non-successful responses
    if ($httpCode < 200 || $httpCode >= 300) {
        $errorData = [
            'http_code' => $httpCode,
            'response' => $result,
            'url' => $url,
            'method' => $method
        ];
        consoleError(['Webill API request returned error', $errorData]);
        logError('Webill API request returned error', $errorData);
    } else {
        consoleInfo(['Webill API request successful', ['url' => $url, 'method' => $method]]);
    }

    return [
        'status_code' => $httpCode,
        'data' => $result,
        'success' => $httpCode >= 200 && $httpCode < 300
    ];
}

/**
 * Clear cached Webill token
 * 
 * Useful for forcing a new token on next request
 * 
 * @return void
 */
function clearWebillToken()
{
    unset($_SESSION['webill_token']);
    unset($_SESSION['webill_token_expires_at']);
}

