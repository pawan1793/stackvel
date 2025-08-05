<?php

namespace Stackvel;

use Stackvel\Application;

/**
 * Stackvel Framework - Router Class
 * 
 * Handles URL routing with support for GET/POST methods,
 * controller mapping, and middleware.
 */
class Router
{
    /**
     * Registered routes
     */
    private array $routes = [
        'GET' => [],
        'POST' => []
    ];

    /**
     * Route groups
     */
    private array $groups = [];

    /**
     * Current group prefix
     */
    private string $currentPrefix = '';

    /**
     * Current group middleware
     */
    private array $currentMiddleware = [];

    /**
     * Register a GET route
     */
    public function get(string $uri, $action): self
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Register a POST route
     */
    public function post(string $uri, $action): self
    {
        return $this->addRoute('POST', $uri, $action);
    }

    /**
     * Register a route for any method
     */
    public function any(string $uri, $action): self
    {
        $this->get($uri, $action);
        $this->post($uri, $action);
        return $this;
    }

    /**
     * Add a route to the collection
     */
    private function addRoute(string $method, string $uri, $action): self
    {
        $uri = $this->currentPrefix . '/' . trim($uri, '/');
        $uri = '/' . trim($uri, '/');

        $this->routes[$method][$uri] = [
            'action' => $action,
            'middleware' => $this->currentMiddleware
        ];

        return $this;
    }

    /**
     * Create a route group
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->currentPrefix;
        $previousMiddleware = $this->currentMiddleware;

        if (isset($attributes['prefix'])) {
            $this->currentPrefix = $this->currentPrefix . '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $this->currentMiddleware = array_merge(
                $this->currentMiddleware,
                is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']]
            );
        }

        $callback($this);

        $this->currentPrefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;
    }

    /**
     * Get the base path for the application
     */
    private function getBasePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        
        // If we're accessing through index.php directly
        if (strpos($scriptName, '/index.php') !== false) {
            return dirname($scriptName);
        }
        
        // If we're in a subdirectory, detect it from the request URI
        // This handles cases like /stackvel/public/
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
        // In this case, we don't need a base path
        if ($scriptName === '/index.php') {
            return '';
        }
        
        return '';
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Get the base path and remove it from the URI
        $basePath = $this->getBasePath();
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Ensure URI starts with /
        if (empty($uri) || $uri[0] !== '/') {
            $uri = '/' . $uri;
        }

        // Remove trailing slash except for root
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // Check for exact match first
        if (isset($this->routes[$method][$uri])) {
            return $this->executeRoute($this->routes[$method][$uri]);
        }

        // Check for parameterized routes
        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->convertRouteToRegex($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove the full match
                return $this->executeRoute($handler, $matches);
            }
        }

        // Route not found
        $this->handleNotFound();
    }

    /**
     * Convert route parameters to regex pattern
     */
    private function convertRouteToRegex(string $route): string
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    /**
     * Execute a route handler
     */
    private function executeRoute(array $route, array $parameters = [])
    {
        $action = $route['action'];
        $middleware = $route['middleware'];

        // Run middleware
        foreach ($middleware as $middlewareClass) {
            $middlewareInstance = new $middlewareClass();
            $middlewareInstance->handle();
        }

        // Execute the action
        if (is_callable($action)) {
            return call_user_func_array($action, $parameters);
        }

        if (is_string($action)) {
            return $this->executeControllerAction($action, $parameters);
        }

        throw new \InvalidArgumentException('Invalid route action');
    }

    /**
     * Execute a controller action
     */
    private function executeControllerAction(string $action, array $parameters = [])
    {
        if (!str_contains($action, '@')) {
            throw new \InvalidArgumentException('Invalid controller action format. Use Controller@method');
        }

        [$controller, $method] = explode('@', $action);
        $controllerClass = "App\\Controllers\\{$controller}";

        if (!class_exists($controllerClass)) {
            throw new \InvalidArgumentException("Controller {$controllerClass} not found");
        }

        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $method)) {
            throw new \InvalidArgumentException("Method {$method} not found in controller {$controllerClass}");
        }

        return call_user_func_array([$controllerInstance, $method], $parameters);
    }

    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        
        if (file_exists(APP_ROOT . '/resources/views/errors/404.blade.php')) {
            $view = new View();
            echo $view->render('errors.404');
        } else {
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The requested page could not be found.</p>';
        }
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Clear all routes
     */
    public function clearRoutes(): void
    {
        $this->routes = ['GET' => [], 'POST' => []];
    }
} 