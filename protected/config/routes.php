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
    '_default'              => 'home',

    // console
    'console'               => 'console_dashboard',
    'console/(\w+)'         => 'console_$1',
    'console/(\w+)/(\w+)'   => 'console_$1_$2',

    // account
    'user'                      => 'user_home',
    'user/divine'               => 'user_divine',
    'user/divine/(\w+)'         => 'user_divine_$1',
    'user/divine/(\w+)/(\w+)'   => 'user_divine_$1/$2',
    'user/(\w+)'                => 'user_$1',
    'user/(\w+)/(\w+)'          => 'user_$1/$2',
    'user/(\w+)/(\w+)/(\w+)'    => 'user_$1/$2/$3',

    // passport
    'signup'                => 'passport_signup',
    'signup/(\w+)'          => 'passport_signup/$1',
    'login'                 => 'passport_login',
    'login/(\w+)'           => 'passport_login/$1',
    'logout'                => 'passport_logout',
    'forget'                => 'passport_forget',
    'forget/(\w+)'          => 'passport_forget/$1',

    // sevice
    'service/(\w+)'         => 'service_$1',
    'service/(\w+)/(\w+)'   => 'service_$1/$2',
    'connect'               => 'service_connect',
    'shortcut'              => 'service_shortcut',
);