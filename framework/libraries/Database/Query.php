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
 * QuickPHP 数据库的请求封装.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Query.php 8641 2012-01-05 08:35:39Z bopo $
 */
class QuickPHP_Database_Query
{

    // 请求类型
    protected $_type;

    // 缓存有效期
    protected $_lifetime;

    // SQL 声明容器
    protected $_sql;

    // 引用请求的参数容器
    protected $_parameters = array();

    // 返回结果为数组还是对象
    protected $_as_object  = false;

    /**
     * 创建数据库查询，并色设置查询类型
     *
     * @param   integer  query type: Database::SELECT, Database::INSERT, etc
     * @param   string   query string
     * @return  void
     */
    public function __construct($type, $sql)
    {
        $this->_type = $type;
        $this->_sql  = $sql;
    }

    /**
     * 魔术方法 __toString 返回查询字符串.
     *
     * @return  string
     */
    final public function __toString()
    {
        try
        {
            return $this->compile(Database::instance());
        }
        catch(Exception $e)
        {
            return QuickPHP_Exception::text($e);
        }
    }

    /**
     * 获取查询类型
     *
     * @return  integer
     */
    public function type()
    {
        return $this->_type;
    }

    /**
     * 开启缓存并设置有效期
     *
     * @param   integer  有效期,默认为空
     * @return  $this
     */
    public function cached($lifetime = null)
    {
        $this->_lifetime = $lifetime;
        return $this;
    }

    /**
     * 联合数组形式返回结果
     *
     * @return  $this
     */
    public function as_assoc()
    {
        $this->_as_object = false;
        return $this;
    }

    /**
     * 将结果以对象形式返回. 并可以设置返回结果返回的对象模型
     *
     * // 获取一个 stdClass 对象形式结果
     * $query->as_object(true); 或者 $query->as_object(); 
     *
     * // 将结果返回至 User_Model 模型,就是每行结果都是一个 User_Model 对象.可以像操作User_Model对象一样对每行数据进行操作
     * $query->as_object('User_Model');
     *
     * @param   string  指定对象模型或者stdClass对象, 布尔值true则 stdClass.
     * @return  $this
     */
    public function as_object($class = true)
    {
        $this->_as_object = $class;
        return $this;
    }

    /**
     * 向查询操作添加一条参数数据
     *
     * // 例如替换 user id 
     * $row = $db->query(Database::SELECT, 'SELECT * FROM users where id = :id LIMIT 1')->param(':id', 1);
     * //返回结果 ”SELECT * FROM users where id = 1 LIMIT 1“
     *
     * @param   string   要替换的参数key
     * @param   mixed    替换值
     * @return  $this
     */
    public function param($param, $value)
    {
        $this->_parameters[$param] = $value;
        return $this;
    }

    /**
     * param 的别名
     *
     * @param   string   要替换的参数key
     * @param   mixed    替换值
     * @return  $this
     */
    public function bind($param, $var)
    {
        $this->_parameters[$param] = $var;
        return $this;
    }

    /**
     * 向查询操作添加多条参数数据.
     *
     * @param   array  参数集合
     * @return  $this
     */
    public function parameters(array $params)
    {
        $this->_parameters = $params + $this->_parameters;
        return $this;
    }

    /**
     * 编译sql字符串,并返回
     *
     * @param   object  数据库实例
     * @return  string  SQL字符串
     */
    public function compile($db)
    {
        $sql = $this->_sql;

        if( ! empty($this->_parameters))
        {
            $values = array_map(array($db, 'quote'), $this->_parameters);
            $sql    = strtr($sql, $values);
        }

        return $sql;
    }

    /**
     * 在指定数据库执行当前查询.
     *
     * @param   mixed    数据库配置组名或者数据库实例
     * @return  object   SELECT 操作会返回 Database_Result 对象
     * @return  mixed    INSERT 操作会返回 insert id
     * @return  integer  所有写操作都会返回受影响条数
     */
    public function execute($db = null)
    {
        if( ! is_object($db))
        {
            $db = Database::instance($db);
        }

        $sql = $this->compile($db);

        if( ! empty($this->_lifetime) and $this->_type === Database::SELECT)
        {
            $cache_key = 'Database::query("' . $db . '", "' . $sql . '")';
            $result    = QuickPHP::cache($cache_key, null, $this->_lifetime);

            if( ! empty($result))
            {
                return new QuickPHP_Database_Result_Cached($result, $sql, $this->_as_object);
            }
        }

        $result = $db->query($this->_type, $sql, $this->_as_object);

        if(isset($cache_key))
        {
            QuickPHP::cache($cache_key, $result->as_array(), $this->_lifetime);
        }

        return $result;
    }
}