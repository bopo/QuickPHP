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
 * QuickPHP 操作和验证$_FILES的文件上传助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: upload.php 8320 2011-10-05 14:59:55Z bopo $
 */

class QuickPHP_upload
{

    /**
     * 保存上传文件到一个新的目录
     *
     * @param   mixed    $_FILE的键名
     * @param   string   新文件名
     * @param   string   新目录
     * @param   integer  chmod mask
     * @return  string   保存后的文件路径
     */
    public static function save($file, $filename = null, $directory = null, $chmod = 0644)
    {
        $file   = is_array($file) ? $file : $_FILES[$file];
        $config = QuickPHP::config('upload');

        if($filename === null)
        {
            $filename = time() . $file['name'];
        }

        if($config->get('remove_spaces') === true)
        {
            $filename = preg_replace('/\s+/', '_', $filename);
        }

        if($directory === null)
        {
            $directory = $config->get('directory', true);
        }

        $directory = rtrim($directory, '/') . '/';

        if( ! is_dir($directory) and $config->get('directory', true) === true)
        {
            mkdir($directory, 0777, true);
        }

        if( ! is_writable($directory))
        {
            throw new QuickPHP_Exception('upload.not_writable', $directory);
        }

        if(is_uploaded_file($file['tmp_name']) and move_uploaded_file($file['tmp_name'], $filename = $directory . $filename))
        {
            if($chmod !== false)
            {
                chmod($filename, $chmod);
            }

            return $filename;
        }

        return false;
    }

    /**
     * 验证上传文件是否有效
     *
     * @param   array  $_FILES item
     * @return  bool
     */
    public static function valid(array $file)
    {
        return (is_array($file)
            and isset($file['error'])
            and isset($file['name'])
            and isset($file['type'])
            and isset($file['tmp_name'])
            and isset($file['size']));
    }

    /**
     * 验证上传文件内容是否有效
     *
     * @param   array    $_FILES item
     * @return  bool
     */
    public static function required(array $file)
    {
        //UPLOAD_ERR_OK
        //其值为 0，没有错误发生，文件上传成功。
        //
        //UPLOAD_ERR_INI_SIZE
        //其值为 1，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。
        //
        //UPLOAD_ERR_FORM_SIZE
        //其值为 2，上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。
        //
        //UPLOAD_ERR_PARTIAL
        //其值为 3，文件只有部分被上传。
        //
        //UPLOAD_ERR_NO_FILE
        //其值为 4，没有文件被上传。
        //
        //UPLOAD_ERR_NO_TMP_DIR
        //其值为 6，找不到临时文件夹。PHP 4.3.10 和 PHP 5.0.3 引进。
        //
        //UPLOAD_ERR_CANT_WRITE
        //其值为 7，文件写入失败。PHP 5.1.0 引进。
        return (isset($file['tmp_name'])
            and isset($file['error'])
            and is_uploaded_file($file['tmp_name'])
            and (int) $file['error'] === UPLOAD_ERR_OK);
    }

    /**
     * 验证上传文件是否允许的扩展名
     *
     * @param   array    $_FILES item
     * @param   array    允许的扩展名
     * @return  bool
     */
    public static function type(array $file, array $allowed_types)
    {
        if((int) $file['error'] !== UPLOAD_ERR_OK)
        {
            return true;
        }

        $extension  = strtolower(substr(strrchr($file['name'], '.'), 1));
        $mime_type  = QuickPHP::config('mimes')->get($extension);

        return ( ! empty($extension) and in_array($extension, $allowed_types) and is_array($mime_type));
    }

    /**
     * 验证上传的文件的文件大小允许的。
     * 文件大小的设定规则是：数字加单位方式，单位：(B)ytes, (K)ilobytes, (M)egabytes, (G)igabytes.
     * 例如：限制大小到1MB，则是“1MB”。
     *
     * @param   array    $_FILES item
     * @param   array    最大文件大小
     * @return  bool
     */
    public static function size(array $file, array $size)
    {
        if((int) $file['error'] !== UPLOAD_ERR_OK)
        {
            return true;
        }

        $size = strtoupper($size[0]);

        if( ! preg_match('/[0-9]++[BKMG]/', $size))
        {
            return false;
        }

        switch (substr($size, - 1))
        {
            case 'T' :
                $size = intval($size) * pow(1024, 4);
                break;
            case 'G' :
                $size = intval($size) * pow(1024, 3);
                break;
            case 'M' :
                $size = intval($size) * pow(1024, 2);
                break;
            case 'K' :
                $size = intval($size) * pow(1024, 1);
                break;
            default :
                $size = intval($size);
                break;
        }

        return ($file['size'] <= $size);
    }

}