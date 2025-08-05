<?php

/**
 * Stackvel Framework - Helper Functions
 * 
 * Common utility functions used throughout the framework.
 */

if (!function_exists('view')) {
    /**
     * Render a view
     */
    function view(string $view, array $data = []): string
    {
        $app = Stackvel\Application::getInstance();
        return $app->view->render($view, $data);
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, $default = null)
    {
        $app = Stackvel\Application::getInstance();
        return $app->config->get($key, $default);
    }
}

if (!function_exists('app')) {
    /**
     * Get application instance
     */
    function app(): Stackvel\Application
    {
        return Stackvel\Application::getInstance();
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset(string $path): string
    {
        $appUrl = config('app.url', 'http://localhost');
        return rtrim($appUrl, '/') . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url(string $path = ''): string
    {
        $appUrl = config('app.url', 'http://localhost');
        return rtrim($appUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL
     */
    function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back to previous page
     */
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old(string $key, $default = null)
    {
        $app = Stackvel\Application::getInstance();
        return $app->session->getOldInput($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate CSRF token
     */
    function csrf_token(): string
    {
        $app = Stackvel\Application::getInstance();
        return $app->session->generateCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF field HTML
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate method field HTML
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($method) . '">';
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities
     */
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate random string
     */
    function str_random(int $length = 16): string
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }
}

if (!function_exists('bcrypt')) {
    /**
     * Hash password using bcrypt
     */
    function bcrypt(string $value, array $options = []): string
    {
        return password_hash($value, PASSWORD_BCRYPT, $options);
    }
}

if (!function_exists('now')) {
    /**
     * Get current timestamp
     */
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date
     */
    function format_date(string $date, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, strtotime($date));
    }
}

if (!function_exists('is_production')) {
    /**
     * Check if application is in production
     */
    function is_production(): bool
    {
        return config('app.env') === 'production';
    }
}

if (!function_exists('is_development')) {
    /**
     * Check if application is in development
     */
    function is_development(): bool
    {
        return config('app.env') === 'development';
    }
}

if (!function_exists('is_testing')) {
    /**
     * Check if application is in testing
     */
    function is_testing(): bool
    {
        return config('app.env') === 'testing';
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        exit;
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable
     */
    function dump(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with error
     */
    function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit;
    }
}

if (!function_exists('abort_if')) {
    /**
     * Abort if condition is true
     */
    function abort_if(bool $condition, int $code, string $message = ''): void
    {
        if ($condition) {
            abort($code, $message);
        }
    }
}

if (!function_exists('abort_unless')) {
    /**
     * Abort unless condition is true
     */
    function abort_unless(bool $condition, int $code, string $message = ''): void
    {
        if (!$condition) {
            abort($code, $message);
        }
    }
} 