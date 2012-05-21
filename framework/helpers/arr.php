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
 * QuickPHP 数组助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  arr
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: arr.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_arr
{

    /**
     * 测试是否是数组组合
     *
     * // 返回 true
     * arr::is_assoc(array('username' => 'john.doe'));
     *
     * // 返回 false
     * arr::is_assoc('foo', 'bar');
     *
     * @param   array   $array
     * @return  boolean
     */
    public static function is_assoc($array)
    {
        $keys = is_array($array) ? array_keys($array) : $array;
        $diff = is_array($keys) ? array_keys($keys) : $keys;

        return (bool) ($diff !== $keys);
    }

    /**
     * 获取"."隔开的路径使用数组的值。
     *
     * // 获得 $array['foo']['bar']的值则:
     * $value = arr::path($array, 'foo.bar');
     *
     * 使用通配符“*”将搜索中间数组并返回一个数组。
     *
     * // 获得 theme键值下的“color”的值则:
     * $colors = arr::path($array, 'theme.*.color');
     *
     * @param array $array
     * @param string $path
     * @param mixed $default
     */
    public static function path($array, $path, $default = null)
    {
        $path = trim($path, '.* ');
        $keys = explode('.', $path);

        do
        {
            $key = array_shift($keys);

            if(ctype_digit($key))
            {
                $key = (int) $key;
            }

            if(isset($array[$key]))
            {
                if($keys)
                {
                    if(is_array($array[$key]))
                    {
                        $array = $array[$key];
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    return $array[$key];
                }
            }
            elseif($key === '*')
            {
                if(empty($keys))
                {
                    return $array;
                }

                $values = array();

                foreach ($array as $arr)
                {
                    $value = arr::path($arr, implode('.', $keys));

                    if( ! empty($value))
                    {
                        $values[] = $value;
                    }
                }

                if($values)
                {
                    return $values;
                }
                else
                {
                    break;
                }
            }
            else
            {
                break;
            }
        }
        while($keys);

        return $default;
    }

    /**
     * 生成一个按一定步数范围填补数字的数组。
     *
     * // 例如要生成 5, 10, 15, 20则:
     * $values = arr::range(5, 20);
     *
     * @param   integer  步数
     * @param   integer  最大值
     * @return  array
     */
    public static function range($step = 10, $max = 100)
    {
        if($step < 1)
        {
            return array();
        }

        $array = array();

        for ($i = $step; $i <= $max; $i += $step)
        {
            $array[$i] = $i;
        }

        return $array;
    }

    /**
     * 从一个数组检索一个键。如果该键不存，将返回默认值。
     *
     * // 从 $_POST从取出 "username"
     * $username = arr::get($_POST, 'username');
     *
     * // 从 $_GET从取出 "sorting"
     * $sorting = arr::get($_GET, 'sorting');
     *
     * @param   array   要检索的数组
     * @param   string  键名
     * @param   mixed   默认值
     * @return  mixed
     */
    public static function get($array, $key, $default = null)
    {
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * 从数组中检索多个键。如果该键不存在于数组，则被默认值代替。
     *
     * // 从 $_POST从取出 "username", "password"
     * $auth = arr::extract($_POST, array('username', 'password'));
     *
     * @param   array   要检索的数组
     * @param   array   键名列表
     * @param   mixed   默认值
     * @return  array
     */
    public static function extract($array, array $keys, $default = null)
    {
        $found = array();

        foreach ($keys as $key)
        {
            $found[$key] = isset($array[$key]) ? $array[$key] : $default;
        }

        return $found;
    }

    /**
     * 添加一个值关联到数组的开头.
     *
     * // 添加一个空值的一个选择列表开头
     * arr::unshift_assoc($array, 'none', 'value');
     *
     * @param   array   要修改的数组
     * @param   string  键名
     * @param   mixed   值
     * @return  array
     */
    public static function unshift(array & $array, $key, $val)
    {
        $array       = array_reverse($array, true);
        $array[$key] = $val;
        $array       = array_reverse($array, true);

        return $array;
    }

    /**
     * 合并一个或多个数组递归并保留所有的键.
     * 请注意，该方法的工作机制并不同于[array_merge_recursive](http://php.net/array_merge_recursive)!
     *
     * // Apply "strip_tags" to every element in the array
     * $array = arr::map('strip_tags', $array);
     *
     * [!!] Unlike `array_map`, this method requires a callback and will only map
     * a single array.
     *
     * @param   mixed   回调并追加到数组的每个元素
     * @param   array   要操作的数组
     * @return  array
     */
    public static function map($callback, $array)
    {
        foreach ($array as $key => $val)
        {
            if(is_array($val))
            {
                $array[$key] = arr::map($callback, $val);
            }
            else
            {
                $array[$key] = call_user_func($callback, $val);
            }
        }

        return $array;
    }

    /**
     * 合并一个或多个数组递归并保留所有的键.
     * 请注意，这个方法不同于函数 [array_merge_recursive](http://php.net/array_merge_recursive)!
     *
     * $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
     * $mary = array('name' => 'mary', 'children' => array('jane'));
     *
     * // john和mary结合，把它们合并起来
     * $john = arr::merge($john, $mary);
     *
     * // john的输出现在是:
     * array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
     *
     * @param   array  初始数组
     * @param   array  要合并的数组
     * @param   array  ...
     * @return  array
     */
    public static function merge(array $a1, array $a2)
    {
        $result = array();

        for ($i = 0, $total = func_num_args(); $i < $total; $i++)
        {
            $arr   = func_get_arg($i);
            $assoc = arr::is_assoc($arr);

            foreach ($arr as $key => $val)
            {
                if(isset($result[$key]))
                {
                    if(is_array($val))
                    {
                        if(arr::is_assoc($val))
                        {
                            $result[$key] = arr::merge($result[$key], $val);
                        }
                        else
                        {
                            $diff = array_diff($val, $result[$key]);
                            $result[$key] = array_merge($result[$key], $diff);
                        }
                    }
                    else
                    {
                        if($assoc)
                        {
                            $result[$key] = $val;
                        }
                        elseif( ! in_array($val, $result, true))
                        {
                            $result[] = $val;
                        }
                    }
                }
                else
                {
                    $result[$key] = $val;
                }
            }
        }

        return $result;
    }

    /**
     * 覆盖与数组的输入值的数组。不会被添加第一个数组键不存的内容(只覆盖不增加)！
     *
     *
     * $a1 = array('name' => 'john', 'mood' => 'happy', 'food' => 'bacon');
     * $a2 = array('name' => 'jack', 'food' => 'tacos', 'drink' => 'beer');
     *
     * // 将$a1覆盖$a2
     * $array = arr::overwrite($a1, $a2);
     *
     * // 现在$array的输出为:
     * array('name' => 'jack', 'mood' => 'happy', 'food' => 'tacos')
     *
     * @param   array   主要数组
     * @param   array   要覆盖的数组
     * @return  array
     */
    public static function overwrite($array1, $array2)
    {
        foreach (array_intersect_key($array2, $array1) as $key => $value)
        {
            $array1[$key] = $value;
        }

        if (func_num_args() > 2)
        {
            foreach (array_slice(func_get_args(), 2) as $array2)
            {
                foreach (array_intersect_key($array2, $array1) as $key => $value)
                {
                    $array1[$key] = $value;
                }
            }
        }

        return $array1;
    }

    /**
     * 创建一个从字符串表示形式可调用函数和参数列表
     * 请注意，此功能不验证回调字符串.
     *
     * // 获得这个回调函数和参数则:
     * list($func, $params) = arr::callback('Foo::bar(apple,orange)');
     *
     * // 获取回调结果
     * $result = call_user_func_array($func, $params);
     *
     * @param   string  回调参数字符串
     * @return  array   function, params
     */
    public static function callback($str)
    {
        $command = $params = null;

        if(preg_match('/^([^\(]*+)\((.*)\)$/', $str, $match))
        {
            $command = $match[1];

            if($match[2] !== '')
            {
                $params = preg_split('/(?<!\\\\),/', $match[2]);
                $params = str_replace('\,', ',', $params);
            }
        }
        else
        {
            $command = $str;
        }

        if(strpos($command, '::') !== false)
        {
            $command = explode('::', $command, 2);
        }

        return array($command, $params);
    }

    /**
     * 将目标数组转换成一维数组
     *
     * $array = array('set' => array('one' => 'something'), 'two' => 'other');
     *
     * // 压缩这个数组
     * $array = arr::flatten($array);
     *
     * // 该数组现在输出为
     * array('one' => 'something', 'two' => 'other');
     *
     * [!!] 数组值的键将被会丢弃.
     *
     * @param   array   要压缩的数组
     * @return  array
     */
    public static function flatten($array)
    {
        $flat = array();

        foreach ($array as $key => $value)
        {
            if(is_array($value))
            {
                $flat += arr::flatten($value);
            }
            else
            {
                $flat[$key] = $value;
            }
        }

        return $flat;
    }

    /**
     * 移出一个键值.
     *
     * @param   string  要移除的key
     * @param   array   要操作的数组
     * @return  mixed   被移除的数组项
     */
    public static function remove($key, $array)
    {
        if ( ! array_key_exists($key, $array))
        {
            return null;
        }

        $val = $array[$key];
        unset($array[$key]);

        return $val;
    }
}
