<?php

namespace Stackvel;

use PDO;
use PDOException;

/**
 * Stackvel Framework - Database Manager Class
 * 
 * Manages multiple database connections and provides a unified interface
 * for accessing different database connections.
 */
class DatabaseManager
{
    /**
     * Configuration instance
     */
    private Config $config;

    /**
     * Active database connections
     */
    private array $connections = [];

    /**
     * Default connection name
     */
    private string $defaultConnection;

    /**
     * Constructor
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->defaultConnection = $config->getDefaultDatabaseConnection();
    }

    /**
     * Get a database connection
     */
    public function connection(?string $name = null): Database
    {
        $name = $name ?: $this->defaultConnection;

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->createConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * Create a new database connection
     */
    private function createConnection(string $name): Database
    {
        $config = $this->config->getDatabaseConnection($name);

        if (!$config) {
            throw new \Exception("Database connection '{$name}' not found in configuration.");
        }

        return new Database($config);
    }

    /**
     * Get the default connection
     */
    public function getDefaultConnection(): Database
    {
        return $this->connection();
    }

    /**
     * Set the default connection
     */
    public function setDefaultConnection(string $name): void
    {
        $this->defaultConnection = $name;
    }

    /**
     * Check if a connection exists
     */
    public function hasConnection(string $name): bool
    {
        return $this->config->getDatabaseConnection($name) !== null;
    }

    /**
     * Get all connection names
     */
    public function getConnectionNames(): array
    {
        return array_keys($this->config->getDatabaseConnections());
    }

    /**
     * Close a specific connection
     */
    public function closeConnection(string $name): void
    {
        if (isset($this->connections[$name])) {
            $this->connections[$name]->close();
            unset($this->connections[$name]);
        }
    }

    /**
     * Close all connections
     */
    public function closeAllConnections(): void
    {
        foreach ($this->connections as $connection) {
            $connection->close();
        }
        $this->connections = [];
    }

    /**
     * Get connection statistics
     */
    public function getConnectionStats(): array
    {
        return [
            'default' => $this->defaultConnection,
            'active_connections' => array_keys($this->connections),
            'available_connections' => $this->getConnectionNames(),
            'total_connections' => count($this->connections)
        ];
    }

    /**
     * Magic method to get a connection
     */
    public function __get(string $name): Database
    {
        return $this->connection($name);
    }
} 