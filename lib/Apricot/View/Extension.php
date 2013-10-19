<?php
/**
 * View Extension class file
 *  
 * @package Apricot\Module
 */

namespace Apricot\View;

use Apricot\Extension\ExtensionAbstract;

/**
 * View Extension
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
     * View object
     *
     * @var \Apricot\View
     */
    protected $_view;

    /**
     * Constructor
     * 
     * @param \Apricot\View $view View object
     * @param array $options Options to use for module
     * @return void
     */
    public function __construct($view, $options = array())
    {
        $this->setView($view);

        $this->setOptions($options);
        $this->handleOptions($this->_options);
    }

    /**
     * Set view
     *
     * @param \Apricot\View $view View object
     * @return \Apricot\View\Extension
     */
    public function setView($view)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Get view
     *
     * @return \Apricot\View
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Called right before the view is assembled
     *
     * @return void
     */
    public function preAssemble()
    {
    }
}
