<?php

/**
 * Create Users Table Migration
 */
return new class {
    /**
     * Run the migration
     */
    public function up(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                email_verified_at TIMESTAMP NULL,
                remember_token VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $app = \Stackvel\Application::getInstance();
        $app->database->query($sql);
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS users;";
        
        $app = \Stackvel\Application::getInstance();
        $app->database->query($sql);
    }
};