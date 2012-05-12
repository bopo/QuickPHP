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
 * 首页(Home)
 *
 @category   Store
 @package    Store_Home
 @copyright  Copyright (c) 2010 http://quickphp.net All rights reserved.
 @license    http://framework.quickphp.net/licenses/LICENSE-2.0
 @version    $Id: passport.php 8773 2012-01-16 06:25:20Z bopo $
 */
abstract class Abstract_Passport_Controller extends Abstract_Frontend_Controller
{
    const ALLOW_PRODUCTION = TRUE;

    public $directory = '';

    public function before()
    {
    }
}