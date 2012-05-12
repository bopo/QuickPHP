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
 * QuickPHP COOKIE助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  cookie
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: cookie.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_cookie
{

    /**
     * 用给定的参数设置了一个cookie
     *
     * @param   string   cookie name
     * @param   string   cookie value
     * @param   integer  cookie expire
     * @param   string   cookie path
     * @param   string   cookie domain
     * @param   boolean  只支持HTTPS
     * @param   boolean  只支持HTTP (需要PHP 5.2或更高版本)
     * @return  boolean
     */
    public static function set($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if(headers_sent())
        {
            return false;
        }

        is_array($name) and extract($name, EXTR_OVERWRITE);

        $config = QuickPHP::config('cookie')->as_array();

        foreach (array('value', 'expire', 'domain', 'path', 'secure', 'httponly') as $item)
        {
            if($$item === null and isset($config[$item]))
            {
                $$item = $config[$item];
            }
        }

        $expire = ($expire == 0) ? 0 : time() + (int) $expire;
        $domain = (empty($domain)) ? QuickPHP::$domain : $domain;

        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * 获得一个cookie的值，使用Input类
     *
     * @param   string   cookie name
     * @param   mixed    default value
     * @param   boolean  use XSS cleaning on the value
     * @return  string
     */
    public static function get($name, $default = null, $xss_clean = false)
    {
        return request::cookie($name, $default, $xss_clean);
    }

    /**
     * 销毁一个cookie.
     *
     * @param   string   cookie name
     * @param   string   URL path
     * @param   string   URL domain
     * @return  boolean
     */
    public static function delete($name, $path = null, $domain = null)
    {
        if( ! isset($_COOKIE[$name]))
        {
            return false;
        }

        unset($_COOKIE[$name]);
        return cookie::set($name, '', -86400, $path, $domain, false, false);
    }

    public static function remove($name, $path = null, $domain = null)
    {
        return cookie::delete($name, $path, $domain);
    }
}