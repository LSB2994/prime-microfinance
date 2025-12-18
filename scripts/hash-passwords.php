<?php
/**
 * Script to hash passwords in users.json
 * Run this once to convert plain text passwords to hashed passwords
 */

require_once __DIR__ . '/../config.php';

$usersFile = __DIR__ . '/../data/users.json';

if (!file_exists($usersFile)) {
    die("users.json file not found!\n");
}

$json = file_get_contents($usersFile);
$data = json_decode($json, true);

if (!is_array($data) || !isset($data['users'])) {
    die("Invalid users.json format!\n");
}

echo "Hashing passwords...\n\n";

foreach ($data['users'] as &$user) {
    if (isset($user['password']) && !empty($user['password'])) {
        // Check if already hashed (starts with $2y$)
        if (strpos($user['password'], '$2y$') === 0) {
            echo "Password for {$user['email']} is already hashed. Skipping.\n";
            continue;
        }
        
        $plainPassword = $user['password'];
        $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
        $user['password'] = $hashedPassword;
        
        echo "Hashed password for: {$user['email']}\n";
        echo "  Plain: {$plainPassword}\n";
        echo "  Hash: {$hashedPassword}\n\n";
    }
}

// Backup original file
$backupFile = $usersFile . '.backup.' . date('Y-m-d_His');
copy($usersFile, $backupFile);
echo "Backup created: {$backupFile}\n\n";

// Write updated data
file_put_contents($usersFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Passwords hashed successfully! Updated {$usersFile}\n";

