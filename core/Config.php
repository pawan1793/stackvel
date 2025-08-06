<?php

namespace Stackvel;

use PDO;

/**
 * Stackvel Framework - Config Class
 * 
 * Provides configuration management for the framework.
 */
class Config
{
    /**
     * Configuration data
     */
    private array $config = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadDefaultConfig();
    }

    /**
     * Load default configuration
     */
    private function loadDefaultConfig(): void
    {
        $this->config = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'Stackvel',
                'env' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => $_ENV['APP_DEBUG'] ?? false,
                'url' => $_ENV['APP_URL'] ?? 'http://localhost',
                'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
                'key' => $_ENV['APP_KEY'] ?? null
            ],
            'database' => [
                'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
                'connections' => [
                    'mysql' => [
                        'driver'    => 'mysql',
                        'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
                        'port'      => $_ENV['DB_PORT'] ?? '3306',
                        'database'  => $_ENV['DB_DATABASE'] ?? 'stackvel',
                        'username'  => $_ENV['DB_USERNAME'] ?? 'root',
                        'password'  => $_ENV['DB_PASSWORD'] ?? '',
                        'charset'   => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix'    => '',
                        'strict'    => false,
                        'options'   => [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                        ]
                    ],
                    'mysql_otherdb' => [
                        'driver'    => 'mysql',
                        'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
                        'port'      => $_ENV['DB_PORT'] ?? '3306',
                        'database'  => $_ENV['DB_OTHER_DATABASE'] ?? 'otherdb',
                        'username'  => $_ENV['DB_USERNAME'] ?? 'root',
                        'password'  => $_ENV['DB_PASSWORD'] ?? '',
                        'charset'   => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix'    => '',
                        'strict'    => false,
                        'options'   => [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                        ]
                    ],
                ]
            ],
            'mail' => [
                'driver' => $_ENV['MAIL_MAILER'] ?? 'smtp',
                'host' => $_ENV['MAIL_HOST'] ?? 'smtp.mailtrap.io',
                'port' => $_ENV['MAIL_PORT'] ?? 2525,
                'username' => $_ENV['MAIL_USERNAME'] ?? null,
                'password' => $_ENV['MAIL_PASSWORD'] ?? null,
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? null,
                'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'hello@example.com',
                'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Stackvel'
            ],
            'session' => [
                'driver' => $_ENV['SESSION_DRIVER'] ?? 'file',
                'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120
            ],
            'logging' => [
                'channel' => $_ENV['LOG_CHANNEL'] ?? 'stack',
                'level' => $_ENV['LOG_LEVEL'] ?? 'debug'
            ],
            'cache' => [
                'driver' => $_ENV['CACHE_DRIVER'] ?? 'file'
            ],
            'uploads' => [
                'max_size' => $_ENV['UPLOAD_MAX_SIZE'] ?? '10M',
                'allowed_types' => $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,txt'
            ]
        ];
    }

    /**
     * Get a configuration value
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $this->config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    /**
     * Set a configuration value
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
    }

    /**
     * Check if a configuration key exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get all configuration
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Load configuration from file
     */
    public function loadFromFile(string $file): void
    {
        if (file_exists($file)) {
            $config = require $file;
            if (is_array($config)) {
                $this->config = array_merge($this->config, $config);
            }
        }
    }

    /**
     * Get application name
     */
    public function getAppName(): string
    {
        return $this->get('app.name', 'Stackvel');
    }

    /**
     * Get application environment
     */
    public function getAppEnv(): string
    {
        return $this->get('app.env', 'production');
    }

    /**
     * Check if application is in debug mode
     */
    public function isDebug(): bool
    {
        return $this->get('app.debug', false);
    }

    /**
     * Get application URL
     */
    public function getAppUrl(): string
    {
        return $this->get('app.url', 'http://localhost');
    }

    /**
     * Get application timezone
     */
    public function getAppTimezone(): string
    {
        return $this->get('app.timezone', 'UTC');
    }

    /**
     * Get application key
     */
    public function getAppKey(): ?string
    {
        return $this->get('app.key');
    }

    /**
     * Get database configuration
     */
    public function getDatabaseConfig(): array
    {
        return $this->get('database', []);
    }

    /**
     * Get database connections configuration
     */
    public function getDatabaseConnections(): array
    {
        return $this->get('database.connections', []);
    }

    /**
     * Get default database connection name
     */
    public function getDefaultDatabaseConnection(): string
    {
        return $this->get('database.default', 'mysql');
    }

    /**
     * Get specific database connection configuration
     */
    public function getDatabaseConnection(string $connection): ?array
    {
        $connections = $this->getDatabaseConnections();
        return $connections[$connection] ?? null;
    }

    /**
     * Get mail configuration
     */
    public function getMailConfig(): array
    {
        return $this->get('mail', []);
    }

    /**
     * Get session configuration
     */
    public function getSessionConfig(): array
    {
        return $this->get('session', []);
    }

    /**
     * Get logging configuration
     */
    public function getLoggingConfig(): array
    {
        return $this->get('logging', []);
    }

    /**
     * Get cache configuration
     */
    public function getCacheConfig(): array
    {
        return $this->get('cache', []);
    }

    /**
     * Get upload configuration
     */
    public function getUploadConfig(): array
    {
        return $this->get('uploads', []);
    }

    /**
     * Check if application is in production
     */
    public function isProduction(): bool
    {
        return $this->getAppEnv() === 'production';
    }

    /**
     * Check if application is in development
     */
    public function isDevelopment(): bool
    {
        return $this->getAppEnv() === 'development';
    }

    /**
     * Check if application is in testing
     */
    public function isTesting(): bool
    {
        return $this->getAppEnv() === 'testing';
    }
} 