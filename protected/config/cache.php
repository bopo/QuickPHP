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
 * @package  Cache
 *
 * Cache settings, defined as arrays, or "groups". If no group name is
 * used when loading the cache library, the group named "default" will be used.
 *
 * Each group can be used independently, and multiple groups can be used at once.
 *
 * Group Options:
 * driver   - Cache backend driver. QuickPHP comes with file, database, and memcache drivers.
 * > File cache is fast and reliable, but requires many filesystem lookups.
 * > Database cache can be used to cache items remotely, but is slower.
 * > Memcache is very high performance, but prevents cache tags from being used.
 *
 * params   - Driver parameters, specific to each driver.
 *
 * lifetime - Default lifetime of caches in seconds. By default caches are stored for
 * thirty minutes. Specific lifetime can also be set when creating a new cache.
 * Setting this to 0 will never automatically delete caches.
 *
 * requests - Average number of cache requests that will processed before all expired
 * caches are deleted. This is commonly referred to as "garbage collection".
 * Setting this to 0 or a negative number will disable automatic garbage collection.
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
        'PHP_AUTH_PW' => 'passwd'
    ),
    'memcache' => array(
        'servers' => array(
            array(
                'host' => '127.0.0.1',
                'port' => '11211',
                'persistent' => FALSE
            )
        ),
        'compression' => FALSE
    ),
    'sqlite' => array(
        'directory' => RUNTIME . '_cache',
        'compression' => FALSE,
        'schema' => 'CREATE TABLE caches ( id VARCHAR(127) PRIMARY KEY, expiration INTEGER, cache TEXT);'
    )
);