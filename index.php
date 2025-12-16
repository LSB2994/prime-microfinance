<?php
/**
 * Root index.php - Shows landing page or redirects
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect to dashboard if logged in, otherwise show landing page
if (isLoggedIn()) {
    redirect('/dashboard');
} else {
    require_once __DIR__ . '/views/landing.php';
}
