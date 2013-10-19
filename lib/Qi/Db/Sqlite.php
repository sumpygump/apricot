<?php
/**
 * Sqlite Db class file
 *
 * @package Qi
 * @subpackage Db
 */

/**
 * Qi_Db_Sqlite
 *
 * Provides common functions for an interface to sqlite db.
 *
 * @package Qi
 * @subpackage Db
 * @author Jansen Price <jansen.price@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version 1.0
 */
class Qi_Db_Sqlite
{
    /**
     * @var mixed Db config settings
     */
    protected $_cfg;

    /**
     * @var string The database filename
     */
    protected $dbfile;

    /**
     * @var int The version (2 or 3)
     */
    protected $version = 3;
    
    /**
     * @var object The database resource object
     */
    protected $link;

    /**
     * @var array Logging configuration settings
     */
    protected $q_log;

    /**
     * @var array Array of errors encountered
     */
    protected $errors = array();

    /**
     * Constructor
     *
     * @param array $dbcfg Database configuration data
     * @return void
     */
    public function __construct($dbcfg)
    {
        $this->_cfg = $dbcfg;

        $this->q_log['log'] = isset($dbcfg['log']) ? $dbcfg['log'] : false;

        $this->q_log['log_file'] = 
            isset($dbcfg['log_file']) ? $dbcfg['log_file'] : '';

        $this->dbfile = $dbcfg['dbfile'];

        if (isset($dbcfg['version'])) {
            $this->version = $dbcfg['version'];
        }

        try {
            switch ($this->version) {
            case 'pdo-3':
                $this->link = new PDO('sqlite:' . $this->dbfile);
                break;
            case '3': 
                $this->link = new SQLite3($this->dbfile);
                break;
            default:
                $this->link = new SQLiteDatabase($this->dbfile, 0777);
            }
        } catch (Exception $exception) {
            die($exception->getMessage());
        }
        if (!$this->link) {
            die("Sqlite connection error.");
        }
    }

    /**
     * Sanitize a sql string (convert quotes)
     *
     * Deprecated method
     *
     * @param string $string The string to sanitize
     * @return string The sanitized string
     */
    public function sql_string($string)
    {
        return str_replace("'", "\\'", stripslashes($string));
    }

    /**
     * Escape string for sqlite use
     *
     * @param string $string The string to be escaped
     * @return string The sanitized string
     */
    public function escape_string($string)
    {
        return sqlite_escape_string($string);
    }

    /**
     * Safely execute a sql query statement
     *
     * @param string $q The sql query statement
     * @param string $debug Enable debug mode
     * @param string $unbuf /dev/null
     * @return array|bool The resulting data or false
     */
    public function safe_query($q='', $debug='', $unbuf='')
    {
        if (!$q) {
            return false;
        }

        // Log the sql statement if logging is enabled
        if ($this->q_log['log']) {
            file_put_contents(
                $this->q_log['log_file'],
                date("m/d/Y H:i:s") . " ==>\n" . $q . "\n", FILE_APPEND
            );
        }

        if ($debug) {
            echo($q);
        }

        // Execute the query
        @$result = $this->link->query($q);

        // Log the error if any
        if ($this->q_log['log']) {
            $handle = fopen($this->q_log['log_file'], 'a');
            //fwrite($handle, "RESULT ==> ".$result."\n\n");
            $err = $this->link->lastError();
            if ($err) {
                fwrite($handle, "Error ==> ".$err."\n\n");
                $this->set_error($err . ": " . sqlite_error_string($err));
            }
            fclose($handle);
        }

        if (!$result) {
            return false;
        }
        return $result;
    }

    /**
     * Safely delete a row or rows from a table
     *
     * @param string $table The table name
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return bool Whether the statement executed successfully
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
     * Safely update row or rows in a table
     *
     * @param string $table The table name
     * @param string $set The set part of the query e.g. "col='value'"
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return bool Whether the statement executed successfully
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
     * @param string $set The set part of the query e.g. "VALUES (...)"
     * @param string $debug Enable debug mode
     * @return bool Whether the statement executed successfully
     */
    public function safe_insert($table, $set, $debug='')
    {
        $q = "insert into $table $set";
        if ($r = $this->safe_query($q, $debug)) {
            return true;
        }
        return false;
    }

    /**
     * Safely alter a table
     *
     * @param string $table The table name
     * @param string $alter The alter part of statement e.g. "ADD COLUMN ... "
     * @param string $debug Enable debug mode
     * @return bool Whether the statement executed successfully
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
     * @return bool Whether the statement executed successfully
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
     * @return bool Whether the statement executed successfully
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
     * Safely get a thing from a table based on a criteria
     *
     * @param string $thing The thing to extract
     * @param string $table The table name
     * @param string $where The where clause
     * @param string $debug Enable debug mode
     * @return mixed The data or false
     */
    public function safe_field($thing, $table, $where, $debug='')
    {
        $q = "select $thing from $table where $where";
        $r = $this->safe_query($q, $debug);
        if ($r->numRows() > 0) {
            return $r->fetchSingle();
        }
        return false;
    }

    /**
     * Safely extract column values from a row or rows
     *
     * @param string $thing The thing to extract
     * @param string $table the table name
     * @param string $where The where clause
     * @param mixed $debug Enable debug mode
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
     * @param mixed $debug Enable debug mode
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
     * @param mixed $debug Enable debug mode
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
     * @param mixed $debug Enable debug mode
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
     * @param mixed $debug Enable debug mode
     * @return mixed The first row matching the query or false
     */
    public function fetch($col, $table, $key, $val, $debug='')
    {
        $q = "select $col from $table "
            . "where $key = '" . sqlite_escape_string($val) . "' limit 1;";

        if ($r = $this->safe_query($q, $debug)) {
            return ($r->numRows() > 0) ? $r->fetchSingle() : '';
        }
        return false;
    }

    /**
     * Execute a sql query and return the first resulting row
     *
     * @param string $query The sql query statement
     * @param mixed $debug Enable debug mode
     * @param int $indices The array indices returned
     *                     (SQLITE_NUM, SQLITE_ASSOC, SQLITE_BOTH)
     * @return array|bool The resulting row or false
     */
    public function getRow($query, $debug='', $indices=SQLITE_ASSOC)
    {
        if ($r = $this->safe_query($query, $debug)) {
            return ($r->numRows() > 0) ? $r->fetch($indices) : false;
        }
        return false;
    }

    /**
     * Execute a sql query and return the resulting rows
     *
     * @param string $query The sql query statement
     * @param mixed $debug Enable debug mode
     * @param int $indices The array indices returned
     *                     (SQLITE_NUM, SQLITE_ASSOC, SQLITE_BOTH)
     * @return array|bool The resulting rows or false
     */
    public function getRows($query, $debug='', $indices=PDO::FETCH_ASSOC)
    {
        if ($r = $this->safe_query($query, $debug)) {
            $out  = array();
            $acfg = array($this->_cfg);

            while ($a = $r->fetchObject('Apricot_ModelRow', $acfg)) {
                $out[] = $a;
            }
            return $out;
        }
        return false;
    }

    /**
     * Execute a sql query and return the first column in the resulting row
     *
     * @param string $query The sql query statement
     * @param mixed $debug Enable debug mode
     * @return mixed The resulting thing or false
     */
    public function getThing($query, $debug='')
    {
        if ($r = $this->safe_query($query, $debug)) {
            return $r->fetchSingle();
        }
        return false;
    }

    /**
     * Return values of one column from multiple rows in an num indexed array
     * this one doesn't work because it expects sqlite_num
     * todo Fix this function to work
     *
     * @param string $query The sql statement
     * @param string $debug Enable debug mode
     * @return void
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
    public function get_insert_string($table, $a)
    {
        $q_cols = '';
        $q_vals = '';

        foreach ($a as $col=>$value) {
            $q_cols .= "$col,";
            $q_vals .= "'".sqlite_escape_string($value)."',";
        }

        $q_cols = substr($q_cols, 0, -1);
        $q_vals = substr($q_vals, 0, -1);

        $q = "INSERT INTO $table ("
            .$q_cols . ") "
            ."VALUES (" . $q_vals . ")";
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
    public function get_update_string($table, $a, $where)
    {
        if (!$where) {
            // enforce where clause to prevent unwanted data loss.
            return false;
        }
        $q_text = '';

        foreach ($a as $col=>$value) {
            $q_text .= "$col='" . sqlite_escape_string($value) . "', ";
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
    public function do_safe_query($q)
    {
        if ($result = $this->safe_query($q)) {
            return $this->link->lastInsertRowid();
        } else {
            return sqlite_error_string($this->link->lastError());
        }
    }

    /**
     * Set an error message
     *
     * @param string $error_message The error message
     * @return void
     */
    protected function set_error($error_message)
    {
        $this->errors = array_merge($this->errors, array($error_message));
    }

    /**
     * Get errors
     *
     * @return array An array of error messages that have been set
     */
    public function get_errors()
    {
        return $this->errors;
    }
}
