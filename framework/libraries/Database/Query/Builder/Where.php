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
 * Database query builder for WHERE statements.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Where.php 8320 2011-10-05 14:59:55Z bopo $
 */
abstract class QuickPHP_Database_Query_Builder_Where extends QuickPHP_Database_Query_Builder
{

    // WHERE ...
    protected $_where    = array();

    // LIMIT ...
    protected $_limit    = null;

    // ORDER BY ...
    protected $_order_by = array();

    /**
     * and_where() 的简写别名
     *
     * @param   mixed   字段(列)名或 array($column, $alias)数组或对象
     * @param   string  逻辑运算
     * @param   mixed   字段(列)值
     * @return  $this
     */
    public function where($column, $op, $value)
    {
        return $this->and_where($column, $op, $value);
    }

    /**
     * 创建 "AND WHERE" 查询条件. 
     *
     * @param   mixed   字段(列)名或 array($column, $alias)数组或对象
     * @param   string  逻辑运算
     * @param   mixed   字段(列)值
     * @return  $this
     */
    public function and_where($column, $op, $value)
    {
        $this->_where[] = array('AND' => array($column, $op, $value));
        return $this;
    }

    /**
     * 创建 "OR WHERE" 查询条件. 
     *
     * @param   mixed   字段(列)名或 array($column, $alias)数组或对象
     * @param   string  逻辑运算
     * @param   mixed   字段(列)值
     * @return  $this
     */
    public function or_where($column, $op, $value)
    {
        $this->_where[] = array('OR' => array($column, $op, $value));
        return $this;
    }

    /**
     * and_where_open() 的别名
     *
     * @return  $this
     */
    public function where_open()
    {
        return $this->and_where_open();
    }

    /**
     * 打开 "AND WHERE (...)" 查询条件组. 
     *
     * @return  $this
     */
    public function and_where_open()
    {
        $this->_where[] = array('AND' => '(');
        return $this;
    }

    /**
     * 打开 "OR WHERE (...)" 查询条件组. 
     *
     * @return  $this
     */
    public function or_where_open()
    {
        $this->_where[] = array('OR' => '(');
        return $this;
    }

    /**
     * 关闭已经打开 "AND WHERE (...)" 查询条件组. 
     *
     * @return  $this
     */
    public function where_close()
    {
        return $this->and_where_close();
    }

    /**
     * 关闭已经打开 "AND WHERE (...)" 查询条件组.
     *
     * @return  $this
     */
    public function and_where_close()
    {
        $this->_where[] = array('AND' => ')');
        return $this;
    }

    /**
     * 关闭已经打开 "OR WHERE (...)" 查询条件组.
     *
     * @return  $this
     */
    public function or_where_close()
    {
        $this->_where[] = array('OR' => ')');
        return $this;
    }

    /**
     * 向 SQL 追加 "ORDER BY ..."
     *
     * @param   mixed   字段(列)名或 array($column, $alias)数组或对象
     * @param   string  排序方式(ASC, DESC)
     * @return  $this
     */
    public function order_by($column, $direction = null)
    {
        $this->_order_by[] = array($column, $direction);
        return $this;
    }

    /**
     * 查询语句返回指定的结果行数 "LIMIT ..."
     *
     * @param   integer 要设置的 LIMIT 值
     * @return  $this
     */
    public function limit($number)
    {
        $this->_limit = (int) $number;
        return $this;
    }
}
