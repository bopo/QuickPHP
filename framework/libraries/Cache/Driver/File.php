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
 * QuickPHP 缓存驱动 File.
 *
 * $Id: File.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Cache
 */
class QuickPHP_Cache_Driver_File implements QuickPHP_Cache_Interface
{
    protected $directory = '';
    protected $compress  = FALSE;

    /**
     * 测试存储器缓存目录是否读写权限等
     */
    public function __construct($config)
    {
        // 缓存目录
        $directory = str_replace('\\', '/', realpath($config['directory'])) . '/';

        // 判断缓存目录是否可写
        if( ! is_dir($directory) or ! is_writable($directory))
            throw new QuickPHP_Cache_Exception('unwritable', array($directory));

        $this->directory = $directory;
        $this->compress  = (bool) $config['compress'];
    }

    /**
     * Finds an array of files matching the given id or tag.
     *
     * @param  string  cache id or tag
     * @param  bool    search for tags
     * @return array   of filenames matching the id or tag
     */
    protected function key_exists($id)
    {
        $paths = $this->directory($id);
        $files = glob($paths . $id . '~*');
        return $files;
    }

    /**
     * Sets a cache item to the given data, tags, and lifetime.
     *
     * @param   string   cache id to set
     * @param   string   data in the cache
     * @param   array    cache tags
     * @param   integer  lifetime
     * @return  bool
     */
    public function set($id, $data, $lifetime)
    {
        $this->delete($id);

        $lifetime = ($lifetime !== 0) ? $lifetime + time() : $lifetime;

        $file = $this->directory($id) . $id . '~' . $lifetime;
        $data = serialize($data);

        if($this->compress === TRUE)
            $data = gzcompress($data);

        return (bool) file_put_contents($file, $data, LOCK_EX);
    }

    /**
     * Fetches a cache item. This will delete the item if it is expired or if
     * the hash does not match the stored hash.
     *
     * @param   string  cache id
     * @return  mixed|NULL
     */
    public function get($id)
    {
        $data = NULL;

        if((bool) ($file = $this->key_exists($id)))
        {
            $file = current($file);

            if($this->expired($file))
            {
                $this->delete($id);
            }
            else
            {
                $ER = error_reporting(0);

                if((bool) ($data = file_get_contents($file)))
                {
                    if($this->compress === TRUE)
                        $data = gzuncompress($data);

                    $data = unserialize($data);
                }

                error_reporting($ER);
            }
        }

        return $data;
    }

    /**
     * Deletes a cache item by id or tag
     *
     * @param   string   cache id or tag, or TRUE for "all items"
     * @param   boolean  use tags
     * @return  boolean
     */
    public function delete($id)
    {
        $files = $this->key_exists($id);

        if(empty($files))
        {
            return FALSE;
        }

        $ER = error_reporting(E_ALL);

        foreach ($files as $file)
        {
            if( ! unlink($file))
            {
                throw new QuickPHP_Cache_Exception('Cache: Unable to delete cache file: {0}' . array($file));
            }
        }

        error_reporting($ER);

        return TRUE;
    }

    /**
     * Deletes all cache files that are older than the current time.
     *
     * @return void
     */
    public function delete_expired()
    {
        if((bool) ($files = $this->exists(TRUE)))
        {
            $ER = error_reporting(0);

            foreach ($files as $file)
            {
                if($this->expired($file))
                {
                    if( ! unlink($file))
                    {
                        throw new QuickPHP_Cache_Exception('Cache: Unable to delete cache file: {0}' . $file);
                    }
                }
            }

            error_reporting($ER);
        }
    }

    /**
     * Check if a cache file has expired by filename.
     *
     * @param  string  filename
     * @return bool
     */
    protected function expired($file)
    {
        $expires = (int) substr($file, strrpos($file, '~') + 1);
        return ($expires !== 0 and $expires <= time());
    }

    /**
     * Check if a cache file has expired by filename.
     *
     * @param  string  filename
     * @return bool
     */
    protected function directory($id, $level = 2)
    {
        $directory = $this->directory;

        for ($i = 1; $i <= $level; $i++)
        {
            $directory .= strtoupper(substr($id, ($i * 2), 2)) . '/';

            $ER = error_reporting(E_ALL);

            if( ! is_dir($directory))
            {
                try
                {
                    mkdir($directory, 0755, TRUE);
                    chmod($directory, 0755);
                }
                catch (Exception $e)
                {
                    throw new QuickPHP_Exception('Could not create cache directory {0}', array(debug::path($settings['cache_dir'])));
                }
            }

            error_reporting($ER);
        }

        return $directory;
    }

    /**
     * 删除全部数据.
     */
    public function flush()
    {
        return true; //(bool) $this->flushdir($this->directory);
    }

    //参数说明
    //$dir 基础目录名
    //返回:$count数组$count[0]删除目录数，$count[1]删除文件数
    //$delroot 是否连基础目录一起删除
    //    protected function flushdir($dir, $delroot = false, $isroot = true, $count = array(0,0))
    //    {
    //        if ($handle = opendir( $dir ))
    //        {
    //            while ( false !== ( $item = readdir( $handle ) ) )
    //            {
    //                if ( $item != "." && $item != ".." && $item != ".svn" )
    //                {
    //                    if ( is_dir( $dir  .'/' . $item ) )
    //                    {
    //                        $count = $this->truncatedir($dir . '/' . $item, $delroot, false, $count);
    //                    }
    //                    else
    //                    {
    //                        $count[1] = @unlink($dir . '/' . $item) ? $count[1]+1 : $count[1];
    //                    }
    //                }
    //            }
    //
    //            closedir( $handle );
    //
    //            if( !$isroot || ($isroot && $delroot) )
    //               $count[0] = rmdir( $dir ) ? $count[0]+1 : $count[0];
    //        }
    //
    //        return $count;
    //    }
}