<?php
/**
 * Apricot Model file
 *
 * <pre>
 *     _               _           _
 *    / \   _ __  _ __(_) ___ ___ | |_
 *   / _ \ | '_ \| '__| |/ __/ _ \| __|
 *  / ___ \| |_) | |  | | (_| (_) | |_
 * /_/   \_\ .__/|_|  |_|\___\___/ \__|
 *         |_|
 * </pre>
 *
 * @package Apricot
 * @subpackage Model
 * @version $Id: Model.php 1734 2010-03-16 02:49:55Z jansen $
 */

namespace Apricot;

/**
 * Apricot\Model
 *
 * @package Apricot
 * @subpackage Model
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 * @abstract
 */
abstract class Model
{
    /**
     * Storage of data
     *
     * @var array
     */
    protected $_data;

    /**
     * Config settings
     * 
     * @var mixed
     */
    protected $_cfg;

    /**
     * List of error messages
     * 
     * @var array
     */
    protected $_errors = array();

    /**
     * Database object
     *
     * @var object
     */
    protected $_db;

    /**
     * The row class
     *
     * @var mixed
     */
    public $rowClass = null;

    /**
     * Kernel object
     * 
     * @var \Apricot\Kernel
     */
    protected $_kernel = null;

    /**#@+
     * Adapter types
     *
     * @var string
     */
    const ADAPTER_GENERIC    = 'generic';
    const ADAPTER_CLASS      = 'class';
    const ADAPTER_HAZELPLUM  = 'hazelplum';
    const ADAPTER_MYSQL      = 'mysql';
    const ADAPTER_POSTGRESQL = 'postgresql';
    const ADAPTER_SQLITE     = 'sqlite';
    const ADAPTER_PDO_MYSQL  = 'pdomysql';
    const ADAPTER_PDO_SQLITE = 'pdosqlite';
    /**#@-*/

    /**
     * Constructor
     *
     * @param \Apricot\Kernel $kernel Kernel object
     * @param array $cfg Options with configuration data
     * @return void
     */
    public function __construct(Kernel $kernel, $cfg = array())
    {
        $this->setKernel($kernel);

        $this->_cfg = $cfg;
        $this->initAdapter();
        $this->init();
    }

    /**
     * Set Kernel object
     * 
     * @param \Apricot\Kernel $kernel Kernel object
     * @return void
     */
    public function setKernel(Kernel $kernel)
    {
        $this->_kernel = $kernel;
    }

    /**
     * Get Kernel object
     * 
     * @return \Apricot\Kernel
     */
    public function getKernel()
    {
        return $this->_kernel;
    }

    /**
     * Initialization of the object (called after construction)
     *
     * @return void
     */
    protected function initAdapter()
    {
        if (isset($this->_cfg->adapter)) {
            switch (strtolower($this->_cfg->adapter)) {
            case self::ADAPTER_HAZELPLUM:
                include_once 'Qi/Db/Hazelplum.php';
                if (!isset($this->_cfg['db_name'])) {
                    $this->_cfg['db_name'] = $this->db_name;
                }
                $this->_db = new \Qi_Db_Hazelplum(
                    $this->_cfg['datapath'],
                    $this->_cfg['db_name'],
                    array('prepend_databasename_to_table_filename' => true)
                );
                break;
            case self::ADAPTER_MYSQL:
                // cfg needs 'host','db','user','pass'
                // cfg options 'log','log_file'
                include_once 'Qi/Db/Mysql.php';
                $this->_db = new \Qi_Db_Mysql((array) $this->_cfg);
                break;
            case self::ADAPTER_PDO_MYSQL:
                // cfg needs 'host','db','user','pass'
                // cfg options 'log','log_file'
                include_once 'Qi/Db/PdoMysql.php';
                $this->_db = new \Qi_Db_PdoMysql((array) $this->_cfg);
                break;
            case self::ADAPTER_POSTGRESQL:
                // cfg needs 'host','db','user','pass'
                // cfg options 'log','log_file'
                include_once 'Qi/Db/Postgresql.php';
                $this->_db = new \Qi_Db_Postgresql($this->_cfg);
                break;
            case self::ADAPTER_SQLITE:
                // cfg needs 'dbfile'
                // cfg options 'log','log_file'
                include_once 'Qi/Db/Sqlite.php';
                $this->_db = new \Qi_Db_Sqlite($this->_cfg);
                break;
            case self::ADAPTER_PDO_SQLITE:
                // cfg needs 'dbfile'
                // cfg options 'log','log_file'
                include_once 'Qi/Db/PdoSqlite.php';
                $this->_db = new \Qi_Db_PdoSqlite($this->_cfg);
                break;
            case self::ADAPTER_CLASS:
                if (isset($this->_cfg['class'])
                    && class_exists($this->_cfg['class'])
                ) {
                    $class     = $this->_cfg['class'];
                    $this->_db = new $class($this->_cfg);
                }
                break;
            }
        }

        if (null == $this->_db) {
            $this->_db = new Db\Generic($this->_cfg);
        }
    }

    /**
     * Initialize model (called after initAdapter())
     * 
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Get a value from dataset
     *
     * @param mixed $val The column name
     * @return mixed
     */
    public function &__get($val)
    {
        switch ($val) {
        default:
            if (isset($this->_data[$val])) {
                return $this->_data[$val];
            } else {
                $result = false;
                return $result;
            }
        }
    }

    /**
     * Set a value
     *
     * @param mixed $name The name of the value to set
     * @param mixed $value The value to set to
     * @return void
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Load row of data
     *
     * Load an array representing a row of data to be
     * accessed like an object
     *
     * @param array $a A key-value array of the data
     * @return object
     */
    public function load($a)
    {
        if (!empty($a)) {
            foreach ($a as $col => $value) {
                $this->_data[$col] = $value;
            }
        }
        return $this;
    }

    /**
     * Get rows as rowset object
     *
     * @param string $sql A sql statement
     * @param array $data Data values to bind
     * @return mixed
     */
    public function getRows($sql, $data = array())
    {
        $rows = $this->_db->getRows($sql, $data);
        
        if (count($rows)) {
            return $this->hydrate($rows, true);
        }

        return array();
    }

    /**
     * Get row as row object
     *
     * @param string $sql A sql statement
     * @param array $data Data values to bind
     * @return void
     */
    public function getRow($sql, $data = array())
    {
        $row = $this->_db->getRows($sql, $data);

        if (count($row)) {
            return $this->hydrate($row);
        }

        return null;
    }

    /**
     * Attempt to load up a \Apricot\ModelRow object with the data.
     *
     * @param mixed $a An array of data to be hydrated
     * @param bool $isRowset Whether this should be hydrated as a rowset
     * @return object
     */
    public function hydrate($a, $isRowset=false)
    {
        if (empty($a) || !is_array($a)) {
            return $a;
        }

        if ($isRowset) {
            if (isset($this->rowsetClass) && $this->rowsetClass != '') {
                $rowset = $this->_kernel->getModel(
                    preg_replace("/Model$/", '', $this->rowsetClass),
                    null, false
                );
                return $rowset->load($a);
            } else {
                $rowset = new Model\Rowset($this->_kernel, $this->_cfg);

                $rowset->rowClass = $this->rowClass;
                $rowset->load($a);
                return $rowset;
            }
        } else {
            if (isset($this->rowClass) && $this->rowClass) {
                $row = $this->_kernel->getModel(
                    preg_replace("/Model$/", '', $this->rowClass),
                    $this->_cfg, false
                );
                return $row->load($a[0]);
            } else {
                $row = new Model\Row($this->_kernel, $this->_cfg);
                $row->load($a[0]);
                return $row;
            }
        }
    }

    /**
     * Return the data retrieved
     *
     * @return array
     */
    public function getData()
    {
        if (!empty($this->_data)) {
            return $this->_data;
        } else {
            return null;
        }
    }

    /**
     * Get a comma delimited list of the columns
     *
     * @param array $array Array of the columns
     * @param string $delimiter A delimter for enclosing string (default is `)
     * @return string
     */
    protected function _columns($array = null, $delimiter = '`')
    {
        $array = $this->_getCurrentDataOrArray($array);

        $out = '';
        foreach (array_keys($array) as $col) {
            $out .= $delimiter . $col . $delimiter . ",";
        }

        return substr($out, 0, -1);
    }

    /**
     * Return an indexed array of the values
     *
     * Useful for converting a key-value array of data into an
     * indexed array
     *
     * @param array $array A key-value array of data
     * @return array
     */
    protected function _values($array=null)
    {
        $array = $this->_getCurrentDataOrArray($array);
        return array_values($array);
    }

    /**
     * Internal function to ensure an array or the current dataset
     *
     * @param mixed $array The array of data or null
     * @return array
     */
    protected function _getCurrentDataOrArray($array = null)
    {
        if (null == $array) {
            $array = $this->_data;
        }

        if (!is_array($array)) {
            $array = array();
        }

        return $array;
    }

    /**
     * Set an error
     *
     * This throws an exception, unless the $silent parameter
     * is set to true.
     *
     * @param mixed $errorMessage The error message to log
     * @param boolean $throwError Flag to throw an exception
     * @return void
     */
    public function setError($errorMessage, $throwError = false)
    {
        if (is_array($errorMessage)) {
            $error = $errorMessage;
        } else {
            $error = array($errorMessage);
        }

        $this->_errors = array_merge($this->_errors, $error);

        if ($throwError) {
            throw new ModelException(implode("\n", $error));
        }
    }

    /**
     * Return if there are any errors
     *
     * @return bool Whether there was an error
     */
    public function hasError()
    {
        return (bool) count($this->getErrors());
    }

    /**
     * Get any errors that were set
     *
     * @return array
     */
    public function getErrors()
    {
        if ($this->_db && is_array($this->_db->getErrors())) {
            return array_merge($this->_errors, $this->_db->getErrors());
        } else {
            return $this->_errors;
        }
    }
}

namespace Apricot\Db;

/**
 * Generic Db Adapter
 * 
 * @package Apricot
 * @subpackage Db
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class Generic 
{
    /**
     * Constructor
     * 
     * @param array $cfg Configuration array
     * @return void
     */
    public function __construct($cfg)
    {
    }

    /**
     * Get rows
     * 
     * @param string $input Input query
     * @return mixed
     */
    public function getRows($input)
    {
    }

    /**
     * Get error messages
     * 
     * @return array
     */
    public function getErrors()
    {
    }
}

/**
 * Apricot\ModelException
 *
 * @uses Exception
 * @package Apricot
 * @subpackage Model
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class ModelException extends \Exception
{
}
