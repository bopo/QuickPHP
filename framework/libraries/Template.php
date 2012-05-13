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
 * 模板引擎
 *
 * $Id: Template.php 8761 2012-01-15 05:10:59Z bopo $
 *
 * @category    QuickPHP
 * @package     Template
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Template.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_Template
{

    /**
     * @var Template 模板引擎单子实例容器
     */
    protected static $_instances;

    /**
     * @var object 模板引擎驱动
     */
    protected $driver;

    /**
     * @var Cache 缓存引擎
     */
    protected $cache_driver;

    /**
     * @var Cache 缓存键名
     */
    protected $cache_key;

    /**
     * @var int 缓存有效时间
     */
    protected $cache_lifetime;

    /**
     * @var boolean 缓存使用状态
     */
    protected $cache_status = false;

    /**
     * @var array 模板引擎缓存配置容器
     */
    public static $config   = array(
            'cache_dir'        => null,
            'layout_dir'       => null,
            'compile_dir'      => null,
            'template_dir'     => null,
            'compress_html'    => null,
            'left_delimiter'   => null,
            'right_delimiter'  => null,
            'template_suffix'  => null,
            'compile_lifetime' => null,
        );

    /**
     * @var array 模板渲染变量容器
     */
    public static $_assigned = array();

    /**
     * 返回模板引擎单子实例.
     *
     * @return  object
     */
    public static function instance($group = 'simple')
    {
        if(empty(Template::$_instances[$group]))
        {
            Template::$_instances[$group] = new Template($group);
        }

        return Template::$_instances[$group];
    }

    /**
     * 构造一个模板引擎对象，并返回一个单子实例.
     *
     * @param   string  配置组名
     * @return  object
     */
    public static function factory($group = 'simple')
    {
        return Template::instance($group);
    }

    /**
     * 构造函数，构造一个新的模板引擎对象.
     *
     * @throws  QuickPHP_Template_Exception
     * @param   string  配置组名
     * @return  void
     */
    public function __construct($group = 'simple')
    {
        $config = QuickPHP::config('template');
        $group  = ( ! is_string($group)) ? 'simple' : $group;
        $params = $config->get($group, array());

        if( empty($group))
        {
            throw new QuickPHP_Exception('undefined_group', array($group));
        }

        if(is_array($group))
        {
            foreach ($group as $key => $value)
            {
                if(array_key_exists($key, Template::$config))
                {
                    Template::$config[$key] = $value;
                }
            }
        }

        $driver       = "Template_Driver_" . ucfirst($group);
        $this->driver = new $driver($params);

        if( ! ($this->driver instanceof QuickPHP_Template_Interface))
        {
            throw new QuickPHP_Exception('driver_implements' , array($config['style'], get_class($this)));
        }
    }

    /**
     * 模板引擎取消缓存加速
     *
     * @return  mixed    html string or void
     */
    public function nocache()
    {
        $this->cache_status = false;
    }

    /**
     * 模板引擎使用缓存加速(用户生产环境).
     *
     * @return  mixed    lifetime 缓存有效时限
     */
    public function cache($lifetime = 86400)
    {
        if($this->cache_status == false)
        {
            $this->cache_key      = md5($_SERVER['REQUEST_URI']);
            $this->cache_status   = true;
            $this->cache_driver   = Cache::instance();
            $this->cache_lifetime = $lifetime;
        }

        $output = $this->cache_driver->get($this->cache_key);
        !empty($output) and exit(($output));
    }

    /**
     * 模板渲染输出.
     *
     * @param   string   模板的路径，不含扩展名。
     * @param   boolean  模板引擎渲染变量
     * @param   boolean  是否返回结果，true时，只返回结果不echo数据
     * @return  mixed    html string or void
     */
    public function render($tempate = null, $_top = array(), $return = false)
    {
        $output = $this->driver->render($tempate, $_top);

        if($this->cache_status == true)
        {
            $this->cache_driver->set($this->cache_key, $output, $this->cache_lifetime);
        }

        if($return == false)
        {
            if(Template::$config['compress_html'] == true)
            {
                echo Template::compress_html($output);
            }
            else
            {
                echo $output;
            }

            return ;
        }

        return $output;
    }

    /**
     * 返回模板输出内容.
     *
     * @param   string   模板的路径，不含扩展名。
     * @param   boolean  模板引擎渲染变量
     * @param   boolean  是否返回结果，true时，只返回结果不echo数据
     * @return  mixed    html string or void
     */
    public function result($tempate = null, $_top = array())
    {
        $output = $this->driver->render($tempate, $_top);

        if($this->cache_status == true)
        {
            $this->cache_driver->set($this->cache_key, $output, $this->cache_lifetime);
        }

        return $output;
    }

    /**
     * 魔术函数 __toString，返回渲染效果
     *
     * @return  mixed
     */
    public function __toString()
    {
        return $this->render(null, null, true);
    }

    /**
     * 魔术方法，调用模板引擎驱动的方法
     *
     * @return  mixed
     */
    public function __call($method, $args)
    {
        $arguments = func_get_args();
        call_user_func_array(array($this->driver, $method), $args);
    }

    /**
     * 魔术方法，分配模板渲染变量.
     *
     * @return  mixed
     */
    public function __set($key, $value = null)
    {
        if(isset($this->_assigned[$key]))
        {
            $this->driver->append($key, $value);
        }

        $this->_assigned[$key] = true;
        $this->driver->assign($key, $value);
    }

    public function append($key, $value = null)
    {
        $this->driver->append($key, $value);
    }

    public function assign($key, $value = null)
    {
        $this->driver->assign($key, $value);
    }

    /**
     * 压缩HTML : 清除换行符,清除制表符,去掉注释标记
     * @param   $string
     * @return  压缩后的HTML
     * */
    protected static function compress_html($string)
    {
        $string = str_replace("\t", '', $string); //清除制表符
        $string = preg_replace('/\/\*.*?\*\//si', '', $string);
        $string = preg_replace('/<!--\s*[^\[].*?[^\/\/]-->/m', '', $string);
        $string = preg_replace('/^\s+/m', '', $string);
        $string = preg_replace('/\s+$/m', '', $string);

        return $string;
    }
}