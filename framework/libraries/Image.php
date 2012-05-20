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
 * 图像处理类
 *
 * @category    QuickPHP
 * @package     Image
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Image.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_Image
{
    // 缩放控制尺寸
    const NONE       = 1;    // 不自适应比例
    const AUTO       = 2;    // 自动适应比例
    const HEIGHT     = 3;    // 按高度自适应比例
    const WIDTH      = 4;    // 按宽度自适应比例
    const SPHERE     = 5;    // 按宽度自适应比例

    // 翻转方向
    const HORIZONTAL = 6;    // 水平方向
    const VERTICAL   = 7;    // 垂直方向

    // 图形允许格式类型
    public static $allowed_types = array
    (
        IMAGETYPE_GIF     => 'gif',
        IMAGETYPE_JPEG    => 'jpg',
        IMAGETYPE_PNG     => 'png',
        IMAGETYPE_TIFF_II => 'tiff',
        IMAGETYPE_TIFF_MM => 'tiff',
    );

    // 图形驱动实例
    protected $driver;

    // 驱动行为
    protected $actions = array();

    // 当前图像参考文件名
    protected $image   = '';

    /**
     * 创建一个图形实例，并返回这个图形实例.
     *
     * @param   string   图形文件名
     * @param   array    非默认配置参数
     * @return  object
     */
    public static function factory($image, $config = null)
    {
        return new Image($image, $config);
    }

    /**
     * 创建一个新的图像编辑器的实例
     *
     * @param   string   filename of image
     * @param   array    non-default configurations
     * @return  void
     */
    public function __construct($image, $config = null)
    {
        static $check;

        ($check === null) and $check = function_exists('getimagesize');

        if ($check === false)
        {
            throw new Image_Exception('getimagesize_missing');
        }

        // 检查输入的图像文件是否存在
        if ( ! is_file($image))
        {
            throw new Image_Exception('file_not_found', $image);
        }

        $ER         = error_reporting(0);
        $image_info = getimagesize($image);
        error_reporting($ER);

        // 确保形象可读和有效的
        if ( ! is_array($image_info) OR count($image_info) < 3)
        {
            throw new Image_Exception('file_unreadable', array($image));
        }

        // 检查确认图像类型是被允许的
        if ( ! isset(Image::$allowed_types[$image_info[2]]))
        {
            throw new Image_Exception('type_not_allowed', array($image));
        }

        // 的形象已经被确认,负荷
        $this->image = array(
            'file'   => str_replace('\\', '/', realpath($image)),
            'width'  => $image_info[0],
            'height' => $image_info[1],
            'type'   => $image_info[2],
            'ext'    => Image::$allowed_types[$image_info[2]],
            'mime'   => $image_info['mime']
        );

        if(is_array($config))
        {
            $this->config = $config;
        }
        elseif(!empty($config) && is_string($config))
        {
            $this->config = QuickPHP::config('image')->get($config, array());
        }
        else
        {
            $this->config = QuickPHP::config('image')->get('default', array());
        }

        $driver = "Image_Driver_".ucfirst($this->config['driver']);

        $this->driver = new $driver($this->config['params']);

        if ( ! ($this->driver Instanceof Image_Abstract))
        {
            throw new QuickPHP_Exception('driver_implements', array($this->config['driver'], get_class($this), 'Image_Abstract'));
        }

        return ;
    }

    /**
     * 检索的图形属性
     *
     * @param   string  property 属性名
     * @return  mixed
     */
    public function __get($property)
    {
        if (isset($this->image[$property]))
        {
            return $this->image[$property];
        }

        throw new Image_Exception('invalid_property', array($property, get_class($this)));
    }

    /**
     * 缩放图片
     *
     * @param   integer  宽度
     * @param   integer  高度
     * @param   integer  缩放自适应比例控制: Image::NONE, Image::AUTO, Image::WIDTH, Image::HEIGHT, Image::SPHERE
     * @return  object
     */
    public function resize($width, $height, $master = null)
    {
        if ( ! $this->valid_size('width', $width))
        {
            throw new Image_Exception('invalid_width', array($width));
        }

        if ( ! $this->valid_size('height', $height))
        {
            throw new Image_Exception('invalid_height', array($height));
        }

        if (empty($width) AND empty($height))
        {
            throw new Image_Exception('invalid_dimensions', array(__FUNCTION__));
        }

        if ($master === null)
        {
            $master = Image::AUTO;
        }
        elseif ( ! $this->valid_size('master', $master))
        {
            throw new Image_Exception('invalid_master');
        }

        $this->actions['resize'] = array(
            'width'  => $width,
            'height' => $height,
            'master' => $master,
        );

        return $this;
    }

    /**
     * 剪切图片
     *
     * @throws  Image_Exception
     * @param   integer  width 宽度
     * @param   integer  height 高度
     * @param   integer  top offset, pixel value or one of: top, center, bottom
     * @param   integer  left offset, pixel value or one of: left, center, right
     * @return  object
     */
    public function crop($width, $height, $left = 'center', $top = 'center')
    {
        if ( ! $this->valid_size('width', $width))
        {
            throw new Image_Exception('invalid_width', array($width));
        }

        if ( ! $this->valid_size('height', $height))
        {
            throw new Image_Exception('invalid_height', array($height));
        }

        if ( ! $this->valid_size('top', $top))
        {
            throw new Image_Exception('invalid_top', array($top));
        }

        if ( ! $this->valid_size('left', $left))
        {
            throw new Image_Exception('invalid_left', array($left));
        }

        if (empty($width) AND empty($height))
        {
            throw new Image_Exception('invalid_dimensions', array(__FUNCTION__));
        }

        $this->actions['crop'] = array(
            'width'  => $width,
            'height' => $height,
            'top'    => $top,
            'left'   => $left,
        );

        return $this;
    }

    /**
     * 旋转图片180度以内，允许图象旋转180度顺时针或台面顺时针
     *
     * @param   integer  degrees 角度
     * @return  object
     */
    public function rotate($degrees)
    {
        $degrees = (int) $degrees;

        if ($degrees > 180)
        {
            do
            {
                $degrees -= 360;
            }
            while($degrees > 180);
        }

        if ($degrees < -180)
        {
            do
            {
                $degrees += 360;
            }
            while($degrees < -180);
        }

        $this->actions['rotate'] = $degrees;
        return $this;
    }

    /**
     * 水平或垂直翻转图像。
     *
     * @throws  Image_Exception
     * @param   integer  翻转方向
     * @return  object
     */
    public function flip($direction)
    {
        if ($direction !== Image::HORIZONTAL AND $direction !== Image::VERTICAL)
        {
            throw new Image_Exception('invalid_flip');
        }

        $this->actions['flip'] = $direction;
        return $this;
    }

    /**
     * 修改图片显示品质，修改图形质量百分比(1-100%)。
     *
     * @param   integer  质量百分比
     * @return  object
     */
    public function quality($amount)
    {
        $this->actions['quality'] = max(1, min($amount, 100));
        return $this;
    }

    /**
     * 图形锐化操作
     *
     * @param   integer  锐化值，通常20就很理想
     * @return  object
     */
    public function sharpen($amount)
    {
        $this->actions['sharpen'] = max(1, min($amount, 100));
        return $this;
    }

    /**
     * 将当前操作图形流保存到一个新文件
     *
     * @throws  Image_Exception
     * @param   string   新图形文件名
     * @param   integer  新图形文件权限
     * @param   boolean  是否保留旧文件
     * @return  object
     */
    public function save($new_image = false, $chmod = 0644, $keep_actions = false)
    {
        empty($new_image) and $new_image = $this->image['file'];

        $dir  = pathinfo($new_image, PATHINFO_DIRNAME);
        $file = pathinfo($new_image, PATHINFO_BASENAME);
        $dir  = str_replace('\\', '/', realpath($dir)).'/';

        if ( ! is_writable($dir))
        {
            throw new Image_Exception('directory_unwritable', array($dir));
        }

        if ($status = $this->driver->process($this->image, $this->actions, $dir, $file))
        {
            if ($chmod !== false)
            {
                chmod($new_image, $chmod);
            }
        }

        if ($keep_actions === false)
        {
            $this->actions = array();
        }

        return $status;
    }

    /**
     * 输出当前图像流到浏览器显示
     *
     * @param   boolean  是否显示html代码
     * @return  object
     */
    public function render($keep_actions = false)
    {
        $new_image = $this->image['file'];

        $dir    = pathinfo($new_image, PATHINFO_DIRNAME);
        $dir    = str_replace('\\', '/', realpath($dir)) . '/';
        $file   = pathinfo($new_image, PATHINFO_BASENAME);
        $status = $this->driver->process($this->image, $this->actions, $dir, $file, true);

        if ($keep_actions === false)
        {
            $this->actions = array();
        }

        return $status;
    }

    /**
     * 过滤指定属性值类型。
     *
     * @param   string   属性类型
     * @param   mixed    属性值
     * @return  boolean
     */
    protected function valid_size($type, & $value)
    {
        if (is_null($value))
        {
            return true;
        }

        if ( ! is_scalar($value))
        {
            return false;
        }

        switch ($type)
        {
            case 'width':
            case 'height':
                if (is_string($value) AND ! ctype_digit($value))
                {
                    if ( ! preg_match('/^[0-9]++%$/D', $value))
                    {
                        return false;
                    }
                }
                else
                {
                    $value = (int) $value;
                }
            break;

            case 'top':
                if (is_string($value) AND ! ctype_digit($value))
                {
                    if ( ! in_array($value, array('top', 'bottom', 'center')))
                    {
                        return false;
                    }
                }
                else
                {
                    $value = (int) $value;
                }
            break;

            case 'left':
                if (is_string($value) AND ! ctype_digit($value))
                {
                    if ( ! in_array($value, array('left', 'right', 'center')))
                    {
                        return false;
                    }
                }
                else
                {
                    $value = (int) $value;
                }
            break;

            case 'master':
                if ($value !== Image::NONE AND $value !== Image::AUTO AND $value !== Image::WIDTH AND $value !== Image::HEIGHT AND $value !== Image::SPHERE)
                {
                    return false;
                }
            break;
        }

        return true;
    }
}
