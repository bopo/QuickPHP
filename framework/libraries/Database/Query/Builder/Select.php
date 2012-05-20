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
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Select.php 8641 2012-01-05 08:35:39Z bopo $ 
 */
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

    // 最后创建 JOIN 段容器
    protected $_last_join;

    /**
     * 设置并初始化 SELECT 选择的表字段(列).
     *
     * @param   array  字段列表
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
     * 开启或关闭 "SELECT DISTINCT"
     *
     * @param   boolean  要开启或关闭 DISTINCT 字段(列)
     * @return  $this
     */
    public function distinct($value = null)
    {
        $this->_distinct = (bool) $value;
        return $this;
    }

    /**
     * SELECT 查询的选择字段.
     *
     * @param   mixed  字段(列)名或者 array($column, $alias) 或者是对象模型
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
     * SELECT 查询的选择字段, 使用数组形式.
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
     * SELECT 操作的主表 "FROM ...".
     *
     * @param   mixed  表名或者 array($table, $alias) 或者是对象模型
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
     * 添加附属表到 "JOIN ..." 中.
     *
     * @param   mixed   字段(列)名或者 array($column, $alias) 或者是对象模型
     * @param   string  JOIN 类型 (LEFT, RIGHT, INNER 等)
     * @return  $this
     */
    public function join($table, $type = null)
    {
        $this->_join[] = $this->_last_join = new Database_Query_Builder_Join($table, $type);
        return $this;
    }

    /**
     * 添加 "ON ..." 条件语句到最后声明的 JOIN 部分.
     *
     * @param   mixed   主表字段(列)名或者 array($column, $alias) 或者是对象模型
     * @param   string  逻辑运算
     * @param   mixed   附表字段(列)名或者 array($column, $alias) 或者是对象模型
     * @return  $this
     */
    public function on($c1, $op, $c2)
    {
        $this->_last_join->on($c1, $op, $c2);
        return $this;
    }

    /**
     * 创建 "GROUP BY ..." 过滤.
     *
     * @param   mixed   字段(列)名或者 array($column, $alias) 或者是对象模型
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
     * and_having() 的别名
     *
     * @param   mixed   字段(列)名或者 array($column, $alias) 或者是对象模型
     * @param   string  逻辑运算
     * @param   mixed   字段(列)值
     * @return  $this
     */
    public function having($column, $op, $value = null)
    {
        return $this->and_having($column, $op, $value);
    }

    /**
     * 创建 "AND HAVING" 查询条件.
     *
     * @param   mixed   字段(列)名或者 array($column, $alias) 或者是对象模型
     * @param   string  逻辑运算
     * @param   mixed   字段(列)值
     * @return  $this
     */
    public function and_having($column, $op, $value = null)
    {
        $this->_having[] = array('AND' => array($column, $op, $value));
        return $this;
    }

    /**
     * 创建 "OR HAVING" 查询条件. 
     *
     * @param   mixed   字段(列)名或者 array($column, $alias) 或者是对象模型
     * @param   string  逻辑运算
     * @param   mixed   字段(列)值
     * @return  $this
     */
    public function or_having($column, $op, $value = null)
    {
        $this->_having[] = array('OR' => array($column, $op, $value));
        return $this;
    }

    /**
     * 打开开始 "AND HAVING (...)" 查询条件组. and_having_open() 的简写别名
     *
     * @return  $this
     */
    public function having_open()
    {
        return $this->and_having_open();
    }

    /**
     * 打开 "AND HAVING (...)" 查询条件组.
     *
     * @return  $this
     */
    public function and_having_open()
    {
        $this->_having[] = array('AND' => '(');
        return $this;
    }

    /**
     * 打开 "OR HAVING (...)" 查询条件组.
     *
     * @return  $this
     */
    public function or_having_open()
    {
        $this->_having[] = array('OR' => '(');
        return $this;
    }

    /**
     * 关闭已经打开 "AND HAVING (...)" 查询条件组. and_having_close() 的简写别名
     *
     * @return  $this
     */
    public function having_close()
    {
        return $this->and_having_close();
    }

    /**
     * 关闭已经打开 "AND HAVING (...)" 查询条件组.
     *
     * @return  $this
     */
    public function and_having_close()
    {
        $this->_having[] = array('AND' => ')');
        return $this;
    }

    /**
     * 关闭已经打开 "OR HAVING (...)" 查询条件组.
     *
     * @return  $this
     */
    public function or_having_close()
    {
        $this->_having[] = array('OR' => ')');
        return $this;
    }

    /**
     * 查询语句返回指定的结果开始行数 "OFFSET ..."
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
     * 编译 SQL 查询语句.
     *
     * @param   object  数据库实例
     * @return  string
     */
    public function compile($db)
    {
        $quote_ident = array($db, 'quote_identifier');
        $quote_table = array($db, 'quote_table');

        $query = 'SELECT ';

        if($this->_distinct === true)
        {
            $query .= 'DISTINCT ';
        }

        if(empty($this->_select))
        {
            $query .= '*';
        }
        else
        {
            $query .= implode(', ', array_unique(array_map($quote_ident, $this->_select)));
        }

        if( ! empty($this->_from))
        {
            $query .= ' FROM ' . implode(', ', array_unique(array_map($quote_table, $this->_from)));
        }

        if( ! empty($this->_join))
        {
            $query .= ' ' . $this->_compile_join($db, $this->_join);
        }

        if( ! empty($this->_where))
        {
            $query .= ' WHERE ' . $this->_compile_conditions($db, $this->_where);
        }

        if( ! empty($this->_group_by))
        {
            $query .= ' GROUP BY ' . implode(', ', array_map($quote_ident, $this->_group_by));
        }

        if( ! empty($this->_having))
        {
            $query .= ' HAVING ' . $this->_compile_conditions($db, $this->_having);
        }

        if( ! empty($this->_order_by))
        {
            $query .= ' ' . $this->_compile_order_by($db, $this->_order_by);
        }

        if($this->_limit !== null)
        {
            $query .= ' LIMIT ' . $this->_limit;
        }

        if($this->_offset !== null)
        {
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