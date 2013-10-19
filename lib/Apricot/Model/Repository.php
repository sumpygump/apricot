<?php
/**
 * Repository class file
 *
 * @package Apricot
 */

namespace Apricot\Model;

/**
 * Repository
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class Repository
{
    /**
     * _adapter
     *
     * @var mixed
     */
    protected $_adapter;

    /**
     * Entity class
     *
     * @var string
     */
    protected $_entityClass = '\\Apricot\\Model\\Entity';

    /**
     * Collection class
     *
     * @var string
     */
    protected $_collectionClass = '\\Apricot\\Model\\Collection';

    /**
     * Model factory
     *
     * @var \Apricot\Model\Factory
     */
    protected $_factory;

    /**
     * List of error messages
     * 
     * @var array
     */
    protected $_errors = array();

    /**
     * Constructor
     *
     * @param \Apricot\Db\AdapterInterface $adapter Db adapter
     * @return void
     */
    public function __construct($adapter)
    {
        $this->setAdapter($adapter);
    }

    /**
     * Set adapter
     *
     * @param \Apricot\Db\AdapterInterface $adapter Db adapter
     * @return \Apricot\Model\Repository
     */
    public function setAdapter($adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Get adapter
     *
     * @return \Apricot\Db\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Set model factory
     *
     * @param \Apricot\Model\Factory $factory Factory
     * @return \Apricot\Model\Repository
     */
    public function setFactory($factory)
    {
        $this->_factory = $factory;
        return $this;
    }

    /**
     * Get factory
     *
     * @return \Apricot\Model\Factory
     */
    public function getFactory()
    {
        return $this->_factory;
    }

    /**
     * Get entity class name
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->_entityClass;
    }

    /**
     * Get collection class name
     *
     * @return void
     */
    public function getCollectionClass()
    {
        return $this->_collectionClass;
    }

    /**
     * Fetch row (an entity)
     *
     * @param string $sql SQL
     * @param array $data Data to bind to SQL
     * @return \Apricot\Model\Entity
     */
    public function fetchRow($sql, $data = array())
    {
        if (is_string($data)) {
            $data = array($data);
        }

        $row = $this->getAdapter()->getRow($sql, $data);

        // If no resulting data, return null
        if (!$row) {
            return null;
        }

        return $this->createEntity($row);
    }

    /**
     * Fetch rows (collection of entities)
     *
     * @param string $sql SQL
     * @param array $data Data to bind to SQL
     * @return \Apricot\Model\Collection
     */
    public function fetchRows($sql, $data = array())
    {
        if (is_string($data)) {
            $data = array($data);
        }

        $rows = $this->getAdapter()->getRows($sql, $data);

        return $this->createCollection($rows);
    }

    /**
     * Create a collection
     *
     * @param mixed $items
     * @return \Apricot\Model\Collection
     */
    public function createCollection($items)
    {
        $class = $this->getCollectionClass();
        $collection = new $class($items);

        // Set repository
        $collection->setRepository($this);

        return $collection;
    }

    /**
     * createEntity
     *
     * @param mixed $data
     * @return void
     */
    public function createEntity($data)
    {
        $class = $this->getEntityClass();

        if (substr($class, 0, 1) != "\\") {
            // If the entity class name doesn't begin with a backslash, we're 
            // going to assume the entity class name is based on the repository 
            // class name and derive it as follows:
            // \Acme\Model\Repository\Users will become \Acme\Model\Entity\User
            // The 'user' part is supplied by the entity class name, We'll take 
            // the last section of the repo class name and replace it with the 
            // class name provided, and the word 'Repository' is replaced with 
            // 'Entity'
            $repositoryClass = get_class($this);
            $basename = basename(str_replace("\\", DIRECTORY_SEPARATOR, $repositoryClass));
            $class = str_replace($basename, ucfirst($class), $repositoryClass);
            $class = str_replace('Repository', 'Entity', $class);
        }

        $entity = new $class($data);

        $entity->setRepository($this);

        return $entity;
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
            throw new RepositoryException(implode("\n", $error));
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
        if ($this->getAdapter() && is_array($this->getAdapter()->getErrors())) {
            return array_merge($this->_errors, $this->getAdapter()->getErrors());
        } else {
            return $this->_errors;
        }
    }
}

/**
 * RepositoryException
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class RepositoryException extends \Exception
{
}
