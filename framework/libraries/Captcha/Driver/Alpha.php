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
 * 验证码希腊字母样式驱动.
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Captcha
 * @version $Id: Alpha.php 8775 2012-01-16 07:21:44Z bopo $
 */
class QuickPHP_Captcha_Driver_Alpha extends Captcha_Abstract
{

    /**
     * 构建一个验证码提问串.
     *
     * @return  string  验证码提问字符串
     */
    public function generate_challenge()
    {
        return text::random('distinct', max(1, Captcha::$config['complexity']));
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

        if(empty(Captcha::$config['background']))
        {
            $color1 = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
            $color2 = imagecolorallocate($this->image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
            $this->image_gradient($color1, $color2);
        }

        for ($i = 0, $count = mt_rand(10, Captcha::$config['complexity'] * 3); $i < $count; $i++)
        {
            $color  = imagecolorallocatealpha($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), mt_rand(80, 120));
            $size   = mt_rand(5, Captcha::$config['height'] / 3);

            imagefilledellipse($this->image, mt_rand(0, Captcha::$config['width']), mt_rand(0, Captcha::$config['height']), $size, $size, $color);
        }

        $default_size = min(Captcha::$config['width'], Captcha::$config['height'] * 2) / strlen($this->response);
        $spacing      = (int) (Captcha::$config['width'] * 0.9 / strlen($this->response));
        $color_limit  = mt_rand(96, 160);
        $chars        = 'ABEFGJKLPQRTVY';

        for ($i = 0, $strlen = strlen($this->response); $i < $strlen; $i++)
        {
            $font  = Captcha::$config['fontpath'] . Captcha::$config['fonts'][array_rand(Captcha::$config['fonts'])];
            $angle = mt_rand(- 40, 20);
            
            $size  = $default_size / 10 * mt_rand(8, 12);
            $box   = imageftbbox($size, $angle, $font, $this->response[$i]);
            
            $x     = $spacing / 4 + $i * $spacing;
            $y     = Captcha::$config['height'] / 2 + ($box[2] - $box[5]) / 4;
            
            $color = imagecolorallocate($this->image, mt_rand(150, 255), mt_rand(200, 255), mt_rand(0, 255));

            imagefttext($this->image, $size, $angle, $x, $y, $color, $font, $this->response[$i]);

            $text_color = imagecolorallocatealpha($this->image, mt_rand($color_limit + 8, 255), mt_rand($color_limit + 8, 255), mt_rand($color_limit + 8, 255), mt_rand(70, 120));
            $char       = $chars[mt_rand(0, 14)];

            imagettftext($this->image, $size * 2, mt_rand(- 45, 45), ($x - (mt_rand(5, 10))), ($y + (mt_rand(5, 10))), $text_color, $font, $char);
        }

        return $this->image_render($html);
    }
}