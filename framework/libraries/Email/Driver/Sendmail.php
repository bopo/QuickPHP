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
 * $Id: Sendmail.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Image
 */
class QuickPHP_Email_Driver_Sendmail extends QuickPHP_Email_Abstract
{
    // Directory that IM is installed in
    protected $dir = '';

    // Command extension (exe for windows)
    protected $ext = '';

    // Temporary image filename
    protected $tmp_image;

    /**
     * Attempts to detect the ImageMagick installation directory.
     *
     * @throws  QuickPHP_Image_Exception
     * @param   array   configuration
     * @return  void
     */
    public function __construct($config)
    {
        if ( ! extension_loaded('imagick'))
            throw new QuickPHP_Cache_Exception('imagick_not_loaded');
    }

    /**
     * Creates a temporary image and executes the given actions. By creating a
     * temporary copy of the image before manipulating it, this process is atomic.
     */
    public function process($image, $actions, $dir, $file, $render = FALSE)
    {
        // We only need the filename
        $image = $image['file'];

        // Unique temporary filename
        $this->tmp_image = $dir.'k2img--'.sha1(time().$dir.$file).substr($file, strrpos($file, '.'));

        // Copy the image to the temporary file
        copy($image, $this->tmp_image);

        // Quality change is done last
        $quality = (int) arr::remove('quality', $actions);

        // Use 95 for the default quality
        empty($quality) and $quality = 95;

        // All calls to these will need to be escaped, so do it now
        $this->cmd_image = escapeshellarg($this->tmp_image);
        $this->new_image = ($render)? $this->cmd_image : escapeshellarg($dir.$file);

        if ($status = $this->execute($actions))
        {
            // Use convert to change the image into its final version. This is
            // done to allow the file type to change correctly, and to handle
            // the quality conversion in the most effective way possible.
            if ($error = exec(escapeshellcmd($this->dir.'convert'.$this->ext).' -quality '.$quality.'% '.$this->cmd_image.' '.$this->new_image))
            {
                $this->errors[] = $error;
            }
            else
            {
                // Output the image directly to the browser
                if ($render !== FALSE)
                {
                    $contents = file_get_contents($this->tmp_image);
                    switch (substr($file, strrpos($file, '.') + 1))
                    {
                        case 'jpg':
                        case 'jpeg':
                            header('Content-Type: image/jpeg');
                        break;
                        case 'gif':
                            header('Content-Type: image/gif');
                        break;
                        case 'png':
                            header('Content-Type: image/png');
                        break;
                    }
                    echo $contents;
                }
            }
        }

        // Remove the temporary image
        unlink($this->tmp_image);
        $this->tmp_image = '';

        return $status;
    }

    public function crop($prop)
    {
        return $this->imagick->cropImage ( $prop['width'] , $prop['height'], $prop['left'] , $prop['top'] );
    }

    public function flip($dir)
    {
        return $this->imagick->flipImage ( $prop['width'] , $prop['height'], $prop['left'] , $prop['top'] );
    }

    public function resize($prop)
    {
        switch ($prop['master'])
        {
            case Image::WIDTH:  // Wx
                $dim = escapeshellarg($prop['width'].'x');
            break;
            case Image::HEIGHT: // xH
                $dim = escapeshellarg('x'.$prop['height']);
            break;
            case Image::AUTO:   // WxH
                $dim = escapeshellarg($prop['width'].'x'.$prop['height']);
            break;
            case Image::NONE:   // WxH!
                $dim = escapeshellarg($prop['width'].'x'.$prop['height'].'!');
            break;
        }

        // Use "convert" to change the width and height
        if ($error = exec(escapeshellcmd($this->dir.'convert'.$this->ext).' -resize '.$dim.' '.$this->cmd_image.' '.$this->cmd_image))
        {
            $this->errors[] = $error;
            return FALSE;
        }

        return $this->imagick->resizeImage ( $columns , $rows , $filter , $blur , $bestfit );
    }

    public function rotate($amt)
    {
        return $this->imagick->rotateImage ( $background , $amt );
    }

    public function sharpen($amount)
    {
        // Set the sigma, radius, and amount. The amount formula allows a nice
        // spread between 1 and 100 without pixelizing the image badly.
        $sigma  = 0.5;
        $radius = $sigma * 2;
        $amount = round(($amount / 80) * 3.14, 2);

        // Convert the amount to an IM command
        return $this->imagick->sharpenImage ( $radius , $sigma, $amount );
    }

    protected function properties()
    {
        return array_slice(getimagesize($this->tmp_image), 0, 2, FALSE);
    }

}