<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

use PHPUnit\Framework\TestCase;

class MvcTest extends TestCase
{
    public function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        // Set up environment for testing
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }
        // Always instantiate the application
        new \Stackvel\Application();
    }

    public function testModelAll()
    {
        $users = \App\Models\User::all();
        $this->assertIsArray($users);
    }

    public function testModelCreateAndDelete()
    {
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'testuser' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->assertNotNull($user);
        $this->assertNotNull($user->id);
        $this->assertTrue($user->delete());
    }

    public function testViewRender()
    {
        $view = new \Stackvel\View();
        $output = $view->render('home.index', [
            'title' => 'Test',
            'description' => 'Test Desc',
            'features' => ['A', 'B', 'C']
        ]);
        $this->assertStringContainsString('Test', $output);
    }

    public function testControllerMethod()
    {
        $controller = new \App\Controllers\HomeController();
        $result = $controller->index();
        $this->assertIsString($result);
        $this->assertStringContainsString('Stackvel', $result);
    }
}