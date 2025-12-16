<?php
/**
 * Root index.php - Redirects to login or dashboard
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect to dashboard if logged in, otherwise redirect to login
if (isLoggedIn()) {
    redirect('/dashboard');
} else {
    redirect('/login');
}
