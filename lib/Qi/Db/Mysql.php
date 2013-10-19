<?php
/**
 * Mysql DB file
 *
 * @package Qi
 * @subpackage Db
 */

/**
 * Qi_Db_Mysql
 *
 * Provides common functions for an interface to mysql db.
 * 20070928 :: improved safe_column() to actually work
 * 20090410 :: Updated docblocks and fixed minor glitches
 *
 * @package Qi
 * @subpackage Db
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version 1.2
 */
class Qi_Db_Mysql
{
    /**
     * @var string The mysql host to connect to
     */
    protected $host;

    /**
     * @var string The name of the database
     */
    protected $db;

    /**
     * @var string The database user to login with
     */
    protected $user;

    /**
     * @var string The database user's password
     */
    protected $pass;

    /**
     * @var object Resource The database resource object
     */
    protected $link;

    /**
     * @var array Logging configuration settings
     */
    protected $q_log;

    /**
     * Constructor
     *
     * @param array $dbcfg Array with configuration details
     * @return void
     */
    public function __construct($dbcfg)
    {
        $this->q_log['log']      = isset($dbcfg['log']) ? $dbcfg['log'] : false;
        $this->q_log['log_file'] =
            isset($dbcfg['log_file']) ? $dbcfg['log_file'] : '';

        $this->host  = $dbcfg['host'];
        $this->db    = $dbcfg['db'];
        $this->user  = $dbcfg['user'];
        $this->pass  = $dbcfg['pass'];
        @$this->link = mysql_connect($this->host, $this->user, $this->pass);
        //if (!$this->link) die("Mysql connection error.");
        if (!$this->link) {
            throw new Exception("Mysql connection error.");
        }
        mysql_select_db($this->db);
    }

    /**
     * Sanitize a string for sql statement
     *
     * Deprecated method
     *
     * @param string $string The string to sanitize
     * @return string
     */
    public function sql_string($string)
    {
        return str_replace("'", "\\'", stripslashes($string));
    }

    /**
     * Escape sql string inputs
     *
     * @param mixed $string The string to escape
     * @return void
     */
    public function escape_string($string)
    {
        return mysql_real_escape_string($string);
    }

    /**
     * Safely run a sql query
     *
     * @param string $q The sql statement
     * @param string $debug Set debugging on (output query and error)
     * @param string $unbuf Use unbuffered query
     * @return array The resulting rows
     */
    public function safe_query($q='', $debug='', $unbuf='')
    {
        $method = (!$unbuf) ? 'mysql_query' : 'mysql_unbuffered_query';
        if (!$q) {
            return false;
        }

        // Log the sql statement
        if ($this->q_log['log']) {
            file_put_contents(
                $this->q_log['log_file'],
                date("m/d/Y H:i:s") . " ==>\n" . $q . "\n", FILE_APPEND
            );
        }

        // Execute the query
        $result = $method($q, $this->link);

        // Debug mode, echo the sql statement
        if ($debug) {
            echo($q);
            echo(mysql_error());
        }

        // Log the result and an error if any
        if ($this->q_log['log']) {
            $handle = fopen($this->q_log['log_file'], 'a');
            fwrite($handle, "RESULT ==> ".$result."\n\n");
            $err = mysql_error();
            if ($err) {
                fwrite($handle, "Error ==> ".$err."\n\n");
            }
            fclose($handle);
        }

        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * Safely delete rows from a table
     *
     * @param string $table The name of the table
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return bool Whether the sql was successful
     */
    public function safe_delete($table, $where, $debug='')
    {
        $q = "delete from $table where $where";
        if ($r = $this->safe_query($q, $debug)) {
            return true;
        }
        return false;
    }

    /**
     * Safely update rows in a table
     *
     * @param string $table The table name
     * @param string $set The set part of the query "col='value'"
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return bool Whether the sql was successful
     */
    public function safe_update($table, $set, $where, $debug='')
    {
        $q = "update $table set $set where $where";
        if ($r = $this->safe_query($q, $debug)) {
            return true;
        }
        return false;
    }

    /**
     * Safely insert rows into a table
     *
     * @param string $table The table name
     * @param string $set The set part of the query "VALUES(...)"
     * @param string $debug Enable debug mode
     * @return bool Whether the sql was successful
     */
    public function safe_insert($table, $set, $debug='')
    {
        $q = "insert into $table $set";
        if ($r = $this->safe_query($q, $debug)) {
            return mysql_insert_id();
        }
        return false;
    }

    /**
     * Safely run an alter statement
     *
     * @param string $table The table name
     * @param string $alter The alter part of statement e.g. "ADD COLUMN x ..."
     * @param string $debug Enable debug mode
     * @return bool Whether the sql was successful
     */
    public function safe_alter($table, $alter, $debug='')
    {
        $q = "alter table $table $alter";
        if ($r = $this->safe_query($q, $debug)) {
            return true;
        }
        return false;
    }

    /**
     * Safely optimize a table
     *
     * @param string $table The table name
     * @param string $debug Enable debug mode
     * @return bool Whether the sql was successful
     */
    public function safe_optimize($table, $debug='')
    {
        $q = "optimize table $table";
        if ($r = $this->safe_query($q, $debug)) {
            return true;
        }
        return false;
    }

    /**
     * Safely repair a table
     *
     * @param string $table The table name
     * @param string $debug Enable debug mode
     * @return bool Whether the sql was successful
     */
    public function safe_repair($table, $debug='')
    {
        $q = "repair table $table";
        if ($r = $this->safe_query($q, $debug)) {
            return true;
        }
        return false;
    }

    /**
     * Safely extract a row from a table
     *
     * @param string $thing The thing to extract
     * @param string $table The table name
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return array|bool The resulting row or false
     */
    public function safe_field($thing, $table, $where, $debug='')
    {
        $q = "select $thing from $table where $where";
        $r = $this->safe_query($q, $debug);
        if (mysql_num_rows($r) > 0) {
            return mysql_result($r, 0);
        }
        return false;
    }

    /**
     * Safely extract column values from a row or rows
     *
     * @param string $thing The thing to extract
     * @param string $table the table name
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return string|array A comma separated list of the values
     *                      returned or an empty array
     */
    public function safe_column($thing, $table, $where, $debug='')
    {
        $q  = "select $thing from $table where $where";
        $rs = $this->getRows($q, $debug);
        if ($rs) {
            $out = array();
            foreach ($rs as $a) {
                $out[] = implode(",", $a);
            }
            return $out;
        }
        return array();
    }

    /**
     * Safely get a row from a table
     *
     * @param string $things Comma separated list of columns to return
     * @param string $table The table name
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return array The row or an empty array
     */
    public function safe_row($things, $table, $where, $debug='')
    {
        $q  = "select $things from $table where $where";
        $rs = $this->getRow($q, $debug);
        if ($rs) {
            return $rs;
        }
        return array();
    }

    /**
     * Safely get rows from a table
     *
     * @param string $things The columns to return
     * @param string $table The table name
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return array The rows or an empty array
     */
    public function safe_rows($things, $table, $where, $debug='')
    {
        $q  = "select $things from $table where $where";
        $rs = $this->getRows($q, $debug);
        if ($rs) {
            return $rs;
        }
        return array();
    }

    /**
     * Get a count of rows
     *
     * @param string $table The table name
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return string The number of rows
     */
    public function safe_count($table, $where, $debug='')
    {
        return $this->getThing(
            "select count(*) from $table where $where", $debug
        );
    }

    /**
     * Fetch a value for a specific condition
     *
     * @param string $col The column to return
     * @param string $table The table name
     * @param string $key The column to test for the condition
     * @param string $val The value to test for in column $key
     * @param string $debug Enable debug mode
     * @return mixed The first row matching the query or false
     */
    public function fetch($col, $table, $key, $val, $debug='')
    {
        $q = "select $col from $table where `$key` = '$val' limit 1;";
        if ($r = $this->safe_query($q, $debug)) {
            return (mysql_num_rows($r) > 0) ? mysql_result($r, 0) : '';
        }
        return false;
    }

    /**
     * Execute a sql query and return the first resulting row
     *
     * @param string $query The sql query statement
     * @param string $debug Enable debug mode
     * @param mixed $indices The array indices returned
     *                       (MYSQL_NUM, MYSQL_ASSOC, MYSQL_BOTH)
     * @return array|bool The resulting row or false
     */
    public function getRow($query, $debug='', $indices=MYSQL_ASSOC)
    {
        if ($r = $this->safe_query($query, $debug)) {
            return (mysql_num_rows($r) > 0)
                ? mysql_fetch_array($r, $indices) : false;
        }
        return false;
    }

    /**
     * Execute a sql query and return the resulting rows
     *
     * @param string $query The sql query statement
     * @param string $debug Enable debug mode
     * @param mixed $indices The array indices returned
     *                       (MYSQL_NUM, MYSQL_ASSOC, MYSQL_BOTH)
     * @return array The resulting rows or false
     */
    public function getRows($query, $debug='', $indices=MYSQL_ASSOC)
    {
        if ($r = $this->safe_query($query, $debug)) {
            if (mysql_num_rows($r) > 0) {
                while ($a = mysql_fetch_array($r, $indices)) {
                    $out[] = $a;
                }
                return $out;
            }
        }
        return false;
    }

    /**
     * Execute a sql query and return the first column in the resulting row
     *
     * @param string $query The sql query statement
     * @param string $debug Enable debug mode
     * @return mixed The resulting thing or false
     */
    public function getThing($query, $debug='')
    {
        if ($r = $this->safe_query($query, $debug)) {
            return (mysql_num_rows($r) != 0) ? mysql_result($r, 0) : '';
        }
        return false;
    }

    /**
     * getThings
     * return values of one column from multiple rows in an num indexed array
     *
     * @param string $query The sql query statement
     * @param string $debug Enable debug mode
     * @return array The resulting rows
     */
    public function getThings($query, $debug='')
    {
        $rs = $this->getRows($query, $debug);
        if ($rs) {
            foreach ($rs as $a) {
                $out[] = $a[0];
            }
            return $out;
        }
        return array();
    }

    /**
     * Get a count of rows meeting a criteria
     *
     * @param string $table The table name
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return string The resulting number of rows
     */
    public function getCount($table, $where, $debug='')
    {
        return $this->getThing(
            "select count(*) from $table where $where", $debug
        );
    }

    /**
     * Creates an insert string from an array of col=>value.
     *
     * @param string $table The name of the table
     * @param data $a The array of data for which to get a string
     * @return string The sql string
     */
    public function getInsertString($table, $a)
    {
        $q_cols = '';
        $q_vals = '';

        foreach ($a as $col => $value) {
            $q_cols .= "$col,";
            $q_vals .= "'" . mysql_escape_string($value) . "',";
        }

        $q_cols = substr($q_cols, 0, -1);
        $q_vals = substr($q_vals, 0, -1);

        $q = "INSERT INTO $table ("
            . $q_cols . ") "
            . "VALUES (" . $q_vals . ")";

        return $q;
    }

    /**
     * Creates an update string from an array of col=>value and a where clause.
     *
     * @param string $table The name of the table
     * @param array $a The array of data for which to get the string
     * @param string $where The where clause
     * @return string The sql string
     */
    public function getUpdateString($table, $a, $where)
    {
        if (!$where) {
            // enforce where clause to prevent unwanted data loss.
            return false;
        }
        $q_text = '';

        foreach ($a as $col=>$value) {
            $q_text .= "$col='" . mysql_escape_string($value) . "', ";
        }

        $q_text = substr($q_text, 0, -2);

        $q = "UPDATE $table SET "
            .$q_text . " "
            ."WHERE " . $where;
        return $q;
    }

    /**
     * Do a safe query, return mysql_insert_id if successful.
     *
     * @param string $q The sql statement
     * @return mixed The insert id or error message
     */
    public function doSafeQuery($q)
    {
        if ($result = $this->safe_query($q)) {
            return mysql_insert_id($this->link);
        } else {
            return mysql_error($this->link);
        }
    }
}
