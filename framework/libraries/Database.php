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
 * 数据库连接的包装。 所有数据库实例是参照一个名字。 查询通常由[Database_Query],而不是用数据库对象直
 *
 * @category    QuickPHP
 * @package     Librares
 * @subpackage  Database
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Config.php 8641 2012-01-05 08:35:39Z bopo $
 */
class QuickPHP_Database
{

    /**
     * @var  string  数据库SElect操作类型
     */    
    const SELECT = 1;

    /**
     * @var  string  数据库SElect操作类型
     */   
    const INSERT = 2;

    /**
     * @var  string  数据库SElect操作类型
     */
    const UPDATE = 3;
    
    /**
     * @var  string  数据库SElect操作类型
     */    
    const DELETE = 4;

    /**
     * @var  string  默认实例名称
     */
    public static $default = 'default';

    /**
     * @var  array  数据库实例容器
     */
    public static $_instances = array();

    /**
     * 让一个子数据库实例。如果配置不规定外,从数据库中进行装货作业配置文件使用同一组的名称。
     *
     * // 加载一个默认的数据库实例
     * $db = Database::instance();
     *
     * // 加载一个自定义配置的数据库实例
     * $db = Database::instance('custom', $config);
     *
     * @param   string   实例名称
     * @param   array    配置参数
     * @return  Database
     */
    public static function instance($name = null, array $config = null)
    {
        if($name === null)
        {
            $name = Database::$default;
        }

        if( ! isset(Database::$_instances[$name]))
        {
            if($config === null)
            {
                $config = QuickPHP::config('database')->get($name, array());
            }

            if( ! isset($config['type']))
            {
                throw new Database_Exception("undefined_group");
            }

            $driver = 'Database_Driver_' . ucfirst($config['type']);
            Database::$_instances[$name] = new $driver($name, $config);
        }

        return Database::$_instances[$name];
    }

    /**
     * 销毁已经存在的数据库链接实例
     *
     * @param   string   数据库配置组名
     * @return  Database
     */
    public static function destroy($name = null)
    {
        if (empty($name))
        {
            Database::$_instances = array();
        }
        else
        {
            if (isset(Database::$_instances[$name])) 
            {
                unset(Database::$_instances[$name]);
            }
        }
    }

    /**
     * 创建一个新的数据库请求[Database_Query]
     *
     * // 创建一个数据库SELECT请求
     * $query = Database::query(Database::SELECT, 'SELECT * FROM users');
     *
     * // 创建一个数据库DELETE请求
     * $query = Database::query(Database::DELETE, 'DELETE FROM users WHERE id = 5');
     *
     * @param   integer  类型: Database::SELECT, Database::UPDATE, Database::INSERT 等...
     * @param   string   SQL statement
     * @return  Database_Query
     */
    public static function query($type, $sql)
    {
        return new Database_Query($type, $sql);
    }

    /**
     * 以数组为参数创建一个SELECT操作. 使用数组方式构建一个别名查询
     *
     * // SELECT id, username
     * $query = Database::select('id', 'username');
     *
     * // SELECT id AS user_id
     * $query = Database::select(array('id', 'user_id'));
     *
     * @param   mixed   字段名，数组 array($column, $alias) 形式或者为对象
     * @param   ...
     * @return  Database_Query_Builder_Select
     */
    public static function select($columns = null)
    {
        return new Database_Query_Builder_Select(func_get_args());
    }

    /**
     * 以数组为参数创建一个SELECT操作
     *
     * // SELECT id, username
     * $query = Database::select_array(array('id', 'username'));
     *
     * @param   array   columns to select
     * @return  Database_Query_Builder_Select
     */
    public static function select_array(array $columns = null)
    {
        return new Database_Query_Builder_Select($columns);
    }

    /**
     * 创建一个Insert操作
     *
     * // INSERT INTO users (id, username)
     * $query = Database::insert('users', array('id', 'username'));
     *
     * @param   string  要插入的表
     * @param   array   字段列表,可以说字段名，或者 array($column, $alias) 或者是对象形式
     * @return  Database_Query_Builder_Insert
     */
    public static function insert($table, array $columns = null)
    {
        return new Database_Query_Builder_Insert($table, $columns);
    }

    /**
     * 创建一个UPDATE操作
     *
     * // UPDATE users
     * $query = Database::update('users');
     *
     * @param   string  要更新的表
     * @return  Database_Query_Builder_Update
     */
    public static function update($table)
    {
        return new Database_Query_Builder_Update($table);
    }

    /**
     * 创建删除操作
     *
     * // DELETE FROM users
     * $query = Database::delete('users');
     *
     * @param   string  要删除的表
     * @return  Database_Query_Builder_Delete
     */
    public static function delete($table)
    {
        return new Database_Query_Builder_Delete($table);
    }

    /**
     * 数据库表达式逃逸方法.
     *
     * $expression = Database::expr('COUNT(users.id)');
     *
     * @param   string  表达式字符串
     * @return  Database_Expression
     */
    public static function expr($string)
    {
        return new Database_Expression($string);
    }
}