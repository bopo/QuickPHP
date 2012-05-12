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
 * 模型的基类。所有模型继承该类。
 *
 * @category    QuickPHP
 * @package     Model
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Model.php 8589 2011-12-21 06:01:18Z bopo $
 */
abstract class QuickPHP_Model
{
    // 数据库实例
    protected $_db = 'default';

    /**
     * 创建一个新的模型实例。传递数据库实例或配置组名称到模型中。如果没有数据库定义，则使用默认数据库组。
     *
     * 实例: $model = Model::factory($name);
     *
     * @param   string   model name
     * @param   mixed    Database instance object or string
     * @return  Model
     */
    public static function factory($name, $db = null)
    {
        $class = "{$name}_Model";
        return new $class($db);
    }

    /**
     * 创建一个新的模型实例。
     *
     * 实例: $model = new Foo_Model($db);
     *
     * @param   mixed  数据库实例或者数据库配置组
     * @return  void
     */
    public function __construct($db = null)
    {
        if($db !== null)
        {
            $this->_db = $db;
        }

        if(is_string($this->_db))
        {
            $this->_db = Database::instance($this->_db);
        }
    }

}