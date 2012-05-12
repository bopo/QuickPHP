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
return array (
    // Generally will be localhost if you're querying from the machine that Mongo is installed on
    'mongo_host' => 'localhost',
    // Generally will be 27017 unless you've configured Mongo otherwise
    'mongo_port' => 27017,
    // The database you want to work from (required)
    'mongo_db' => '',
    // Leave blank if Mongo is not running in auth mode
    'mongo_user' => '',
    'mongo_pass' => '',
    // Persistant connections
    'mongo_persist' => true,
    'mongo_persist_key' => '__mongo_persist',
    // Get results as an object instead of an array
    'mongo_return' => 'array',
    // When you run an insert/update/delete how sure do you want to be that the database has received the query?
    // safe = the database has receieved and executed the query
    // fysnc = as above + the change has been committed to harddisk <- NOTE: will introduce a performance penalty
    'mongo_query_safety' => 'safe',
    // Supress connection error password display
    'mongo_supress_connect_error' => true,
    // If you are having problems connecting try changing this to TRUE
    'host_db_flag' => false,
);