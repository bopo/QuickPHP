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
 * 验证码驱动抽象类.
 *
 * @category    QuickPHP
 * @package     Captcha
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Abstract.php 8320 2011-10-05 14:59:55Z bopo $
 */
abstract class QuickPHP_Captcha_Abstract
{
    // 正确的确认码挑战的提示
    protected $response;

    // 图像资源标识符和类型("png"、"gif"或"jpeg")
    protected $image;

    protected $image_type = 'png';

    /**
     * 构造函数,构建一个新验证码提示
     *
     * @return  void
     */
    public function __construct()
    {
        $this->response = $this->generate_challenge();
    }

    /**
     * 构建一个新验证码问题
     *
     * @return  string  the challenge answer
     */
    abstract public function generate_challenge();

    /**
     * 输出验证码提示。
     *
     * @param   boolean  是否输出HTML
     * @return  mixed    渲染验证码输出 (图形或者谜语)
     */
    abstract public function render($html);

    /**
     * 目前储存响应验证码挑战在一节课上,所以它可在下一页负荷,为Captcha:正确的。()。
     * 这种方法被称作后在system.post_controller控制器执行(事件)为了不覆盖本身得太快了。
     * 更新问题到session中
     *
     * @return  void
     */
    public function update_response_session()
    {
        Session::instance()->set('captcha_response', sha1(strtoupper($this->response)));
    }

    /**
     * 验证是否符合验证码
     *
     * @param   string   用户输入验证码提示
     * @return  boolean
     */
    public function valid($response)
    {
        return (bool) (sha1(strtoupper($response)) === Session::instance()->get('captcha_response'));
    }

    /**
     * 判断图像类型
     *
     * @param   string        文件名
     * @return  string|false  图像类型 ("png", "gif" or "jpeg")
     */
    public function image_type($filename)
    {
        switch (strtolower(substr(strrchr($filename, '.'), 1)))
        {
            case 'png'  : 
                return 'png';
                break;
            case 'gif'  : 
                return 'gif';
                break;
            case 'jpg'  :
            case 'jpeg' : 
                return 'jpeg';
                break;
            default     : 
                return false;
                break;
        }
    }

    /**
     * 创建一个图像资源的维度中指定配置。
     * 如果一个背景图像,图像尺寸提供使用。
     *
     * @param   string  背景图像文件路径
     * @return  void
     */
    public function image_create($background = NULL)
    {
        if( ! function_exists('imagegd2'))
        {
            throw new QuickPHP_Captcha_Exception('requires_GD2');
        }

        $this->image = imagecreatetruecolor(Captcha::$config['width'], Captcha::$config['height']);

        if( ! empty($background))
        {
            $function = 'imagecreatefrom' . $this->image_type($background);
            $this->background_image = $function($background);

            if(imagesx($this->background_image) !== Captcha::$config['width'] or imagesy($this->background_image) !== Captcha::$config['height'])
            {
                imagecopyresampled($this->image, $this->background_image, 0, 0, 0, 0, Captcha::$config['width'], Captcha::$config['height'], imagesx($this->background_image), imagesy($this->background_image));
            }

            imagedestroy($this->background_image);
        }
    }

    /**
     * 充满具有渐变色填充。
     *
     * @param   resource  gd image color identifier for start color|GD图像颜色标识符为起动的颜色
     * @param   resource  gd image color identifier for end color|GD图像颜色标识符为最终的颜色
     * @param   string    direction: 'horizontal' or 'vertical', 'random' by default|方向:“水平”或“垂直的”、“随机的默认
     * @return  void
     */
    public function image_gradient($color1, $color2, $direction = NULL)
    {
        $directions = array('horizontal', 'vertical');

        if( ! in_array($direction, $directions))
        {
            $direction = $directions[array_rand($directions)];

            if(mt_rand(0, 1) === 1)
            {
                $temp = $color1;
                $color1 = $color2;
                $color2 = $temp;
            }
        }

        $color1 = imagecolorsforindex($this->image, $color1);
        $color2 = imagecolorsforindex($this->image, $color2);
        $steps  = ($direction === 'horizontal') ? Captcha::$config['width'] : Captcha::$config['height'];
        
        $r1     = ($color1['red'] - $color2['red']) / $steps;
        $g1     = ($color1['green'] - $color2['green']) / $steps;
        $b1     = ($color1['blue'] - $color2['blue']) / $steps;
        $i      = null;

        if($direction === 'horizontal')
        {
            $x1 = & $i;
            $y1 = 0;
            $x2 = & $i;
            $y2 = Captcha::$config['height'];
        }
        else
        {
            $x1 = 0;
            $y1 = & $i;
            $x2 = Captcha::$config['width'];
            $y2 = & $i;
        }

        for ($i = 0; $i <= $steps; $i++)
        {
            $r2 = $color1['red'] - floor($i * $r1);
            $g2 = $color1['green'] - floor($i * $g1);
            $b2 = $color1['blue'] - floor($i * $b1);
            $color = imagecolorallocate($this->image, $r2, $g2, $b2);
            imageline($this->image, $x1, $y1, $x2, $y2, $color);
        }
    }

    /**
     * 返回HTML元素和输出的形象浏览器。
     *
     * @param   boolean  是否输出HTML元素
     * @return  mixed    HTML或者无返回
     */
    public function image_render($html)
    {
        if($html)
        {
            return '<img alt="Captcha" src="' . url::bind('captcha/' . Captcha::$config['group']) . '" width="' . Captcha::$config['width'] . '" height="' . Captcha::$config['height'] . '" />';
        }

        ob_clean();
        
        header('Content-Type: image/' . $this->image_type);

        $function = 'image' . $this->image_type;
        $function($this->image);

        imagedestroy($this->image);
    }

    /**
     * 按指定的类型及长度产生一个随机字符串。
     *
     * @param   string   a type of pool, or a string of characters to use as the pool|一种池、或一个字符串作为游泳池
     * @param   integer  length of string to return 字符串长度回来了
     * @return  string
     *
     * @tutorial  alnum     alpha-numeric characters|alpha-numeric人物
     * @tutorial  alpha     alphabetical characters|字母字符
     * @tutorial  hexdec    hexadecimal characters, 0-9 plus a-f|十六进制特点、0 - 9 + a-f
     * @tutorial  numeric   digit characters, 0-9|0 - 9数字字符,
     * @tutorial  nozero    digit characters, 1-9|1 - 9数字字符,
     * @tutorial  distinct  clearly distinct alpha-numeric characters|alpha-numeric角色分明
     */
    // public static function random($type = 'alnum', $length = 8)
    // {
    //     $utf8 = FALSE;

    //     switch ($type)
    //     {
    //         case 'alnum' :
    //             $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //             break;
    //         case 'alpha' :
    //             $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //             break;
    //         case 'hexdec' :
    //             $pool = '0123456789abcdef';
    //             break;
    //         case 'numeric' :
    //             $pool = '0123456789';
    //             break;
    //         case 'nozero' :
    //             $pool = '123456789';
    //             break;
    //         case 'distinct' :
    //             $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
    //             break;
    //         default :
    //             $pool = (string) $type;
    //             $utf8 = ! Unicode::is_ascii($pool);
    //             break;
    //     }

    //     $pool = ($utf8 === TRUE) ? Unicode::str_split($pool, 1) : str_split($pool, 1);
    //     $max  = count($pool) - 1;
    //     $str  = '';

    //     for ($i = 0; $i < $length; $i++)
    //     {
    //         $str .= $pool[mt_rand(0, $max)];
    //     }

    //     if($type === 'alnum' and $length > 1)
    //     {
    //         if(ctype_alpha($str))
    //         {
    //             $str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
    //         }
    //         elseif(ctype_digit($str))
    //         {
    //             $str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
    //         }
    //     }

    //     return $str;
    // }
}