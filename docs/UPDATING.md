# Updating Stackvel Framework

This guide explains how to update your Stackvel Framework project to the latest version.

## Overview

Stackvel Framework supports automatic updates for projects created with `composer create-project pawanmore/stackvel`. The update system includes:

- Version checking against Packagist
- Automatic backup creation
- Composer-based updates
- Post-update optimization
- Rollback capabilities

## Prerequisites

Before updating, ensure you have:

- Composer installed and accessible
- Internet connection to check for updates
- Write permissions to your project directory
- A backup of your current project (recommended)

## Update Methods

### Method 1: Automatic Update (Recommended)

Use the built-in update command:

```bash
# Check for available updates
php console.php update:check

# Update to the latest version
php console.php update:framework
```

### Method 2: Composer Update

Update using Composer directly:

```bash
# Update the framework package
composer update pawanmore/stackvel

# The post-update script will automatically run
```

### Method 3: Manual Update

For more control over the update process:

```bash
# 1. Check current version
php console.php version

# 2. Create manual backup
tar -czf backup_$(date +%Y-%m-%d_%H-%M-%S).tar.gz --exclude=vendor --exclude=.git .

# 3. Update via Composer
composer update pawanmore/stackvel --no-dev --optimize-autoloader

# 4. Clear cache
php console.php clear-cache

# 5. Optimize for production
php console.php optimize
```

## Update Process

When you run an update, the following steps are executed:

1. **Version Check**: Compares your current version with the latest available
2. **Backup Creation**: Creates a timestamped backup in `storage/backups/`
3. **Composer Update**: Updates the framework package via Composer
4. **Post-Update Scripts**: Runs framework-specific update tasks
5. **Cache Clear**: Clears application cache
6. **Autoloader Optimization**: Optimizes the Composer autoloader
7. **Version Update**: Updates the `VERSION` file

## Available Commands

### Check for Updates

```bash
php console.php update:check
```

This command:
- Shows your current version
- Fetches the latest version from Packagist
- Compares versions and shows update status
- Provides update instructions if a new version is available

### Update Framework

```bash
php console.php update:framework
```

This command:
- Performs all update steps automatically
- Creates a backup before updating
- Handles the entire update process
- Provides status updates throughout

### Composer Scripts

The following Composer scripts are available:

```bash
# Check for updates
composer check-updates

# Update framework
composer update

# Run tests after update
composer test
```

## Backup and Rollback

### Automatic Backups

The update system automatically creates backups in `storage/backups/` with the format:
```
backup_YYYY-MM-DD_HH-MM-SS.tar.gz
```

### Manual Rollback

If you need to rollback to a previous version:

```bash
# 1. Stop your application
# 2. Restore from backup
tar -xzf storage/backups/backup_YYYY-MM-DD_HH-MM-SS.tar.gz

# 3. Update composer.lock to match the backup version
composer install --lock

# 4. Clear cache
php console.php clear-cache
```

## Version Management

### Version File

The current version is stored in the `VERSION` file at the project root. This file is automatically updated during the update process.

### Version Checking

You can check your current version using:

```bash
# Show framework version
php console.php version

# Or check the VERSION file directly
cat VERSION
```

## Troubleshooting

### Update Fails

If the update fails:

1. **Check Composer**: Ensure Composer is working correctly
   ```bash
   composer diagnose
   ```

2. **Check Permissions**: Ensure write permissions to the project directory
   ```bash
   chmod -R 755 .
   ```

3. **Manual Update**: Try updating manually
   ```bash
   composer update pawanmore/stackvel --verbose
   ```

4. **Clear Composer Cache**: Clear Composer's cache
   ```bash
   composer clear-cache
   ```

### Version Conflicts

If you encounter version conflicts:

1. **Check Dependencies**: Review your `composer.json` for conflicting requirements
2. **Update Dependencies**: Update other packages that might conflict
   ```bash
   composer update
   ```
3. **Resolve Conflicts**: Manually resolve version conflicts in `composer.json`

### Backup Issues

If backup creation fails:

1. **Check Disk Space**: Ensure sufficient disk space
2. **Check Permissions**: Verify write permissions to `storage/backups/`
3. **Manual Backup**: Create a manual backup before updating

## Best Practices

### Before Updating

1. **Test Environment**: Always test updates in a development environment first
2. **Backup**: Create a manual backup before major updates
3. **Review Changelog**: Check the changelog for breaking changes
4. **Dependencies**: Ensure all dependencies are compatible

### During Update

1. **Monitor Output**: Watch for any error messages during the update
2. **Don't Interrupt**: Don't interrupt the update process
3. **Check Logs**: Review logs if issues occur

### After Update

1. **Test Application**: Test your application thoroughly
2. **Check Features**: Verify all features work as expected
3. **Update Documentation**: Update any custom documentation
4. **Monitor Performance**: Monitor application performance

## Security Updates

For security updates:

1. **Immediate Update**: Apply security updates immediately
2. **Production Priority**: Security updates should be prioritized in production
3. **Testing**: Even security updates should be tested when possible
4. **Monitoring**: Monitor for any security-related issues after update

## Version Compatibility

### Breaking Changes

Major version updates may include breaking changes. Always:

1. **Read Changelog**: Review the changelog for breaking changes
2. **Update Code**: Update your application code to match new APIs
3. **Test Thoroughly**: Test all functionality after major updates
4. **Gradual Migration**: Consider gradual migration for major changes

### Supported Versions

- **Current Version**: Latest stable release
- **Previous Version**: One major version back (for critical fixes)
- **LTS Versions**: Long-term support versions when available

## Support

If you encounter issues during updates:

1. **Check Documentation**: Review this guide and other documentation
2. **Search Issues**: Search existing issues on GitHub
3. **Create Issue**: Create a new issue with detailed information
4. **Community**: Ask for help in the community forums

## Changelog

For detailed information about changes in each version, see the [CHANGELOG.md](../CHANGELOG.md) file.

---

**Note**: Always test updates in a development environment before applying them to production. 