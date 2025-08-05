<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    protected $view;

    public function setUp(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
        
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }
        
        $this->view = new \Stackvel\View();
    }

    public function testViewRender()
    {
        $output = $this->view->render('home.index', [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'features' => ['Feature A', 'Feature B', 'Feature C']
        ]);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('@extends', $output);
        $this->assertStringContainsString('@section', $output);
    }

    public function testViewRenderWithNoData()
    {
        $output = $this->view->render('home.index');
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewRenderWithEmptyData()
    {
        $output = $this->view->render('home.index', []);
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewExists()
    {
        $exists = $this->view->exists('home.index');
        $this->assertTrue($exists);
    }

    public function testViewNotExists()
    {
        $exists = $this->view->exists('nonexistent.view');
        $this->assertFalse($exists);
    }

    public function testViewRenderWithLayout()
    {
        $output = $this->view->render('home.index', [
            'title' => 'Layout Test',
            'description' => 'Testing layout functionality',
            'features' => ['Layout', 'Template', 'Engine']
        ]);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('@extends', $output);
        $this->assertStringContainsString('@section', $output);
    }

    public function testViewRenderWithComplexData()
    {
        $data = [
            'title' => 'Complex Test',
            'description' => 'Testing complex data structures',
            'features' => [
                ['name' => 'Feature 1', 'description' => 'First feature'],
                ['name' => 'Feature 2', 'description' => 'Second feature'],
                ['name' => 'Feature 3', 'description' => 'Third feature']
            ],
            'settings' => [
                'enabled' => true,
                'count' => 42,
                'items' => ['a', 'b', 'c']
            ]
        ];

        $output = $this->view->render('home.index', $data);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewRenderWithSpecialCharacters()
    {
        $data = [
            'title' => 'Special & Characters < > " \' Test',
            'description' => 'Testing with special characters: & < > " \'',
            'features' => ['Feature & Test', 'Feature < Test', 'Feature > Test']
        ];

        $output = $this->view->render('home.index', $data);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewRenderWithNumericData()
    {
        $data = [
            'title' => 'Numeric Test',
            'count' => 123,
            'price' => 99.99,
            'percentage' => 85.5,
            'features' => [1, 2, 3, 4, 5]
        ];

        $output = $this->view->render('home.index', $data);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewRenderWithBooleanData()
    {
        $data = [
            'title' => 'Boolean Test',
            'enabled' => true,
            'disabled' => false,
            'features' => ['Feature 1', 'Feature 2']
        ];

        $output = $this->view->render('home.index', $data);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewRenderWithNullData()
    {
        $data = [
            'title' => 'Null Test',
            'description' => null,
            'features' => ['Feature 1', null, 'Feature 3']
        ];

        $output = $this->view->render('home.index', $data);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewRenderWithLargeData()
    {
        $features = [];
        for ($i = 1; $i <= 100; $i++) {
            $features[] = "Feature {$i}";
        }

        $data = [
            'title' => 'Large Data Test',
            'description' => 'Testing with large amount of data',
            'features' => $features
        ];

        $output = $this->view->render('home.index', $data);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewRenderWithEscapedData()
    {
        $data = [
            'title' => 'Escaped Test',
            'html_content' => '<script>alert("test")</script>',
            'features' => ['<b>Bold</b>', '<i>Italic</i>']
        ];

        $output = $this->view->render('home.index', $data);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewRenderPerformance()
    {
        $startTime = microtime(true);
        
        for ($i = 0; $i < 10; $i++) {
            $output = $this->view->render('home.index', [
                'title' => "Performance Test {$i}",
                'description' => "Testing performance iteration {$i}",
                'features' => ['Fast', 'Efficient', 'Optimized']
            ]);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $this->assertLessThan(1.0, $executionTime, 'View rendering should be fast');
    }

    public function testViewShare()
    {
        $this->view->share('shared_key', 'shared_value');
        
        $output = $this->view->render('home.index', [
            'title' => 'Share Test',
            'features' => ['Feature 1']
        ]);
        
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testViewGetShared()
    {
        $this->view->share('test_key', 'test_value');
        
        $value = $this->view->getShared('test_key');
        $this->assertEquals('test_value', $value);
    }

    public function testViewClearCache()
    {
        $this->view->clearCache();
        $this->assertTrue(true); // Method should not throw exception
    }
} 