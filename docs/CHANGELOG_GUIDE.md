# Changelog Guide

This guide explains how the CHANGELOG.md file is structured and how it relates to the GitHub releases.

## Structure

The changelog follows the [Keep a Changelog](https://keepachangelog.com/en/1.0.0/) format and includes:

### Version Sections

Each version has its own section with:

- **Release Date**: When the version was released
- **Release Title**: Descriptive title for the release
- **Enhancements**: New features and improvements
- **Bug Fixes**: Issues that were resolved
- **Breaking Changes**: Changes that may affect existing code
- **Deprecations**: Features that will be removed in future versions

### Current Versions

Based on the [GitHub releases](https://github.com/pawan1793/stackvel/releases):

#### v1.0.2 (Latest) - Error Handling, Pagination & Query Builder
- **Release Date**: August 6, 2024
- **Key Features**:
  - Exception handling for uninitialized instance access
  - Improved error reporting for database connections
  - Paginator class for managing paginated data
  - QueryBuilder class for fluent database queries
  - Model refactor using QueryBuilder

#### v1.0.1 - Multiple Database Connections Support
- **Release Date**: August 6, 2024
- **Key Features**:
  - DatabaseManager for multiple database connections
  - Enhanced Model class with connection specification
  - Refactored Application and Config classes
  - Updated documentation for multi-DB configurations

#### v1.0.0 - Initial Stable Release
- **Release Date**: August 6, 2024
- **Key Features**:
  - Complete MVC framework
  - Blade-style templating
  - Eloquent-style ORM
  - CLI tools and console commands
  - Security features and validation
  - Email support with PHPMailer

## Version Tracking

### Files Updated for Each Release

1. **CHANGELOG.md**: Main changelog file
2. **VERSION**: Simple version file
3. **core/Application.php**: Framework version constant
4. **composer.json**: Package version field

### Version Sources

- **GitHub Releases**: Primary source for release information
- **Packagist**: Package version information
- **Git Tags**: Version tags in the repository

## Update Process

### For New Releases

1. **Update Version Files**:
   ```bash
   # Update VERSION file
   echo "1.0.3" > VERSION
   
   # Update Application.php
   # Change const VERSION = '1.0.3';
   
   # Update composer.json
   # Change "version": "1.0.3"
   ```

2. **Update CHANGELOG.md**:
   - Add new version section at the top
   - Document all changes
   - Update version history
   - Add breaking changes if any

3. **Create Git Tag**:
   ```bash
   git tag v1.0.3
   git push origin v1.0.3
   ```

4. **Update GitHub Release**:
   - Create new release on GitHub
   - Add release notes
   - Upload assets if needed

## Breaking Changes

### v1.0.2
- Model internal methods now use QueryBuilder
- Database connection handling refactored

### v1.0.1
- Database architecture updated for multiple connections

### v1.0.0
- Initial release - no breaking changes

## Migration Guides

### From v1.0.1 to v1.0.2
- Check custom model extensions
- Review database connection usage
- Update any custom query methods

### From v1.0.0 to v1.0.2
- Follow v1.0.1 migration first
- Then follow v1.0.2 migration
- Test thoroughly after each step

## Contributing to Changelog

When contributing changes:

1. **Add Section**: Add to [Unreleased] section
2. **Categorize**: Use appropriate category (Added, Changed, Fixed, etc.)
3. **Be Specific**: Describe what changed and why
4. **Link Issues**: Reference related issues or pull requests

### Categories

- **Added**: New features
- **Changed**: Changes in existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Now removed features
- **Fixed**: Bug fixes
- **Security**: Security vulnerability fixes

## Automation

The changelog is currently maintained manually. For future automation, consider:

- [Conventional Changelog](https://github.com/conventional-changelog/conventional-changelog)
- [Release Drafter](https://github.com/release-drafter/release-drafter)
- [GitHub Actions](https://github.com/features/actions) for automated releases

## References

- [GitHub Releases](https://github.com/pawan1793/stackvel/releases)
- [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
- [Semantic Versioning](https://semver.org/spec/v2.0.0.html)

---

**Note**: Always keep the changelog up-to-date with each release to help users understand what has changed and how to migrate their applications. 