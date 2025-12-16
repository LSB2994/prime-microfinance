# PHP Installation Guide for Windows

## Quick Installation Steps

### Option 1: Download PHP Directly (Recommended)

1. **Download PHP:**
   - Visit: https://www.php.net/downloads.php
   - Download the latest **Thread Safe** version (e.g., `php-8.3.x-Win32-vs16-x64.zip`)
   - Choose the ZIP package (not installer)

2. **Extract PHP:**
   - Extract the ZIP file to `C:\php` (or any location you prefer)
   - Example: `C:\php\php.exe` should exist

3. **Add PHP to System PATH:**
   - Press `Win + X` and select "System"
   - Click "Advanced system settings"
   - Click "Environment Variables"
   - Under "System variables", find "Path" and click "Edit"
   - Click "New" and add: `C:\php` (or your PHP installation path)
   - Click "OK" on all dialogs

4. **Verify Installation:**
   - Open a new PowerShell/Command Prompt window
   - Run: `php -v`
   - You should see PHP version information

5. **Configure PHP (Optional):**
   - Copy `php.ini-development` to `php.ini` in your PHP folder
   - Edit `php.ini` and uncomment (remove `;`) these lines:
     ```
     extension_dir = "ext"
     extension=mysqli
     extension=pdo_mysql
     ```

### Option 2: Use XAMPP (Easiest for Beginners)

1. **Download XAMPP:**
   - Visit: https://www.apachefriends.org/
   - Download XAMPP for Windows
   - Run the installer

2. **Start Apache:**
   - Open XAMPP Control Panel
   - Click "Start" next to Apache

3. **Copy Project:**
   - Copy this project folder to: `C:\xampp\htdocs\prime-microfinance`

4. **Access Project:**
   - Open browser: http://localhost/prime-microfinance/

### Option 3: Use WAMP

1. **Download WAMP:**
   - Visit: https://www.wampserver.com/
   - Download and install WAMP

2. **Start Services:**
   - Launch WAMP
   - Ensure Apache is running (green icon)

3. **Copy Project:**
   - Copy this project folder to: `C:\wamp64\www\prime-microfinance`

4. **Access Project:**
   - Open browser: http://localhost/prime-microfinance/

## After Installation

Once PHP is installed, you can start the project:

### Using PHP Built-in Server:
```bash
cd D:\prime-microfinance
php -S localhost:8000 router.php
```

Or use the provided scripts:
- Double-click `start-server.bat`
- Or run `.\start-server.ps1` in PowerShell

### Using XAMPP/WAMP:
- Just access via browser: http://localhost/prime-microfinance/

## Troubleshooting

### PHP not recognized after adding to PATH:
- Close and reopen your terminal/PowerShell
- Restart your computer if needed
- Verify PATH by running: `echo $env:Path` (PowerShell)

### Port 8000 already in use:
- Change port in `start-server.bat`: `php -S localhost:8080 router.php`
- Or kill the process using port 8000

### Permission errors:
- Run PowerShell/Command Prompt as Administrator
- Check folder permissions

## Quick Test

After installation, test PHP:
```bash
php -v
php -m  # List loaded extensions
php -r "echo 'PHP is working!';"
```

