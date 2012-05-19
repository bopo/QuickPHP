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
    'there_can_be_only_one'       => '每个请求页面只允许一个 QuickPHP 的实例化',
    'uncaught_exception'          => '未捕获 {0} 异常：{1} 于文件 {2} 的行 {3}',
    'invalid_method'              => '无效方法 {0} 调用于 {1} 类中。',
    'invalid_property'            => '无效属性 {0} 调用于 {1} 类中。',
    'log_dir_unwritable'          => '日志目录不可写：{0}',
    'cache_dir_not_create'        => '高速缓存目录无法创建：{0}',
    'cache_dir_unwritable'        => '高速缓存目录不可写：{0}',
    'resource_not_found'          => '请求的 {0}，{1}，不存在',
    'invalid_filetype'            => '在视图配置文件内请求的文件类型，.{0}，不允许',
    'page_not_found'              => '您请求的页面 {0}，不存在。',
    'model_not_found'             => '您请求的模型 {0}，不存在。',
    'stats_footer'                => '页面加载: {0} 秒，使用内存: {1}。本程序由 QuickPHP v{2} 构建。',
    'error_file_line'             => '<tt>{0} <strong>[{1}]：</strong></tt>',
    'stack_trace'                 => '堆栈跟踪',
    'generic_error'               => '无法完成请求',
    'file_not_found'              => '文件未找到: {0}',
    'errors_disabled'             => '您可以返回<a href="{0}">首页</a>或者<a href="{1}">重新尝试</a>。',

    'default_route_not_found'     => '请在 APPPATH/config/routes.php 文件设置默认的路由参数值',
    'controller_not_found'        => 'QuickPHP 没有找到可以处理该请求的控制器',
    'controller_is_not_allowed'   => '产品模式下,未设置 ALLOW_PRODUCTION, 所以该控制器不允许执行: {0}',
    'method_is_not_exists'        => '不允许请求“_”前缀的方法名：{0}',
    'protected_or_private_method' => '请求的控制器的方法为 protected 或者 private ,不允许执行: {0}',

    // 驱动
    'driver_implements'           => '{0} 驱动在类 {1} 中必须继承 {2} 接口',
    'driver_not_found'            => '{0} 驱动在类 {1} 中没有发现',
    'extension_not_found'         => '{0} 扩展没有被安装.',

    // 资源名称
    'config'                      => '配置文件',
    'controller'                  => '控制器',
    'helper'                      => '辅助函数',
    'library'                     => '库',
    'driver'                      => '驱动',
    'model'                       => '模型',
    'view'                        => '视图',

    // HTTP请求
    'request_unknown_method'      => '无效的HTTP请求方法 {0}',
    'cannot_generate_etag'        => '无法自动构建 ETAG',
);