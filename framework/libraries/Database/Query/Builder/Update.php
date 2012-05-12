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
 * Database query builder for UPDATE statements.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Update.php 8320 2011-10-05 14:59:55Z bopo $ */
class QuickPHP_Database_Query_Builder_Update extends QuickPHP_Database_Query_Builder_Where
{

    // UPDATE ...
    protected $_table;

    // SET ...
    protected $_set = array();

    /**
     * Set the table for a update.
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @return  void
     */
    public function __construct($table)
    {
        $this->_table = $table;
        return parent::__construct(Database::UPDATE, '');
    }

    /**
     * Sets the table to update.
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @return  $this
     */
    public function table($table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * Set the values to update with an associative array.
     *
     * @param   array   associative (column => value) list
     * @return  $this
     */
    public function set(array $pairs)
    {
        foreach ($pairs as $column => $value)
        {
            $this->_set[] = array($column, $value);
        }

        return $this;
    }

    /**
     * Set the value of a single column.
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @param   mixed  column value
     * @return  $this
     */
    public function value($column, $value)
    {
        $this->_set[] = array($column, $value);
        return $this;
    }

    /**
     * Compile the SQL query and return it.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile($db)
    {
        $query  = 'UPDATE ' . $db->quote_table($this->_table);
        $query .= ' SET ' . $this->_compile_set($db, $this->_set);

        if( ! empty($this->_where))
        {
            $query .= ' WHERE ' . $this->_compile_conditions($db, $this->_where);
        }

        return $query;
    }

    public function reset()
    {
        $this->_table      = NULL;
        $this->_set        =
        $this->_where      =
        $this->_parameters = array();
        return $this;
    }
}