<?php
/**
 * Service
 *
 * @package Apricot
 */

namespace Apricot\Model;

use Apricot\Kernel;

/**
 * Service
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Service
{
    /**
     * Constructor
     *
     * @param Kernel $kernel
     * @return void
     */
    public function __construct(Kernel $kernel)
    {
        $this->setKernel($kernel);
    }

    /**
     * Set kernel
     *
     * @param \Apricot\Kernel $kernel
     * @return \Apricot\Model\Service
     */
    public function setKernel($kernel)
    {
        $this->_kernel($kernel);
        return $this;
    }

    /**
     * Get kernel object
     *
     * @return \Apricot\Kernel
     */
    public function getKernel()
    {
        return $this->_kernel();
    }

    /**
     * Get or set the kernel
     *
     * It is stored staticly in this method
     *
     * @param \Apricot\Kernel $set Kernel object
     * @return void
     */
    protected function _kernel($set = null)
    {
        static $kernel;

        if (null === $set) {
            return $kernel;
        }

        $kernel = $set;
    }
}
