<?php
/**
 * Script to add multiple users to data/users.json at once
 * 
 * Usage: php scripts/add-multiple-users.php
 * 
 * This script allows you to add multiple users in one go.
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

echo "=== Add Multiple Users ===\n\n";
echo "How many users do you want to add? ";
$count = (int)trim(fgets(STDIN));

if ($count <= 0) {
    die("Error: Please enter a valid number greater than 0.\n");
}

$added = 0;
$skipped = 0;

for ($i = 1; $i <= $count; $i++) {
    echo "\n--- User $i of $count ---\n";
    
    echo "Email: ";
    $email = trim(fgets(STDIN));
    
    if (empty($email)) {
        echo "⚠ Skipping user $i: Email cannot be empty.\n";
        $skipped++;
        continue;
    }
    
    // Check if user already exists
    $exists = false;
    foreach ($users as $user) {
        if (isset($user['email']) && strcasecmp($user['email'], $email) === 0) {
            echo "⚠ Skipping user $i: User with email '$email' already exists.\n";
            $exists = true;
            $skipped++;
            break;
        }
    }
    
    if ($exists) {
        continue;
    }
    
    echo "Password: ";
    $password = trim(fgets(STDIN));
    
    if (empty($password)) {
        echo "⚠ Skipping user $i: Password cannot be empty.\n";
        $skipped++;
        continue;
    }
    
    echo "Name (optional, press Enter to use email): ";
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
    $added++;
    
    echo "✓ User $i added: $email\n";
}

// Update data
$data['users'] = $users;

// Save to file
file_put_contents($usersFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== Summary ===\n";
echo "✓ Added: $added user(s)\n";
if ($skipped > 0) {
    echo "⚠ Skipped: $skipped user(s)\n";
}
echo "\n";
?>

