<?php

namespace Stackvel;

use Stackvel\Router;
use Stackvel\Database;
use Stackvel\View;
use Stackvel\Mailer;
use Stackvel\Session;
use Stackvel\Config;

/**
 * Stackvel Framework - Main Application Class
 * 
 * This is the core application class that bootstraps the framework
 * and orchestrates all components.
 */
class Application
{
    /**
     * The framework version
     */
    const VERSION = '1.0.0';

    /**
     * The application instance
     */
    private static $instance = null;

    /**
     * Core framework components
     */
    public Router $router;
    public Database $database;
    public View $view;
    public Mailer $mailer;
    public Session $session;
    public Config $config;

    /**
     * Application constructor
     */
    public function __construct()
    {
        self::$instance = $this;
        
        // Load helper functions
        $this->loadHelpers();
        
        // Initialize core components
        $this->config = new Config();
        $this->session = new Session();
        $this->database = new Database();
        $this->view = new View();
        $this->mailer = new Mailer();
        $this->router = new Router();
        
        // Load routes
        $this->loadRoutes();
    }

    /**
     * Get the application instance
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        try {
            // Start session
            $this->session->start();
            
            // Handle the request
            $response = $this->router->dispatch();
            
            // Send response
            $this->sendResponse($response);
            
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Load helper functions
     */
    private function loadHelpers(): void
    {
        $helpersFile = APP_ROOT . '/core/helpers.php';
        
        if (file_exists($helpersFile)) {
            require $helpersFile;
        }
    }

    /**
     * Load application routes
     */
    private function loadRoutes(): void
    {
        $routesFile = APP_ROOT . '/routes/web.php';
        
        if (file_exists($routesFile)) {
            $router = $this->router;
            require $routesFile;
        }
    }

    /**
     * Send HTTP response
     */
    private function sendResponse($response): void
    {
        if (is_string($response)) {
            echo $response;
        } elseif (is_array($response)) {
            header('Content-Type: application/json');
            echo json_encode($response);
        } elseif (is_object($response) && method_exists($response, 'send')) {
            $response->send();
        }
    }

    /**
     * Handle application exceptions
     */
    private function handleException(\Exception $e): void
    {
        if ($_ENV['APP_ENV'] === 'production') {
            http_response_code(500);
            echo 'Internal Server Error';
        } else {
            throw $e;
        }
    }

    /**
     * Get a service from the application
     */
    public function get(string $service)
    {
        return match ($service) {
            'router' => $this->router,
            'database' => $this->database,
            'view' => $this->view,
            'mailer' => $this->mailer,
            'session' => $this->session,
            'config' => $this->config,
            default => throw new \InvalidArgumentException("Service '{$service}' not found")
        };
    }

    /**
     * Check if the application is running in console mode
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Generate a URL for the application
     */
    public function url(string $path = ''): string
    {
        $baseUrl = $this->config->getAppUrl();
        $basePath = $this->getBasePath();
        
        // Remove trailing slash from base URL
        $baseUrl = rtrim($baseUrl, '/');
        
        // Ensure path starts with /
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        
        // For subdirectory installations, include the base path
        if (!empty($basePath)) {
            return $baseUrl . $basePath . $path;
        }
        
        return $baseUrl . $path;
    }
    
    /**
     * Get the base path for the application
     */
    public function getBasePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        // If we're accessing through index.php directly
        if (strpos($scriptName, '/index.php') !== false) {
            return dirname($scriptName);
        }
        
        // If we're in a subdirectory, detect it from the request URI
        $pathInfo = pathinfo($requestUri);
        if (isset($pathInfo['dirname']) && $pathInfo['dirname'] !== '/') {
            return $pathInfo['dirname'];
        }
        
        // Try to detect from script name if it contains the project path
        if (strpos($scriptName, '/public/') !== false) {
            $publicPos = strpos($scriptName, '/public/');
            return substr($scriptName, 0, $publicPos + 7); // Include /public/
        }
        
        // For development server (php -S), the script name might be just /index.php
        if ($scriptName === '/index.php') {
            return '';
        }
        
        return '';
    }

    /**
     * Get the application version
     */
    public function version(): string
    {
        return self::VERSION;
    }
} 