<?php
/**
 * Params class file
 *
 * @package Apricot
 * @version $Id$
 */

namespace Apricot;

/**
 * Params
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Params implements \Iterator
{
    /**
     * Data storage for the params
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Configuration options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Constructor
     *
     * @param array $data A list of parameter defaults to set
     * @param array $options Array of options
     * @return void
     */
    public function __construct($data = array(), $options = array())
    {
        $this->populate($data);
        $this->_options = $options;
    }

    /**
     * Rewind to beginning of data array
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->_data);
    }

    /**
     * Get the current data item
     *
     * @return object
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * Get the key of the current data item
     *
     * @return int
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Go to the next data item
     *
     * @return void
     */
    public function next()
    {
        return next($this->_data);
    }

    /**
     * Is the current data item valid?
     *
     * @return bool
     */
    public function valid()
    {
        return key($this->_data) !== null;
    }

    /**
     * Return whether the object any data loaded
     *
     * @return bool
     */
    public function hasData()
    {
        return (count($this->_data) > 0);
    }

    /**
     * Get the value for a given parameter
     *
     * @param string $var The name of the parameter to get
     * @return mixed The value of the parameter
     */
    public function __get($var)
    {
        return $this->get($var);
    }

    /**
     * Set a parameter
     *
     * @param string $name The name of the variable to set
     * @param mixed $value The value of the variable
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * String representation of current params
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Set a parameter
     * 
     * @param string $name Name of key
     * @param mixed $value Value to store
     * @return void
     */
    public function set($name, $value = null)
    {
        if (null === $name) {
            return false;
        }

        if (!is_string($name) && !is_int($name)) {
            throw new ParamsException("Key must be integer or string");
        }

        if ($value == null && strpos($name, '=')) {
            list($name, $value) = explode('=', $name);
        }

        $this->_data[$name] = $value;
    }

    /**
     * Get a parameter
     *
     * If the parameter has not been set, it will return the default.
     *
     * @param mixed $name The name of the parameter
     * @param mixed $default The default parameter to return
     * @return mixed The value of the parameter
     */
    public function get($name, $default = null)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        } else {
            return $default;
        }
    }

    /**
     * Return an array of the data loaded
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this->_data;
    }

    /**
     * Return a StdClass object of the data
     *
     * @return object StdClass
     */
    public function toSimpleObject()
    {
        return (object) $this->_data;
    }

    /**
     * Set default parameters and/or parameter values
     *
     * The input is either a name value array, or
     * an array of strings with the format "variable=value"
     *
     * @param array $data The parameter list
     * @return void
     */
    public function populate($data = array())
    {
        if (!is_array($data) && !is_object($data)) {
            $data = array($data);
        }

        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $this->set($value);
            } else {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Run a "data cleaning" function on the contents of the fetched data
     *
     * Can have multiple parameters passed in that will
     * go to the cleaning function.
     * Examples ($this->sanitize('trim');
     *     $this->sanitize('trim',"\n\t");
     *     $this->sanitize('strip_tags');
     *
     * @return void
     */
    public function sanitize()
    {
        // Get the params passed into this function
        $args = func_get_args();

        // Shift off the first param,
        // that will be the sanitation function to use.
        $filter = array_shift($args);

        switch ($filter) {
        case 'trim':
            if (!isset($args[0]) || null == $args[0]) {
                $args[0] = " \t\n\r\0\x0B";
            }
            // pass thru
        case 'strip_tags':
            if (!isset($args[0])) {
                $args[0] = null;
            }
            foreach ($this->_data as &$data) {
                if (!is_array($data)) {
                    $data = $filter($data, $args[0]);
                }
            }
            break;
        case 'preg_replace':
            if (!isset($args[0]) || !isset($args[1])) {
                // with no arguments, we can't do anything
                trigger_error(
                    "No arguments provided for sanitize preg_replace.",
                    E_USER_ERROR
                );
            }
            foreach ($this->_data as &$data) {
                if (!is_array($data)) {
                    $data = $filter($args[0], $args[1], $data);
                }
            }
            break;
        default:
            foreach ($this->_data as &$data) {
                if (function_exists($filter) && !is_array($data)) {
                    $data = $filter($data);
                }
            }
            break;
        }
    }

    /**
     * Get this object's configuration options
     * 
     * @return mixed
     */
    public function getOptions()
    {
        return $this->_options;
    }
}

/**
 * Apricot_ParamsException
 *
 * @uses Exception
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class ParamsException extends \Exception
{
}
