<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected $router;

    public function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }
        
        $this->router = new \Stackvel\Router();
    }

    public function testRouterGetRoute()
    {
        $this->router->get('/test', function() {
            return 'Test Route';
        });

        $route = $this->router->match('GET', '/test');
        $this->assertNotNull($route);
        $this->assertEquals('GET', $route['method']);
        $this->assertEquals('/test', $route['path']);
    }

    public function testRouterPostRoute()
    {
        $this->router->post('/users', function() {
            return 'Create User';
        });

        $route = $this->router->match('POST', '/users');
        $this->assertNotNull($route);
        $this->assertEquals('POST', $route['method']);
        $this->assertEquals('/users', $route['path']);
    }

    public function testRouterPutRoute()
    {
        $this->router->put('/users/{id}', function($id) {
            return "Update User {$id}";
        });

        $route = $this->router->match('PUT', '/users/123');
        $this->assertNotNull($route);
        $this->assertEquals('PUT', $route['method']);
        $this->assertEquals('/users/{id}', $route['path']);
        $this->assertEquals(['123'], $route['params']);
    }

    public function testRouterDeleteRoute()
    {
        $this->router->delete('/users/{id}', function($id) {
            return "Delete User {$id}";
        });

        $route = $this->router->match('DELETE', '/users/456');
        $this->assertNotNull($route);
        $this->assertEquals('DELETE', $route['method']);
        $this->assertEquals('/users/{id}', $route['path']);
        $this->assertEquals(['456'], $route['params']);
    }

    public function testRouterMultipleParams()
    {
        $this->router->get('/users/{id}/posts/{postId}', function($id, $postId) {
            return "User {$id} Post {$postId}";
        });

        $route = $this->router->match('GET', '/users/123/posts/789');
        $this->assertNotNull($route);
        $this->assertEquals(['123', '789'], $route['params']);
    }

    public function testRouterNoMatch()
    {
        $this->router->get('/test', function() {
            return 'Test Route';
        });

        $route = $this->router->match('GET', '/nonexistent');
        $this->assertNull($route);
    }

    public function testRouterMethodNoMatch()
    {
        $this->router->get('/test', function() {
            return 'Test Route';
        });

        $route = $this->router->match('POST', '/test');
        $this->assertNull($route);
    }

    public function testRouterWithQueryString()
    {
        $this->router->get('/search', function() {
            return 'Search Results';
        });

        $route = $this->router->match('GET', '/search?q=test&page=1');
        $this->assertNotNull($route);
        $this->assertEquals('/search', $route['path']);
    }

    public function testRouterWithTrailingSlash()
    {
        $this->router->get('/users', function() {
            return 'Users List';
        });

        $route = $this->router->match('GET', '/users/');
        $this->assertNotNull($route);
        $this->assertEquals('/users', $route['path']);
    }

    public function testRouterComplexPath()
    {
        $this->router->get('/api/v1/users/{id}/profile', function($id) {
            return "User Profile {$id}";
        });

        $route = $this->router->match('GET', '/api/v1/users/123/profile');
        $this->assertNotNull($route);
        $this->assertEquals('/api/v1/users/{id}/profile', $route['path']);
        $this->assertEquals(['123'], $route['params']);
    }

    public function testRouterMultipleRoutes()
    {
        $this->router->get('/users', function() {
            return 'Users List';
        });

        $this->router->get('/users/{id}', function($id) {
            return "User {$id}";
        });

        $this->router->post('/users', function() {
            return 'Create User';
        });

        // Test first route
        $route1 = $this->router->match('GET', '/users');
        $this->assertNotNull($route1);
        $this->assertEquals('/users', $route1['path']);

        // Test second route
        $route2 = $this->router->match('GET', '/users/123');
        $this->assertNotNull($route2);
        $this->assertEquals('/users/{id}', $route2['path']);
        $this->assertEquals(['123'], $route2['params']);

        // Test third route
        $route3 = $this->router->match('POST', '/users');
        $this->assertNotNull($route3);
        $this->assertEquals('/users', $route3['path']);
    }

    public function testRouterExecuteCallback()
    {
        $testValue = 'Callback Executed';
        
        $this->router->get('/test', function() use ($testValue) {
            return $testValue;
        });

        $route = $this->router->match('GET', '/test');
        $this->assertNotNull($route);
        
        $callback = $route['callback'];
        $result = $callback();
        $this->assertEquals($testValue, $result);
    }

    public function testRouterExecuteCallbackWithParams()
    {
        $this->router->get('/users/{id}', function($id) {
            return "User ID: {$id}";
        });

        $route = $this->router->match('GET', '/users/456');
        $this->assertNotNull($route);
        
        $callback = $route['callback'];
        $params = $route['params'];
        $result = call_user_func_array($callback, $params);
        $this->assertEquals('User ID: 456', $result);
    }

    public function testRouterExecuteCallbackWithMultipleParams()
    {
        $this->router->get('/users/{id}/posts/{postId}', function($id, $postId) {
            return "User {$id}, Post {$postId}";
        });

        $route = $this->router->match('GET', '/users/123/posts/789');
        $this->assertNotNull($route);
        
        $callback = $route['callback'];
        $params = $route['params'];
        $result = call_user_func_array($callback, $params);
        $this->assertEquals('User 123, Post 789', $result);
    }

    public function testRouterWithControllerAction()
    {
        $this->router->get('/home', 'HomeController@index');
        
        $route = $this->router->match('GET', '/home');
        $this->assertNotNull($route);
        $this->assertEquals('HomeController@index', $route['callback']);
    }

    public function testRouterWithControllerActionAndParams()
    {
        $this->router->get('/users/{id}', 'UserController@show');
        
        $route = $this->router->match('GET', '/users/123');
        $this->assertNotNull($route);
        $this->assertEquals('UserController@show', $route['callback']);
        $this->assertEquals(['123'], $route['params']);
    }

    public function testRouterPerformance()
    {
        // Add multiple routes
        for ($i = 1; $i <= 100; $i++) {
            $this->router->get("/route{$i}", function() use ($i) {
                return "Route {$i}";
            });
        }

        $startTime = microtime(true);
        
        // Test route matching
        for ($i = 1; $i <= 10; $i++) {
            $route = $this->router->match('GET', "/route{$i}");
            $this->assertNotNull($route);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $this->assertLessThan(0.1, $executionTime, 'Router should be fast');
    }

    public function testRouterWithSpecialCharacters()
    {
        $this->router->get('/test/special-chars', function() {
            return 'Special Characters Route';
        });

        $route = $this->router->match('GET', '/test/special-chars');
        $this->assertNotNull($route);
        $this->assertEquals('/test/special-chars', $route['path']);
    }

    public function testRouterWithNumericParams()
    {
        $this->router->get('/posts/{id}', function($id) {
            return "Post {$id}";
        });

        $route = $this->router->match('GET', '/posts/12345');
        $this->assertNotNull($route);
        $this->assertEquals(['12345'], $route['params']);
    }

    public function testRouterWithAlphaNumericParams()
    {
        $this->router->get('/users/{username}', function($username) {
            return "User {$username}";
        });

        $route = $this->router->match('GET', '/users/john123');
        $this->assertNotNull($route);
        $this->assertEquals(['john123'], $route['params']);
    }
} 