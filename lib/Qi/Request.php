<?php
/**
 * Qi Request class file
 *
 * <pre>
 *  ____                            _
 * |  _ \ ___  __ _ _   _  ___  ___| |_
 * | |_) / _ \/ _` | | | |/ _ \/ __| __|
 * |  _ <  __/ (_| | |_| |  __/\__ \ |_
 * |_| \_\___|\__, |\__,_|\___||___/\__|
 *               |_|
 * Request : Get superglobals and transform into objects
 *
 * CHANGELOG:
 * ==========
 * 2008-11-19: Added to_array() method.
 * 2009-02-03: Added option to choose whether to trim values on fetch
 * 2009-04-28: Added option to retrieve no data from superglobal
 * 2009-08-15: Added default parameter to getParameter()
 * 2009-09-23: Added ability to pass a simple method to sanitize()
 * 2009-12-08: Added to_simple_object() method.
 * 2010-01-23: Added get_type() method
 * </pre>
 * @package Qi
 * @version $Id$
 */

/**
 * Request
 *
 * todo Add a parse_options method and better documentation for options
 *
 * @package Qi
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version 0.9.7
 */
class Qi_Request implements Iterator
{
    /**
     * The data for the current request object
     *
     * @var array
     */
    protected $_data;

    /**
     * The current request type (GET|POST|FILES|COOKIE|SERVER)
     *
     * @var string
     */
    protected $_request_type;

    /**
     * Whether the object has any data loaded
     *
     * @var bool
     */
    protected $_has_data = false;

    /**
     * Configuration options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Constructor
     *
     * @param array $varlist A list of parameter defaults to set
     * @param string $type The request type.
     *                     Possible values: get, post, files, cookie, server.
     * @param array $options Array of options
     * @return void
     */
    public function __construct($varlist=array(), $type='get', $options=array())
    {
        $this->_request_type = $type;
        $this->_init_varlist($varlist);
        $this->_options = $options;
        $this->fetch();
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
     * @return object Apricot_ModelRow
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
    public function has_data()
    {
        return $this->_has_data;
    }

    /**
     * Get the value for a given parameter
     *
     * @param string $var The name of the parameter to get
     * @return mixed The value of the parameter
     */
    public function __get($var)
    {
        return $this->getParameter($var);
    }

    /**
     * Set a parameter
     *
     * todo Should this be private?
     *
     * @param string $name The name of the variable to set
     * @param mixed $value The value of the variable
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Get a parameter
     *
     * If the parameter has not been set, it will return the default.
     *
     * @param mixed $var The name of the parameter
     * @param mixed $default The default parameter to return
     * @return mixed The value of the parameter
     */
    public function getParameter($var, $default=false)
    {
        if (isset($this->_data[$var])) {
            return $this->_data[$var];
        } else {
            return $default;
        }
    }

    /**
     * Return an array of the data loaded
     *
     * @return array
     */
    public function to_array()
    {
        return (array) $this->_data;
    }

    /**
     * Return a StdClass object of the data
     *
     * @return object StdClass
     */
    public function to_simple_object()
    {
        return (object) $this->_data;
    }

    /**
     * Set default parameters and/or parameter values
     *
     * The input is either a name value array, or
     * an array of strings with the format "variable=value"
     *
     * @param array $varlist The parameter list
     * @return void
     */
    private function _init_varlist($varlist = array())
    {
        foreach ($varlist as $key => $var_default) {
            if (is_int($key)) {
                if (strpos($var_default, '=')) {
                    list($name, $value) = split('=', $var_default);
                    $this->_data[$name] = $value;
                } else {
                    $this->_data[$var_default] = '';
                }
            } else {
                $this->_data[$key] = $var_default;
            }
        }
    }

    /**
     * Fetch the values and load into the object
     *
     * @param string $extra_var Manually add an extra var at call-time
     * @return void
     */
    public function fetch($extra_var='')
    {
        $var = $this->_fetch_request_var();

        foreach ($var as $name=>$value) {
            if (isset($this->_options['trim'])
                && $this->_options['trim'] == true
                && $this->_request_type != 'files'
                && !is_array($value)
            ) {
                $value = stripslashes(trim($value));
            }

            if (in_array('strip_tags', $this->_options) && !is_array($value)) {
                $this->_data[$name] = strip_tags($value);
            } else {
                $this->_data[$name] = $value;
            }
            $this->_has_data = true;
        }
    }

    /**
     * Run a "data cleaning" function on the contents of the fetched data
     *
     * Can have multiple parameters passed in that will go to the
     * cleaning function.
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
     * Get this request type
     *
     * @return string The request type
     */
    public function get_type()
    {
        return $this->_request_type;
    }

    /**
     * Get the corresponding superglobal array
     *
     * @return array
     */
    private function _fetch_request_var()
    {
        switch ($this->_request_type) {
        case 'post':
            return $_POST;
            break;
        case 'files':
            return $_FILES;
            break;
        case 'cookie':
            return $_COOKIE;
            break;
        case 'server':
            return $_SERVER;
            break;
        case 'get':
            return $_GET;
            break;
        default:
            return array();
            break;
        }
    }
}
