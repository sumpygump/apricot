<?php
/**
 * Index command
 *
 * @package Glimpses
 * @subpackage Command
 */

namespace Glimpses\Command;

use Apricot\Console\Command\CommandAbstract;

/**
 * IndexCommand
 *
 * @uses CommandAbstract
 * @package Glimpses
 * @subpackage Command
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class IndexCommand extends CommandAbstract
{
    /**
     * Execute main logic for this command
     *
     * @param object $args Arguments object
     * @return void
     */
    public function execute($args)
    {
        $this->_terminal->set_fgcolor(2); // Change terminal color
        $this->_terminal->bold();
        echo "Apricot Command\n";
        echo "---------------\n";
        $this->_terminal->sgr0(); // Un bold
        echo "This is the apricot index command\n";
        $this->_terminal->op(); // Change to default color
        exit(0);
    }
}
