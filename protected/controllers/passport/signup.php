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
 * $Id: signup.php 8727 2012-01-12 07:25:28Z bopo $
 *
 * @package    Home
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */
class Passport_Signup_Controller extends Abstract_Passport_Controller
{
    public function __call($method, $args)
    {
        $this->view->invite = isset($_GET['invite']) ? $_GET['invite'] : NULL;
        $this->view->title  = '免费注册';
    }

    public function confirm()
    {
    }

    public function step2()
    {
    }

    public function step3()
    {
    }

    public function step4()
    {
    }

    public function step5()
    {
    }

    public function step6()
    {
    }

    public function finish()
    {
        $this->auto_render = FALSE;

        if(request::is_ajax() AND $_POST)
        {
            $valid = Validate::factory($_POST)
                ->label('username',         'username')
                ->label('password',         'password')
                ->label('password_confirm', 'password_confirm')
                ->label('captcha',          'captcha')
                ->label('invite',           'invite ')
                ->label('email',            'email')
                ->rule(TRUE, 'trim')
                ->callback(TRUE, 'trim')
                ->filter(TRUE, 'trim');

            if($valid->check())
            {
                try
                {
                    if(!Captcha::instance()->valid($_POST['captcha']))
                        $this->error("验证码错误，请重新输入!");

                    $user = ORM::factory('user');
                    $user->username         = $valid['username'];
                    $user->password         = $valid['password'];
                    $user->password_confirm = $valid['password_confirm'];
                    $user->email            = $valid['email'];

                    // 验证数据
                    if($user->validate())
                    {
                        // 保存用户数据
                        $user->save();

                        // 增加角色
                        $user->add('roles', ORM::factory('role', array('name' => 'login')));

                        // 增加邀请
                        if(!empty($valid['invite']))
                        {
                            $inviter = ORM::factory('user', array('username' => $valid['invite']));

                            if(!empty($invite->id))
                                $user->add('invite', $inviter, array('created' => time())); // invited
                        }

                        Auth::instance()->force_login($user);

                        $this->success('注册成功', 'user');
                    }
                    else
                    {
                        $this->error($user->error());
                    }
                }
                catch(Exception $e)
                {
                    $this->error($e->getMessage());
                }
            }
        }

        return url::redirect('signup');
    }

    public function unique()
    {
        $this->auto_render = FALSE;

        if(request::is_ajax())
        {
            $valid = Validate::factory($_GET)
                ->label('username', 'username')
                ->label('email',    'email')
                ->rule(TRUE, 'trim')
                ->callback(TRUE, 'trim')
                ->filter(TRUE, 'trim');

            if(!empty($valid['username']))
                $array['username']  = $valid['username'];

            if(!empty($_GET['email']))
                $array['email']     = $valid['email'];

            $user = ORM::factory('user', $array);

            if(empty($user->id))
                exit('true');

            exit('false');
        }

        exit('No direct script access allowed');
    }
}