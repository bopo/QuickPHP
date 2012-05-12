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

return array(
    'getimagesize_missing'  => '图像库需求 getimagesize() PHP 函数且在 PHP 配置文件没有激活.',
    'unsupported_method'    => '配置驱动不支持 {0} 图片转变.',
    'file_not_found'        => '指定图片 {0} 没有发现。在使用之前请使用 file_exists() 确认文件是否存在.',
    'type_not_allowed'      => '指定图片 {0} 为不允许图片类型. ',
    'invalid_width'         => '无效的宽度, {0}.',
    'invalid_height'        => '无效的高度, {0}.',
    'invalid_dimensions'    => '无效的 dimensions, {0}.',
    'invalid_master'        => '无效的 master dimension.',
    'invalid_flip'          => '无效的 flip direction.',
    'directory_unwritable'  => '指定的目录（文件夹）不可写, {0}.',

    // ImageMagick 信息
    'imagick_not_found'     => '指定的 ImageMagick 目录不包含在程序中, {0}.',
    // GraphicsMagick 信息
    'gmagick_not_found'     => '指定的 GraphicsMagick 目录不包含在程序中, {0}.',
    // GD 信息
    'gd_requires_v2'        => '图片库需求 GD2。详情请看 http://php.net/gd_info.',
);