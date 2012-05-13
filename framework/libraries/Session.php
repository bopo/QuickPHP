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
 * 会话变量存取库.
 *
 * $Id: Session.php 8761 2012-01-15 05:10:59Z bopo $
 *
 * @category    QuickPHP
 * @package     Session
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Session.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_Session
{
    // 会话变量单子实例容器
    protected static $_instance = null;

    // 保留键名 (不允许用户设置)
    protected static $protect = array('session_id', 'user_agent', 'last_activity', 'ip_address', 'total_hits', '_kf_flash_');

    protected static $config;

    protected static $driver;

    protected static $user_agent;

    protected static $flash;

    protected static $run = null;

    /**
     * 单例方法.
     */
    public static function instance($config = null)
    {
        if(Session::$_instance == null)
        {
            Session::$_instance = new Session($config);
        }

        return Session::$_instance;
    }

    /**
     * 初始化 Session
     */
    public function __construct($config = null)
    {
        if( ! empty($config) && is_string($config))
        {
            Session::$config = QuickPHP::config('session')->get($config);
        }
        else if( ! empty($config) && is_array($config))
        {
            Session::$config = $config;
        }
        else
        {
            Session::$config = QuickPHP::config('session')->get('default');
        }

        Session::$protect    = array_combine(Session::$protect, Session::$protect);
        Session::$user_agent = request::user_agent();

        ini_set('session.gc_probability', (int) Session::$config['gc_probability']);
        ini_set('session.gc_divisor', 100);
        ini_set('session.gc_maxlifetime', (Session::$config['expiration'] == 0) ? 86400 : Session::$config['expiration']);

        $this->create();

        if(Session::$config['regenerate'] > 0 and ($_SESSION['total_hits'] % Session::$config['regenerate']) === 0)
        {
            $this->regenerate();
        }
        else
        {
            cookie::set(Session::$config['name'], $_SESSION['session_id'], Session::$config['expiration']);
        }

        register_shutdown_function(array($this, 'write_close'));
    }

    /**
     * 获得Session id.
     *
     * @return  string
     */
    public function id()
    {
        return $_SESSION['session_id'];
    }

    /**
     * 创建一个 Session
     *
     * @param   array  variables to set after creation
     * @return  void
     */
    public function create($vars = null)
    {
        // 销毁当前会话变量
        $this->destroy();

        if(Session::$config['driver'] !== 'native')
        {
            // 设置驱动名称
            $driver = 'Session_Driver_' . ucfirst(Session::$config['driver']);

            // 初始化Session驱动
            Session::$driver = new $driver();

            // 验证驱动是否继承指定接口
            if( ! (Session::$driver instanceof Session_Interface))
            {
                throw new QuickPHP_Exception('driver_implements', array(Session::$config['driver'], get_class($this), 'Session_Interface'));
            }

            // 注册非native驱动到会话变量处理器
            session_set_save_handler(
                array(Session::$driver, 'open'), array(Session::$driver, 'close'), 
                array(Session::$driver, 'read'), array(Session::$driver, 'write'), 
                array(Session::$driver, 'destroy'), array(Session::$driver, 'gc'));
        }

        // 验证Session名称
        if( ! preg_match('~^(?=.*[a-z])[a-z0-9_]++$~iD', Session::$config['name']))
        {
            throw new Session_Exception('invalid_session_name', Session::$config['name']);
        }

        // 会话变量名称,这也将是会话变量的cookie的名称
        session_name(Session::$config['name']);

        if(is_dir(RUNTIME."_sessions"))
        {
            session_save_path(RUNTIME."_sessions");
        }

        // 设置会话变量的cookie参数
        session_set_cookie_params(
            Session::$config['expiration'],
            QuickPHP::config('cookie')->path,
            QuickPHP::config('cookie')->domain,
            QuickPHP::config('cookie')->secure,
            QuickPHP::config('cookie')->httponly);

        session_start();

        // 把session_id设置到会话变量中
        $_SESSION['session_id'] = session_id();

        // 设置默认值
        if( ! isset($_SESSION['_kf_flash_']))
        {
            $_SESSION['total_hits'] = 0;
            $_SESSION['_kf_flash_'] = array();
            $_SESSION['user_agent'] = Session::$user_agent;
            $_SESSION['ip_address'] = request::ip_address();
        }

        // 设置flash变量
        Session::$flash = $_SESSION['_kf_flash_'];

        // 计算总触发数
        $_SESSION['total_hits'] += 1;

        // 检验数据只有在支安打之后的一个
        if($_SESSION['total_hits'] > 1)
        {
            foreach (Session::$config['validate'] as $valid)
            {
                switch ($valid)
                {
                    case 'user_agent' :
                        if($_SESSION[$valid] !== Session::$user_agent)
                        {
                            return $this->create();
                        }

                    break;
                    
                    case 'ip_address' :
                        if($_SESSION[$valid] !== request::$valid())
                        {
                            return $this->create();
                        }

                    break;
                    
                    case 'expiration' :
                        if(time() - $_SESSION['last_activity'] > ini_get('session.gc_maxlifetime'))
                        {
                            return $this->create();
                        }

                    break;
                }
            }
        }

        $this->expire_flash();

        // 更新最新的活动
        $_SESSION['last_activity'] = time();

        // 设置新数据
        Session::set($vars);

        return true;
    }

    /**
     * 重构全局会话变量的id。
     *
     * @return  void
     */
    public function regenerate()
    {
        if(Session::$config['driver'] === 'native')
        {
            session_regenerate_id(true);
            $_SESSION['session_id'] = session_id();
        }
        else
        {
            $_SESSION['session_id'] = Session::$driver->regenerate();
        }

        $name = session_name();

        if(isset($_COOKIE[$name]))
        {
            $_COOKIE[$name] = $_SESSION['session_id'];
        }
    }

    /**
     * 销毁当前的会话变量。
     *
     * @return  void
     */
    public function destroy()
    {
        if(session_id() !== '')
        {
            $name = session_name();
            session_destroy();
            $_SESSION = array();
            cookie::delete($name);
        }
    }

    /**
     * Session 写入操作之后事件
     *
     * @return  void
     */
    public function write_close()
    {
        if(Session::$run === null)
        {
            Session::$run = true;
            $this->expire_flash();
            session_write_close();
        }
    }

    /**
     * 会话变量设置方法。
     *
     * @param   string|array  
     * @param   mixed         
     * @return  void
     */
    public function set($keys, $val = false)
    {
        if(empty($keys))
        {
            return false;
        }

        if( ! is_array($keys))
        {
            $keys = array($keys => $val);
        }

        foreach ($keys as $key => $val)
        {
            if(isset(Session::$protect[$key]))
            {
                continue;
            }

            $_SESSION[$key] = $val;
        }

        return true;
    }

    /**
     * 设定一个闪光的变量。
     *
     * @param   string|array  key, or array of values
     * @param   mixed         value (if keys is not an array)
     * @return  void
     */
    public function set_flash($keys, $val = false)
    {
        if(empty($keys))
        {
            return false;
        }

        if( ! is_array($keys))
        {
            $keys = array($keys => $val);
        }

        foreach ($keys as $key => $val)
        {
            if($key == false)
            {
                continue;
            }

            Session::$flash[$key] = 'new';
            Session::set($key, $val);
        }

        return true;
    }

    /**
     * 清洁你、多或所有闪光变量。
     *
     * @param   string  变量的键
     * @return  void
     */
    public function keep_flash($keys = null)
    {
        $keys = ($keys === null) ? array_keys(Session::$flash) : func_get_args();

        foreach ($keys as $key)
        {
            if(isset(Session::$flash[$key]))
            {
                Session::$flash[$key] = 'new';
            }
        }
    }

    /**
     * 期满老闪光数据和删除它从会议。
     *
     * @return  void
     */
    public function expire_flash()
    {
        if(Session::$run === true)
        {
            return;
        }

        if( ! empty(Session::$flash))
        {
            foreach (Session::$flash as $key => $state)
            {
                if($state === 'old')
                {
                    unset(Session::$flash[$key], $_SESSION[$key]);
                }
                else
                {
                    Session::$flash[$key] = 'old';
                }
            }
        }

        $run = true;
    }

    /**
     * 得到一个变量。 进入子阵key.subkey并支持。
     *
     * @param   string  变量的键
     * @param   mixed   默认值
     * @return  mixed   从会话中取得变量，如果没有取得则返回这个默认值.
     */
    public function get($key = false, $default = false)
    {
        if(empty($key))
        {
            return $_SESSION;
        }

        $result = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        return ($result === null) ? $default : $result;
    }

    /**
     * 取得一个会话变量,删除原数据。
     *
     * @param   string  变量的键
     * @param   mixed   默认值
     * @return  mixed   从会话中取得变量，如果没有取得则返回这个默认值.
     */
    public function get_once($key, $default = false)
    {
        $return = Session::get($key, $default);
        Session::delete($key);
        return $return;
    }

    /**
     * 删除一个或多个会话变量。
     *
     * // 删除单个变量
     * $session->delete('var1');
     *
     * // 删除多个变量
     * $session->delete('var1', 'var2', 'var3' ... );
     *
     * @param   string  变量的键
     * @return  void
     */
    public function delete($keys = null)
    {
        $args = func_get_args();

        foreach ($args as $key)
        {
            if(isset(Session::$protect[$key]))
            {
                continue;
            }

            unset($_SESSION[$key]);
        }
    }
}