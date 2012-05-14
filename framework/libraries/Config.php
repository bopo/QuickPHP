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
 * QuickPHP 配置文件操作类。
 *
 * @category    QuickPHP
 * @package     Librares
 * @subpackage  Config
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Config.php 8641 2012-01-05 08:35:39Z bopo $
 */
class QuickPHP_Config
{

    public static $loaded;
    protected $_readers = array();
    protected static $_instance;

    /**
     * 获得配置对象的实例方法
     *
     * $config = Config::instance();
     *
     * @return  QuickPHP_Config
     */
    public static function instance()
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 装载一个配置驱动类型
     *
     * $config->attach($reader);        // 尝试第一个驱动
     * $config->attach($reader, false); // 尝试最后个驱动
     *
     * @param   object   Config_Abstract 驱动对象
     * @param   boolean  序列
     * @return  $this
     */
    public function attach(Config_Abstract $reader, $first = true)
    {
        if($first === true)
        {
            array_unshift($this->_readers, $reader);
        }
        else
        {
            $this->_readers[] = $reader;
        }

        return $this;
    }

    /**
     * 消除配置驱动
     *
     * $config->detach($reader);
     *
     * @param   object  Config_Reader 驱动对象
     * @return  $this
     */
    public function detach(Config_Abstract $reader)
    {
        if(($key = array_search($reader, $this->_readers)) !== false)
        {
            unset($this->_readers[$key]);
        }

        return $this;
    }

    /**
     * 加载一个配置组
     *
     * $array = $config->load($name);
     *
     * @param   string  配置组名
     * @return  object  配置数据
     * @throws  Config_Exception
     */
    public function load($group)
    {
        foreach ($this->_readers as $reader)
        {
            if(($config = $reader->load($group)) == false)
            {
                return $config;
            }
        }

        reset($this->_readers);

        if( ! is_object($config = current($this->_readers)))
        {
            throw new Config_Exception('no_readers_attached');
        }

        return $config->load($group, array());
    }

    /**
     * 复制一个配置组到全局活着其他配置组中
     *
     * $config->copy($name);
     *
     * @param   string   配置组名
     * @return  $this
     */
    public function copy($group)
    {
        $config = $this->load($group);

        foreach ($this->_readers as $reader)
        {
            if($config instanceof $reader)
            {
                continue;
            }

            $object = $reader->load($group, array());

            foreach ($config as $key => $value)
            {
                $object->offsetSet($key, $value);
            }
        }

        return $this;
    }
}