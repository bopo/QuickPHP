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
 * Database expressions can be used to add unescaped SQL fragments to a
 * [Database_Query_Builder] object.
 * QuickPHP 数据库请求逃逸封装类.
 *
 * For example, you can use an expression to generate a column alias:
 *
 * // SELECT CONCAT(first_name, last_name) AS full_name
 * $query = Database::select(array(Database::expr('CONCAT(first_name, last_name)'), 'full_name')));
 *
 * @category    QuickPHP
 * @package     Database
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Expression.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Database_Expression
{

    protected $_value;

    /**
     * 设置表达式字符串
     *
     * $expression = new Database_Expression('COUNT(users.id)');
     * or
     * $expression = Database::Expr('COUNT(users.id)');
     *
     * @return  void
     */
    public function __construct($value)
    {
        $this->_value = $value;
    }

    /**
     * 获得表单式的值
     *
     * $sql = $expression->value();
     *
     * @return  string
     */
    public function value()
    {
        return (string) $this->_value;
    }

    /**
     * 魔术方法 __toString 获得表单式的值
     *
     * echo $expression;
     *
     * @return  string
     * @uses    Database_Expression::value
     */
    public function __toString()
    {
        return $this->value();
    }
}