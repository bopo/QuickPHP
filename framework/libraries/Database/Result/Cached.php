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
 * Cached database result.
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Cached.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Database_Result_Cached extends QuickPHP_Database_Result
{

    public function __construct(array $result, $sql, $as_object = NULL)
    {
        parent::__construct($result, $sql, $as_object);
        $this->_total_rows = count($result);
    }

    public function __destruct()
    {
    }

    public function cached()
    {
        return $this;
    }

    public function seek($offset)
    {
        if($this->offsetExists($offset))
        {
            $this->_current_row = $offset;
            return true;
        }

        return false;
    }

    public function current()
    {
        return $this->_result[$this->_current_row];
    }
}