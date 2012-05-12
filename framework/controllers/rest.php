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
 * 抽象控制器类的REST风格的控制器映射。支持GET，PUT，POST和DELETE。默认情况下，这些方法将被映射到这些操作：
 *
 * GET
 * :  映射到“查看”操作，列出了所有对象
 *
 * POST
 * :  映射到“创建”操作，创建一个新的对象
 *
 * PUT
 * :  映射到“更新/创建”操作，更新现有对象
 *
 * DELETE
 * :  映射到“删除”操作，删除现有对象
 *
 * 通过增减`$_action_map`的内容类增减所支持的映射
 *
 * 由于大多数Web浏览器只支持GET和POST方法, 一般来说，这个类应该只用于web services和API。
 *
 * @category    QuickPHP
 * @package     Controller
 * @subpackage  Reset_Controller
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license.html
 * @version     $Id: rest.php 8320 2011-10-05 14:59:55Z bopo $
 */
abstract class QuickPHP_REST_Controller extends QuickPHP_Controller
{

    protected $_action_map = array(
        'GET'    => 'index', 
        'PUT'    => 'update', 
        'POST'   => 'create', 
        'DELETE' => 'remove',
    );

    protected $_action_requested = '';

    /**
     * 检查所请求的方法是否有效. 如果支持,则设置方法名. 否则色设置 invalid(无效) 方法。
     */
    public function before()
    {
        // $method = QuickPHP::route()->get('method');
        $method = $_SERVER['REQUEST_METHOD'];

        $this->_action_requested = $method;

        if( ! isset($this->_action_map[$method]))
        {
            $this->method = 'invalid';
        }
        else
        {
            $this->method = $this->_action_map[$method];
        }

        return parent::before();
    }

    /**
     * 发送一个405 "Method Not Allowed" 并返回一个运行的方法列表
     */
    protected function invalid()
    {
        Header("http/1.1 405 Method Not Allowed");
        exit("Allow method: ".implode(', ', array_keys($this->_action_map)));
    }
}