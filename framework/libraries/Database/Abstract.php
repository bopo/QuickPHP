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
 * 数据库连接封装器(抽象类)
 *
 * @category   QuickPHP
 * @package    Database
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
abstract class QuickPHP_Database_Abstract
{

    /**
     * @var  string  最后请求字符串
     */
    public $last_query;

    // SQL字符引用标示符
    protected $_identifier = '"';

    // 实例容器
    protected $_instance;

    // 原始服务器连接
    protected $_connection;

    // 配置数组
    protected $_config;

    /**
     * 在本地存储数据库配置并命名实例。
     *
     * @return  void
     */
    public function __construct($name, array $config)
    {
        $this->_instance = $name;
        $this->_config   = $config;
    }

    /**
     * 当对象被销毁时,断开数据库连接。
     *
     * // 销毁一个数据库对象
     * unset(Database::instances[(string) $db], $db);
     *
     * [!!] 使用 `unset($db)` 并不能销毁数据库对象, 它是存储`Database::$_instances`里的
     *
     * @return  void
     */
    final public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * 返回数据库实例名.
     *
     * echo (string) $db;
     *
     * @return  string
     */
    final public function __toString()
    {
        return $this->_instance;
    }

    /**
     * 抽象方法，连接数据库. 这个方法是在请求的时候自动被调用,因此不用特别单独去调用
     *
     * $db->connect();
     *
     * @throws  Database_Exception
     * @return  void
     */
    abstract public function connect();

    /**
     * 断开数据库连接. 会被[Database::__destruct] 自动调用.
     *
     * $db->disconnect();
     *
     * @return  boolean
     */
    abstract public function disconnect();

    /**
     * 设置连接的字符集. 会被[Database::connect] 自动调用.
     *
     * $db->set_charset('utf8');
     *
     * @throws  Database_Exception
     * @param   string   character set name
     * @return  void
     */
    abstract public function set_charset($charset);

    /**
     * 按指定类型执行一个SQL查询,并可以设置返回结果返回的对象模型
     *
     * // 获取一个SELECT查询
     * $db->query(Database::SELECT, 'SELECT * FROM groups', true);
     *
     * 让一个SELECT查询并且将结果返回至 User_Model 模型,就是每行结果都是一个 User_Model 对象.可以像操作User_Model对象一样对每行数据进行操作
     * $db->query(Database::SELECT, 'SELECT * FROM users LIMIT 1', 'User_Model');
     *
     * @param   integer  Database::SELECT, Database::INSERT, 等查询类型
     * @param   string   SQL查询字符串
     * @param   mixed    返回对象类, 布尔值真则为 stdClass, 布尔值否则为关联数组形式
     * @return  object   SELECT 请求返回 Database_Result 对象
     * @return  array    INSERT 请求返回 最后插入数据库主键值和影响列数
     * @return  integer  其他请求返回影响列数(整形)
     */
    abstract public function query($type, $sql, $as_object);

    /**
     * 统计过去的查询的次数,没有加 LIMIT 或 OFFSET 的操作限制
     *
     * $count = $db->count_last_query();
     *
     * @return  integer
     */
    public function count_last_query()
    {
        $sql = $this->last_query;

        if( ! empty($sql))
        {
            $sql = trim($sql);

            if(stripos($sql, 'SELECT') !== 0)
            {
                return false;
            }

            if(stripos($sql, 'LIMIT') !== false)
            {
                $sql = preg_replace('/\sLIMIT\s+[^a-z]+/i', ' ', $sql);
            }

            if(stripos($sql, 'OFFSET') !== false)
            {
                $sql = preg_replace('/\sOFFSET\s+\d+/i', '', $sql);
            }

            $result = $this->query(Database::SELECT, 'SELECT COUNT(*) AS ' . $this->quote_identifier('total_rows') . ' ' . 'FROM (' . $sql . ') AS ' . $this-quote_table('counted_results'), true);

            return (int) $result->current()->total_rows;
        }

        return false;
    }

    /**
     * 统计一个表有多条记录.
     *
     * // 统计users表的记录数
     * $count = $db->count_records('users');
     *
     * @param   mixed    table name string or array(query, alias)
     * @return  integer
     */
    public function count_records($table)
    {
        $table = $this->quote_identifier($table);
        return $this->query(Database::SELECT, 'SELECT COUNT(*) AS total_row_count FROM ' . $table, false)->get('total_row_count');
    }

    /**
     * 返回一个SQL规范化数据类型的数组
     *
     * $db->datatype('char');
     *
     * @param   string  SQL data type
     * @return  array
     */
    public function datatype($type)
    {
        static $types = array(
            // SQL-92
            'bit'                             => array('type' => 'string', 'exact' => true),
            'bit varying'                     => array('type' => 'string'),
            'char'                            => array('type' => 'string', 'exact' => true),
            'char varying'                    => array('type' => 'string'),
            'character'                       => array('type' => 'string', 'exact' => true),
            'character varying'               => array('type' => 'string'),
            'date'                            => array('type' => 'string'),
            'dec'                             => array('type' => 'float', 'exact' => true),
            'decimal'                         => array('type' => 'float', 'exact' => true),
            'double precision'                => array('type' => 'float'),
            'float'                           => array('type' => 'float'),
            'int'                             => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
            'integer'                         => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
            'interval'                        => array('type' => 'string'),
            'national char'                   => array('type' => 'string', 'exact' => true),
            'national char varying'           => array('type' => 'string'),
            'national character'              => array('type' => 'string', 'exact' => true),
            'national character varying'      => array('type' => 'string'),
            'nchar'                           => array('type' => 'string', 'exact' => true),
            'nchar varying'                   => array('type' => 'string'),
            'numeric'                         => array('type' => 'float', 'exact' => true),
            'real'                            => array('type' => 'float'),
            'smallint'                        => array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
            'time'                            => array('type' => 'string'),
            'time with time zone'             => array('type' => 'string'),
            'timestamp'                       => array('type' => 'string'),
            'timestamp with time zone'        => array('type' => 'string'),
            'varchar'                         => array('type' => 'string'),

            // SQL:1999
            'binary large object'             => array('type' => 'string', 'binary' => true),
            'blob'                            => array('type' => 'string', 'binary' => true),
            'boolean'                         => array('type' => 'bool'),
            'char large object'               => array('type' => 'string'),
            'character large object'          => array('type' => 'string'),
            'clob'                            => array('type' => 'string'),
            'national character large object' => array('type' => 'string'),
            'nchar large object'              => array('type' => 'string'),
            'nclob'                           => array('type' => 'string'),
            'time without time zone'          => array('type' => 'string'),
            'timestamp without time zone'     => array('type' => 'string'),

            // SQL:2003
            'bigint'                          => array('type' => 'int',
            'min'                             => '-9223372036854775808',
            'max'                             => '9223372036854775807'),

            // SQL:2008
            'binary'                          => array('type' => 'string', 'binary' => true, 'exact' => true),
            'binary varying'                  => array('type' => 'string', 'binary' => true),
            'varbinary'                       => array('type' => 'string', 'binary' => true),
        );

        if (isset($types[$type]))
        {
            return $types[$type];
        }

        return array();
    }

    /**
     * 列出数据库中的所有表。表名可以使用通配符查找相似表。
     *
     * // 获取当前数据库中的所有表
     * $tables = $db->list_tables();
     *
     * // 所有与user开头表名的表
     * $tables = $db->list_tables('user%');
     *
     * @param   string   table to search for
     * @return  array
     */
    abstract public function list_tables($like = null);

    /**
     * 获取表中字段(列)信息，并可以使用 SQL 的 LIKE 语法方式进行过滤.
     * 
     * // 获取“users”表所有字段(列)信息
     * $columns = $db->list_columns('users');
     *
     * // 获取“users”表所有包含 “name” 的字段(列)信息
     * $columns = $db->list_columns('users', '%name%');
     *
     * @param   string  表名
     * @param   string  要过滤的字段(列)名称
     * @return  array
     */
    abstract public function list_columns($table, $like = null);

    /**
     * 解析SQL类型
     *
     * // 返回: array('CHAR', '6')
     * list($type, $length) = $db->_parse_type('CHAR(6)');
     *
     * @param   string
     * @return  array   list containing the type and length, if any
     */
    protected function _parse_type($type)
    {
        if(($open = strpos($type, '(')) === false)
        {
            return array($type, null);
        }

        $close  = strpos($type, ')', $open);
        $length = substr($type, $open + 1, $close - 1 - $open);
        $type   = substr($type, 0, $open) . substr($type, $close + 1);

        return array($type, $length);
    }

    /**
     * 返回表名前缀.
     *
     * $prefix = $db->table_prefix();
     *
     * @return  string
     */
    public function table_prefix()
    {
        return $this->_config['table_prefix'];
    }

    public function table_exists($table = null, $like = null)
    {
        return in_array($table, $this->list_tables($like));
    }

    /**
     * 引用一个SQL查询的值.判断数据类型，是字符串还是数字等
     *
     * $db->quote(null);   // 'null'
     * $db->quote(10);     // 10
     * $db->quote('fred'); // 'fred'
     *
     * 如果传入的值是对象,则将被转换为字符串形式.
     * [Database_Expression]对象将使用逃逸表达式的值.
     * [Database_Query]对象将编译并转换为一个子查询.
     * 其余对象将使用魔术方法 “__toString” 转换成字符串.
     *
     * @param   mixed   要引用的值
     * @return  string
     * @uses    Database::escape
     */
    public function quote($value)
    {
        if($value === null)
        {
            return 'null';
        }
        elseif($value === true)
        {
            return "'1'";
        }
        elseif($value === false)
        {
            return "'0'";
        }
        elseif(is_object($value))
        {
            if($value instanceof Database_Query)
            {
                return '(' . $value->compile($this) . ')';
            }
            elseif($value instanceof Database_Expression)
            {
                return $value->value();
            }
            else
            {
                return $this->quote((string) $value);
            }
        }
        elseif(is_array($value))
        {
            return '(' . implode(', ', array_map(array($this, __FUNCTION__), $value)) . ')';
        }
        elseif(is_int($value))
        {
            return (int) $value;
        }
        elseif(is_float($value))
        {
            return sprintf('%F', $value);
        }

        return $this->escape($value);
    }

    /**
     * 引用一个数据库表名,将表名加上前缀,加上SQL关键字标示符
     *
     * $table = $db->quote_table($table);
     *
     * @param   mixed   表名或者 array(表名, 别名)
     * @return  string
     * @uses    Database::quote_identifier
     * @uses    Database::table_prefix
     */
    public function quote_table($value)
    {
        if(is_array($value))
        {
            $table = & $value[0];
        }
        else
        {
            $table = & $value;
        }

        if(is_string($table) and strpos($table, '.') === false)
        {
            $table = $this->table_prefix() . $table;
        }

        return $this->quote_identifier($value);
    }

    /**
     * 将一个字段加上数据库标识符. 如果是带有表名的字段(例如 users.id), 则将表名和字段名分开进行引用(例如MYSQL返回: `users`.`id`)
     *
     * $column = $db->quote_identifier($column);
     *
     * // 您也可以使用SQL中函数加标识符,下面表达式返回: COUNT(`column`).
     * $column = $db->quote_identifier('COUNT("column")');
     *
     * 如果传入的值是对象,则将被转换为字符串形式.
     * [Database_Expression]对象将使用逃逸表达式的值.
     * [Database_Query]对象将编译并转换为一个子查询.
     * 其余对象将使用魔术方法 “__toString” 转换成字符串.
     *
     * @param   mixed   任意需要加标示符的对象
     * @return  string
     * @uses    Database::table_prefix
     */
    public function quote_identifier($value)
    {
        if($value === '*')
        {
            return $value;
        }
        elseif(is_object($value))
        {
            if($value instanceof Database_Query)
            {
                return '(' . $value->compile($this) . ')';
            }
            elseif($value instanceof Database_Expression)
            {
                return $value->value();
            }
            else
            {
                return $this->quote_identifier((string) $value);
            }
        }
        elseif(is_array($value))
        {
            list ($value, $alias) = $value;
            return $this->quote_identifier($value) . ' AS ' . $this->quote_identifier($alias);
        }

        if(strpos($value, '"') !== false)
        {
            return preg_replace('/"(.+?)"/e', '$this->quote_identifier("$1")', $value);
        }
        elseif(strpos($value, '.') !== false)
        {
            $parts  = explode('.', $value);
            $prefix = $this->table_prefix();

            if( ! empty($prefix))
            {
                $offset         = count($parts) - 2;
                $parts[$offset] = $prefix . $parts[$offset];
            }

            return implode('.', array_map(array($this, __FUNCTION__), $parts));
        }
        else
        {
            if( ! empty($this->_identifier))
            {
                if(count($this->_identifier) > 1)
                {
                    list($_identifier_l, $_identifier_r) = $this->_identifier;
                }
                else
                {
                    list($_identifier_l, $_identifier_r) = array(current($this->_identifier),end($this->_identifier));
                }

                return $_identifier_l . $value . $_identifier_r;
            }

            return $value;
        }
    }

    /**
     * 清理字符串的转义字符,避免导致SQL注入攻击。
     * 
     * $value = $db->escape('any string');
     *
     * @param   string   要清理的字符串
     * @return  string
     */
    abstract public function escape($value);
}