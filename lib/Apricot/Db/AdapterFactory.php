<?php
/**
 * Apricot Db Adapter Factory class file
 *
 * @package Apricot
 */

namespace Apricot\Db;

/**
 * AdapterFactory
 *
 * @package Apricot
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class AdapterFactory
{
    /**#@+
     * Adapter types
     *
     * @var string
     */
    const ADAPTER_GENERIC    = 'generic';
    const ADAPTER_CLASS      = 'class';
    const ADAPTER_HAZELPLUM  = 'hazelplum';
    const ADAPTER_POSTGRESQL = 'postgresql';
    const ADAPTER_PDO_MYSQL  = 'pdomysql';
    const ADAPTER_PDO_SQLITE = 'pdosqlite';
    /**#@-*/

    /**
     * Make an adapter for a given connection configuration
     *
     * Each adapter has a different set of required params (see below)
     *
     * @param mixed $connectionConfig Connection configuration options
     * @return Apricot\Db\AdapterInterface
     */
    static public function makeAdapter($connectionConfig)
    {
        if (is_array($connectionConfig)) {
            $connectionConfig = (object) $connectionConfig;
        }

        if (isset($connectionConfig->adapter)) {
            switch (strtolower($connectionConfig->adapter)) {
            case self::ADAPTER_HAZELPLUM:
                include_once 'Qi/Db/Hazelplum.php';
                //if (!isset($connectionConfig['db_name'])) {
                    //$connectionConfig['db_name'] = $this->db_name;
                //}
                return new \Qi_Db_Hazelplum(
                    $connectionConfig['datapath'],
                    $connectionConfig['db_name'],
                    array('prepend_databasename_to_table_filename' => true)
                );
                break;
            case self::ADAPTER_PDO_MYSQL:
                // cfg needs 'host','db','user','pass'
                // cfg options 'log','log_file'
                include_once 'Qi/Db/PdoMysql.php';
                return new \Qi_Db_PdoMysql((array) $connectionConfig);
                break;
            case self::ADAPTER_POSTGRESQL:
                // cfg needs 'host','db','user','pass'
                // cfg options 'log','log_file'
                include_once 'Qi/Db/Postgresql.php';
                return new \Qi_Db_Postgresql($connectionConfig);
                break;
            case self::ADAPTER_PDO_SQLITE:
                // cfg needs 'dbfile'
                // cfg options 'log','log_file'
                include_once 'Qi/Db/PdoSqlite.php';
                return new \Qi_Db_PdoSqlite($connectionConfig);
                break;
            case self::ADAPTER_CLASS:
                if (isset($connectionConfig->class)
                    && class_exists($connectionConfig->class)
                ) {
                    $class     = $connectionConfig->class;
                    return new $class($connectionConfig);
                }
                break;
            }
        }
    }
}
