<?php
/**
 * Apricot Model Rowset class file
 *  
 * @package Apricot\Model
 */

namespace Apricot\Model;

use \Apricot\Kernel;
use \Apricot\Model;

/**
 * Apricot Model Rowset
 *
 * @uses Apricot\Model
 * @package Apricot\Model
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Rowset extends Model implements \Iterator
{
    /**
     * Position indicator
     *
     * @var int
     */
    private $_pos = 0;

    /**
     * Constructor
     *
     * @param \Apricot\Kernel $kernel Apricot Kernel object
     * @param array $cfg Configuration array
     * @param string $rowClass The name of the rowClass to set
     * @return void
     */
    public function __construct(\Apricot\Kernel $kernel, $cfg = array(),
        $rowClass = null)
    {
        $this->setKernel($kernel);

        if ($rowClass != null) {
            $this->rowClass = $rowClass;
        }

        $this->_cfg = $cfg;
        $this->initAdapter();
        $this->_pos = 0;
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
        return $this->hydrate(array($this->_data[$this->_pos]));
        //return $this->_data[$this->_pos];
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
}
