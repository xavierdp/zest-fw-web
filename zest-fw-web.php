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

// Define global component paths array if not already defined
if (!isset($GLOBALS['COMPONENT_PATHS']) || !is_array($GLOBALS['COMPONENT_PATHS'])) {
    $GLOBALS['COMPONENT_PATHS'] = [];
}

// Include ZestPHP Core
$zestCorePath = DIR_ROOT . '/zest-fw-core/zest-fw-core.php';
if (!file_exists($zestCorePath)) {
    die('ZestPHP Core not found at: ' . $zestCorePath);
}
require_once $zestCorePath;

/**
 * Base Component Class
 * 
 * Abstract base class for all components
 */
abstract class Component {
    /**
     * Prepare data for the component
     * 
     * @param array $data Input data
     * @return array Prepared data
     */
    public function prepare($data = []) {
        return $data;
    }
    
    /**
     * Render the component
     * 
     * @param array $data Component data
     * @return string Rendered component
     */
    public function render($data = []) {
        return '';
    }
}

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
     */
    private function __construct() {
        // Set components directory
        $this->componentsDir = DIR_ROOT . "/app/webroot/static/components";
        
        // Register in global class paths
        if (!isset($GLOBALS['CLASS_PATHS'])) {
            $GLOBALS['CLASS_PATHS'] = [];
        }
        $GLOBALS['CLASS_PATHS'][] = $this->componentsDir;
        
        // Register component autoloader
        spl_autoload_register([$this, 'autoloadComponent']);
    }
    
    /**
     * Get singleton instance
     * 
     * @return ComponentSystem
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
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
     * Autoload a component class
     * 
     * @param string $className Component class name
     * @return bool True if component was loaded, false otherwise
     */
    public function autoloadComponent($className) {
        // Get component name from class name
        $componentName = $className;
        
        // Try to load from components directory
        $componentFile = $this->componentsDir . '/' . $componentName . '/' . $componentName . '.php';
        if (file_exists($componentFile)) {
            require_once $componentFile;
            return class_exists($componentName);
        }
        
        // Try to load from components directory (direct file)
        $componentFile = $this->componentsDir . '/' . $componentName . '.php';
        if (file_exists($componentFile)) {
            require_once $componentFile;
            return class_exists($componentName);
        }
        
        // Try to load from class paths
        if (isset($GLOBALS['CLASS_PATHS']) && is_array($GLOBALS['CLASS_PATHS'])) {
            foreach ($GLOBALS['CLASS_PATHS'] as $path) {
                $classFile = $path . '/' . $componentName . '.php';
                if (file_exists($classFile)) {
                    require_once $classFile;
                    return class_exists($componentName);
                }
            }
        }
        
        // Debug output
        $debug = "================================= Tried paths: =================================\n";
        if (isset($GLOBALS['CLASS_PATHS']) && is_array($GLOBALS['CLASS_PATHS'])) {
            foreach ($GLOBALS['CLASS_PATHS'] as $path) {
                $classFile = $path . '/' . $componentName . '.php';
                $debug .= str_pad("  - " . $classFile . " ", 80, "=", STR_PAD_BOTH) . "\n";
            }
        }
        $debug .= str_pad(" Class not found: " . $componentName . " ", 80, "=", STR_PAD_BOTH) . "\n";
        
        error_log($debug);
        
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
        // Try to load component class - use only the direct class name, not with Component suffix
        $componentClass = $componentName;
        
        // Try to load the component class
        if (!class_exists($componentClass)) {
            $this->autoloadComponent($componentClass);
        }
        
        // Check if component class exists
        if (class_exists($componentClass)) {
            // Create instance of the component
            $component = new $componentClass();
        } else {
            // Try one more time with direct path to component file
            $componentFile = $this->componentsDir . '/' . $componentName . '/' . $componentName . '.php';
            if (file_exists($componentFile)) {
                require_once $componentFile;
                
                // Check again after direct include
                if (class_exists($componentClass)) {
                    $component = new $componentClass();
                } else {
                    // Component not found
                    return "Component not found: $componentName";
                }
            } else {
                // Component not found
                return "Component not found: $componentName";
            }
        }
        
        // Prepare data
        if (method_exists($component, 'prepare')) {
            $data = $component->prepare($data);
        }
        
        // Load assets if needed
        if ($loadAssets) {
            $this->loadComponentAssets($componentName);
        }
        
        // Render component
        if (method_exists($component, 'render')) {
            return $component->render($data);
        }
        
        // If no render method, try to load template
        $templateFile = $this->findComponentTemplate($componentName);
        if ($templateFile) {
            // Use Twig to render the template
            $twig = TemplateEngine::getInstance()->getTwig();
            if ($twig) {
                try {
                    // Add component name to data for fully dynamic templates
                    $data['_componentName'] = $componentName;
                    return $twig->render($templateFile, $data);
                } catch (\Exception $e) {
                    return "Error rendering component template: " . $e->getMessage();
                }
            }
        }
        
        return "No render method or template found for component: $componentName";
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
        // Create component directory
        $componentDir = $this->componentsDir . '/' . $componentName;
        if (!file_exists($componentDir)) {
            mkdir($componentDir, 0755, true);
        }
        
        // Create component files with concatenated strings to avoid syntax issues
        $phpContent = '<?php' . "\n";
        $phpContent .= '/**' . "\n";
        $phpContent .= ' * ' . $componentName . ' Component' . "\n";
        $phpContent .= ' */' . "\n";
        $phpContent .= 'class ' . $componentName . ' extends Component {' . "\n";
        $phpContent .= '    /**' . "\n";
        $phpContent .= '     * Prepare data for the component' . "\n";
        $phpContent .= '     * ' . "\n";
        $phpContent .= '     * @param array $data Input data' . "\n";
        $phpContent .= '     * @return array Prepared data' . "\n";
        $phpContent .= '     */' . "\n";
        $phpContent .= '    public function prepare($data = []) {' . "\n";
        $phpContent .= '        // Add your component logic here' . "\n";
        $phpContent .= '        return $data;' . "\n";
        $phpContent .= '    }' . "\n";
        $phpContent .= '}';
        
        $twigContent = '<div class="' . $componentName . '-component">' . "\n";
        $twigContent .= '    <h3>{{ title|default(\'' . $componentName . ' Component\') }}</h3>' . "\n";
        $twigContent .= '    <div class="content">' . "\n";
        $twigContent .= '        {{ content|default(\'\') }}' . "\n";
        $twigContent .= '    </div>' . "\n";
        $twigContent .= '</div>';
        
        $cssContent = '.' . $componentName . '-component {' . "\n";
        $cssContent .= '    /* Add your component styles here */' . "\n";
        $cssContent .= '}';
        
        $jsContent = '// ' . $componentName . ' component JavaScript' . "\n";
        
        // Write files
        file_put_contents($componentDir . '/' . $componentName . '.php', $phpContent);
        file_put_contents($componentDir . '/' . $componentName . '.twig', $twigContent);
        file_put_contents($componentDir . '/' . $componentName . '.css', $cssContent);
        file_put_contents($componentDir . '/' . $componentName . '.js', $jsContent);
        
        return true;
    }
    
    private function loadComponentAssets($componentName) {
        // Get component directory
        $componentDir = $this->componentsDir . '/' . $componentName;
        
        // Check if component CSS exists
        $cssFile = $componentDir . '/' . $componentName . '.css';
        if (file_exists($cssFile)) {
            // Add CSS to header
            $cssUrl = '/static/components/' . $componentName . '/' . $componentName . '.css';
            echo '<link rel="stylesheet" href="' . $cssUrl . '">';
        }
        
        // Check if component JS exists
        $jsFile = $componentDir . '/' . $componentName . '.js';
        if (file_exists($jsFile)) {
            // Add JS to footer
            $jsUrl = '/static/components/' . $componentName . '/' . $componentName . '.js';
            echo '<script src="' . $jsUrl . '"></script>';
        }
    }
    
    private function findComponentTemplate($componentName) {
        $templateFile = $this->componentsDir . '/' . $componentName . '/' . $componentName . '.twig';
        if (file_exists($templateFile)) {
            return '@component_' . $componentName . '/' . $componentName . '.twig';
        }
        
        return null;
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
     * Add custom functions to Twig
     */
    private function addCustomFunctions() {
        if ($this->twig) {
            // Add component function
            $this->twig->addFunction(new \Twig\TwigFunction('component', function($componentName, $data = []) {
                $componentSystem = ComponentSystem::getInstance();
                if ($componentSystem) {
                    return $componentSystem->renderComponent($componentName, $data);
                }
                return "Component system not initialized";
            }));
            
            // Add url function
            $this->twig->addFunction(new \Twig\TwigFunction('url', function($path = '') {
                $baseUrl = isset($_SERVER['BASE_URL']) ? $_SERVER['BASE_URL'] : '';
                return $baseUrl . '/' . ltrim($path, '/');
            }));
            
            // Add asset function
            $this->twig->addFunction(new \Twig\TwigFunction('asset', function($path) {
                $baseUrl = isset($_SERVER['BASE_URL']) ? $_SERVER['BASE_URL'] : '';
                return $baseUrl . '/static/' . ltrim($path, '/');
            }));
        }
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
 * Helper function to register a class path
 * 
 * @param string $path Path to classes directory
 * @return void
 */
function register_class_path($path) {
    if (!isset($GLOBALS['CLASS_PATHS'])) {
        $GLOBALS['CLASS_PATHS'] = [];
    }
    
    if (!in_array($path, $GLOBALS['CLASS_PATHS'])) {
        $GLOBALS['CLASS_PATHS'][] = $path;
    }
}

/**
 * Helper function to register a component path
 * 
 * @param string $path Path to components directory
 * @return void
 */
function register_component_path($path) {
    if (!isset($GLOBALS['COMPONENT_PATHS'])) {
        $GLOBALS['COMPONENT_PATHS'] = [];
    }
    
    if (!in_array($path, $GLOBALS['COMPONENT_PATHS'])) {
        $GLOBALS['COMPONENT_PATHS'][] = $path;
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
 * Autoload component by name
 * 
 * This function is used to autoload components by their name
 * 
 * @param string $component Component name
 */
function autoloadComponentByName($component) {
    $componentSystem = ComponentSystem::getInstance();
    
    if ($componentSystem) {
        // Try with component name directly
        $componentClassSimple = str_replace('/', '\\', $component);
        if (!class_exists($componentClassSimple)) {
            $componentSystem->autoloadComponent($componentClassSimple);
        }
    }
}

/**
 * Initialize the web framework
 * 
 * This function is called automatically when the framework is loaded
 */
function init_web_framework() {
    // Initialize Twig template engine
    $templateDir = DIR_APP . '/templates';
    $cacheDir = DIR_CACHE . '/twig';
    
    // Create template directory if it doesn't exist
    if (!file_exists($templateDir)) {
        mkdir($templateDir, 0755, true);
    }
    
    // Create cache directory if it doesn't exist
    if (!file_exists($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    // Initialize template engine
    $templateEngine = TemplateEngine::getInstance($templateDir, $cacheDir);
    
    // Initialize component system
    $componentsDir = DIR_ROOT . "/app/webroot/static/components";
    $componentSystem = ComponentSystem::getInstance();
    
    // Register component paths
    register_component_path($componentsDir);
    
    // Register components directory in class paths as well
    if (!isset($GLOBALS['CLASS_PATHS'])) {
        $GLOBALS['CLASS_PATHS'] = [];
    }
    if (!in_array($componentsDir, $GLOBALS['CLASS_PATHS'])) {
        $GLOBALS['CLASS_PATHS'][] = $componentsDir;
    }
    
    // If we have a components directory in the webroot, register it
    $webrootComponentsDir = DIR_WEBROOT . '/static/components';
    if (file_exists($webrootComponentsDir) && $webrootComponentsDir !== $componentsDir) {
        register_component_path($webrootComponentsDir);
        
        // Register in class paths as well
        if (!in_array($webrootComponentsDir, $GLOBALS['CLASS_PATHS'])) {
            $GLOBALS['CLASS_PATHS'][] = $webrootComponentsDir;
        }
    }
    
    // Add Twig functions for components only if Twig is available
    $twig = $templateEngine->getTwig();
    
    // Only add functions if Twig is properly initialized
    if ($twig !== null) {
        // Vérifier si les fonctions existent déjà avant de les ajouter
        $twigFunctions = [
            'component' => function($name, $data = [], $loadAssets = true) {
                return render_component($name, $data, $loadAssets);
            },
            'asset' => function($path) {
                return '/static/' . ltrim($path, '/');
            },
            'url' => function($path = '') {
                return '/' . ltrim($path, '/');
            }
        ];
        
        // Add functions to Twig
        foreach ($twigFunctions as $name => $function) {
            try {
                // Try to get the function to see if it exists
                $existingFunction = $twig->getFunction($name);
                // If no exception is thrown, the function exists
            } catch (\Twig\Error\RuntimeError $e) {
                // La fonction n'existe pas, on peut l'ajouter
                $twig->addFunction(new \Twig\TwigFunction($name, $function, ['is_safe' => ['html']]));
            } catch (\Exception $e) {
                // Catch any other exceptions
                error_log('Error adding Twig function: ' . $e->getMessage());
            }
        }
    }
    
    // Load component namespaces from configuration file if exists
    $componentNamespacesFile = DIR_APP . '/config/component_namespaces.php';
    if (file_exists($componentNamespacesFile)) {
        include $componentNamespacesFile;
    }
    
    // Auto-scan for components
    $components = scan_components();
    foreach ($components as $component) {
        // Try to preload component class with just the component name first (without suffix)
        $componentClassSimple = str_replace('/', '\\', $component);
        if (!class_exists($componentClassSimple)) {
            $componentSystem->autoloadComponent($componentClassSimple);
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

// Ensure CLASS_PATHS are properly initialized
if (!isset($GLOBALS['CLASS_PATHS'])) {
    $GLOBALS['CLASS_PATHS'] = [];
}

// Add essential paths if they don't exist
$essentialPaths = [
    DIR_APP.'/classes',
    DIR_WEBROOT.'/static/components',
];

foreach ($essentialPaths as $path) {
    if (!in_array($path, $GLOBALS['CLASS_PATHS'])) {
        $GLOBALS['CLASS_PATHS'][] = $path;
    }
}

// Scan for component directories and add them to CLASS_PATHS
if (is_dir(DIR_WEBROOT.'/static/components')) {
    $componentDirs = glob(DIR_WEBROOT.'/static/components/*', GLOB_ONLYDIR);
    foreach ($componentDirs as $componentDir) {
        if (!in_array($componentDir, $GLOBALS['CLASS_PATHS'])) {
            $GLOBALS['CLASS_PATHS'][] = $componentDir;
        }
    }
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
