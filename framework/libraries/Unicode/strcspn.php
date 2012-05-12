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
 * Unicode::strcspn
 *
 * @category   Library
 * @package    Unicode
 * @author     bopo <ibopo@126.com>
 * @copyright  (c) 2007 Quick
 * @license    http://www.quickphp.net/licenses/
 */
function _strcspn($str, $mask, $offset = null, $length = null)
{
    if($str == '' or $mask == '')
    {
        return 0;
    }
    if(Unicode::is_ascii($str) and Unicode::is_ascii($mask))
    {
        return ($offset === null) ? strcspn($str, $mask) : (($length === null) ? strcspn($str, $mask, $offset) : strcspn($str, $mask, $offset, $length));
    }
    if($str !== null or $length !== null)
    {
        $str = Unicode::substr($str, $offset, $length);
    }
    // Escape these characters:  - [ ] . : \ ^ /
    // The . and : are escaped to prevent possible warnings about POSIX regex elements
    $mask = preg_replace('#[-[\].:\\\\^/]#', '\\\\$0', $mask);
    preg_match('/^[^' . $mask . ']+/u', $str, $matches);
    return isset($matches[0]) ? Unicode::strlen($matches[0]) : 0;
}