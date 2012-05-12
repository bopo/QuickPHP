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
 * Config exception.
 *
 * @package    Config
 * @category   QuickPHP
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
class QuickPHP_Config_Exception extends QuickPHP_Exception
{
    /**
     * QuickPHP自定义异常处理,将程序错误的错误信息转化为人类可读内容
     *
     * throw new QuickPHP_Exception('Something went terrible wrong, :user',
     * array(':user' => $user));
     *
     * @param   string   错误信息
     * @param   array    错误变量
     * @param   integer  异常代码
     * @return  void
     */
    public function __construct($message, array $variables = NULL, $code = 0)
    {
        parent::__construct('config.'.$message, $variables, 500);
    }

}