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
 * Unicode::str_ireplace
 *
 * @category   Library
 * @package    Unicode
 * @author     bopo <ibopo@126.com>
 * @copyright  (c) 2007 Quick
 * @license    http://www.quickphp.net/licenses/
 */
function _str_ireplace($search, $replace, $str, & $count = null)
{
    if(Unicode::is_ascii($search) and Unicode::is_ascii($replace) and Unicode::is_ascii($str))
    {
        return str_ireplace($search, $replace, $str, $count);
    }

    if(is_array($str))
    {
        foreach ($str as $key => $val)
        {
            $str[$key] = Unicode::str_ireplace($search, $replace, $val, $count);
        }

        return $str;
    }

    if(is_array($search))
    {
        $keys = array_keys($search);

        foreach ($keys as $k)
        {
            if(is_array($replace))
            {
                if(array_key_exists($k, $replace))
                {
                    $str = Unicode::str_ireplace($search[$k], $replace[$k], $str, $count);
                }
                else
                {
                    $str = Unicode::str_ireplace($search[$k], '', $str, $count);
                }
            }
            else
            {
                $str = Unicode::str_ireplace($search[$k], $replace, $str, $count);
            }
        }

        return $str;
    }

    $i                    = 0;
    $search               = Unicode::strtolower($search);
    $str_lower            = Unicode::strtolower($str);
    $total_matched_strlen = 0;

    while(preg_match('/(.*?)' . preg_quote($search, '/') . '/s', $str_lower, $matches))
    {
        $matched_strlen = strlen($matches[0]);
        $str_lower      = substr($str_lower, $matched_strlen);
        $offset         = $total_matched_strlen + strlen($matches[1]) + ($i * (strlen($replace) - 1));
        $str            = substr_replace($str, $replace, $offset, strlen($search));
        $total_matched_strlen += $matched_strlen;
        $i++;
    }
    
    $count += $i;
    return $str;
}
