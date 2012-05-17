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
$framework  = dirname(__FILE__) . "/../framework";
/** protected 目录路径 */
$protected  = dirname(__FILE__) . "/../protected";
/** runtime 目录路径 */
$runtime    = dirname(__FILE__) . "/../runtime";

/** 系统常量 IN_PRODUCTION,产品模式开关，如果设置成 FALSE 则为开发模式 */
define('IN_PRODUCTION', false);
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

/** 载入框架,加载相关配置,进行调度,执行应用 */
set_include_path(SYSPATH . PATH_SEPARATOR . get_include_path());

version_compare(PHP_VERSION, '5.2', '<') and exit('QuickPHP requires PHP 5.2 or newer.');
version_compare(PHP_VERSION, '5.3', '<') and set_magic_quotes_runtime(0);

date_default_timezone_set('Asia/Shanghai'); // 设置默认时区
setlocale(LC_ALL, 'zh_CN.utf-8');           // 设置默认编码

require_once ('QuickPHP.php');

spl_autoload_register(array('QuickPHP', 'autoloader'));
ini_set('unserialize_callback_func', 'spl_autoload_call');

$settings = array(
	'profiling'  => true,                    // 开启分析器
	'log_error'  => true,                    // 开启log分析
	'errors'     => true,                    // 开启错误分析
	'caching'    => true,                    // 开启高速缓存
	'frontend'   => '',             // 入口文件名(默认为index.php)
	'url_suffix' => 'html',
	'domain'     => '/quickphp/',      // 网站域名
);

QuickPHP::instance($settings);

require_once 'PHPUnit/Framework.php';