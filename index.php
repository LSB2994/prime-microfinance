<?php
/**
 * Root index.php - Redirects to login or customer page
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/interceptor.php';

// Redirect to customer view if logged in, otherwise redirect to login
if (isLoggedIn()) {
    redirect('/customer');
} else {
    redirect('/login');
}
