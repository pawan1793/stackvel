<?php

namespace App\Controllers;

use Stackvel\Application;
use Stackvel\View;
use Stackvel\Session;
use Stackvel\Mailer;

/**
 * Stackvel Framework - Base Controller Class
 * 
 * Provides common functionality for all controllers.
 */
abstract class Controller
{
    /**
     * Application instance
     */
    protected Application $app;

    /**
     * View instance
     */
    protected View $view;

    /**
     * Session instance
     */
    protected Session $session;

    /**
     * Mailer instance
     */
    protected Mailer $mailer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->view = $this->app->view;
        $this->session = $this->app->session;
        $this->mailer = $this->app->mailer;
    }

    /**
     * Render a view
     */
    protected function render(string $view, array $data = []): string
    {
        return $this->view->render($view, $data);
    }

    /**
     * Render a view and return response
     */
    protected function view(string $view, array $data = []): string
    {
        return $this->render($view, $data);
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): array
    {
        http_response_code($status);
        return $data;
    }

    /**
     * Return success response
     */
    protected function success(array $data = [], string $message = 'Success'): array
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Return error response
     */
    protected function error(string $message = 'Error', int $status = 400): array
    {
        return $this->json([
            'success' => false,
            'message' => $message
        ], $status);
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirect back to previous page
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Get request input
     */
    protected function input(string $key = null, $default = null)
    {
        $input = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $input;
        }
        
        return $input[$key] ?? $default;
    }

    /**
     * Get request method
     */
    protected function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request is GET
     */
    protected function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Check if request is POST
     */
    protected function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Check if request is PUT
     */
    protected function isPut(): bool
    {
        return $this->method() === 'PUT';
    }

    /**
     * Check if request is DELETE
     */
    protected function isDelete(): bool
    {
        return $this->method() === 'DELETE';
    }

    /**
     * Validate request data
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (!$this->validateField($value, $rule)) {
                $errors[$field] = "The {$field} field is invalid.";
            }
        }
        
        if (!empty($errors)) {
            $this->session->setErrors($errors);
            $this->session->setOldInput($data);
        }
        
        return $errors;
    }

    /**
     * Validate a single field
     */
    private function validateField($value, string $rule): bool
    {
        $rules = explode('|', $rule);
        
        foreach ($rules as $singleRule) {
            if (!$this->applyValidationRule($value, $singleRule)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Apply a validation rule
     */
    private function applyValidationRule($value, string $rule): bool
    {
        switch ($rule) {
            case 'required':
                return !empty($value);
            
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            
            case 'numeric':
                return is_numeric($value);
            
            case 'string':
                return is_string($value);
            
            case 'min:':
                $min = (int) substr($rule, 4);
                return strlen($value) >= $min;
            
            case 'max:':
                $max = (int) substr($rule, 4);
                return strlen($value) <= $max;
            
            default:
                return true;
        }
    }

    /**
     * Get validation errors
     */
    protected function getErrors(): array
    {
        return $this->session->getErrors();
    }

    /**
     * Check if there are validation errors
     */
    protected function hasErrors(): bool
    {
        return $this->session->hasErrors();
    }

    /**
     * Get old input
     */
    protected function old(string $key, $default = null)
    {
        return $this->session->getOldInput($key, $default);
    }

    /**
     * Set flash message
     */
    protected function flash(string $key, string $message): void
    {
        $this->session->flash($key, $message);
    }

    /**
     * Get flash message
     */
    protected function getFlash(string $key, $default = null)
    {
        return $this->session->getFlash($key, $default);
    }

    /**
     * Check if flash message exists
     */
    protected function hasFlash(string $key): bool
    {
        return $this->session->hasFlash($key);
    }

    /**
     * Send email
     */
    protected function sendEmail(string $to, string $subject, string $body, array $options = []): bool
    {
        return $this->mailer->send($to, $subject, $body, $options);
    }

    /**
     * Send email using view template
     */
    protected function sendEmailView(string $to, string $subject, string $view, array $data = [], array $options = []): bool
    {
        return $this->mailer->sendView($to, $subject, $view, $data, $options);
    }

    /**
     * Get uploaded file
     */
    protected function file(string $key)
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Upload file
     */
    protected function upload(string $key, string $destination): bool
    {
        $file = $this->file($key);
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $uploadDir = APP_ROOT . '/public/uploads/' . trim($destination, '/');
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = basename($file['name']);
        $filepath = $uploadDir . '/' . $filename;
        
        return move_uploaded_file($file['tmp_name'], $filepath);
    }

    /**
     * Get application instance
     */
    protected function getApp(): Application
    {
        return $this->app;
    }
} 