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
 * QuickPHP 命令行助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  cli
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: cli.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_cli
{
    /**
     * 返回一个或多个命令行选项。选项指定使用
     * 标准命令行语法:
     *
     * php index.php --username="john.smith" --password="secret"
     *
     * // 获取"username"和 "password"的值
     * $auth = cli::options('username', 'password');
     *
     * @param   string  选项名称
     * @param   ...
     * @return  array
     */
    public static function options($options = null)
    {
        $options = func_get_args();
        $values  = array();

        if(empty($options))
        {
            return null;
        }

        for ($i = 1; $i < $_SERVER['argc']; $i++)
        {
            if( ! isset($_SERVER['argv'][$i]))
            {
                break;
            }

            $opt = $_SERVER['argv'][$i];

            if(substr($opt, 0, 2) !== '--')
            {
                continue;
            }

            $opt = substr($opt, 2);

            if(strpos($opt, '='))
            {
                list($opt, $value) = explode('=', $opt, 2);
            }
            else
            {
                $value = null;
            }

            if(in_array($opt, $options))
            {
                $values[$opt] = $value;
            }
        }

        return $values;
    }

    public static function option($option = null)
    {
        $option = cli::options($option);
        return current($option);
    }
}