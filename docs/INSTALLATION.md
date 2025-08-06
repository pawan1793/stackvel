# Stackvel Framework - Installation Guide

This guide provides comprehensive instructions for installing Stackvel Framework using different methods.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation Methods](#installation-methods)
  - [Method 1: Composer Create-Project (Recommended)](#method-1-composer-create-project-recommended)
  - [Method 2: Manual Installation](#method-2-manual-installation)
  - [Method 3: Git Clone](#method-3-git-clone)
- [Post-Installation Setup](#post-installation-setup)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Web Server Configuration](#web-server-configuration)
- [Development Server](#development-server)
- [Update System](#update-system)
- [Troubleshooting](#troubleshooting)

## Prerequisites

Before installing Stackvel Framework, ensure you have the following:

### System Requirements

- **PHP**: 8.0 or higher
- **Composer**: Latest version
- **Web Server**: Apache/Nginx (or PHP built-in server for development)
- **Database**: MySQL 5.7+, MariaDB 10.2+, PostgreSQL 10+, or SQLite 3.8+
- **Extensions**: 
  - PDO PHP Extension
  - PDO MySQL/PostgreSQL/SQLite Extension
  - OpenSSL PHP Extension
  - Mbstring PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - BCMath PHP Extension

### Check PHP Version

```bash
php --version
```

### Check Composer

```bash
composer --version
```

### Install Required Extensions

#### Ubuntu/Debian
```bash
sudo apt update
sudo apt install php8.0 php8.0-cli php8.0-common php8.0-mysql php8.0-pgsql php8.0-sqlite3 php8.0-curl php8.0-mbstring php8.0-xml php8.0-zip php8.0-openssl php8.0-json php8.0-bcmath
```

#### CentOS/RHEL/Fedora
```bash
sudo dnf install php php-cli php-common php-mysqlnd php-pgsql php-sqlite3 php-curl php-mbstring php-xml php-zip php-openssl php-json php-bcmath
```

#### macOS (using Homebrew)
```bash
brew install php
brew install composer
```

#### Windows
Download and install from:
- PHP: https://windows.php.net/download/
- Composer: https://getcomposer.org/download/

## Installation Methods

### Method 1: Composer Create-Project (Recommended)

This is the recommended method for new projects as it provides the best update experience.

#### Step 1: Create New Project

```bash
# Create a new Stackvel project
composer create-project pawanmore/stackvel my-project

# Navigate to project directory
cd my-project
```

#### Step 2: Verify Installation

```bash
# Check framework version
php console.php version

# Show available commands
php console.php help
```

#### Step 3: Configure Environment

```bash
# Copy environment file
cp env.example .env

# Edit environment settings
nano .env
```

#### Step 4: Set Permissions

```bash
# Set proper permissions
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env
```

### Method 2: Manual Installation

Use this method if you want more control over the installation process.

#### Step 1: Download Framework

```bash
# Download the latest release
wget https://github.com/pawan1793/stackvel/archive/refs/heads/main.zip

# Extract the archive
unzip main.zip
mv stackvel-main my-project
cd my-project
```

#### Step 2: Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader
```

#### Step 3: Setup Environment

```bash
# Copy environment file
cp env.example .env

# Edit environment settings
nano .env
```

#### Step 4: Create Directories

```bash
# Create required directories
mkdir -p storage/cache
mkdir -p storage/logs
mkdir -p storage/backups
mkdir -p public/uploads

# Set permissions
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env
```

### Method 3: Git Clone

Use this method for development or if you want to track the repository.

#### Step 1: Clone Repository

```bash
# Clone the repository
git clone https://github.com/pawan1793/stackvel.git my-project
cd my-project

# Install dependencies
composer install
```

#### Step 2: Setup Environment

```bash
# Copy environment file
cp env.example .env

# Edit environment settings
nano .env
```

#### Step 3: Create Directories

```bash
# Create required directories
mkdir -p storage/cache
mkdir -p storage/logs
mkdir -p storage/backups
mkdir -p public/uploads

# Set permissions
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env
```

## Post-Installation Setup

### 1. Environment Configuration

Edit the `.env` file with your specific settings:

```env
# Application
APP_NAME=Stackvel
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=UTC

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stackvel
DB_USERNAME=root
DB_PASSWORD=your_password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME=Stackvel
```

### 2. Database Setup

#### MySQL/MariaDB

```sql
-- Create database
CREATE DATABASE stackvel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (optional)
CREATE USER 'stackvel_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON stackvel.* TO 'stackvel_user'@'localhost';
FLUSH PRIVILEGES;
```

#### PostgreSQL

```sql
-- Create database
CREATE DATABASE stackvel;

-- Create user (optional)
CREATE USER stackvel_user WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE stackvel TO stackvel_user;
```

#### SQLite

```bash
# Create SQLite database file
touch database/stackvel.sqlite
chmod 664 database/stackvel.sqlite
```

### 3. Run Migrations

```bash
# Run database migrations
php console.php migrate

# Seed database with sample data (optional)
php console.php seed
```

### 4. Test Installation

```bash
# Start development server
php console.php serve

# Visit http://localhost:8000 in your browser
```

## Configuration

### Web Server Configuration

#### Apache Configuration

Create or edit your Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/your-project/public
    
    <Directory /path/to/your-project/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/stackvel_error.log
    CustomLog ${APACHE_LOG_DIR}/stackvel_access.log combined
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your-project/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Development Server

For development, you can use PHP's built-in server:

```bash
# Start development server
php console.php serve

# Or manually
php -S localhost:8000 -t public/
```

## Update System

Stackvel Framework includes a comprehensive update system for projects created with `composer create-project`.

### Check for Updates

```bash
# Check if updates are available
php console.php update:check

# Or use Composer script
composer check-updates
```

### Update Framework

```bash
# Automatic update with backup
php console.php update:framework

# Or use Composer
composer update pawanmore/stackvel
```

### Update Features

- **Automatic Backup**: Creates timestamped backups before updates
- **Version Checking**: Compares against latest version on Packagist
- **Post-Update Optimization**: Clears cache and optimizes autoloader
- **Rollback Support**: Easy rollback using created backups

For detailed update instructions, see [UPDATING.md](UPDATING.md).

## Troubleshooting

### Common Issues

#### 1. Permission Denied Errors

```bash
# Fix permissions
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env
```

#### 2. Composer Memory Limit

```bash
# Increase PHP memory limit
php -d memory_limit=-1 composer install
```

#### 3. Database Connection Issues

- Verify database credentials in `.env`
- Ensure database server is running
- Check if database exists
- Verify user permissions

#### 4. Missing PHP Extensions

```bash
# Check installed extensions
php -m

# Install missing extensions (Ubuntu/Debian example)
sudo apt install php8.0-mysql php8.0-pgsql php8.0-sqlite3
```

#### 5. Web Server Configuration

- Ensure mod_rewrite is enabled (Apache)
- Check file permissions
- Verify DocumentRoot points to `public/` directory

### Debug Mode

Enable debug mode for development:

```env
APP_DEBUG=true
APP_ENV=development
```

### Logs

Check application logs:

```bash
# View error logs
tail -f storage/logs/error.log

# View access logs
tail -f storage/logs/access.log
```

### Performance Optimization

For production environments:

```bash
# Optimize autoloader
composer dump-autoload --optimize

# Clear cache
php console.php clear-cache

# Optimize application
php console.php optimize
```

## Security Considerations

### Production Deployment

1. **Environment**: Set `APP_ENV=production`
2. **Debug**: Set `APP_DEBUG=false`
3. **HTTPS**: Use SSL/TLS certificates
4. **Permissions**: Restrict file permissions
5. **Firewall**: Configure server firewall
6. **Updates**: Keep framework and dependencies updated

### File Permissions

```bash
# Secure file permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env
```

## Next Steps

After successful installation:

1. **Read Documentation**: Review the [README.md](../README.md)
2. **Create Your First Controller**: Use `php console.php make:controller WelcomeController`
3. **Set Up Routes**: Edit `routes/web.php`
4. **Create Models**: Use `php console.php make:model User`
5. **Run Tests**: Execute `composer test`
6. **Deploy**: Follow production deployment guidelines

## Support

If you encounter issues:

1. **Check Documentation**: Review this guide and other documentation
2. **Search Issues**: Search existing issues on GitHub
3. **Create Issue**: Create a new issue with detailed information
4. **Community**: Ask for help in the community forums

---

**Note**: Always test your application thoroughly after installation and before deploying to production. 