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
 * QuickPHP 的加/解密类,该类依赖PHP的MCrypt扩展。
 *
 * @category    QuickPHP
 * @package     Librares
 * @subpackage  Crypto
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Config.php 8641 2012-01-05 08:35:39Z bopo $
 */
class QuickPHP_Crypto
{

    static function factory($adapter = 'encrypt')
    {
        $crypto = new Crypto($adapter);
        return $crypto;
    }
    /**
     * 构造函数，读取配置和初始化数据
     *
     * @param   array|string 自定义配置或一个配置组名(配置文件中的组),如果为空，则使用默认值
     * @throws  QuickPHP_Exception
     */
    public function __construct($adapter = 'encrypt')
    {
        $adapter = 'Crypto_Adapter_'.ucfirst($adapter);
        $config  = QuickPHP::config('crypto')->get($adapter, array());
        $this->adapter = new $adapter($config);
    }

    /**
     * 加密方法.
     *
     * @param   string  需要加密的字符串
     * @return  string  加密后字符串
     */
    public function encode($data)
    {
        return $this->adapter->encode($data);
    }

    /**
     * 解密方法.
     *
     * @param   string  需要解密的字符串
     * @return  string  解密后字符串
     */
    public function decode($data)
    {
        return $this->adapter->decode($data);
    }

}