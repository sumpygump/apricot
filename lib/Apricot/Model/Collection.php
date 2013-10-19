<?php
/**
 * Model Collection
 */

namespace Apricot\Model;

/**
 * Collection
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class Collection implements \Iterator, \Countable
{
    /**
     * Position indicator
     *
     * @var int
     */
    private $_pos = 0;

    /**
     * The entites in this collection
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param array $items Items to set in this collection
     * @return void
     */
    public function __construct($items)
    {
        $this->populate($items);
        $this->_pos = 0;
    }

    /**
     * Populate this collection
     *
     * @param array $items Items to populate to this collection
     * @return void
     */
    public function populate($items)
    {
        $this->_data = $items;
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
     * Rewind to beginning of data array
     *
     * @return void
     */
    public function rewind()
    {
        $this->_pos = 0;
    }

    /**
     * Get the current data item
     *
     * @return object Apricot\ModelRow
     */
    public function current()
    {
        return $this->createEntity($this->_data[$this->_pos]);
    }

    /**
     * Get the key of the current data item
     *
     * @return int
     */
    public function key()
    {
        return $this->_pos;
    }

    /**
     * Go to the next data item
     *
     * @return void
     */
    public function next()
    {
        ++$this->_pos;
    }

    /**
     * Is the current data item valid?
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->_data[$this->_pos]);
    }

    /**
     * Count
     *
     * For Countable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }

    /**
     * Create entity
     *
     * @param array $data Data to populate entity
     * @return \Apricot\Model\Entity
     */
    public function createEntity($data)
    {
        return $this->getRepository()->createEntity($data);
    }
}
