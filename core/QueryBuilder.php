<?php

namespace Stackvel;

/**
 * Stackvel Framework - Query Builder Class
 * 
 * Provides fluent interface for building database queries
 * with support for where conditions, orderBy, paginate, etc.
 */
class QueryBuilder
{
    /**
     * The model instance
     */
    private Model $model;

    /**
     * The table name
     */
    private string $table;

    /**
     * Where conditions
     */
    private array $whereConditions = [];

    /**
     * Order by conditions
     */
    private array $orderByConditions = [];

    /**
     * Group by conditions
     */
    private array $groupByConditions = [];

    /**
     * Select columns
     */
    private array $selectColumns = ['*'];

    /**
     * Limit
     */
    private ?int $limit = null;

    /**
     * Offset
     */
    private ?int $offset = null;

    /**
     * Constructor
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->table = $model->getTable();
    }

    /**
     * Add a where condition
     * Handles various operators and special cases like Laravel
     * Laravel-style signature: where($column, $operator, $value)
     */
    public function where($column, $operator = null, $value = null): self
    {
        // Handle Laravel-style where($column, $value) - no operator specified
        if ($value === null && $operator !== null && !in_array($operator, ['IS NULL', 'IS NOT NULL'])) {
            $value = $operator;
            $operator = '=';
        }
        
        // Ensure operator is not null
        if ($operator === null) {
            $operator = '=';
        }
        
        // Handle special cases for null values
        if ($value === null) {
            if ($operator === '=' || $operator === 'IS NULL') {
                $operator = 'IS NULL';
                $value = null;
            } elseif ($operator === '!=' || $operator === 'IS NOT NULL') {
                $operator = 'IS NOT NULL';
                $value = null;
            }
        }
        
        // Handle array values for IN clauses
        if (is_array($value) && $operator === '=') {
            $operator = 'IN';
        } elseif (is_array($value) && $operator === 'NOT IN') {
            // Keep NOT IN operator as is
        }
        
        $this->whereConditions[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        
        return $this;
    }

    /**
     * Add where conditions from array
     * Handles multiple formats like Laravel:
     * - ['column' => 'value'] (simple equality)
     * - [['column', 'operator', 'value']] (array format)
     * - [['column', '!=', null]] (null checks)
     * - Mixed array with both formats
     */
    public function whereArray(array $conditions): self
    {
        foreach ($conditions as $key => $value) {
            if (is_numeric($key)) {
                // Array format: ['column', 'operator', 'value']
                if (is_array($value) && count($value) >= 2) {
                    $column = $value[0];
                    $operator = $value[1] ?? '=';
                    $conditionValue = $value[2] ?? null;
                    $this->where($column, $operator, $conditionValue);
                }
                            } else {
                    // Key-value format: 'column' => 'value'
                    // Handle special cases like null values
                    if ($value === null) {
                        $this->where($key, 'IS NULL');
                    } else {
                        $this->where($key, '=', $value);
                    }
                }
        }
        
        return $this;
    }

    /**
     * Add where null condition
     */
    public function whereNull(string $column): self
    {
        return $this->where($column, 'IS NULL');
    }

    /**
     * Add where not null condition
     */
    public function whereNotNull(string $column): self
    {
        return $this->where($column, 'IS NOT NULL');
    }

    /**
     * Add where in condition
     */
    public function whereIn(string $column, array $values): self
    {
        return $this->where($column, 'IN', $values);
    }

    /**
     * Add where not in condition
     */
    public function whereNotIn(string $column, array $values): self
    {
        return $this->where($column, 'NOT IN', $values);
    }

    /**
     * Add where between condition
     */
    public function whereBetween(string $column, array $values): self
    {
        return $this->where($column, 'BETWEEN', $values);
    }

    /**
     * Add where not empty condition (not null and not empty string)
     */
    public function whereNotEmpty(string $column): self
    {
        $this->where($column, 'IS NOT NULL');
        $this->where($column, '!=', '');
        return $this;
    }

    /**
     * Add order by condition
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderByConditions[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        
        return $this;
    }

    /**
     * Add group by condition
     */
    public function groupBy(string $column): self
    {
        $this->groupByConditions[] = $column;
        return $this;
    }

    /**
     * Set select columns
     */
    public function select(array $columns): self
    {
        $this->selectColumns = $columns;
        return $this;
    }

    /**
     * Set raw select columns
     */
    public function selectRaw(string $raw): self
    {
        $this->selectColumns = [$raw];
        return $this;
    }

    /**
     * Set limit
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set offset
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Get the first result
     */
    public function first(): ?Model
    {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Get all results
     */
    public function get(): array
    {
        $sql = $this->buildSql();
        $params = $this->buildParams();
        
        // Check if model and database are properly set
        if (!$this->model) {
            throw new \Exception("Model is null in QueryBuilder");
        }
        
        $database = $this->model->getDatabase();
        if (!$database) {
            throw new \Exception("Database is null in Model: " . get_class($this->model));
        }
        
        $results = $database->select($sql, $params);
        
        return array_map(function ($row) {
            return new (get_class($this->model))($row);
        }, $results);
    }

    /**
     * Get results as array
     */
    public function toArray(): array
    {
        $results = $this->get();
        return array_map(function ($model) {
            return $model->toArray();
        }, $results);
    }

    /**
     * Paginate results
     */
    public function paginate(int $perPage = 15, int $page = null): Paginator
    {
        // Get current page from request if not provided
        if ($page === null) {
            $page = (int) ($_GET['page'] ?? 1);
        }

        // Get total count
        $countBuilder = clone $this;
        $countBuilder->selectColumns = ['COUNT(*) as total'];
        $countSql = $countBuilder->buildSql();
        $countParams = $countBuilder->buildParams();
        $countResult = $this->model->getDatabase()->first($countSql, $countParams);
        $total = $countResult['total'] ?? 0;

        // Calculate offset
        $offset = ($page - 1) * $perPage;
        $this->limit($perPage)->offset($offset);

        // Get paginated results
        $results = $this->get();

        return new Paginator($results, $total, $perPage, $page);
    }

    /**
     * Build the SQL query
     */
    private function buildSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->selectColumns) . " FROM {$this->table}";

        // Add where conditions
        if (!empty($this->whereConditions)) {
            $sql .= " WHERE " . $this->buildWhereClause();
        }

        // Add group by
        if (!empty($this->groupByConditions)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupByConditions);
        }

        // Add order by
        if (!empty($this->orderByConditions)) {
            $orderByClauses = array_map(function ($condition) {
                return "{$condition['column']} {$condition['direction']}";
            }, $this->orderByConditions);
            $sql .= " ORDER BY " . implode(', ', $orderByClauses);
        }

        // Add limit and offset
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }
        }



        return $sql;
    }

    /**
     * Build where clause
     */
    private function buildWhereClause(): string
    {
        $clauses = [];
        
        foreach ($this->whereConditions as $condition) {
            $column = $condition['column'];
            $operator = $condition['operator'];
            $value = $condition['value'];
            
            switch ($operator) {
                case 'IS NULL':
                case 'IS NOT NULL':
                    $clauses[] = "{$column} {$operator}";
                    break;
                    
                case 'IN':
                case 'NOT IN':
                    if (is_array($value)) {
                        $placeholders = str_repeat('?,', count($value) - 1) . '?';
                        $clauses[] = "{$column} {$operator} ({$placeholders})";
                    } else {
                        $clauses[] = "{$column} {$operator} (?)";
                    }
                    break;
                    
                case 'BETWEEN':
                    if (is_array($value) && count($value) === 2) {
                        $clauses[] = "{$column} BETWEEN ? AND ?";
                    } else {
                        $clauses[] = "{$column} = ?";
                    }
                    break;
                    
                default:
                    $clauses[] = "{$column} {$operator} ?";
                    break;
            }
        }
        
        return implode(' AND ', $clauses);
    }

    /**
     * Build parameters array
     */
    private function buildParams(): array
    {
        $params = [];
        
        foreach ($this->whereConditions as $condition) {
            $operator = $condition['operator'];
            $value = $condition['value'];
            
            switch ($operator) {
                case 'IS NULL':
                case 'IS NOT NULL':
                    // No parameters for NULL checks
                    break;
                    
                case 'IN':
                    if (is_array($value)) {
                        $params = array_merge($params, $value);
                    } else {
                        $params[] = $value;
                    }
                    break;
                    
                case 'BETWEEN':
                    if (is_array($value) && count($value) === 2) {
                        $params[] = $value[0];
                        $params[] = $value[1];
                    } else {
                        $params[] = $value;
                    }
                    break;
                    
                default:
                    $params[] = $value;
                    break;
            }
        }
        
        return $params;
    }

    /**
     * Magic method for dynamic method calls
     */
    public function __call(string $method, array $arguments)
    {
        // Handle methods like selectRaw, groupBy, etc.
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }
        
        throw new \Exception("Method {$method} not found in QueryBuilder");
    }
} 