<?php

namespace Stackvel;

use Stackvel\Application;

/**
 * Stackvel Framework - Base Model Class
 * 
 * Provides Eloquent-style ORM functionality with common methods
 * like all(), find(), where(), save(), delete().
 */
abstract class Model
{
    /**
     * The table associated with the model
     */
    protected string $table;

    /**
     * The primary key for the model
     */
    protected string $primaryKey = 'id';

    /**
     * The attributes that are mass assignable
     */
    protected array $fillable = [];

    /**
     * The attributes that should be hidden for arrays
     */
    protected array $hidden = [];

    /**
     * The model's attributes
     */
    protected array $attributes = [];

    /**
     * The database instance
     */
    protected Database $database;

    /**
     * The database connection name
     */
    protected string $connection = '';

    /**
     * Constructor
     */
    public function __construct(array $attributes = [])
    {
        $app = Application::getInstance();
        
        // Use specified connection or default connection
        if (!empty($this->connection)) {
            $this->database = $app->database->connection($this->connection);
        } else {
            $this->database = $app->database->getDefaultConnection();
        }
        
        $this->fill($attributes);
    }

    /**
     * Get all records from the table
     */
    public static function all(): array
    {
        $instance = new static();
        $table = $instance->getTable();
        
        $sql = "SELECT * FROM {$table}";
        $results = $instance->database->select($sql);
        
        return array_map(function ($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Find a record by primary key
     */
    public static function find($id): ?static
    {
        $instance = new static();
        $table = $instance->getTable();
        $primaryKey = $instance->primaryKey;
        
        $sql = "SELECT * FROM {$table} WHERE {$primaryKey} = ?";
        $result = $instance->database->first($sql, [$id]);
        
        return $result ? new static($result) : null;
    }

    /**
     * Find a record by primary key or throw an exception
     */
    public static function findOrFail($id): static
    {
        $model = static::find($id);
        
        if (!$model) {
            throw new \Exception("Model not found with ID: {$id}");
        }
        
        return $model;
    }

    /**
     * Find records by a column value
     */
    public static function where(string $column, $value): array
    {
        $instance = new static();
        $table = $instance->getTable();
        
        $sql = "SELECT * FROM {$table} WHERE {$column} = ?";
        $results = $instance->database->select($sql, [$value]);
        
        return array_map(function ($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Find the first record by a column value
     */
    public static function whereFirst(string $column, $value): ?static
    {
        $instance = new static();
        $table = $instance->getTable();
        
        $sql = "SELECT * FROM {$table} WHERE {$column} = ? LIMIT 1";
        $result = $instance->database->first($sql, [$value]);
        
        return $result ? new static($result) : null;
    }

    /**
     * Create a new record
     */
    public static function create(array $attributes): static
    {
        $instance = new static($attributes);
        $instance->save();
        return $instance;
    }

    /**
     * Save the model to the database
     */
    public function save(): bool
    {
        $table = $this->getTable();
        $primaryKey = $this->primaryKey;
        
        if (isset($this->attributes[$primaryKey])) {
            // Update existing record
            return $this->update();
        } else {
            // Insert new record
            return $this->insert();
        }
    }

    /**
     * Insert a new record
     */
    private function insert(): bool
    {
        $table = $this->getTable();
        $fillableAttributes = $this->getFillableAttributes();
        
        if (empty($fillableAttributes)) {
            return false;
        }
        
        $columns = implode(', ', array_keys($fillableAttributes));
        $placeholders = ':' . implode(', :', array_keys($fillableAttributes));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $id = $this->database->insert($sql, $fillableAttributes);
        
        if ($id) {
            $this->attributes[$this->primaryKey] = $id;
            return true;
        }
        
        return false;
    }

    /**
     * Update an existing record
     */
    private function update(): bool
    {
        $table = $this->getTable();
        $primaryKey = $this->primaryKey;
        $fillableAttributes = $this->getFillableAttributes();
        
        if (empty($fillableAttributes)) {
            return false;
        }
        
        $setClause = implode(', ', array_map(function ($column) {
            return "{$column} = :{$column}";
        }, array_keys($fillableAttributes)));
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$primaryKey} = :{$primaryKey}";
        
        $fillableAttributes[$primaryKey] = $this->attributes[$primaryKey];
        
        $affectedRows = $this->database->update($sql, $fillableAttributes);
        
        return $affectedRows > 0;
    }

    /**
     * Delete the model from the database
     */
    public function delete(): bool
    {
        $table = $this->getTable();
        $primaryKey = $this->primaryKey;
        
        if (!isset($this->attributes[$primaryKey])) {
            return false;
        }
        
        $sql = "DELETE FROM {$table} WHERE {$primaryKey} = ?";
        $affectedRows = $this->database->delete($sql, [$this->attributes[$primaryKey]]);
        
        return $affectedRows > 0;
    }

    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        
        return $this;
    }

    /**
     * Get an attribute from the model
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set an attribute on the model
     */
    public function setAttribute(string $key, $value): static
    {
        if (in_array($key, $this->fillable) || empty($this->fillable)) {
            $this->attributes[$key] = $value;
        }
        
        return $this;
    }

    /**
     * Get all attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get fillable attributes
     */
    private function getFillableAttributes(): array
    {
        if (empty($this->fillable)) {
            return $this->attributes;
        }
        
        return array_intersect_key($this->attributes, array_flip($this->fillable));
    }

    /**
     * Get the table name for the model
     */
    public function getTable(): string
    {
        if (!isset($this->table)) {
            $this->table = $this->database->getTableName(static::class);
        }
        
        return $this->table;
    }

    /**
     * Convert the model to an array
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;
        
        // Hide attributes that should be hidden
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }
        
        return $attributes;
    }

    /**
     * Convert the model to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Magic method to get attributes
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic method to set attributes
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Magic method to check if attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Magic method to unset attribute
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }
} 