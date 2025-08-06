#!/usr/bin/env php
<?php

/**
 * Stackvel Framework - Standalone Update Script
 * 
 * This script can be used to test the update functionality
 * without going through the full console system.
 */

// Define the application root path
define('APP_ROOT', dirname(__DIR__));

// Load Composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables if .env exists
if (file_exists(APP_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
    $dotenv->load();
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

/**
 * Update Manager Class
 */
class UpdateManager
{
    private string $appRoot;
    private string $currentVersion;
    
    public function __construct()
    {
        $this->appRoot = APP_ROOT;
        $this->currentVersion = $this->getCurrentVersion();
    }
    
    /**
     * Check for updates
     */
    public function checkForUpdates(): bool
    {
        echo "Checking for Stackvel Framework updates...\n";
        echo "Current version: {$this->currentVersion}\n";
        
        $latestVersion = $this->getLatestVersion();
        
        if ($latestVersion === false) {
            echo "Could not check for updates. Please check your internet connection.\n";
            return false;
        }
        
        echo "Latest version: {$latestVersion}\n";
        
        if (version_compare($this->currentVersion, $latestVersion, '<')) {
            echo "\n🎉 A new version is available!\n";
            echo "Run 'composer update pawanmore/stackvel' to update.\n";
            return true;
        } else {
            echo "\n✅ You are running the latest version.\n";
            return false;
        }
    }
    
    /**
     * Update framework
     */
    public function updateFramework(): bool
    {
        echo "Updating Stackvel Framework...\n";
        
        $latestVersion = $this->getLatestVersion();
        
        if ($latestVersion === false) {
            echo "Could not check for updates. Please check your internet connection.\n";
            return false;
        }
        
        if (version_compare($this->currentVersion, $latestVersion, '>=')) {
            echo "You are already running the latest version.\n";
            return true;
        }
        
        echo "Latest version: {$latestVersion}\n";
        echo "Updating...\n";
        
        // Create backup
        $this->createBackup();
        
        // Run composer update
        $command = 'composer update pawanmore/stackvel --no-dev --optimize-autoloader';
        echo "Running: {$command}\n";
        
        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "Update failed. Please run 'composer update pawanmore/stackvel' manually.\n";
            echo "Output:\n" . implode("\n", $output) . "\n";
            return false;
        }
        
        // Update version file
        file_put_contents($this->appRoot . '/VERSION', $latestVersion);
        
        // Clear cache
        $this->clearCache();
        
        // Optimize autoloader
        echo "Optimizing autoloader...\n";
        system('composer dump-autoload --optimize');
        
        echo "\n✅ Framework updated successfully to version {$latestVersion}!\n";
        echo "Please review the changelog for any breaking changes.\n";
        
        return true;
    }
    
    /**
     * Get current version
     */
    private function getCurrentVersion(): string
    {
        $versionFile = $this->appRoot . '/VERSION';
        
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        
        // Fallback to Application class version
        return '1.0.0';
    }
    
    /**
     * Get latest version from Packagist
     */
    private function getLatestVersion(): string|false
    {
        $url = 'https://packagist.org/packages/pawanmore/stackvel.json';
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Stackvel-Framework/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['package']['versions'])) {
            return false;
        }
        
        // Get the latest stable version
        $versions = array_keys($data['package']['versions']);
        $stableVersions = array_filter($versions, function($version) {
            return !str_contains($version, 'dev') && !str_contains($version, 'alpha') && !str_contains($version, 'beta');
        });
        
        if (empty($stableVersions)) {
            return false;
        }
        
        // Sort versions and return the latest
        usort($stableVersions, 'version_compare');
        return end($stableVersions);
    }
    
    /**
     * Create backup
     */
    private function createBackup(): void
    {
        echo "Creating backup...\n";
        
        $backupDir = $this->appRoot . '/storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . '/backup_' . $timestamp . '.tar.gz';
        
        $command = "tar -czf {$backupFile} --exclude=vendor --exclude=storage/backups --exclude=.git " . $this->appRoot;
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "Backup created: {$backupFile}\n";
        } else {
            echo "Warning: Could not create backup. Continuing with update...\n";
        }
    }
    
    /**
     * Clear cache
     */
    private function clearCache(): void
    {
        echo "Clearing application cache...\n";
        
        $cachePath = $this->appRoot . '/storage/cache';
        
        if (is_dir($cachePath)) {
            $this->removeDirectory($cachePath);
            echo "Cache cleared successfully.\n";
        } else {
            echo "No cache directory found.\n";
        }
    }
    
    /**
     * Remove directory recursively
     */
    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        
        $files = array_diff(scandir($path), ['.', '..']);
        
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        
        rmdir($path);
    }
}

// Handle command line arguments
$command = $argv[1] ?? 'check';

$updateManager = new UpdateManager();

switch ($command) {
    case 'check':
        $updateManager->checkForUpdates();
        break;
        
    case 'update':
        $updateManager->updateFramework();
        break;
        
    default:
        echo "Usage: php scripts/update.php [check|update]\n";
        echo "  check  - Check for available updates\n";
        echo "  update - Update to the latest version\n";
        break;
} 