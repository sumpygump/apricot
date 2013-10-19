<?php
/**
 * Apricot Model Adapter Interface file 
 *
 * @package Apricot\Db
 */

/**
 * Apricot Model Adapter Interface
 * 
 * @package Apricot\Db
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version ApricotVersion: 1.3b
 */
interface Apricot_Db_AdapterInterface
{
    /**
     * Get rows
     * 
     * @param string $query Query to send to db
     * @return mixed
     */
    public function getRows($query);

    /**
     * Get one row
     * 
     * @param string $query Query to send to db
     * @return mixed
     */
    public function getRow($query);
}
