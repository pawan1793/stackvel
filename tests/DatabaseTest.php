<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    protected $database;
    protected $testTable = 'test_table';

    public function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }
        
        $this->database = new \Stackvel\Database();
        
        // Create test table
        $this->createTestTable();
    }

    public function tearDown(): void
    {
        // Clean up test table
        $this->dropTestTable();
    }

    protected function createTestTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS {$this->testTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $this->database->query($sql);
    }

    protected function dropTestTable()
    {
        $sql = "DROP TABLE IF EXISTS {$this->testTable}";
        $this->database->query($sql);
    }

    public function testDatabaseConnection()
    {
        $this->assertNotNull($this->database);
        $this->assertInstanceOf(\Stackvel\Database::class, $this->database);
    }

    public function testDatabaseQuery()
    {
        $sql = "SELECT 1 as test";
        $result = $this->database->query($sql);
        $this->assertTrue($result);
    }

    public function testDatabaseSelect()
    {
        // Insert test data
        $this->database->query("INSERT INTO {$this->testTable} (name, email) VALUES ('Test User', 'test@example.com')");
        
        $sql = "SELECT * FROM {$this->testTable} WHERE name = ?";
        $results = $this->database->select($sql, ['Test User']);
        
        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertEquals('Test User', $results[0]['name']);
        $this->assertEquals('test@example.com', $results[0]['email']);
    }

    public function testDatabaseFirst()
    {
        // Insert test data
        $this->database->query("INSERT INTO {$this->testTable} (name, email) VALUES ('First User', 'first@example.com')");
        
        $sql = "SELECT * FROM {$this->testTable} WHERE name = ?";
        $result = $this->database->first($sql, ['First User']);
        
        $this->assertIsArray($result);
        $this->assertEquals('First User', $result['name']);
        $this->assertEquals('first@example.com', $result['email']);
    }

    public function testDatabaseFirstNotFound()
    {
        $sql = "SELECT * FROM {$this->testTable} WHERE name = ?";
        $result = $this->database->first($sql, ['NonExistentUser']);
        
        $this->assertNull($result);
    }

    public function testDatabaseInsert()
    {
        $sql = "INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)";
        $result = $this->database->insert($sql, ['Insert User', 'insert@example.com']);
        
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
        
        // Verify insertion
        $inserted = $this->database->first("SELECT * FROM {$this->testTable} WHERE id = ?", [$result]);
        $this->assertEquals('Insert User', $inserted['name']);
        $this->assertEquals('insert@example.com', $inserted['email']);
    }

    public function testDatabaseUpdate()
    {
        // Insert test data
        $id = $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['Update User', 'update@example.com']);
        
        // Update the record
        $sql = "UPDATE {$this->testTable} SET name = ?, email = ? WHERE id = ?";
        $result = $this->database->update($sql, ['Updated User', 'updated@example.com', $id]);
        
        $this->assertTrue($result);
        
        // Verify update
        $updated = $this->database->first("SELECT * FROM {$this->testTable} WHERE id = ?", [$id]);
        $this->assertEquals('Updated User', $updated['name']);
        $this->assertEquals('updated@example.com', $updated['email']);
    }

    public function testDatabaseDelete()
    {
        // Insert test data
        $id = $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['Delete User', 'delete@example.com']);
        
        // Delete the record
        $sql = "DELETE FROM {$this->testTable} WHERE id = ?";
        $result = $this->database->delete($sql, [$id]);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $deleted = $this->database->first("SELECT * FROM {$this->testTable} WHERE id = ?", [$id]);
        $this->assertNull($deleted);
    }

    public function testDatabaseTransaction()
    {
        $this->database->beginTransaction();
        
        try {
            // Insert multiple records
            $id1 = $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 1', 'user1@example.com']);
            $id2 = $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 2', 'user2@example.com']);
            
            $this->database->commit();
            
            // Verify both records exist
            $user1 = $this->database->first("SELECT * FROM {$this->testTable} WHERE id = ?", [$id1]);
            $user2 = $this->database->first("SELECT * FROM {$this->testTable} WHERE id = ?", [$id2]);
            
            $this->assertNotNull($user1);
            $this->assertNotNull($user2);
            
        } catch (Exception $e) {
            $this->database->rollback();
            throw $e;
        }
    }

    public function testDatabaseTransactionRollback()
    {
        $this->database->beginTransaction();
        
        try {
            // Insert first record
            $id1 = $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 1', 'user1@example.com']);
            
            // Force an error by inserting duplicate data (if unique constraint exists)
            $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 1', 'user1@example.com']);
            
            $this->database->commit();
            
        } catch (Exception $e) {
            $this->database->rollback();
            
            // Verify no records exist (rollback worked)
            $count = $this->database->first("SELECT COUNT(*) as count FROM {$this->testTable} WHERE name = ?", ['User 1']);
            $this->assertEquals(0, $count['count']);
        }
    }

    public function testDatabaseCount()
    {
        // Insert multiple records
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 1', 'user1@example.com']);
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 2', 'user2@example.com']);
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 3', 'user3@example.com']);
        
        $count = $this->database->first("SELECT COUNT(*) as count FROM {$this->testTable}");
        $this->assertEquals(3, $count['count']);
    }

    public function testDatabaseOrderBy()
    {
        // Insert test data
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['Charlie', 'charlie@example.com']);
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['Alice', 'alice@example.com']);
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['Bob', 'bob@example.com']);
        
        $results = $this->database->select("SELECT * FROM {$this->testTable} ORDER BY name ASC");
        
        $this->assertCount(3, $results);
        $this->assertEquals('Alice', $results[0]['name']);
        $this->assertEquals('Bob', $results[1]['name']);
        $this->assertEquals('Charlie', $results[2]['name']);
    }

    public function testDatabaseLimit()
    {
        // Insert multiple records
        for ($i = 1; $i <= 10; $i++) {
            $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ["User {$i}", "user{$i}@example.com"]);
        }
        
        $results = $this->database->select("SELECT * FROM {$this->testTable} LIMIT 5");
        
        $this->assertCount(5, $results);
    }

    public function testDatabaseWhereIn()
    {
        // Insert test data
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 1', 'user1@example.com']);
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 2', 'user2@example.com']);
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['User 3', 'user3@example.com']);
        
        $placeholders = str_repeat('?,', count(['User 1', 'User 2']) - 1) . '?';
        $sql = "SELECT * FROM {$this->testTable} WHERE name IN ({$placeholders})";
        $results = $this->database->select($sql, ['User 1', 'User 2']);
        
        $this->assertCount(2, $results);
    }

    public function testDatabaseLike()
    {
        // Insert test data
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['John Doe', 'john@example.com']);
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['Jane Doe', 'jane@example.com']);
        $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ['Bob Smith', 'bob@example.com']);
        
        $results = $this->database->select("SELECT * FROM {$this->testTable} WHERE name LIKE ?", ['%Doe%']);
        
        $this->assertCount(2, $results);
    }

    public function testDatabasePerformance()
    {
        $startTime = microtime(true);
        
        // Insert multiple records
        for ($i = 1; $i <= 100; $i++) {
            $this->database->insert("INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)", ["User {$i}", "user{$i}@example.com"]);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $this->assertLessThan(1.0, $executionTime, 'Database operations should be fast');
    }

    public function testDatabaseEscape()
    {
        $maliciousInput = "'; DROP TABLE {$this->testTable}; --";
        
        $sql = "INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)";
        $result = $this->database->insert($sql, [$maliciousInput, 'test@example.com']);
        
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
        
        // Verify the input was properly escaped and stored
        $stored = $this->database->first("SELECT * FROM {$this->testTable} WHERE id = ?", [$result]);
        $this->assertEquals($maliciousInput, $stored['name']);
    }

    public function testDatabaseNullValues()
    {
        $sql = "INSERT INTO {$this->testTable} (name, email) VALUES (?, ?)";
        $result = $this->database->insert($sql, [null, 'test@example.com']);
        
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
        
        // Verify null value was stored
        $stored = $this->database->first("SELECT * FROM {$this->testTable} WHERE id = ?", [$result]);
        $this->assertNull($stored['name']);
    }
} 