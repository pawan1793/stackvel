<?php

namespace Console;

use Stackvel\Application;

/**
 * Stackvel Framework - Console Kernel
 * 
 * Handles CLI commands and scheduled tasks.
 */
class Kernel
{
    /**
     * Application instance
     */
    protected Application $app;

    /**
     * Available commands
     */
    protected array $commands = [];

    /**
     * Scheduled tasks
     */
    protected array $scheduledTasks = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->registerCommands();
        $this->registerScheduledTasks();
    }

    /**
     * Register available commands
     */
    protected function registerCommands(): void
    {
        $this->commands = [
            'help' => 'Show available commands',
            'version' => 'Show framework version',
            'serve' => 'Start development server',
            'migrate' => 'Run database migrations',
            'seed' => 'Seed database with sample data',
            'clear-cache' => 'Clear application cache',
            'optimize' => 'Optimize application for production',
            'schedule' => 'Run scheduled tasks',
            'make:controller' => 'Create a new controller',
            'make:model' => 'Create a new model',
            'make:migration' => 'Create a new migration',
            'update:check' => 'Check for framework updates',
            'update:framework' => 'Update framework to latest version'
        ];
    }

    /**
     * Register scheduled tasks
     */
    protected function registerScheduledTasks(): void
    {
        $this->scheduledTasks = [
            'daily' => [
                'cleanup:logs' => 'Clean up old log files',
                'backup:database' => 'Create database backup',
                'send:reports' => 'Send daily reports',
                'cleanup:temp' => 'Clean up temporary files',
                'send:newsletter' => 'Send daily newsletter'
            ],
            'hourly' => [
                'check:system' => 'Check system health',
                'process:queue' => 'Process queued jobs',
                'sync:data' => 'Sync data with external APIs',
                'check:disk-space' => 'Check available disk space'
            ],
            'every-minute' => [
                'monitor:services' => 'Monitor external services',
                'check:heartbeat' => 'Check application heartbeat'
            ]
        ];
    }

    /**
     * Handle console command
     */
    public function handle(array $args): int
    {
        $command = $args[1] ?? 'help';
        
        switch ($command) {
            case 'help':
                return $this->showHelp();
            
            case 'version':
                return $this->showVersion();
            
            case 'serve':
                return $this->serve();
            
            case 'migrate':
                return $this->migrate();
            
            case 'seed':
                return $this->seed();
            
            case 'clear-cache':
                return $this->clearCache();
            
            case 'optimize':
                return $this->optimize();
            
            case 'schedule':
                return $this->runScheduledTasks();
            
            case 'make:controller':
                return $this->makeController($args[2] ?? null);
            
            case 'make:model':
                return $this->makeModel($args[2] ?? null);
            
            case 'make:migration':
                return $this->makeMigration($args[2] ?? null);
            
            case 'update:check':
                return $this->checkForUpdates();
            
            case 'update:framework':
                return $this->updateFramework();
            
            default:
                echo "Unknown command: {$command}\n";
                echo "Use 'php console.php help' to see available commands.\n";
                return 1;
        }
    }

    /**
     * Show help information
     */
    protected function showHelp(): int
    {
        echo "Stackvel Framework Console\n";
        echo "========================\n\n";
        echo "Available commands:\n\n";
        
        foreach ($this->commands as $command => $description) {
            echo "  {$command}\n";
            echo "    {$description}\n\n";
        }
        
        return 0;
    }

    /**
     * Show framework version
     */
    protected function showVersion(): int
    {
        echo "Stackvel Framework v" . $this->app->version() . "\n";
        echo "PHP " . PHP_VERSION . "\n";
        return 0;
    }

    /**
     * Start development server
     */
    protected function serve(): int
    {
        $host = '127.0.0.1';
        $port = 8000;
        
        echo "Starting development server at http://{$host}:{$port}\n";
        echo "Press Ctrl+C to stop.\n\n";
        
        $command = "php -S {$host}:{$port} -t " . APP_ROOT . "/public";
        system($command);
        
        return 0;
    }

    /**
     * Run database migrations
     */
    protected function migrate(): int
    {
        echo "Running database migrations...\n";
        
        $migrationsPath = APP_ROOT . '/database/migrations';
        
        if (!is_dir($migrationsPath)) {
            echo "No migrations directory found.\n";
            return 0;
        }
        
        $migrations = glob($migrationsPath . '/*.php');
        
        if (empty($migrations)) {
            echo "No migrations found.\n";
            return 0;
        }
        
        foreach ($migrations as $migration) {
            $filename = basename($migration);
            echo "Running migration: {$filename}\n";
            
            // Include and run migration
            $migrationClass = require $migration;
            if (is_object($migrationClass) && method_exists($migrationClass, 'up')) {
                $migrationClass->up();
            }
        }
        
        echo "Migrations completed successfully.\n";
        return 0;
    }

    /**
     * Seed database with sample data
     */
    protected function seed(): int
    {
        echo "Seeding database...\n";
        
        $seedsPath = APP_ROOT . '/database/seeds';
        
        if (!is_dir($seedsPath)) {
            echo "No seeds directory found.\n";
            return 0;
        }
        
        $seeds = glob($seedsPath . '/*.php');
        
        if (empty($seeds)) {
            echo "No seeders found.\n";
            return 0;
        }
        
        foreach ($seeds as $seed) {
            $filename = basename($seed);
            echo "Running seeder: {$filename}\n";
            
            // Include and run seeder
            $seederClass = require $seed;
            if (is_object($seederClass) && method_exists($seederClass, 'run')) {
                $seederClass->run();
            }
        }
        
        echo "Database seeded successfully.\n";
        return 0;
    }

    /**
     * Clear application cache
     */
    protected function clearCache(): int
    {
        echo "Clearing application cache...\n";
        
        $cachePath = APP_ROOT . '/storage/cache';
        
        if (is_dir($cachePath)) {
            $this->removeDirectory($cachePath);
            echo "Cache cleared successfully.\n";
        } else {
            echo "No cache directory found.\n";
        }
        
        return 0;
    }

    /**
     * Optimize application for production
     */
    protected function optimize(): int
    {
        echo "Optimizing application for production...\n";
        
        // Clear cache
        $this->clearCache();
        
        // Optimize autoloader
        echo "Optimizing autoloader...\n";
        system('composer dump-autoload --optimize');
        
        echo "Application optimized successfully.\n";
        return 0;
    }

    /**
     * Run scheduled tasks
     */
    protected function runScheduledTasks(): int
    {
        echo "Running scheduled tasks...\n";
        
        $frequency = $_SERVER['argv'][2] ?? 'daily';
        
        if (!isset($this->scheduledTasks[$frequency])) {
            echo "Unknown frequency: {$frequency}\n";
            echo "Available frequencies: " . implode(', ', array_keys($this->scheduledTasks)) . "\n";
            return 1;
        }
        
        $tasks = $this->scheduledTasks[$frequency];
        
        foreach ($tasks as $task => $description) {
            echo "Running task: {$task} - {$description}\n";
            $this->runTask($task);
        }
        
        echo "Scheduled tasks completed.\n";
        return 0;
    }

    /**
     * Run a specific task
     */
    protected function runTask(string $task): void
    {
        switch ($task) {
            case 'cleanup:logs':
                $this->cleanupLogs();
                break;
            
            case 'backup:database':
                $this->backupDatabase();
                break;
            
            case 'send:reports':
                $this->sendReports();
                break;
            
            case 'cleanup:temp':
                $this->cleanupTemp();
                break;
            
            case 'send:newsletter':
                $this->sendNewsletter();
                break;
            
            case 'check:system':
                $this->checkSystem();
                break;
            
            case 'process:queue':
                $this->processQueue();
                break;
            
            case 'sync:data':
                $this->syncData();
                break;
            
            case 'check:disk-space':
                $this->checkDiskSpace();
                break;
            
            case 'monitor:services':
                $this->monitorServices();
                break;
            
            case 'check:heartbeat':
                $this->checkHeartbeat();
                break;
            
            default:
                echo "Unknown task: {$task}\n";
        }
    }

    /**
     * Create a new controller
     */
    protected function makeController(?string $name): int
    {
        if (!$name) {
            echo "Controller name is required.\n";
            echo "Usage: php console.php make:controller ControllerName\n";
            return 1;
        }
        
        $controllerPath = APP_ROOT . "/app/Controllers/{$name}.php";
        
        if (file_exists($controllerPath)) {
            echo "Controller already exists: {$name}\n";
            return 1;
        }
        
        $template = $this->getControllerTemplate($name);
        file_put_contents($controllerPath, $template);
        
        echo "Controller created successfully: {$name}\n";
        return 0;
    }

    /**
     * Create a new model
     */
    protected function makeModel(?string $name): int
    {
        if (!$name) {
            echo "Model name is required.\n";
            echo "Usage: php console.php make:model ModelName\n";
            return 1;
        }
        
        $modelPath = APP_ROOT . "/app/Models/{$name}.php";
        
        if (file_exists($modelPath)) {
            echo "Model already exists: {$name}\n";
            return 1;
        }
        
        $template = $this->getModelTemplate($name);
        file_put_contents($modelPath, $template);
        
        echo "Model created successfully: {$name}\n";
        return 0;
    }

    /**
     * Create a new migration
     */
    protected function makeMigration(?string $name): int
    {
        if (!$name) {
            echo "Migration name is required.\n";
            echo "Usage: php console.php make:migration MigrationName\n";
            return 1;
        }
        
        $timestamp = date('Y_m_d_His');
        $migrationPath = APP_ROOT . "/database/migrations/{$timestamp}_{$name}.php";
        
        $template = $this->getMigrationTemplate($name);
        file_put_contents($migrationPath, $template);
        
        echo "Migration created successfully: {$timestamp}_{$name}\n";
        return 0;
    }

    /**
     * Get controller template
     */
    protected function getControllerTemplate(string $name): string
    {
        return "<?php

namespace App\Controllers;

/**
 * {$name} Controller
 */
class {$name} extends Controller
{
    /**
     * Display a listing of the resource
     */
    public function index(): string
    {
        return \$this->view('{$name}.index');
    }

    /**
     * Show the form for creating a new resource
     */
    public function create(): string
    {
        return \$this->view('{$name}.create');
    }

    /**
     * Store a newly created resource
     */
    public function store(): void
    {
        // Implementation here
    }

    /**
     * Display the specified resource
     */
    public function show(string \$id): string
    {
        return \$this->view('{$name}.show');
    }

    /**
     * Show the form for editing the specified resource
     */
    public function edit(string \$id): string
    {
        return \$this->view('{$name}.edit');
    }

    /**
     * Update the specified resource
     */
    public function update(string \$id): void
    {
        // Implementation here
    }

    /**
     * Remove the specified resource
     */
    public function destroy(string \$id): void
    {
        // Implementation here
    }
}";
    }

    /**
     * Get model template
     */
    protected function getModelTemplate(string $name): string
    {
        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)) . 's';
        
        return "<?php

namespace App\Models;

use Stackvel\Model;

/**
 * {$name} Model
 */
class {$name} extends Model
{
    /**
     * The table associated with the model
     */
    protected string \$table = '{$tableName}';

    /**
     * The attributes that are mass assignable
     */
    protected array \$fillable = [
        // Add fillable attributes here
    ];

    /**
     * The attributes that should be hidden for arrays
     */
    protected array \$hidden = [
        // Add hidden attributes here
    ];
}";
    }

    /**
     * Get migration template
     */
    protected function getMigrationTemplate(string $name): string
    {
        return "<?php

/**
 * {$name} Migration
 */
return new class {
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Migration logic here
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Rollback logic here
    }
};";
    }

    /**
     * Remove directory recursively
     */
    protected function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        
        $files = glob($path . '/*');
        
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->removeDirectory($file);
            } else {
                unlink($file);
            }
        }
        
        rmdir($path);
    }

    // Task implementations (simplified examples)

    protected function cleanupLogs(): void
    {
        echo "Cleaning up old log files...\n";
        $logPath = APP_ROOT . '/storage/logs';
        if (is_dir($logPath)) {
            $files = glob($logPath . '/*.log');
            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-7 days')) {
                    unlink($file);
                    echo "Deleted old log file: " . basename($file) . "\n";
                }
            }
        }
    }

    protected function backupDatabase(): void
    {
        echo "Creating database backup...\n";
        $backupPath = APP_ROOT . '/storage/backups';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }
        
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupPath . '/' . $filename;
        
        // This is a simplified example - you'd use mysqldump in production
        echo "Database backup created: {$filename}\n";
    }

    protected function sendReports(): void
    {
        echo "Sending daily reports...\n";
        // Example: Send daily reports to administrators
        $mailer = $this->app->mailer;
        $mailer->send(
            'admin@example.com',
            'Daily System Report',
            '<h1>Daily System Report</h1><p>System is running smoothly.</p>'
        );
    }

    protected function cleanupTemp(): void
    {
        echo "Cleaning up temporary files...\n";
        $tempPath = APP_ROOT . '/storage/temp';
        if (is_dir($tempPath)) {
            $files = glob($tempPath . '/*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < strtotime('-1 day')) {
                    unlink($file);
                    echo "Deleted temp file: " . basename($file) . "\n";
                }
            }
        }
    }

    protected function sendNewsletter(): void
    {
        echo "Sending daily newsletter...\n";
        // Example: Send newsletter to subscribers
        $subscribers = ['user1@example.com', 'user2@example.com'];
        $mailer = $this->app->mailer;
        
        foreach ($subscribers as $subscriber) {
            $mailer->send(
                $subscriber,
                'Daily Newsletter',
                '<h1>Daily Newsletter</h1><p>Here are today\'s updates...</p>'
            );
        }
    }

    protected function checkSystem(): void
    {
        echo "Checking system health...\n";
        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        echo "Memory usage: " . round($memoryUsage / 1024 / 1024, 2) . "MB\n";
        
        // Check disk space
        $diskFree = disk_free_space(APP_ROOT);
        $diskTotal = disk_total_space(APP_ROOT);
        $diskUsage = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);
        echo "Disk usage: {$diskUsage}%\n";
    }

    protected function processQueue(): void
    {
        echo "Processing queued jobs...\n";
        // Example: Process queued jobs from database
        $queuePath = APP_ROOT . '/storage/queue';
        if (is_dir($queuePath)) {
            $jobs = glob($queuePath . '/*.job');
            foreach ($jobs as $job) {
                echo "Processing job: " . basename($job) . "\n";
                // Process the job here
                unlink($job); // Remove processed job
            }
        }
    }

    protected function syncData(): void
    {
        echo "Syncing data with external APIs...\n";
        // Example: Sync data with external services
        $apis = ['api1.example.com', 'api2.example.com'];
        foreach ($apis as $api) {
            echo "Syncing with {$api}...\n";
            // Perform API sync here
        }
    }

    protected function checkDiskSpace(): void
    {
        echo "Checking available disk space...\n";
        $diskFree = disk_free_space(APP_ROOT);
        $diskFreeGB = round($diskFree / 1024 / 1024 / 1024, 2);
        echo "Available disk space: {$diskFreeGB}GB\n";
        
        if ($diskFree < 1024 * 1024 * 1024) { // Less than 1GB
            echo "WARNING: Low disk space!\n";
            // Send alert email
            $this->app->mailer->send(
                'admin@example.com',
                'Low Disk Space Alert',
                'Server is running low on disk space!'
            );
        }
    }

    protected function monitorServices(): void
    {
        echo "Monitoring external services...\n";
        $services = [
            'database' => 'localhost:3306',
            'redis' => 'localhost:6379',
            'api' => 'api.example.com'
        ];
        
        foreach ($services as $service => $endpoint) {
            echo "Checking {$service}...\n";
            // Perform health check here
        }
    }

    protected function checkHeartbeat(): void
    {
        echo "Checking application heartbeat...\n";
        $heartbeatFile = APP_ROOT . '/storage/heartbeat.txt';
        file_put_contents($heartbeatFile, date('Y-m-d H:i:s'));
        echo "Heartbeat updated: " . date('Y-m-d H:i:s') . "\n";
    }

    /**
     * Check for framework updates
     */
    protected function checkForUpdates(): int
    {
        echo "Checking for Stackvel Framework updates...\n";
        
        $currentVersion = $this->getCurrentVersion();
        echo "Current version: {$currentVersion}\n";
        
        // Get latest version from Packagist
        $latestVersion = $this->getLatestVersion();
        
        if ($latestVersion === false) {
            echo "Could not check for updates. Please check your internet connection.\n";
            return 1;
        }
        
        echo "Latest version: {$latestVersion}\n";
        
        if (version_compare($currentVersion, $latestVersion, '<')) {
            echo "\n🎉 A new version is available!\n";
            echo "Run 'composer update pawanmore/stackvel' to update.\n";
            echo "Or run 'php console.php update:framework' for automatic update.\n";
            return 0;
        } else {
            echo "\n✅ You are running the latest version.\n";
            return 0;
        }
    }

    /**
     * Update framework to latest version
     */
    protected function updateFramework(): int
    {
        echo "Updating Stackvel Framework...\n";
        
        $currentVersion = $this->getCurrentVersion();
        echo "Current version: {$currentVersion}\n";
        
        // Get latest version
        $latestVersion = $this->getLatestVersion();
        
        if ($latestVersion === false) {
            echo "Could not check for updates. Please check your internet connection.\n";
            return 1;
        }
        
        if (version_compare($currentVersion, $latestVersion, '>=')) {
            echo "You are already running the latest version.\n";
            return 0;
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
            return 1;
        }
        
        // Update version file
        file_put_contents(APP_ROOT . '/VERSION', $latestVersion);
        
        // Clear cache
        $this->clearCache();
        
        // Optimize autoloader
        echo "Optimizing autoloader...\n";
        system('composer dump-autoload --optimize');
        
        echo "\n✅ Framework updated successfully to version {$latestVersion}!\n";
        echo "Please review the changelog for any breaking changes.\n";
        
        return 0;
    }

    /**
     * Get current framework version
     */
    protected function getCurrentVersion(): string
    {
        $versionFile = APP_ROOT . '/VERSION';
        
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        
        // Fallback to Application class version
        return $this->app->version();
    }

    /**
     * Get latest version from Packagist
     */
    protected function getLatestVersion(): string|false
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
     * Create backup before update
     */
    protected function createBackup(): void
    {
        echo "Creating backup...\n";
        
        $backupDir = APP_ROOT . '/storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . '/backup_' . $timestamp . '.tar.gz';
        
        $command = "tar -czf {$backupFile} --exclude=vendor --exclude=storage/backups --exclude=.git " . APP_ROOT;
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            echo "Backup created: {$backupFile}\n";
        } else {
            echo "Warning: Could not create backup. Continuing with update...\n";
        }
    }
}; 