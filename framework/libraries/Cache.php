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
 * Provides a driver-based interface for finding, creating, and deleting Cached
 * resources. Caches are identified by a unique string. Tagging of Caches is
 * also supported, and Caches can be found and deleted by id or tag.
 *
 * @category    QuickPHP
 * @package     Libraries
 * @subpackage  Cache
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Cache.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Cache
{
    protected static $_instances = null;
    protected static $loaded;

    protected $config;
    protected $driver;

    /**
     * 返回一个缓存的单子实例。
     *
     * @param   string  configuration
     * @return  Cache
     */
    public static function instance($name = 'file', array$config = array())
    {
        if ( ! isset(Cache::$_instances[$name]))
        {
            Cache::$_instances[$name] = new Cache(empty($config) ? $name : $config);
        }

        return Cache::$_instances[$name];
    }

    /**
     * 初始化缓存并装载驱动，验证并清除过期数据。
     *
     * @param   array|string  custom configuration or config group name
     * @return  void
     */
    public function __construct($config = FALSE)
    {
        if (is_string($config))
        {
            $driver = $config = strtolower($config);

            if (($config = QuickPHP::config('cache')->$config) === NULL)
            {
                $config = array();
            }
        }

        if (is_array($config))
        {
            $this->config = array_merge($config, QuickPHP::config('cache')->default);
        }
        else
        {
            $this->config = QuickPHP::config('cache')->default;
        }

        $driver = 'Cache_Driver_'.ucfirst($driver);
        $this->driver = new $driver($this->config);

        if ( ! ($this->driver instanceof QuickPHP_Cache_Interface))
        {
            throw new QuickPHP_Cache_Exception('driver_implements', array($driver, get_class($this), 'Cache_Interface'));
        }

        if (Cache::$loaded !== TRUE)
        {
            $config['requests'] = (int) $config['requests'];

            if ($config['requests'] > 0 AND mt_rand(1, $config['requests']) === 1)
            {
                $this->driver->delete_expired();
            }

            Cache::$loaded = TRUE;
        }
    }

    /**
     * 通过id获取指定的缓存，如果没有缓存的数据则返回NULL(空)值。
     *
     * @param   string  唯一的缓存id
     * @return  mixed   已经缓存的数据 或 NULL(空)值
     */
    public function get($id)
    {
        $id = $this->sanitize_id($id);
        return $this->driver->get($id);
    }

    /**
     * Set a Cache item by id. Tags may also be added and a custom lifetime
     * can be set. Non-string data is automatically serialized.
     *
     * @param   string  唯一的缓存id
     * @param   mixed   缓存数据
     * @param   integer 缓存的过期时间，单位秒
     * @return  boolean
     */
    public function set($id, $data, $lifetime = '7200')
    {
        if (is_resource($data))
        {
            throw new QuickPHP_Cache_Exception('resources');
        }

        $id = $this->sanitize_id($id);

        if ($lifetime === NULL)
        {
            $lifetime = $this->config['lifetime'];
        }

        return $this->driver->set($id, $data, $lifetime);
    }

    /**
     * 按指定id删除一个缓存项目。
     *
     * @param   string   Cache id
     * @return  boolean
     */
    public function delete($id)
    {
        $id = $this->sanitize_id($id);
        return $this->driver->delete($id);
    }

    /**
     * 清空全部缓存方法，.
     * 请小心使用该方法，会影响正在使用缓存的程序。
     */
    public function flush()
    {
        return $this->driver->delete(TRUE);
    }

    /**
     * 序列化缓存的id,用于防止特殊字符做id影响存储
     *
     * @param   string   Cache id
     * @return  string
     */
    protected function sanitize_id($id)
    {
        return md5($id);
    }
}
