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
 * Database query builder for JOIN statements.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Join.php 8320 2011-10-05 14:59:55Z bopo $ */
class QuickPHP_Database_Query_Builder_Join extends QuickPHP_Database_Query_Builder
{

    // Type of JOIN
    protected $_type;

    // JOIN ...
    protected $_table;

    // ON ...
    protected $_on = array();

    /**
     * Creates a new JOIN statement for a table. Optionally, the type of JOIN
     * can be specified as the second parameter.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  type of JOIN: INNER, RIGHT, LEFT, etc
     * @return  void
     */
    public function __construct($table, $type = null)
    {
        // Set the table to JOIN on
        $this->_table = $table;

        if($type !== null)
        {
            $this->_type = (string) $type;
        }
    }

    /**
     * Adds a new condition for joining.
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   string  logic operator
     * @param   mixed   column name or array($column, $alias) or object
     * @return  $this
     */
    public function on($c1, $op, $c2)
    {
        $this->_on[] = array($c1, $op, $c2);
        return $this;
    }

    /**
     * Compile the SQL partial for a JOIN statement and return it.
     *
     * @param   object  Database instance
     * @return  string
     */
    public function compile($db)
    {
        if($this->_type)
        {
            $sql = strtoupper($this->_type) . ' JOIN';
        }
        else
        {
            $sql = 'JOIN';
        }

        // Quote the table name that is being joined
        $sql .= ' ' . $db->quote_table($this->_table) . ' ON ';
        $conditions = array();

        foreach ($this->_on as $condition)
        {
            // Split the condition
            list ($c1, $op, $c2) = $condition;

            if($op)
            {
                $op = ' ' . strtoupper($op);
            }

            // Quote each of the identifiers used for the condition
            $conditions[] = $db->quote_identifier($c1) . $op . ' ' . $db->quote_identifier($c2);
        }

        // Concat the conditions "... AND ..."
        $sql .= '(' . implode(' AND ', $conditions) . ')';

        return $sql;
    }

    public function reset()
    {
        $this->_type = $this->_table = null;
        $this->_on   = array();
    }
}