# Stackvel Framework - Update Quick Reference

This is a quick reference guide for updating Stackvel Framework projects.

## Quick Commands

### Check for Updates
```bash
# Using console command
php console.php update:check

# Using Composer script
composer check-updates

# Using standalone script
php scripts/update.php check
```

### Update Framework
```bash
# Using console command (recommended)
php console.php update:framework

# Using Composer
composer update pawanmore/stackvel

# Using standalone script
php scripts/update.php update
```

## Update Process Overview

1. **Check Version**: Compare current vs latest version
2. **Create Backup**: Automatic timestamped backup
3. **Update Package**: Run Composer update
4. **Post-Update**: Clear cache, optimize autoloader
5. **Update Version**: Update VERSION file

## Backup Location

Backups are stored in: `storage/backups/backup_YYYY-MM-DD_HH-MM-SS.tar.gz`

## Rollback

If you need to rollback:

```bash
# Stop application
# Restore from backup
tar -xzf storage/backups/backup_YYYY-MM-DD_HH-MM-SS.tar.gz

# Update composer.lock
composer install --lock

# Clear cache
php console.php clear-cache
```

## Troubleshooting

### Update Fails
```bash
# Check Composer
composer diagnose

# Clear Composer cache
composer clear-cache

# Manual update
composer update pawanmore/stackvel --verbose
```

### Permission Issues
```bash
# Fix permissions
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env
```

### Version Conflicts
```bash
# Update all dependencies
composer update

# Check for conflicts
composer why pawanmore/stackvel
```

## Composer Scripts

Available in `composer.json`:

```bash
composer check-updates    # Check for updates
composer update          # Update framework
composer test           # Run tests after update
```

## Version Tracking

- **VERSION file**: Contains current version
- **Packagist**: Source for latest version
- **Console command**: `php console.php version`

## Best Practices

1. **Always backup** before updating
2. **Test in development** first
3. **Check changelog** for breaking changes
4. **Monitor logs** after update
5. **Test application** thoroughly

## Support

- **Documentation**: [UPDATING.md](UPDATING.md)
- **Installation**: [INSTALLATION.md](INSTALLATION.md)
- **Issues**: GitHub repository
- **Community**: Framework forums

---

**Remember**: Always test updates in a development environment before applying to production! 