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
 * QucikPHP 图形处理 GD 驱动.
 *
 * $Id: GD.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Image
 */
class QuickPHP_Image_Driver_Gd extends Image_Abstract
{
    protected static $params;
    protected static $blank_png;
    protected static $blank_png_width;
    protected static $blank_png_height;

    public function __construct($params = array())
    {
        self::$params = $params;

        if ( ! function_exists('gd_info'))
        {
            throw new Image_Exception('gd_requires_v2');
        }

        $info = gd_info();

        if (strpos($info['GD Version'], '2.') === FALSE)
        {
            throw new Image_Exception('gd_requires_v2');
        }
    }

    protected function create($file, $type)
    {
        switch ($type)
        {
            case IMAGETYPE_JPEG:
                $create = 'imagecreatefromjpeg';
            break;
            
            case IMAGETYPE_GIF:
                $create = 'imagecreatefromgif';
            break;

            case IMAGETYPE_PNG:
                $create = 'imagecreatefrompng';
            break;
        }

        if (empty($create) OR ! function_exists($create))
        {
            throw new Image_Exception('image.type_not_allowed', $file);
        }

        return $create($file);
    }

    public function process($image, $actions, $dir, $file, $render = FALSE)
    {
        switch (strtolower(substr(strrchr($file, '.'), 1)))
        {
            case 'jpg':
            case 'jpeg':
                $save = 'imagejpeg';
            break;
            
            case 'gif':
                $save = 'imagegif';
            break;

            case 'png':
                $save = 'imagepng';
            break;
        }

        if (empty($save) OR ! function_exists($save))
            throw new Image_Exception('image.type_not_allowed', $dir.$file);

        $this->tmp_image = $this->create($image['file'], $image['type']);
        $quality         = arr::remove('quality', $actions);

        if ($status = $this->execute($actions))
        {
            imagealphablending($this->tmp_image, TRUE);
            imagesavealpha($this->tmp_image, TRUE);

            switch ($save)
            {
                case 'imagejpeg':
                    ($quality === NULL) and $quality = 95;
                break;

                case 'imagegif':
                    unset($quality);
                break;

                case 'imagepng':
                    $quality = 9;
                break;
            }

            if ($render === FALSE)
            {
                $status = isset($quality) ? $save($this->tmp_image, $dir.$file, $quality) : $save($this->tmp_image, $dir.$file);
            }
            else
            {
                switch ($save)
                {
                    case 'imagejpeg':
                        header('Content-Type: image/jpeg');
                    break;

                    case 'imagegif':
                        header('Content-Type: image/gif');
                    break;

                    case 'imagepng':
                        header('Content-Type: image/png');
                    break;
                }

                $status = isset($quality) ? $save($this->tmp_image, NULL, $quality) : $save($this->tmp_image);
            }

            imagedestroy($this->tmp_image);
        }

        return $status;
    }

    public function flip($direction)
    {
        $src_width  = imagesx($this->tmp_image);
        $src_height = imagesy($this->tmp_image);

        $flipped = $this->imagecreatetransparent($src_width, $src_height);

        if ($direction === Image::HORIZONTAL)
        {
            for ($x = 0; $x < $src_width; $x++)
            {
                $status = imagecopy($flipped, $this->tmp_image, $x, 0, $src_width - $x - 1, 0, 1, $src_height);
            }
        }
        elseif ($direction === Image::VERTICAL)
        {
            for ($y = 0; $y < $src_height; $y++)
            {
                $status = imagecopy($flipped, $this->tmp_image, 0, $y, 0, $src_height - $y - 1, $src_width, 1);
            }
        }
        else
        {
            return TRUE;
        }

        if ($status === TRUE)
        {
            imagedestroy($this->tmp_image);
            $this->tmp_image = $flipped;
        }

        return $status;
    }

    public function crop($properties)
    {
        $properties = $this->sanitize_geometry($properties);
        $src_width  = imagesx($this->tmp_image);
        $src_height = imagesy($this->tmp_image);
        $img        = $this->imagecreatetransparent($properties['width'], $properties['height']);

        if ($status = imagecopyresampled($img, $this->tmp_image, 0, 0, $properties['left'], $properties['top'], $src_width, $src_height, $src_width, $src_height))
        {
            imagedestroy($this->tmp_image);
            $this->tmp_image = $img;
        }

        return $status;
    }

    public function resize($properties)
    {
        $src_width  = imagesx($this->tmp_image);
        $src_height = imagesy($this->tmp_image);

        if ($properties['master'] === Image::SPHERE)
        {
            if($src_width > $src_height)
            {
                $properties['master'] = Image::HEIGHT;
            }
            else
            {
                $properties['master'] = Image::WIDTH;
            }
        }

        if (substr($properties['width'], -1) === '%')
        {
            $properties['width'] = round($src_width * (substr($properties['width'], 0, -1) / 100));
        }

        if (substr($properties['height'], -1) === '%')
        {
            $properties['height'] = round($src_height * (substr($properties['height'], 0, -1) / 100));
        }

        empty($properties['width'])  and $properties['width']  = round($src_width * $properties['height'] / $src_height);
        empty($properties['height']) and $properties['height'] = round($src_height * $properties['width'] / $src_width);

        if ($properties['master'] === Image::AUTO)
        {
            $properties['master'] = (($src_width / $properties['width']) > ($src_height / $properties['height'])) ? Image::WIDTH : Image::HEIGHT;
        }

        if (empty($properties['height']) OR $properties['master'] === Image::WIDTH)
        {
            $properties['height'] = round($src_height * $properties['width'] / $src_width);
        }

        if (empty($properties['width']) OR $properties['master'] === Image::HEIGHT)
        {
            $properties['width'] = round($src_width * $properties['height'] / $src_height);
        }

        if ($properties['width'] > $src_width / 2 AND $properties['height'] > $src_height / 2)
        {
            $pre_width  = $src_width;
            $pre_height = $src_height;

            $max_reduction_width  = round($properties['width']  * 1.1);
            $max_reduction_height = round($properties['height'] * 1.1);

            while ($pre_width / 2 > $max_reduction_width AND $pre_height / 2 > $max_reduction_height)
            {
                $pre_width /= 2;
                $pre_height /= 2;
            }

            $img = $this->imagecreatetransparent($pre_width, $pre_height);

            if ($status = imagecopyresized($img, $this->tmp_image, 0, 0, 0, 0, $pre_width, $pre_height, $src_width, $src_height))
            {
                imagedestroy($this->tmp_image);
                $this->tmp_image = $img;
            }

            $src_width  = $pre_width;
            $src_height = $pre_height;
        }

        $img = $this->imagecreatetransparent($properties['width'], $properties['height']);

        if ($status = imagecopyresampled($img, $this->tmp_image, 0, 0, 0, 0, $properties['width'], $properties['height'], $src_width, $src_height))
        {
            imagedestroy($this->tmp_image);
            $this->tmp_image = $img;
        }

        return $status;
    }

    public function rotate($amount)
    {
        $img         = $this->tmp_image;
        $transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
        $img         = imagerotate($img, 360 - $amount, $transparent, -1);

        imagecolortransparent($img, $transparent);

        if ($status = imagecopymerge($this->tmp_image, $img, 0, 0, 0, 0, imagesx($this->tmp_image), imagesy($this->tmp_image), 100))
        {
            imagealphablending($img, TRUE);
            imagesavealpha($img, TRUE);
            imagedestroy($this->tmp_image);
            $this->tmp_image = $img;
        }

        return $status;
    }

    public function sharpen($amount)
    {
        if ( ! function_exists('imageconvolution'))
        {
            throw new Image_Exception('image.unsupported_method', __FUNCTION__);
        }

        $amount = round(abs(-18 + ($amount * 0.08)), 2);

        $matrix = array
        (
            array(-1,   -1,    -1),
            array(-1, $amount, -1),
            array(-1,   -1,    -1),
        );

        return imageconvolution($this->tmp_image, $matrix, $amount - 8, 0);
    }

    protected function properties()
    {
        return array(imagesx($this->tmp_image), imagesy($this->tmp_image));
    }

    /**
     * Returns an image with a transparent background. Used for rotating to
     * prevent unfilled backgrounds.
     *
     * @param   integer  image width
     * @param   integer  image height
     * @return  resource
     */
    protected function imagecreatetransparent($src_width, $src_height)
    {
        if (self::$blank_png === NULL)
        {
            self::$blank_png = imagecreatefromstring(base64_decode
            (
                'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29'.
                'mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADqSURBVHjaYvz//z/DYAYAAcTEMMgBQAANegcCBN'.
                'CgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQ'.
                'AANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoH'.
                'AgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAAA16BwIE0KB'.
                '3IEAADXoHAgTQoHcgQAANegcCBNCgdyBAgAEAMpcDTTQWJVEAAAAASUVORK5CYII='
            ));

            self::$blank_png_width  = imagesx(self::$blank_png);
            self::$blank_png_height = imagesy(self::$blank_png);
        }

        $img = imagecreatetruecolor($src_width, $src_height);

        imagecopyresized($img, self::$blank_png, 0, 0, 0, 0, $src_width, $src_height, self::$blank_png_width, self::$blank_png_height);
        imagealphablending($img, FALSE);
        imagesavealpha($img, TRUE);

        return $img;
    }

    public function watermark($options = array())
    {
        if ($options['model'] == 'overlay')
        {
            return $this->overlay_watermark($options['params']);
        }
        else
        {
            return $this->text_watermark($options['params']);
        }
    }


    /**
     * Watermark - Graphic Version
     *
     * @access  public
     * @return  bool
     */
    public function overlay_watermark($options = array())
    {
        $params     = array_merge(self::$params, $options);

        if (!file_exists($params['wm_overlay_path']))
        {
            throw new Image_Exception('imglib_missing_overlay_path');
        }

        $image_info = getimagesize($params['wm_overlay_path']);
        $wm_img     = $this->create($params['wm_overlay_path'], $image_info[2]);
        
        $wm_width   = $image_info[0];;
        $wm_height  = $image_info[1];

        $src_width  = imagesx($this->tmp_image);
        $src_height = imagesy($this->tmp_image);

        $params['wm_vrt_alignment'] = strtoupper(substr($params['wm_vrt_alignment'], 0, 1));
        $params['wm_hor_alignment'] = strtoupper(substr($params['wm_hor_alignment'], 0, 1));

        if ($params['wm_vrt_alignment'] == 'B')
        {
            $params['wm_vrt_offset'] = $params['wm_vrt_offset'] * -1;
        }

        if ($params['wm_hor_alignment'] == 'R')
        {
            $params['wm_hor_offset'] = $params['wm_hor_offset'] * -1;
        }

        $x_axis = $params['wm_hor_offset'] + $params['wm_padding'];
        $y_axis = $params['wm_vrt_offset'] + $params['wm_padding'];

        switch ($params['wm_vrt_alignment'])
        {
            case 'T':
                break;
            case 'M':   $y_axis += ($src_height / 2) - ($wm_height / 2);
                break;
            case 'B':   $y_axis += $src_height - $wm_height;
                break;
        }

        switch ($params['wm_hor_alignment'])
        {
            case 'L':
                break;
            case 'C':   $x_axis += ($src_width / 2) - ($wm_width / 2);
                break;
            case 'R':   $x_axis += $src_width - $wm_width;
                break;
        }

        if ($wm_img_type == 3 AND function_exists('imagealphablending'))
        {
            @imagealphablending($this->tmp_image, TRUE);
        }

        $rgba  = imagecolorat($wm_img, $params['wm_x_transp'], $params['wm_y_transp']);
        $alpha = ($rgba & 0x7F000000) >> 24;

        if ($alpha > 0)
        {
            imagecopy($this->tmp_image, $wm_img, $x_axis, $y_axis, 0, 0, $wm_width, $wm_height);
        }
        else
        {
            imagecolortransparent($wm_img, imagecolorat($wm_img, $params['wm_x_transp'], $params['wm_y_transp']));
            imagecopymerge($this->tmp_image, $wm_img, $x_axis, $y_axis, 0, 0, $wm_width, $wm_height, $params['wm_opacity']);
        }

        unset($params);
        return true;
    }

    /**
     * Watermark - Text Version
     *
     * @access  public
     * @return  bool
     */
    public function text_watermark($options = array())
    {
        $params  = array_merge(self::$params, $options);

        if ($params['wm_use_truetype'] == TRUE AND ! file_exists($params['wm_font_path']))
        {
            throw new Image_Exception('imglib_missing_font');
        }

        $src_width  = imagesx($this->tmp_image);
        $src_height = imagesy($this->tmp_image);

        $params['wm_font_color']   = str_replace('#', '', $params['wm_font_color']);
        $params['wm_shadow_color'] = str_replace('#', '', $params['wm_shadow_color']);

        $R1 = hexdec(substr($params['wm_font_color'], 0, 2));
        $G1 = hexdec(substr($params['wm_font_color'], 2, 2));
        $B1 = hexdec(substr($params['wm_font_color'], 4, 2));
        $R2 = hexdec(substr($params['wm_shadow_color'], 0, 2));
        $G2 = hexdec(substr($params['wm_shadow_color'], 2, 2));
        $B2 = hexdec(substr($params['wm_shadow_color'], 4, 2));

        $txt_color  = imagecolorclosest($this->tmp_image, $R1, $G1, $B1);
        $drp_color  = imagecolorclosest($this->tmp_image, $R2, $G2, $B2);

        if ($params['wm_vrt_alignment'] == 'B')
        {
            $params['wm_vrt_offset'] = $params['wm_vrt_offset'] * -1;
        }

        if ($params['wm_hor_alignment'] == 'R')
        {
            $params['wm_hor_offset'] = $params['wm_hor_offset'] * -1;
        }

        if ($params['wm_use_truetype'] == TRUE)
        {
            if ($params['wm_font_size'] == '')
            {
                $params['wm_font_size'] = '17';
            }

            $fontwidth  = $params['wm_font_size']-($params['wm_font_size']/4);
            $fontheight = $params['wm_font_size'];
            $params['wm_vrt_offset'] += $params['wm_font_size'];
        }
        else
        {
            $fontwidth  = imagefontwidth($params['wm_font_size']);
            $fontheight = imagefontheight($params['wm_font_size']);
        }

        $x_axis = $params['wm_hor_offset'] + $params['wm_padding'];
        $y_axis = $params['wm_vrt_offset'] + $params['wm_padding'];

        if ($params['wm_use_drop_shadow'] == FALSE)
        {
            $params['wm_shadow_distance'] = 0;
        }

        $params['wm_vrt_alignment'] = strtoupper(substr($params['wm_vrt_alignment'], 0, 1));
        $params['wm_hor_alignment'] = strtoupper(substr($params['wm_hor_alignment'], 0, 1));

        switch ($params['wm_vrt_alignment'])
        {
            case "T" :
                break;

            case "M":   $y_axis += ($src_height/2)+($fontheight/2);
                break;

            case "B":   $y_axis += ($src_height - $fontheight - $params['wm_shadow_distance'] - ($fontheight/2));
                break;
        }

        $x_shad = $x_axis + $params['wm_shadow_distance'];
        $y_shad = $y_axis + $params['wm_shadow_distance'];

        switch ($params['wm_hor_alignment'])
        {
            case "L":
                break;
            case "R":
                        if ($params['wm_use_drop_shadow'])
                            $x_shad += ($src_width - $fontwidth * strlen($params['wm_text']));
                            $x_axis += ($src_width - $fontwidth * strlen($params['wm_text']));
                break;
            case "C":
                        if ($params['wm_use_drop_shadow'])
                            $x_shad += floor(($src_width - $fontwidth * strlen($params['wm_text']))/2);
                            $x_axis += floor(($src_width  -$fontwidth * strlen($params['wm_text']))/2);
                break;
        }

        if ($params['wm_use_truetype'])
        {
            if ($params['wm_use_drop_shadow'])
                imagettftext($this->tmp_image, $params['wm_font_size'], 0, $x_shad, $y_shad, $drp_color, $params['wm_font_path'], $params['wm_text']);
                imagettftext($this->tmp_image, $params['wm_font_size'], 0, $x_axis, $y_axis, $txt_color, $params['wm_font_path'], $params['wm_text']);
        }
        else
        {
            if ($params['wm_use_drop_shadow'])
                imagestring($this->tmp_image, $params['wm_font_size'], $x_shad, $y_shad, $params['wm_text'], $drp_color);
                imagestring($this->tmp_image, $params['wm_font_size'], $x_axis, $y_axis, $params['wm_text'], $txt_color);
        }

        unset($params);
        return true;
    }

}