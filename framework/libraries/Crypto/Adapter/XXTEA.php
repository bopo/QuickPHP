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
 *
 * @category    QuickPHP
 * @package     XXTEA
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Encrypt.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Crypto_Adapter_XXTEA extends QuickPHP_Crypto_Abstract
{
    protected $config = array('key' => 'K0H@NA+PHP_7hE-SW!FtFraM3w0R|<');

    /**
     * 构造函数，读取配置和初始化数据
     *
     * @param   array|string 自定义配置或一个配置组名(配置文件中的组),如果为空，则使用默认值
     * @throws  QuickPHP_Exception
     */
    public function __construct(array $config = null)
    {
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
    }

    protected function long2str($v, $w)
    {
        $len = count($v);
        $n   = ($len - 1) << 2;

        if ($w) 
        {
            $m = $v[$len - 1];
            
            if (($m < $n - 3) || ($m > $n)) 
            {
                return false;
            }

            $n = $m;
        }
        
        $s = array();
        
        for ($i = 0; $i < $len; $i++) 
        {
            $s[$i] = pack("V", $v[$i]);
        }
        
        if ($w) 
        {
            return substr(join('', $s), 0, $n);
        }
        else 
        {
            return join('', $s);
        }
    }

    protected function str2long($s, $w) 
    {
        $v = unpack("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
        $v = array_values($v);
    
        if ($w) 
        {
            $v[count($v)] = strlen($s);
        }
    
        return $v;
    }

    protected function int32($n) 
    {
        while ($n >= 2147483648) $n -= 4294967296;
        while ($n <= -2147483649) $n += 4294967296;
        return (int)$n;
    }

    public function encode($data = null) 
    {
        $key = $this->config['key'];
    
        if ($data == "") 
        {
            return "";
        }
        
        $v = $this->str2long($data, true);
        $k = $this->str2long($key, false);
        
        if (count($k) < 4) 
        {
            for ($i = count($k); $i < 4; $i++) 
            {
                $k[$i] = 0;
            }
        }
        
        $n     = count($v) - 1;
        $z     = $v[$n];
        $y     = $v[0];
        $delta = 0x9E3779B9;
        $q     = floor(6 + 52 / ($n + 1));
        $sum   = 0;

        while (0 < $q--) 
        {
            $sum = $this->int32($sum + $delta);
            $e = $sum >> 2 & 3;
        
            for ($p = 0; $p < $n; $p++) 
            {
                $y  = $v[$p + 1];
                $mx = $this->int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ $this->int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                $z  = $v[$p] = $this->int32($v[$p] + $mx);
            }
            
            $y  = $v[0];
            $mx = $this->int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ $this->int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $z  = $v[$n] = $this->int32($v[$n] + $mx);
        }

        return $this->long2str($v, false);
    }

    public function decode($data = null)
    {
        $key = $this->config['key'];

        if ($data == "") 
        {
            return "";
        }
        
        $v = $this->str2long($data, false);
        $k = $this->str2long($key, false);
        
        if (count($k) < 4) 
        {
            for ($i = count($k); $i < 4; $i++) 
            {
                $k[$i] = 0;
            }
        }

        $n     = count($v) - 1;
        $z     = $v[$n];
        $y     = $v[0];
        $delta = 0x9E3779B9;
        $q     = floor(6 + 52 / ($n + 1));
        $sum   = $this->int32($q * $delta);

        while ($sum != 0) 
        {
            $e = $sum >> 2 & 3;
        
            for ($p = $n; $p > 0; $p--) 
            {
                $z  = $v[$p - 1];
                $mx = $this->int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ $this->int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
                $y  = $v[$p] = $this->int32($v[$p] - $mx);
            }
            
            $z   = $v[$n];
            $mx  = $this->int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ $this->int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
            $y   = $v[0] = $this->int32($v[0] - $mx);
            $sum = $this->int32($sum - $delta);
        }

        return $this->long2str($v, true);
    }
}
