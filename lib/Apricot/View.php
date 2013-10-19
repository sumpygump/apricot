<?php
/**
 * Apricot View file
 *
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
 * @subpackage View
 * @version $Id: View.php 1734 2010-03-16 02:49:55Z jansen $
 */

namespace Apricot;

/**
 * Apricot_View
 *
 * @package Apricot
 * @subpackage View
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 * @abstract
 */
abstract class View
{
    /**
     * The data for this object
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Request defaults
     *
     * @var string 
     */
    protected $_requestDefaults = array();

    /**
     * The view engine
     *
     * @var mixed
     */
    protected $_engine = null;

    /**
     * Storage for Apricot Kernel
     * 
     * @var mixed
     */
    protected $_kernel = null;

    /**
     * Whether the action should be rendered
     * 
     * @var bool
     */
    public $shouldRenderAction = true;

    /**
     * Extension manager
     *
     * @var \Apricot\View\ExtensionManager
     */
    public $extension;

    /**
     * Constructor
     *
     * @param object $kernel Apricot Kernel object
     * @param mixed $engine View Engine
     * @param array $options Array of options
     * @return void
     */
    public function __construct($kernel, $engine = null, $options = array())
    {
        $this->setKernel($kernel);
        if (null != $engine) {
            $this->setEngine($engine);
        }

        $this->setOption(
            'default_title', $this->_kernel->getConfig('default_title')
        );
        
        $this->_parseOptions($options);

        $this->extension = $this->getKernel()->makeViewExtensionManager($this);

        $this->init();
    }

    /**
     * Initialization (called right after constructor)
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Pre assemble
     * 
     * @return void
     */
    public function preAssemble()
    {
    }

    /**
     * Assemble this view
     *
     * @param string $content The main content of the page
     * @return void
     */
    public function assemble($content)
    {
    }

    /**
     * Set Kernel object
     * 
     * @param object $kernel Apricot Kernel object
     * @return void
     */
    public function setKernel($kernel)
    {
        $this->_kernel = $kernel;
    }

    /**
     * Get Kernel object
     * 
     * @return object Apricot_Kernel
     */
    public function getKernel()
    {
        return $this->_kernel;
    }

    /**
     * Set the view engine
     * 
     * @param mixed $engine View engine object
     * @param array $args Args to send to contructor for view engine
     * @return void
     */
    public function setEngine($engine, $args = array())
    {
        if (is_object($engine)) {
            $this->_engine = $engine;
        } else {
            if (!class_exists($engine)) {
                throw new KernelException(
                    "Class '$engine' not found.",
                    KernelException::ERROR_CLASS_NOT_FOUND
                );
            }
            $this->_engine = new $engine($args);
        }
    }

    /**
     * Get the view engine
     * 
     * @return mixed
     */
    public function getEngine()
    {
        return $this->_engine;
    }

    /**
     * Get View Extension
     *
     * @param string $name Name of extension
     * @return \Apricot\Extension
     */
    public function getExtension($name)
    {
        return $this->extension->getExtension($name);
    }

    /**
     * Call (pass to engine)
     * 
     * @param string $method Method name
     * @param array $args Arguments
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (null == $this->_engine) {
            $this->getKernel()->log("Calling method '$method' on view with no engine. Args:" . print_r($args, 1));
            return false;
        }

        return call_user_func_array(array($this->_engine, $method), $args);
    }

    /**
     * Parse the options string
     *
     * @param array $options Array of options
     * @return void
     */
    private function _parseOptions($options)
    {
        if ($options == null) {
            return;
        }

        foreach ($options as $key => $option) {
            switch($key) {
            case 'action':
                if ($option) {
                    $this->_requestDefaults['action'] = $option;
                }
                break;
            default:
                $this->setOption($key, $option);
                break;
            }
        }
    }

    /**
     * Get the default action for this view
     *
     * @return string
     */
    protected function _getDefaultAction()
    {
        if (isset($this->_requestDefaults['action'])) {
            return $this->_requestDefaults['action'];
        } else {
            return 'index';
        }
    }

    /**
     * Render a partial
     * 
     * @param string $filename Name of partial file to render
     * @param array $vars An array of values to add to current scope
     * @return string
     */
    public function renderPartial($filename, $vars = array())
    {
        $filename = $this->_getViewTemplatesDir()
            . DIRECTORY_SEPARATOR . $filename;

        if (!is_file($filename)) {
            return false;
        }

        foreach ($vars as $key => $value) {
            $this->_data[$key] = $value;
        }

        // Capture the content
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();

        return $this->transformContents($contents);
    }

    /**
     * Transform contents
     *
     * This method should be overridden to provide additional functionality
     * by replacing parts to the html. This is called right after including
     * the contents from renderPartial()
     * 
     * @param string $input A string of html
     * @return string A string of html
     */
    public function transformContents($input)
    {
        return $input;
    }

    /**
     * Get the view templates directory
     * 
     * @return string
     */
    protected function _getViewTemplatesDir()
    {
        $appRoot  = $this->_kernel->getConfig('app_root');
        $viewsDir = $this->_kernel->getConfig('views_dir');

        $templateDir = $appRoot . DIRECTORY_SEPARATOR
            . $viewsDir . 'templates';

        return $templateDir;
    }

    /**
     * set_param
     *
     * @param mixed $name The name of the parameter
     * @param mixed $value The value to set the parameter to
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * get_param
     *
     * @param mixed $name The name of the parameter
     * @return mixed
     */
    public function getParam($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        return null;
    }

    /**
     * __set
     *
     * @param string $name The name of the parameter to set
     * @param mixed $value The value to which to set the parameter
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setParam($name, $value);
    }

    /**
     * __get
     *
     * @param string $name The name of the parameter
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getParam($name);
    }
}
