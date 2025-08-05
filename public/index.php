<?php

/**
 * Stackvel Framework - Secure Entry Point
 * 
 * This is the single point of entry for all web requests.
 * It handles routing, security, and framework initialization.
 */

// Prevent direct access to this file from outside the web server
if (!isset($_SERVER['REQUEST_URI'])) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

// Define the application root path
define('APP_ROOT', dirname(__DIR__));

// Load Composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Set error reporting based on environment
if ($_ENV['APP_ENV'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Initialize the application
try {
    $app = new Stackvel\Application();
    $app->run();
} catch (Exception $e) {
    // Log the error
    error_log($e->getMessage());
    
    // Show appropriate error response
    if ($_ENV['APP_ENV'] === 'production') {
        http_response_code(500);
        echo 'Internal Server Error';
    } else {
        http_response_code(500);
        echo '<h1>Stackvel Framework Error</h1>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
        if ($_ENV['APP_DEBUG'] ?? false) {
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
    }
} 