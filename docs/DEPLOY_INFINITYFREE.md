# Deploying SAIP to InfinityFree

## 1. Create Account
- Sign up at https://infinityfree.net
- Create a new hosting account (free)
- Note your FTP credentials + MySQL details

## 2. Upload Files
Use FileZilla FTP client:
- Host: `ftpupload.net`
- Username: your InfinityFree username
- Password: your FTP password
- Upload to `/htdocs/`

Upload the entire SAIP folder contents INTO htdocs (not the SAIP folder itself).
Structure should be:
htdocs/
├── app/
├── config/
├── public/
├── database/
├── .htaccess
└── ...

## 3. Import Database
- InfinityFree cPanel → MySQL Databases
- Create database (name will be prefixed: `if0_xxxxx_saip`)
- Open phpMyAdmin → import `database/saip_enterprise.sql`
- Update `config/config.php` with new DB name/user/password

## 4. Update config/config.php
```php
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_USER', 'if0_xxxxx');
define('DB_PASS', 'your_password');
define('DB_NAME', 'if0_xxxxx_saip');
define('BASE_URL', 'http://yoursite.infinityfreeapp.com');
define('OPENWEATHER_API_KEY', 'your_key_here');
# Setup Cron
* * * * * /usr/bin/php /var/www/saip/public/cron.php?key=your_secret > /dev/null 2>&1