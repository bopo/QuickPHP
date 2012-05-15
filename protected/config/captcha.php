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
 * @package Captcha
 *
 * Captcha configuration is defined in groups which allows you to easily switch
 * between different Captcha settings for different forms on your website.
 * Note: all groups inherit and overwrite the default group.
 *
 * Group Options:
 * style      - Captcha type, e.g. basic, alpha, word, math, riddle
 * width      - Width of the Captcha image
 * height     - Height of the Captcha image
 * complexity - Difficulty level (0-10), usage depends on chosen style
 * background - Path to background image file
 * fontpath   - Path to font folder
 * fonts      - Font files
 * promote    - Valid response count threshold to promote user (FALSE to disable)
 */
return array(
    'default' => array(
        'style'      => 'alpha',
        'width'      => 88,
        'height'     => 31,
        'complexity' => 4,
        'background' => '',
        'fontpath'   => APPPATH . 'fonts/',
        'fonts'      => array('DejaVuSerif.ttf'),
        'promote'    => FALSE
    )
);