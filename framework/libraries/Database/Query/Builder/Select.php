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
 * Database query builder for SELECT statements.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Select.php 8641 2012-01-05 08:35:39Z bopo $ */
class QuickPHP_Database_Query_Builder_Select extends QuickPHP_Database_Query_Builder_Where
{

    // SELECT ...
    protected $_select   = array();
    
    // DISTINCT
    protected $_distinct = false;
    
    // FROM ...
    protected $_from     = array();
    
    // JOIN ...
    protected $_join     = array();
    
    // GROUP BY ...
    protected $_group_by = array();
    
    // HAVING ...
    protected $_having   = array();
    
    // OFFSET ...
    protected $_offset   = null;

    // The last JOIN statement created
    protected $_last_join;

    /**
     * Sets the initial columns to select from.
     *
     * @param   array  column list
     * @return  void
     */
    public function __construct(array $columns = null)
    {
        if( ! empty($columns))
        {
            $this->_select = $columns;
        }

        parent::__construct(Database::SELECT, '');
    }

    /**
     * Enables or disables selecting only unique columns using "SELECT DISTINCT"
     *
     * @param   boolean  enable or disable distinct columns
     * @return  $this
     */
    public function distinct($value = null)
    {
        $this->_distinct = (bool) $value;
        return $this;
    }

    /**
     * Choose the columns to select from.
     *
     * @param   mixed  column name or array($column, $alias) or object
     * @param   ...
     * @return  $this
     */
    public function select($columns = null)
    {
        $columns       = func_get_args();
        $this->_select = array_merge($this->_select, $columns);

        return $this;
    }

    /**
     * Choose the columns to select from, using an array.
     *
     * @param   array  list of column names or aliases
     * @return  $this
     */
    public function select_array(array $columns)
    {
        $this->_select = array_merge($this->_select, $columns);
        return $this;
    }

    /**
     * Choose the tables to select "FROM ..."
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @param   ...
     * @return  $this
     */
    public function from($tables)
    {
        $tables         = func_get_args();
        $this->_from    = array_merge($this->_from, $tables);
        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  join type (LEFT, RIGHT, INNER, etc)
     * @return  $this
     */
    public function join($table, $type = null)
    {
        $this->_join[] = $this->_last_join = new Database_Query_Builder_Join($table, $type);
        return $this;
    }

    /**
     * Adds "ON ..." conditions for the last created JOIN statement.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column name or array($column, $alias) or object
     * @return  $this
     */
    public function on($c1, $op, $c2)
    {
        $this->_last_join->on($c1, $op, $c2);
        return $this;
    }

    /**
     * Creates a "GROUP BY ..." filter.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   ...
     * @return  $this
     */
    public function group_by($columns)
    {
        $columns            = func_get_args();
        $this->_group_by    = array_merge($this->_group_by, $columns);
        return $this;
    }

    /**
     * Alias of and_having()
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  $this
     */
    public function having($column, $op, $value = null)
    {
        return $this->and_having($column, $op, $value);
    }

    /**
     * Creates a new "AND HAVING" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  $this
     */
    public function and_having($column, $op, $value = null)
    {
        $this->_having[] = array('AND' => array($column, $op, $value));

        return $this;
    }

    /**
     * Creates a new "OR HAVING" condition for the query.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column value
     * @return  $this
     */
    public function or_having($column, $op, $value = null)
    {
        $this->_having[] = array('OR' => array($column, $op, $value));
        return $this;
    }

    /**
     * Alias of and_having_open()
     *
     * @return  $this
     */
    public function having_open()
    {
        return $this->and_having_open();
    }

    /**
     * Opens a new "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function and_having_open()
    {
        $this->_having[] = array('AND' => '(');
        return $this;
    }

    /**
     * Opens a new "OR HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function or_having_open()
    {
        $this->_having[] = array('OR' => '(');
        return $this;
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function having_close()
    {
        return $this->and_having_close();
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function and_having_close()
    {
        $this->_having[] = array('AND' => ')');
        return $this;
    }

    /**
     * Closes an open "OR HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function or_having_close()
    {
        $this->_having[] = array('OR' => ')');
        return $this;
    }

    /**
     * Start returning results after "OFFSET ..."
     *
     * @param   integer   starting result number
     * @return  $this
     */
    public function offset($number)
    {
        $this->_offset = (int) $number;
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
        // Callback to quote identifiers
        $quote_ident = array($db, 'quote_identifier');

        // Callback to quote tables
        $quote_table = array($db, 'quote_table');

        // Start a selection query
        $query = 'SELECT ';

        if($this->_distinct === true)
        {
            // Select only unique results
            $query .= 'DISTINCT ';
        }

        if(empty($this->_select))
        {
            // Select all columns
            $query .= '*';
        }
        else
        {
            // Select all columns
            $query .= implode(', ', array_unique(array_map($quote_ident, $this->_select)));
        }

        if( ! empty($this->_from))
        {
            // Set tables to select from
            $query .= ' FROM ' . implode(', ', array_unique(array_map($quote_table, $this->_from)));
        }

        if( ! empty($this->_join))
        {
            // Add tables to join
            $query .= ' ' . $this->_compile_join($db, $this->_join);
        }

        if( ! empty($this->_where))
        {
            // Add selection conditions
            $query .= ' WHERE ' . $this->_compile_conditions($db, $this->_where);
        }

        if( ! empty($this->_group_by))
        {
            // Add sorting
            $query .= ' GROUP BY ' . implode(', ', array_map($quote_ident, $this->_group_by));
        }

        if( ! empty($this->_having))
        {
            // Add filtering conditions
            $query .= ' HAVING ' . $this->_compile_conditions($db, $this->_having);
        }

        if( ! empty($this->_order_by))
        {
            // Add sorting
            $query .= ' ' . $this->_compile_order_by($db, $this->_order_by);
        }

        if($this->_limit !== null)
        {
            // Add limiting
            $query .= ' LIMIT ' . $this->_limit;
        }

        if($this->_offset !== null)
        {
            // Add offsets
            $query .= ' OFFSET ' . $this->_offset;
        }

        return $query;
    }

    public function reset()
    {
        $this->_select     = array();
        $this->_from       = array();
        $this->_join       = array();
        $this->_where      = array();
        $this->_group_by   = array();
        $this->_having     = array();
        $this->_order_by   = array();
        $this->_parameters = array();
        $this->_distinct   = false;
        $this->_limit      = false;
        $this->_offset     = false;
        $this->_last_join  = null;
        return $this;
    }
}