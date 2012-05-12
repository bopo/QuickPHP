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
 * 请求操作辅助工具
 *
 * @category   QuickPHP
 * @package    Helper
 * @subpackage request
 * @copyright  Copyright (c) 2010 http://quickphp.net All rights reserved.
 * @license    http://framework.quickphp.net/licenses/LICENSE-2.0
 * @version    $Id: request.php 8582 2011-12-19 01:47:02Z bopo $
 */
class QuickPHP_request
{

    protected static $http_methods = array('GET', 'HEAD', 'OPTIONS', 'POST', 'PUT', 'DELETE');
    protected static $accept_types;

    /**
     * Returns the HTTP referrer, or the default if the referrer is not set.
     *
     * @param   mixed   default to return
     * @return  string
     */
    public static function referrer($default = false)
    {
        if( ! empty($_SERVER['HTTP_REFERER']))
        {
            $referrer = $_SERVER['HTTP_REFERER'];

            if(strpos($referrer, url::base(false)) === 0)
            {
                $referrer = substr($referrer, strlen(url::base(false)));
            }
        }

        return isset($referrer) ? $referrer : $default;
    }

    /**
     * Returns the current request protocol, based on $_SERVER['https']. In CLI
     * mode, null will be returned.
     *
     * @return  string
     */
    public static function protocol()
    {
        if(PHP_SAPI === 'cli')
        {
            return null;
        }
        elseif( ! empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] === 'on')
        {
            return 'https';
        }
        else
        {
            return 'http';
        }
    }

    /**
     * Tests if the current request is an AJAX request by checking the X-Requested-With HTTP
     * request header that most popular JS frameworks now set for AJAX calls.
     *
     * @return  boolean
     */
    public static function is_ajax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            and strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) === 'XMLHTTPREQUEST');
    }

    /**
     * Returns current request method.
     *
     * @throws  QuickPHP_Exception in case of an unknown request method
     * @return  string
     */
    public static function method()
    {
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        if( ! in_array($method, request::$http_methods))
        {
            throw new QuickPHP_Exception('request_unknown_method', array($method));
        }

        return $method;
    }

    /**
     * Returns boolean of whether client accepts content type.
     *
     * @param   string   content type
     * @param   boolean  set to true to disable wildcard checking
     * @return  boolean
     */
    public static function accepts($type = null, $explicit_check = false)
    {
        request::parse_accept_header();

        if($type === null)
        {
            return request::$accept_types;
        }

        return (request::accepts_at_quality($type, $explicit_check) > 0);
    }

    /**
     * Compare the q values for given array of content types and return the one with the highest value.
     * If items are found to have the same q value, the first one encountered in the given array wins.
     * If all items in the given array have a q value of 0, false is returned.
     *
     * @param   array    content types
     * @param   boolean  set to true to disable wildcard checking
     * @return  mixed    string mime type with highest q value, false if none of the given types are accepted
     */
    public static function preferred_accept($types, $explicit_check = false)
    {
        $mime_types = array();
        $preferred  = false;
        $max_q      = 0;

        foreach (array_unique($types) as $type)
        {
            $mime_types[$type] = request::accepts_at_quality($type, $explicit_check);
        }

        foreach ($mime_types as $type => $q)
        {
            if($q > $max_q)
            {
                $max_q      = $q;
                $preferred  = $type;
            }
        }

        return $preferred;
    }

    /**
     * Returns quality factor at which the client accepts content type.
     *
     * @param   string   content type (e.g. "image/jpg", "jpg")
     * @param   boolean  set to true to disable wildcard checking
     * @return  integer|float
     */
    public static function accepts_at_quality($type = null, $explicit_check = false)
    {
        request::parse_accept_header();
        $type = strtolower((string) $type);

        if(strpos($type, '/') === false)
        {
            $q      = 0;
            $mimes  = QuickPHP::config('mimes')->get($type, array());

            foreach ($mimes as $type)
            {
                $q2 = request::accepts_at_quality($type, $explicit_check);
                $q  = ($q2 > $q) ? $q2 : $q;
            }

            return $q;
        }

        $type = explode('/', $type, 2);

        if(isset(request::$accept_types[$type[0]][$type[1]]))
        {
            return request::$accept_types[$type[0]][$type[1]];
        }

        if($explicit_check === false and isset(request::$accept_types[$type[0]]['*']))
        {
            return request::$accept_types[$type[0]]['*'];
        }

        if($explicit_check === false and isset(request::$accept_types['*']['*']))
        {
            return request::$accept_types['*']['*'];
        }

        return 0;
    }

    /**
     * Parses client's HTTP Accept request header, and builds array structure representing it.
     *
     * @return  void
     */
    protected static function parse_accept_header()
    {
        if(request::$accept_types !== null)
        {
            return null;
        }

        request::$accept_types = array();

        if(empty($_SERVER['HTTP_ACCEPT']))
        {
            request::$accept_types['*']['*'] = 1;
            return;
        }

        foreach (explode(',', str_replace(array("\r", "\n"), '', $_SERVER['HTTP_ACCEPT'])) as $accept_entry)
        {
            $accept_entry = explode(';', trim($accept_entry), 2);
            $type = explode('/', $accept_entry[0], 2);

            if( ! isset($type[1]))
            {
                continue;
            }

            $q = (isset($accept_entry[1]) and preg_match('~\bq\s*+=\s*+([.0-9]+)~', $accept_entry[1], $match)) ? (float) $match[1] : 1;

            if( ! isset(request::$accept_types[$type[0]][$type[1]]) or $q > request::$accept_types[$type[0]][$type[1]])
            {
                request::$accept_types[$type[0]][$type[1]] = $q;
            }
        }
    }


    /**
     * Generates an [ETag](http://en.wikipedia.org/wiki/HTTP_ETag) from the
     * request response.
     *
     *     $etag = $request->generate_etag();
     *
     * [!!] If the request response is empty when this method is called, an
     * exception will be thrown!
     *
     * @return string
     * @throws QuickPHP_Request_Exception
     */
    public function generate_etag()
    {
        if ($this->response === null)
        {
            throw new QuickPHP_Exception('cannot_generate_etag');
        }

        return '"'.sha1($this->response).'"';
    }


    /**
     * Checks the browser cache to see the response needs to be returned.
     *
     * $request->check_cache($etag);
     *
     * [!!] If the cache check succeeds, no further processing can be done!
     *
     * @param   string  etag to check
     * @return  $this
     * @throws  QuickPHP_Request_Exception
     * @uses    request::generate_etag
     */
    public function check_cache($etag = null)
    {
        if (empty($etag))
        {
            $etag = $this->generate_etag();
        }

        $this->headers['ETag']          = $etag;
        $this->headers['Cache-Control'] = 'must-revalidate';

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) and $_SERVER['HTTP_IF_NONE_MATCH'] === $etag)
        {
            $this->status = 304;
            $this->send_headers();
            exit;
        }

        return $this;
    }


    /**
     * 获取IP.
     *
     * @return string
     */
    public static function ip_address()
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');

        foreach ($keys as $key)
        {
            $ip = request::server($key);

            if( ! empty($ip))
            {
                $ip_address = $ip;
                break;
            }
        }

        $comma = strrpos($ip_address, ',');

        if($comma !== false)
        {
            $ip_address = substr($ip_address, $comma + 1);
        }

        if( ! valid::ip($ip_address))
        {
            $ip_address = '0.0.0.0';
        }

        return $ip_address;
    }

    /**
     * 从 $_SERVER 数组取一个指定项目.
     *
     * @param   string   键值
     * @param   mixed    默认值
     * @param   boolean  XSS clean 开关
     * @return  mixed
     */
    public static function server($key = array(), $default = null, $xss_clean = false)
    {
        return request::search_array($_SERVER, $key, $default, $xss_clean);
    }

    protected static function search_array($array, $key, $default = null, $xss_clean = false)
    {
        if($key === array())
        {
            return $array;
        }

        if( ! isset($array[$key]))
        {
            return $default;
        }

        $value = $array[$key];

        if($xss_clean === true)
        {
            $value = security::xss_clean($value);
        }

        return $value;
    }

    /**
     * 从  $_POST 数组取一个指定项目.
     *
     * @param   string   键值
     * @param   mixed    默认值
     * @param   boolean  XSS clean 开关
     * @return  mixed
     */
    public static function post($key = array(), $default = null, $xss_clean = false)
    {
        return request::search_array($_POST, $key, $default, $xss_clean);
    }

    /**
     * 从 $_COOKIE 数组取一个指定项目.
     *
     * @param   string   键值
     * @param   mixed    默认值
     * @param   boolean  XSS clean 开关
     * @return  mixed
     */
    public static function cookie($key = array(), $default = null, $xss_clean = false)
    {
        return request::search_array($_COOKIE, $key, $default, $xss_clean);
    }

    /**
     * 获得 user_agnet.
     *
     * @return  string
     */
    public static function user_agent()
    {
        return request::server('HTTP_USER_AGENT');
    }

    /**
     * 强行一个文件下载到用户的浏览器。
     *
     * @param string $filename  浏览器输出文件名
     * @param mixed $data       浏览器输出文件流
     */
    public static function download($filename, $data = null)
    {
        return download::force($filename, $data);
    }
}