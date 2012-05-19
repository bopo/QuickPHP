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
 * QuickPHP 接口类.
 *
 * @category    QuickPHP
 * @package     Image
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Interface.php 8773 2012-01-16 06:25:20Z bopo $
 */
interface Image_Interface
{
    /**
     * 创建一个压缩存档并可以保存到一个文件($filename有值的情况)
     *
     * @return  boolean
     */
    public function process($image, $actions, $dir, $file, $render = FALSE);
    public function crop($prop);
    public function flip($dir);
    public function resize($prop);
    public function rotate($amt);
    public function sharpen($amount);
//    public function watermark($watermark, $model = 'image', $options = array());
}