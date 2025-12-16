<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/dashboard');
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // Simple registration - in production, save to database
    $email = sanitize($_POST['email']);
    $password = $_POST['password'] ?? '';
    $name = sanitize($_POST['name'] ?? '');
    
    // Demo: accept any valid input
    if (!empty($email) && !empty($password) && !empty($name)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['user_name'] = $name;
        setFlashMessage('success', 'Registration successful!');
        redirect('/dashboard');
    } else {
        setFlashMessage('error', 'Please fill in all fields');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - <?php echo APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo baseUrl('/assets/images/logo.png'); ?>">
    <link rel="alternate icon" href="<?php echo baseUrl('/assets/images/logo.png'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Passion+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/style.css'); ?>">
</head>
<body>
    <div class="registration-container">
        <!-- Left side with avatar and title -->
        <div class="registration-left">
            <div class="registration-avatar-container">
                <img src="<?php echo baseUrl('/assets/images/user.png'); ?>" alt="Avatar" class="registration-avatar">
            </div>
            <h1 class="registration-title">Registration</h1>
        </div>
        
        <!-- Right side with form card -->
        <div class="registration-card">
            <div class="registration-card-bg">
                <img src="<?php echo baseUrl('/assets/images/login.png'); ?>" alt="Background" class="registration-bg-image">
            </div>
            <div class="registration-form-wrapper">
                <?php
                $flash = getFlashMessage();
                if ($flash): ?>
                    <div class="flash-message <?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo baseUrl('/registration'); ?>" method="POST" class="registration-form">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required>
                            <span class="toggle-password" onclick="togglePassword()">👁</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="registration-btn">Register</button>
                    
                    <p class="login-link">Already have an account? <a href="<?php echo baseUrl('/login'); ?>">Login</a></p>
                </form>
            </div>
        </div>
    </div>
    
    <script src="<?php echo baseUrl('/assets/js/main.js'); ?>"></script>
</body>
</html>

