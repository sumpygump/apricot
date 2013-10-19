<?php
/**
 * Apricot Environment class file
 *
 * @package Apricot
 * @version $Id$
 */

namespace Apricot;

/**
 * Apricot Environment
 * 
 * @uses Apricot_Params
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Environment extends Params
{
    /**
     * Constructor
     * 
     * @param array $data Data to initialize with
     * @param array $options Options for configuration
     * @return void
     */
    public function __construct($data = null, $options = array())
    {
        if (null === $data) {
            $this->_populateFromServer();
        }
        parent::__construct($data, $options);
    }

    /**
     * Populate environment vars from server vars array
     * 
     * @return void
     */
    protected function _populateFromServer()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) != 'HTTP_') {
                $this->set($name, $value);
            }
        }
        ksort($this->_data);
    }
}
