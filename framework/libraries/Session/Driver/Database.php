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
 * Session 数据库驱动.
 *
 * @category    QuickPHP
 * @package     Session
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Database.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Session_Driver_Database implements QuickPHP_Session_Interface
{

    /*
    CREATE TABLE sessions
    (
        session_id VARCHAR(127) NOT NULL,
        last_activity INT(10) UNSIGNED NOT NULL,
        data TEXT NOT NULL,
        PRIMARY KEY (session_id)
    );
    */

    protected $db           = 'default';
    protected $table        = 'sessions';
    protected $encrypt      = NULL;
    protected $session_id   = NULL;
    protected $written      = FALSE;

    /**
     * 构造函数,初始化session数据库模型
     *
     */
    public function __construct()
    {
        $config = QuickPHP::config('session');

        if( ! empty($config['encryption']))
        {
            $this->encrypt = Encrypt::instance();
        }

        $this->session = ORM::factory('session');
    }

    /**
     * 打开方法
     *
     * @param string $path
     * @param string $name
     * @return bool
     */
    public function open($path, $name)
    {
        return TRUE;
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
     * 读取方法
     *
     * @return bool
     */
    public function read($id)
    {
        $session = ORM::factory('session', $id);

        if($session->session_id === NULL)
        {
            return $this->session_id = NULL;
        }

        $this->session_id = $id;
        return ($this->encrypt === NULL) ? base64_decode($session->data) : $this->encrypt->decode($session->data);
    }

    /**
     * 写入方法
     *
     * @return bool
     */
    public function write($id, $data)
    {
        $session = ORM::factory('session', $id);

        if($session->session_id !== NULL)
        {
            if($id !== $this->session_id)
            {
                $this->session_id = $id;
            }
        }
        else
        {
            $session->session_id = $id;
        }

        $session->last_activity = time();
        $session->data = ($this->encrypt === NULL) ? base64_encode($data) : $this->encrypt->encode($data);
        $session->save();

        return (bool) $session;
    }

    /**
     * 销毁方法
     *
     * @return bool
     */
    public function destroy($id)
    {
        $session = ORM::factory('session')->delete($id);
        $this->session_id = null;
        return (bool)$session;
    }

    /**
     * 重构方法
     *
     * @return bool
     */
    public function regenerate()
    {
        session_regenerate_id();
        return session_id();
    }

    /**
     * 垃圾回收方法
     *
     * @return bool
     */
    public function gc($maxlifetime)
    {
        $session = ORM::factory('session')
            ->where('last_activity', '<', time() - $maxlifetime)
            ->delete_all();

        return (bool) $session;
    }
}