<?php
/**
 * ==========================================================================
 * Root index.php - Application Entry Point
 * ==========================================================================
 * 
 * Redirects to login page if not authenticated, otherwise redirects to
 * customer dashboard.
 * ==========================================================================
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/interceptor.php';

// Redirect to customer view if logged in, otherwise redirect to login
if (isLoggedIn()) {
    redirect('/customer');
} else {
    redirect('/login');
}
