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
 * QuickPHP 缓存驱动 Xcache.
 *
 * $Id: Xcache.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Cache
 */
class QuickPHP_Cache_Driver_XCache implements QuickPHP_Cache_Interface
{
    protected $PHP_AUTH_USER = NULL;
    protected $PHP_AUTH_PW   = NULL;

    /**
     * 构造函数
     *
     */
    public function __construct($config = array())
    {
        foreach ($config as $key => $var)
            $this->$$key = $var;

        if( ! extension_loaded('xcache'))
            throw new QuickPHP_Cache_Exception('extension_not_loaded', array('xcache'));
    }

    /**
     * 获取缓存数据，继承父类接口.
     *
     * @param string $id 键值
     * @return mixed
     */
    public function get($id)
    {
        if(xcache_isset($id))
            return xcache_get($id);

        return NULL;
    }

    /**
     * 设置缓存数据，继承父类接口.
     *
     * @param int $id
     * @param array $data
     * @param string $tags
     * @param int $lifetime
     * @return void
     */
    public function set($id, $data, $lifetime)
    {
        return (bool) xcache_set($id, $data, $lifetime);
    }

    /**
     * 删除缓存，继承父类接口.
     *
     * @param string $id
     * @param bool $tag
     * @return mixed
     */
    public function delete($id)
    {
        if(xcache_isset($id))
            return xcache_unset($id);

        return FALSE;
    }

    /**
     * 认证
     *
     * @param bool $reverse
     * @return mixed
     */
    private function auth($reverse = FALSE)
    {
        static $backup = array();

        $keys = array('PHP_AUTH_USER', 'PHP_AUTH_PW');

        foreach ($keys as $key)
        {
            if($reverse)
            {
                if(isset($backup[$key]))
                {
                    $_SERVER[$key] = $backup[$key];
                    unset($backup[$key]);
                }
                else
                {
                    unset($_SERVER[$key]);
                }
            }
            else
            {
                $value = getenv($key);

                if( ! empty($value))
                    $backup[$key] = $value;

                $_SERVER[$key] = $this->$key;
            }
        }
    }

    /** (non-PHPdoc)
     * @see QuickPHP_Cache_Interface::delete_expired()
     */
    public function delete_expired()
    {
        return TRUE;
    }

    /**
     * 清空数据
     *
     * @return bool
     */
    public function flush()
    {
        $this->auth();

        $result = TRUE;

        for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++)
        {
            if(xcache_clear_cache(XC_TYPE_VAR, $i) !== NULL)
            {
                $result = FALSE;
                break;
            }
        }

        $this->auth(TRUE);

        return $result;
    }
}