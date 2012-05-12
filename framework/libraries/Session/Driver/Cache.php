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
 * Session 缓存驱动.
 *
 * Cache library config goes in the session.storage config entry:
 * $config['storage'] = array(
 * 'driver' => 'apc',
 * 'requests' => 10000
 * );
 * Lifetime does not need to be set as it is
 * overridden by the session expiration setting.
 *
 * $Id: Cache.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @category    QuickPHP
 * @package     Session
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Cache.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Session_Driver_Cache implements QuickPHP_Session_Interface
{

    protected $cache;
    protected $config;
    protected $encrypt;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->config = QuickPHP::config('session');

        if($this->config->get('encryption', false))
        {
            $this->encrypt = new Encrypt();
        }
    }

    /**
     * 打开方法.
     *
     * @param string $path
     * @param sting $name
     * @return object
     */
    public function open($path, $name)
    {
        $config = $this->config->get('storage', NULL);

        if(empty($config))
        {
            $config = QuickPHP::config('cache')->get('default');
        }
        elseif(is_string($config))
        {
            $name = $config;

            if(($config = QuickPHP::config('cache')->get($config)) === NULL)
            {
                throw new QuickPHP_Cache_Exception('cache.undefined_group', $name);
            }
        }

        $config['lifetime'] = ($this->config->expiration == 0) ? 86400 : $this->config->get('expiration');
        $this->cache        = new Cache('file', $config);

        return is_object($this->cache);
    }

    /**
     * 关闭方法
     *
     * @return bool
     */
    public function close()
    {
        return TRUE;
    }

    /**
     * 读取方法.
     *
     * @param mixed $id
     * @return string
     */
    public function read($id)
    {
        $id     = 'session_' . $id;
        $data   = $this->cache->get($id);

        if(empty($data))
        {
            return $this->config->encryption ? $this->encrypt->decode($data) : $data;
        }

        return '';
    }

    /**
     * 写入方法
     *
     * @param string $id
     * @param mixed $data
     * @return bool
     */
    public function write($id, $data)
    {
        $id   = 'session_' . $id;
        $data = $this->config->encryption ? $this->encrypt->encode($data) : $data;

        return $this->cache->set($id, $data);
    }

    /**
     * 销毁方法
     *
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        $id = 'session_' . $id;

        return $this->cache->delete($id);
    }

    /**
     * 重构方法.
     *
     * @return string
     */
    public function regenerate()
    {
        session_regenerate_id(TRUE);

        return session_id();
    }

    /**
     * 垃圾回收方法.
     *
     * @param int $maxlifetime 最大时限
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return TRUE;
    }

}