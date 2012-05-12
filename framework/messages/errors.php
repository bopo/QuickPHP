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
    E_SYSPATH            => array( 1, '框架错误', '请根据下面的相关错误查阅 QuickPHP 文档。'),
    E_PAGE_NOT_FOUND     => array( 1, '页面不存在', '请求页面不存在。或许它被转移，删除或存档。'),
    E_DATABASE_ERROR     => array( 1, '数据库错误', '数据库在执行程序时出现错误。请从下面的错误信息检查数据库错误。'),
    E_RECOVERABLE_ERROR  => array( 1, '可回收错误', '发生错误在加载此页面时。如果这个问题仍然存在，请联系网站管理员。'),
    E_ERROR              => array( 1, '致命错误', ''),
    E_USER_ERROR         => array( 1, '致命错误', ''),
    E_PARSE              => array( 1, '语法错误', ''),
    E_WARNING            => array( 1, '警告消息', ''),
    E_USER_WARNING       => array( 1, '警告消息', ''),
    E_STRICT             => array( 2, '严格（标准）模式错误', ''),
    E_NOTICE             => array( 2, '运行信息', ''),
);

