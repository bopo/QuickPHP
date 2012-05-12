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
 * Database query builder for DELETE statements.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Delete.php 8320 2011-10-05 14:59:55Z bopo $ */
class QuickPHP_Database_Query_Builder_Delete extends QuickPHP_Database_Query_Builder_Where
{

    protected $_table;

    /**
     * Set the table for a delete.
     *
     * @param   mixed  table name or array($table, $alias) or object
     * @return  void
     */
    public function __construct($table)
    {
        $this->_table = $table;
        return parent::__construct(Database::DELETE, '');
    }

    /**
     * Sets the table to delete from.
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
     * Compile the SQL query and return it.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile($db)
    {
        $query = 'DELETE FROM ' . $db->quote_table($this->_table);

        if( ! empty($this->_where))
        {
            $query .= ' WHERE ' . $this->_compile_conditions($db, $this->_where);
        }

        if( ! empty($this->_order_by))
        {
            $query .= ' ' . $this->_compile_order_by($db, $this->_order_by);
        }

        if($this->_limit !== NULL)
        {
            $query .= ' LIMIT ' . $this->_limit;
        }

        return $query;
    }

    public function reset()
    {
        $this->_table = NULL;
        $this->_where = $this->_parameters = array();

        return $this;
    }
}