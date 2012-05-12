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
 * Database query builder for INSERT statements.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Insert.php 8320 2011-10-05 14:59:55Z bopo $ */
class QuickPHP_Database_Query_Builder_Insert extends QuickPHP_Database_Query_Builder
{

    // INSERT INTO ...
    protected $_table;

    // (...)
    protected $_columns = array();

    // VALUES (...)
    protected $_values = array();

    /**
     * Set the table and columns for an insert.
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @param   array  column names
     * @return  void
     */
    public function __construct($table, array $columns = NULL)
    {
        // Set the inital table name
        $this->_table = $table;

        if( ! empty($columns))
        {
            $this->_columns = $columns;
        }

        // Start the query with no SQL
        return parent::__construct(Database::INSERT, '');
    }

    /**
     * Sets the table to insert into.
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
     * Set the columns that will be inserted.
     *
     * @param   array  column names
     * @return  $this
     */
    public function columns(array $columns)
    {
        $this->_columns = $columns;

        return $this;
    }

    /**
     * Adds or overwrites values. Multiple value sets can be added.
     *
     * @param   array   values list
     * @param   ...
     * @return  $this
     */
    public function values(array $values)
    {
        if( ! is_array($this->_values))
        {
            throw new QuickPHP_Database_Exception('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES');
        }

        // Get all of the passed values
        $values        = func_get_args();
        $this->_values = array_merge($this->_values, $values);

        return $this;
    }

    /**
     * Use a sub-query to for the inserted values.
     *
     * @param   object  Database_Query of SELECT type
     * @return  $this
     */
    public function select(Database_Query $query)
    {
        if($query->type() !== Database::SELECT)
        {
            throw new QuickPHP_Database_Exception('Only SELECT queries can be combined with INSERT queries');
        }

        $this->_values = $query;

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
        // Start an insertion query
        $query = 'INSERT INTO ' . $db->quote_table($this->_table);

        // Add the column names
        $query .= ' (' . implode(', ', array_map(array($db, 'quote_identifier'), $this->_columns)) . ') ';

        if(is_array($this->_values))
        {
            $quote  = array($db, 'quote');
            $groups = array();

            foreach ($this->_values as $group)
            {
                foreach ($group as $i => $value)
                {
                    if(is_string($value) and isset($this->_parameters[$value]))
                    {
                        // Use the parameter value
                        $group[$i] = $this->_parameters[$value];
                    }
                }

                $groups[] = '(' . implode(', ', array_map($quote, $group)) . ')';
            }

            // Add the values
            $query .= 'VALUES ' . implode(', ', $groups);
        }
        else
        {
            // Add the sub-query
            $query .= (string) $this->_values;
        }

        return $query;
    }

    public function reset()
    {
        $this->_table   = NULL;
        $this->_columns = $this->_values = $this->_parameters = array();

        return $this;
    }
}
