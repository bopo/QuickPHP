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
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license.html
 * @version     $Id: Database.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_Database
{
    // 请求类型
    const SELECT = 1;
    const INSERT = 2;
    const UPDATE = 3;
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
    public static function instance($name = NULL, array $config = NULL)
    {
        if($name === NULL)
        {
            $name = Database::$default;
        }

        if( ! isset(Database::$_instances[$name]))
        {
            if($config === NULL)
            {
                $config = QuickPHP::config('database')->get($name, array());
            }

            if( ! isset($config['type']))
            {
                throw new QuickPHP_Database_Exception("Database type not defined in {$name} configuration");
            }

            $driver = 'Database_Driver_' . ucfirst($config['type']);
            Database::$_instances[$name] = new $driver($name, $config);
        }

        return Database::$_instances[$name];
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
     * Specifying the type changes the returned result. When using
     * `Database::SELECT`, a [Database_Query_Result] will be returned.
     * `Database::INSERT` queries will return the insert id and number of rows.
     * For all other queries, the number of affected rows is returned.
     *
     * @param   integer  type: Database::SELECT, Database::UPDATE, etc
     * @param   string   SQL statement
     * @return  Database_Query
     */
    public static function query($type, $sql)
    {
        return new Database_Query($type, $sql);
    }

    /**
     * Create a new [Database_Query_Builder_Select]. Each argument will be
     * treated as a column. To generate a `foo AS bar` alias, use an array.
     *
     * // SELECT id, username
     * $query = Database::select('id', 'username');
     *
     * // SELECT id AS user_id
     * $query = Database::select(array('id', 'user_id'));
     *
     * @param   mixed   column name or array($column, $alias) or object
     * @param   ...
     * @return  Database_Query_Builder_Select
     */
    public static function select($columns = NULL)
    {
        return new Database_Query_Builder_Select(func_get_args());
    }

    /**
     * Create a new [Database_Query_Builder_Select] from an array of columns.
     * 创建一个SELECT操作
     *
     * // SELECT id, username
     * $query = Database::select_array(array('id', 'username'));
     *
     * @param   array   columns to select
     * @return  Database_Query_Builder_Select
     */
    public static function select_array(array $columns = NULL)
    {
        return new Database_Query_Builder_Select($columns);
    }

    /**
     * Create a new [Database_Query_Builder_Insert].
     * 创建一个Insert操作
     *
     * // INSERT INTO users (id, username)
     * $query = Database::insert('users', array('id', 'username'));
     *
     * @param   string  table to insert into
     * @param   array   list of column names or array($column, $alias) or object
     * @return  Database_Query_Builder_Insert
     */
    public static function insert($table, array $columns = NULL)
    {
        return new Database_Query_Builder_Insert($table, $columns);
    }

    /**
     * Create a new [Database_Query_Builder_Update].
     * 创建一个UPDATE操作
     *
     * // UPDATE users
     * $query = Database::update('users');
     *
     * @param   string  table to update
     * @return  Database_Query_Builder_Update
     */
    public static function update($table)
    {
        return new Database_Query_Builder_Update($table);
    }

    /**
     * Create a new [Database_Query_Builder_Delete].
     * 创建删除操作
     *
     * // DELETE FROM users
     * $query = Database::delete('users');
     *
     * @param   string  table to delete from
     * @return  Database_Query_Builder_Delete
     */
    public static function delete($table)
    {
        return new Database_Query_Builder_Delete($table);
    }

    /**
     * 创建一个新的[Database_Expression]不是逃逸了。 一个表达式是唯一的办法,使用SQL查询职能部门建设者。
     *
     * $expression = Database::expr('COUNT(users.id)');
     *
     * @param   string  expression
     * @return  Database_Expression
     */
    public static function expr($string)
    {
        return new Database_Expression($string);
    }

    /**
     * 创建一个新的[Database_Utility]不是逃逸了。 一个表达式是唯一的办法,使用SQL查询职能部门建设者。
     *
     * $expression = Database::utility();
     *
     * @param   string  expression
     * @return  Database_Expression
     */
    public static function utility()
    {
        return new Database_Utility();
    }
}