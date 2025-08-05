#!/usr/bin/env php
<?php

/**
 * Stackvel Framework - Console Entry Point
 * 
 * This file handles CLI commands and scheduled tasks.
 */

// Define the application root path
define('APP_ROOT', __DIR__);

// Load Composer autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Initialize the application
$app = new Stackvel\Application();

// Initialize console kernel
$kernel = new \Console\Kernel();

// Handle the command
$exitCode = $kernel->handle($argv);

exit($exitCode); 