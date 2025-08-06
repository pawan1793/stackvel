# Changelog

All notable changes to Stackvel Framework will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Composer-based update system for projects created with `composer create-project`
- Automatic version checking against Packagist
- Built-in backup creation before updates
- Update commands: `update:check` and `update:framework`
- Version tracking with `VERSION` file
- Post-update optimization scripts
- Comprehensive update documentation

### Changed
- Enhanced `composer.json` with update-related scripts
- Improved console commands with update functionality
- Added storage directories creation in post-install scripts

## [1.0.2] - 2024-08-06

### 🏷️ Release v1.0.2 – Error Handling, Pagination & Query Builder 🚀

This release brings critical improvements to error handling and introduces powerful database query tools.

#### 🔧 Enhancements:

- ✅ **Application**: Added exception handling for uninitialized instance access.
- ✅ **Model**: Improved error reporting during database connection and initialization.
- ✅ **Pagination**: Introduced `Paginator` class for managing paginated data with metadata.
- ✅ **Query Builder**: Added `QueryBuilder` class for fluent and expressive query building, including support for:
  - where, orWhere, orderBy, limit, offset, paginate, etc.
- 🔄 **Model Refactor**: Updated internal methods to utilize `QueryBuilder` for more robust and flexible querying.

These changes make Stackvel more **resilient to errors** and much **easier to use for complex database operations**.

## [1.0.1] - 2024-08-06

### 🏷️ Release v1.0.1 – Multiple Database Connections Support ⚙️

This patch release introduces support for **multiple database connections** and improves database handling architecture.

#### 🔧 What's New:

- ✅ Added `DatabaseManager` for managing multiple database connections.
- ✅ Enhanced `Model` class to allow specifying connection per model.
- ✅ Refactored `Application` and `Config` to support new database setup.
- ✅ Updated `README.md` with usage instructions for multi-DB configurations.

## [1.0.0] - 2024-08-06

### 🏷️ Release v1.0.0 – Initial Stable Release 🎉

We're excited to announce the first stable release of **Stackvel**, a lightweight and secure PHP MVC framework

#### ✅ What's included:

- 🧱 MVC architecture with clean project structure
- 🔐 Built-in CSRF protection and input validation
- 🧵 Blade-style templating with layouts and directives
- 🗂 Eloquent-style ORM with PDO support
- 📧 PHPMailer integration for sending HTML emails
- 🛠 CLI tools for development, migration, and maintenance
- 🕑 Cronjob support via scheduled task system
- ⚙️ .env-based configuration
- 🔄 Secure session and flash messaging
- 🧪 PHPUnit-ready for testing

Whether you're building a small project or a full-stack app, **Stackvel** gives you maximum control with minimal setup.

### Features
- **Routing**: Flexible URL routing with parameter support
- **Controllers**: MVC pattern with dependency injection
- **Models**: Eloquent-style database operations
- **Views**: Blade templating with layouts and components
- **Database**: PDO-based with multiple connection support
- **Email**: HTML email support with templates
- **Console**: CLI commands for development and maintenance
- **Security**: Built-in security features and best practices
- **Performance**: Optimized for speed and efficiency

## Version History

### Version 1.0.2
- **Release Date**: August 6, 2024
- **Status**: Stable
- **PHP Requirement**: >= 8.0
- **Key Features**: Error handling improvements, pagination, and query builder

### Version 1.0.1
- **Release Date**: August 6, 2024
- **Status**: Stable
- **PHP Requirement**: >= 8.0
- **Key Features**: Multiple database connections support

### Version 1.0.0
- **Release Date**: August 6, 2024
- **Status**: Stable
- **PHP Requirement**: >= 8.0
- **Key Features**: Complete MVC framework with all core functionality

---

## Update Instructions

### Updating from Previous Versions

#### From 1.0.0 to 1.0.2
```bash
# Check for updates
php console.php update:check

# Update to latest version
php console.php update:framework
```

#### From 1.0.1 to 1.0.2
```bash
# Update to latest version
php console.php update:framework
```

### Breaking Changes

#### Version 1.0.2
- **Model Refactor**: Internal methods now use `QueryBuilder` - check your custom model extensions
- **Database Manager**: Database connection handling has been refactored

#### Version 1.0.1
- **Database Architecture**: Database connection handling has been updated to support multiple connections

#### Version 1.0.0
- Initial release - no breaking changes

### Deprecations

None in current version.

### Security Updates

All security updates will be documented here with severity levels and recommended actions.

---

## Contributing

When contributing to this project, please update this changelog with:

1. **Added** for new features
2. **Changed** for changes in existing functionality
3. **Deprecated** for soon-to-be removed features
4. **Removed** for now removed features
5. **Fixed** for any bug fixes
6. **Security** for security vulnerability fixes

## Release Process

1. Update version in `composer.json`
2. Update version in `core/Application.php`
3. Update version in `VERSION` file
4. Update this changelog
5. Create git tag
6. Push to repository
7. Update Packagist

---

**Note**: This changelog is maintained manually. For automated changelog generation, consider using tools like [Conventional Changelog](https://github.com/conventional-changelog/conventional-changelog). 