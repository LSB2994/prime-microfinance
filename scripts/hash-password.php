<?php
/**
 * Quick script to hash a password
 * 
 * Usage: php scripts/hash-password.php
 * 
 * This will output a hashed password that you can copy into users.json
 */

require_once __DIR__ . '/../config.php';

echo "Enter password to hash: ";
$password = trim(fgets(STDIN));

if (empty($password)) {
    die("Error: Password cannot be empty.\n");
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

echo "\nHashed password:\n";
echo $hashed . "\n\n";
echo "Copy this into your users.json file.\n";
?>

