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
 * Unicode::substr
 *
 * @category   Library
 * @package    Unicode
 * @author     bopo <ibopo@126.com>
 * @copyright  (c) 2007 Quick
 * @license    http://www.quickphp.net/licenses/
 */
function _substr($str, $offset, $length = null)
{
    if(SERVER_UTF8)
    {
        return ($length === null) ? mb_substr($str, $offset) : mb_substr($str, $offset, $length);
    }

    if(Unicode::is_ascii($str))
    {
        return ($length === null) ? substr($str, $offset) : substr($str, $offset, $length);
    }

    $str    = (string) $str;
    $strlen = Unicode::strlen($str);
    $offset = (int) ($offset < 0) ? max(0, $strlen + $offset) : $offset; // Normalize to positive offset
    $length = ($length === null) ? null : (int) $length;

    if($length === 0 or $offset >= $strlen or ($length < 0 and $length <= $offset - $strlen))
    {
        return '';
    }

    if($offset == 0 and ($length === null or $length >= $strlen))
    {
        return $str;
    }

    $regex = '^';

    if($offset > 0)
    {
        $x = (int) ($offset / 65535);
        $y = (int) ($offset % 65535);
        $regex .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
        $regex .= ($y == 0) ? '' : '.{' . $y . '}';
    }

    if($length === null)
    {
        $regex .= '(.*)'; // No length set, grab it all
    } 
    elseif($length > 0)
    {
        $length = min($strlen - $offset, $length);
        $x      = (int) ($length / 65535);
        $y      = (int) ($length % 65535);
        $regex  .= '(';
        $regex  .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
        $regex  .= '.{' . $y . '})';
    } 
    else
    {
        $x     = (int) (- $length / 65535);
        $y     = (int) (- $length % 65535);
        $regex .= '(.*)';
        $regex .= ($x == 0) ? '' : '(?:.{65535}){' . $x . '}';
        $regex .= '.{' . $y . '}';
    }
    
    preg_match('/' . $regex . '/us', $str, $matches);
    return $matches[1];
}