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
    'simple' => array(
        'layout_dir'       => APPPATH . 'views/layout/',
        'template_dir'     => APPPATH . 'views/template/',
        'template_suffix'  => '.html',
        'compile_dir'      => RUNTIME . '_views/',
        'cache_dir'        => RUNTIME . '_views/',
        'compile_lifetime' => 0, //3600 * 24 * 30,
        'left_delimiter'   => "{{",
        'right_delimiter'  => "}}"
    ),
);