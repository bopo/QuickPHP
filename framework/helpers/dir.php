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
 * QuickPHP 目录搜索类
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  dir
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: download.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_dir extends Directory
{
    /**
     *
     * The OS-specific temporary directory location.
     *
     * @var string
     *
     */
    protected static $_tmp;

    /**
     *
     * Hack for [[php::is_dir() | ]] that checks the include_path.
     *
     * Use this to see if a directory exists anywhere in the include_path.
     *
     * {{code: php
     * $dir = dir::exists('path/to/dir')
     * if ($dir) {
     *      $files = scandir($dir);
     * } else {
     *      echo "Not found in the include-path.";
     * }
     * }}
     *
     * @param string $dir Check for this directory in the include_path.
     *
     * @return mixed If the directory exists in the include_path, returns the
     * absolute path; if not, returns boolean false.
     *
     */
    public static function exists($dir)
    {
        // no file requested?
        $dir = trim($dir);

        if( ! $dir)
        {
            return false;
        }

        // using an absolute path for the file?
        // dual check for Unix '/' and Windows '\',
        // or Windows drive letter and a ':'.
        $abs = ($dir[0] == '/' || $dir[0] == '\\' || $dir[1] == ':');

        if($abs && is_dir($dir))
        {
            return $dir;
        }

        // using a relative path on the file
        $path = explode(PATH_SEPARATOR, ini_get('include_path'));

        foreach ($path as $base)
        {
            // strip Unix '/' and Windows '\'
            $target = rtrim($base, '\\/') . DIRECTORY_SEPARATOR . $dir;

            if(is_dir($target))
            {
                return $target;
            }
        }

        // never found it
        return false;
    }

    /**
     *
     * "Fixes" a directory string for the operating system.
     *
     * Use slashes anywhere you need a directory separator. Then run the
     * string through fixdir() and the slashes will be converted to the
     * proper separator (for example '\' on Windows).
     *
     * Always adds a final trailing separator.
     *
     * @param string $dir The directory string to 'fix'.
     *
     * @return string The "fixed" directory string.
     *
     */
    public static function fix($dir)
    {
        $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     *
     * Convenience method for dirname() and higher-level directories.
     *
     * @param string $file Get the dirname() of this file.
     *
     * @param int $up Move up in the directory structure this many
     * times, default 0.
     *
     * @return string The dirname() of the file.
     *
     */
    public static function name($file, $up = 0)
    {
        $dir = dirname($file);

        while($up--)
        {
            $dir = dirname($dir);
        }

        return $dir;
    }

    /**
     *
     * Returns the OS-specific directory for temporary files.
     *
     * @param string $sub Add this subdirectory to the returned temporary
     * directory name.
     *
     * @return string The temporary directory path.
     *
     */
    public static function tmp($sub = '')
    {
        if( ! dir::$_tmp)
        {
            if(function_exists('sys_get_temp_dir'))
            {
                $tmp = sys_get_temp_dir();
            }
            else
            {
                $tmp = dir::_tmp();
            }

            dir::$_tmp = rtrim($tmp, DIRECTORY_SEPARATOR);
        }

        $sub = trim($sub);

        if($sub)
        {
            $sub = trim($sub, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        return dir::fix(dir::$_tmp . DIRECTORY_SEPARATOR . $sub);
    }

    /**
     *
     * Returns the OS-specific temporary directory location.
     *
     * @return string The temp directory path.
     *
     */
    protected static function _tmp()
    {
        // non-Windows system?
        if(strtolower(substr(PHP_OS, 0, 3)) != 'win')
        {
            $tmp = empty($_ENV['TMPDIR']) ? getenv('TMPDIR') : $_ENV['TMPDIR'];

            if($tmp)
            {
                return $tmp;
            }
            else
            {
                return '/tmp';
            }
        }

        // Windows 'TEMP'
        $tmp = empty($_ENV['TEMP']) ? getenv('TEMP') : $_ENV['TEMP'];

        if($tmp)
        {
            return $tmp;
        }

        // Windows 'TMP'
        $tmp = empty($_ENV['TMP']) ? getenv('TMP') : $_ENV['TMP'];

        if($tmp)
        {
            return $tmp;
        }

        // Windows 'windir'
        $tmp = empty($_ENV['windir']) ? getenv('windir') : $_ENV['windir'];

        if($tmp)
        {
            return $tmp;
        }

        // final fallback for Windows
        return getenv('SystemRoot') . '\\temp';
    }

    /**
     * 遍历目录的结构，并以数组结构返回。
     *
     * @param string $source_dir     要遍历的目录
     * @param bool $top_level_only  是否只遍历顶层结构开关
     * @param $hidden               是否隐藏“.”和“..”
     */
    public static function map($source_dir, $top_level_only = FALSE, $hidden = FALSE)
    {
        if((bool) ($fp = opendir($source_dir)))
        {
            $source_dir = dir::fix($source_dir);
            $files      = scandir($source_dir);
            $filedata   = array();

            foreach($files as $file)
            {
                if(($hidden == FALSE && strncmp($file, '.', 1) == 0) or ($file == '.' or $file == '..'))
                {
                    continue;
                }

                if($top_level_only == FALSE && @is_dir($source_dir . $file))
                {
                    $temp_array = array();
                    $temp_array = dir::map(dir::fix($source_dir . $file), $top_level_only, $hidden);
                    $filedata[$file] = $temp_array;
                }
                else
                {
                    $filedata[] = $file;
                }
            }

            return $filedata;
        }

        return FALSE;
    }

}