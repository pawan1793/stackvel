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



// Example routes demonstrating Request parameter injection
$router->get('/request-example', 'HomeController@requestExample');
$router->post('/request-example', 'HomeController@requestExample');

// User routes with Request parameter examples
$router->get('/users/{id}/request-example', 'UserController@exampleWithRequest');
$router->post('/users/{id}/request-example', 'UserController@exampleWithRequest');
$router->get('/users/advanced-request', 'UserController@advancedRequestExample');
$router->post('/users/advanced-request', 'UserController@advancedRequestExample');
