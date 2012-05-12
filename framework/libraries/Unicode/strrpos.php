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
 * Unicode::strrpos
 *
 * @category   Library
 * @package    Unicode
 * @author     bopo <ibopo@126.com>
 * @copyright  (c) 2007 Quick
 * @license    http://www.quickphp.net/licenses/
 */
function _strrpos($str, $search, $offset = 0)
{
    $offset = (int) $offset;
    if(SERVER_UTF8)
    {
        return mb_strrpos($str, $search, $offset);
    }
    if(Unicode::is_ascii($str) and Unicode::is_ascii($search))
    {
        return strrpos($str, $search, $offset);
    }
    if($offset == 0)
    {
        $array = explode($search, $str, - 1);
        return isset($array[0]) ? Unicode::strlen(implode($search, $array)) : false;
    }
    $str = Unicode::substr($str, $offset);
    $pos = Unicode::strrpos($str, $search);
    return ($pos === false) ? false : $pos + $offset;
}