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
 * Pgsql database connection.
 *
 * @category   QuickPHP
 * @package    Database
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
class QuickPHP_Database_Driver_Postgre extends QuickPHP_Database_Abstract
{

    // Database in use by each connection
    protected static $_current_databases = array();

    // Identifier for this connection within the PHP driver
    protected $_connection_id;

    // Pgsql uses a backtick for identifiers
    protected $_identifier        = array('"');

    protected $_connection_string = NULL;

    protected $_escape_char       = '"';

    // clause and character used for LIKE escape sequences
    protected $_like_escape_str   = " ESCAPE '%s' ";
    protected $_like_escape_chr   = '!';

    public function connect()
    {
        if($this->_connection)
        {
            return TRUE;
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
            $port = !empty($port) ? $port : 5432;

            $this->_connection_string = "host=$hostname port=$port dbname=$database user=$username password=$password";

            if( ! empty($this->_config['charset']))
            {
                $connect_string .= " options='--client_encoding=".$this->_config['charset']."'";
            }

            if(empty($persistent))
            {
                $this->_connection = pg_connect($this->_connection_string);
            }
            else
            {
                $this->_connection = pg_pconnect($this->_connection_string);
            }

            self::$_current_databases[$this->_connection_id] = $database;
        }
        catch(ErrorException $e)
        {
            $this->_connection = NULL;
            throw $e;
        }

        $this->_connection_id = sha1($_connection_string);
    }

    /**
     * Select the database
     *
     * @param   string  Database
     * @return  void
     */
    protected function _select_db($database)
    {
        return true;
    }

    public function disconnect()
    {
        try
        {
            $status = true;

            if(is_resource($this->_connection))
            {
                $status = pg_close($this->_connection);

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

        if( ! empty($this->_config['connection']['persistent']) and $this->_config['connection']['database'] !== self::$_current_databases[$this->_connection_id])
        {
            $this->_select_db($this->_config['connection']['database']);
        }

        pg_get_result($result);

        if(($result = pg_query($this->_connection, $sql)) === false)
        {
            if(isset($benchmark))
            {
                Profiler::delete($benchmark);
            }

            throw new QuickPHP_Database_Exception('error', array($this->_connection->lastError(), $sql));
        }

        if(isset($benchmark))
        {
            rofiler::stop($benchmark);
        }

        $this->last_query = $sql;

        if(pg_result_status($result))
        {
            if($type === Database::SELECT)
            {
                return new Database_Driver_Postgre_Result($result, $sql, $as_object);
            }
            elseif($type === Database::INSERT)
            {
                return array(pg_last_oid($result), pg_affected_rows($result));
            }
            else
            {
                return pg_affected_rows($result);
            }
        }
        else
        {
            return false;
        }
    }

    public function datatype($type)
    {
        return parent::datatype($type);
    }

    public function list_tables($like = null)
    {
        if(is_string($like))
        {
            $result = $this->query(Database::SELECT,
                "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name LIKE '"
                .$this->escape($like)."%' ".sprintf($this->_like_escape_str, $this->_like_escape_char), FALSE);
        }
        else
        {
            $result = $this->query(Database::SELECT, "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'", false);
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
        $table = $this->quote_table($table);

        if(is_string($like))
        {
            $result = $this->query(Database::SELECT, "SELECT * FROM information_schema.columns WHERE table_name ='".$table."'" . ' LIKE ' . $this->quote($like) . '%', FALSE);
        }
        else
        {
            $result = $this->query(Database::SELECT, "SELECT * FROM information_schema.columns WHERE table_name ='".$table."'", false);
        }

        $columns = array();

        foreach ($result as $key=>$row)
        {

            list ($type, $length) = $this->_parse_type($row['Type']);

            $column                     = $this->datatype($row['data_type']);
            $column['column_name']      = $row['column_name'];
            $column['column_default']   = $row['column_default'];
            $column['data_type']        = $row['data_type'];
            $column['is_nullable']      = (bool) ($row['is_nullable'] == 'YES');
            $column['ordinal_position'] = $row['ordinal_position'];

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
                            $column['character_maximum_length'] = $row['character_maximum_length'];
                            break;
                        case 'char' :
                        case 'character varying' :
                            $column['character_maximum_length'] = $row['character_maximum_length'];
                        case 'text' :
                        case 'tinytext' :
                        case 'mediumtext' :
                        case 'longtext' :
                            $column['collation_name'] = $row['Collation'];
                            break;
                        case 'enum' :
                        case 'set' :
                            $column['collation_name'] = $row['Collation'];
                            $column['options']        = explode('\',\'', substr($length, 1, - 1));
                            break;
                    }
                    break;
            }

            $column['comment']            = $row['Comment'];
            $column['extra']              = $row['Extra'];
            $column['key']                = $row['Key'];
            $column['privileges']         = $row['Privileges'];
            $columns[$row['column_name']] = $column;
        }

        return $columns;
    }

    public function escape($value)
    {
        $this->_connection or $this->connect();

        if(($value = pg_escape_string($this->_connection, $value)) === FALSE)
        {
            throw new QuickPHP_Database_Exception('error', array(pg_last_notice($this->_connection)), pg_last_error($this->_connection));
        }

        return "'$value'";
    }
}
