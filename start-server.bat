@echo off
echo Starting PRIME Micro finance PHP Server...
echo.
echo Server will be available at: http://localhost:8000
echo Using router.php for clean URLs
echo Press Ctrl+C to stop the server
echo.
php -S localhost:8000 router.php
pause

