<?php defined('SYSPATH') or die('No direct access allowed.');
// +----------------------------------------------------------------------+
// | Quick PHP Framework Version 0.10                                     |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 Quick.cn All rights reserved.                     |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: BoPo <ibopo@126.com>                                         |
// +----------------------------------------------------------------------+
/**
 * QucikPHP 图形处理ImageMagick驱动.
 *
 * $Id: ImageMagick.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Image
 */
class QuickPHP_Image_Driver_Imagick extends Image_Abstract
{
    // Directory that IM is installed in
    protected $dir = '';

    // Command extension (exe for windows)
    protected $ext = '';

    // Temporary image filename
    protected $tmp_image;

    protected $imagick;

    /**
     * Attempts to detect the ImageMagick installation directory.
     *
     * @throws  QuickPHP_Image_Exception
     * @param   array   configuration
     * @return  void
     */
    public function __construct($image)
    {
        if ( ! extension_loaded('imagick'))
        {
            throw new QuickPHP_Image_Exception('imagick_not_loaded', array('dfsdf'));
        }

        $this->imagick = new Imagick();
    }

    /**
     * Creates a temporary image and executes the given actions. By creating a
     * temporary copy of the image before manipulating it, this process is atomic.
     */
    public function process($image, $actions, $dir, $file, $render = FALSE)
    {
        // We only need the filename
        $this->imagick->readImage($image['file']);
        $quality = arr::remove('quality', $actions);

        $this->imagick->setImageCompressionQuality($quality);

        if ($status = $this->execute($actions))
        {
            if ($render !== FALSE)
            {
                $format = strtolower($this->imagick->getImageFormat());

                switch($format)
                {
                    case 'jpg':
                    case 'jpeg':
                        header('Content-Type: image/jpeg');
                        $this->imagick->setCompression(Imagick::COMPRESSION_JPEG);
                    break;
                    case 'gif':
                        header('Content-Type: image/gif');
                        $this->imagick->setCompression(Imagick::COMPRESSION_JPEG);
                    break;
                    case 'png':
                        header('Content-Type: image/png');
                    break;
                }

                echo $this->imagick;
            }
            else
            {
                $this->imagick->writeImage( $dir . $file );
            }

            $this->imagick->destroy();
        }

        return $status;
    }

    public function crop($properties)
    {
        $properties = $this->sanitize_geometry($properties);
        return $this->imagick->cropImage ( $properties['width'] , $properties['height'], $properties['left'] , $properties['top'] );
    }

    public function flip($direction)
    {
        if ($direction === Image::HORIZONTAL)
        {
            return $this->imagick->flopImage();
        }
        elseif ($direction === Image::VERTICAL)
        {
            return $this->imagick->flipImage();
        }
        else
        {
            return TRUE;
        }
    }

    public function resize($properties)
    {
        return $this->imagick->ResizeImage( $properties['width'] , $properties['height'] , imagick::FILTER_UNDEFINED , 0.9 , true );
    }

    public function rotate($amount)
    {
        $background = '#ffffff';
        return $this->imagick->rotateImage ( $background , $amount );
    }

    public function sharpen($amount)
    {
        $sigma  = 0.5;
        $radius = $sigma * 2;
        // $amount = round(($amount / 80) * 3.14, 2);
        return $this->imagick->sharpenImage( $radius , $sigma, Imagick::CHANNEL_ALL );
    }

    protected function properties()
    {
        $page = $this->imagick->getImagePage();
        return array($page['width'], $page['height']);
    }

    protected function createTextImagickDraw($fontSize=12, $fillColor='', $underColor='', $font='msyh.ttf')
    {
        $draw = new ImagickDraw();
        $draw->setFont($font);
        $draw->setFontSize($fontSize);
        
        //$draw->setGravity(Imagick::GRAVITY_SOUTHEAST);//设置水印位置
        if(!empty($underColor)) 
            $draw->setTextUnderColor(new ImagickPixel($underColor));

        if(!empty($fillColor)) 
            $draw->setFillColor(new ImagickPixel($fillColor));
        
        return $draw;
    }

    protected function createWaterImagickDraw($waterImg='water.png',$x=10,$y=85,$width=16,$height=16)
    {
        $water = new Imagick($waterImg);
        
        //$second->setImageOpacity (0.4);//设置透明度
        $draw = new ImagickDraw();
        
        //$draw->setGravity(Imagick::GRAVITY_CENTER);//设置位置
        $draw->composite($water->getImageCompose(), $x, $y, $width, $height,$water);
        return $draw;
    }

    /**
     * Watermark - Text Version
     *
     * @access  public
     * @return  bool
     */
    public function text_watermark($options = array())
    {
        $image_name = '01351346.gif';
        //$image_name = 'Left_spinning_dancer.gif';
        //$image_name = 'gifmerge.gif';
        $image = new Imagick($image_name);
        $animation = new Imagick();
        $animation->setFormat( "gif" );
        $image = $image->coalesceImages();
        $unitl = $image->getNumberImages();

        for ($i=0; $i<$unitl; $i++) 
        {
            $image->setImageIndex($i);
            $thisimage = new Imagick();
            $thisimage->readImageBlob($image);
            $delay = $thisimage->getImageDelay();
            
            $thisimage->annotateImage(createTextImagickDraw(12, 'red'), 30, 100, 0, '阿维卡');
            $thisimage->annotateImage(createTextImagickDraw(12, 'green'), 10, 120, 0, 'http://kller.cn');
            $thisimage->annotateImage(createTextImagickDraw(12, 'blue'), 10, 140, 0, 'http://www.aweika.com');
            $thisimage->drawImage(createWaterImagickDraw('f.jpg'));
            $animation->addImage($thisimage);
            $animation->setImageDelay( $delay );
        }

        $animation->writeImages('new_'.$image_name, true);
        header( "Content-Type: image/gif" );
        echo $animation->getImagesBlob();
    }
}

