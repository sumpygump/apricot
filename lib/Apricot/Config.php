<?php
/**
 * Apricot Config class file
 *
 * @package Apricot
 * @version $Id$
 */

namespace Apricot;

/**
 * Apricot Config class
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Config
{
    /**
     * Data storage
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Default section name
     *
     * @var string
     */
    protected $_defaultSection = 'default';

    /**
     * Set of values to be replaced (expanded)
     *
     * @var array
     */
    protected $_expansions = array();

    /**
     * Constructor
     *
     * @param string $filename Ini file to load
     * @param array $defaults Default options to use
     * @param array $expansions Set an array of values to be expanded after 
     *      construction
     * @return void
     */
    public function __construct($filename = null, $defaults = array(), $expansions = array())
    {
        $this->set($this->_defaultSection, $defaults);

        $this->_expansions = $expansions;

        if ($filename !== null && is_string($filename)) {
            $this->loadIniFile($filename);
        }
    }

    /**
     * Load ini file
     *
     * @param string $filename Ini filename
     * @return void
     */
    public function loadIniFile($filename)
    {
        if (!file_exists($filename)) {
            throw new ConfigException("Cannot load ini file '$filename'");
        }

        $raw = parse_ini_file($filename, true);
        $this->loadArray($raw);

        $this->_performExpansions($this->_expansions);
        return $raw;
    }

    /**
     * Load ini from string
     *
     * @param string $ini Ini values
     * @return void
     */
    public function loadIniString($ini)
    {
        $raw = parse_ini_string($ini, true);
        $this->loadArray($raw);

        $this->_performExpansions($this->_expansions);
        return $raw;
    }

    /**
     * Load configuration data from array
     *
     * @param array $array Array with key-value pairs
     * @return void
     */
    public function loadArray($array)
    {
        foreach ($array as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Convert to array
     *
     * @param string $sectionName The name of the section
     * @return array
     */
    public function toArray($sectionName = null)
    {
        if ($sectionName == null) {
            return $this->_data;
        }

        if (!isset($this->_data[$sectionName])) {
            return null;
        }

        return $this->_data[$sectionName];
    }

    /**
     * Convert to object (StdClass)
     *
     * @param string $sectionName The name of the section to convert
     * @return object
     */
    public function toObject($sectionName = null)
    {
        if ($sectionName == null) {
            return self::_convertToObject($this->_data);
        }

        if (!isset($this->_data[$sectionName])) {
            return null;
        }

        return self::_convertToObject($this->_data[$sectionName]);
    }

    /**
     * Get a configuration value
     *
     * @param mixed $var The name of the variable to get
     * @param string $section The section name in which to seek the var
     * @return object
     */
    public function get($var, $section = null)
    {
        $value = null;

        if (null == $section) {
            if (isset($this->_data[$var])) {
                $value = $this->_data[$var];
            } else {
                // If the param is not found, try in the default section
                return $this->get($var, $this->_defaultSection);
            }
        } else {
            if (isset($this->_data[$section])
                && isset($this->_data[$section][$var])
            ) {
                $value = $this->_data[$section][$var];
            }
        }

        return self::_convertToObject($value);
    }

    /**
     * Set a value
     *
     * @param string $key The key name
     * @param mixed $value The value
     * @param mixed $sectionName The name of the section
     * @return bool
     */
    public function set($key, $value, $sectionName = null)
    {
        $key = (string) $key;
        $key = trim($key);

        if ($key == '') {
            return $this;
        }

        if (null === $sectionName) {
            if (is_array($value)) {
                $this->_addArray($key, $value);
            } else {
                $this->_data[$key] = $value;
            }
        } else {
            $this->_addArray($sectionName, array($key => $value));
        }

        return $this;
    }

    /**
     * Get value from default section
     *
     * @param string $key Key name
     * @return mixed
     */
    public function getDefault($key)
    {
        return $this->get($key, $this->_defaultSection);
    }

    /**
     * Set Default
     *
     * @param string $key Key name
     * @param mixed $value Value to set
     * @return this
     */
    public function setDefault($key, $value)
    {
        return $this->set($key, $value, $this->_defaultSection);
    }

    /**
     * Bulk set key value pairs within a certain section
     *
     * @param string $sectionName The name of the section
     * @param array $values A list of mapped key-value pairs
     * @return bool Whether setting was successful
     */
    public function setSection($sectionName, $values)
    {
        if (!is_array($values)) {
            return false;
        }

        foreach ($values as $key => $value) {
            $this->set($key, $value, $sectionName);
        }

        return true;
    }

    /**
     * Perform multiple expansions by array
     *
     * @return void
     */
    protected function _performExpansions()
    {
        foreach ($this->_expansions as $token => $value) {
            $this->expandValues($token, $value);
        }
    }

    /**
     * Loop through all values and replace tokens with values in the data
     *
     * @param string $token A token string to search
     * @param string $value The value the token should be replaced with
     * @param mixed &$item Array in which to process
     * @return void
     */
    public function expandValues($token, $value, &$item = null)
    {
        if (null === $item) {
            $item = &$this->_data;
        }

        foreach ($item as $key => &$itemValue) {
            if (is_array($itemValue)) {
                $this->expandValues($token, $value, $itemValue);
            } else {
                if (strpos($itemValue, $token) !== false) {
                    $item[$key] = str_replace($token, $value, $itemValue);
                }
            }
        }

        return $item;
    }

    /**
     * Magic get method
     *
     * @param string $var Name of item
     * @return mixed
     */
    public function __get($var)
    {
        return $this->get($var);
    }

    /**
     * Magic set method
     *
     * Places params in the default section
     *
     * @param string $var Name of key
     * @param mixed $value Value to set
     * @return bool
     */
    public function __set($var, $value)
    {
        return $this->set($var, $value, $this->_defaultSection);
    }

    /**
     * Add an array to the config data
     *
     * Parse out and nest sub items
     *
     * @param string $sectionName Config section
     * @param array $data Data to add
     * @return void
     */
    protected function _addArray($sectionName, $data)
    {
        if (!isset($this->_data[$sectionName])) {
            $this->_data[$sectionName] = array();

            $section = array();
        } else {
            $section = $this->_data[$sectionName];
        }

        foreach ($data as $key => $value) {
            if (false !== strpos($key, '.')) {
                $pieces = explode('.', $key, 2);

                // We can't use empty strings as keys
                if ($pieces[0] == '') {
                    $pieces[0] = 'empty';
                }
                if ($pieces[1] == '') {
                    $pieces[1] = 'empty';
                }

                // If there are any extra periods, convert them
                // to underscores
                $pieces[1] = str_replace('.', '_', $pieces[1]);

                // Create array if it doesn't already exist
                if (!isset($section[$pieces[0]])) {
                    $section[$pieces[0]] = array();
                }
                $section[$pieces[0]][$pieces[1]] = $value;
            } else {
                $section[$key] = $value;
            }
        }

        $this->_data[$sectionName] = $section;
    }

    /**
     * Two level deep array to object
     *
     * @param array $input The input data to convert
     * @return object
     */
    protected static function _convertToObject($input)
    {
        if (is_array($input)) {
            $keys = array_keys($input);

            if (isset($keys[0]) && !is_numeric($keys[0])) {
                foreach ($input as &$item) {
                    $item = self::_convertToObject($item);
                }
                return (object) $input;
            }
        }

        return $input;
    }
}

/**
 * Apricot_ConfigException
 *
 * @uses Exception
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class ConfigException extends \Exception
{
}
