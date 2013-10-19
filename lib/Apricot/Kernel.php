<?php
/**
 * Apricot Kernel file
 * <pre>
 *     _               _           _
 *    / \   _ __  _ __(_) ___ ___ | |_
 *   / _ \ | '_ \| '__| |/ __/ _ \| __|
 *  / ___ \| |_) | |  | | (_| (_) | |_
 * /_/   \_\ .__/|_|  |_|\___\___/ \__|
 *         |_|
 * </pre>
 *
 * @package Apricot
 * @version $Id$
 */

namespace Apricot;

use Apricot\Extension\ExtensionInterface;
use Apricot\Extension\ExtensionAbstract;
use Apricot\Application\Router;
use Apricot\Model\Factory;

/**
 * Apricot Kernel
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Kernel
{
    /**
     * Config object
     * 
     * @var Apricot\Config
     */
    protected $_config = null;

    /**
     * The request object (controller, action, params)
     *
     * @var Apricot\Request
     */
    protected $_request = null;

    /**
     * The model cache
     *
     * @var array
     */
    protected $_models = array();

    /**
     * Whether application is a terminal
     *
     * @var bool
     */
    protected $_isTerm = false;

    /**
     * Storage for the Terminal object
     *
     * @var \Qi_Console_Terminal
     */
    protected $_terminal = null;

    /**
     * Storage for the session object
     * 
     * @var mixed
     */
    protected $_session = null;

    /**
     * Extensions
     * 
     * @var array
     */
    protected $_extensions = array();

    /**
     * Extension settings
     *
     * @var array
     */
    protected $_extensionSettings = array();

    /**
     * Router
     *
     * @var mixed
     */
    protected $_router;

    /**
     * Default settings for config
     *
     * @var array
     */
    protected static $_configDefaults = array(
        'namespace'           => 'Application',
        'app_root'            => APP_ROOT,
        'www_root'            => '',
        'session_namespace'   => 'Apricot',
        'default_controller'  => 'index',
        'controllers_dir'     => 'app/controllers/',
        'views_dir'           => 'app/views/',
        'models_dir'          => 'app/models/',
        'commands_dir'        => 'app/commands/',
        'log_dir'             => 'log/',
        'default_view_engine' => '\Apricot\View\Htmldoc',
        'reporting_level'     => E_ALL,
        'display_errors'      => '1',
        'output'              => 'http',
        'extensions'          => array(
            'logger' => array(
                'enabled' => true,
            ),
        ),
    );

    /**
     * Whether the application is allowed to exit
     *
     * @var bool
     */
    public static $exit = true;

    /**
     * Constructor
     *
     * @param Config $config Configuration object
     * @return void
     */
    public function __construct(Config $config = null)
    {
        $this->setConfig($config);

        if ($this->_config->extensions) {
            $this->prepareExtensionSettings($this->_config->extensions);
        }

        $this->extension = $this->makeKernelExtensionManager($this);

        $this->_router = new Router($this->_config);

        $factory = new Factory($this->getConfig('db'));
        $factory->setNamespace($this->getConfig('namespace'));
        $this->setModelFactory($factory);
    }

    /**
     * Set config object
     * 
     * @param Config $config Config object
     * @return void
     */
    public function setConfig(Config $config = null)
    {
        if (null == $config) {
            $config = self::makeConfig();
        } else {
            $config = $this->mergeConfigDefaults($config);
        }

        $this->_config = $config;
        $this->init();
    }

    /**
     * Get the config setting for a given key
     *
     * @param string $setting The desired key name
     * @return mixed
     */
    public function getConfig($setting = null)
    {
        if (null === $setting) {
            return $this->_config;
        } else {
            return $this->_config->get($setting);
        }
    }

    /**
     * Merge config defaults
     *
     * @param Config $config Config object
     * @return Config
     */
    public function mergeConfigDefaults(Config $config)
    {
        foreach (self::$_configDefaults as $name => $value) {
            if (null === $config->getDefault($name)) {
                $config->setDefault($name, $value);
            }
        }

        return $config;
    }

    /**
     * Initialize (after config is loaded)
     *
     * @return void
     */
    public function init()
    {
        // Set up terminal handling
        if ($this->getConfig('output') == 'term'
            || $this->getConfig('output') == 'console'
        ) {
            $this->_isTerm   = true;
            $this->_terminal = self::makeTerminal();
        } else {
            $this->_isTerm   = false;
            $this->_terminal = null;
        }

        // Set the error_reporting details
        $reportingLevel = $this->getConfig('reporting_level');
        if (null == $reportingLevel) {
            $reportingLevel = E_ALL | E_STRICT;
        }

        error_reporting($reportingLevel);

        // If display_errors config has been set, then set the display_errors 
        if ($this->getConfig('display_errors') !== null) {
            ini_set('display_errors', $this->getConfig('display_errors'));
        }
    }

    /**
     * Call up the Init object and run init() (bootstrap custom application logic)
     *
     * @param \Apricot\Controller $controller Controller
     * @return bool
     */
    public function initializeApplication($controller)
    {
        $initClassName = $this->getConfig('namespace') . '\\Init';

        if (!class_exists($initClassName)) {
            $this->log("Class $initClassName not found. Nothing to initialize.");
            return false;
        }

        $init = new $initClassName($this);
        $init->init($controller);
        return true;
    }

    /**
     * Make default config object
     * 
     * @return object Apricot_Config
     */
    public static function makeConfig()
    {
        return new Config(null, self::$_configDefaults);
    }

    /**
     * Set model factory
     *
     * @param \Apricot\Model\Factory $factory
     * @return \Apricot\Kernel
     */
    public function setModelFactory($factory)
    {
        $this->_modelFactory = $factory;
        return $this;
    }

    /**
     * Get model factory
     *
     * @return \Apricot\Model\Factory
     */
    public function getModelFactory()
    {
        return $this->_modelFactory;
    }

    /**
     * Make default terminal object
     * 
     * @return \Qi_Console_Terminal
     */
    public static function makeTerminal()
    {
        return new \Qi_Console_Terminal();
    }

    /**
     * Make a request object
     *
     * @param array $params Array of params to pass to Request
     * @return \Apricot\Request
     */
    public function makeRequest($params = array())
    {
        // TODO: Think about this: terminal doesn't even use request, why do i 
        // need a non http request?
        if ($this->_isTerm) {
            return new Request($params, $this->getConfig());
        }

        return new Http\Request($params, $this->getConfig());
    }

    /**
     * Make cache object
     * 
     * @return \Apricot\Cache
     */
    public function makeCache()
    {
        return new Cache($this);
    }

    /**
     * Make View object
     * 
     * @param mixed $options Optional options passed to constructor
     * @return object
     */
    public function makeView($options = null)
    {
        if ($this->getConfig('view_class')) {
            $viewClassName = $this->getConfig('view_class');
        } else {
            $viewClassName = '\Apricot\Application\ViewLayout';
        }

        $this->_loadClass($viewClassName);

        if (isset($options['engine'])) {
            $engine = $options['engine'];
        } else {
            $engine = $this->makeDefaultViewEngine(null, $options);
        }

        $view = new $viewClassName($this, $engine, $options);
        return $view;
    }

    /**
     * Make default view engine
     * 
     * @param string $engine The view engine object or class name
     * @param array $options Options to pass to view engine constructor
     * @return object
     */
    public function makeDefaultViewEngine($engine = null, $options = array())
    {
        if (null === $engine) {
            $engine = $this->getConfig('default_view_engine');
            if (trim($engine) == '') {
                $engine = '\Apricot\View\Htmldoc';
            }
        }

        // TODO: This doesn't account for namespaces
        if (!class_exists($engine)) {
            include_once 'lib' . DIRECTORY_SEPARATOR
                . str_replace('_', DIRECTORY_SEPARATOR, $engine)
                . '.php';
        }

        return new $engine($options);
    }

    /**
     * Make sesspool object
     * 
     * @param string $name Session namespace
     * @return \Qi_Sesspool
     */
    public function makeSesspool($name)
    {
        return new \Qi_Sesspool($name);
    }

    /**
     * Set the session handler object
     *
     * @param mixed $session The session handler object
     * @return \Apricot\Kernel
     */
    public function setSession($session)
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * Get session
     * 
     * @return object Session object
     */
    public function getSession()
    {
        // TODO: When in terminal you don't need a session
        if (null == $this->_session) {
            // Set the sesspool object (if not already set)
            $namespace = $this->getConfig('session_namespace');
            $this->setSession($this->makeSesspool($namespace));
        }

        return $this->_session;
    }

    /**
     * Dispatch request to a controller
     *
     * @param mixed $request The request object or array
     * @return mixed Response from controller::execute()
     */
    public function dispatch($request = null)
    {
        if (null === $request) {
            $request = $this->makeRequest();
        } else {
            $request = $this->_transformRequest($request);
        }

        $this->setRequest($request);
        $this->log("Dispatch: Request :~ " . print_r($request->__toString(), 1));

        $controllerClassName =
            $this->loadController($request->controller);
        $this->log("Dispatch: Loading controller '$controllerClassName'");

        $controller = new $controllerClassName($this, $request);
        $this->initializeApplication($controller);
        return $controller->execute();
    }

    /**
     * Set request object
     *
     * @param Request $request Request object
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->_request = $request;
        $this->_router->setRequest($request);
    }

    /**
     * Transform request to ensure is correct type
     *
     * @param mixed $request Request or params for request
     * @return void
     */
    protected function _transformRequest($request)
    {
        if ($request instanceof \Apricot\Request) {
            return $request;
        }

        if (is_object($request)) {
            $request = (array) $request;
        }

        return $this->makeRequest($request);
    }

    /**
     * Convenience to log message with the Logger extension
     * 
     * @param mixed $input Message to log
     * @param string $logFile Log file name
     * @return bool Whether the message was logged
     */
    public function log($input, $logFile = null)
    {
        $logger = $this->extension->getExtension('logger');

        if (!$logger) {
            error_log($input);
        }

        return $logger->log($input, $logFile);
    }

    /**
     * Get the Apricot request
     *
     * @param bool $refresh Whether to force a reparse on the request
     * @return object Request
     */
    public function getRequest($refresh = false)
    {
        if (!isset($this->_request)) {
            // If it hasn't been set yet, return a new blank request
            return $this->makeRequest();
        }

        if ($refresh == true) {
            $this->_request->refresh();
        }

        return $this->_request;
    }

    /**
     * Get Router
     *
     * @return Apricot\Application\Router
     */
    public function getRouter()
    {
        return $this->_router;
    }

    /**
     * Return whether this request is from a terminal
     *
     * @return bool
     */
    public function isTerminal()
    {
        return $this->_isTerm;
    }

    /**
     * Get the Terminal object
     *
     * @return object \Qi_Console_Terminal The terminal object
     */
    public function getTerminal()
    {
        return $this->_terminal;
    }

    /**
     * Get a repository
     *
     * @param mixed $name
     * @param mixed $connectionConfig
     * @param bool $cache
     * @return void
     */
    public function getRepository($name, $connectionConfig = null, $cache = true)
    {
        return $this->getModelFactory()->getRepository($name, $connectionConfig, $cache);
    }

    /**
     * This is an alias to getRepository
     *
     * This method is deprecated
     *
     * @param string $model The name of the model to get
     * @param array $dbCfg Array of alternate db config settings
     * @param bool $cache Use the cache
     * @return object Apricot_Model The model
     */
    public function getModel($model, $dbCfg = null, $cache = true)
    {
        return $this->getRepository($model, $dbCfg, $cache);
    }

    /**
     * load_controller
     *
     * @param mixed $name The name of the controller
     * @return void
     */
    public function loadController($name)
    {
        $namespace = $this->getConfig('namespace') . '\\Controller\\';

        $controllerClassName = ucfirst($name) . "Controller";

        if (class_exists($namespace . $controllerClassName)) {
            return $namespace . $controllerClassName;
        }

        $controllersDir = $this->ensureEndingDirectorySeparator(
            $this->getConfig('controllers_dir')
        );

        $controllerFilePath = $this->getConfig('app_root')
            . DIRECTORY_SEPARATOR
            . $controllersDir
            . $controllerClassName . '.php';

        if (!file_exists($controllerFilePath)) {
            // Controller does not exist
            throw new KernelException(
                "Cannot load controller '$name'.",
                KernelException::ERROR_LOAD_CONTROLLER
            );
        }

        include_once $controllerFilePath;

        $fullClassName = $namespace . $controllerClassName;
        if (!class_exists($fullClassName)) {
            throw new KernelException(
                "Cannot load controller '$fullClassName' at path '$controllerFilePath'",
                KernelException::ERROR_LOAD_CONTROLLER
            );
        }

        return $fullClassName;
    }

    /**
     * Load Command
     *
     * @param mixed $name The name of the controller
     * @return void
     */
    public function loadCommand($name)
    {
        $namespace = $this->getConfig('namespace') . '\\Command\\';

        $commandClassName = ucfirst($name) . "Command";

        if (class_exists($namespace . $commandClassName)) {
            return $namespace . $commandClassName;
        }

        $commandDir = $this->ensureEndingDirectorySeparator(
            $this->getConfig('commands_dir')
        );

        $commandFilePath = $this->getConfig('app_root')
            . DIRECTORY_SEPARATOR
            . $commandDir
            . $commandClassName . '.php';

        if (!file_exists($commandFilePath)) {
            // Command does not exist
            throw new KernelException(
                "Cannot load command '$name'.",
                KernelException::ERROR_LOAD_CONTROLLER
            );
        }

        include_once $commandFilePath;

        $fullClassName = $namespace . $commandClassName;
        if (!class_exists($fullClassName)) {
            throw new KernelException(
                "Cannot load command '$fullClassName' at path '$commandFilePath'",
                KernelException::ERROR_LOAD_CONTROLLER
            );
        }

        return $fullClassName;
    }

    /**
     * Attempt to load a class
     *
     * @param mixed $classname The name of the class to load
     * @return void
     */
    private function _loadClass($classname)
    {
        //TODO: Use an autoloader instead
        if (class_exists($classname)) {
            return true;
        }

        $classname_section = strtolower(substr($classname, -4));

        switch ($classname_section) {
        case "view":
            $path = $this->getConfig('views_dir');
            break;
        case "mode": // model
        case "odel":
            $path = $this->getConfig('models_dir');
            break;
        default:
            break;
        }

        $file = $this->getConfig('app_root') . DIRECTORY_SEPARATOR
            . $path . str_replace("_", ".", $classname) . ".php";

        if (!file_exists($file)) {
            throw new KernelException(
                "Cannot load class. File '$file' doesn't exist.",
                KernelException::ERROR_CLASS_NOT_FOUND
            );
        }

        include_once $file;
    }

    /**
     * Prepare extension managers for any extensions that need settings from 
     * the config object
     *
     * @param mixed $extensionsOptions
     * @return void
     */
    public function prepareExtensionSettings($extensionsOptions)
    {
        $extensionSettings = array();

        foreach ($extensionsOptions as $name => $options) {
            $type = (isset($options->type)) ? ucfirst($options->type) : "Kernel";

            if (!isset($extensionSettings[$type])) {
                $extensionSettings[$type] = array();
            }

            $extensionSettings[$type][$name] = $options;
        }

        $this->_extensionSettings = $extensionSettings;
    }

    /**
     * Make Kernel Extension Manager
     *
     * @return \Apricot\Kernel\ExtensionManager
     */
    public function makeKernelExtensionManager()
    {
        $namespace = 'Apricot\\Kernel\\Extension';
        $path      = 'Apricot' . DIRECTORY_SEPARATOR . 'Kernel'
            . DIRECTORY_SEPARATOR . 'Extension' . DIRECTORY_SEPARATOR;

        $manager = new Kernel\ExtensionManager($namespace, $path);
        $manager->setKernel($this);

        $settings = isset($this->_extensionSettings['Kernel']) ? $this->_extensionSettings['Kernel'] : array();
        $manager->importExtensions($settings);

        return $manager;
    }

    /**
     * Make Controller Extension Manager
     *
     * @param \Apricot\Controller $controller Controller object
     * @return \Apricot\Controller\ExtensionManager
     */
    public function makeControllerExtensionManager($controller)
    {
        $namespace = ucfirst($this->getConfig('namespace')) . '\\Controller\\Extension';
        $path      = $this->getConfig('controllers_dir') . 'extensions' . DIRECTORY_SEPARATOR;

        $manager = new Controller\ExtensionManager($namespace, $path);
        $manager->setController($controller);

        $settings = isset($this->_extensionSettings['Controller']) ? $this->_extensionSettings['Controller'] : array();
        $manager->importExtensions($settings);

        return $manager;
    }

    /**
     * Make View Extension Manager
     *
     * @param \Apricot\View $view View object
     * @return \Apricot\View\ExtensionManager
     */
    public function makeViewExtensionManager($view)
    {
        $namespace = ucfirst($this->getConfig('namespace')) . '\\View\\Extension';
        $path      = $this->getConfig('views_dir') . 'extensions' . DIRECTORY_SEPARATOR;

        $manager = new View\ExtensionManager($namespace, $path);
        $manager->setView($view);

        $settings = isset($this->_extensionSettings['View']) ? $this->_extensionSettings['View'] : array();
        $manager->importExtensions($settings);

        return $manager;
    }

    /**
     * Get a kernel extension by name
     * 
     * @param string $name Name of extension key
     * @return object|null
     */
    public function getExtension($name)
    {
        return $this->extension->getExtension($name);
    }

    /**
     * Magic call is passed to any extensions (if exist)
     * 
     * @param string $method Method invoked
     * @param array $args Arguments
     * @return mixed
     */
    public function __call($method, $args)
    {
        $extension = $this->getExtension($method);

        return call_user_func_array(array($extension, 'direct'), $args);
    }

    /**
     * Assign view to modules
     * 
     * @param Apricot_View $view View
     * @return void
     */
    public function assignViewToExtensions($view)
    {
        /*foreach ($this->_extensions as $extension) {
            if ($extension->getType() == AbstractExtension::TYPE_VIEW) {
                $extension->view = $view;
            }
        }*/
    }

    /**
     * Call the preAssemble on the view modules right before the view is 
     * assembled.
     *
     * @return void
     */
    public function preAssembleViewModules()
    {
        /*foreach ($this->_extensions as $extension) {
            if ($extension->getType() == AbstractExtension::TYPE_VIEW) {
                $extension->preAssemble();
            }
        }*/
    }

    /**
     * Ensure that a string ends in a directory separator ('/')
     *
     * @param string $string String to check
     * @return string
     */
    public function ensureEndingDirectorySeparator($string)
    {
        $string = rtrim($string, DIRECTORY_SEPARATOR);

        return $string . DIRECTORY_SEPARATOR;
    }

    /**
     * Set the exit state
     *
     * @param bool $value Exit value
     * @return Apricot\Kernel
     */
    public function setExit($value)
    {
        self::$exit = (bool) $value;
        return $this;
    }

    /**
     * Get exit state
     * 
     * @return bool
     */
    public function getExit()
    {
        return self::$exit;
    }

    /**
     * Exit application
     * 
     * @return mixed
     */
    public function exitApplication()
    {
        if ($this->getExit()) {
            exit(0);
        }

        return false;
    }
}

/**
 * Apricot_KernelException
 *
 * @uses Exception
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class KernelException extends \Exception
{
    const ERROR_LOAD                  = 1;
    const ERROR_CONTROLLER            = 2;
    const ERROR_VIEW                  = 4;
    const ERROR_INDEX                 = 8;
    const ERROR_MASTER                = 16;
    const ERROR_MODEL                 = 32;
    const ERROR_LOAD_CONTROLLER       = 3;
    const ERROR_LOAD_VIEW             = 5;
    const ERROR_LOAD_CONTROLLER_INDEX = 11;
    const ERROR_LOAD_VIEW_INDEX       = 13;
    const ERROR_LOAD_VIEW_MASTER      = 21;
    const ERROR_LOAD_MODEL            = 33;
    const ERROR_CLASS_NOT_FOUND       = 64;
}
