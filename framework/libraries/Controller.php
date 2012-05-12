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
 * QuickPHP 控制器的抽象类。
 *
 * @category    QuickPHP
 * @package     Librares
 * @subpackage  Controller
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Config.php 8641 2012-01-05 08:35:39Z bopo $
 */
abstract class QuickPHP_Controller
{

    /**
     * @const string 用来定义控制器是否为产品模式下运行,子类可以重载
     */
    const ALLOW_PRODUCTION = TRUE;

    /**
     * @const string
     */
    public $response = NULL;

    /**
     * 控制器执行前运行的方法.
     */
    public function before()
    {
    }

    /**
     * 控制器执行后运行的方法.
     */
    public function after()
    {
    }

    /**
     * __call 重载方法.
     *
     * @param   string  method
     * @param   array   arguments
     * @return  void
     */
    public function __call($method, $args)
    {
        throw new QuickPHP_Exception('page_not_found', QuickPHP::route()->segments, 404);
    }
}
