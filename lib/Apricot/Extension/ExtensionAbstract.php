<?php
/**
 * Extension Abstract class file
 *  
 * @package Apricot\Module
 */

namespace Apricot\Extension;

/**
 * Apricot Module Abstract
 * 
 * @uses Apricot_Module_ModuleInterface
 * @package Apricot\Module
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
abstract class ExtensionAbstract implements ExtensionInterface
{
    /**
     * Constructor
     * 
     * @param object $object A Kernel, Controller or View object
     * @param array $options Options to use for module
     * @return void
     */
    public function __construct($object, $options = array())
    {
        // Child classes should handle custom object $object
        // It will be either a kernel or controller or view object
        
        $this->setOptions($options);
        $this->handleOptions($this->_options);
    }

    /**
     * Set Options
     *
     * @param mixed $options Options for this extension
     * @return \Apricot\Extension\ExtensionAbstract
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        return $this;
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
     * Called when the module is loaded in the kernel
     * 
     * @return void
     */
    public function register()
    {
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
}
