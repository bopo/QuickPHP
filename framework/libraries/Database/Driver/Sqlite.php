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
class QuickPHP_Database_Driver_SQLite extends QuickPHP_Database_Abstract
{
    protected static $_current_databases = array();

    // Use SET NAMES to set the character set
    protected static $_set_names;

    // Identifier for this connection within the PHP driver
    protected $_connection_id;

    // MySQL uses a backtick for identifiers
    protected $_identifier = array('[',']');

    public function connect()
    {
        if($this->_connection)
            return TRUE;

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
                $this->_connection = sqlite_open($database, 0666, $error);
            }
            else
            {
                $this->_connection = sqlite_popen($database, 0666, $error);
            }
        }
        catch(SQLiteException $e)
        {

            $this->_connection = NULL;
            throw $e;
        }

        $this->_connection_id = sha1($hostname . '_' . $username . '_' . $password);
    }

    /**
     * Select the database
     *
     * @param   string  Database
     * @return  void
     */
    protected function _select_db($database)
    {
        return TRUE;
    }

    public function disconnect()
    {
        try
        {
            $status = TRUE;

            if(is_resource($this->_connection))
            {
                $status = sqlite_close($this->_connection);

                if( ! empty($status))
                {
                    $this->_connection = NULL;
                }
            }
        }
        catch(Exception $e)
        {
            $status = ! is_resource($this->_connection);
        }

        return $status;
    }

    public function set_charset($charset)
    {
        $this->_connection or $this->connect();
        return TRUE;
    }

    public function query($type, $sql, $as_object)
    {
        $this->_connection or $this->connect();

        if( ! empty($this->_config['profiling']))
        {
            $benchmark = Profiler::start("Database ({$this->_instance})", $sql);
        }

        if( ! empty($this->_config['connection']['persistent']) AND $this->_config['connection']['database'] !== self::$_current_databases[$this->_connection_id])
        {
            $this->_select_db($this->_config['connection']['database']);
        }

        $error_message = NULL;

        $result = sqlite_query($this->_connection, $sql, SQLITE_ASSOC);

        if( ! empty($error))
        {
            if(isset($benchmark))
            {
                Profiler::delete($benchmark);
            }

            throw new QuickPHP_Database_Exception('error', array($this->_connection->lastError(), $sql));
        }

        if(isset($benchmark))
        {
            Profiler::stop($benchmark);
        }

        $this->last_query = $sql;

        if($type === Database::SELECT)
        {
            return new Database_Driver_SQLite_Result($result, $sql, $as_object);
        }
        elseif($type === Database::INSERT)
        {
            return array(sqlite_last_insert_rowid($this->_connection), sqlite_changes($this->_connection));
        }
        else
        {
            return sqlite_changes($this->_connection);
        }
    }

    public function datatype($type)
    {
        return parent::datatype($type);
    }

    public function list_tables($like = NULL)
    {
        if(is_string($like))
        {
            $result = $this->query(Database::SELECT, "SELECT name from [sqlite_master] WHERE [type]='table'", FALSE);
        }
        else
        {
            $result = $this->query(Database::SELECT, "SELECT name from [sqlite_master] WHERE [type]='table'", FALSE);
        }

        $tables = array();

        foreach ($result as $row)
        {
            $tables[] = reset($row);
        }

        return $tables;
    }

    public function list_columns($table, $like = NULL)
    {
        if(is_string($like))
        {
            $result = $this->query(Database::SELECT, 'SELECT * FROM ' . $this->quote_table($table) . ' LIKE ' . $this->quote($like) . ' LIMIT 1', FALSE);
        }
        else
        {
            $result = $this->query(Database::SELECT, 'SELECT * FROM ' . $this->quote_table($table) . ' LIMIT 1', FALSE);
        }

        $columns = sqlite_fetch_column_types($table, $this->_connection ,SQLITE_ASSOC);//->fetchColumnTypes($table, SQLITE_ASSOC);
        $count   = 0;

        foreach ($columns as $name => $type)
        {
            $name = strtolower($name);
            $type = strtolower($type);

            list ($type, $length)       = $this->_parse_type($type);
            $column                     = $this->datatype($type);
            $column['column_name']      = $name;
            $column['data_type']        = $type;
            $column['ordinal_position'] = ++$count;

            switch ($column['type'])
            {
                case 'float' :
                    if(isset($length))
                    {
                        list($column['numeric_precision'], $column['numeric_scale']) = explode(',', $length);
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

            $columns[$name] = $column;
        }

        return $columns;
    }

    public function escape($value)
    {
        $this->_connection or $this->connect();
        $value = (sqlite_escape_string($value));

        if( (bool) ($value = sqlite_escape_string($value)) === FALSE)
        {
            throw new QuickPHP_Database_Exception('error', array(sqlite_last_error($this->_connection)), $sql);
        }

        return "'$value'";
    }
}