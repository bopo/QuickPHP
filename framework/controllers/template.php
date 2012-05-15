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
 * QuickPHP自带简单模板控制器，预先实例化好模板对象，使用时只需要赋值即可
 *
 * @category    QuickPHP
 * @package     Controller
 * @subpackage  Template_Controller
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: template.php 8320 2011-10-05 14:59:55Z bopo $
 */
abstract class QuickPHP_Template_Controller extends QuickPHP_Controller
{
    /**
     * @const string 允许所有控制装置都运行在生产模式下
     */
    const ALLOW_PRODUCTION = true;

    /**
     * @var  boolean  自动渲染模板
     */
    public $auto_render    = true;

    /**
     * @var  string  模板名称
     */
    public $template       = null;

    /**
     * @var  string  输出MIME类型
     */
    public $mime_type      = 'html';

    /**
     * @var  string  模板的子路径
     */
    public $directory      = '';
    public $view           = null;

    /**
     * 构造函数，加载模板引擎
     *
     * @return  void
     */
    public function __construct()
    {
        // 判断如果自动渲染开关为true，并初始化模板引擎
        if($this->auto_render === true)
        {
            $this->view = Template::instance();
        }
    }

    /**
     * 模板渲染之前运行方法
     */
    public function before()
    {
        return parent::before();
    }

    /**
     * 模板渲染之后运行的方法
     */
    public function after()
    {
        $mime = QuickPHP::config('mimes')->get($this->mime_type, null);

        if( ! empty($mime) and ! headers_sent())
        {
            header('Content-Type: ' . $mime[0]);
        }

        if($this->auto_render === true )
        {
            if(empty($this->template))
            {
                $directory  = rtrim($this->directory, '/') . '/';
                $controller = QuickPHP::route()->get('controller');
                $method     = QuickPHP::route()->get('method', 'index');

                $this->template = $directory . $controller . '/' . $method;
                $this->template = str_replace("_", "/", $this->template);
            }

            $this->view->render($this->template);
        }

        return parent::after();
    }

}