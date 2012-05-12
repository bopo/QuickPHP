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
 | permissions AND limitations under the License.                       |
 +----------------------------------------------------------------------+
 | Author: BoPo <ibopo@126.com>                                         |
 +----------------------------------------------------------------------+
*/

/**
 * 验证码black样式驱动.
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Captcha
 * @version $Id: Black.php 8775 2012-01-16 07:21:44Z bopo $
 */
class QuickPHP_Captcha_Driver_Black extends Captcha_Abstract
{

    /**
     * 构建一个验证码提问串.
     *
     * @return  string  验证码提问字符串
     */
    public function generate_challenge()
    {
        return self::random('distinct', max(1, ceil(Captcha::$config['complexity'] / 1.5)));
    }

    /**
     * 输出验证码图像.
     *
     * @param   boolean  是否输出HTML元素
     * @return  mixed
     */
    public function render($html)
    {
        $this->update_response_session();
        $this->image_create(Captcha::$config['background']);

        $count = (Captcha::$config['width'] + Captcha::$config['height']) / 2;
        $count = $count / 5 * min(10, Captcha::$config['complexity']);

        for ($i = 0; $i < $count; $i++)
        {
            imagesetthickness($this->image, mt_rand(1, 2));
            $color = imagecolorallocatealpha($this->image, 255, 255, 255, mt_rand(0, 120));
            imagearc($this->image, mt_rand(- Captcha::$config['width'], Captcha::$config['width']), mt_rand(- Captcha::$config['height'], Captcha::$config['height']), mt_rand(- Captcha::$config['width'], Captcha::$config['width']), mt_rand(- Captcha::$config['height'], Captcha::$config['height']), mt_rand(0, 360), mt_rand(0, 360), $color);
        }

        $font   = Captcha::$config['fontpath'] . Captcha::$config['fonts'][array_rand(Captcha::$config['fonts'])];
        $size   = (int) min(Captcha::$config['height'] / 2, Captcha::$config['width'] * 0.8 / strlen($this->response));
        $angle  = mt_rand(- 15 + strlen($this->response), 15 - strlen($this->response));
        $x      = mt_rand(1, Captcha::$config['width'] * 0.9 - $size * strlen($this->response));
        $y      = ((Captcha::$config['height'] - $size) / 2) + $size;
        $color  = imagecolorallocate($this->image, 255, 255, 255);

        imagefttext($this->image, $size, $angle, $x + 1, $y + 1, $color, $font, $this->response);

        (Captcha::$config['complexity'] < 10) AND imagefttext($this->image, $size, $angle, $x - 1, $y - 1, $color, $font, $this->response);
        (Captcha::$config['complexity'] < 8) AND imagefttext($this->image, $size, $angle, $x - 2, $y + 2, $color, $font, $this->response);
        (Captcha::$config['complexity'] < 6) AND imagefttext($this->image, $size, $angle, $x + 2, $y - 2, $color, $font, $this->response);
        (Captcha::$config['complexity'] < 4) AND imagefttext($this->image, $size, $angle, $x + 3, $y + 3, $color, $font, $this->response);
        (Captcha::$config['complexity'] < 2) AND imagefttext($this->image, $size, $angle, $x - 3, $y - 3, $color, $font, $this->response);

        $color = imagecolorallocate($this->image, 0, 0, 0);
        imagefttext($this->image, $size, $angle, $x, $y, $color, $font, $this->response);

        return $this->image_render($html);
    }

}