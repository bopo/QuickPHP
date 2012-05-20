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
 * 数据库查询绑定器.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Builder.php 8320 2011-10-05 14:59:55Z bopo $
 */
abstract class QuickPHP_Database_Query_Builder extends Database_Query
{
    /**
     * 编译 SQL 的 JOIN 段.
     *
     * @param   object  数据库实例
     * @param   array   JOIN 条件语句
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
     * 编译 SQL 的条件语句段. WHERE 和 HAVING 部分使用
     *
     * @param   object  数据库实例
     * @param   array   条件语句
     * @return  string
     */
    protected function _compile_conditions($db, array $conditions)
    {
        $last_condition = null;
        $sql            = '';

        foreach ($conditions as $group)
        {
            foreach ($group as $logic => $condition)
            {
                if($condition === '(')
                {
                    if( ! empty($sql) and $last_condition !== '(')
                    {
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
                    // 添加逻辑操作符
                    if( ! empty($sql) and $last_condition !== '(')
                    {
                        $sql .= ' ' . $logic . ' ';
                    }

                    // 拆分条件内容
                    list ($column, $op, $value) = $condition;

                    if($value === null)
                    {
                        if($op === '=')
                        {
                            // 转换 "val = null" 为 "val IS null"
                            $op = 'IS';
                        }
                        elseif($op === '!=')
                        {
                            // 转换 "val != null" 为 "valu IS NOT null"
                            $op = 'IS NOT';
                        }
                     }

                    // 数据库操作关键字转换大写
                    $op = strtoupper($op);

                    if($op === 'BETWEEN' and is_array($value))
                    {
                        // BETWEEN 最大,最小值
                        list ($min, $max) = $value;

                        // 设置参数的最小值
                        if(is_string($min) and array_key_exists($min, $this->_parameters))
                        {
                            $min = $this->_parameters[$min];
                        }

                        // 设置参数的最大值
                        if(is_string($max) and array_key_exists($max, $this->_parameters))
                        {
                            $max = $this->_parameters[$max];
                        }

                        $value = $db->quote($min) . ' AND ' . $db->quote($max);
                    }
                    else
                    {
                        // 设置参数值
                        if(is_string($value) and array_key_exists($value, $this->_parameters))
                        {
                            $value = $this->_parameters[$value];
                        }

                        $value = $db->quote($value);
                    }

                    // 追加到 SQL 查询语句
                    $sql .= $db->quote_identifier($column) . ' ' . $op . ' ' . $value;
                }

                $last_condition = $condition;
            }
        }

        return $sql;
    }

    /**
     * 将一个数组编译成 SQL 的 SET 段. UPDATE 操作时使用
     *
     * @param   object  数据库实例
     * @param   array   要编译的数组
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
     * 编译 SQL 的 ORDER BY 段.
     * 
     * @param   object  数据库实例
     * @param   array   排序字段
     * @return  string
     */
    protected function _compile_order_by($db, array $columns)
    {
        $sort = array();

        foreach ($columns as $group)
        {
            list ($column, $direction) = $group;

            if( ! empty($direction))
            {
                $direction = ' ' . strtoupper($direction);
            }

            $sort[] = $db->quote_identifier($column) . $direction;
        }

        return 'ORDER BY ' . implode(', ', $sort);
    }

    /**
     * 重置绑定器状态.
     *
     * @return  $this
     */
    abstract public function reset();
}