<?php
/**
 * Sesspool class file
 *
 * @package Qi
 */

/**
 * Qi Sesspool class
 *
 * A PHP session manager
 *
 * @package Qi
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version 0.9.1
 */
class Qi_Sesspool
{
    /**
     * Namespace allows for separation of variables in the session
     *
     * @var string
     */
    private $_namespace;

    /**
     * Storage for the session variable
     *
     * @var mixed
     */
    private $_session;

    /**
     * Constructor
     *
     * @param string $namespace Name of container for variables
     * @return void
     */
    public function __construct($namespace=null)
    {
        if (null === $namespace) {
            $this->_namespace = 'Sess_default';
        } else {
            $this->_namespace = $namespace;
        }
        if (!$this->_is_session_started()) {
            $this->_start_session();
        }
        $this->_session = $_SESSION;
    }

    /**
     * Return if the session has been started
     *
     * @return bool
     */
    protected function _is_session_started()
    {
        return isset($_SESSION);
    }

    /**
     * Start session
     *
     * @return void
     */
    protected function _start_session()
    {
        if (!isset($_SERVER['TERM'])) {
            // only start if we are not in the console.
            session_start();
        }
    }

    /**
     * Set a parameter and value to store in the session
     *
     * @param string $name The name of the parameter
     * @param mixed $value The value of the parameter
     * @return void
     */
    public function __set($name, $value)
    {
        $_SESSION[$this->_namespace][$name] = $value;
    }

    /**
     * Get a value for a parameter
     *
     * @param string $name The name of the parameter
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($_SESSION[$this->_namespace][$name])) {
            return $_SESSION[$this->_namespace][$name];
        }

        return null;
    }

    /**
     * Clear a value from this namespace
     *
     * @param string $name The name of the value to clear
     * @return void
     */
    public function clear($name = '')
    {
        if ($name != '') {
            if (isset($_SESSION[$this->_namespace][$name])) {
                unset($_SESSION[$this->_namespace][$name]);
            }
        } else {
            if (isset($_SESSION[$this->_namespace])) {
                unset($_SESSION[$this->_namespace]);
            }
        }
    }
}
