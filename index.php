<?php
/**
 * Root index.php - Redirects to login or customer page
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/interceptor.php';

// Redirect to client_infm view if logged in, otherwise redirect to login
if (isLoggedIn()) {
    redirect('/client_infm');
} else {
    redirect('/login');
}
