<?php
/**
 * Apricot Module Interface file
 *
 * @package Apricot\Module
 */

namespace Apricot\Extension;

/**
 * Apricot Module Interface
 * 
 * @package Apricot\Module
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
interface ExtensionInterface
{
    /**
     * Called when the module is loaded in the kernel
     * 
     * @return void
     */
    public function register();

    /**
     * Direct call method
     * 
     * @param array $options Options
     * @return mixed
     */
    public function direct($options = null);
}
