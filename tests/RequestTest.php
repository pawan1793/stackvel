<?php

use PHPUnit\Framework\TestCase;
use Stackvel\Request;
use App\Controllers\UserController;

/**
 * Test Request parameter injection feature
 */
class RequestTest extends TestCase
{
    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        // Define APP_ROOT for testing
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', __DIR__ . '/..');
        }
    }
    /**
     * Test Request object creation and basic functionality
     */
    public function testRequestObjectCreation()
    {
        // Mock $_GET and $_POST
        $_GET = ['name' => 'John', 'age' => '25'];
        $_POST = ['email' => 'john@example.com'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/users/123?page=1';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Test Browser';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $request = new Request();

        // Test input methods
        $this->assertEquals('John', $request->input('name'));
        $this->assertEquals('john@example.com', $request->input('email'));
        $this->assertEquals('25', $request->input('age'));
        $this->assertNull($request->input('nonexistent'));

        // Test method
        $this->assertEquals('POST', $request->method());
        $this->assertTrue($request->isPost());
        $this->assertFalse($request->isGet());

        // Test AJAX detection
        $this->assertTrue($request->isAjax());

        // Test URI and path
        $this->assertEquals('/users/123?page=1', $request->uri());
        $this->assertEquals('/users/123', $request->path());

        // Test query parameters - need to set $_GET for query parameters
        $_GET['page'] = '1';
        $this->assertEquals('1', $request->query('page'));
        $this->assertEquals('John', $request->query('name'));

        // Test headers
        $this->assertEquals('Test Browser', $request->userAgent());
        $this->assertEquals('127.0.0.1', $request->ip());

        // Test URL construction
        $this->assertEquals('http://localhost', $request->baseUrl());
        $this->assertEquals('http://localhost/users/123?page=1', $request->url());
    }

    /**
     * Test Request parameter injection in controller methods
     */
    public function testRequestParameterInjection()
    {
        // Define APP_ROOT for testing
        if (!defined('APP_ROOT')) {
            define('APP_ROOT', __DIR__ . '/..');
        }

        // Mock request data
        $_GET = ['name' => 'Jane'];
        $_POST = ['email' => 'jane@example.com'];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        // Initialize Application singleton for testing
        new \Stackvel\Application();

        // Create a Request object with the mocked data
        $request = new Request();

        // Test that the Request object has the expected data
        $this->assertEquals('Jane', $request->input('name'));
        $this->assertEquals('jane@example.com', $request->input('email'));
        $this->assertTrue($request->isAjax());
        $this->assertTrue($request->expectsJson());

        // Test that the Request object can be used in controller methods
        $controller = new UserController();
        
        // Use reflection to call the protected method for testing
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('exampleWithRequest');
        $method->setAccessible(true);
        
        $result = $method->invoke($controller, $request, '123');

        // Verify the result contains expected data
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Request processed successfully', $result['message']);
        
        $data = $result['data'];
        $this->assertEquals('Jane', $data['name']);
        $this->assertEquals('jane@example.com', $data['email']);
        $this->assertEquals('123', $data['user_id']);
    }

    /**
     * Test Request object with route parameters
     */
    public function testRequestWithRouteParameters()
    {
        $request = new Request();
        
        // Set route parameters
        $parameters = ['id' => '456', 'action' => 'edit'];
        $request->setParameters($parameters);

        // Test parameter access
        $this->assertEquals('456', $request->parameter('id'));
        $this->assertEquals('edit', $request->parameter('action'));
        $this->assertNull($request->parameter('nonexistent'));
        $this->assertEquals('default', $request->parameter('nonexistent', 'default'));

        // Test all parameters
        $allParams = $request->parameters();
        $this->assertEquals($parameters, $allParams);
    }

    /**
     * Test Request object input filtering methods
     */
    public function testRequestInputFiltering()
    {
        $_GET = [];
        $_POST = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'secret123',
            'age' => '30'
        ];

        $request = new Request();

        // Test only() method
        $userData = $request->only(['name', 'email', 'password']);
        $expected = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'secret123'
        ];
        $this->assertEquals($expected, $userData);

        // Test except() method
        $safeData = $request->except(['password']);
        $expected = [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'age' => '30'
        ];
        $this->assertEquals($expected, $safeData);

        // Test has() methods
        $this->assertTrue($request->has('name'));
        $this->assertFalse($request->has('nonexistent'));
        $this->assertTrue($request->hasAll(['name', 'email']));
        $this->assertFalse($request->hasAll(['name', 'nonexistent']));
        $this->assertTrue($request->hasAny(['name', 'nonexistent']));
        $this->assertFalse($request->hasAny(['nonexistent1', 'nonexistent2']));
    }

    /**
     * Test Request object file handling
     */
    public function testRequestFileHandling()
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [
            'avatar' => [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/test.jpg',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ]
        ];

        $request = new Request();

        // Test file access
        $file = $request->file('avatar');
        $this->assertNotNull($file);
        $this->assertEquals('test.jpg', $file['name']);

        // Test file existence
        $this->assertTrue($request->hasFile('avatar'));
        $this->assertFalse($request->hasFile('nonexistent'));

        // Test all files
        $files = $request->files();
        $this->assertEquals($_FILES, $files);
    }

    /**
     * Test Request object security and URL methods
     */
    public function testRequestSecurityAndUrlMethods()
    {
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/secure/page';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['HTTPS'] = 'on';

        $request = new Request();

        // Test secure connection
        $this->assertTrue($request->isSecure());

        // Test URL construction
        $this->assertEquals('https://example.com', $request->baseUrl());
        $this->assertEquals('https://example.com/secure/page', $request->url());
        $this->assertEquals('https', $request->scheme());
        $this->assertEquals('example.com', $request->host());
        $this->assertEquals(443, $request->port());
    }

    /**
     * Test Request object JSON expectation
     */
    public function testRequestJsonExpectation()
    {
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $request = new Request();

        // Test JSON expectation
        $this->assertTrue($request->expectsJson());

        // Test without JSON accept header
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        $request = new Request();
        $this->assertFalse($request->expectsJson());

        // Test with AJAX header
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = new Request();
        $this->assertTrue($request->expectsJson());
    }

    /**
     * Clean up after tests
     */
    protected function tearDown(): void
    {
        // Clean up global variables
        unset($_GET, $_POST, $_SERVER, $_FILES);
    }
} 