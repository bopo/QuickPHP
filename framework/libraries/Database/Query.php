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
 * QuickPHP 数据库的请求封装.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Query.php 8641 2012-01-05 08:35:39Z bopo $
 */
class QuickPHP_Database_Query
{

    // Query type
    protected $_type;

    // Cache lifetime
    protected $_lifetime;

    // SQL statement
    protected $_sql;

    // Quoted query parameters
    protected $_parameters = array();
    
    // Return results as associative arrays or objects
    protected $_as_object  = FALSE;

    /**
     * Creates a new SQL query of the specified type.
     *
     * @param   integer  query type: Database::SELECT, Database::INSERT, etc
     * @param   string   query string
     * @return  void
     */
    public function __construct($type, $sql)
    {
        $this->_type = $type;
        $this->_sql  = $sql;
    }

    /**
     * Return the SQL query string.
     *
     * @return  string
     */
    final public function __toString()
    {
        try
        {
            return $this->compile(Database::instance());
        }
        catch(Exception $e)
        {
            return QuickPHP_Exception::text($e);
        }
    }

    /**
     * Get the type of the query.
     *
     * @return  integer
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * Enables the query to be cached for a specified amount of time.
     *
     * @param   integer  number of seconds to cache or null for default
     * @return  $this
     */
    public function cached($lifetime = NULL)
    {
        $this->_lifetime = $lifetime;
        return $this;
    }

    /**
     * Returns results as associative arrays
     *
     * @return  $this
     */
    public function as_assoc()
    {
        $this->_as_object = FALSE;
        return $this;
    }

    /**
     * Returns results as objects
     *
     * @param   string  classname or TRUE for stdClass
     * @return  $this
     */
    public function as_object($class = TRUE)
    {
        $this->_as_object = $class;
        return $this;
    }

    /**
     * Set the value of a parameter in the query.
     *
     * @param   string   parameter key to replace
     * @param   mixed    value to use
     * @return  $this
     */
    public function param($param, $value)
    {
        $this->_parameters[$param] = $value;
        return $this;
    }

    /**
     * Bind a variable to a parameter in the query.
     *
     * @param   string  parameter key to replace
     * @param   mixed   variable to use
     * @return  $this
     */
    public function bind($param, $var)
    {
        $this->_parameters[$param] = $var;
        return $this;
    }

    /**
     * Add multiple parameters to the query.
     *
     * @param   array  list of parameters
     * @return  $this
     */
    public function parameters(array $params)
    {
        $this->_parameters = $params + $this->_parameters;
        return $this;
    }

    /**
     * Compile the SQL query and return it. Replaces any parameters with their
     * given values.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile($db)
    {
        $sql = $this->_sql;

        if( ! empty($this->_parameters))
        {
            $values = array_map(array($db, 'quote'), $this->_parameters);
            $sql    = strtr($sql, $values);
        }

        return $sql;
    }

    /**
     * Execute the current query on the given database.
     *
     * @param   mixed    Database instance or name of instance
     * @return  object   Database_Result for SELECT queries
     * @return  mixed    the insert id for INSERT queries
     * @return  integer  number of affected rows for all other queries
     */
    public function execute($db = NULL)
    {
        if( ! is_object($db))
        {
            $db = Database::instance($db);
        }

        $sql = $this->compile($db);

        if( ! empty($this->_lifetime) and $this->_type === Database::SELECT)
        {
            $cache_key = 'Database::query("' . $db . '", "' . $sql . '")';
            $result    = QuickPHP::cache($cache_key, NULL, $this->_lifetime);

            if( ! empty($result))
            {
                return new QuickPHP_Database_Result_Cached($result, $sql, $this->_as_object);
            }
        }

        $result = $db->query($this->_type, $sql, $this->_as_object);

        if(isset($cache_key))
        {
            QuickPHP::cache($cache_key, $result->as_array(), $this->_lifetime);
        }

        return $result;
    }
}