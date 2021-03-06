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
 * Abstract configuration reader. 
 *
 * @category   QuickPHP
 * @package    Config
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
abstract class QuickPHP_Config_Abstract extends ArrayObject
{

    protected $_configuration_group;

    /**
     * 构建一个空的数组
     *
     * @return  void
     */
    public function __construct()
    {
        parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * 返回当前组数据序列化结果
     *
     * echo QuickPHP::config();
     *
     * @return  string
     */
    public function __toString()
    {
        return serialize($this->getArrayCopy());
    }

    /**
     * 加载一个配置组
     *
     * QuickPHP::config()->load($name, $array);
     *
     * @param   string  配置组名
     * @param   array   配置数组数据
     * @return  $this
     */
    public function load($group, array $config = null)
    {
        if($config === null)
        {
            return false;
        }

        // 克隆当前配置组名
        $object = clone $this;

        // 设置当前配置组名
        $object->_configuration_group = $group;
        $object->exchangeArray($config);

        return $object;
    }

    /**
     * 将已经加载的对象转换到原始数组
     *
     * $array = QuickPHP::config()->as_array();
     *
     * @return  array
     */
    public function as_array()
    {
        return $this->getArrayCopy();
    }

    /**
     * 按指定键从配置数组中取出所需的变量，如果没有在返回默认值
     *
     * $value = QuickPHP::config()->get($key);
     *
     * @param   string   数组的键
     * @param   mixed    默认值
     * @return  mixed
     */
    public function get($key, $default = null)
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
    }

    /**
     * 设置值到配置数组中
     *
     * QuickPHP::config()->set($key, $new_value);
     *
     * @param   string   数组的键
     * @param   mixed    数组的值
     * @return  $this
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }
}