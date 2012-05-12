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
 * MySQL database connection.
 *
 * @category   QuickPHP
 * @package    Database
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
class QuickPHP_Database_Driver_Mysql extends QuickPHP_Database_Abstract
{

    // Database in use by each connection
    protected static $_current_databases = array();

    // Use SET NAMES to set the character set
    protected static $_set_names;

    // Identifier for this connection within the PHP driver
    protected $_connection_id;

    // MySQL uses a backtick for identifiers
    protected $_identifier = array('`');

    public function connect()
    {
        if($this->_connection)
        {
            return true;
        }

        if(Database_Driver_Mysql::$_set_names === NULL)
        {
            Database_Driver_Mysql::$_set_names = ! function_exists('mysql_set_charset');
        }

        extract($this->_config['connection'] + array(
            'database'   => '',
            'hostname'   => '',
            'port'       => NULL,
            'socket'     => NULL,
            'username'   => '',
            'password'   => '',
            'persistent' => FALSE));

        unset($this->_config['connection']['username'], $this->_config['connection']['password']);

        try
        {
            if(empty($persistent))
            {
                $this->_connection = mysql_connect($hostname, $username, $password, true);
            }
            else
            {
                $this->_connection = mysql_pconnect($hostname, $username, $password);
            }
        }
        catch(ErrorException $e)
        {
            $this->_connection = null;
            throw $e;
        }

        $this->_connection_id = sha1($hostname . '_' . $username . '_' . $password);
        $this->_select_db($database);

        if( ! empty($this->_config['charset']))
        {
            $this->set_charset($this->_config['charset']);
        }
    }

    /**
     * Select the database
     *
     * @param   string  Database
     * @return  void
     */
    protected function _select_db($database)
    {
        if( ! mysql_select_db($database, $this->_connection))
        {
            throw new QuickPHP_Database_Exception('connection', array(mysql_error($this->_connection), mysql_errno($this->_connection)));
        }

        self::$_current_databases[$this->_connection_id] = $database;
    }

    /**
     * 关闭数据库链接(non-PHPdoc)
     * @see framework/libraries/Database/QuickPHP_Database_Abstract::disconnect()
     */
    public function disconnect()
    {
        try
        {
            $status = true;

            if(is_resource($this->_connection))
            {
                $status = mysql_close($this->_connection);

                if( ! empty($status))
                {
                    $this->_connection = null;
                }
            }
        }
        catch(Exception $e)
        {
            $status = ! is_resource($this->_connection);
        }

        return $status;
    }

    /**
     * 设置字符集(non-PHPdoc)
     * @see framework/libraries/Database/QuickPHP_Database_Abstract::set_charset()
     */
    public function set_charset($charset)
    {
        $this->_connection or $this->connect();

        if(Database_Driver_Mysql::$_set_names === true)
        {
            $status = (bool) mysql_query('SET NAMES ' . $this->quote($charset), $this->_connection);
        }
        else
        {
            $status = (bool) mysql_set_charset($charset, $this->_connection);
        }

        if($status === false)
        {
            throw new QuickPHP_Database_Exception('error', array(mysql_error($this->_connection), mysql_errno($this->_connection)));
        }
    }

    /**
     * 数据库请求操作(non-PHPdoc)
     * @see framework/libraries/Database/QuickPHP_Database_Abstract::query()
     */
    public function query($type, $sql, $as_object)
    {
        $this->_connection or $this->connect();

        if( ! empty($this->_config['profiling']))
        {
            $benchmark = Profiler::start("Database ({$this->_instance})", $sql);
        }

        if( ! empty($this->_config['connection']['persistent']) and $this->_config['connection']['database'] !== Database_Driver_Mysql::$_current_databases[$this->_connection_id])
        {
            $this->_select_db($this->_config['connection']['database']);
        }

        if(($result = mysql_query($sql, $this->_connection)) === false)
        {
            if(isset($benchmark))
            {
                Profiler::delete($benchmark);
            }

            throw new QuickPHP_Database_Exception('invalid_query', array(mysql_error($this->_connection), $sql), $sql);
        }

        if(isset($benchmark))
        {
            Profiler::stop($benchmark);
        }

        $this->last_query = $sql;

        if($type === Database::SELECT)
        {
            return new Database_Driver_Mysql_Result($result, $sql, $as_object);
        }
        elseif($type === Database::INSERT)
        {
            return array(mysql_insert_id($this->_connection), mysql_affected_rows($this->_connection));
        }
        else
        {
            return mysql_affected_rows($this->_connection);
        }
    }

    /**
     * 数据类型(non-PHPdoc)
     * @see framework/libraries/Database/QuickPHP_Database_Abstract::datatype()
     */
    public function datatype($type)
    {
        static $types = array
        (
            'blob'                      => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '65535'),
            'bool'                      => array('type' => 'bool'),
            'bigint unsigned'           => array('type' => 'int', 'min' => '0', 'max' => '18446744073709551615'),
            'datetime'                  => array('type' => 'string'),
            'decimal unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
            'double'                    => array('type' => 'float'),
            'double precision unsigned' => array('type' => 'float', 'min' => '0'),
            'double unsigned'           => array('type' => 'float', 'min' => '0'),
            'enum'                      => array('type' => 'string'),
            'fixed'                     => array('type' => 'float', 'exact' => TRUE),
            'fixed unsigned'            => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
            'float unsigned'            => array('type' => 'float', 'min' => '0'),
            'int unsigned'              => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
            'integer unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '4294967295'),
            'longblob'                  => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '4294967295'),
            'longtext'                  => array('type' => 'string', 'character_maximum_length' => '4294967295'),
            'mediumblob'                => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '16777215'),
            'mediumint'                 => array('type' => 'int', 'min' => '-8388608', 'max' => '8388607'),
            'mediumint unsigned'        => array('type' => 'int', 'min' => '0', 'max' => '16777215'),
            'mediumtext'                => array('type' => 'string', 'character_maximum_length' => '16777215'),
            'national varchar'          => array('type' => 'string'),
            'numeric unsigned'          => array('type' => 'float', 'exact' => TRUE, 'min' => '0'),
            'nvarchar'                  => array('type' => 'string'),
            'point'                     => array('type' => 'string', 'binary' => TRUE),
            'real unsigned'             => array('type' => 'float', 'min' => '0'),
            'set'                       => array('type' => 'string'),
            'smallint unsigned'         => array('type' => 'int', 'min' => '0', 'max' => '65535'),
            'text'                      => array('type' => 'string', 'character_maximum_length' => '65535'),
            'tinyblob'                  => array('type' => 'string', 'binary' => TRUE, 'character_maximum_length' => '255'),
            'tinyint'                   => array('type' => 'int', 'min' => '-128', 'max' => '127'),
            'tinyint unsigned'          => array('type' => 'int', 'min' => '0', 'max' => '255'),
            'tinytext'                  => array('type' => 'string', 'character_maximum_length' => '255'),
            'year'                      => array('type' => 'string'),
        );

        $type = str_replace(' zerofill', '', $type);

        if (isset($types[$type]))
        {
            return $types[$type];
        }

        return parent::datatype($type);
    }

    /**
     * 数据库中的列表(non-PHPdoc)
     * @see framework/libraries/Database/QuickPHP_Database_Abstract::list_tables()
     */
    public function list_tables($like = NULL)
    {
        // @todo 增加缓存

        if(is_string($like))
        {
            $sql = 'SHOW TABLES LIKE ' . $this->quote($like);
        }
        else
        {
            $sql = 'SHOW TABLES';
        }

        /** @todo 产品模式增加缓存 */
        if(IN_PRODUCTION == true)
        {
            $cache_key = 'Database::list_tables("' . $db . '", "' . $sql . '")';
            $tables    = Cache::instance()->get($cache_key);

            if(!empty($tables))
            {
                return $tables;
            }
        }

        $result = $this->query(Database::SELECT, $sql, false);
        $tables = array();

        foreach ($result as $row)
        {
            $tables[] = reset($row);
        }

        /** @todo 产品模式增加缓存 */
        if(isset($cache_key))
        {
            Cache::instance()->set($cache_key, $tables, 0);
        }

        return $tables;
    }

    /**
     * 列出表中的字段(non-PHPdoc)
     * @see framework/libraries/Database/QuickPHP_Database_Abstract::list_columns()
     */
    public function list_columns($table, $like = NULL)
    {
        $table = $this->quote_table($table);

        if(is_string($like))
        {
            $sql = 'SHOW FULL COLUMNS FROM ' . $table . ' LIKE ' . $this->quote($like);
        }
        else
        {
            $sql = 'SHOW FULL COLUMNS FROM ' . $table;
        }

        if(IN_PRODUCTION == true)
        {
            $cache_key = 'Database::list_tables("' . $db . '", "' . $sql . '")';
            $columns   = Cache::instance()->get($cache_key);

            if(!empty($columns))
            {
                return $columns;
            }
        }

        $result  = $this->query(Database::SELECT, $sql, FALSE);
        $count   = 0;
        $columns = array();

        foreach ($result as $row)
        {
            list ($type, $length) = $this->_parse_type($row['Type']);

            $column = $this->datatype($type);

            $column['column_name']      = $row['Field'];
            $column['column_default']   = $row['Default'];
            $column['data_type']        = $type;
            $column['is_nullable']      = ($row['Null'] == 'YES');
            $column['ordinal_position'] = ++$count;

            switch ($column['type'])
            {
                case 'float' :
                    if(isset($length))
                    {
                        list ($column['numeric_precision'], $column['numeric_scale']) = explode(',', $length);
                    }
                    break;
                case 'int' :
                    if(isset($length))
                    {
                        $column['display'] = $length;
                    }
                    break;
                case 'string' :
                    switch ($column['data_type'])
                    {
                        case 'binary' :
                        case 'varbinary' :
                            $column['character_maximum_length'] = $length;
                            break;
                        case 'char' :
                        case 'varchar' :
                            $column['character_maximum_length'] = $length;
                        case 'text' :
                        case 'tinytext' :
                        case 'mediumtext' :
                        case 'longtext' :
                            $column['collation_name'] = $row['Collation'];
                            break;
                        case 'enum' :
                        case 'set' :
                            $column['collation_name'] = $row['Collation'];
                            $column['options'] = explode('\',\'', substr($length, 1, - 1));
                            break;
                    }
                    break;
            }

            $column['comment']      = $row['Comment'];
            $column['extra']        = $row['Extra'];
            $column['key']          = $row['Key'];
            $column['privileges']   = $row['Privileges'];
            $columns[$row['Field']] = $column;
        }

        /** @todo 产品模式增加缓存 */
        if(isset($cache_key))
        {
            Cache::instance()->set($cache_key, $columns, 0);
        }

        return $columns;
    }

    /**
     * 请求转码(non-PHPdoc)
     * @see framework/libraries/Database/QuickPHP_Database_Abstract::escape()
     */
    public function escape($value)
    {
        $this->_connection or $this->connect();

        if(($value = mysql_real_escape_string((string) $value, $this->_connection)) === FALSE)
        {
            throw new QuickPHP_Database_Exception('error', array(mysql_error($this->_connection)));
        }

        return "'$value'";
    }
}