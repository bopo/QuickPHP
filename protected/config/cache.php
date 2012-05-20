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
 +----------------------------------------------------------------------+ | Author: BoPo <ibopo@126.com>                                         |
 +----------------------------------------------------------------------+
*/
return array(
    'default' => array(
        'lifetime' => 1800,
        'requests' => 1000
    ),
    'file' => array(
        'directory' => RUNTIME . '_cache'
    ),
    'xcache' => array(
        'PHP_AUTH_USER' => 'xcache',
        'PHP_AUTH_PW'   => 'passwd'
    ),
    'memcache' => array(
        'servers' => array(
            array(
                'host'       => '127.0.0.1',
                'port'       => '11211',
                'persistent' => FALSE
            )
        ),
        'compression' => FALSE
    ),
    'sqlite' => array(
        'directory'   => RUNTIME . '_cache',
        'compression' => FALSE,
        'schema'      => 'CREATE TABLE caches ( id VARCHAR(127) PRIMARY KEY, expiration INTEGER, cache TEXT);'
    )
);