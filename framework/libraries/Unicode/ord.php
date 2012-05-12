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
 * Unicode::ord
 *
 * @category   Library
 * @package    Unicode
 * @author     bopo <ibopo@126.com>
 * @copyright  (c) 2007 Quick
 * @license    http://www.quickphp.net/licenses/
 */
function _ord($chr)
{
    $ord0 = ord($chr);
    if($ord0 >= 0 and $ord0 <= 127)
    {
        return $ord0;
    }
    if( ! isset($chr[1]))
    {
        trigger_error('Short sequence - at least 2 bytes expected, only 1 seen', E_USER_WARNING);
        return false;
    }
    $ord1 = ord($chr[1]);
    if($ord0 >= 192 and $ord0 <= 223)
    {
        return ($ord0 - 192) * 64 + ($ord1 - 128);
    }
    if( ! isset($chr[2]))
    {
        trigger_error('Short sequence - at least 3 bytes expected, only 2 seen', E_USER_WARNING);
        return false;
    }
    $ord2 = ord($chr[2]);
    if($ord0 >= 224 and $ord0 <= 239)
    {
        return ($ord0 - 224) * 4096 + ($ord1 - 128) * 64 + ($ord2 - 128);
    }
    if( ! isset($chr[3]))
    {
        trigger_error('Short sequence - at least 4 bytes expected, only 3 seen', E_USER_WARNING);
        return false;
    }
    $ord3 = ord($chr[3]);
    if($ord0 >= 240 and $ord0 <= 247)
    {
        return ($ord0 - 240) * 262144 + ($ord1 - 128) * 4096 + ($ord2 - 128) * 64 + ($ord3 - 128);
    }
    if( ! isset($chr[4]))
    {
        trigger_error('Short sequence - at least 5 bytes expected, only 4 seen', E_USER_WARNING);
        return false;
    }
    $ord4 = ord($chr[4]);
    if($ord0 >= 248 and $ord0 <= 251)
    {
        return ($ord0 - 248) * 16777216 + ($ord1 - 128) * 262144 + ($ord2 - 128) * 4096 + ($ord3 - 128) * 64 + ($ord4 - 128);
    }
    if( ! isset($chr[5]))
    {
        trigger_error('Short sequence - at least 6 bytes expected, only 5 seen', E_USER_WARNING);
        return false;
    }
    if($ord0 >= 252 and $ord0 <= 253)
    {
        return ($ord0 - 252) * 1073741824 + ($ord1 - 128) * 16777216 + ($ord2 - 128) * 262144 + ($ord3 - 128) * 4096 + ($ord4 - 128) * 64 + (ord($chr[5]) - 128);
    }
    if($ord0 >= 254 and $ord0 <= 255)
    {
        trigger_error('Invalid UTF-8 with surrogate ordinal ' . $ord0, E_USER_WARNING);
        return false;
    }
}