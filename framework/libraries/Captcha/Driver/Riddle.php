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
 * 验证码问答样式驱动.
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Captcha
 * @version $Id: Riddle.php 8775 2012-01-16 07:21:44Z bopo $
 */
class QuickPHP_Captcha_Driver_Riddle extends Captcha_Abstract
{

    private $riddle;

    /**
     * 构建一个验证码提问串.
     *
     * @return  string  验证码提问字符串
     */
    public function generate_challenge()
    {
        // 选择不同的谜语
        $riddles = array(
            array('请问你是否讨厌垃圾留言（SPAM）吗？（是或否）', '是'),
            array('你是机器人吗？（是或否）', '否'),
            array('火是... （热的 还是 冷的）', '热'),
            array('秋季之后是什么季节？', '冬季'),
            array('今天是这周的哪一天?', strftime('%A')),
            array('现在是几月份？', strftime('%B')));

        $riddle       = $riddles[array_rand($riddles)];
        $this->riddle = $riddle[0];

        return $riddle[1];
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
        return $this->riddle;
    }

}