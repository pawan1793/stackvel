<?php

namespace App\Models;

use Stackvel\Model;

/**
 * User Model
 * 
 * Represents a user in the application.
 */
class User extends Model
{
    /**
     * The table associated with the model
     */
    protected string $table = 'users';

    /**
     * The attributes that are mass assignable
     */
    protected array $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays
     */
    protected array $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Get the user's full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Check if the user's email is verified
     */
    public function isEmailVerified(): bool
    {
        return !empty($this->email_verified_at);
    }

    /**
     * Mark the user's email as verified
     */
    public function markEmailAsVerified(): bool
    {
        $this->email_verified_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Verify the user's password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * Set the user's password
     */
    public function setPassword(string $password): void
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Get users by role (example method)
     */
    public static function findByRole(string $role): array
    {
        return static::where('role', $role);
    }

    /**
     * Get active users
     */
    public static function getActive(): array
    {
        return static::where('status', 'active');
    }

    /**
     * Get users created in the last X days
     */
    public static function getRecent(int $days = 7): array
    {
        $instance = new static();
        $table = $instance->getTable();
        
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $sql = "SELECT * FROM {$table} WHERE created_at >= ?";
        $results = $instance->database->select($sql, [$date]);
        
        return array_map(function ($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Search users by name or email
     */
    public static function search(string $query): array
    {
        $instance = new static();
        $table = $instance->getTable();
        
        $searchTerm = "%{$query}%";
        
        $sql = "SELECT * FROM {$table} WHERE name LIKE ? OR email LIKE ?";
        $results = $instance->database->select($sql, [$searchTerm, $searchTerm]);
        
        return array_map(function ($row) {
            return new static($row);
        }, $results);
    }

    /**
     * Get user with posts (example relationship)
     */
    public function getWithPosts(): static
    {
        // This is a simplified example. In a real application,
        // you would implement proper relationships
        return $this;
    }

    /**
     * Get user statistics
     */
    public function getStats(): array
    {
        $instance = new static();
        $table = $instance->getTable();
        
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users
                FROM {$table}";
        
        $result = $instance->database->first($sql);
        
        return $result ?: [
            'total_users' => 0,
            'verified_users' => 0,
            'new_users' => 0
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();
        
        // Add any model boot logic here
        // For example, event listeners, global scopes, etc.
    }

    /**
     * Convert the model to an array
     */
    public function toArray(): array
    {
        $attributes = parent::toArray();
        
        // Add computed attributes
        $attributes['full_name'] = $this->getFullNameAttribute();
        $attributes['email_verified'] = $this->isEmailVerified();
        
        return $attributes;
    }
} 