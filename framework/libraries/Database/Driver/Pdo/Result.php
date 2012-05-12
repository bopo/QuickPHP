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
 * @package    Database
 * @category   Exception
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
class QuickPHP_Database_Driver_Pdo_Result extends QuickPHP_Database_Result
{

    protected $_internal_row = 0;

    /**
     * Optimize table query
     *
     * Generates a platform-specific query so that a table can be optimized
     *
     * @access  private
     * @param   string  the table name
     * @return  object
     */
    public function __construct($result, $sql, $as_object)
    {
        parent::__construct($result, $sql, $as_object);
        $this->_total_rows  = $this->_result->rowCount();
    }

    /**
     * Optimize table query
     *
     * Generates a platform-specific query so that a table can be optimized
     *
     * @access  private
     * @param   string  the table name
     * @return  object
     */
    public function __destruct()
    {
        return TRUE;
    }

    /**
     * Optimize table query
     *
     * Generates a platform-specific query so that a table can be optimized
     *
     * @access  private
     * @param   string  the table name
     * @return  object
     */

    public function as_array()
    {
        return $this->_result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function seek($offset)
    {
        if($this->offsetExists($offset) and Pdo_seek($this->_result, $offset))
        {
            return $this->_current_row = $this->_internal_row = $offset;
        }

        return FALSE;
    }

    /**
     * Optimize table query
     *
     * Generates a platform-specific query so that a table can be optimized
     *
     * @access  private
     * @param   string  the table name
     * @return  object
     */
    public function current()
    {
        if($this->_current_row !== $this->_internal_row and ! $this->seek($this->_current_row))
        {
            return FALSE;
        }

        // Increment internal row for optimization assuming rows are fetched in order
        $this->_internal_row++;

        if($this->_as_object === TRUE)
        {
            return $this->_array2object(Pdo_fetch_array($this->_result));
        }
        elseif(is_string($this->_as_object))
        {
            return Pdo_fetch_single($this->_result, $this->_as_object);
        }
        else
        {
            return $this->_result->fetch(PDO::FETCH_ASSOC);
        }
    }

    protected function _array2object($array)
    {
        if( ! is_array($array))
        {
            return $array;
        }

        $object = new stdClass();

        if(is_array($array) && count($array) > 0)
        {
            foreach ($array as $name => $value)
            {
                $name = strtolower(trim($name));

                if( ! empty($name))
                {
                    $object->$name = $this->_array2object($value);
                }
            }

            return $object;
        }

        return FALSE;
    }
}