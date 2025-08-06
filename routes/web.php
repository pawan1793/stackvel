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

