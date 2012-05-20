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
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Insert.php 8320 2011-10-05 14:59:55Z bopo $ 
 */
class QuickPHP_Database_Query_Builder_Insert extends QuickPHP_Database_Query_Builder
{

    // INSERT INTO ...
    protected $_table;
    
    // (...)
    protected $_columns = array();
    
    // VALUES (...)
    protected $_values  = array();

    /**
     * 设置表和要插入字段(列)
     *
     * @param   mixed  表名或者 array($table, $alias) 或者是对象模型
     * @param   array  column names
     * @return  void
     */
    public function __construct($table, array $columns = null)
    {
        $this->_table = $table;

        if( ! empty($columns))
        {
            $this->_columns = $columns;
        }

        return parent::__construct(Database::INSERT, '');
    }

    /**
     * 设置要进行插入操作的表.
     *
     * @param   mixed  表名或者 array($table, $alias) 或者是对象模型
     * @return  $this
     */
    public function table($table)
    {
        $this->_table = $table;

        return $this;
    }

    /**
     * 设置要插入操作的字段名列表.
     *
     * @param   array  字段名列表 array(column1,column2 ...)
     * @return  $this
     */
    public function columns(array $columns)
    {
        $this->_columns = $columns;

        return $this;
    }

    /**
     * 添加与字段相匹配的值. 
     *
     * @param   array   字段值列表 array(value1, value2 ...)
     * @param   ...
     * @return  $this
     */
    public function values(array $values)
    {
        if( ! is_array($this->_values))
        {
            throw new Database_Exception('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES');
        }

        $values        = func_get_args();
        $this->_values = array_merge($this->_values, $values);

        return $this;
    }

    /**
     * 使用子查询进行插入值
     *
     * @param   object  Database_Query of SELECT 类型
     * @return  $this
     */
    public function select(Database_Query $query)
    {
        if($query->type() !== Database::SELECT)
        {
            throw new Database_Exception('Only SELECT queries can be combined with INSERT queries');
        }

        $this->_values = $query;

        return $this;
    }

    /**
     * 编译 SQL 查询语句.
     *
     * @param   object  数据库实例
     * @return  string
     */
    public function compile($db)
    {
        $query　= 'INSERT INTO ' . $db->quote_table($this->_table);
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
                        $group[$i] = $this->_parameters[$value];
                    }
                }

                $groups[] = '(' . implode(', ', array_map($quote, $group)) . ')';
            }

            $query .= 'VALUES ' . implode(', ', $groups);
        }
        else
        {
            $query .= (string) $this->_values;
        }

        return $query;
    }

    public function reset()
    {
        $this->_table   = null;
        $this->_columns = $this->_values = $this->_parameters = array();

        return $this;
    }
}
