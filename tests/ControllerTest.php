<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    protected $app;
    protected $testUserId;

    public function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }
        
        $this->app = new \Stackvel\Application();
    }

    public function tearDown(): void
    {
        // Clean up test data
        if ($this->testUserId) {
            $user = \App\Models\User::find($this->testUserId);
            if ($user) {
                $user->delete();
            }
        }
    }

    public function testHomeControllerIndex()
    {
        $controller = new \App\Controllers\HomeController();
        $result = $controller->index();
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Stackvel', $result);
        $this->assertStringContainsString('Framework', $result);
    }

    public function testHomeControllerIndexWithData()
    {
        $controller = new \App\Controllers\HomeController();
        $result = $controller->index();
        
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('Features', $result);
    }

    public function testUserControllerIndex()
    {
        $controller = new \App\Controllers\UserController();
        $result = $controller->index();
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Users', $result);
    }

    public function testUserControllerShow()
    {
        // First create a test user
        $userData = [
            'name' => 'Show Test User',
            'email' => 'showtest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = \App\Models\User::create($userData);
        $this->testUserId = $user->id;

        $controller = new \App\Controllers\UserController();
        $result = $controller->show($user->id);
        
        $this->assertIsString($result);
        $this->assertStringContainsString($userData['name'], $result);
        $this->assertStringContainsString($userData['email'], $result);
    }

    public function testUserControllerCreate()
    {
        $controller = new \App\Controllers\UserController();
        $result = $controller->create();
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Create', $result);
        $this->assertStringContainsString('User', $result);
    }

    public function testUserControllerStore()
    {
        $controller = new \App\Controllers\UserController();
        
        $userData = [
            'name' => 'Store Test User',
            'email' => 'storetest' . uniqid() . '@example.com',
            'password' => 'secret123'
        ];

        $result = $controller->store($userData);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('created', $result);
        
        // Verify user was actually created
        $user = \App\Models\User::whereFirst('email', $userData['email']);
        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user->name);
        
        $this->testUserId = $user->id;
    }

    public function testUserControllerEdit()
    {
        // First create a test user
        $userData = [
            'name' => 'Edit Test User',
            'email' => 'edittest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = \App\Models\User::create($userData);
        $this->testUserId = $user->id;

        $controller = new \App\Controllers\UserController();
        $result = $controller->edit($user->id);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Edit', $result);
        $this->assertStringContainsString($userData['name'], $result);
    }

    public function testUserControllerUpdate()
    {
        // First create a test user
        $userData = [
            'name' => 'Update Test User',
            'email' => 'updatetest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = \App\Models\User::create($userData);
        $this->testUserId = $user->id;

        $controller = new \App\Controllers\UserController();
        
        $updateData = [
            'name' => 'Updated Test User',
            'email' => 'updatedtest' . uniqid() . '@example.com'
        ];

        $result = $controller->update($user->id, $updateData);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('updated', $result);
        
        // Verify user was actually updated
        $updatedUser = \App\Models\User::find($user->id);
        $this->assertEquals($updateData['name'], $updatedUser->name);
    }

    public function testUserControllerDestroy()
    {
        // First create a test user
        $userData = [
            'name' => 'Destroy Test User',
            'email' => 'destroytest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = \App\Models\User::create($userData);
        $userId = $user->id;

        $controller = new \App\Controllers\UserController();
        $result = $controller->destroy($user->id);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('deleted', $result);
        
        // Verify user was actually deleted
        $deletedUser = \App\Models\User::find($userId);
        $this->assertNull($deletedUser);
    }

    public function testControllerWithInvalidId()
    {
        $controller = new \App\Controllers\UserController();
        
        // Test with non-existent ID
        $result = $controller->show(99999);
        $this->assertIsString($result);
        $this->assertStringContainsString('not found', $result);
    }

    public function testControllerWithInvalidData()
    {
        $controller = new \App\Controllers\UserController();
        
        // Test with invalid data
        $invalidData = [
            'name' => '', // Empty name
            'email' => 'invalid-email', // Invalid email
            'password' => '' // Empty password
        ];

        $result = $controller->store($invalidData);
        $this->assertIsString($result);
        $this->assertStringContainsString('error', $result);
    }

    public function testControllerResponseTypes()
    {
        $controller = new \App\Controllers\HomeController();
        
        // Test that controller methods return strings
        $result = $controller->index();
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testControllerValidation()
    {
        $controller = new \App\Controllers\UserController();
        
        // Test validation with missing required fields
        $invalidData = [
            'name' => 'Test User'
            // Missing email and password
        ];

        $result = $controller->store($invalidData);
        $this->assertIsString($result);
        $this->assertStringContainsString('error', $result);
    }

    public function testControllerPagination()
    {
        $controller = new \App\Controllers\UserController();
        $result = $controller->index();
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Users', $result);
    }

    public function testControllerSearch()
    {
        $controller = new \App\Controllers\UserController();
        $result = $controller->search('test');
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Search', $result);
    }

    public function testControllerPerformance()
    {
        $controller = new \App\Controllers\HomeController();
        
        $startTime = microtime(true);
        
        for ($i = 0; $i < 10; $i++) {
            $result = $controller->index();
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $this->assertLessThan(1.0, $executionTime, 'Controller should be fast');
    }
} 