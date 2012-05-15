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
 * QuickPHP 数据库中结果集的包装。
 *
 * @category   QuickPHP
 * @package    Database
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
abstract class QuickPHP_Database_Result implements Countable, Iterator, SeekableIterator, ArrayAccess
{

    // 本结果集的查询sql
    protected $_query;

    // 结果资源对象
    protected $_result;

    // 结果集总数
    protected $_total_rows  = 0;

    // 当前列序号
    protected $_current_row = 0;

    // 是以对象形式返回结果还是以联合数组形式返回
    protected $_as_object;

    /**
     * 初始化结果集操作的一些设置
     *
     * @param   mixed   数据库结果集
     * @param   string  SQL 字符串
     * @return  void
     */
    public function __construct($result, $sql, $as_object)
    {
        $this->_result = $result;
        $this->_query  = $sql;

        if(is_object($as_object))
        {
            $as_object = get_class($as_object);
        }

        $this->_as_object = $as_object;
    }

    /**
     * 清除已经打开的全部结果集
     *
     * @return  void
     */
    abstract public function __destruct();

    /**
     * 从当前结果迭代器中获取已缓存的数据库结果
     *
     * $cachable = serialize($result->cached());
     *
     * @return  Database_Result_Cached
     */
    public function cached()
    {
        return new QuickPHP_Database_Result_Cached($this->as_array(), $this->_query, $this->_as_object);
    }

    /**
     * 以数组形式返回结果.
     *
     * // 返回所有结果
     * $rows = $result->as_array();
     *
     * // 返回键为 "id" 的结果
     * $rows = $result->as_array('id');
     *
     * // 返回 "id" => "name" 形式
     * $rows = $result->as_array('id', 'name');
     *
     * @param   string  键
     * @param   string  值
     * @return  array
     */
    public function as_array($key = null, $value = null)
    {
        $results = array();

        if($key === null and $value === null)
        {
            foreach ($this as $row)
            {
                $results[] = $row;
            }
        }
        elseif($key === null)
        {
            if($this->_as_object)
            {
                foreach ($this as $row)
                {
                    $results[] = $row->$value;
                }
            }
            else
            {
                foreach ($this as $row)
                {
                    $results[] = $row[$value];
                }
            }
        }
        elseif($value === null)
        {
            if($this->_as_object)
            {
                foreach ($this as $row)
                {
                    $results[$row->$key] = $row;
                }
            }
            else
            {
                foreach ($this as $row)
                {
                    $results[$row[$key]] = $row;
                }
            }
        }
        else
        {
            if($this->_as_object)
            {
                foreach ($this as $row)
                {
                    $results[$row->$key] = $row->$value;
                }
            }
            else
            {
                foreach ($this as $row)
                {
                    $results[$row[$key]] = $row[$value];
                }
            }
        }

        $this->rewind();
        return $results;
    }

    /**
     * 返回当前行指定字段的值.
     *
     * // 获取 "id" 值
     * $id = $result->get('id');
     *
     * @param   string  要回去的字段名
     * @param   mixed   默认值，如果该字段不存在或者为空则使用默认值
     * @return  mixed
     */
    public function get($name, $default = null)
    {
        $row = $this->current();

        if($this->_as_object)
        {
            if(isset($row->$name))
            {
                return $row->$name;
            }
        }
        else
        {
            if(isset($row[$name]))
            {
                return $row[$name];
            }
        }

        return $default;
    }

    /**
     * Implements [Countable::count], 返回总行数.
     *
     * echo count($result);
     *
     * @return  integer
     */
    public function count()
    {
        return $this->_total_rows;
    }

    /**
     * Implements [ArrayAccess::offsetExists], 确定指定行号是否存在.
     *
     * if (isset($result[10]))
     * {
     *      // Row 10 exists
     * }
     *
     * @return  boolean
     */
    public function offsetExists($offset)
    {
        return ($offset >= 0 and $offset < $this->_total_rows);
    }

    /**
     * Implements [ArrayAccess::offsetGet], 获取已有的行.
     *
     * $row = $result[10];
     *
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        if( ! $this->seek($offset))
        {
            return null;
        }

        return $this->current();
    }

    /**
     * Implements [ArrayAccess::offsetSet], throws an error.
     *
     * [!!] You cannot modify a database result.
     *
     * @return  void
     * @throws  Database_Exception
     */
    final public function offsetSet($offset, $value)
    {
        throw new Database_Exception('result_read_only');
    }

    /**
     * Implements [ArrayAccess::offsetUnset], throws an error.
     *
     * [!!] You cannot modify a database result.
     *
     * @return  void
     * @throws  Database_Exception
     */
    final public function offsetUnset($offset)
    {
        throw new Database_Exception('result_read_only');
    }

    /**
     * Implements [Iterator::key], 返回当前行的序号
     *
     * echo key($result);
     *
     * @return  integer
     */
    public function key()
    {
        return $this->_current_row;
    }

    /**
     * Implements [Iterator::next], 移动到下一行.
     *
     * next($result);
     *
     * @return  $this
     */
    public function next()
    {
        ++$this->_current_row;
        return $this;
    }

    /**
     * Implements [Iterator::prev], 移动到上一行.
     *
     * prev($result);
     *
     * @return  $this
     */
    public function prev()
    {
        --$this->_current_row;
        return $this;
    }

    /**
     * Implements [Iterator::rewind], 设置当前行序号.
     *
     * rewind($result);
     *
     * @return  $this
     */
    public function rewind()
    {
        $this->_current_row = 0;
        return $this;
    }

    /**
     * Implements [Iterator::valid], 验证当前行是否存在.
     *
     * [!!] This method is only used internally.
     *
     * @return  boolean
     */
    public function valid()
    {
        return $this->offsetExists($this->_current_row);
    }
}