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
    'uncaught_exception'          => '未捕获 %s 异常：%s 于文件 %s 的行 %s',
    'invalid_method'              => '无效方法 %s 调用于 %s 类中。',
    'invalid_property'            => '无效属性 %s 调用于 %s 类中。',
    'log_dir_unwritable'          => '日志目录不可写：%s',
    'cache_dir_not_create'        => '高速缓存目录无法创建：%s',
    'cache_dir_unwritable'        => '高速缓存目录不可写：%s',
    'resource_not_found'          => '请求的 %s，%s，不存在',
    'invalid_filetype'            => '在视图配置文件内请求的文件类型，.%s，不允许',
    'page_not_found'              => '您请求的页面 %s，不存在。',
    'model_not_found'             => '您请求的模型 %s，不存在。',
    'stats_footer'                => '页面加载: %s 秒，使用内存: %s。本程序由 QuickPHP v%s 构建。',
    'error_file_line'             => '<tt>%s <strong>[%s]：</strong></tt>',
    'stack_trace'                 => '堆栈跟踪',
    'generic_error'               => '无法完成请求',
    'file_not_found'              => '文件未找到: %s',
    'errors_disabled'             => '您可以返回<a href="%s">首页</a>或者<a href="%s">重新尝试</a>。',

    'default_route_not_found'     => '请在 APPPATH/config/routes.php 文件设置默认的路由参数值',
    'controller_not_found'        => 'QuickPHP 没有找到可以处理该请求的控制器',
    'controller_is_not_allowed'   => '产品模式下,未设置 ALLOW_PRODUCTION, 所以该控制器不允许执行: %s',
    'method_is_not_exists'        => '不允许请求“_”前缀的方法名：%s',
    'protected_or_private_method' => '请求的控制器的方法为 protected 或者 private ,不允许执行: %s',

    // 驱动
    'driver_implements'           => '%s 驱动在类 %s 中必须继承 %s 接口',
    'driver_not_found'            => '%s 驱动在类 %s 中没有发现',
    'extension_not_found'         => '%s 扩展没有被安装.',

    // 资源名称
    'config'                      => '配置文件',
    'controller'                  => '控制器',
    'helper'                      => '辅助函数',
    'library'                     => '库',
    'driver'                      => '驱动',
    'model'                       => '模型',
    'view'                        => '视图',

    // HTTP请求
    'request_unknown_method'      => '无效的HTTP请求方法 %s',
    'cannot_generate_etag'        => '无法自动构建 ETAG',
);