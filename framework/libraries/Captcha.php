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
 * 验证码类
 *
 * @category    QuickPHP
 * @package     Libraries
 * @subpackage  Captcha
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Cache.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Captcha
{
    protected static $_instance;
    protected static $counted;
    protected $driver = 'gd';

    public static $config = array(
        'style'      => 'basic',
        'width'      => 114,
        'height'     => 34,
        'complexity' => 4,
        'background' => '',
        'fontpath'   => 'fonts',
        'fonts'      => array('DejaVuSerif.ttf'),
        'promote'    => false,
    );

    /**
     * 工厂方法，构建一个验证码对象.
     *
     * @param   string  配置组名
     * @return  object
     */
    public static function factory($group = null)
    {
        return new Captcha($group);
    }

    /**
     * 构建一个Captcha对象.
     *
     * @throws  Captcha_Exception
     * @param   string  配置组名
     * @return  void
     */
    public function __construct($group = null)
    {
        if(empty(Captcha::$_instance))
        {
            Captcha::$_instance = $this;
        }

        if ( ! is_string($group))
        {
            $group = 'default';
        }

        if ( ! is_array($config = QuickPHP::config('captcha')->$group))
        {
            throw new Captcha_Exception('undefined_group', array($group));
        }

        if ($group !== 'default')
        {
            if ( ! is_array($default = QuickPHP::config('captcha.default')))
            {
                throw new Captcha_Exception('undefined_group', array('default'));
            }

            $config += $default;
        }

        foreach ($config as $key => $value)
        {
            if (array_key_exists($key, Captcha::$config))
            {
                Captcha::$config[$key] = $value;
            }
        }

        Captcha::$config['group'] = $group;

        if ( ! empty($config['background']))
        {
            Captcha::$config['background'] = str_replace('\\', '/', realpath($config['background']));

            if ( ! is_file(Captcha::$config['background']))
            {
                throw new Captcha_Exception('file_not_found', array(Captcha::$config['background']));
            }
        }

        if ( ! empty($config['fonts']))
        {
            Captcha::$config['fontpath'] = str_replace('\\', '/', realpath($config['fontpath'])).'/';

            foreach ($config['fonts'] as $font)
            {
                if ( ! is_file(Captcha::$config['fontpath'].$font))
                {
                    throw new Captcha_Exception('file_not_found', array(Captcha::$config['fontpath'].$font));
                }
            }
        }

        $driver = 'Captcha_Driver_'.ucfirst($config['style']);

        $this->driver = new $driver;

        if ( ! ($this->driver instanceof Captcha_Interface))
        {
            throw new Captcha_Exception('driver_implements',
                array($config['style'], get_class($this), 'Captcha_Interface'));
        }
    }

    /**
     * 验证验证码输入数据,并且更新验证正确次数和错误数量
     *
     * @param   string   验证验证码输入数据
     * @return  boolean
     */
    public static function valid($response)
    {
        if (Captcha::factory()->promoted())
        {
            return true;
        }

        $result = (bool) Captcha::factory()->driver->valid($response);

        if (Captcha::$counted !== true)
        {
            Captcha::$counted = true;

            if ($result === true)
            {
                Captcha::factory()->valid_count(Session::instance()->get('captcha_valid_count') + 1);
            }
            else
            {
                Captcha::factory()->invalid_count(Session::instance()->get('captcha_invalid_count') + 1);
            }
        }

        return $result;
    }

    /**
     * 获得验证次数,如果参数不为空则更新验证次数.
     *
     * @param   integer  新验证次数
     * @param   boolean  是否是错误次数还是正确次数
     * @return  integer  新验证次数
     */
    public function valid_count($new_count = null, $invalid = false)
    {
        $session = ($invalid === true) ? 'captcha_invalid_count' : 'captcha_valid_count';

        if ($new_count !== null)
        {
            $new_count = (int) $new_count;

            if ($new_count < 1)
            {
                Session::instance()->delete($session);
            }
            else
            {
                Session::instance()->set($session, (int) $new_count);
            }

            return (int) $new_count;
        }

        return (int) Session::instance()->get($session);
    }

    /**
     * 获得验证错误次数,如果参数不为空则更新验证错误次数.
     *
     * @param   integer  新验证次数
     * @return  integer  新验证次数
     */
    public function invalid_count($new_count = null)
    {
        return $this->valid_count($new_count, true);
    }

    /**
     * 复位会话次数
     *
     * @return  void
     */
    public function reset_count()
    {
        $this->valid_count(0);
        $this->valid_count(0, true);
    }

    /**
     * 检查用户是否超出验证限制次数
     *
     * @param   integer  验证次数
     * @return  boolean
     */
    public function promoted($threshold = null)
    {
        if (Captcha::$config['promote'] === false)
        {
            return false;
        }

        if ($threshold === null)
        {
            $threshold = Captcha::$config['promote'];
        }

        return ($this->valid_count() >= $threshold);
    }

    /**
     * 显示出验证码(含HTML格式)
     *
     * @param   boolean  是否HTML格式, 输出类似 <img src="#" />
     * @return  mixed    HTML字符串或者图片对象
     */
    public function render($html = true)
    {
        return $this->driver->render($html);
    }

    /**
     * 魔术方法,显示出验证码.
     *
     * @return  mixed
     */
    public function __toString()
    {
        return $this->render();
    }

}