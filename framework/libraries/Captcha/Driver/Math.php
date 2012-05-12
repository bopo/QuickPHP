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
 * 验证码谜语样式驱动.
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Captcha
 * @version $Id: Math.php 8775 2012-01-16 07:21:44Z bopo $
 */
class QuickPHP_Captcha_Driver_Math extends Captcha_Abstract
{

    private $math_exercice;

    /**
     * 构建一个验证码提问串.
     *
     * @return  string  验证码提问字符串
     */
    public function generate_challenge()
    {
        if(Captcha::$config['complexity'] < 4)
        {
            $numbers[] = mt_rand(1, 5);
            $numbers[] = mt_rand(1, 4);
        }
        elseif(Captcha::$config['complexity'] < 7) // Normal
        {
            $numbers[] = mt_rand(10, 20);
            $numbers[] = mt_rand(1, 10);
        }
        else
        {
            $numbers[] = mt_rand(100, 200);
            $numbers[] = mt_rand(10, 20);
            $numbers[] = mt_rand(1, 10);
        }

        $this->math_exercice = implode(' + ', $numbers) . ' = ';
        return array_sum($numbers);
    }

    /**
     * 输出验证码谜语.
     *
     * @param   boolean  是否HTML输出
     * @return  mixed
     */
    public function render($html)
    {
        $this->update_response_session();
        return $this->math_exercice;
    }

}