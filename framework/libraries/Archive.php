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
 * 压缩存档库,提供多种压缩格式(zip,gzip,tar,bz2)
 *
 * @category    QuickPHP
 * @package     Libraries
 * @subpackage  Archive
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Archive.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Archive
{
    // 文件和目录容器
    protected $paths;

    // 压缩驱动实例容器
    protected $driver;

    /**
     * 构造函数,加载压缩驱动.
     *
     * @throws  QuickPHP_Archive_Exception
     * @param   string   创建压缩包的类型(zip,rar,tar等)
     * @return  void
     */
    public function __construct($type = 'zip')
    {
        $type  = empty($type) ? 'zip' : $type;
        $class = 'Archive_Driver_' . ucfirst($type);
        $this->driver = new $class();

        if( ! ($this->driver instanceof QuickPHP_Archive_Interface))
        {
            throw new QuickPHP_Archive_Exception('driver_implements' , array($type, get_class($this)));
        }
    }

    /**
     * 添加文件或目录，递归到压缩存档.
     *
     * @param   string   文件或目录
     * @param   string   名称可以指定的文件或目录
     * @param   bool     是否递归添加文件或目录
     * @return  object
     */
    public function add($path, $name = NULL, $recursive = NULL)
    {
        // 转化为正斜线(/)
        $path = str_replace('\\', '/', $path);

        // 设置名称
        empty($name) and $name = $path;

        if(is_dir($path))
        {
            // 强行转化目录分割为正斜线(/)
            $path = rtrim($path, '/') . '/';
            $name = rtrim($name, '/') . '/';

            // 添加目录的路径
            $this->paths[] = array($path, $name);

            if($recursive === TRUE)
            {
                $dir = opendir($path);

                while(($file = readdir($dir)) !== FALSE)
                {
                    // 屏蔽隐藏的文件或目录
                    if($file[0] === '.')
                    {
                        continue;
                    }

                    // 添加路径内容
                    $this->add($path . $file, $name . $file, TRUE);
                }

                closedir($dir);
            }
        }
        else
        {
            $this->paths[] = array($path, $name);
        }

        return $this;
    }

    /**
     * 创建一个压缩存档，并保存到一个文件中.
     *
     * @param   string   压缩存档文件名
     * @return  boolean
     */
    public function save($filename)
    {
        $directory = pathinfo($filename, PATHINFO_DIRNAME);

        if( ! is_writable($directory))
        {
            throw new QuickPHP_Archive_Exception('directory_unwritable', array($directory));
        }

        if(is_file($filename))
        {
            if( ! is_writable($filename))
            {
                throw new QuickPHP_Archive_Exception('filename_conflict', array($directory));
            }

            unlink($filename);
        }

        return $this->driver->create($this->paths, $filename);
    }

    /**
     * 创建一个原始档案文件，并返回.
     *
     * @return  string
     */
    public function create()
    {
        return $this->driver->create($this->paths);
    }

    /**
     * 强行下载压缩文件.
     *
     * @param   string   下载压缩存档的文件名
     * @return  void
     */
    public function download($filename)
    {
        download::force($filename, $this->driver->create($this->paths));
    }

}
