<?php

namespace Stackvel;

/**
 * Stackvel Framework - Session Class
 * 
 * Provides secure session management with CSRF protection
 * and flash messages.
 */
class Session
{
    /**
     * Session configuration
     */
    private array $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->config = [
            'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 120,
            'path' => '/',
            'domain' => '',
            'secure' => $_ENV['APP_ENV'] === 'production',
            'httponly' => true,
            'samesite' => 'Lax'
        ];
    }

    /**
     * Start the session
     */
    public function start(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true;
        }

        // Set session configuration
        session_set_cookie_params(
            $this->config['lifetime'] * 60,
            $this->config['path'],
            $this->config['domain'],
            $this->config['secure'],
            $this->config['httponly']
        );

        // Set session name
        session_name('stackvel_session');

        // Start session
        if (session_start()) {
            $this->regenerateId();
            return true;
        }

        return false;
    }

    /**
     * Regenerate session ID for security
     */
    public function regenerateId(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return session_regenerate_id(true);
        }
        return false;
    }

    /**
     * Set a session value
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session key exists
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public function all(): array
    {
        return $_SESSION;
    }

    /**
     * Clear all session data
     */
    public function clear(): void
    {
        session_unset();
    }

    /**
     * Destroy the session
     */
    public function destroy(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            return true;
        }
        return false;
    }

    /**
     * Set a flash message
     */
    public function flash(string $key, $value): void
    {
        $_SESSION['flash'][$key] = $value;
    }

    /**
     * Get a flash message
     */
    public function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    /**
     * Check if a flash message exists
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Get all flash messages
     */
    public function getFlashMessages(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    /**
     * Set old input data
     */
    public function setOldInput(array $data): void
    {
        $_SESSION['old'] = $data;
    }

    /**
     * Get old input data
     */
    public function getOldInput(string $key = null, $default = null)
    {
        if ($key === null) {
            $data = $_SESSION['old'] ?? [];
            unset($_SESSION['old']);
            return $data;
        }

        $value = $_SESSION['old'][$key] ?? $default;
        unset($_SESSION['old'][$key]);
        return $value;
    }

    /**
     * Set validation errors
     */
    public function setErrors(array $errors): void
    {
        $_SESSION['errors'] = $errors;
    }

    /**
     * Get validation errors
     */
    public function getErrors(string $key = null): array
    {
        if ($key === null) {
            $errors = $_SESSION['errors'] ?? [];
            unset($_SESSION['errors']);
            return $errors;
        }

        return $_SESSION['errors'][$key] ?? [];
    }

    /**
     * Check if there are validation errors
     */
    public function hasErrors(): bool
    {
        return !empty($_SESSION['errors']);
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Get session status
     */
    public function getStatus(): int
    {
        return session_status();
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Set session configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get session configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
} 