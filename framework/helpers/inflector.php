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
 * QuickPHP Inflector 英文单复数助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  inflector
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: inflector.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_inflector
{

    protected static $cache = array();
    protected static $uncountable;
    protected static $irregular;

    /**
     * 如果一个词是不可数的定义检查
     *
     * @param   string   待检测的单词
     * @return  boolean
     */
    public static function uncountable($str)
    {
        if(inflector::$uncountable === null)
        {
            inflector::$uncountable = QuickPHP::config('inflector')->get('uncountable', null);
        }

        return isset(inflector::$uncountable[strtolower($str)]);
    }

    /**
     * 把一个单词的复数形式更改为单数形式并返回转换后的字符串。如果字符串是不可数的将会无修改返回
     *
     * @param   string  待转换的单词（一般是单复数）
     * @param   integer 单词实质的数量 - 默认 null
     * @return  string
     */
    public static function singular($str, $count = null)
    {
        $str = strtolower(trim($str));

        if(is_string($count))
        {
            $count = (int) $count;
        }

        if($count === 0 or $count > 1)
        {
            return $str;
        }

        $key = 'singular_' . $str . $count;

        if(isset(inflector::$cache[$key]))
        {
            return inflector::$cache[$key];
        }

        if(inflector::uncountable($str))
        {
            return inflector::$cache[$key] = $str;
        }

        if(empty(inflector::$irregular))
        {
            inflector::$irregular = QuickPHP::config('inflector')->irregular;
        }

        $irregular = array_search($str, inflector::$irregular);

        if( ! empty($irregular))
        {
            $str = $irregular;
        }
        elseif(preg_match('/[sxz]es$/', $str) or preg_match('/[^aeioudgkprt]hes$/', $str))
        {
            $str = substr($str, 0, - 2);
        }
        elseif(preg_match('/[^aeiou]ies$/', $str))
        {
            $str = substr($str, 0, - 3) . 'y';
        }
        elseif(substr($str, - 1) === 's' and substr($str, - 2) !== 'ss')
        {
            $str = substr($str, 0, - 1);
        }

        return inflector::$cache[$key] = $str;
    }

    /**
     * 单数转复数形式
     *
     * @param   string  待转换的字符串
     * @return  string
     */
    public static function plural($str, $count = null)
    {
        $str = strtolower(trim($str));

        if(is_string($count))
        {
            $count = (int) $count;
        }

        if($count === 1)
        {
            return $str;
        }

        $key = 'plural_' . $str . $count;

        if(isset(inflector::$cache[$key]))
        {
            return inflector::$cache[$key];
        }

        if(inflector::uncountable($str))
        {
            return inflector::$cache[$key] = $str;
        }

        if(empty(inflector::$irregular))
        {
            inflector::$irregular = QuickPHP::config('inflector')->irregular;
        }

        if(isset(inflector::$irregular[$str]))
        {
            $str = inflector::$irregular[$str];
        }
        elseif(preg_match('/[sxz]$/', $str) or preg_match('/[^aeioudgkprt]h$/', $str))
        {
            $str .= 'es';
        }
        elseif(preg_match('/[^aeiou]y$/', $str))
        {
            $str = substr_replace($str, 'ies', - 1);
        }
        else
        {
            $str .= 's';
        }

        return inflector::$cache[$key] = $str;
    }

    /**
     * 一个短语转化成骆驼字符形式
     *
     * @param   string  phrase to camelize
     * @return  string
     */
    public static function camelize($str)
    {
        $str = 'x' . strtolower(trim($str));
        $str = ucwords(preg_replace('/[\s_]+/', ' ', $str));

        return substr(str_replace(' ', '', $str), 1);
    }

    /**
     * 把一个以空格或下划线分隔的单词字符串更改为骆驼拼写法并返回转换后的字符串。
     *
     * @param   string  phrase to underscore
     * @return  string
     */
    public static function underscore($str)
    {
        return preg_replace('/\s+/', '_', trim($str));
    }

    /**
     * 转换字符串为人类可读的文字并返回转换后的字符串
     *
     * @param   string  phrase to make human-reable 人力reable短语
     * @return  string
     */
    public static function humanize($str)
    {
        return preg_replace('/[_-]+/', ' ', trim($str));
    }
}