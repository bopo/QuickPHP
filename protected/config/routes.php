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
    // default
    '_default'                    => 'home',

    // frontend
    'tagcloud'                    => 'browse/tagcloud',
    'tagged/(.*?)'                => 'browse/tagged/$1',
    'newest'                      => 'browse/newest',
    'recommend'                   => 'browse/recommend',
    'content/(\w+)'               => 'content_$1',
    'content/(\w+)/(\w+)'         => 'content_$1/$2',

    // console
    'console'                     => 'console_dashboard',
    'console/(\w+)'               => 'console_$1',
    'console/(\w+)/(\w+)'         => 'console_$1_$2',

    // user
    'user'                        => 'user_home',
    'user/divine'                 => 'user_divine',
    'user/divine/(\w+)'           => 'user_divine_$1',
    'user/divine/(\w+)/(\w+)'     => 'user_divine_$1/$2',
    'user/(\w+)'                  => 'user_$1',
    'user/(\w+)/(\w+)'            => 'user_$1/$2',
    'user/(\w+)/(\w+)/(\w+)'      => 'user_$1/$2/$3',

    // passport
    'signup'                      => 'passport_signup',
    'signup/(\w+)'                => 'passport_signup/$1',
    'login'                       => 'passport_login',
    'login/(\w+)'                 => 'passport_login/$1',
    'logout'                      => 'passport_logout',
    'forget'                      => 'passport_forget',
    'forget/(\w+)'                => 'passport_forget/$1',

    // connect
    'connect/(\w+)'               => 'passport_connect/$1',
    'connect/callback/(\w+)'      => 'passport_connect/callback/$1',

    // sevice
    'static/(\w+)/(\w+)'          => 'service_static/$1/$2',
    'service/(\w+)'               => 'service_$1',
    'service/(\w+)/(\w+)'         => 'service_$1/$2',
    'service/oauth/request_token' => 'service_oauth_request_token',
    'service/oauth/authorize'     => 'service_oauth_authorize',
    'service/oauth/authenticate'  => 'service_oauth_authenticate',
    'service/oauth/access_token'  => 'service_oauth_access_token',
);