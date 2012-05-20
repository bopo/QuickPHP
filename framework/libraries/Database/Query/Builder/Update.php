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
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Update.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Database_Query_Builder_Update extends QuickPHP_Database_Query_Builder_Where
{

    // UPDATE ...
    protected $_table;

    // SET ...
    protected $_set = array();

    /**
     * 设置要更新 (UPDATE) 操作的表.
     *
     * @param   mixed  表名或者 array($table, $alias) 或者是对象模型
     * @return  void
     */
    public function __construct($table)
    {
        $this->_table = $table;
        return parent::__construct(Database::UPDATE, '');
    }

    /**
     * 设置要更新(update)操作的表.
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
     * 设置多字段要更新的值. 以(column => value) 数组形式传入.
     *
     * @param   array   (column => value) 的形式数组组合
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
     * 设置一个字段要更新的值.
     *
     * @param   mixed  字段名
     * @param   mixed  字段值
     * @return  $this
     */
    public function value($column, $value)
    {
        $this->_set[] = array($column, $value);
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
        $this->_table      = null;
        $this->_set        = array();
        $this->_where      = array();
        $this->_parameters = array();
        
        return $this;
    }
}