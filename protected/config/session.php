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
 * 文件：Session
 *
 * 选项：
 * driver         - Session 驱动名：'cookie'，'database'，'native' 或 'cache'
 * storage        - Session 使用驱动（数据库或缓存）的储存参数
 * name           - 默认 Session 名（仅字母，数字和下划线）
 * validate       - 设置 Session 参数到变量（user_agent，ip_address）
 * encryption     - 密钥，设置 FALSE 关闭 session 加密
 * expiration     - 每个 Session 维持的生命周期（秒）（设置为 0 表示直到退出浏览器才终止 Session）
 * regenerate     - 一些网页载入前 Session 更新（设置为 0 标志关闭自动更新）
 * gc_probability - 百分比概率表示垃圾收集将被收集
 */
return array
(
    'default' => array(
        'driver'         => 'native',
        'storage'        => '',
        'name'           => 'session_data',
        'validate'       => array('user_agent','ip_address'),
        'encryption'     => false,
        'expiration'     => 7200,
        'regenerate'     => 0,
        'gc_probability' => 2
    ),
    'database' => array(
        'driver'         => 'database',
        'storage'        => '',
        'name'           => 'session_data',
        'validate'       => array('user_agent','ip_address'),
        'encryption'     => false,
        'expiration'     => 7200,
        'regenerate'     => 0,
        'gc_probability' => 2
    )
);