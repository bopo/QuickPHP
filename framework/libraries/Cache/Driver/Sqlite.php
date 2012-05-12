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
 * QuickPHP 缓存驱动 Sqlite.
 *
 * $Id: Sqlite.php 8320 2011-10-05 14:59:55Z bopo $
 *
 * @author BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @package Cache
 */
class QuickPHP_Cache_Driver_Sqlite implements QuickPHP_Cache_Interface
{

    // SQLite database instance
    protected $db;

    // Database error messages
    protected $error_message;

    /**
     * Tests that the storage location is a directory and is writable.
     */
    public function __construct($config = array())
    {
        $directory      = str_replace('\\', '/', realpath($config['directory'])).'/';
        $filename       = $directory.'caches.db';
        $error_message  = NULL;

        if ( ! is_dir($directory) OR ! is_writable($directory))
            throw new QuickPHP_Cache_Exception('unwritable', array($directory));

        if (is_file($filename) AND ! is_writable($filename))
            throw new QuickPHP_Cache_Exception('unwritable', array($filename));

        $this->sqlite = new SQLiteDatabase($filename, '0666', $error_message);

        if ( ! empty($error_message))
            throw new QuickPHP_Cache_Exception('driver_error', array(sqlite_error_string($error_message)));

        $query  = "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'caches'";
        $tables = $this->sqlite->query($query, SQLITE_BOTH, $error_message);

        if ( ! empty($error_message))
            throw new QuickPHP_Cache_Exception('driver_error', array(sqlite_error_string($error_message)));

        if ($tables->numRows() == 0)
            $this->sqlite->unbufferedQuery($config['schema']);
    }

    /**
     * Checks if a cache id is already set.
     *
     * @param  string   cache id
     * @return boolean
     */
    public function exists($id)
    {
        $query = "SELECT id FROM caches WHERE id = '$id'";

        return ($this->sqlite->query($query)->numRows() > 0);
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
        $data = sqlite_escape_string(serialize($data));

        $lifetime = ($lifetime !== 0) ? $lifetime + time() : $lifetime;

        $query = $this->exists($id)
            ? "UPDATE caches SET expiration = '$lifetime', cache = '$data' WHERE id = '$id'"
            : "INSERT INTO caches VALUES('$id', '$lifetime', '$data')";

        $this->sqlite->unbufferedQuery($query, SQLITE_BOTH, $error_message);

        if ( ! empty($error_message))
            return FALSE;

        return TRUE;
    }


    /**
     * Fetches a cache item. This will delete the item if it is expired or if
     * the hash does not match the stored hash.
     *
     * @param  string  cache id
     * @return mixed|NULL
     */
    public function get($id)
    {
        $data  = NULL;
        $query = "SELECT id, expiration, cache FROM caches WHERE id = '$id' LIMIT 0, 1";
        $query = $this->sqlite->query($query, SQLITE_BOTH, $error_message);

        if ((bool)($cache = $query->fetchObject()))
        {
            if ($cache->expiration != 0 AND $cache->expiration <= time())
            {
                $this->delete($cache->id);
            }
            else
            {
                $ER = error_reporting(~E_NOTICE);
                $data = unserialize($cache->cache);
                error_reporting($ER);
            }
        }

        return $data;
    }

    /**
     * Deletes a cache item by id or tag
     *
     * @param  string  cache id or tag, or TRUE for "all items"
     * @param  bool    delete a tag
     * @return bool
     */
    public function delete($id)
    {
        $where = ($id === TRUE) ? '1' : "id = '$id'";

        $this->sqlite->unbufferedQuery('DELETE FROM caches WHERE '.$where, SQLITE_BOTH, $error_message);

        if ( ! empty($error_message))
            return FALSE;
        else
            return TRUE;
    }

    /**
     * Deletes all cache files that are older than the current time.
     */
    public function delete_expired()
    {
        $query = 'DELETE FROM caches WHERE expiration != 0 AND expiration <= '.time();
        $this->sqlite->unbufferedQuery($query);

        return TRUE;
    }

    /**
     * 清空数据
     *
     * @return bool
     */
    public function flush()
    {
        $query = 'DELETE FROM caches';
        $this->sqlite->unbufferedQuery($query);

        return TRUE;
    }
}