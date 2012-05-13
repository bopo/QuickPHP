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
        session_id VARCHAR(127) NOT null,
        last_activity INT(10) UNSIGNED NOT null,
        data TEXT NOT null,
        PRIMARY KEY (session_id)
    );
    */

    protected $db         = 'default';
    protected $table      = 'sessions';
    protected $written    = false;
    protected $encrypt    = null;
    protected $session_id = null;

    public function __construct()
    {
        $config = QuickPHP::config('session');

        if( ! empty($config['encryption']))
        {
            $this->encrypt = Encrypt::instance();
        }

        $this->session = ORM::factory('session');
    }

    public function open($path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $session = ORM::factory('session', $id);

        if($session->session_id === null)
        {
            return $this->session_id = null;
        }

        $this->session_id = $id;
        return ($this->encrypt === null) ? base64_decode($session->data) : $this->encrypt->decode($session->data);
    }

    public function write($id, $data)
    {
        $session = ORM::factory('session', $id);

        if($session->session_id !== null)
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
        $session->data = ($this->encrypt === null) ? base64_encode($data) : $this->encrypt->encode($data);
        $session->save();

        return (bool) $session;
    }

    public function destroy($id)
    {
        $session = ORM::factory('session')->delete($id);
        $this->session_id = null;
        return (bool)$session;
    }

    public function regenerate()
    {
        session_regenerate_id();
        return session_id();
    }

    public function gc($maxlifetime)
    {
        $session = ORM::factory('session')
            ->where('last_activity', '<', time() - $maxlifetime)
            ->delete_all();

        return (bool) $session;
    }
}