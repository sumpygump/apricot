<?php
/**
 * Kernel Extension class file
 *  
 * @package Apricot\Kernel
 */

namespace Apricot\Kernel;

use Apricot\Extension\ExtensionAbstract;

/**
 * Controller Extension
 * 
 * @uses Apricot\Extension\ExtensionInterface
 * @package Apricot\Kernel
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Extension extends ExtensionAbstract
{
    /**
     * Kernel object
     *
     * @var \Apricot\Kernel
     */
    protected $_kernel;

    /**
     * Constructor
     * 
     * @param \Apricot\Kernel $kernel Kernel object
     * @param array $options Options to use for module
     * @return void
     */
    public function __construct(\Apricot\Kernel $kernel, $options = array())
    {
        $this->_kernel = $kernel;

        $this->setOptions($options);
        $this->handleOptions($this->_options);
    }

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
}
