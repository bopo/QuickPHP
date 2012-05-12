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
 * 验证码Word样式驱动.
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Captcha
 * @version $Id: Word.php 8775 2012-01-16 07:21:44Z bopo $
 */
class QuickPHP_Captcha_Driver_Word extends Captcha_Driver_Basic
{

    /**
     * 构建一个验证码提问串.
     *
     * @return  string  验证码提问字符串
     */
    public function generate_challenge()
    {
        $words = array('cd', 'tv', 'it', 'to', 'be', 'or', 'sun', 'car', 'dog', 'bed', 'kid', 'egg', 'bike', 'tree', 'bath', 'roof', 'road', 'hair', 'hello', 'world', 'earth', 'beard', 'chess', 'water', 'barber', 'bakery', 'banana', 'market', 'purple', 'writer', 'america', 'release', 'playing', 'working', 'foreign', 'general', 'aircraft', 'computer', 'laughter', 'alphabet', 'kangaroo', 'spelling', 'architect', 'president', 'cockroach', 'encounter', 'terrorism', 'cylinders');

        shuffle($words);

        foreach ($words as $word)
        {
            if(abs(Captcha::$config['complexity'] - strlen($word)) < 2)
            {
                return strtoupper($word);
            }
        }

        return strtoupper($words[array_rand($words)]);
    }

}