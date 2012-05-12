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
 * QuickPHP 下载助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: download.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_download
{
    /**
     * 强行一个文件下载到用户的浏览器。
     *
     * @param string $filename  浏览器输出文件名
     * @param mixed $data       浏览器输出文件流
     */
    public static function force($filename, $data = NULL)
    {
        if(empty($filename))
        {
            return FALSE;
        }

        if(is_file($filename))
        {
            $filepath  = str_replace('\\', '/', realpath($filename));
            $filesize  = filesize($filepath);
            $filename  = substr(strrchr('/' . $filepath, '/'), 1);
            $extension = strtolower(substr(strrchr($filepath, '.'), 1));
        }
        else
        {
            $filesize  = strlen($data);
            $filename  = substr(strrchr('/' . $filename, '/'), 1);
            $extension = strtolower(substr(strrchr($filename, '.'), 1));
        }

        $mime = QuickPHP::config('mimes')->get($extension, array('application/octet-stream'));

        header('Content-Type: ' . $mime[0]);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . sprintf('%d', $filesize));
        header('Expires: 0');

        if(strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
        {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        }
        else
        {
            header('Pragma: no-cache');
        }

        if(isset($filepath))
        {
            $handle = fopen($filepath, 'rb');
            fpassthru($handle);
            fclose($handle);
        }
        else
        {
            echo $data;
        }
    }
}