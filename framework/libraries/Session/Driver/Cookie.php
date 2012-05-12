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
 * Session的COOKIE驱动.
 *
 * $Id: Cookie.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @category    QuickPHP
 * @package     Session
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Cookie.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Session_Driver_Cookie implements QuickPHP_Session_Interface
{

    protected $cookie_name;
    protected $encrypt;

    /**
     * __construct method
     *
     * @return mixed
     */
    public function __construct()
    {
        $this->cookie_name = QuickPHP::config('session')->get('name') . '_data';

        if(QuickPHP::config('session')->get('encryption'))
        {
            $this->encrypt = Encrypt::instance();
        }
    }

    /**
     * 打开方法
     *
     * @return mixed
     */
    public function open($path, $name)
    {
        return TRUE;
    }

    /**
     * 关闭方法
     *
     * @return mixed
     */
    public function close()
    {
        return TRUE;
    }

    /**
     * 读取方法
     *
     * @return mixed
     */
    public function read($id)
    {
        $data = (string) cookie::get($this->cookie_name);

        if($data == '')
        {
            return $data;
        }

        return empty($this->encrypt) ? base64_decode($data) : $this->encrypt->decode($data);
    }

    /**
     * 写入方法
     *
     * @return mixed
     */
    public function write($id, $data)
    {
        $data = empty($this->encrypt) ? base64_encode($data) : $this->encrypt->encode($data);

        if(strlen($data) > 4048)
        {
            return FALSE;
        }

        return cookie::set($this->cookie_name, $data, QuickPHP::config('session')->get('expiration'));
    }

    /**
     * 销毁方法
     *
     * @return mixed
     */
    public function destroy($id)
    {
        return cookie::delete($this->cookie_name);
    }

    /**
     * 重构方法
     *
     * @return mixed
     */
    public function regenerate()
    {
        session_regenerate_id(true);
        return session_id();
    }

    /**
     * 垃圾回收方法
     *
     * @return mixed
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}
