<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/dashboard');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // Simple authentication - in production, verify against database
    $email = sanitize($_POST['email']);
    $password = $_POST['password'] ?? '';
    
    // Demo: accept any password for demo purposes
    if (!empty($email) && !empty($password)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['user_name'] = 'Danielle Campbell'; // Demo user
        setFlashMessage('success', 'Login successful!');
        redirect('/dashboard');
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

