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
return array
(
    'default' => array(
        'driver' => 'gd',
        'params' => array(
            'wm_text'           => '这是水印文字',          // Watermark text if graphic is not used
            'wm_type'           => 'text',      // Type of watermarking.  Options:  text/overlay
            'wm_x_transp'       => 4,
            'wm_y_transp'       => 4,
            'wm_overlay_path'   => 'photo.jpg', // Watermark image path
            'wm_font_path'      => 'protected/fonts/ARIALUNI.TTF',  // TT font
            'wm_font_size'      => 50,          // Font size (different versions of GD will either use points or pixels)
            'wm_vrt_alignment'  => 'M',         // Vertical alignment:   T M B
            'wm_hor_alignment'  => 'C',         // Horizontal alignment: L R C
            'wm_padding'        => 0,           // Padding around text
            'wm_hor_offset'     => 0,           // Lets you push text to the right
            'wm_vrt_offset'     => 0,           // Lets you push  text down
            'wm_font_color'     => '#fff000',   // Text color
            'wm_shadow_color'   => '#000000',          // Dropshadow color
            'wm_shadow_distance'=> 2,           // Dropshadow distance
            'wm_opacity'        => 100,          // Image opacity: 1 - 100  Only works with image
            'wm_use_drop_shadow'=> true,          // Image opacity: 1 - 100  Only works with image
            'wm_use_truetype'   => 1,          // Image opacity: 1 - 100  Only works with image
        ),
    ),

    'imagick' => array(
        'driver' => 'imagick',
        'params' => array(),
    ),
);