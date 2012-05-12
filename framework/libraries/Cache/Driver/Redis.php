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
 * QuickPHP 缓存驱动 MongoDB.
 *
 * $Id: TokyoTyrant.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Cache
 */
class QuickPHP_Cache_Driver_Redis implements QuickPHP_Cache_Interface
{
    protected $backend;
    protected $flags;

    public function __construct($config = array())
    {
        if( ! extension_loaded('MongoDB'))
            throw new QuickPHP_Cache_Exception('extension_not_loaded', array('Redis'));

        $this->backend  = new TokyoTyrant();
        $this->flags    = $config['compression'] ? MEMCACHE_COMPRESSED : FALSE;
        $server         = $config['servers'];

        $this->backend->connect($server['host'], $server['port'], (bool) $server['persistent']);
    }

    /**
     * 获取缓存数据
     *
     * @param string $id 键值
     * @return mixed
     */
    public function get($id)
    {
        return (($return = $this->backend->get($id)) === FALSE) ? NULL : $return;
    }

    /**
     * 设置缓存数据
     *
     * @param int $id
     * @param array $data
     * @param string $tags
     * @param int $lifetime
     * @return void
     */
    public function set($id, $data, $lifetime)
    {
        $lifetime = ($lifetime !== 0) ? $lifetime + time() : $lifetime;
        return $this->backend->put($id, $data, $lifetime);
    }

    /**
     * 删除缓存
     *
     * @param string $id
     * @param bool $tag
     * @return mixed
     */
    public function delete($id)
    {
        return TRUE;
    }

    /**
     * 删除过时数据
     *
     * @return bool
     */
    public function delete_expired()
    {
        return TRUE;
    }

}