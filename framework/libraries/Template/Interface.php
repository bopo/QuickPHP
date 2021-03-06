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
 * 模板引擎驱动接口.
 *
 * $Id: Interface.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @category    QuickPHP
 * @package     Template
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Interface.php 8320 2011-10-05 14:59:55Z bopo $
 */
interface QuickPHP_Template_Interface
{
    /**
     * assign(分配)变量方法.
     */
    public function assign($var, $value = null);

    /**
     * append(追加)变量方法.
     */
    public function append($var, $value = null, $merge = false);

    /**
     * render(渲染)HTML方法.
     */
    public function render($tempate = null, $_top = array(), $return = false);
}