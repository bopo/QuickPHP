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
 * QuickPHP 图像驱动抽象类.
 *
 * $Id: Abstract.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Image
 */
abstract class QuickPHP_Image_Abstract
{
    // Reference to the current image
    protected $image;

    // Reference to the temporary processing image
    protected $tmp_image;

    // Processing errors
    protected $errors = array();

    /**
     * 执行动作的集合,定义成双成对。
     *
     * @param   array    actions
     * @return  boolean
     */
    public function execute($actions)
    {
        foreach ($actions as $func => $args)
        {
            if (method_exists($this, $func))
            {
                if ( ! $this->$func($args))
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Sanitize and normalize a geometry array based on the temporary image
     * width and height. Valid properties are: width, height, top, left.
     *
     * @param   array  geometry properties
     * @return  void
     */
    protected function sanitize_geometry( $geometry)
    {
        list($width, $height) = $this->properties();

        $RE = error_reporting(0);

        $geometry['width']  = min($geometry['width'], $width);
        $geometry['height'] = min($geometry['height'], $height);

        if ($geometry['top'] === 'center')
        {
            $geometry['top'] = floor(($height / 2) - ($geometry['height'] / 2));
        }
        elseif ($geometry['top'] === 'top')
        {
            $geometry['top'] = 0;
        }
        elseif ($geometry['top'] === 'bottom')
        {
            $geometry['top'] = $height - $geometry['height'];
        }

        if ($geometry['left'] === 'center')
        {
            $geometry['left'] = floor(($width / 2) - ($geometry['width'] / 2));
        }
        elseif ($geometry['left'] === 'left')
        {
            $geometry['left'] = 0;
        }
        elseif ($geometry['left'] === 'right')
        {
            $geometry['left'] = $width - $geometry['height'];
        }

        error_reporting($RE);
        return $geometry;
    }

    /**
     * Return the current width and height of the temporary image. This is mainly
     * needed for sanitizing the geometry.
     *
     * @return  array  width, height
     */
    abstract protected function properties();

    /**
     * 过程图像在动作的集合。
     *
     * @param   string   image filename
     * @param   array    actions to execute
     * @param   string   destination directory path
     * @param   string   destination filename
     * @return  boolean
     */
    abstract public function process($image, $actions, $dir, $file);

    /**
     * 翻转(镜像)图形. Valid directions are horizontal and vertical.
     *
     * @param   integer   direction to flip
     * @return  boolean
     */
    abstract function flip($direction);

    /**
     * 剪切图形. Valid properties are: width, height, top, left.
     *
     * @param   array     new properties
     * @return  boolean
     */
    abstract function crop($properties);

    /**
     * 缩放图形. Valid properties are: width, height, and master.
     *
     * @param   array     new properties
     * @return  boolean
     */
    abstract public function resize($properties);

    /**
     * 旋转图形. Valid amounts are -180 to 180.
     *
     * @param   integer   amount to rotate
     * @return  boolean
     */
    abstract public function rotate($amount);

    /**
     * 锐化图形. Valid amounts are 1 to 100.
     *
     * @param   integer  amount to sharpen
     * @return  boolean
     */
    abstract public function sharpen($amount);

    /**
     * 水印操作.
     *
     * @param   integer
     * @return  boolean
     */
    abstract public function watermark($options = array());
}