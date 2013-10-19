<?php
/**
 * Entity
 *
 * @package Apricot
 */

namespace Apricot\Model;

/**
 * Entity
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Entity
{
    /**
     * Storage of data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param mixed $input A key-value array or traversable object
     * @return void
     */
    public function __construct($input = array())
    {
        $this->populate($input);
    }

    /**
     * Set repository
     *
     * @param \Apricot\Model\Repository $repository
     * @return \Apricot\Model\Collection
     */
    public function setRepository($repository)
    {
        $this->_repository($repository);
        return $this;
    }

    /**
     * Get repository object
     *
     * @return \Apricot\Model\Repository
     */
    public function getRepository()
    {
        return $this->_repository();
    }

    /**
     * Get or set the repository
     *
     * It is stored staticly in this method
     *
     * @param \Apricot\Model\Repository $set Repository object
     * @return void
     */
    protected function _repository($set = null)
    {
        static $repository;

        if (null === $set) {
            return $repository;
        }

        $repository = $set;
    }

    /**
     * Populate row of data
     *
     * Load an array representing a row of data to populate this entity
     *
     * @param array $input A key-value array or traversable object
     * @return \Apricot\Model\Entity
     */
    public function populate($input)
    {
        if (empty($input)) {
            return $this;
        }

        if (is_scalar($input)) {
            return $this;
        }

        foreach ($input as $name => $value) {
            if (is_numeric($name)) {
                // If a numeric key exists in the array, also add a value with 
                // the underscore to make it easy to access with a $object->_1;
                $this->_data['_' . $name] = $value;
            }

            $this->_data[$name] = $value;
        }

        return $this;
    }

    /**
     * Return the data from this entity as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Get a comma delimited list of the columns
     *
     * @param string $separator Separator character between column names 
     * @param string $delimiter A character for enclosing names (default is blank)
     * @return string
     */
    public function getColumnList($separator = ',', $delimiter = '')
    {
        $list = array();
        foreach (array_keys($this->toArray()) as $columnName) {
            $list[] = $delimiter . $columnName . $delimiter;
        }

        return implode($separator, $list);
    }

    /**
     * Return an indexed array of the values
     *
     * Useful for converting a key-value array of data into an
     * indexed array
     *
     * @return array
     */
    public function getValues()
    {
        $array = $this->toArray();
        return array_values($array);
    }

    /**
     * Get a value from dataset
     *
     * @param mixed $name The column name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }

        return null;
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
}
