<?php
/**
 * Controller Extension class file
 *  
 * @package Apricot\Module
 */

namespace Apricot\Controller;

use Apricot\Extension\ExtensionAbstract;

/**
 * Controller Extension
 * 
 * @uses Apricot\Extension\ExtensionInterface
 * @package Apricot\Extension
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Extension extends ExtensionAbstract
{
    /**
     * Controller object
     *
     * @var \Apricot\Controller
     */
    protected $_controller;

    /**
     * Constructor
     * 
     * @param \Apricot\Controller $controller Controller object
     * @param array $options Options to use for module
     * @return void
     */
    public function __construct($controller, $options = array())
    {
        $this->setController($controller);

        $this->setOptions($options);
        $this->handleOptions($this->_options);
    }

    /**
     * Set controller
     *
     * @param \Apricot\Controller $controller Controller object
     * @return \Apricot\Controller\Extension
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
        return $this;
    }

    /**
     * Get controller
     *
     * @return \Apricot\Controller
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * Handle options
     * 
     * @param array $options Options passed in constructor
     * @return void
     */
    public function handleOptions($options)
    {
        if (empty($options)) {
            return false;
        }
    }

    /**
     * Direct call method
     * 
     * @param array $options Options
     * @return mixed
     */
    public function direct($options = null)
    {
    }

    /**
     * Register this extension
     *
     * This is called when the extension is added to the list
     *
     * @return void
     */
    public function register()
    {
    }
}
