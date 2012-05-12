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
 * Unicode::str_pad
 *
 * @category   Library
 * @package    Unicode
 * @author     bopo <ibopo@126.com>
 * @copyright  (c) 2007 Quick
 * @license    http://www.quickphp.net/licenses/
 */
function _str_pad($str, $final_str_length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT)
{
    if(Unicode::is_ascii($str) and Unicode::is_ascii($pad_str))
    {
        return str_pad($str, $final_str_length, $pad_str, $pad_type);
    }
    $str_length = Unicode::strlen($str);
    if($final_str_length <= 0 or $final_str_length <= $str_length)
    {
        return $str;
    }
    $pad_str_length = Unicode::strlen($pad_str);
    $pad_length = $final_str_length - $str_length;
    if($pad_type == STR_PAD_RIGHT)
    {
        $repeat = ceil($pad_length / $pad_str_length);
        return Unicode::substr($str . str_repeat($pad_str, $repeat), 0, $final_str_length);
    }
    if($pad_type == STR_PAD_LEFT)
    {
        $repeat = ceil($pad_length / $pad_str_length);
        return Unicode::substr(str_repeat($pad_str, $repeat), 0, floor($pad_length)) . $str;
    }
    if($pad_type == STR_PAD_BOTH)
    {
        $pad_length /= 2;
        $pad_length_left = floor($pad_length);
        $pad_length_right = ceil($pad_length);
        $repeat_left = ceil($pad_length_left / $pad_str_length);
        $repeat_right = ceil($pad_length_right / $pad_str_length);
        $pad_left = Unicode::substr(str_repeat($pad_str, $repeat_left), 0, $pad_length_left);
        $pad_right = Unicode::substr(str_repeat($pad_str, $repeat_right), 0, $pad_length_left);
        return $pad_left . $str . $pad_right;
    }
    trigger_error('Unicode::str_pad: Unknown padding type (' . $pad_type . ')', E_USER_ERROR);
}