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
 * QuickPHP 压缩Tar驱动.
 *
 * $Id: Tar.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @category    QuickPHP
 * @package     Archive
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2007 Quick
 * @license     http://www.quickphp.net/license/
 */
class QuickPHP_Archive_Driver_Tar implements QuickPHP_Archive_Interface
{

    // 编译的存档数据
    protected $data = '';

    /**
     * 创建一个压缩存档并可以保存到一个文件($filename有值的情况)
     *
     * @param string $paths
     * @param string $filename
     * @return bool
     */
    public function create($paths, $filename = FALSE)
    {
        // Sort the paths to make sure that directories come before files
        sort($paths);

        foreach ($paths as $set)
            $this->add_data($set[0], $set[1], isset($set[2]) ? $set[2] : NULL);

        $tarfile = implode('', $this->data) . pack('a1024', ''); // EOF


        if($filename == FALSE)
            return $tarfile;

        if(substr($filename, - 3) != 'tar')
            $filename .= '.tar';

        // 以写模式创建文件并打开
        $file = fopen($filename, 'wb');

        // 文件锁死
        flock($file, LOCK_EX);

        // 写文件
        $return = fwrite($file, $tarfile);

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
     */
    public function add_data($file, $name, $contents = NULL)
    {
        // Determine the file type
        $type = is_dir($file) ? 5 : (is_link($file) ? 2 : 0);

        // Get file stat
        $stat = stat($file);

        // Get path info
        $path = pathinfo($file);

        // File header
        $tmpdata =  pack('a100', $name) . // Name of file
                    pack('a8', sprintf('%07o', $stat[2])) . // File mode
                    pack('a8', sprintf('%07o', $stat[4])) . // Owner user ID
                    pack('a8', sprintf('%07o', $stat[5])) . // Owner group ID
                    pack('a12', sprintf('%011o', $type === 2 ? 0 : $stat[7])) . // Length of file in bytes
                    pack('a12', sprintf('%011o', $stat[9])) . // Modify time of file
                    pack('a8', str_repeat(chr(32), 8)) . // Reserved for checksum for header
                    pack('a1', $type) . // Type of file
                    pack('a100', $type === 2 ? readlink($file) : '') . // Name of linked file
                    pack('a6', 'ustar') . // USTAR indicator
                    pack('a2', chr(32)) . // USTAR version
                    pack('a32', 'Unknown') . // Owner user name
                    pack('a32', 'Unknown') . // Owner group name
                    pack('a8', chr(0)) . // Device major number
                    pack('a8', chr(0)) . // Device minor number
                    pack('a155', $path['dirname'] === '.' ? '' : $path['dirname']) . // Prefix for file name
                    pack('a12', chr(0)); // End

        $checksum = pack('a8', sprintf('%07o', array_sum(array_map('ord', str_split(substr($tmpdata, 0, 512))))));

        $this->data[] = substr_replace($tmpdata, $checksum, 148, 8) . str_pad(file_get_contents($file), (ceil($stat[7] / 512) * 512), chr(0));
    }

}