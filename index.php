<?php
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
/** framework 目录路径  */
$framework  = dirname(__FILE__) . "/framework";
/** protected 目录路径 */
$protected  = dirname(__FILE__) . "/protected";
/** runtime 目录路径 */
$runtime    = dirname(__FILE__) . "/runtime";

/** 系统常量 EXT,入口文件扩展名称 */
define('EXT', '.' . pathinfo(__FILE__, PATHINFO_EXTENSION));
/** 系统常量 FRONTEND,入口文件名称 */
define('FRONTEND', pathinfo(__FILE__, PATHINFO_BASENAME));
/** 系统常量 htdocs,网站目录路径 */
define('DOCROOT', str_replace("\\", "/", realpath(dirname(__FILE__))) . '/');
/** 系统常量 RUNTIME,临时目录路径 */
define('RUNTIME', str_replace("\\", "/", realpath($runtime)) . '/');
/** 系统常量 QuickPHP 框架 framework 目录路径 */
define('SYSPATH', str_replace("\\", "/", realpath($framework)) . '/');
/** 系统常量 APPPATH 目录路径 */
define('APPPATH', str_replace("\\", "/", realpath($protected)) . '/');

// 调试时使用，生产模式删除改行
if(isset($_GET['phpinfo']) and !IN_PRODUCTION) die(phpinfo());

/** 载入框架,加载相关配置,进行调度,执行应用 */
require APPPATH . 'bootstrap' . EXT;
//http://www.phpunit.de/manual/current/en/installation.html