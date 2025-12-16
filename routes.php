<?php
/**
 * Route Definitions
 * Centralized routing configuration
 */

return [
    // Public routes
    'GET' => [
        '/' => ['controller' => 'AuthController', 'action' => 'login'],
        '/login' => ['controller' => 'AuthController', 'action' => 'login'],
        '/dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    ],
    
    // POST routes
    'POST' => [
        '/login' => ['controller' => 'AuthController', 'action' => 'handleLogin'],
        '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    ],
    
    // API routes
    'API' => [
        'GET' => [
            '/api/customers' => ['controller' => 'ApiController', 'action' => 'getCustomers'],
            '/api/loan-repayment/:id' => ['controller' => 'ApiController', 'action' => 'getLoanRepayment'],
        ],
        'POST' => [
            '/api/customers' => ['controller' => 'ApiController', 'action' => 'createCustomer'],
        ],
    ],
];

