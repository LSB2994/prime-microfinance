<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/dashboard');
}

/**
 * Load hard-coded users from JSON file
 *
 * @return array
 */
function loadUsersFromJson(): array {
    $usersFile = __DIR__ . '/../data/users.json';

    if (!file_exists($usersFile)) {
        return [];
    }

    $json = file_get_contents($usersFile);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return [];
    }

    return $data['users'] ?? [];
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $users = loadUsersFromJson();
        $matchedUser = null;

        foreach ($users as $user) {
            // Match by email (case insensitive) and plain-text password from JSON
            if (
                isset($user['email'], $user['password']) &&
                strcasecmp($user['email'], $email) === 0 &&
                $user['password'] === $password
            ) {
                $matchedUser = $user;
                break;
            }
        }

        if ($matchedUser) {
            // Store login state in PHP session (backed by browser cookie)
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $matchedUser['email'];
            $_SESSION['user_name'] = $matchedUser['name'] ?? $matchedUser['email'];

            setFlashMessage('success', 'Login successful!');
            redirect('/dashboard');
        } else {
            setFlashMessage('error', 'Invalid email or password');
        }
    } else {
        setFlashMessage('error', 'Please enter email and password');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/style.css'); ?>">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <div class="avatar-container">
                <div class="avatar"></div>
            </div>
            <h1>Login</h1>
            <p class="subtitle">Login to access your <?php echo APP_NAME; ?> account</p>
            
            <?php
            $flash = getFlashMessage();
            if ($flash): ?>
                <div class="flash-message <?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo baseUrl('/login'); ?>" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="john.doe@gmail.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required>
                        <span class="toggle-password" onclick="togglePassword()">👁</span>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot Password</a>
                </div>
                
                <button type="submit" class="login-btn">Login</button>
                
                <p class="signup-link">Don't have an account? <a href="#">Sign up</a></p>
            </form>
        </div>
        
        <div class="login-illustration">
            <div class="illustration-panel">
                <div class="phone-illustration">
                    <div class="hand-phone-container">
                        <div class="hand"></div>
                        <div class="phone">
                            <div class="phone-screen">
                                <div class="lock-icon">🔒</div>
                                <div class="asterisks">****</div>
                            </div>
                        </div>
                        <div class="shield-icon"></div>
                    </div>
                </div>
                <div class="pagination-dots">
                    <div class="pagination-dot active"></div>
                    <div class="pagination-dot"></div>
                    <div class="pagination-dot"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo baseUrl('/assets/js/main.js'); ?>"></script>
</body>
</html>

