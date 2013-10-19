<?php
/**
 * Apricot Controller Extension Manager class file
 *
 * @package Apricot
 */

namespace Apricot\Controller;

/**
 * ExtensionManager
 *
 * @uses \Apricot\Extension\Manager
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class ExtensionManager extends \Apricot\Extension\Manager
{
    /**
     * Controller object
     *
     * @var \Apricot\Controller
     */
    protected $_controller;

    /**
     * Set the controller object
     *
     * @param \Apricot\Controller $controller Controller object
     * @return \Apricot\View\ExtensionManager
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
        return $this;
    }

    /**
     * Get the controller object
     *
     * @return \Apricot\Controller
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * Make an extension class
     *
     * Allow for different options passed into the constructors for different 
     * extension managers
     *
     * @param string $className Name of class
     * @param mixed $options Options for constructor
     * @return \Apricot\Extension\ExtensionInterface
     */
    public function makeExtension($className, $options = array())
    {
        return new $className($this->getController(), $options);
    }
}
