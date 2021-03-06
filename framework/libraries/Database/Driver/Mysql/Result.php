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
 * MySQL database result.
 *
 * @category   QuickPHP
 * @package    Database
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
class QuickPHP_Database_Driver_MySQL_Result extends QuickPHP_Database_Result
{

    protected $_internal_row = 0;

    public function __construct($result, $sql, $as_object)
    {
        parent::__construct($result, $sql, $as_object);
        $this->_total_rows = mysql_num_rows($result);
    }

    public function __destruct()
    {
        if(is_resource($this->_result))
        {
            return mysql_free_result($this->_result);
        }
    }

    public function seek($offset)
    {
        if($this->offsetExists($offset) and mysql_data_seek($this->_result, $offset))
        {
            return $this->_current_row = $this->_internal_row = $offset;
        }

        return false;
    }

    public function current()
    {
        if($this->_current_row !== $this->_internal_row and ! $this->seek($this->_current_row))
        {
            return false;
        }

        $this->_internal_row++;

        if($this->_as_object === true)
        {
            return mysql_fetch_object($this->_result);
        }
        elseif(is_string($this->_as_object))
        {
            return mysql_fetch_object($this->_result, $this->_as_object);
        }
        else
        {
            return mysql_fetch_assoc($this->_result);
        }
    }
}
