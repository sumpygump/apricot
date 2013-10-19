<?php
/**
 * Apricot Model Row class file
 *  
 * @package Apricot\Model
 */

namespace Apricot\Model;

use \Apricot\Model;

/**
 * Apricot\ModelRow
 *
 * @uses Apricot\Model
 * @package Apricot\Model
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
class Row extends Model
{
    /**
     * Return the data as an array
     * 
     * @return void
     */
    public function toArray()
    {
        return $this->_data;
    }
}
