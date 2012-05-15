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
    'default'   => array(
        'type' => 'mysql',
        'connection' => array(
            'hostname'   => 'localhost',
            'database'   => 'divine',
            'username'   => 'root',
            'password'   => '',
            'persistent' => FALSE,
        ),
        'table_prefix' => '',
        'charset'      => 'UTF8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
    ),
    'sqlite'    => array(
        'type' => 'pdo',
        'connection' => array(
            'dsn'        => 'sqlite:astro.db',
            'username'   => 'root',
            'password'   => '',
            'persistent' => FALSE,
        ),
        'table_prefix' => '',
        'charset'      => 'UTF8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
    ),
    'postgre'   => array(
        'type' => 'postgre',
        'connection' => array(
            'hostname'   => 'localhost',
            'database'   => 'test',
            'username'   => 'bopo',
            'password'   => '87225300',
            'persistent' => FALSE,
        ),
        'table_prefix' => '',
        'charset'      => 'UTF8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
    ),
    'alternate' => array(
        'type' => 'pdo',
        'connection' => array
        (
            /**
             * The following options are available for PDO:
             *
             * string   dsn         Data Source Name
             * string   username    database username
             * string   password    database password
             * boolean  persistent  use persistent connections?
             */
            'dsn'        => 'mysql:host=localhost;dbname=mitang',
            'username'   => 'root',
            'password'   => '',
            'persistent' => FALSE,
        ),
        /**
         * The following extra options are available for PDO:
         *
         * string   identifier  set the escaping identifier
         */
        'table_prefix' => '',
        'charset'      => 'UTF8',
        'caching'      => FALSE,
        'profiling'    => TRUE,
    )
);
