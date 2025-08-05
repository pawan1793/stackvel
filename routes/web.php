<?php

/**
 * Stackvel Framework - Web Routes
 * 
 * Define your web routes here. Routes are loaded by the Application class.
 */


// Home routes
$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');
$router->get('/contact', 'HomeController@contact');
$router->post('/contact', 'HomeController@sendContact');

// Example of closure-based routes
$router->get('/test', function () {
    return 'Hello from Stackvel Framework!';
});

$router->get('/json', function () {
    return ['message' => 'JSON response from Stackvel Framework'];
});

// Test route to verify routing is working
$router->get('/routing-test', function () {
    $app = Stackvel\Application::getInstance();
    $basePath = $app->getBasePath();
    $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    return [
        'message' => 'Routing test successful!',
        'base_path' => $basePath,
        'current_url' => $currentUrl,
        'script_name' => $scriptName,
        'detected_uri' => parse_url($currentUrl, PHP_URL_PATH),
        'app_url' => $app->url('/test')
    ];
});
