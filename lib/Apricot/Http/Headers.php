<?php
/**
 * Apricot Headers class file
 *
 * @package Apricot
 * @version $Id$
 */

namespace Apricot\Http;

use Apricot\Params;

/**
 * Apricot Headers Storage
 * 
 * @uses Apricot_Params
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Headers extends Params
{
    /**
     * Constructor
     * 
     * @param array $data Data to initially populate
     * @param array $options Configuration options
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
            if (substr($name, 0, 5) == 'HTTP_') {
                $headerName = substr($name, 5);
                $this->set($headerName, $value);
                $this->set(strtolower($headerName), $value);
            }
        }

        ksort($this->_data);
    }
}
