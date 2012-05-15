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
 * QuickPHP 路由处理(路由器)
 *
 * @category    QuickPHP
 * @package     Router
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Router.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_Router
{
    // 路由规则容器
    protected $_routes;

    // 当前uri
    protected $current_uri  = '';

    // 请求字符串
    protected $query_string = '';

    // 请求字符串转化为数组形式
    protected $query_array  = null;

    // 编译的uri
    protected $complete_uri = '';

    // 已经路由过的uri
    protected $routed_uri   = '';

    // URL后缀
    protected $url_suffix   = 'html';

    // 默认方法
    protected $method       = 'index';

    // 参数
    protected $arguments    = array();

    // 默认路由协议
    protected $protocol     = 'http';

    // 默认路由协议
    // 'AUTO'			Default - auto detects
    // 'PATH_INFO'		Uses the PATH_INFO
    // 'QUERY_STRING'	Uses the QUERY_STRING
    // 'REQUEST_URI'    Uses the REQUEST_URI
    // 'ORIG_PATH_INFO'	Uses the ORIG_PATH_INFO
    protected $uri_protocol = 'AUOT';

    // 路由片段
    protected $segments;

    // 重写后的路由片段
    protected $rsegments;

    // 控制器名称
    protected $controller;

    // 控制器文件的路径
    protected $controller_path;

    // 路由器单例化容器
    protected static $_instance = null;

    /**
     * 得到这个类的实例单身。
     *
     * $log = QuickPHP_Router::instance();
     *
     * @return  QuickPHP_Router
     */
    public static function instance()
    {
        if(empty(Router::$_instance))
        {
            Router::$_instance = new Router();
        }

        return Router::$_instance;
    }

    /**
     * 获取已经路由的全部属性值
     *
     */
    public function get($key = null)
    {
        if(isset($this->$key))
        {
            return $this->$key;
        }
    }

    /**
     * 获取已经路由的全部属性值
     *
     */
    public function get_all()
    {
        return get_object_vars($this);
    }

    /**
     * 魔术函数 __get
     */
    public function __get($key)
    {
        if(isset($this->$key))
        {
            return $this->$key;
        }
    }

    /**
     * 魔术函数 __call
     */
    public function __call($key, $args)
    {
        if(isset($this->$key))
        {
            return $this->$key;
        }
    }

    /**
     * 魔术函数 __toString
     */
    public function __toString()
    {
        return true;
    }

    /**
     * 魔术函数 __reset
     */
    public function __reset()
    {
        $this->current_uri     = '';
        $this->query_string    = '';
        $this->complete_uri    = '';
        $this->routed_uri      = '';
        $this->url_suffix      = '';

        $this->segments        = array();
        $this->rsegments       = array();
        $this->arguments       = array();
        $this->query_array     = array();

        $this->controller_path = null;
        $this->protocol        = null;
        $this->controller      = null;
    }

    /**
     * 路由器初始化
     *
     * @return  void
     */
    protected function __construct()
    {
        if($this->_routes === null)
        {
            $this->_routes = QuickPHP::config('routes')->as_array();
        }

        $this->find_uri();

        if( ! empty($_SERVER['REQUEST_URI']))
        {
            $query_array = array();
            $parse       = parse_url($_SERVER['REQUEST_URI']);

            parse_str($parse['query'], $query_array);

            if(isset($parse['query']) && ! empty($query_array))
            {
                $this->query_string = $parse['query'];
                $this->query_array  = $query_array;
            }
        }

        $default_route = false;

        if($this->current_uri === '')
        {
            if( ! isset($this->_routes['_default']))
            {
                throw new QuickPHP_Exception('no_default_route', array($this->segments));
            }

            $this->current_uri = $this->_routes['_default'];
            $default_route = true;
        }

        $this->current_uri  = htmlspecialchars($this->current_uri, false);
        $this->current_uri  = preg_replace('#\.[\s./]*/#', '', $this->current_uri);
        $this->segments     = $this->rsegments = $this->current_uri = trim($this->current_uri, '/');
        $this->complete_uri = $this->current_uri . $this->query_string;
        $this->segments     = ($default_route === true or $this->segments === '') ? array() : explode('/', $this->segments);

        if($default_route === false and count($this->_routes) > 1)
        {
            $this->rsegments = $this->routed_uri($this->current_uri);
        }

        $this->routed_uri = $this->rsegments;
        $this->rsegments  = explode('/', $this->rsegments);
        $controller_path  = '';
        $method_segment   = null;

        foreach ($this->rsegments as $key => $segment)
        {
            $controller_path .= str_replace("_", "/", $segment);

            $found = false;
            $dirs  = array(APPPATH . 'controllers/', SYSPATH . 'controllers/');

            foreach ($dirs as $dir)
            {
                if(is_dir($dir . $controller_path) or is_file($dir . $controller_path . EXT))
                {
                    $found = true;

                    if($c = str_replace('\\', '/', realpath($dir . $controller_path . EXT)) and is_file($c) and strpos($c, $dir) === 0)
                    {
                        $this->controller      = $segment;
                        $this->controller_path = $c;
                        $method_segment        = $key + 1;
                        break;
                    }
                }
            }

            if($found === false)
            {
                break;
            }

            $controller_path .= '/';
        }

        if($method_segment !== null and isset($this->rsegments[$method_segment]))
        {
            $this->method = $this->rsegments[$method_segment];

            if(isset($this->rsegments[$method_segment + 1]))
            {
                $this->arguments = array_slice($this->rsegments, $method_segment + 1);
            }
        }

        if($this->controller === null)
        {
            throw new QuickPHP_Exception('no_controller', $this->segments, 404);
        }
    }

    /**
     * 解析URI (PATH_INFO, ORIG_PATH_INFO, PHP_ROUTER, QUERY_STRING 等方式)
     *
     * @return  void
     */
    protected function find_uri()
    {
        if(QuickPHP::$is_cli === true)
        {
            $this->protocol = 'cli';

            $options = cli::options('uri', 'method', 'get', 'post');

            if(isset($options['uri']))
            {
                $this->current_uri = $options['uri'];
            }

            if(isset($options['method']))
            {
                $this->method = strtolower($options['method']);
            }

            if(isset($options['get']))
            {
                parse_str($options['get'], $_GET);
            }

            if(isset($options['post']))
            {
                parse_str($options['post'], $_POST);
            }
        }
        elseif(isset($_SERVER['PATH_INFO']) and $_SERVER['PATH_INFO'])
        {
            $this->current_uri = $_SERVER['PATH_INFO'];
            $this->uri_protocol = 'PATH_INFO';
        }
        elseif(isset($_SERVER['ORIG_PATH_INFO']) and $_SERVER['ORIG_PATH_INFO'])
        {
            $this->current_uri = $_SERVER['ORIG_PATH_INFO'];
            $this->uri_protocol = 'ORIG_PATH_INFO';
        }
        elseif(isset($_SERVER['PHP_Router']) and $_SERVER['PHP_Router'])
        {
            $this->current_uri = $_SERVER['PHP_Router'];
            $this->uri_protocol = 'PHP_Router';
        }
        elseif(isset($_SERVER['QUERY_STRING']) and !empty($_SERVER['QUERY_STRING']))
        {
            $query_string      = explode("?", $_SERVER['QUERY_STRING']);
            $this->current_uri = ltrim(current($query_string), '/');

            if (is_array($query_string))
            {
                array_shift($query_string);
                $query_string = implode("&", $query_string);
            }

            $_SERVER['QUERY_STRING'] = $query_string;
            parse_str($query_string, $_GET);
            $this->uri_protocol = 'QUERY_STRING';
        }

        if(($strpos_fc = strpos($this->current_uri, EXT)) !== false)
        {
            $this->current_uri = (string) substr($this->current_uri, $strpos_fc + strlen(EXT));
        }

        $this->current_uri = trim($this->current_uri, '/');

        if($this->current_uri !== '')
        {
            preg_match_all('/\.(' . QuickPHP::$url_suffix . ')$/', $this->current_uri, $suffixs);
            $suffix = isset($suffixs[0][0]) ? $suffixs[0][0] : QuickPHP::$url_suffix;

            if(!empty($suffix))
            {
                $this->current_uri = preg_replace('#' . preg_quote($suffix) . '$#u', '', $this->current_uri);
                $this->url_suffix  = $suffix;
            }

            $this->current_uri = preg_replace('#//+#', '/', $this->current_uri);
        }
    }

    /**
     * 构建重构后的URI.
     *
     * @param  string  要转换的URI
     * @return string  重构后的URI
     */
    protected function routed_uri($uri)
    {
        $routed_uri = $uri = trim($uri, '/');

        if(isset($this->_routes[$uri]))
        {
            $routed_uri = $this->_routes[$uri];
        }
        else
        {
            foreach ($this->_routes as $key => $val)
            {
                if($key === '_default')
                {
                    continue;
                }

                $key = trim($key, '/');
                $val = trim($val, '/');

                if(preg_match('#^' . $key . '$#u', $uri))
                {
                    if(strpos($val, '$') !== false)
                    {
                        $routed_uri = preg_replace('#^' . $key . '$#u', $val, $uri);
                    }
                    else
                    {
                        $routed_uri = $val;
                    }

                    break;
                }
            }
        }

        if(isset($this->_routes[$routed_uri]))
        {
            $routed_uri = $this->_routes[$routed_uri];
        }

        return trim($routed_uri, '/');
    }
}