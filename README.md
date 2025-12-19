# PRIME Micro finance - PHP Project

A microfinance management system built with raw PHP.

## Features

- Login page with authentication
- Customer dashboard with data table
- Loan repayment tracking modal
- Responsive design

## Requirements

- PHP 7.4 or higher
- Web server (PHP built-in server or Apache/Nginx)

## Installation

1. Install PHP from https://www.php.net/downloads.php
2. Add PHP to your system PATH
3. Or use XAMPP/WAMP which includes PHP

## Running the Project

### Option 1: Using PHP Built-in Server (Recommended)

Open a terminal in this directory and run:

```bash
php -S localhost:8000 router.php
```

Or use the provided scripts:
- **Windows**: Double-click `start-server.bat`
- **PowerShell**: Run `.\start-server.ps1`

Then open your browser and navigate to:
- http://localhost:8000/ (or http://localhost:8000/login)
- http://localhost:8000/dashboard (after login)

### Option 2: Using XAMPP/WAMP

1. Copy this project folder to `htdocs` (XAMPP) or `www` (WAMP)
2. Start Apache server
3. Navigate to: http://localhost/prime-microfinance/index.php

## Project Structure

```
prime-microfinance/
├── config.php             # Application configuration
├── router.php             # Routing handler (for PHP built-in server)
├── index.php              # Entry point (redirects to login or customer)
├── views/
│   ├── login.php          # Login page
│   ├── customer.php       # Customer dashboard
│   └── logout.php        # Logout handler
├── api/
│   └── qr-collection.php  # QR code generation API endpoint
├── includes/
│   ├── interceptor.php    # Helper functions (auth, redirects, etc.)
│   ├── db.php             # Oracle database connection functions
│   ├── helpers.php        # Formatting helper functions
│   ├── webill_api.php     # Webill API integration functions
│   └── console_log.php    # Console logging utility for debugging
├── scripts/
│   ├── add-user.php       # Add a single user interactively
│   ├── add-multiple-users.php # Add multiple users at once
│   ├── hash-password.php  # Hash a password for manual editing
│   └── hash-passwords.php # Hash existing plain text passwords
├── data/
│   └── users.json         # User credentials (hashed passwords)
├── assets/
│   ├── css/
│   │   └── style.css      # Stylesheet
│   ├── js/
│   │   └── customer.js    # JavaScript functions
│   └── images/            # Images (logo, icons, etc.)
├── logs/                  # Error logs (gitignored)
└── README.md
```

## User Management

### Adding Users

The project includes scripts to help you add users with automatically hashed passwords.

#### Option 1: Add Multiple Users (Recommended)

To add multiple users at once (e.g., 10 users):

```bash
php scripts/add-multiple-users.php
```

The script will:
1. Ask how many users you want to add
2. Prompt for each user's details:
   - Email address
   - Password (automatically hashed)
   - Name (optional, press Enter to use email)
3. Automatically hash all passwords
4. Check for duplicate emails
5. Save everything to `data/users.json`
6. Show a summary of added/skipped users

#### Option 2: Add Single User

To add one user at a time:

```bash
php scripts/add-user.php
```

The script will prompt for:
- Email address
- Password (automatically hashed)
- Name (optional)

#### Option 3: Hash Password Only

If you want to manually edit `data/users.json`, you can hash a password first:

```bash
php scripts/hash-password.php
```

This will output a hashed password that you can copy into the JSON file.

#### Manual Method

You can also manually edit `data/users.json`. The format is:

```json
{
  "users": [
    {
      "email": "user@example.com",
      "password": "$2y$10$...hashed_password...",
      "name": "User Name"
    }
  ]
}
```

**Important**: Passwords must be hashed using PHP's `password_hash()` function. Use the scripts above to ensure proper hashing.

### User Scripts Location

All user management scripts are in the `scripts/` directory:
- `add-user.php` - Add a single user interactively
- `add-multiple-users.php` - Add multiple users at once
- `hash-password.php` - Hash a password for manual editing
- `hash-passwords.php` - Hash existing plain text passwords in users.json

## Default Login

- Email: john.doe@gmail.com
- Password: (check with administrator or use the scripts to add new users)

## Routes

- `/` - Redirects to `/login` if not logged in, or `/customer` if logged in
- `/login` - Login page
- `/customer` - Customer dashboard (requires login)
- `/logout` - Logout handler
- `/api/customers` - GET customers API (server-side pagination with search)
- `/api/qr-collection` - POST Generate QR code for payment (requires login)

## Configuration

Edit `config.php` to configure:
- **Oracle Database**: Connection credentials (username, password, connect string)
- **Webill API**: Base URL, client ID, and client secret for Webill API integration
- **Session**: Lifetime (default: 8 hours), cookie settings
- **Base URL**: For routing (empty for PHP built-in server)
- **Error Reporting**: Set `display_errors` to 0 in production
- **Security**: Encryption keys, CSRF protection

### Oracle Database Configuration

The application connects to an Oracle database with **environment-based configuration**:

- **Development/UAT**: `192.168.168.2:1521/PRIMEUATO9`
- **Production**: `192.168.168.3:1521/PRMPRDO9`

Database settings are automatically selected based on the `APP_ENV` environment variable:
- `development` → UAT database
- `uat` → UAT database  
- `production` → Production database

**Set environment variable:**
```bash
# Linux/Apache
export APP_ENV=production

# Or in Apache config
SetEnv APP_ENV production
```

**Test database connection:**
```bash
php scripts/test-db-connection.php
```

**Note**: 
- Ensure PHP OCI8 extension is installed and enabled
- See `DEPLOYMENT.md` for detailed deployment instructions
- Test connection before deploying to production

### Webill API Configuration

The application integrates with Webill API for external services. Configuration is set in `config.php`:

```php
define('WEBILL_BASE_URL', 'https://apitest-va.webill365.com/kh');
define('WEBILL_CLIENT_ID', 'c60a576aad10cdb8f172c00d8f0ff694');
define('WEBILL_CLIENT_SECRET', '1680faf2ed96fa592669f7a6740ab705');
```

**Using Webill API in your code:**

```php
require_once __DIR__ . '/includes/webill_api.php';

// Get access token (automatically cached)
$tokenData = getWebillAccessToken();

// Make authenticated API request
$response = webillApiRequest('/api/wbi/client/v1/endpoint', 'GET', [], []);

if ($response && $response['success']) {
    $data = $response['data'];
    // Process data
}
```

**Features:**
- Automatic token caching (tokens are cached in session)
- Automatic token refresh on expiration
- Error logging for debugging
- Console logging for API requests/responses (enabled in development)
- Support for GET, POST, PUT, DELETE methods

**Console Logging:**

The application includes console logging utilities for debugging. In development mode, logs are automatically output to both server logs and browser console.

```php
require_once __DIR__ . '/includes/console_log.php';

// Basic logging
console('This is a log message');
consoleInfo('Informational message');
consoleWarn('Warning message');
consoleError('Error message');
consoleDebug('Debug message (only in development)');

// API request/response logging (automatically used in webill_api.php)
consoleLogApiRequest('GET', '/api/endpoint', $data, $headers);
consoleLogApiResponse(200, $response, '/api/endpoint');
```

Console logging is automatically enabled in development mode and disabled in production.

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()` function
- **Session Security**: Session ID regeneration on login to prevent session fixation
- **CSRF Protection**: CSRF tokens for form submissions (can be enabled in login)
- **Input Validation**: Email and password validation
- **Error Logging**: Errors are logged to `logs/app-errors.log` (not displayed in production)

## Notes

- **Session Management**: Session data is stored in PHP sessions with 8-hour lifetime
- **Database**: Uses Oracle OCI8 extension for database connectivity
- **Routing**: `router.php` enables clean URLs when using PHP built-in server
- **User Storage**: Users are stored in `data/users.json` with hashed passwords
- **Production**: Set `display_errors = 0` in `config.php` for production environments
- **Logs**: Error logs are stored in `logs/` directory (gitignored)

#   p r i m e - m i c r o f i n a n c e 
 
 