<?php
/**
 * ==========================================================================
 * CustomerController
 * ==========================================================================
 * 
 * MVC controller for the main customer dashboard page.
 * ==========================================================================
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/interceptor.php';

class CustomerController
{
    public static function index(): void
    {
        require __DIR__ . '/../views/customer.php';
    }
}

CustomerController::index();


