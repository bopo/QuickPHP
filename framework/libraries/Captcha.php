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
 * Provides a driver-based interface for finding, creating, and deleting Cached
 * resources. Caches are identified by a unique string. Tagging of Caches is
 * also supported, and Caches can be found and deleted by id or tag.
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
     * Constructs and returns a new Captcha object.
     *
     * @param   string  config group name
     * @return  object
     */
    public static function factory($group = null)
    {
        return new Captcha($group);
    }

    /**
     * Constructs a new Captcha object.
     *
     * @throws  Captcha_Exception
     * @param   string  config group name
     * @return  void
     */
    public function __construct($group = null)
    {
        if(empty(self::$_instance))
        {
            self::$_instance = $this;
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
            if (array_key_exists($key, self::$config))
            {
                self::$config[$key] = $value;
            }
        }

        self::$config['group'] = $group;

        if ( ! empty($config['background']))
        {
            self::$config['background'] = str_replace('\\', '/', realpath($config['background']));

            if ( ! is_file(self::$config['background']))
            {
                throw new Captcha_Exception('file_not_found', array(self::$config['background']));
            }
        }

        if ( ! empty($config['fonts']))
        {
            self::$config['fontpath'] = str_replace('\\', '/', realpath($config['fontpath'])).'/';

            foreach ($config['fonts'] as $font)
            {
                if ( ! is_file(self::$config['fontpath'].$font))
                {
                    throw new Captcha_Exception('file_not_found', array(self::$config['fontpath'].$font));
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
     * Validates a Captcha response and updates response counter.
     *
     * @param   string   captcha response
     * @return  boolean
     */
    public static function valid($response)
    {
        if (self::factory()->promoted())
        {
            return true;
        }

        $result = (bool) self::factory()->driver->valid($response);

        if (self::$counted !== true)
        {
            self::$counted = true;

            if ($result === true)
            {
                self::factory()->valid_count(Session::instance()->get('captcha_valid_count') + 1);
            }
            else
            {
                self::factory()->invalid_count(Session::instance()->get('captcha_invalid_count') + 1);
            }
        }

        return $result;
    }

    /**
     * Gets or sets the number of valid Captcha responses for this session.
     *
     * @param   integer  new counter value
     * @param   boolean  trigger invalid counter (for internal use only)
     * @return  integer  counter value
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
     * Gets or sets the number of invalid Captcha responses for this session.
     *
     * @param   integer  new counter value
     * @return  integer  counter value
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
     * Checks whether user has been promoted after having given enough valid responses.
     *
     *
     * @param   integer  valid response count threshold
     * @return  boolean
     */
    public function promoted($threshold = null)
    {
        if (self::$config['promote'] === false)
        {
            return false;
        }

        if ($threshold === null)
        {
            $threshold = self::$config['promote'];
        }

        return ($this->valid_count() >= $threshold);
    }

    /**
     * 渲染出验证码
     *
     * @param   boolean  true to output html, e.g. <img src="#" />
     * @return  mixed    html string or void
     */
    public function render($html = true)
    {
        return $this->driver->render($html);
    }

    /**
     * Magically outputs the Captcha challenge.
     *
     * @return  mixed
     */
    public function __toString()
    {
        return $this->render();
    }

}