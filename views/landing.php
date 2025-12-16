<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/dashboard');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo baseUrl('/assets/css/style.css'); ?>">
</head>
<body>
    <div class="landing-page">
        <!-- Header -->
        <header class="landing-header">
            <div class="landing-container">
                <div class="landing-logo">
                    <img src="<?php echo baseUrl('/assets/images/prime_logo.svg'); ?>" alt="PRIME Microfinance" class="landing-logo-img">
                </div>
                <nav class="landing-nav">
                    <a href="<?php echo baseUrl('/login'); ?>" class="landing-nav-link">Login</a>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="landing-hero">
            <div class="landing-container">
                <div class="hero-content">
                    <h1 class="hero-title">Empowering Financial Growth</h1>
                    <p class="hero-subtitle">Comprehensive microfinance management system for efficient loan tracking, customer management, and financial operations.</p>
                    <div class="hero-actions">
                        <a href="<?php echo baseUrl('/login'); ?>" class="hero-btn primary">Get Started</a>
                        <a href="#features" class="hero-btn secondary">Learn More</a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="hero-illustration">
                        <div class="illustration-card">
                            <div class="card-icon">💰</div>
                            <div class="card-text">Financial Management</div>
                        </div>
                        <div class="illustration-card">
                            <div class="card-icon">📊</div>
                            <div class="card-text">Loan Tracking</div>
                        </div>
                        <div class="illustration-card">
                            <div class="card-icon">👥</div>
                            <div class="card-text">Customer Care</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="landing-features">
            <div class="landing-container">
                <h2 class="section-title">Key Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">👥</div>
                        <h3 class="feature-title">Customer Management</h3>
                        <p class="feature-description">Efficiently manage customer accounts, track loan information, and maintain comprehensive customer records.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📋</div>
                        <h3 class="feature-title">Loan Tracking</h3>
                        <p class="feature-description">Monitor loan status, track repayments, and generate detailed loan schedules with payment history.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3 class="feature-title">Financial Reports</h3>
                        <p class="feature-description">Generate comprehensive reports and analyze financial data to make informed business decisions.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3 class="feature-title">Secure Platform</h3>
                        <p class="feature-description">Bank-level security with encrypted data storage and secure authentication for your peace of mind.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3 class="feature-title">Fast & Reliable</h3>
                        <p class="feature-description">Lightning-fast performance with real-time updates and reliable data synchronization.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3 class="feature-title">Responsive Design</h3>
                        <p class="feature-description">Access your dashboard from any device - desktop, tablet, or mobile - with a seamless experience.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="landing-cta">
            <div class="landing-container">
                <div class="cta-content">
                    <h2 class="cta-title">Ready to Get Started?</h2>
                    <p class="cta-subtitle">Join PRIME Microfinance and streamline your financial operations today.</p>
                    <a href="<?php echo baseUrl('/login'); ?>" class="cta-btn">Login to Dashboard</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="landing-footer">
            <div class="landing-container">
                <div class="footer-content">
                    <div class="footer-logo">
                        <img src="<?php echo baseUrl('/assets/images/prime_logo.svg'); ?>" alt="PRIME Microfinance" class="footer-logo-img">
                    </div>
                    <div class="footer-info">
                        <p>&copy; <?php echo date('Y'); ?> PRIME Microfinance. All rights reserved.</p>
                        <p>Powered by <img src="<?php echo baseUrl('/assets/images/webill365_logo_full.svg'); ?>" alt="WeBill365" class="footer-webill-logo"></p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>

