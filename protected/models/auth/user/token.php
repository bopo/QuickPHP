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
 * $Id: token.php 8585 2011-12-20 10:19:40Z bopo $
 *
 * 首页模块(Home)
 *
 * @package    Search
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */
class Auth_User_Token_Model extends ORM
{
    // Relationships
    protected $_belongs_to = array('user' => array());

    // Current timestamp
    protected $_now;

    /**
     * Handles garbage collection and deleting of expired objects.
     *
     * @return  void
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);

        // Set the now, we use this a lot
        $this->_now = time();

        // Do garbage collection
        if (mt_rand(1, 100) === 1)
            $this->delete_expired();

        // This object has expired
        if ($this->expires < $this->_now)
            $this->delete();
    }

    /**
     * Overload saving to set the created time and to create a new token
     * when the object is saved.
     *
     * @return  ORM
     */
    public function save()
    {
        if ($this->loaded() === FALSE)
        {
            // Set the created time, token, and hash of the user agent
            $this->created      = $this->_now;
            $this->user_agent   = sha1(request::user_agent());
        }

        while (TRUE)
        {
            // Generate a new token
            $this->token = $this->create_token();

            try
            {
                return parent::save();
            }
            catch (QuickPHP_Database_Exception $e)
            {
                // Collision occurred, token is not unique
            }
        }
    }

    /**
     * Deletes all expired tokens.
     *
     * @return  ORM
     */
    public function delete_expired()
    {
        Database::delete($this->_table_name)
            ->where('expires', '<', $this->_now)
            ->execute($this->_db);

        return $this;
    }

    /**
     * Generate a new unique token.
     *
     * @return  string
     * @uses    Text::random
     */
    protected function create_token()
    {
        return text::random('alnum', 32);
    }

}