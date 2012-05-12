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
 * QuickPHP 文件操作助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  file
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: file.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_file
{
    /**
     * 尝试获得文件的MIME类型,该方法非精确
     *
     * @param   string   文件名
     * @return  string   文件MIME类型
     * @return  boolean
     */
    public static function mime($filename)
    {
        if( ! (is_file($filename) and is_readable($filename)))
        {
            return false;
        }

        $extension = strtolower(substr(strrchr($filename, '.'), 1));

        if(preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension))
        {
            $ER   = error_reporting(0);
            $mime = getimagesize($filename);

            error_reporting($ER);

            if(isset($mime['mime']))
            {
                return $mime['mime'];
            }
        }

        if(function_exists('finfo_open'))
        {
            $finfo = finfo_open(FILEINFO_MIME);
            $mime  = finfo_file($finfo, $filename);

            finfo_close($finfo);

            return $mime;
        }

        if(ini_get('mime_magic.magicfile') and function_exists('mime_content_type'))
        {
            return mime_content_type($filename);
        }

        if( ! QuickPHP::$is_windows)
        {
            if($command = trim(exec('which file', $output, $return)) and $return === 0)
            {
                return trim(exec($command . ' -bi ' . escapeshellarg($filename)));
            }
        }

        $mime = QuickPHP::config('mimes')->get($extension, null);

        if( ! empty($extension) and is_array($mime))
        {
            return $mime[0];
        }

        return false;
    }

    /**
     * 按照指定大小拆分文件
     *
     * @param   string   要拆分的文件
     * @param   string   输出的目录
     * @param   integer  拆分大小(单位 MB)
     * @return  bool
     */
    public static function split($filename, $output_dir = false, $piece_size = 10)
    {
        $output_dir = ($output_dir == false)
            ? pathinfo(str_replace('\\', '/', realpath($filename)), PATHINFO_DIRNAME)
            : str_replace('\\', '/', realpath($output_dir));

        $output_dir = rtrim($output_dir, '/') . '/';
        $input_file = fopen($filename, 'rb');
        $piece_size = 1024 * 1024 * (int) $piece_size;

        $read  = 0;
        $piece = 1;
        $chunk = 1024 * 8;

        while( ! feof($input_file))
        {
            $piece_name = $filename . '.' . str_pad($piece, 3, '0', STR_PAD_LEFT);
            $piece_open = @fopen($piece_name, 'wb+') or die('打开的文件不可写 ' . $piece_name);

            while($read < $piece_size and $data = fread($input_file, $chunk))
            {
                fwrite($piece_open, $data) or die('打开的文件不可写 ' . $piece_name);
                $read += $chunk;
            }

            fclose($piece_open);

            $read = 0;
            $piece++;

            ($piece < 999) or die('最大值已超过999');
        }

        fclose($input_file);

        return ($piece - 1);
    }

    /**
     * 将一个拆分文件合并到整个文件
     *
     * @param   string   拆分文件名, 扩展名不能是 .000
     * @param   string   输出文件名
     * @return  bool
     */
    public static function join($filename, $output = false)
    {
        if($output == false)
        {
            $output = $filename;
        }

        $piece = 1;
        $chunk = 1024 * 8;

        $output_file = @fopen($output, 'wb+') or die('无法打开输出文件 ' . $output);

        while((bool) ($piece_open = @fopen(($piece_name = $filename . '.' . str_pad($piece, 3, '0', STR_PAD_LEFT)), 'rb')))
        {
            while( ! feof($piece_open))
            {
                fwrite($output_file, fread($piece_open, $chunk));
            }

            fclose($piece_open);

            $piece++;

            ($piece < 999) or die('最大值已超过999');
        }

        fclose($output_file);
        return ($piece - 1);
    }

    /**
     * 读文件
     * @param string    文件名
     * @return bool
     */
    public static function read($filename)
    {
        (file_exists($filename)) OR die($filename . '文件不存在 <br />[文件:' . __FILE__ . '(行数:' . __LINE__ . ')]');
        $output = @file_get_contents($filename);

        return $output;
    }

    /**
     * 向文件写数据
     *
     * @param   string  文件名
     * @param   mixed   数据
     * @param   bool    是否是累加
     * @return  mixed
     */
    public static function write($filename, $data, $append = false)
    {
        (is_writable($filename)) or die($filename . '文件不可写 <br />[文件:' . __FILE__ . '(行数:' . __LINE__ . ')]');

        $append = $append ? 'FILE_APPEND' : null;
        $output = @file_put_contents($filename, $data, $append);

        @chmod($filename, FILE_WRITE_MODE);
        return $output;
    }

    /**
     * 删除文件
     * @param   string  文件名
     * @return  bool
     */
    public static function delete($filename)
    {
        (is_writable($filename)) OR die($filename . '文件不可写 <br />[文件:' . __FILE__ . '(行数:' . __LINE__ . ')]');

        $output = @unlink($filename);
        return (bool) $output;
    }

    /**
     * 移动文件
     * @param   string  文件名
     * @return  bool
     */
    public static function move($filename, $filename2)
    {
        return (bool) @rename($filename, $filename2);
    }
}