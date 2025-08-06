<?php

namespace Stackvel;

/**
 * Stackvel Framework - Request Class
 * 
 * Encapsulates HTTP request data and provides a clean interface
 * for accessing request information, headers, and input data.
 */
class Request
{
    /**
     * Request data (GET + POST)
     */
    private array $input;

    /**
     * Request headers
     */
    private array $headers;

    /**
     * Request method
     */
    private string $method;

    /**
     * Request URI
     */
    private string $uri;

    /**
     * Request parameters (from route)
     */
    private array $parameters;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->input = array_merge($_GET ?? [], $_POST ?? []);
        $this->headers = $this->getRequestHeaders();
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->parameters = [];
    }

    /**
     * Get all input data
     */
    public function all(): array
    {
        return $this->input;
    }

    /**
     * Get a specific input value
     */
    public function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->input;
        }
        
        return $this->input[$key] ?? $default;
    }

    /**
     * Get only specific keys from input
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->input, array_flip($keys));
    }

    /**
     * Get all input except specified keys
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->input, array_flip($keys));
    }

    /**
     * Check if input has a specific key
     */
    public function has(string $key): bool
    {
        return isset($this->input[$key]);
    }

    /**
     * Check if input has any of the specified keys
     */
    public function hasAny(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->has($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if input has all of the specified keys
     */
    public function hasAll(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get request method
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Check if request is GET
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Check if request is POST
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Check if request is PUT
     */
    public function isPut(): bool
    {
        return $this->method === 'PUT';
    }

    /**
     * Check if request is DELETE
     */
    public function isDelete(): bool
    {
        return $this->method === 'DELETE';
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return isset($this->headers['x-requested-with']) && 
               $this->headers['x-requested-with'] === 'XMLHttpRequest';
    }

    /**
     * Check if request expects JSON response
     */
    public function expectsJson(): bool
    {
        return $this->isAjax() || 
               (isset($this->headers['accept']) && 
                strpos($this->headers['accept'], 'application/json') !== false);
    }

    /**
     * Get request URI
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * Get request path (without query string)
     */
    public function path(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        return $path ?: '/';
    }

    /**
     * Get query string
     */
    public function queryString(): string
    {
        return parse_url($this->uri, PHP_URL_QUERY) ?: '';
    }

    /**
     * Get a specific query parameter
     */
    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET ?? [];
        }
        
        return $_GET[$key] ?? $default;
    }

    /**
     * Get request headers
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get a specific header
     */
    public function header(string $key, $default = null)
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }

    /**
     * Get uploaded file
     */
    public function file(string $key)
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Get all uploaded files
     */
    public function files(): array
    {
        return $_FILES;
    }

    /**
     * Check if file was uploaded
     */
    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Set route parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * Get route parameters
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get a specific route parameter
     */
    public function parameter(string $key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * Get request headers from $_SERVER
     */
    private function getRequestHeaders(): array
    {
        $headers = [];
        
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$header] = $value;
            }
        }
        
        return $headers;
    }

    /**
     * Get the user agent
     */
    public function userAgent(): string
    {
        return $this->header('User-Agent', '');
    }

    /**
     * Get the IP address
     */
    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /**
     * Get the referer
     */
    public function referer(): string
    {
        return $this->header('Referer', '');
    }

    /**
     * Get the content type
     */
    public function contentType(): string
    {
        return $this->header('Content-Type', '');
    }

    /**
     * Check if request is secure (HTTPS)
     */
    public function isSecure(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }

    /**
     * Get the host
     */
    public function host(): string
    {
        return $_SERVER['HTTP_HOST'] ?? '';
    }

    /**
     * Get the port
     */
    public function port(): int
    {
        return (int) ($_SERVER['SERVER_PORT'] ?? 80);
    }

    /**
     * Get the scheme (http or https)
     */
    public function scheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Get the full URL
     */
    public function url(): string
    {
        return $this->scheme() . '://' . $this->host() . $this->uri;
    }

    /**
     * Get the base URL
     */
    public function baseUrl(): string
    {
        return $this->scheme() . '://' . $this->host();
    }
} 