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
 * QuickPHP URI相关助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: uri.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_uri
{
    /**
     * 检索一个特定的URI部分
     *
     * @param   integer|string  segment number or label
     * @param   mixed           default value returned if segment does not exist
     * @return  string
     */
    public static function segment($index = 1, $default = FALSE)
    {
        if(is_string($index))
        {
            if(($key = array_search($index, QuickPHP::route()->segments)) === FALSE)
            {
                return $default;
            }

            $index = $key + 2;
        }

        $index = (int) $index - 1;

        return isset(QuickPHP::route()->segments[$index]) ? QuickPHP::route()->segments[$index] : $default;
    }

    /**
     * 检索特定路由的URI部分
     *
     * @param   integer|string  rsegment number or label
     * @param   mixed           default value returned if segment does not exist
     * @return  string
     */
    public static function rsegment($index = 1, $default = FALSE)
    {
        if(is_string($index))
        {
            if(($key = array_search($index, QuickPHP::route()->rsegments)) === FALSE)
            {
                return $default;
            }

            $index = $key + 2;
        }

        $index = (int) $index - 1;

        return isset(QuickPHP::route()->rsegments[$index]) ? QuickPHP::route()->rsegments[$index] : $default;
    }

    /**
     * 检索一个特定的URI参数。这是该段，这并不表明控制器或方法的一部分
     *
     * @param   integer|string  argument number or label
     * @param   mixed           default value returned if segment does not exist
     * @return  string
     */
    public static function argument($index = 1, $default = FALSE)
    {
        if(is_string($index))
        {
            if(($key = array_search($index, QuickPHP::route()->arguments)) === FALSE)
            {
                return $default;
            }

            $index = $key + 2;
        }

        $index = (int) $index - 1;

        return isset(QuickPHP::route()->arguments[$index]) ? QuickPHP::route()->arguments[$index] : $default;
    }

    /**
     * 返回一个数组，包含所有的URI部分
     *
     * @param   integer  segment offset
     * @param   boolean  return an associative array
     * @return  array
     */
    public static function segment_array($offset = 0, $associative = FALSE)
    {
        return $this->build_array(QuickPHP::route()->segments, $offset, $associative);
    }

    /**
     * 返回一个数组，包含所有的重新路由的URI部分
     *
     * @param   integer  rsegment offset
     * @param   boolean  return an associative array
     * @return  array
     */
    public function rsegment_array($offset = 0, $associative = FALSE)
    {
        return $this->build_array(QuickPHP::route()->rsegments, $offset, $associative);
    }

    /**
     * 返回一个数组，包含所有的URI参数
     *
     * @param   integer  segment offset
     * @param   boolean  return an associative array
     * @return  array
     */
    public static function argument_array($offset = 0, $associative = FALSE)
    {
        return $this->build_array(QuickPHP::route()->arguments, $offset, $associative);
    }

    /**
     * 从创建一个数组和一个简单的或关联数组的偏移量。作为一个助手用（R）的segment_array和argument_array
     *
     * @param   array    array to rebuild
     * @param   integer  offset to start from
     * @param   boolean  create an associative array
     * @return  array
     */
    public static function build_array($array, $offset = 0, $associative = FALSE)
    {
        array_unshift($array, 0);

        $array = array_slice($array, $offset + 1, count($array) - 1, TRUE);

        if($associative === FALSE)
        {
            return $array;
        }

        $associative = array();
        $pairs       = array_chunk($array, 2);

        foreach ($pairs as $pair)
        {
            $associative[$pair[0]] = isset($pair[1]) ? $pair[1] : '';
        }

        return $associative;
    }

    /**
     * 作为一个字符串返回完整的URI
     *
     * @return  string
     */
    public static function string()
    {
        return QuickPHP::route()->current_uri;
    }

    /**
     * 魔术方法对象转换为字符串
     *
     * @return  string
     */
    public static function __toString()
    {
        return QuickPHP::route()->current_uri;
    }

    /**
     * 返回的URI段总数
     *
     * @return  integer
     */
    public static function total_segments()
    {
        return count(QuickPHP::route()->segments);
    }

    /**
     * 返回重新路由的URI段总数
     *
     * @return  integer
     */
    public static function total_rsegments()
    {
        return count(QuickPHP::route()->rsegments);
    }

    /**
     * 返回的URI参数总数
     *
     * @return  integer
     */
    public static function total_arguments()
    {
        return count(QuickPHP::route()->arguments);
    }

    /**
     * 返回最后的URI部分
     *
     * @param   mixed   default value returned if segment does not exist
     * @return  string
     */
    public static function last_segment($default = FALSE)
    {
        if(($end = $this->total_segments()) < 1)
        {
            return $default;
        }

        return QuickPHP::route()->segments[$end - 1];
    }

    /**
     * 返回最后改为URI的部分
     *
     * @param   mixed   default value returned if segment does not exist
     * @return  string
     */
    public static function last_rsegment($default = FALSE)
    {
        if(($end = $this->total_segments()) < 1)
        {
            return $default;
        }

        return QuickPHP::route()->rsegments[$end - 1];
    }

    /**
     * 返回的路径，电流控制器（不包括实际控制人），作为网络路径
     *
     * @param   boolean  return a full url, or only the path specifically
     * @return  string
     */
    public static function controller_path($full = TRUE)
    {
        return ($full) ? url::site(QuickPHP::route()->controller_path) : QuickPHP::route()->controller_path;
    }

    /**
     * 返回电流控制器，作为一个网络路径
     *
     * @param   boolean  return a full url, or only the controller specifically
     * @return  string
     */
    public static function controller($full = TRUE)
    {
        return ($full) ? url::site(QuickPHP::route()->controller_path . QuickPHP::route()->controller) : QuickPHP::route()->controller;
    }

    /**
     * 返回当前的方法，作为一个网络路径
     *
     * @param   boolean  return a full url, or only the method specifically
     * @return  string
     */
    public static function method($full = TRUE)
    {
        return ($full) ? url::site(QuickPHP::route()->controller_path . QuickPHP::route()->controller . '/' . QuickPHP::route()->method) : QuickPHP::route()->method;
    }
    /**
     *
     * Enter description here ...
     * @param unknown_type $n
     * @param unknown_type $default
     */
    public static function uri_to_assoc($n = 3, $default = array())
    {
        return uri::_uri_to_assoc($n, $default, 'segment');
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $n
     * @param unknown_type $default
     */
    public static function ruri_to_assoc($n = 3, $default = array())
    {
        return uri::_uri_to_assoc($n, $default, 'rsegment');
    }

    /**
     * uri 转数组
     *
     * @param $n
     * @param $default
     * @param $which
     */
    protected static function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')
    {
        if($which == 'segment')
        {
            $total_segments = 'total_segments';
            $segment_array  = 'segment_array';
        }
        else
        {
            $total_segments = 'total_rsegments';
            $segment_array  = 'rsegment_array';
        }

        if( ! is_numeric($n))
            return $default;

        if($this->$total_segments() < $n)
        {
            if(count($default) == 0)
            {
                return array();
            }

            $retval = array();

            foreach ($default as $val)
            {
                $retval[$val] = FALSE;
            }

            return $retval;
        }

        $segments = array_slice($this->$segment_array(), ($n - 1));

        $i          = 0;
        $lastval    = '';
        $retval     = array();

        foreach ($segments as $seg)
        {
            if($i % 2)
            {
                $retval[$lastval] = $seg;
            }
            else
            {
                $retval[$seg] = FALSE;
                $lastval = $seg;
            }

            $i++;
        }

        if(count($default) > 0)
        {
            foreach ($default as $val)
            {
                if( ! array_key_exists($val, $retval))
                {
                    $retval[$val] = FALSE;
                }
            }
        }

        return $retval;
    }

    /**
     * 数组转uri
     *
     * @param unknown_type $array
     */
    public static function assoc_to_uri($array)
    {
        $temp = array();

        foreach ((array) $array as $key => $val)
        {
            $temp[] = $key;
            $temp[] = $val;
        }

        return implode('/', $temp);
    }
}