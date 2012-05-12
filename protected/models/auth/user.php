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
 * $Id: user.php 8646 2012-01-05 11:01:20Z bopo $
 *
 * 首页模块(Home)
 *
 * @package    Search
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */
class Auth_User_Model extends Custom_Model
{
    // 数据关系
    protected $_has_many = array(
        'tokens' => array('model' => 'user_token'),
        'roles'  => array('model' => 'role', 'through' => 'roles_has_users'),
    );

    // 验证规则
    protected $_rules = array(
        'username' => array(
            'not_empty'  => NULL,
            'min_length' => array(4),
            'max_length' => array(32),
            'regex'      => array('/^[-\pL\pN_.]++$/uD'),
        ),
        'password' => array(
            'not_empty'  => NULL,
            'min_length' => array(5),
            'max_length' => array(42),
        ),
        'password_confirm' => array(
            'matches'    => array('password'),
        ),
        'email' => array(
            'not_empty'  => NULL,
            'min_length' => array(4),
            'max_length' => array(127),
            'email'      => NULL,
        ),
    );

    // 验证回调
    protected $_callbacks = array(
        'username' => array('username_available'),
        'email'    => array('email_available'),
    );

    // 字段标签
    protected $_labels = array(
        'username'         => 'username',
        'email'            => 'email address',
        'password'         => 'password',
        'password_confirm' => 'password confirmation',
    );

    // 过滤字段
    protected $_ignored_columns = array(
        'password_confirm'
    );

    /**
     * Validates login information from an array, and optionally redirects
     * after a successful login.
     *
     * @param   array    values to check
     * @param   string   URI or URL to redirect to
     * @return  boolean
     */
    public function login(array $array, $redirect = FALSE)
    {
        $array = Validate::factory($array)
            ->label('username', $this->_labels['username'])
            ->label('password', $this->_labels['password'])
            ->filter(TRUE, 'trim')
            ->rules('username', $this->_rules['username'])
            ->rules('password', $this->_rules['password']);

        // 获得记住我选项
        $remember = isset($array['remember']);

        // 初始化登录状态
        $status = FALSE;

        if ($array->check())
        {
            $this->where('username', '=', $array['username'])->find();

            if ($this->loaded() AND Auth::instance()->login($this, $array['password'], $remember))
            {
                // 跳转到成功页面
                if (is_string($redirect))
                    return url::redirect($redirect);

                // 登录成功状态
                $status = TRUE;
            }
            else
            {
                $array->error('username', 'invalid');
            }
        }

        return $status;
    }

    /**
     * 修改密码
     *
     * @param   array    values to check
     * @param   string   URI or URL to redirect to
     * @return  boolean
     */
    public function change_password(array $array, $redirect = FALSE)
    {
        $array = Validate::factory($array)
            ->label('password', $this->_labels['password'])
            ->label('password_confirm', $this->_labels['password_confirm'])
            ->filter(TRUE, 'trim')
            ->rules('password', $this->_rules['password'])
            ->rules('password_confirm', $this->_rules['password_confirm']);

        if ((bool)($status = $array->check()))
        {
            // 修改密码
            $this->password = $array['password'];

            // 跳转成功页面
            if ($status = (bool) $this->save() AND is_string($redirect))
                return url::redirect($redirect);
        }

        return $status;
    }

    /**
     * Complete the login for a user by incrementing the logins and saving login timestamp
     *
     * @return void
     */
    public function complete_login()
    {
        // 判断是否载入
        if ( ! $this->_loaded)
            return TRUE;

        // 更新用户登录测试
        $this->logins += 1;

        // 设置最后登录时间
        $this->last_login = time();

        // 保存用户
        $this->save();
    }

    /**
     * Does the reverse of unique_key_exists() by triggering error if username exists.
     * Validation callback.
     *
     * @param   Validate  Validate object
     * @param   string    field name
     * @return  void
     */
    public function username_available($array, $field)
    {
        if ($this->unique_key_exists($array[$field]))
            $array->error($field, 'username_available', array($array[$field]));
    }

    /**
     * Does the reverse of unique_key_exists() by triggering error if email exists.
     * Validation callback.
     *
     * @param   Validate  Validate object
     * @param   string    field name
     * @return  void
     */
    public function email_available($array, $field)
    {
        if ($this->unique_key_exists($array[$field]))
            $array->error($field, 'email_available', array($array[$field]));
    }

    /**
     * 判断数据库中唯一字段的值是否已经存在.
     *
     * @param   mixed    the value to test
     * @return  boolean
     */
    public function unique_key_exists($value)
    {
        return (bool) Database::select(array('COUNT("*")', 'total_count'))
            ->from($this->_table_name)
            ->where($this->unique_key($value), '=', $value)
            ->where($this->_primary_key, '!=', $this->pk())
            ->execute($this->_db)
            ->get('total_count');
    }

    /**
     * Allows a model use both email and username as unique identifiers for login
     *
     * @param   string  unique value
     * @return  string  field name
     */
    public function unique_key($value)
    {
        return Validate::email($value) ? 'email' : 'username';
    }

    /**
     * 保存当前对象，并加密密码.
     *
     * @return  ORM
     */
    public function save()
    {
        // 判断密码如果被载入，则加密密码
        if (array_key_exists('password', $this->_changed))
            $this->_object['password'] = Auth::instance()->hash_password($this->_object['password']);

        return parent::save();
    }

}