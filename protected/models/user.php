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
 * $Id: user.php 8650 2012-01-05 11:21:29Z bopo $
 *
 * 首页模块(Home)
 *
 * @package    Search
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */
class User_Model extends Auth_User_Model
{
    // protected $_db       = NULL;

    // // Relationships
    // protected $_has_one  = array();

    // protected $_has_many = array(
    //     '_tokens'   => array('model' => 'user_token'),
    //     '_roles'    => array('model' => 'role', 'through' => 'roles_has_users'),
    // );

    // protected $_belongs_to = array();

    /**
     * Complete the login for a user by incrementing the logins and saving login timestamp
     *
     * @return void
     */
    public function complete_login()
    {
        // 判断是否载入
        if ( ! $this->_loaded) 
        {
            return false;
        }

        if (isset($this->last_ip)) 
        {
            $this->last_ip = ip2long(request::ip_address());
        }

        return parent::complete_login();
    }

    /**
     * 保存当前对象，并加密密码.
     *
     * @return  ORM
     */
    public function save()
    {
        if ($this->empty_pk())
        {
            if(isset($this->created))
            {
                $this->created = time();
            }
        }
        else
        {
            if(isset($this->modified))
            {
                $this->modified = time();
            }
        }

        return parent::save();
    }

}