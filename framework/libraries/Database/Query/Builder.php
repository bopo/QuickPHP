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
 * Database query builder.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Builder.php 8320 2011-10-05 14:59:55Z bopo $
 */
abstract class QuickPHP_Database_Query_Builder extends QuickPHP_Database_Query
{

    /**
     * Compiles an array of JOIN statements into an SQL partial.
     *
     * @param   object  Database instance
     * @param   array   join statements
     * @return  string
     */
    protected function _compile_join($db, array $joins)
    {
        $statements = array();

        foreach ($joins as $join)
        {
            $statements[] = $join->compile($db);
        }

        return implode(' ', $statements);
    }

    /**
     * Compiles an array of conditions into an SQL partial. Used for WHERE
     * and HAVING.
     *
     * @param   object  Database instance
     * @param   array   condition statements
     * @return  string
     */
    protected function _compile_conditions($db, array $conditions)
    {
        $last_condition = NULL;
        $sql            = '';

        foreach ($conditions as $group)
        {
            // Process groups of conditions
            foreach ($group as $logic => $condition)
            {
                if($condition === '(')
                {
                    if( ! empty($sql) and $last_condition !== '(')
                    {
                        // Include logic operator
                        $sql .= ' ' . $logic . ' ';
                    }

                    $sql .= '(';
                }
                elseif($condition === ')')
                {
                    $sql .= ')';
                }
                else
                {
                    // Add the logic operator
                    if( ! empty($sql) and $last_condition !== '(')
                        $sql .= ' ' . $logic . ' ';

                    // Split the condition
                    list ($column, $op, $value) = $condition;

                    if($value === NULL)
                    {
                        if($op === '=')
                        {
                            // Convert "val = NULL" to "val IS NULL"
                            $op = 'IS';
                        }
                        elseif($op === '!=')
                        {
                            // Convert "val != NULL" to "valu IS NOT NULL"
                            $op = 'IS NOT';
                        }
                     }

                    // Database operators are always uppercase
                    $op = strtoupper($op);

                    if($op === 'BETWEEN' and is_array($value))
                    {
                        // BETWEEN always has exactly two arguments
                        list ($min, $max) = $value;

                        // Set the parameter as the minimum
                        if(is_string($min) and array_key_exists($min, $this->_parameters))
                            $min = $this->_parameters[$min];

                        // Set the parameter as the maximum
                        if(is_string($max) and array_key_exists($max, $this->_parameters))
                            $max = $this->_parameters[$max];

                        // Quote the min and max value
                        $value = $db->quote($min) . ' AND ' . $db->quote($max);
                    }
                    else
                    {
                        // Set the parameter as the value
                        if(is_string($value) and array_key_exists($value, $this->_parameters))
                            $value = $this->_parameters[$value];

                        // Quote the entire value normally
                        $value = $db->quote($value);
                    }

                    // Append the statement to the query
                    $sql .= $db->quote_identifier($column) . ' ' . $op . ' ' . $value;
                }

                $last_condition = $condition;
            }
        }

        return $sql;
    }

    /**
     * Compiles an array of set values into an SQL partial. Used for UPDATE.
     *
     * @param   object  Database instance
     * @param   array   updated values
     * @return  string
     */
    protected function _compile_set($db, array $values)
    {
        $set = array();

        foreach ($values as $group)
        {
            list ($column, $value) = $group;
            $column = $db->quote_identifier($column);

            if(is_string($value) and array_key_exists($value, $this->_parameters))
            {
                $value = $this->_parameters[$value];
            }

            $set[$column] = $column . ' = ' . $db->quote($value);
        }

        return implode(', ', $set);
    }

    /**
     * Compiles an array of ORDER BY statements into an SQL partial.
     *
     * @param   object  Database instance
     * @param   array   sorting columns
     * @return  string
     */
    protected function _compile_order_by($db, array $columns)
    {
        $sort = array();

        foreach ($columns as $group)
        {
            list ($column, $direction) = $group;

            // Make the direction uppercase
            if( ! empty($direction))
            {
                $direction = ' ' . strtoupper($direction);
            }

            $sort[] = $db->quote_identifier($column) . $direction;
        }

        return 'ORDER BY ' . implode(', ', $sort);
    }

    /**
     * Reset the current builder status.
     *
     * @return  $this
     */
    abstract public function reset();
}