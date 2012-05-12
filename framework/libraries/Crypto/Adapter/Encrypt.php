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
 * Encrypt的加/解密类,该类依赖PHP的MCrypt扩展.
 * @see http://php.net/mcrypt
 *
 * @category    QuickPHP
 * @package     Encrypt
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Encrypt.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Crypto_Adapter_Encrypt extends QuickPHP_Crypto_Abstract
{

    protected static $_instance;

    protected static $rand;

    protected $config = array(
        'key'    => 'K0H@NA+PHP_7hE-SW!FtFraM3w0R|<',
        'mode'   => MCRYPT_MODE_NOFB,
        'cipher' => MCRYPT_RIJNDAEL_128);

    /**
     * 构造函数，读取配置和初始化数据
     *
     * @param   array|string 自定义配置或一个配置组名(配置文件中的组),如果为空，则使用默认值
     * @throws  QuickPHP_Exception
     */
    public function __construct(array $config = null)
    {
        if( ! extension_loaded('mcrypt'))
        {
            throw new QuickPHP_Exception('requires_mcrypt');
        }

        if( ! empty($config))
        {
            foreach ($config as $key => $val)
            {
                if(isset($this->config[$key]))
                {
                    $this->config[$key] = $val;
                }
            }
        }

        $size = mcrypt_get_key_size($this->config['cipher'], $this->config['mode']);

        if(strlen($this->config['key']) > $size)
        {
            $this->config['key'] = substr($this->config['key'], 0, $size);
        }

        $this->config['iv_size'] = mcrypt_get_iv_size($this->config['cipher'], $this->config['mode']);
    }

    /**
     * 加密方法.
     *
     * @param   string  需要加密的字符串
     * @return  string  加密后字符串
     */
    public function encode($data = null)
    {
        if(self::$rand === NULL)
        {
            if(QuickPHP::$is_windows)
            {
                self::$rand = MCRYPT_RAND;
            }
            else
            {
                if(defined('MCRYPT_DEV_URANDOM'))
                {
                    self::$rand = MCRYPT_DEV_URANDOM;
                }
                elseif(defined('MCRYPT_DEV_RANDOM'))
                {
                    self::$rand = MCRYPT_DEV_RANDOM;
                }
                else
                {
                    self::$rand = MCRYPT_RAND;
                }
            }
        }

        if(self::$rand === MCRYPT_RAND)
        {
            mt_srand();
        }

        $iv   = mcrypt_create_iv($this->config['iv_size'], self::$rand);
        $data = mcrypt_Encrypt($this->config['cipher'], $this->config['key'], $data, $this->config['mode'], $iv);

        return base64_encode($iv . $data);
    }

    /**
     * 解密方法.
     *
     * @param   string  需要解密的字符串
     * @return  string  解密后字符串
     */
    public function decode($data = null)
    {
        $data = base64_decode($data);
        $iv   = substr($data, 0, $this->config['iv_size']);
        $data = substr($data, $this->config['iv_size']);

        return rtrim(mcrypt_decrypt($this->config['cipher'], $this->config['key'], $data, $this->config['mode'], $iv), "\0");
    }

}