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
 * QuickPHP 格式化助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: format.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_format
{

    /**
     * Formats a phone number according to the specified format.
     *
     * @param   string  phone number
     * @param   string  format string
     * @return  string
     */
    public static function phone($number, $format = '3-3-4')
    {
        $number_clean = preg_replace('/\D+/', '', (string) $number);
        $format_parts = preg_split('/[^1-9][^0-9]*/', $format, - 1, PREG_SPLIT_NO_EMPTY);

        if(strlen($number_clean) !== array_sum($format_parts))
        {
            return $number;
        }

        $regex = '(\d{' . implode('})(\d{', $format_parts) . '})';

        for ($i = 1, $c = count($format_parts); $i <= $c; $i++)
        {
            $format = preg_replace('/(?<!\$)[1-9][0-9]*/', '\$' . $i, $format, 1);
        }

        return preg_replace('/^' . $regex . '$/', $format, $number_clean);
    }

    /**
     * Formats a URL to contain a protocol at the beginning.
     *
     * @param   string  possibly incomplete URL
     * @return  string
     */
    public static function url($str = '')
    {
        if($str === '' or substr($str, - 3) === '://')
        {
            return '';
        }

        if(strpos($str, '://') === false)
        {
            return 'http://' . $str;
        }

        return $str;
    }

    /**
     * 格式化时间.
     *
     * @param   string
     * @return  string
     */
    public static function date($date = NULL, $format = 'Y-m-d H:i:s')
    {
        $date      = empty($date) ? time() : $date;
        $timestamp = strtotime($date);

        return date($format, $timestamp);
    }
}