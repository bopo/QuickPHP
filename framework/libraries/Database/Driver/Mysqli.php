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
class QuickPHP_Database_Driver_Mysqli extends Database_Driver_Mysql
{
    public function connect()
    {
        if($this->_connection)
        {
            return true;
        }

        if(Database_Driver_Mysqli::$_set_names === null)
        {
            Database_Driver_Mysqli::$_set_names = ! function_exists('mysqli_set_charset');
        }

        extract($this->_config['connection'] + array(
            'database'   => '',
            'hostname'   => '',
            'port'       => null,
            'socket'     => null,
            'username'   => '',
            'password'   => '',
            'persistent' => false));

        unset($this->_config['connection']['username'], $this->_config['connection']['password']);

        try
        {
            $this->_connection = mysqli_connect($hostname, $username, $password);
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

    protected function _select_db($database)
    {
        if( ! mysqli_select_db($this->_connection, $database))
        {
            throw new Database_Exception('error', array(mysqli_error($this->_connection)));
        }

        self::$_current_databases[$this->_connection_id] = $database;
    }

    public function disconnect()
    {
        try
        {
            $status = true;

            if(is_resource($this->_connection))
            {
                $status = mysqli_close($this->_connection);

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

        if(Database_Driver_Mysqli::$_set_names === true)
        {
            $status = (bool) mysqli_query($this->_connection, 'SET NAMES ' . $this->quote($charset));
        }
        else
        {
            $status = (bool) mysqli_set_charset($this->_connection, $charset);
        }

        if($status === false)
        {
            throw new Database_Exception('error', array(mysqli_error($this->_connection)));
        }
    }

    public function query($type, $sql, $as_object)
    {
        $this->_connection or $this->connect();

        if( ! empty($this->_config['profiling']))
        {
            $benchmark = Profiler::start("Database ({$this->_instance})", $sql);
        }

        if( ! empty($this->_config['connection']['persistent'])
            and $this->_config['connection']['database'] !== Database_Driver_Mysqli::$_current_databases[$this->_connection_id])
        {
            $this->_select_db($this->_config['connection']['database']);
        }

        if(($result = mysqli_query($this->_connection, $sql)) === false)
        {
            if(isset($benchmark))
            {
                Profiler::delete($benchmark);
            }

            throw new Database_Exception('error', array(mysqli_error($this->_connection), $sql));
        }

        if(isset($benchmark))
        {
            Profiler::stop($benchmark);
        }

        $this->last_query = $sql;

        if($type === Database::SELECT)
        {
            return new Database_Driver_Mysqli_Result($result, $sql, $as_object);
        }
        elseif($type === Database::INSERT)
        {
            return array(mysqli_insert_id($this->_connection), mysqli_affected_rows($this->_connection));
        }
        else
        {
            return mysqli_affected_rows($this->_connection);
        }
    }

    public function escape($value)
    {
        $this->_connection or $this->connect();

        if(($value = mysqli_real_escape_string($this->_connection, (string) $value)) === false)
        {
            throw new Database_Exception('error', array(mysqli_error($this->_connection)));
        }

        return "'$value'";
    }
}
