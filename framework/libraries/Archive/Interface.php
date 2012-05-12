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
 * QuickPHP 压缩驱动接口.
 *
 * $Id: Interface.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @category    QuickPHP
 * @package     Archive
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2007 Quick
 * @license     http://www.quickphp.net/license/
 */
interface QuickPHP_Archive_Interface
{
    /**
     * 创建一个压缩存档并可以保存到一个文件($filename有值的情况)
     *
     * @param   array    向压缩包添加文件
     * @param   string   保存压缩包的文件名
     * @return  boolean
     */
    public function create($paths, $filename = FALSE);

    /**
     * 添加数据到压缩存档
     *
     * @param   string   要添加到压缩包的文件名
     * @param   string   在压缩包中显示的文件名
     * @param   string   要压缩的内容
     * @return  void
     */
    public function add_data($file, $name, $contents = NULL);

}