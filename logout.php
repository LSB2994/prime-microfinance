<?php
/**
 * Logout Handler
 */
require_once 'config.php';
require_once 'includes/functions.php';

// Clear all session data
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Set logout message
session_start();
setFlashMessage('success', 'You have been logged out successfully.');
session_write_close();

// Redirect to login
redirect('/login');

