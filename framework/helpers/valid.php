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
 * QuickPHP 数据验证助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  valid
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: valid.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_valid extends QuickPHP_Validate
{
    /**
     * Checks whether a string is a valid text. Letters, numbers, whitespace,
     * dashes, periods, and underscores are allowed.
     *
     * @param   string   text to check
     * @return  boolean
     */
    public static function standard_text($str)
    {
        // pL matches letters
        // pN matches numbers
        // pZ matches whitespace
        // pPc matches underscores
        // pPd matches dashes
        // pPo matches normal puncuation
        return (bool) preg_match('/^[\pL\pN\pZ\p{Pc}\p{Pd}\p{Po}]++$/uD', (string) $str);
    }
}