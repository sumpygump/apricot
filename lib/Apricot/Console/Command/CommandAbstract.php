<?php
/**
 * Abstract Task class file
 *
 * @package Apricot
 * @subpackage Tests
 */

namespace Apricot\Console\Command;

use \Apricot\Kernel;

/**
 * Task Abstract
 *
 * @package Apricot
 * @subpackage Tests
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class CommandAbstract extends \Qi_Console_Client
{
    /**
     * Apricot Kernel object
     *
     * @var \Apricot\Kernel
     */
    protected $_kernel;

    /**
     * Terminal object
     *
     * @var Qi_Console_Terminal
     */
    protected $_terminal;

    /**
     * Construct
     *
     * @param Kernel $kernel Kernel object
     * @return void
     */
    public function __construct(Kernel $kernel)
    {
        $this->_kernel   = $kernel;
        $this->_terminal = $this->_kernel->getTerminal();
    }

    /**
     * Execute this task
     *
     * @param mixed $args
     * @return void
     */
    public function execute($args)
    {
    }
}
