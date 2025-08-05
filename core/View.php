<?php

namespace Stackvel;

/**
 * Stackvel Framework - View Class
 * 
 * Provides Blade templating functionality with support for layouts,
 * components, and data passing.
 */
class View
{
    /**
     * View data
     */
    private array $data = [];

    /**
     * View cache
     */
    private array $cache = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialize with common data
        $this->data = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'Stackvel',
                'url' => $_ENV['APP_URL'] ?? 'http://localhost',
                'env' => $_ENV['APP_ENV'] ?? 'production'
            ]
        ];
    }

    /**
     * Render a view
     */
    public function render(string $view, array $data = []): string
    {
        $viewPath = $this->getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$view}");
        }
        
        // Merge data
        $this->data = array_merge($this->data, $data);
        
        // Extract data to variables
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include $viewPath;
        
        // Get the content
        $content = ob_get_clean();
        
        // Process Blade directives
        $content = $this->processBladeDirectives($content);
        
        // Execute the processed PHP code
        return $this->executeProcessedContent($content);
    }

    /**
     * Get the full path to a view file
     */
    private function getViewPath(string $view): string
    {
        $view = str_replace('.', '/', $view);
        return APP_ROOT . "/resources/views/{$view}.blade.php";
    }

    /**
     * Process Blade template directives
     */
    private function processBladeDirectives(string $content): string
    {
        // Process variable interpolation {{ }}
        $content = $this->processVariables($content);
        
        // Process @if directives
        $content = $this->processIfDirectives($content);
        
        // Process @foreach directives
        $content = $this->processForeachDirectives($content);
        
        // Process @include directives
        $content = $this->processIncludeDirectives($content);
        
        // Process @extends and @section directives
        $content = $this->processLayoutDirectives($content);
        
        // Process @csrf directive
        $content = $this->processCsrfDirective($content);
        
        // Process @method directive
        $content = $this->processMethodDirective($content);
        
        // Process @old directive
        $content = $this->processOldDirective($content);
        
        // Process @error directive
        $content = $this->processErrorDirective($content);
        
        return $content;
    }

    /**
     * Process variable interpolation {{ }}
     */
    private function processVariables(string $content): string
    {
        return preg_replace_callback('/\{\{\s*([^}]+)\s*\}\}/', function ($matches) {
            $variable = trim($matches[1]);
            $variable = str_replace('$this->', '$view->', $variable);
            return '<?php echo htmlspecialchars(' . $variable . ' ?? \'\'); ?>';
        }, $content);
    }

    /**
     * Process @if directives
     */
    private function processIfDirectives(string $content): string
    {
        // @if directive
        $content = preg_replace_callback('/@if\s*\((.+)\)/', function ($matches) {
            $condition = str_replace('$this->', '$view->', $matches[1]);
            return '<?php if (' . $condition . '): ?>';
        }, $content);
        
        // @elseif directive
        $content = preg_replace_callback('/@elseif\s*\((.+)\)/', function ($matches) {
            $condition = str_replace('$this->', '$view->', $matches[1]);
            return '<?php elseif (' . $condition . '): ?>';
        }, $content);
        
        // @else directive
        $content = preg_replace('/@else/', '<?php else: ?>', $content);
        
        // @endif directive
        $content = preg_replace('/@endif/', '<?php endif; ?>', $content);
        
        return $content;
    }

    /**
     * Process @foreach directives
     */
    private function processForeachDirectives(string $content): string
    {
        // @foreach directive
        $content = preg_replace_callback('/@foreach\s*\((.+?)\)/', function ($matches) {
            return '<?php foreach (' . $matches[1] . '): ?>';
        }, $content);
        
        // @endforeach directive
        $content = preg_replace('/@endforeach/', '<?php endforeach; ?>', $content);
        
        return $content;
    }

    /**
     * Process @include directives
     */
    private function processIncludeDirectives(string $content): string
    {
        return preg_replace_callback('/@include\s*[\'"]([^\'"]+)[\'"]/', function ($matches) {
            $view = $matches[1];
            return '<?php echo $this->render(\'' . $view . '\'); ?>';
        }, $content);
    }

    /**
     * Process @extends and @section directives
     */
    private function processLayoutDirectives(string $content): string
    {
        // Extract layout name
        if (preg_match('/@extends\s*\([\'"]([^\'"]+)[\'"]\)/', $content, $matches)) {
            $layout = $matches[1];
            
            // Extract sections - handle both formats
            $sections = [];
            
            // Handle @section('name', content) format
            preg_match_all('/@section\s*\([\'"]([^\'"]+)[\'"],\s*([^)]+)\)/', $content, $simpleMatches, PREG_SET_ORDER);
            foreach ($simpleMatches as $match) {
                $sectionName = $match[1];
                $sectionContent = trim($match[2]);
                
                // Process Blade directives in the section content
                $sectionContent = $this->processVariables($sectionContent);
                $sectionContent = $this->processIfDirectives($sectionContent);
                $sectionContent = $this->processForeachDirectives($sectionContent);
                $sectionContent = $this->processIncludeDirectives($sectionContent);
                $sectionContent = $this->processCsrfDirective($sectionContent);
                $sectionContent = $this->processMethodDirective($sectionContent);
                $sectionContent = $this->processOldDirective($sectionContent);
                $sectionContent = $this->processErrorDirective($sectionContent);
                
                $sections[$sectionName] = $sectionContent;
            }
            
            // Handle @section('name') ... @endsection format
            preg_match_all('/@section\s*\([\'"]([^\'"]+)[\'"]\)(.*?)@endsection/s', $content, $blockMatches, PREG_SET_ORDER);
            foreach ($blockMatches as $match) {
                $sectionName = $match[1];
                $sectionContent = trim($match[2]);
                
                // Process Blade directives in the section content
                $sectionContent = $this->processVariables($sectionContent);
                $sectionContent = $this->processIfDirectives($sectionContent);
                $sectionContent = $this->processForeachDirectives($sectionContent);
                $sectionContent = $this->processIncludeDirectives($sectionContent);
                $sectionContent = $this->processCsrfDirective($sectionContent);
                $sectionContent = $this->processMethodDirective($sectionContent);
                $sectionContent = $this->processOldDirective($sectionContent);
                $sectionContent = $this->processErrorDirective($sectionContent);
                
                $sections[$sectionName] = $sectionContent;
            }
            
            // Render layout with sections
            return $this->renderLayout($layout, $sections);
        }
        
        return $content;
    }



    /**
     * Render a layout with sections
     */
    private function renderLayout(string $layout, array $sections): string
    {
        $layoutPath = $this->getViewPath($layout);
        
        if (!file_exists($layoutPath)) {
            throw new \Exception("Layout not found: {$layout}");
        }
        
        // Store sections in cache
        $this->cache['sections'] = $sections;
        
        // Extract data to variables
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the layout file
        include $layoutPath;
        
        // Get the content
        $content = ob_get_clean();
        
        // Process Blade directives for the layout (including @yield)
        $content = $this->processLayoutBladeDirectives($content);
        
        // Execute the processed PHP code
        return $this->executeProcessedContent($content);
    }

    /**
     * Process @csrf directive
     */
    private function processCsrfDirective(string $content): string
    {
        return preg_replace('/@csrf/', '<input type="hidden" name="_token" value="<?php echo $this->generateCsrfToken(); ?>">', $content);
    }

    /**
     * Process @method directive
     */
    private function processMethodDirective(string $content): string
    {
        return preg_replace_callback('/@method\s*[\'"]([^\'"]+)[\'"]/', function ($matches) {
            $method = strtoupper($matches[1]);
            return '<input type="hidden" name="_method" value="' . $method . '">';
        }, $content);
    }

    /**
     * Process @old directive
     */
    private function processOldDirective(string $content): string
    {
        return preg_replace_callback('/@old\s*[\'"]([^\'"]+)[\'"]/', function ($matches) {
            $field = $matches[1];
            return '<?php echo $_SESSION[\'old\'][\'' . $field . '\'] ?? \'\'; ?>';
        }, $content);
    }

    /**
     * Process @error directive
     */
    private function processErrorDirective(string $content): string
    {
        return preg_replace_callback('/@error\s*[\'"]([^\'"]+)[\'"](.*?)@enderror/s', function ($matches) {
            $field = $matches[1];
            $content = trim($matches[2]);
            // Replace $error with the actual error message
            $content = str_replace('$error', '$_SESSION[\'errors\'][\'' . $field . '\']', $content);
            return '<?php if (isset($_SESSION[\'errors\'][\'' . $field . '\'])): ?>' . $content . '<?php endif; ?>';
        }, $content);
    }

    /**
     * Execute processed PHP content
     */
    private function executeProcessedContent(string $content): string
    {
        // Create a temporary file with the processed content
        $tempFile = tempnam(sys_get_temp_dir(), 'stackvel_view_');
        file_put_contents($tempFile, $content);
        
        // Extract data to variables
        extract($this->data);
        
        // Make $this available in the temporary file
        $view = $this;
        
        // Start output buffering
        ob_start();
        
        // Include the temporary file
        include $tempFile;
        
        // Get the content
        $result = ob_get_clean();
        
        // Clean up
        unlink($tempFile);
        
        return $result;
    }

    /**
     * Process Blade directives for layouts (including @yield)
     */
    private function processLayoutBladeDirectives(string $content): string
    {
        // Process variable interpolation {{ }}
        $content = $this->processVariables($content);
        
        // Process @yield directives
        $content = preg_replace_callback('/@yield\s*\([\'"]([^\'"]+)[\'"](?:,\s*([^)]+))?\)?/', function ($matches) {
            $section = $matches[1];
            $default = isset($matches[2]) ? '(' . $matches[2] . ')' : "''";
            return '<?php echo $view->yield(\'' . $section . '\') ?: ' . $default . '; ?>';
        }, $content);
        
        // Process other directives that might be in layouts
        $content = $this->processIfDirectives($content);
        $content = $this->processForeachDirectives($content);
        $content = $this->processCsrfDirective($content);
        $content = $this->processMethodDirective($content);
        $content = $this->processOldDirective($content);
        $content = $this->processErrorDirective($content);
        
        return $content;
    }

    /**
     * Generate CSRF token
     */
    private function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Yield a section content
     */
    public function yield(string $section): string
    {
        return $this->cache['sections'][$section] ?? '';
    }

    /**
     * Share data with all views
     */
    public function share(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Get shared data
     */
    public function getShared(string $key = null)
    {
        if ($key === null) {
            return $this->data;
        }
        
        return $this->data[$key] ?? null;
    }

    /**
     * Check if a view exists
     */
    public function exists(string $view): bool
    {
        return file_exists($this->getViewPath($view));
    }

    /**
     * Clear view cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Check if flash message exists
     */
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Get flash message
     */
    public function getFlash(string $key, $default = null)
    {
        return $_SESSION['flash'][$key] ?? $default;
    }
} 