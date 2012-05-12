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
 * Unicode::str_split
 *
 * @category   Library
 * @package    Unicode
 * @author     bopo <ibopo@126.com>
 * @copyright  (c) 2007 Quick
 * @license    http://www.quickphp.net/licenses/
 */
function _str_split($str, $split_length = 1)
{
    $split_length = (int) $split_length;
    if(Unicode::is_ascii($str))
    {
        return str_split($str, $split_length);
    }
    if($split_length < 1)
    {
        return FALSE;
    }
    if(Unicode::strlen($str) <= $split_length)
    {
        return array($str);
    }
    preg_match_all('/.{' . $split_length . '}|[^\x00]{1,' . $split_length . '}$/us', $str, $matches);
    return $matches[0];
}