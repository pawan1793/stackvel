<?php

namespace Stackvel;

use PDO;
use PDOException;

/**
 * Stackvel Framework - Database Class
 * 
 * Provides PDO-based database connectivity and Eloquent-style ORM functionality.
 */
class Database
{
    /**
     * PDO instance
     */
    private ?PDO $connection = null;

    /**
     * Database configuration
     */
    private array $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = [
            'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
            'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'stackvel',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];
    }

    /**
     * Get database connection
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $dsn = $this->buildDsn();
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Build DSN string
     */
    private function buildDsn(): string
    {
        $driver = $this->config['driver'];
        $host = $this->config['host'];
        $port = $this->config['port'];
        $database = $this->config['database'];
        $charset = $this->config['charset'];

        return "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";
    }

    /**
     * Execute a query
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Execute a query and return all results
     */
    public function select(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Execute a query and return first result
     */
    public function first(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Execute an insert query
     */
    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Execute an update query
     */
    public function update(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Execute a delete query
     */
    public function delete(string $sql, array $params = []): int
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollback();
    }

    /**
     * Check if currently in a transaction
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Get table name from model class
     */
    public function getTableName(string $modelClass): string
    {
        $reflection = new \ReflectionClass($modelClass);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        
        return $tableProperty->getValue(new $modelClass()) ?: $this->getDefaultTableName($modelClass);
    }

    /**
     * Get default table name from model class
     */
    private function getDefaultTableName(string $modelClass): string
    {
        $className = class_basename($modelClass);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
    }

    /**
     * Get database configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Close the database connection
     */
    public function close(): void
    {
        $this->connection = null;
    }
} 