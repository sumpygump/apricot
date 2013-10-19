<?php
/**
 * Apricot Request class file
 *
 * @package Apricot
 * @version $Id$
 */

namespace Apricot;

use Apricot\Params;

/**
 * Apricot Request class
 * 
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Request extends Params
{
    /**
     * Storage for environment variables
     *
     * @var object 
     */
    protected $_environment = null;

    /**
     * Data storage
     * 
     * @var array
     */
    protected $_data = array();

    /**
     * Default storage items
     * 
     * @var array
     */
    protected $_defaults = array(
        'controller' => 'index',
        'action'     => 'index',
    );

    /**
     * Storage of config object
     *
     * @var Apricot_Config
     */
    protected $_config = null;

    /**
     * Whether this request is autopopulated
     * 
     * @var mixed
     */
    protected $_autoPopulate = false;

    /**
     * Constructor
     * 
     * @param mixed $params Params to load
     * @param object $config Config object
     * @return void
     */
    public function __construct($params = null, $config = null)
    {
        if (null !== $config) {
            $this->setConfig($config);
        }

        $this->_populateDefaults();

        $this->populate($params);

        $this->_environment = new Environment();
    }

    /**
     * Set the config object
     * 
     * @param mixed $config Apricot config object
     * @return void
     */
    public function setConfig($config)
    {
        $this->_config = $config;
    }

    /**
     * Refresh request
     *
     * To be extended in subclass
     * 
     * @return void
     */
    public function refresh()
    {
    }

    /**
     * Load default params required by Request
     * 
     * @return void
     */
    protected function _populateDefaults()
    {
        $this->populate($this->_defaults);
    }

    /**
     * Set a data item
     * 
     * @param string $name Param name
     * @param mixed $value Param value
     * @return void
     */
    public function set($name, $value = null)
    {
        // Enforce controller or action to be strings
        if ($name == 'controller' || $name == 'action') {
            if (is_array($value)) {
                // If an array, return the first value
                $value = reset($value);
                trigger_error(
                    "Apricot\\Request: Setting key '$name' to '$value', "
                    . "since input was an array. Key should be a string.",
                    E_USER_NOTICE
                );
            }

            if (is_object($value)) {
                $r = new \ReflectionClass($value);
                if (!$r->hasMethod('__toString')) {
                    throw new RequestException(
                        "Apricot\\Request: Cannot set key $name to object "
                        . "without __toString() method."
                    );
                }
            }

            $value = (string) $value;
        }

        parent::set($name, $value);
    }

    /**
     * Get environment object
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * Get an environment param
     * 
     * @param string $param Name of param to get
     * @return mixed
     */
    public function getEnvironmentParam($param)
    {
        $param = strtoupper(trim($param));
        return $this->_environment->get($param);
    }
}

/**
 * Apricot_RequestException
 *
 * @uses Exception
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class RequestException extends \Exception
{
}
