<?php
/**
 * Apricot View Extension Manager class file
 *
 * @package Apricot
 */

namespace Apricot\View;

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
     * View object
     *
     * @var \Apricot\View
     */
    protected $_view;

    /**
     * Set the view object
     *
     * @param \Apricot\View $view View object
     * @return \Apricot\View\ExtensionManager
     */
    public function setView($view)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Get the view object
     *
     * @return \Apricot\View
     */
    public function getView()
    {
        return $this->_view;
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
        return new $className($this->getView(), $options);
    }
}
