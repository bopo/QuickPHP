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
 * $Id: forget.php 8727 2012-01-12 07:25:28Z bopo $
 *
 * @package    Home
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */
class Passport_Forget_Controller extends Abstract_Passport_Controller
{
    public function __call($method, $args)
    {
        if(request::is_ajax() AND $_POST)
        {
            $this->auto_render = FALSE;

            // 用户选择用户名或者Email来获取密码
            // 如果用户名判断用户名是否存在，并读取用户Email地址
            // 如果Email，判断是否存在
            // 发送Email通知，提示信息

            $valid = Validate::factory($_POST)
                ->label('username', 'username')
                ->filter(TRUE, 'trim');

            if($valid->check())
            {
                try
                {
                    $user = ORM::factory('user');
                    $user->where($user->unique_key($valid['username']), '=', $valid['username']);
                    $user->find();

                    if(empty($user->id))
                        $this->error('该用户名或者邮件不存在');

                    $token = uniqid(time());

                    ORM::factory('forget')
                        ->where('user_id', '=', $user->id)
                        ->delete_all();

                    $forget = ORM::factory('forget');
                    $forget->token      = $token;
                    $forget->user_id    = $user->id;
                    $forget->user_agent = request::user_agent();
                    $forget->created    = time();
                    $forget->expired    = time() + (3600 * 24);
                    $forget->save();

                    $array = $user->as_array();
                    $array['token']       = $token;
                    $array['domain']      = trim(QuickPHP::$domain,"/");
                    $array['pubDate']     = date("Y-m-d");
                    $array['pubDataTime'] = date("Y-m-d H:i:s");

                    // 发送通知邮件
                    sendmail::send('forget', $array);

                    $this->success('请查看您的邮箱，继续下一步操作！');
                }
                catch(Exception $e)
                {
                    $this->error($e->getMessage());
                }
            }
        }

        $this->view->title = '忘记密码';
    }

    public function verify()
    {
        // 判断令牌码是否过期
        // 通过令牌码获取用户信息
        // 修改密码，写入数据库
        // 完成重置密码，提示信息

        ORM::factory('forget')
            ->where('expired', '<=', time())
            ->delete_all();

        if(request::is_ajax() AND $_POST)
        {
            $this->auto_render = FALSE;

            $valid = Validate::factory($_POST)
                ->label('verify',    'token')
                ->label('user_id',  'user id')
                ->label('password', 'password')
                ->filter(TRUE, 'trim');

            if ($valid->check())
            {
                $forget = ORM::factory('forget', array('verify' => $valid['verify']));

                if(empty($forget->id))
                    $this->error('对不起，链接已经过期');

                $user = ORM::factory('user', $valid['user_id']);
                $user->password = $valid['password'];
                $user->save();

                $forget->delete();

                $this->success('密码修改成功,现在可以登录了!', 'login');
            }
        }

        $forget = ORM::factory('forget', array('token' => $_GET['token']));

        if(empty($forget->id))
            exit('对不起，链接已经过期!');

        $this->view->token      = $forget->token;
        $this->view->user_id    = $forget->user->id;
        $this->view->username   = $forget->user->username;
    }

    public function change_password()
    {

    }
}