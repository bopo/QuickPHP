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

return array(
    'there_can_be_only_one' => '每个请求页面只允许一个 Quick 的实例化',
    'uncaught_exception'    => '未捕获 {0} 异常：{1} 于文件 {2} 的行 {3}',
    'invalid_method'        => '无效方法 {0} 调用于 {1}',
    'invalid_property'      => '属性 {0} 不存在于 {1} 类中。',
    'log_dir_unwritable'    => '日志目录不可写：{0}',
    'resource_not_found'    => '请求的 {0}，{1}，不存在',
    'invalid_filetype'      => '在视图配置文件内请求的文件类型，.{0}，不允许',
    'view_set_filename'     => '在调用 render 之前您必须设置视图文件名',
    'no_default_route'      => '请在 config/routes.php 文件设置默认的路由参数值',
    'no_controller'         => 'QuickPHP 没有找到处理该请求的控制器：{0}',
    'page_not_found'        => '您请求的页面 {0}，不存在。',
    'model_not_found'       => '您请求的模型 {0}，不存在。',
    'stats_footer'          => '页面加载 {0} 秒，使用内存 {1}。程序生成 QuickPHP v{2}。',
    'error_file_line'       => '<tt>{0} <strong>[{1}]：</strong></tt>',
    'stack_trace'           => '堆栈跟踪',
    'generic_error'         => '无法完成请求',
    'errors_disabled'       => '您可以返回<a href="{0}">首页</a>或者<a href="{1}">重新尝试</a>。',

    // 驱动
    'driver_implements'     => '{0} 驱动在类 {1} 中必须继承 {2} 接口',
    'driver_not_found'      => '{0} 驱动在类 {1} 中没有发现',

    // 资源名称
    'config'                => '配置文件',
    'controller'            => '控制器',
    'helper'                => '辅助函数',
    'library'               => '库',
    'driver'                => '驱动',
    'model'                 => '模型',
    'view'                  => '视图',
);