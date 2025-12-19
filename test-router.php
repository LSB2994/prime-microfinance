<?php
/**
 * Simple test to verify router is working
 * Access: http://localhost:8000/test-router.php
 */

echo "Router test file accessed directly\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "If you see this, the router is NOT being used for this file\n";
echo "The router should handle /api/ routes\n";
