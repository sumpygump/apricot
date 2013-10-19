<?php
/**
 * Apricot Application Init class file
 *
 * @package Apricot
 */

namespace Apricot\Application;

/**
 * Init
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class Init
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
     * @param \Apricot\Kernel $kernel
     * @return void
     */
    public function __construct($kernel)
    {
        $this->setKernel($kernel);
    }

    /**
     * Set kernel
     *
     * @param \Apricot\Kernel $kernel Kernel object
     * @return Init
     */
    public function setKernel($kernel)
    {
        $this->_kernel = $kernel;
        return $this;
    }

    /**
     * Get kernel
     *
     * @return \Apricot\Kernel
     */
    public function getKernel()
    {
        return $this->_kernel;
    }
}
