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
 * $Id: login.php 8727 2012-01-12 07:25:28Z bopo $
 *
 * @package    Home
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */
class Passport_Login_Controller extends Abstract_Passport_Controller
{
    public function __call($method, $args)
    {
        $this->view->uname  = $_GET['u'];
        $this->view->title  = '快速登录';
    }

    public function finish()
    {
        $this->auto_render = FALSE;

        if(request::is_ajax() AND $_POST)
        {

            try
            {
                $valid = Validate::factory($_POST)
                    ->label('username', 'username')
                    ->label('password', 'password')
                    ->label('remember', 'remember')
                    ->label('referer',  'referer')
                    ->rule(TRUE, 'trim');

                if($valid->check())
                {
                    $username = $valid['username'];
                    $password = $valid['password'];
                    $remember = (bool) isset($valid['remember']);
                    $referer  = ! empty($valid['referer']) ? $valid['referer'] : url::bind('user');

                    if(empty($username))
                    {
                        $this->error('请输入用户名!');
                    }

                    if(empty($password))
                    {
                        $this->error('请输入密码!');
                    }

                    if(Auth::instance()->login($username, $password, $remember, 'login'))
                    {
                        $this->success('登录成功', $refer);
                    }
                    else
                    {
                        $this->error('用户名或者密码错误，请重新登录');
                    }
                }
                else
                {
                    $this->error('用户名或者密码错误，请重新登录');
                }
            }
            catch (QuickPHP_Exception $e)
            {
                FirePHP::instance()->info($e->getMessage());
            }
        }
    }
}