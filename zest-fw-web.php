<?php
/**
 * ZestPHP Web Framework
 * 
 * A web extension for ZestPHP Core that adds Twig templating,
 * jQuery, and TailwindCSS integration.
 * 
 * @version 1.0.0
 */

// Define the ZestPHP Web version
define('ZESTPHP_WEB_VERSION', '1.0.0');

// Include ZestPHP Core
if (!defined('DIR_ROOT')) {
    define('DIR_ROOT', dirname(dirname(__FILE__)));
}

// Include ZestPHP Core
$zestCorePath = DIR_ROOT . '/zest-fw-core/zest-fw-core.php';
if (!file_exists($zestCorePath)) {
    die('ZestPHP Core not found at: ' . $zestCorePath);
}
require_once $zestCorePath;

/**
 * Component System Class
 * 
 * Handles component loading, rendering and autoloading
 */
class ComponentSystem {
    private static $instance = null;
    private $loadedComponents = [];
    private $componentsDir = null;
    private $componentNamespaces = [];
    
    /**
     * Constructor
     * 
     * @param string $componentsDir Directory containing components
     */
    private function __construct($componentsDir) {
        $this->componentsDir = $componentsDir;
        
        // Register component autoloader
        spl_autoload_register([$this, 'autoloadComponent']);
    }
    
    /**
     * Get singleton instance
     * 
     * @param string $componentsDir Directory containing components
     * @return ComponentSystem
     */
    public static function getInstance($componentsDir = null) {
        if (self::$instance === null) {
            if ($componentsDir === null) {
                $componentsDir = DIR_APP . '/webroot/static/components';
            }
            
            // Create directory if it doesn't exist
            if (!file_exists($componentsDir)) {
                mkdir($componentsDir, 0755, true);
            }
            
            self::$instance = new self($componentsDir);
        }
        return self::$instance;
    }
    
    /**
     * Register additional component directories
     * 
     * @param string $namespace Namespace for the components
     * @param string $directory Directory containing components
     * @return void
     */
    public function registerComponentNamespace($namespace, $directory) {
        $this->componentNamespaces[$namespace] = $directory;
    }
    
    /**
     * Autoload component class
     * 
     * @param string $className Class name to load
     * @return bool True if loaded, false otherwise
     */
    public function autoloadComponent($className) {
        // Check if it's a component class (Component suffix)
        if (substr($className, -9) === 'Component') {
            // Extract component name (remove Component suffix)
            $componentName = substr($className, 0, -9);
            
            // Check for namespace
            $namespace = '';
            $namespacedComponent = $componentName;
            
            if (strpos($componentName, '\\') !== false) {
                $parts = explode('\\', $componentName);
                $namespace = implode('\\', array_slice($parts, 0, -1));
                $namespacedComponent = end($parts);
            }
            
            // Try to load from registered namespaces first
            if ($namespace && isset($this->componentNamespaces[$namespace])) {
                $componentFile = $this->componentNamespaces[$namespace] . '/' . $namespacedComponent . '/' . $namespacedComponent . '.php';
                
                if (file_exists($componentFile)) {
                    require_once $componentFile;
                    return true;
                }
            }
            
            // Try to load from default components directory
            $componentFile = $this->componentsDir . '/' . $componentName . '/' . $componentName . '.php';
            
            if (file_exists($componentFile)) {
                require_once $componentFile;
                return true;
            }
            
            // Try to load from subdirectories (for organization)
            if ($handle = opendir($this->componentsDir)) {
                while (false !== ($subdir = readdir($handle))) {
                    if ($subdir != "." && $subdir != ".." && is_dir($this->componentsDir . '/' . $subdir)) {
                        $componentFile = $this->componentsDir . '/' . $subdir . '/' . $componentName . '/' . $componentName . '.php';
                        
                        if (file_exists($componentFile)) {
                            require_once $componentFile;
                            return true;
                        }
                    }
                }
                closedir($handle);
            }
        }
        
        return false;
    }
    
    /**
     * Get component assets (CSS, JS)
     * 
     * @param string $componentName Component name
     * @return array Array with CSS and JS paths
     */
    public function getComponentAssets($componentName) {
        $result = [
            'css' => null,
            'js' => null
        ];
        
        $cssFile = $this->componentsDir . '/' . $componentName . '/' . $componentName . '.css';
        if (file_exists($cssFile)) {
            $result['css'] = '/static/components/' . $componentName . '/' . $componentName . '.css';
        }
        
        $jsFile = $this->componentsDir . '/' . $componentName . '/' . $componentName . '.js';
        if (file_exists($jsFile)) {
            $result['js'] = '/static/components/' . $componentName . '/' . $componentName . '.js';
        }
        
        return $result;
    }
    
    /**
     * Get components directory
     * 
     * @return string Components directory
     */
    public function getComponentsDir() {
        return $this->componentsDir;
    }
    
    /**
     * Render a component
     * 
     * @param string $componentName Component name
     * @param array $data Data to pass to the component
     * @param bool $loadAssets Whether to load component assets
     * @return string Rendered component
     */
    public function renderComponent($componentName, $data = [], $loadAssets = true) {
        // Check if component exists
        $componentDir = $this->componentsDir . '/' . $componentName;
        if (!file_exists($componentDir)) {
            return "Component '$componentName' not found";
        }
        
        // Load component class if exists
        $componentClass = $componentName . 'Component';
        $componentInstance = null;
        
        if (class_exists($componentClass)) {
            $componentInstance = new $componentClass();
            
            // Call prepare method if exists
            if (method_exists($componentInstance, 'prepare')) {
                $data = $componentInstance->prepare($data);
            }
        }
        
        // Load component template
        $templateFile = $componentDir . '/' . $componentName . '.twig';
        if (!file_exists($templateFile)) {
            return "Component template for '$componentName' not found";
        }
        
        // Add component to template paths
        $templateEngine = TemplateEngine::getInstance();
        $templateEngine->addPath($componentDir, 'component_' . $componentName);
        
        // Load assets if needed
        if ($loadAssets) {
            $assets = $this->getComponentAssets($componentName);
            
            // Add assets to global assets list for later inclusion
            if ($assets['css']) {
                $GLOBALS['COMPONENT_CSS'][] = $assets['css'];
            }
            
            if ($assets['js']) {
                $GLOBALS['COMPONENT_JS'][] = $assets['js'];
            }
        }
        
        // Mark component as loaded
        $this->loadedComponents[$componentName] = true;
        
        // Render template
        return $templateEngine->render('@component_' . $componentName . '/' . $componentName . '.twig', $data);
    }
    
    /**
     * Get all loaded components
     * 
     * @return array Array of loaded component names
     */
    public function getLoadedComponents() {
        return array_keys($this->loadedComponents);
    }
    
    /**
     * Create a new component
     * 
     * @param string $componentName Component name
     * @return bool True if created, false otherwise
     */
    public function createComponent($componentName) {
        $componentDir = $this->componentsDir . '/' . $componentName;
        
        // Check if component already exists
        if (file_exists($componentDir)) {
            return false;
        }
        
        // Create component directory
        mkdir($componentDir, 0755, true);
        
        // Create component files
        $phpContent = "<?php\n/**\n * {$componentName} Component\n */\nclass {$componentName}Component {\n    /**\n     * Prepare data for the component\n     * \n     * @param array \$data Input data\n     * @return array Prepared data\n     */\n    public function prepare(\$data = []) {\n        // Add your component logic here\n        return \$data;\n    }\n}\n";
        
        $twigContent = "<div class=\"{$componentName}-component\">\n    <h3>{{ title|default('{$componentName} Component') }}</h3>\n    <div class=\"content\">\n        {{ content|default('') }}\n    </div>\n</div>";
        
        $cssContent = "/**\n * {$componentName} Component Styles\n */\n.{$componentName}-component {\n    margin-bottom: 1rem;\n    padding: 1rem;\n    border: 1px solid #e2e8f0;\n    border-radius: 0.25rem;\n}\n\n.{$componentName}-component h3 {\n    margin-top: 0;\n    margin-bottom: 0.5rem;\n    font-size: 1.25rem;\n    font-weight: 600;\n}\n";
        
        $jsContent = "/**\n * {$componentName} Component JavaScript\n */\n$(document).ready(function() {\n    // Initialize {$componentName} component\n    $('.{$componentName}-component').each(function() {\n        const \$component = $(this);\n        \n        // Add your component JavaScript here\n        console.log('{$componentName} component initialized');\n    });\n});\n";
        
        file_put_contents($componentDir . '/' . $componentName . '.php', $phpContent);
        file_put_contents($componentDir . '/' . $componentName . '.twig', $twigContent);
        file_put_contents($componentDir . '/' . $componentName . '.css', $cssContent);
        file_put_contents($componentDir . '/' . $componentName . '.js', $jsContent);
        
        return true;
    }
}

/**
 * Template Engine Class
 * 
 * Handles rendering templates using Twig
 */
class TemplateEngine {
    private static $instance = null;
    private $twig = null;
    private $loader = null;
    
    /**
     * Constructor
     * 
     * @param string $templateDir Directory containing templates
     * @param string $cacheDir Directory for template cache
     */
    private function __construct($templateDir, $cacheDir) {
        // Check if Twig is installed
        if (!class_exists('\\Twig\\Environment')) {
            $this->loadTwig();
        }
        
        // Initialize Twig if available
        if (class_exists('\\Twig\\Environment')) {
            $this->loader = new \Twig\Loader\FilesystemLoader($templateDir);
            $this->twig = new \Twig\Environment($this->loader, [
                'cache' => $cacheDir,
                'debug' => isset($GLOBALS["DEBUG"]) && $GLOBALS["DEBUG"] === true,
                'auto_reload' => true
            ]);
            
            // Add debug extension if debug is enabled
            if (isset($GLOBALS["DEBUG"]) && $GLOBALS["DEBUG"] === true) {
                $this->twig->addExtension(new \Twig\Extension\DebugExtension());
            }
            
            // Add custom functions and filters
            $this->addCustomFunctions();
        }
    }
    
    /**
     * Get singleton instance
     * 
     * @param string $templateDir Directory containing templates
     * @param string $cacheDir Directory for template cache
     * @return TemplateEngine
     */
    public static function getInstance($templateDir = null, $cacheDir = null) {
        if (self::$instance === null) {
            if ($templateDir === null) {
                $templateDir = DIR_APP . '/templates';
            }
            if ($cacheDir === null) {
                $cacheDir = DIR_CACHE . '/twig';
            }
            
            // Create directories if they don't exist
            if (!file_exists($templateDir)) {
                mkdir($templateDir, 0755, true);
            }
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            
            self::$instance = new self($templateDir, $cacheDir);
        }
        return self::$instance;
    }
    
    /**
     * Load Twig using Composer autoloader
     */
    private function loadTwig() {
        $composerAutoload = DIR_APP . '/lib/vendor/autoload.php';
        if (file_exists($composerAutoload)) {
            require_once $composerAutoload;
        } else {
            // Log warning about missing Twig
            error_log('Twig not found. Please install it using Composer.');
        }
    }
    
    /**
     * Add custom functions and filters to Twig
     */
    private function addCustomFunctions() {
        // Add asset function
        $this->twig->addFunction(new \Twig\TwigFunction('asset', function ($path) {
            return '/static/' . ltrim($path, '/');
        }));
        
        // Add url function
        $this->twig->addFunction(new \Twig\TwigFunction('url', function ($path) {
            return '/' . ltrim($path, '/');
        }));
        
        // Add debug function
        $this->twig->addFunction(new \Twig\TwigFunction('debug', function ($var) {
            if (isset($GLOBALS["DEBUG"]) && $GLOBALS["DEBUG"] === true) {
                pr($var);
            }
        }));
        
        // Add component function
        $this->twig->addFunction(new \Twig\TwigFunction('component', function ($name, $data = [], $loadAssets = true) {
            return ComponentSystem::getInstance()->renderComponent($name, $data, $loadAssets);
        }, ['is_safe' => ['html']]));
    }
    
    /**
     * Render a template
     * 
     * @param string $template Template name
     * @param array $data Data to pass to the template
     * @return string Rendered template
     */
    public function render($template, $data = []) {
        if ($this->twig === null) {
            return 'Template engine not initialized';
        }
        
        try {
            // Add component assets to data
            if (isset($GLOBALS['COMPONENT_CSS']) && !empty($GLOBALS['COMPONENT_CSS'])) {
                $data['component_css'] = array_unique($GLOBALS['COMPONENT_CSS']);
            }
            
            if (isset($GLOBALS['COMPONENT_JS']) && !empty($GLOBALS['COMPONENT_JS'])) {
                $data['component_js'] = array_unique($GLOBALS['COMPONENT_JS']);
            }
            
            return $this->twig->render($template, $data);
        } catch (\Exception $e) {
            if (isset($GLOBALS["DEBUG"]) && $GLOBALS["DEBUG"] === true) {
                return 'Template error: ' . $e->getMessage();
            } else {
                return 'An error occurred while rendering the template.';
            }
        }
    }
    
    /**
     * Add a template path
     * 
     * @param string $path Path to templates
     * @param string $namespace Namespace for templates
     */
    public function addPath($path, $namespace = null) {
        if ($this->loader !== null) {
            if ($namespace !== null) {
                $this->loader->addPath($path, $namespace);
            } else {
                $this->loader->addPath($path);
            }
        }
    }
    
    /**
     * Get the Twig instance
     * 
     * @return \Twig\Environment
     */
    public function getTwig() {
        return $this->twig;
    }
}

/**
 * Helper function to register a component namespace
 * 
 * @param string $namespace Namespace for the components
 * @param string $directory Directory containing components
 * @return void
 */
function register_component_namespace($namespace, $directory) {
    $componentSystem = ComponentSystem::getInstance();
    $componentSystem->registerComponentNamespace($namespace, $directory);
}

/**
 * Helper function to scan for components in a directory
 * 
 * @param string $directory Directory to scan for components
 * @return array Array of component names
 */
function scan_components($directory = null) {
    if ($directory === null) {
        $componentSystem = ComponentSystem::getInstance();
        $directory = $componentSystem->getComponentsDir();
    }
    
    $components = [];
    
    if (is_dir($directory)) {
        if ($handle = opendir($directory)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && is_dir($directory . '/' . $entry)) {
                    // Check if this is a component directory (has ComponentName.php file)
                    if (file_exists($directory . '/' . $entry . '/' . $entry . '.php')) {
                        $components[] = $entry;
                    } else {
                        // Check if this is a category directory
                        $subComponents = scan_components($directory . '/' . $entry);
                        foreach ($subComponents as $subComponent) {
                            $components[] = $entry . '/' . $subComponent;
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
    
    return $components;
}

/**
 * Initialize the web framework
 * 
 * This function is called automatically when the framework is loaded
 */
function init_web_framework() {
    // Define web-specific directories
    if (!defined('DIR_TEMPLATES')) define('DIR_TEMPLATES', DIR_APP . '/templates');
    if (!defined('DIR_STATIC')) define('DIR_STATIC', DIR_WEBROOT . '/static');
    if (!defined('DIR_COMPONENTS')) define('DIR_COMPONENTS', DIR_STATIC . '/components');
    
    // Initialize component system
    $componentSystem = ComponentSystem::getInstance();
    
    // Initialize template engine
    $templateEngine = TemplateEngine::getInstance();
    
    // Initialize global arrays for component assets
    $GLOBALS['COMPONENT_CSS'] = [];
    $GLOBALS['COMPONENT_JS'] = [];
    
    // Create directories if they don't exist
    $webDirectories = [
        DIR_TEMPLATES,
        DIR_STATIC,
        DIR_STATIC . '/css',
        DIR_STATIC . '/js',
        DIR_STATIC . '/img',
        DIR_STATIC . '/fonts',
        DIR_COMPONENTS
    ];
    
    foreach ($webDirectories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    // Add Twig functions for components - only if they don't already exist
    $twig = $templateEngine->getTwig();
    
    // Vérifier si les fonctions existent déjà avant de les ajouter
    $twigFunctions = [
        'component' => function($componentName, $data = [], $loadAssets = true) {
            return render_component($componentName, $data, $loadAssets);
        },
        'asset' => function($path) {
            return '/static/' . ltrim($path, '/');
        },
        'url' => function($path = '') {
            return '/' . ltrim($path, '/');
        }
    ];
    
    foreach ($twigFunctions as $name => $callback) {
        try {
            $twig->getFunction($name);
        } catch (\Twig\Error\RuntimeError $e) {
            // La fonction n'existe pas, on peut l'ajouter
            $twig->addFunction(new \Twig\TwigFunction($name, $callback));
        }
    }
    
    // Auto-register component namespaces if config exists
    $componentNamespacesFile = DIR_APP . '/config/component_namespaces.php';
    if (file_exists($componentNamespacesFile)) {
        include $componentNamespacesFile;
    }
    
    // Auto-scan for components
    $components = scan_components();
    foreach ($components as $component) {
        // Preload component class
        $componentClass = str_replace('/', '\\', $component) . 'Component';
        if (!class_exists($componentClass)) {
            $componentSystem->autoloadComponent($componentClass);
        }
    }
}

/**
 * Helper function to render a template
 * 
 * @param string $template Template name
 * @param array $data Data to pass to the template
 * @return string Rendered template
 */
function render_template($template, $data = []) {
    return TemplateEngine::getInstance()->render($template, $data);
}

/**
 * Helper function to output a rendered template
 * 
 * @param string $template Template name
 * @param array $data Data to pass to the template
 */
function display_template($template, $data = []) {
    echo render_template($template, $data);
}

/**
 * Helper function to render a component
 * 
 * @param string $componentName Component name
 * @param array $data Data to pass to the component
 * @param bool $loadAssets Whether to load component assets
 * @return string Rendered component
 */
function render_component($componentName, $data = [], $loadAssets = true) {
    return ComponentSystem::getInstance()->renderComponent($componentName, $data, $loadAssets);
}

/**
 * Helper function to output a rendered component
 * 
 * @param string $componentName Component name
 * @param array $data Data to pass to the component
 * @param bool $loadAssets Whether to load component assets
 */
function display_component($componentName, $data = [], $loadAssets = true) {
    echo render_component($componentName, $data, $loadAssets);
}

/**
 * Helper function to create a new component
 * 
 * @param string $componentName Component name
 * @return bool True if created, false otherwise
 */
function create_component($componentName) {
    return ComponentSystem::getInstance()->createComponent($componentName);
}

/**
 * Initialize a new ZestPHP Web application
 * 
 * This function copies the boilerplate files to the app directory
 * 
 * @return bool True if successful, false otherwise
 */
function initializeWebApplication() {
    $boilerplateDir = __DIR__ . '/boilerplate';
    $appDir = DIR_APP;
    
    if (!file_exists($boilerplateDir)) {
        error_log("Boilerplate directory not found: $boilerplateDir");
        return false;
    }
    
    // Create app directory if it doesn't exist
    if (!file_exists($appDir)) {
        mkdir($appDir, 0755, true);
    }
    
    // Copy boilerplate files to app directory
    copyWebBoilerplate($boilerplateDir, $appDir);
    
    // Install Composer dependencies if Composer is available
    installComposerDependencies($appDir);
    
    return true;
}

/**
 * Copy web boilerplate files to the app directory
 * 
 * @param string $source Source directory
 * @param string $destination Destination directory
 * @return bool True if successful, false otherwise
 */
function copyWebBoilerplate($source, $destination) {
    if (!is_dir($source)) {
        return false;
    }
    
    $dir = opendir($source);
    if (!file_exists($destination)) {
        mkdir($destination, 0755, true);
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file == "." || $file == "..") {
            continue;
        }
        
        $srcFile = $source . '/' . $file;
        $destFile = $destination . '/' . $file;
        
        if (is_dir($srcFile)) {
            copyWebBoilerplate($srcFile, $destFile);
        } else {
            copy($srcFile, $destFile);
        }
    }
    
    closedir($dir);
    return true;
}

/**
 * Install Composer dependencies
 * 
 * @param string $appDir Application directory
 * @return bool True if successful, false otherwise
 */
function installComposerDependencies($appDir) {
    $libDir = $appDir . '/lib';
    
    // Create lib directory if it doesn't exist
    if (!file_exists($libDir)) {
        mkdir($libDir, 0755, true);
    }
    
    // Check if Composer is available
    $composerPath = exec('which composer');
    if (empty($composerPath)) {
        error_log("Composer not found. Please install Composer to manage dependencies.");
        return false;
    }
    
    // Run Composer install
    $currentDir = getcwd();
    chdir($libDir);
    $output = [];
    $returnVar = 0;
    exec('composer install --no-interaction', $output, $returnVar);
    chdir($currentDir);
    
    return $returnVar === 0;
}

// Initialize the web framework
init_web_framework();

// Check if the script is called with --init argument
if (isset($argv) && in_array('--init', $argv)) {
    if (initializeWebApplication()) {
        echo "ZestPHP Web application initialized successfully.\n";
    } else {
        echo "Failed to initialize ZestPHP Web application.\n";
    }
}