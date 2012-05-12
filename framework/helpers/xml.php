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
 * QuickPHP XML解析助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: xml.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_xml
{

    /**
     * 解析一个xml文本.
     *
     * @param   string
     * @return  array
     */
    public static function toArray($xml = null)
    {
        if (empty($xml))
        {
            return null;
        }
        
        return self::parse($xml);
    }

    public static function parse($xml = null)
    {
        if (empty($xml))
        {
            return null;
        }
          
        if(strpos($xml, 'xml'))
        {
            $dom   = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $array = self::get_object_vars_final($dom);

            return $array;
        }

        return NULL;
    }

    /**
     * get object vars.
     *
     * @param   string   $obj
     * @return  array
     */
    protected static function get_object_vars_final($obj)
    {
        if(is_object($obj))
        {
            $obj = get_object_vars($obj);
        }

        if(is_array($obj))
        {
            foreach ($obj as $key => $value)
            {
                $obj[$key] = self::get_object_vars_final($value);
            }
        }

        return $obj;
    }

    /**
     * 转换方法
     *
     * @param   string   $xml
     * @return  array
     */
    public static function convert($xml)
    {
        $temp = '__TEMP_AMPERSANDS__';
        $str  = preg_replace('/&#(\d+);/', "$temp\\1;", $xml);
        $str  = preg_replace('/&(\w+);/', "$temp\\1;", $xml);
        $str  = str_replace(array("&", "<", ">", "\"", "'", "-"), array("&amp;", "&lt;", "&gt;", "&quot;", "&#39;", "&#45;"), $xml);
        $str  = preg_replace("/$temp(\d+);/", "&#\\1;", $xml);
        $str  = preg_replace("/$temp(\w+);/", "&\\1;", $xml);
        return $xml;
    }
}

