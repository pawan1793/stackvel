# Request Parameter Injection Feature

This document explains how to use the new Request parameter injection feature in the Stackvel framework.

## Overview

The Stackvel framework now supports dependency injection of `Request` objects into controller methods. This allows you to access request data, headers, and other HTTP information in a clean, object-oriented way.

## How It Works

When you define a controller method with a `Request` parameter, the framework automatically injects a `Request` object instance into that parameter. The framework uses PHP reflection to detect the parameter type and inject the appropriate object.

## Basic Usage

### 1. Simple Request Parameter

```php
<?php

namespace App\Controllers;

use Stackvel\Request;

class ExampleController extends Controller
{
    public function example(Request $request): array
    {
        // Get input data
        $name = $request->input('name');
        $email = $request->input('email');
        
        // Get request method
        $method = $request->method();
        
        return $this->success([
            'name' => $name,
            'email' => $email,
            'method' => $method
        ]);
    }
}
```

### 2. Request Parameter with Route Parameters

```php
public function show(Request $request, string $id): array
{
    // $request contains the Request object
    // $id contains the route parameter
    
    $user = User::find($id);
    $isAjax = $request->isAjax();
    
    return $this->success([
        'user' => $user,
        'is_ajax' => $isAjax
    ]);
}
```

## Request Object Methods

The `Request` object provides many useful methods for accessing request data:

### Input Data
- `input(string $key = null, $default = null)` - Get input value or all input
- `all()` - Get all input data
- `only(array $keys)` - Get only specific keys
- `except(array $keys)` - Get all except specified keys
- `has(string $key)` - Check if key exists
- `hasAny(array $keys)` - Check if any key exists
- `hasAll(array $keys)` - Check if all keys exist

### Request Information
- `method()` - Get HTTP method (GET, POST, etc.)
- `isGet()`, `isPost()`, `isPut()`, `isDelete()` - Check HTTP method
- `isAjax()` - Check if request is AJAX
- `expectsJson()` - Check if request expects JSON response
- `uri()` - Get request URI
- `path()` - Get request path
- `url()` - Get full URL
- `baseUrl()` - Get base URL

### Headers and Meta Information
- `headers()` - Get all headers
- `header(string $key, $default = null)` - Get specific header
- `userAgent()` - Get user agent
- `ip()` - Get client IP
- `referer()` - Get referer
- `contentType()` - Get content type
- `isSecure()` - Check if HTTPS
- `host()` - Get host
- `port()` - Get port
- `scheme()` - Get scheme (http/https)

### Query Parameters
- `query(string $key = null, $default = null)` - Get query parameter
- `queryString()` - Get full query string

### File Uploads
- `file(string $key)` - Get uploaded file
- `files()` - Get all uploaded files
- `hasFile(string $key)` - Check if file was uploaded

### Route Parameters
- `parameters()` - Get all route parameters
- `parameter(string $key, $default = null)` - Get specific route parameter

## Examples

### Example 1: Form Processing

```php
public function store(Request $request): array
{
    // Validate required fields
    if (!$request->hasAll(['name', 'email', 'password'])) {
        return $this->error('Missing required fields', 400);
    }
    
    // Get only safe data (exclude password for logging)
    $safeData = $request->except(['password']);
    
    // Get user data
    $userData = $request->only(['name', 'email', 'password']);
    
    // Create user
    $user = User::create($userData);
    
    return $this->success(['user' => $user]);
}
```

### Example 2: API Endpoint with AJAX Detection

```php
public function apiUpdate(Request $request, string $id): array
{
    // Check if it's an AJAX request
    if (!$request->isAjax()) {
        return $this->error('AJAX request required', 400);
    }
    
    // Get input data
    $data = $request->all();
    
    // Validate
    $errors = $this->validate($data, [
        'name' => 'required|string|min:2',
        'email' => 'required|email'
    ]);
    
    if (!empty($errors)) {
        return $this->error('Validation failed', 422);
    }
    
    // Update user
    $user = User::find($id);
    $user->update($data);
    
    return $this->success(['user' => $user]);
}
```

### Example 3: File Upload

```php
public function upload(Request $request): array
{
    // Check if file was uploaded
    if (!$request->hasFile('avatar')) {
        return $this->error('No file uploaded', 400);
    }
    
    $file = $request->file('avatar');
    
    // Process file upload
    $filename = $this->uploadFile($file);
    
    return $this->success(['filename' => $filename]);
}
```

### Example 4: Advanced Request Information

```php
public function debug(Request $request): array
{
    return $this->success([
        'request_info' => [
            'method' => $request->method(),
            'path' => $request->path(),
            'url' => $request->url(),
            'is_secure' => $request->isSecure(),
            'is_ajax' => $request->isAjax(),
            'expects_json' => $request->expectsJson(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'referer' => $request->referer()
        ],
        'input_data' => $request->all(),
        'query_params' => $request->query(),
        'headers' => $request->headers()
    ]);
}
```

## Route Definition

Routes are defined normally - the framework automatically handles the Request injection:

```php
// In routes/web.php
$router->post('/users', 'UserController@store');
$router->get('/users/{id}', 'UserController@show');
$router->post('/api/users/{id}', 'UserController@apiUpdate');
```

## Benefits

1. **Clean Code**: No need to access `$_GET`, `$_POST`, `$_SERVER` directly
2. **Type Safety**: PHP type hints ensure you get a Request object
3. **Testability**: Easy to mock Request objects in tests
4. **Consistency**: All request data access goes through the same interface
5. **Extensibility**: Easy to add new request-related functionality

## Backward Compatibility

The existing controller methods that don't use Request parameters continue to work as before. The framework maintains backward compatibility with the existing `input()`, `method()`, `isGet()`, etc. methods in the base Controller class.

## Migration

To migrate existing methods to use Request parameters:

**Before:**
```php
public function store(): void
{
    $data = $this->input();
    $method = $this->method();
    // ...
}
```

**After:**
```php
public function store(Request $request): void
{
    $data = $request->all();
    $method = $request->method();
    // ...
}
```

Both approaches work, so you can migrate gradually. 