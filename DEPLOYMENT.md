# Deployment Guide

## Environment Configuration

The application supports three environments:
- **Development**: Local development environment
- **UAT**: User Acceptance Testing environment
- **Production**: Live production environment

## Database Configuration

Database connections are automatically configured based on the `APP_ENV` environment variable.

### Environment-Specific Database Settings

| Environment | IP Address | Database Name | Connect String |
|------------|------------|---------------|----------------|
| Development | 192.168.168.2 | PRIMEUATO9 | 192.168.168.2:1521/PRIMEUATO9 |
| UAT | 192.168.168.2 | PRIMEUATO9 | 192.168.168.2:1521/PRIMEUATO9 |
| Production | 192.168.168.3 | PRMPRDO9 | 192.168.168.3:1521/PRMPRDO9 |

### Setting Environment Variable

#### Option 1: Environment Variable (Recommended)

**Linux/Apache (.htaccess or Apache config):**
```apache
SetEnv APP_ENV production
```

**Nginx:**
```nginx
fastcgi_param APP_ENV production;
```

**PHP Built-in Server:**
```bash
export APP_ENV=production
php -S localhost:8000 router.php
```

**System Environment (Linux):**
```bash
# Add to /etc/environment or ~/.bashrc
export APP_ENV=production
```

**Windows (IIS):**
- Set in IIS Manager → Application → Environment Variables
- Or in `web.config`:
```xml
<environmentVariables>
    <environmentVariable name="APP_ENV" value="production" />
</environmentVariables>
```

#### Option 2: Direct Edit (Not Recommended for Production)

Edit `config.php` and change:
```php
define('APP_ENV', 'production'); // Change from 'development'
```

## Pre-Deployment Checklist

### 1. Test Database Connection

Test the connection for each environment:

```bash
# Test Development
APP_ENV=development php scripts/test-db-connection.php

# Test UAT
APP_ENV=uat php scripts/test-db-connection.php

# Test Production
APP_ENV=production php scripts/test-db-connection.php
```

### 2. Verify PHP Extensions

Ensure OCI8 extension is installed and enabled:

```bash
php -m | grep oci8
```

If not installed:
- **Linux**: `sudo apt-get install php-oci8` or `sudo yum install php-oci8`
- **Windows**: Enable `php_oci8_12c.dll` or `php_oci8_11g.dll` in `php.ini`

### 3. Check File Permissions

```bash
# Create logs directory if it doesn't exist
mkdir -p logs
chmod 755 logs

# Ensure data directory is writable (for user management)
chmod 755 data
chmod 644 data/users.json
```

### 4. Security Configuration

- [ ] Set `APP_ENV=production` in production
- [ ] Verify `display_errors = 0` in production
- [ ] Change `ENCRYPTION_KEY` in `config.php`
- [ ] Ensure `logs/` directory is writable
- [ ] Review and update `data/users.json` with production users
- [ ] Set secure cookie flags (already configured in `config.php`)

### 5. Network Configuration

- [ ] Verify firewall rules allow connection to Oracle database
- [ ] Test network connectivity: `telnet 192.168.168.3 1521` (production)
- [ ] Ensure Oracle client libraries are installed on server

## Deployment Steps

### Step 1: Upload Files

Upload all project files to the production server, excluding:
- `logs/` directory (create empty directory)
- `.git/` directory
- Development/test files

### Step 2: Set Environment Variable

Set `APP_ENV=production` using one of the methods above.

### Step 3: Test Connection

```bash
APP_ENV=production php scripts/test-db-connection.php
```

Expected output:
```
=== Database Connection Test ===

Environment: production
Database Host: 192.168.168.3:1521/PRMPRDO9
Username: BI

✓ STATUS: SUCCESS
✓ MESSAGE: Oracle connection established successfully

✓ Query Test: SUCCESS
  Server Date/Time: [current date/time]

✓ View Access Test: SUCCESS
  Total Records in CTM_INFOR_V1: [number]
```

### Step 4: Verify Application

1. Access the login page
2. Test login functionality
3. Verify customer data loads correctly
4. Test search functionality
5. Check error logs: `logs/app-errors.log` and `logs/php-errors.log`

## Troubleshooting

### Connection Failed

**Error**: `Failed to connect to Oracle: ORA-12154: TNS:could not resolve the connect identifier`

**Solutions**:
1. Verify IP address and port are correct
2. Check network connectivity: `ping 192.168.168.3`
3. Verify Oracle service name is correct
4. Check Oracle TNS configuration if using TNS names

**Error**: `OCI8 extension is not enabled`

**Solutions**:
1. Install OCI8 extension: `sudo apt-get install php-oci8`
2. Enable in `php.ini`: `extension=oci8.so` (Linux) or `extension=php_oci8_12c.dll` (Windows)
3. Restart web server: `sudo service apache2 restart` or `sudo service nginx restart`

### Data Not Loading

1. Check error logs: `logs/app-errors.log`
2. Verify database view exists: `SELECT * FROM BI.CTM_INFOR_V1 WHERE ROWNUM <= 1`
3. Check user permissions in Oracle database
4. Verify schema name is correct (BI)

### Performance Issues

1. Check database connection pooling
2. Review query performance
3. Check server resources (CPU, memory)
4. Monitor Oracle database performance

## Environment-Specific Notes

### Production

- Always use `APP_ENV=production`
- Never display errors to users
- Monitor error logs regularly
- Use HTTPS if possible
- Regular database backups
- Monitor application performance

### UAT

- Use `APP_ENV=uat`
- Can display errors for testing
- Test all features before production deployment
- Verify data matches production structure

### Development

- Use `APP_ENV=development` (default)
- Errors displayed for debugging
- Can use local database if available
- Development tools enabled

## Support

For issues:
1. Check error logs: `logs/app-errors.log` and `logs/php-errors.log`
2. Test database connection: `php scripts/test-db-connection.php`
3. Verify environment variable: `echo $APP_ENV` or check server configuration
4. Review Oracle database logs if connection issues persist

