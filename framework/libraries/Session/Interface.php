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
 * Session 驱动接口.
 *
 * $Id: Interface.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @package     Session
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Interface.php 8320 2011-10-05 14:59:55Z bopo $
 */
interface QuickPHP_Session_Interface
{

    /**
     * 打开方法.
     *
     * @param   string   save path
     * @param   string   session name
     * @return  boolean
     */
    public function open($path, $name);

    /**
     * 关闭方法.
     *
     * @return  boolean
     */
    public function close();

    /**
     * 读取方法.
     *
     * @param   string  session id
     * @return  string
     */
    public function read($id);

    /**
     * 写入方法.
     *
     * @param   string   session id
     * @param   string   session data
     * @return  boolean
     */
    public function write($id, $data);

    /**
     * 销毁方法.
     *
     * @param   string   session id
     * @return  boolean
     */
    public function destroy($id);

    /**
     * 重构ID方法
     *
     * @return  string
     */
    public function regenerate();

    /**
     * 垃圾回收.
     *
     * @param   integer  session expiration period
     * @return  boolean
     */
    public function gc($maxlifetime);
}