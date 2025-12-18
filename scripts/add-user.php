<?php
/**
 * Script to add new users to data/users.json
 * 
 * Usage: php scripts/add-user.php
 * 
 * This script will prompt you to enter user details and automatically hash the password.
 */

require_once __DIR__ . '/../config.php';

$usersFile = __DIR__ . '/../data/users.json';

if (!file_exists($usersFile)) {
    die("Error: users.json not found at $usersFile\n");
}

// Load existing users
$json = file_get_contents($usersFile);
$data = json_decode($json, true);

if (!is_array($data) || !isset($data['users'])) {
    die("Error: Invalid users.json format.\n");
}

$users = $data['users'];

echo "=== Add New User ===\n\n";

// Get user input
echo "Enter email: ";
$email = trim(fgets(STDIN));

if (empty($email)) {
    die("Error: Email cannot be empty.\n");
}

// Check if user already exists
foreach ($users as $user) {
    if (isset($user['email']) && strcasecmp($user['email'], $email) === 0) {
        die("Error: User with email '$email' already exists.\n");
    }
}

echo "Enter password: ";
$password = trim(fgets(STDIN));

if (empty($password)) {
    die("Error: Password cannot be empty.\n");
}

echo "Enter name (optional, press Enter to skip): ";
$name = trim(fgets(STDIN));

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Create new user
$newUser = [
    'email' => $email,
    'password' => $hashedPassword,
    'name' => !empty($name) ? $name : $email
];

// Add to users array
$users[] = $newUser;

// Update data
$data['users'] = $users;

// Save to file
file_put_contents($usersFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n✓ User added successfully!\n";
echo "Email: $email\n";
echo "Name: " . $newUser['name'] . "\n";
echo "Password: [Hashed]\n\n";
?>

