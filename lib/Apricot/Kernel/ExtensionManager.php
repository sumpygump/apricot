<?php
/**
 * Apricot Kernel Extension Manager class file
 *
 * @package Apricot
 */

namespace Apricot\Kernel;

use \Apricot\Extension\ExtensionInterface;
use \Apricot\KernelException;

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
     * Kernel object
     *
     * @var \Apricot\Kernel
     */
    protected $_kernel;

    /**
     * Set Kernel object
     *
     * @param \Apricot\Kernel $kernel
     * @return void
     */
    public function setKernel(\Apricot\Kernel $kernel)
    {
        $this->_kernel = $kernel;
        return $this;
    }

    /**
     * Get kenrel
     *
     * @return \Apricot\Kernel
     */
    public function getKernel()
    {
        return $this->_kernel;
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
        return new $className($this->getKernel(), $options);
    }
}
