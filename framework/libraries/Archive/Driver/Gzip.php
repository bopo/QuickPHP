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
 * QuickPHP 压缩Gzip驱动
 *
 * $Id: Gzip.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @category    QuickPHP
 * @package     Archive
 * @subpackage  Archive_Gzip
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 */
class QuickPHP_Archive_Driver_Gzip implements QuickPHP_Archive_Interface
{

    /**
     * 创建一个压缩存档并可以保存到一个文件($filename有值的情况)
     *
     * @param string $paths
     * @param string $filename
     * @return unknown
     */
    public function create($paths, $filename = FALSE)
    {
        $archive = new Archive('tar');

        foreach ($paths as $set)
            $archive->add($set[0], $set[1]);

        $gzfile = gzencode($archive->create());

        if($filename == FALSE)
            return $gzfile;

        if(substr($filename, - 7) !== '.tar.gz')
            $filename .= '.tar.gz';

        // 以写模式创建文件并打开
        $file = fopen($filename, 'wb');

        // 文件锁死
        flock($file, LOCK_EX);

        // 写文件
        $return = fwrite($file, $gzfile);

        // 文件解锁
        flock($file, LOCK_UN);

        // 关闭打开的文件
        fclose($file);

        return (bool) $return;
    }

    /**
     * 添加数据到压缩存档
     *
     * @param string $file
     * @param string $name
     * @param string $contents
     * @return unknown
     */
    public function add_data($file, $name, $contents = NULL)
    {
        return FALSE;
    }

}