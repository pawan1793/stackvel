<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
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

    public function testModelAll()
    {
        $users = \App\Models\User::all();
        $this->assertIsArray($users);
    }

    public function testModelCreate()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = \App\Models\User::create($userData);
        $this->assertNotNull($user);
        $this->assertNotNull($user->id);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);

        $this->testUserId = $user->id;
    }

    public function testModelFind()
    {
        // First create a user
        $userData = [
            'name' => 'Find Test User',
            'email' => 'findtest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $createdUser = \App\Models\User::create($userData);
        $this->testUserId = $createdUser->id;

        // Test find by ID
        $foundUser = \App\Models\User::find($createdUser->id);
        $this->assertNotNull($foundUser);
        $this->assertEquals($userData['name'], $foundUser->name);
        $this->assertEquals($userData['email'], $foundUser->email);
    }

    public function testModelWhere()
    {
        // First create a user
        $userData = [
            'name' => 'Where Test User',
            'email' => 'wheretest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $createdUser = \App\Models\User::create($userData);
        $this->testUserId = $createdUser->id;

        // Test where clause
        $users = \App\Models\User::where('email', $userData['email']);
        $this->assertIsArray($users);
        $this->assertCount(1, $users);
        $this->assertEquals($userData['name'], $users[0]->name);
    }

    public function testModelUpdate()
    {
        // First create a user
        $userData = [
            'name' => 'Update Test User',
            'email' => 'updatetest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = \App\Models\User::create($userData);
        $this->testUserId = $user->id;

        // Test update
        $newName = 'Updated Test User';
        $user->name = $newName;
        $result = $user->save();
        $this->assertTrue($result);

        // Verify update
        $updatedUser = \App\Models\User::find($user->id);
        $this->assertEquals($newName, $updatedUser->name);
    }

    public function testModelDelete()
    {
        // First create a user
        $userData = [
            'name' => 'Delete Test User',
            'email' => 'deletetest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $user = \App\Models\User::create($userData);
        $userId = $user->id;

        // Test delete
        $result = $user->delete();
        $this->assertTrue($result);

        // Verify deletion
        $deletedUser = \App\Models\User::find($userId);
        $this->assertNull($deletedUser);
    }

    public function testModelWhereFirst()
    {
        // First create a user
        $userData = [
            'name' => 'WhereFirst Test User',
            'email' => 'wherefirsttest' . uniqid() . '@example.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $createdUser = \App\Models\User::create($userData);
        $this->testUserId = $createdUser->id;

        // Test whereFirst
        $user = \App\Models\User::whereFirst('email', $userData['email']);
        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user->name);
    }

    public function testModelTableName()
    {
        $user = new \App\Models\User();
        $tableName = $user->getTable();
        $this->assertEquals('users', $tableName);
    }

    public function testModelAttributes()
    {
        $user = new \App\Models\User();
        $attributes = $user->getAttributes();
        $this->assertIsArray($attributes);
    }
} 