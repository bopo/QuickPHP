<?php defined('SYSPATH') or die('No direct access allowed.');
/*
 +----------------------------------------------------------------------+
 | QuickPHP Framework Version 0.10                                      |
 +----------------------------------------------------------------------+
 | Copyright (c) 2010 QuickPHP.net All rights reserved.                 |
 +----------------------------------------------------------------------+
 | Licensed under the Apache License, Version 2.0 (the 'License');      |
 | you may not use this file except in compliance with the License.     |
 | You may obtain a copy of the License at                              |
 | http://www.apache.org/licenses/LICENSE-2.0                           |
 | Unless required by applicable law or agreed to in writing, software  |
 | distributed under the License is distributed on an 'AS IS' BASIS,    |
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
 | implied. See the License for the specific language governing         |
 | permissions and limitations under the License.                       |
 +----------------------------------------------------------------------+
 | Author: BoPo <ibopo@126.com>                                         |
 +----------------------------------------------------------------------+
*/
/**
 * Database connection wrapper. All database object instances are referenced
 * by a name. Queries are typically handled by [Database_Query], rather than
 * using the database object directly.
 *
 * @category   QuickPHP
 * @package    Database
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
abstract class QuickPHP_Database_Abstract
{

    /**
     * @var  string  the last query executed
     */
    public $last_query;

    // Character that is used to quote identifiers
    protected $_identifier = '"';

    // instance name
    protected $_instance;

    // Raw server connection
    protected $_connection;

    // Configuration array
    protected $_config;

    /**
     * Stores the database configuration locally and name the instance.
     *
     * [!!] This method cannot be accessed directly, you must use [Database::instance].
     *
     * @return  void
     */
    public function __construct($name, array $config)
    {
        $this->_instance = $name;
        $this->_config   = $config;
    }

    /**
     * Disconnect from the database when the object is destroyed.
     *
     * // Destroy the database instance
     * unset(Database::instances[(string) $db], $db);
     *
     * [!!] Calling `unset($db)` is not enough to destroy the database, as it
     * will still be stored in `Database::$_instances`.
     *
     * @return  void
     */
    final public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Returns the database instance name.
     *
     * echo (string) $db;
     *
     * @return  string
     */
    final public function __toString()
    {
        return $this->_instance;
    }

    /**
     * Connect to the database. This is called automatically when the first
     * query is executed.
     *
     * $db->connect();
     *
     * @throws  Database_Exception
     * @return  void
     */
    abstract public function connect();

    /**
     * Disconnect from the database. This is called automatically by [Database::__destruct].
     *
     * $db->disconnect();
     *
     * @return  boolean
     */
    abstract public function disconnect();

    /**
     * Set the connection character set. This is called automatically by [Database::connect].
     *
     * $db->set_charset('utf8');
     *
     * @throws  Database_Exception
     * @param   string   character set name
     * @return  void
     */
    abstract public function set_charset($charset);

    /**
     * Perform an SQL query of the given type.
     *
     * // Make a SELECT query and use objects for results
     * $db->query(Database::SELECT, 'SELECT * FROM groups', TRUE);
     *
     * // Make a SELECT query and use "Model_User" for the results
     * $db->query(Database::SELECT, 'SELECT * FROM users LIMIT 1', 'Model_User');
     *
     * @param   integer  Database::SELECT, Database::INSERT, etc
     * @param   string   SQL query
     * @param   mixed    result object class, TRUE for stdClass, FALSE for assoc array
     * @return  object   Database_Result for SELECT queries
     * @return  array    list (insert id, row count) for INSERT queries
     * @return  integer  number of affected rows for all other queries
     */
    abstract public function query($type, $sql, $as_object);

    /**
     * Count the number of records in the last query, without LIMIT or OFFSET applied.
     *
     * // Get the total number of records that match the last query
     * $count = $db->count_last_query();
     *
     * @return  integer
     */
    public function count_last_query()
    {
        $sql = $this->last_query;

        if( ! empty($sql))
        {
            $sql = trim($sql);

            if(stripos($sql, 'SELECT') !== 0)
            {
                return FALSE;
            }

            if(stripos($sql, 'LIMIT') !== FALSE)
            {
                $sql = preg_replace('/\sLIMIT\s+[^a-z]+/i', ' ', $sql);
            }

            if(stripos($sql, 'OFFSET') !== FALSE)
            {
                $sql = preg_replace('/\sOFFSET\s+\d+/i', '', $sql);
            }

            $result = $this->query(Database::SELECT, 'SELECT COUNT(*) AS ' . $this->quote_identifier('total_rows') . ' ' . 'FROM (' . $sql . ') AS ' . $this-quote_table('counted_results'), TRUE);

            return (int) $result->current()->total_rows;
        }

        return FALSE;
    }

    /**
     * Count the number of records in a table.
     *
     * // Get the total number of records in the "users" table
     * $count = $db->count_records('users');
     *
     * @param   mixed    table name string or array(query, alias)
     * @return  integer
     */
    public function count_records($table)
    {
        $table = $this->quote_identifier($table);
        return $this->query(Database::SELECT, 'SELECT COUNT(*) AS total_row_count FROM ' . $table, FALSE)->get('total_row_count');
    }

    /**
     * Returns a normalized array describing the SQL data type
     *
     * $db->datatype('char');
     *
     * @param   string  SQL data type
     * @return  array
     */
    public function datatype($type)
    {
        static $types = array(
            // SQL-92
            'bit'                             => array('type' => 'string', 'exact' => TRUE),
            'bit varying'                     => array('type' => 'string'),
            'char'                            => array('type' => 'string', 'exact' => TRUE),
            'char varying'                    => array('type' => 'string'),
            'character'                       => array('type' => 'string', 'exact' => TRUE),
            'character varying'               => array('type' => 'string'),
            'date'                            => array('type' => 'string'),
            'dec'                             => array('type' => 'float', 'exact' => TRUE),
            'decimal'                         => array('type' => 'float', 'exact' => TRUE),
            'double precision'                => array('type' => 'float'),
            'float'                           => array('type' => 'float'),
            'int'                             => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
            'integer'                         => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
            'interval'                        => array('type' => 'string'),
            'national char'                   => array('type' => 'string', 'exact' => TRUE),
            'national char varying'           => array('type' => 'string'),
            'national character'              => array('type' => 'string', 'exact' => TRUE),
            'national character varying'      => array('type' => 'string'),
            'nchar'                           => array('type' => 'string', 'exact' => TRUE),
            'nchar varying'                   => array('type' => 'string'),
            'numeric'                         => array('type' => 'float', 'exact' => TRUE),
            'real'                            => array('type' => 'float'),
            'smallint'                        => array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
            'time'                            => array('type' => 'string'),
            'time with time zone'             => array('type' => 'string'),
            'timestamp'                       => array('type' => 'string'),
            'timestamp with time zone'        => array('type' => 'string'),
            'varchar'                         => array('type' => 'string'),
            
            // SQL:1999
            'binary large object'             => array('type' => 'string', 'binary' => TRUE),
            'blob'                            => array('type' => 'string', 'binary' => TRUE),
            'boolean'                         => array('type' => 'bool'),
            'char large object'               => array('type' => 'string'),
            'character large object'          => array('type' => 'string'),
            'clob'                            => array('type' => 'string'),
            'national character large object' => array('type' => 'string'),
            'nchar large object'              => array('type' => 'string'),
            'nclob'                           => array('type' => 'string'),
            'time without time zone'          => array('type' => 'string'),
            'timestamp without time zone'     => array('type' => 'string'),

            // SQL:2003
            'bigint'                          => array('type' => 'int',
            'min'                             => '-9223372036854775808',
            'max'                             => '9223372036854775807'),

            // SQL:2008
            'binary'                          => array('type' => 'string', 'binary' => TRUE, 'exact' => TRUE),
            'binary varying'                  => array('type' => 'string', 'binary' => TRUE),
            'varbinary'                       => array('type' => 'string', 'binary' => TRUE),
        );

        if (isset($types[$type]))
        {
            return $types[$type];
        }

        return array();
    }

    /**
     * List all of the tables in the database. Optionally, a LIKE string can
     * be used to search for specific tables.
     *
     * // Get all tables in the current database
     * $tables = $db->list_tables();
     *
     * // Get all user-related tables
     * $tables = $db->list_tables('user%');
     *
     * @param   string   table to search for
     * @return  array
     */
    abstract public function list_tables($like = NULL);

    /**
     * Lists all of the columns in a table. Optionally, a LIKE string can be
     * used to search for specific fields.
     *
     * // Get all columns from the "users" table
     * $columns = $db->list_columns('users');
     *
     * // Get all name-related columns
     * $columns = $db->list_columns('users', '%name%');
     *
     * @param   string  table to get columns from
     * @param   string  column to search for
     * @return  array
     */
    abstract public function list_columns($table, $like = NULL);

    /**
     * Extracts the text between parentheses, if any.
     *
     * // Returns: array('CHAR', '6')
     * list($type, $length) = $db->_parse_type('CHAR(6)');
     *
     * @param   string
     * @return  array   list containing the type and length, if any
     */
    protected function _parse_type($type)
    {
        if(($open = strpos($type, '(')) === FALSE)
        {
            return array($type, NULL);
        }

        $close  = strpos($type, ')', $open);
        $length = substr($type, $open + 1, $close - 1 - $open);
        $type   = substr($type, 0, $open) . substr($type, $close + 1);

        return array($type, $length);
    }

    /**
     * Return the table prefix defined in the current configuration.
     *
     * $prefix = $db->table_prefix();
     *
     * @return  string
     */
    public function table_prefix()
    {
        return $this->_config['table_prefix'];
    }

    public function table_exists($table = NULL, $like = NULL)
    {
        return in_array($table, $this->list_tables($like));
    }

    /**
     * Quote a value for an SQL query.
     *
     * $db->quote(NULL);   // 'NULL'
     * $db->quote(10);     // 10
     * $db->quote('fred'); // 'fred'
     *
     * Objects passed to this function will be converted to strings.
     * [Database_Expression] objects will use the value of the expression.
     * [Database_Query] objects will be compiled and converted to a sub-query.
     * All other objects will be converted using the `__toString` method.
     *
     * @param   mixed   any value to quote
     * @return  string
     * @uses    Database::escape
     */
    public function quote($value)
    {
        if($value === NULL)
        {
            return 'NULL';
        }
        elseif($value === TRUE)
        {
            return "'1'";
        }
        elseif($value === FALSE)
        {
            return "'0'";
        }
        elseif(is_object($value))
        {
            if($value instanceof Database_Query)
            {
                return '(' . $value->compile($this) . ')';
            }
            elseif($value instanceof Database_Expression)
            {
                return $value->value();
            }
            else
            {
                return $this->quote((string) $value);
            }
        }
        elseif(is_array($value))
        {
            return '(' . implode(', ', array_map(array($this, __FUNCTION__), $value)) . ')';
        }
        elseif(is_int($value))
        {
            return (int) $value;
        }
        elseif(is_float($value))
        {
            return sprintf('%F', $value);
        }

        return $this->escape($value);
    }

    /**
     * Quote a database table name and adds the table prefix if needed.
     *
     * $table = $db->quote_table($table);
     *
     * @param   mixed   table name or array(table, alias)
     * @return  string
     * @uses    Database::quote_identifier
     * @uses    Database::table_prefix
     */
    public function quote_table($value)
    {
        if(is_array($value))
        {
            $table = & $value[0];
        }
        else
        {
            $table = & $value;
        }

        if(is_string($table) and strpos($table, '.') === FALSE)
        {
            $table = $this->table_prefix() . $table;
        }

        return $this->quote_identifier($value);
    }

    /**
     * Quote a database identifier, such as a column name. Adds the
     * table prefix to the identifier if a table name is present.
     *
     * $column = $db->quote_identifier($column);
     *
     * You can also use SQL methods within identifiers.
     *
     * // The value of "column" will be quoted
     * $column = $db->quote_identifier('COUNT("column")');
     *
     * Objects passed to this function will be converted to strings.
     * [Database_Expression] objects will use the value of the expression.
     * [Database_Query] objects will be compiled and converted to a sub-query.
     * All other objects will be converted using the `__toString` method.
     *
     * @param   mixed   any identifier
     * @return  string
     * @uses    Database::table_prefix
     */
    public function quote_identifier($value)
    {
        if($value === '*')
        {
            return $value;
        }
        elseif(is_object($value))
        {
            if($value instanceof Database_Query)
            {
                return '(' . $value->compile($this) . ')';
            }
            elseif($value instanceof Database_Expression)
            {
                return $value->value();
            }
            else
            {
                return $this->quote_identifier((string) $value);
            }
        }
        elseif(is_array($value))
        {
            list ($value, $alias) = $value;
            return $this->quote_identifier($value) . ' AS ' . $this->quote_identifier($alias);
        }

        if(strpos($value, '"') !== FALSE)
        {
            return preg_replace('/"(.+?)"/e', '$this->quote_identifier("$1")', $value);
        }
        elseif(strpos($value, '.') !== FALSE)
        {
            $parts  = explode('.', $value);
            $prefix = $this->table_prefix();

            if( ! empty($prefix))
            {
                $offset         = count($parts) - 2;
                $parts[$offset] = $prefix . $parts[$offset];
            }

            return implode('.', array_map(array($this, __FUNCTION__), $parts));
        }
        else
        {
            if( ! empty($this->_identifier))
            {
                if(count($this->_identifier) > 1)
                {
                    list($_identifier_l, $_identifier_r) = $this->_identifier;
                }
                else
                {
                    list($_identifier_l, $_identifier_r) = array(current($this->_identifier),end($this->_identifier));
                }

                return $_identifier_l . $value . $_identifier_r;
            }

            return $value;
        }
    }

    /**
     * Sanitize a string by escaping characters that could cause an SQL
     * injection attack.
     *
     * $value = $db->escape('any string');
     *
     * @param   string   value to quote
     * @return  string
     */
    abstract public function escape($value);
}