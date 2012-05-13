<?php defined('SYSPATH') or die('No direct access allowed.');
// +----------------------------------------------------------------------+
// | Quick PHP Framework Version 0.10                                     |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 Quick.cn All rights reserved.                     |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: BoPo <ibopo@126.com>                                         |
// +----------------------------------------------------------------------+
/**
 * URL helper class.
 *
 * $Id: url.php 474 2012-03-25 07:34:34Z bopo $
 *
 * @package helpers
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 */
class url extends QuickPHP_url
{

    /**
     * Fetches the current URI.
     *
     * @param   boolean  include the query string
     * @return  string
     */
    public static function current($qs = FALSE)
    {
        return ($qs === TRUE) ? QuickPHP::route()->get('complete_uri') : QuickPHP::route()->get('current_uri');
    }

    /**
     *
     * Enter description here ...
     */
    public static function bind()
    {
        $args = func_get_args();
        $uri = implode("/", $args);

        return url::site($uri, 'http');
    }

    /**
     * Return the URL to a file. Absolute filenames and relative filenames
     * are allowed.
     *
     * @param   string   filename
     * @param   boolean  include the index page
     * @return  string
     */
    public static function file($file, $protocol = 'http')
    {
        if(strpos($file, '://') === FALSE)
        {
            $file = url::base(TRUE, $protocol) . ltrim($file, "/");
        }

        return $file;
    }

    /**
     * Return the URL to a javascript. Absolute filenames and relative filenames
     * are allowed.
     *
     * @param   string   filename
     * @param   boolean  include the index page
     * @return  string
     */
    public static function themes($file, $theme = null)
    {
        if(empty($theme))
        {
            $theme = THEME;
        }

        return url::file('assets/themes/'.$theme . '/' . ltrim($file, "/"));
    }

    /**
     * Return the URL to a javascript. Absolute filenames and relative filenames
     * are allowed.
     *
     * @param   string   filename
     * @param   boolean  include the index page
     * @return  string
     */
    public static function thirdparty($file)
    {
        return url::file('assets/thirdparty/' . ltrim($file, "/"));
    }
    /**
     * Return the URL to a javascript. Absolute filenames and relative filenames
     * are allowed.
     *
     * @param   string   filename
     * @param   boolean  include the index page
     * @return  string
     */
    public static function script($file)
    {
        return url::file('assets/scripts/' . ltrim($file, "/"));
    }

    /**
     * Return the URL to a stylesheet. Absolute filenames and relative filenames
     * are allowed.
     *
     * @param   string   filename
     * @param   boolean  include the index page
     * @return  string
     */
    public static function stylesheet($file)
    {
        return url::file('assets/styles/' . ltrim($file, "/"));
    }

    /**
     * Return the URL to a image. Absolute filenames and relative filenames
     * are allowed.
     *
     * @param   string   filename
     * @param   boolean  include the index page
     * @return  string
     */
    public static function image($file, $index = 'assets/images')
    {
        return url::file('assets/images/' . ltrim($file, "/"));
    }

    /**
     * Merges an array of arguments with the current URI and query string to
     * overload, instead of replace, the current query string.
     *
     * @param   array   associative array of arguments
     * @return  string
     */
    public static function merge(array $arguments)
    {
        if($_GET === $arguments)
        {
            $query = QuickPHP::route()->$query_string;
        }
        elseif((bool) $query = http_build_query(array_merge($_GET, $arguments)))
        {
            $query = '?' . $query;
        }

        return QuickPHP::route()->$current_uri . $query;
    }

    public static function domain($url)
    {
        $pattern = '/[\w-]+\.(com|net|org|gov|cc|biz|info|cn|co)(\.(cn|hk))*/';
        $pattern = preg_match($pattern, $url, $matches);

        if(count($matches) > 0)
        {
            return $matches[0];
        }
        else
        {
            $rs = parse_url($url);
            $main_url = $rs["host"];

            if(! strcmp((sprintf("%u", ip2long($main_url))), $main_url))
            {
                return $main_url;
            }
            else
            {
                $arr    = explode(".", $main_url);
                $count  = count($arr);
                $endArr = array("com", "net", "org", "3322"); //com.cn  net.cn 等情况

                if(in_array($arr[$count - 2], $endArr))
                {
                    $domain = $arr[$count - 3] . "." . $arr[$count - 2] . "." . $arr[$count - 1];
                }
                else
                {
                    $domain = $arr[$count - 2] . "." . $arr[$count - 1];
                }

                return $domain;

            }

        }

    }

    public static function encode($url)
    {
        return urlencode($url);
    }
}