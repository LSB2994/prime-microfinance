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
    <link rel="icon" type="image/png" href="<?php echo baseUrl('/assets/images/logo.png'); ?>">
    <link rel="alternate icon" href="<?php echo baseUrl('/assets/images/logo.png'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/style.css'); ?>">
</head>
<body>
    <div class="login-page-container">
        <!-- Left side with avatar -->
        <div class="login-avatar-section">
            <div class="login-avatar-container">
                <img src="<?php echo baseUrl('/assets/images/user.png'); ?>" alt="Avatar" class="login-avatar-img">
            </div>
        </div>
        
        <!-- Center section with form -->
        <div class="login-form-section">
            <div class="login-header">
                <h1 class="login-title">LOGIN</h1>
                <p class="login-subtitle">Login to access your account</p>
            </div>
            
            <?php
            $flash = getFlashMessage();
            if ($flash): ?>
                <div class="flash-message <?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo baseUrl('/login'); ?>" method="POST" class="login-form-new">
                <div class="form-field-group">
                    <label for="email" class="form-label">Email</label>
                    <div class="form-input-wrapper">
                        <input type="email" id="email" name="email" value="john.doe@gmail.com" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-field-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="form-input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" class="form-input" required>
                        <button type="button" class="eye-toggle" onclick="togglePassword()">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 4C6 4 3.5 6.5 2 10C3.5 13.5 6 16 10 16C14 16 16.5 13.5 18 10C16.5 6.5 14 4 10 4ZM10 14C7.79 14 6 12.21 6 10C6 7.79 7.79 6 10 6C12.21 6 14 7.79 14 10C14 12.21 12.21 14 10 14ZM10 8C8.9 8 8 8.9 8 10C8 11.1 8.9 12 10 12C11.1 12 12 11.1 12 10C12 8.9 11.1 8 10 8Z" fill="#1E1E1E"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="remember-me-group">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember" class="remember-checkbox" checked>
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-label">Remember me</span>
                    </label>
                </div>
                
                <button type="submit" class="login-button-new">Login</button>
            </form>
        </div>
        
        <!-- Right side with illustration -->
        <div class="login-illustration-section">
            <div class="login-illustration-panel">
                <img src="<?php echo baseUrl('/assets/images/login.png'); ?>" alt="Login Illustration" class="login-illustration-img">
            </div>
        </div>
    </div>
    
    <script src="<?php echo baseUrl('/assets/js/main.js'); ?>"></script>
</body>
</html>

