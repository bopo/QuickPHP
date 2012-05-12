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
 * QuickPHP 压缩Zip驱动
 *
 * $Id: Zip.php 8761 2012-01-15 05:10:59Z bopo $
 *
 * @category   QuickPHP
 * @package    Archive
 * @subpackage Archive_Zip
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 */
class QuickPHP_Archive_Driver_Zip implements QuickPHP_Archive_Interface
{

    // 编译的目录结构
    protected $dirs = '';

    // 编译的存档数据
    protected $data = '';

    // 偏移位置
    protected $offset = 0;

    /**
     * 创建一个压缩存档并可以保存到一个文件($filename有值的情况)
     *
     * @param string $paths
     * @param string $filename
     * @return void
     */
    public function create($paths, $filename = FALSE)
    {
        // 排序路径，以确保目录在前
        sort($paths);

        // 每个路径单独添加
        foreach ($paths as $set)
            $this->add_data($set[0], $set[1], isset($set[2]) ? $set[2] : NULL);

        // 文件数据
        $data = implode('', $this->data);

        // 目录数据
        $dirs = implode('', $this->dirs);

        $zipfile = $data . // 文件数据
                    $dirs . // 目录数据
                    "\x50\x4b\x05\x06\x00\x00\x00\x00" . // 目录的末尾
                    pack('v', count($this->dirs)) . // Total number of entries "on disk"
                    pack('v', count($this->dirs)) . // Total number of entries in file
                    pack('V', strlen($dirs)) . // Size of directories
                    pack('V', strlen($data)) . // Offset to directories
                    "\x00\x00"; // Zip comment length


        if($filename == FALSE)
            return $zipfile;

        if(substr($filename, - 3) != 'zip')
            $filename .= '.zip';

        // 以写模式创建文件并打开
        $file = fopen($filename, 'wb');

        // 文件锁死
        flock($file, LOCK_EX);

        // 写文件
        $return = fwrite($file, $zipfile);

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
        // Determine the file type: 16 = dir, 32 = file
        $type = (substr($file, - 1) === '/') ? 16 : 32;

        // Fetch the timestamp, using the current time if manually setting the contents
        $timestamp = date::unix2dos(($contents === NULL) ? filemtime($file) : time());

        // Read the file or use the defined contents
        $data = ($contents === NULL) ? file_get_contents($file) : $contents;

        // Gzip the data, use substr to fix a CRC bug
        $zdata = substr(gzcompress($data), 2, - 4);

        $this->data[] = "\x50\x4b\x03\x04" . // Zip header
                        "\x14\x00" . // Version required for extraction
                        "\x00\x00" . // General bit flag
                        "\x08\x00" . // Compression method
                        pack('V', $timestamp) . // Last mod time and date
                        pack('V', crc32($data)) . // CRC32
                        pack('V', strlen($zdata)) . // Compressed filesize
                        pack('V', strlen($data)) . // Uncompressed filesize
                        pack('v', strlen($name)) . // Length of file name
                        pack('v', 0) . // Extra field length
                        $name . // File name
                        $zdata; // Compressed data

        $this->dirs[] = "\x50\x4b\x01\x02" . // Zip header
                        "\x00\x00" . // Version made by
                        "\x14\x00" . // Version required for extraction
                        "\x00\x00" . // General bit flag
                        "\x08\x00" . // Compression method
                        pack('V', $timestamp) . // Last mod time and date
                        pack('V', crc32($data)) . // CRC32
                        pack('V', strlen($zdata)) . // Compressed filesize
                        pack('V', strlen($data)) . // Uncompressed filesize
                        pack('v', strlen($name)) . // Length of file name
                        pack('v', 0) . // Extra field length
                        // End "local file header"
                        // Start "data descriptor"
                        pack('v', 0) . // CRC32
                        pack('v', 0) . // Compressed filesize
                        pack('v', 0) . // Uncompressed filesize
                        pack('V', $type) . // File attribute type
                        pack('V', $this->offset) . // Directory offset
                        $name; // File name

        // 设置新的偏移
        $this->offset = strlen(implode('', $this->data));
    }

}