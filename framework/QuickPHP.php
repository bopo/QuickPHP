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
 * QuickPHP 核心类，应用程序的调度工作
 *
 * @category   QuickPHP
 * @package    QuickPHP
 * @copyright  Copyright (c) 2010 http://quickphp.net All rights reserved.
 * @license    http://framework.quickphp.net/license/new-bsd     New BSD License
 */

class QuickPHP
{
    /**
     *  @const string Base path to AOL CDN
     */
    const VERSION   = '1.0.2';

    /**
     *  @const string Base path to AOL CDN
     */
    const CODENAME  = 'snail';

    /**
     *  @const string 日志消息类型
     */
    const ERROR     = 'ERROR';

    /**
     *  @const string 日志消息类型
     */
    const DEBUG     = 'DEBUG';

    /**
     *  @const string 日志消息类型
     */
    const INFO      = 'INFO';

    /**
     *  @const string PHP文件开头安全检查代码
     */
    const FILE_SECURITY = '<?php defined(\'SYSPATH\') or die(\'No direct script access.\');';

    /**
     * PHP 错误码
     *
     * @var array
     */
    public static $php_errors = array(
        E_ERROR             => 'Fatal Error',
        E_USER_ERROR        => 'User Error',
        E_PARSE             => 'Parse Error',
        E_WARNING           => 'Warning',
        E_USER_WARNING      => 'User Warning',
        E_STRICT            => 'Strict',
        E_NOTICE            => 'Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error'
    );

    /**
     * @var  boolean  判断是否命令行环境
     */
    public static $is_cli = false;

    /**
     * @var  boolean  判断是否是windows环境
     */
    public static $is_windows = false;

    /**
     * @var  boolean  判断是否开启魔术引号
     */
    public static $magic_quotes = false;

    /**
     * @var  boolean  日志记录错误以及异常信息
     */
    public static $log_error = false;

    /**
     * @var  string  输入和输出的编码
     */
    public static $charset = 'UTF-8';

    /**
     * @var  string  网站域名
     */
    public static $domain = '/';

    /**
     * @var  string  默认协议
     */
    public static $protocol = 'http';

    /**
     * @var  string  应用的索引文件
     */
    public static $frontend = 'index.php';

    /**
     * @var  string  引用的url后缀
     */
    public static $url_suffix = 'html';

    /**
     * @var  string  高速缓存目录
     */
    public static $cache_dir;

    /**
     * @var  boolean  是否开启高速缓存
     */
    public static $caching = false;

    /**
     * @var  boolean  是否开启基础测试
     */
    public static $profiling = true;

    /**
     * @var  boolean  是否开启错误管理
     */
    public static $errors = true;

    /**
     * @var  boolean  是否安全模式
     */
    public static $safe_mode = null;

    /**
     * @var  array  在shutdown显示错误类型
     */
    public static $shutdown_errors = array(E_PARSE, E_ERROR, E_USER_ERROR, E_COMPILE_ERROR);

    /**
     * @var  object   日志存储接口对象
     */
    public static $log;

    /**
     * @var  object  配置接口对象
     */
    public static $config;

    /**
     * @var  object  路由接口对象
     */
    public static $router;

    /**
     * @var bool 判断是否初始化系统
     */
    protected static $_init = false;

    /**
     * @var array 加载的错误消息集合
     */
    protected static $_messages = array();

    /**
     * @var array 自动加载的路径
     */
    protected static $_paths = array(APPPATH, SYSPATH);

    /**
     * @var array 已加载缓存文件的目录集合
     */
    protected static $_files = array();

    /**
     * @var bool 高速缓存修改的状态
     */
    protected static $_files_changed = false;

    /**
     * @var string 本地语言(待定)
     */
    public static $locale  = 'zh-CN';
    public static $_locale = array();

    /**
     * @var array 配置信息集合
     */
    public static $_config;

    /**
     * 构建PHP的初始化环境。
     * 增加异常错误处理机制，增加自动加载方法。
     * 通过传入的参数来开启基准测试。
     * 为了安全起见,过滤输入数据，防止不安全数据进入应用
     *
     * @see http://www.php.net/globals
     *
     * @return void
     */
    public function instance(array $settings = null)
    {
        if(QuickPHP::$_init)
        {
            return QuickPHP::$_init;
        }

        // 判断配置是否开启基准测试
        if(isset($settings['locale']))
        {
            QuickPHP::$locale = $settings['locale'];
        }

        // 判断配置是否开启基准测试
        if(isset($settings['profiling']))
        {
            QuickPHP::$profiling = (bool) $settings['profiling'];
        }

        // 如果框架开启基准测试，则开始一个测试
        if(QuickPHP::$profiling === true)// && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) != 'XMLHTTPREQUEST')
        {
            $benchmark = Profiler::start('QuickPHP', 'QuickPHP::' . __FUNCTION__ );
        }

        if(defined('E_DEPRECATED'))
        {
            QuickPHP::$php_errors[E_DEPRECATED] = 'Deprecated';
        }

        // 判读是否显示错误
        if(isset($settings['log_error']))
        {
            QuickPHP::$log_error = (bool) $settings['log_error'];
        }

        // 判读是否显示错误
        if(isset($settings['errors']))
        {
            QuickPHP::$errors = (bool) $settings['errors'];
        }

        // 如果开启了错误提示，自定义异常和错误管理器
        if(QuickPHP::$errors === true)
        {
            set_exception_handler(array('QuickPHP', 'exception_handler'));
            set_error_handler(array('QuickPHP', 'error_handler'));
        }

        // 注册shutdown函数
        register_shutdown_function(array('QuickPHP', 'shutdown_handler'));

        // 如果已经开启了register_globals，销毁 register_globals 所影响的设置
        if(ini_get('register_globals'))
        {
            QuickPHP::globals();
        }

        // 判读是否以命令行方式运行
        QuickPHP::$is_cli = (PHP_SAPI === 'cli');

        // 判读是否是windows操作系统
        QuickPHP::$is_windows = (DIRECTORY_SEPARATOR === '\\');

        // 如果是命令行环境，则开启缓冲
        if( ! QuickPHP::$is_cli)
        {
            ob_start();
        }

        // 判断是否设置系统高速缓存目录，否则为默认值，系统高速缓存用于存储配置器数据以及基准测试数据
        if (isset($settings['cache_dir']))
        {
            if ( ! is_dir($settings['cache_dir']))
            {
                try
                {
                    mkdir($settings['cache_dir'], 0755, true);
                    chmod($settings['cache_dir'], 0755);
                }
                catch (Exception $e)
                {
                    throw new QuickPHP_Exception('Could not create cache directory {0}', array(debug::path($settings['cache_dir'])));
                }
            }

            QuickPHP::$cache_dir = realpath($settings['cache_dir']);
        }
        else
        {
            QuickPHP::$cache_dir = RUNTIME . '_caching';
        }

        // 判断系统高速缓存目录是否可写
        if( ! is_writable(QuickPHP::$cache_dir))
        {
            throw new QuickPHP_Exception('Directory "{0}" must be writable', array(debug::path(QuickPHP::$cache_dir)));
        }
        // 设置默认高速缓存周期
        if (isset($settings['url_suffix']))
        {
            QuickPHP::$url_suffix = $settings['url_suffix'];
        }

        // 设置默认高速缓存周期
        if (isset($settings['cache_life']))
        {
            QuickPHP::$cache_life = (int) $settings['cache_life'];
        }

        // 判断系统高速缓存开启是否设置
        if(isset($settings['caching']))
        {
            QuickPHP::$caching = (bool) $settings['caching'];
        }

        // 如果开启则缓存所有导入数据的文件
        if(QuickPHP::$caching === true)
        {
            QuickPHP::$_files = QuickPHP::cache('QuickPHP::find()');
        }

        // 判断是否设置了字符编码
        if(isset($settings['charset']))
        {
            QuickPHP::$charset = strtolower($settings['charset']);
        }

        // 判断是否开启了mbstring扩展，如果开启则设置mbstring内部编码
        if(function_exists('mb_internal_encoding'))
        {
            mb_internal_encoding(QuickPHP::$charset);
        }

        // 判断是否设置域名
        if(isset($settings['domain']))
        {
            QuickPHP::$domain = rtrim($settings['domain'], '/') . '/';
        }

        // 判断是否设置系统入口文件名
        if(isset($settings['frontend']))
        {
            QuickPHP::$frontend = trim($settings['frontend'], '/');
        }

        // 判断魔术引号状态, 5.3以上版本默认已经关闭魔术引号
        QuickPHP::$magic_quotes = (bool) get_magic_quotes_gpc();

        // 判断是否运行于安全模式下
        QuickPHP::$safe_mode = (bool) ini_get('safe_mode');

        // 过滤输入数据，防止不安全数据进入应用
        $_GET     = QuickPHP::sanitize($_GET);
        $_POST    = QuickPHP::sanitize($_POST);
        $_COOKIE  = QuickPHP::sanitize($_COOKIE);
        $_REQUEST = QuickPHP::sanitize($_REQUEST);

        // 关闭基准测试
        if(isset($benchmark))
        {
            Profiler::stop($benchmark);
        }

        QuickPHP::$_init = new QuickPHP();

        return QuickPHP::$_init;
    }

    /**
     * 销毁 register_globals 所影响的设置。
     *
     * if (ini_get('register_globals'))
     *      QuickPHP::globals();
     *
     * @return  void
     */
    public static function globals()
    {
        if(isset($_REQUEST['GLOBALS']) or isset($_FILES['GLOBALS']))
        {
            exit("Global variable overload attack detected! Request aborted.\n");
        }

        $global_variables = array_keys($GLOBALS);
        $global_variables = array_diff($global_variables, array('_COOKIE', '_ENV', '_GET', '_FILES', '_POST', '_REQUEST', '_SERVER', '_SESSION', 'GLOBALS'));

        foreach ($global_variables as $name)
        {
            unset($GLOBALS[$name]);
        }
    }

    /**
     * Cleans up the environment:
     *
     * - Restore the previous error and exception handlers
     * - Destroy the QuickPHP::$log and QuickPHP::$config objects
     *
     * @return  void
     */
    public static function reset()
    {
        if (QuickPHP::$_init)
        {
            spl_autoload_unregister(array('QuickPHP', 'autoloader'));

            if (QuickPHP::$errors)
            {
                restore_error_handler();
                restore_exception_handler();
            }

            QuickPHP::$log      = null;
            QuickPHP::$_init    = false;
            QuickPHP::$config   = null;
            QuickPHP::$_files   = array();
            QuickPHP::$_paths   = array(APPPATH, SYSPATH);
            QuickPHP::$_modules = array();
            QuickPHP::$_files_changed = false;
        }
    }

    /**
     * 过滤输入数据，防止不安全数据进入应用
     *
     * - 如果启用魔术引号则反引用字符
     * - 标准化所有换行符为LF
     *
     * @param   mixed  变量
     * @return  mixed  序列化后的值
     */
    public static function sanitize($value)
    {
        if(is_array($value) or is_object($value))
        {
            foreach ($value as $key => $val)
            {
                $value[$key] = QuickPHP::sanitize($val);
            }
        }
        elseif(is_string($value))
        {
            if(QuickPHP::$magic_quotes === true)
            {
                $value = stripslashes($value);
            }

            if(strpos($value, "\r") !== false)
            {
                $value = str_replace(array("\r\n", "\r"), "\n", $value);
            }
        }

        return $value;
    }

    /**
     * QuickPHP 框架控制器调度
     *
     * @return  object
     */
    public function dispatch()
    {
        if(QuickPHP::$_init != null)
        {
            try
            {
                // 加载控制器文件,并反射这个控制器
                require_once QuickPHP::route()->controller_path;
                $class = new ReflectionClass(ucfirst(QuickPHP::route()->get('controller')) . '_Controller');
            }
            catch(ReflectionException $e)
            {
                throw new QuickPHP_Exception('route.not_controller', $e->getMessage());
            }

            // 判断控制器文件是否是抽象类，以及开启了生产模式，ALLOW_PRODUCTION 常量设置为否
            if($class->isAbstract() or (IN_PRODUCTION and $class->getConstant('ALLOW_PRODUCTION') == false))
            {
                throw new QuickPHP_Exception('route.controller_is_not_allowed', QuickPHP::route()->segments);
            }

            $controller = $class->newinstance();

            try
            {
                $method = QuickPHP::route()->get('method');

                if($method[0] === '_')
                {
                    throw new QuickPHP_Exception('route.method_is_not_exists', array($method));
                }

                $method = $class->getMethod($method);

                if($method->isProtected() or $method->isPrivate())
                {
                    throw new QuickPHP_Exception('route.protected_or_private_controller_method', array($method));
                }

                $arguments = QuickPHP::route()->get('arguments');
            }
            catch(ReflectionException $e)
            {
                $method    = $class->getMethod('__call');
                $arguments = array(QuickPHP::route()->get('method'), QuickPHP::route()->get('arguments'));
            }

            $class->getMethod('before')->invoke($controller);
            $method->invokeArgs($controller, $arguments);
            $class->getMethod('after')->invoke($controller);
        }
    }

    /**
     * 自定义自动加载器
     * QuickPHP_为前缀的类名为框架自带类，就是存放在framework目录中的文件.
     * 可以直接省略前缀使用例如 QuickPHP_url::base(),可以直接简化为url::base().
     * 没有QuickPHP开头的类名则为自定义类名.
     * 同名类的加载顺序为，APPPATH，MODPATH，SYSPATH。依次从前向后。
     * 说明APPPATH和MODPATH的url类，和QuickPHP_url视为同名
     *
     * @param   string  类名
     * @return  bool
     */
    public static function autoloader($class)
    {
        if(class_exists($class))
        {
            return true;
        }

        $cache_key  = "autoloader($class)";
        $segments   = explode("_", $class);
        $system     = false;

        if(($segments[0]) == 'QuickPHP')
        {
            array_shift($segments);
            $system = true;
        }

        $suffix = count($segments) > 1 ? end($segments) : null;

        // 产品模式下使用高速缓存保存加载路径
        $class_file = QuickPHP::cache($cache_key);

        if(!empty($class_file) and file_exists($class_file))
        {
            require_once $class_file;

            if(class_exists($class) || interface_exists($class))
            {
                return true;
            }
            elseif((stripos($class_file, SYSPATH) == 0))
            {
                if(strtolower($suffix) === 'abstract')
                {
                    $alis = "abstract class $class extends QuickPHP_$class{}";
                }
                elseif(strtolower($suffix) === 'interface')
                {
                    $alis = "interface $class extends QuickPHP_$class{}";
                }
                else
                {
                    $alis = "class $class extends QuickPHP_$class{}";
                }
            }

            return (bool) eval($alis);
        }

        if($suffix === 'Controller')
        {
            array_pop($segments);
            $directory = 'controllers';
            $filepath  = $directory .'/'. strtolower(implode("/", $segments)) . EXT;
        }
        elseif($suffix === 'Model')
        {
            array_pop($segments);
            $directory = 'models';
            $filepath  = $directory .'/'. strtolower(implode("/", $segments)) . EXT;
        }
        else
        {
            $directory = ($segments[0] < 'a') ? 'libraries' : 'helpers';
            $filepath  = $directory .'/'. (implode("/", $segments)) . EXT;
        }

        if($system === false)
        {
            if(file_exists(APPPATH . $filepath))
            {
                require_once APPPATH . $filepath;

                if(IN_PRODUCTION)
                {
                    QuickPHP::cache($cache_key, APPPATH . $filepath);
                }
            }
            elseif(file_exists(SYSPATH . $filepath))
            {
                require_once SYSPATH . $filepath;

                if(IN_PRODUCTION)
                {
                    QuickPHP::cache($cache_key, SYSPATH . $filepath);
                }

                if($system === false)
                {
                    if(strtolower($suffix) === 'abstract')
                    {
                        $alis = "abstract class $class extends QuickPHP_$class{}";
                    }
                    elseif(strtolower($suffix) === 'interface')
                    {
                        $alis = "interface $class extends QuickPHP_$class{}";
                    }
                    else
                    {
                        $alis = "class $class extends QuickPHP_$class{}";
                    }

                    eval($alis);
                }
            }
        }
        else
        {
            if(file_exists(SYSPATH . $filepath))
            {
                require_once SYSPATH . $filepath;

                if(IN_PRODUCTION)
                {
                    QuickPHP::cache($cache_key, SYSPATH . $filepath);
                }
            }
        }

        return true;
    }

    /**
     * 返回当前已经包含的文件路径，包括 APPPATH, SYSPATH.
     *
     * @return  array
     */
    public static function get_include_paths()
    {
        return QuickPHP::$_paths;
    }

    /**
     * QuickPHP::find  的别名
     *
     * @param   string   目录名
     * @param   string   含有子目录和文件名
     * @param   string   扩展名
     * @param   boolean  是否返回数组形式的文件列表?
     * @return  array    如果$array为真返回数组形式的文件列表
     * @return  string   单文件路径
     */
    public static function locate($dir, $file, $ext = null, $array = false)
    {
        return self::find($dir, $file, $ext, $array);
    }

    /**
     * 寻找文件路径
     *
     * // 返回绝对路径 views/template.php
     * QuickPHP::find('views', 'template');
     *
     * // 返回绝对路径 media/css/style.css
     * QuickPHP::find('media', 'css/style', 'css');
     *
     * // Returns an array of all the "mimes" configuration file
     * QuickPHP::find('config', 'mimes');
     *
     * @param   string   目录名
     * @param   string   含有子目录和文件名
     * @param   string   扩展名
     * @param   boolean  是否返回数组形式的文件列表?
     * @return  array    如果$array为真返回数组形式的文件列表
     * @return  string   单文件路径
     */
    public static function find($dir, $file, $ext = null, $array = false)
    {
        $ext  = ($ext === null) ? EXT : '.' . $ext;
        $path = $dir . '/' . $file . $ext;

        if(QuickPHP::$caching === true and isset(QuickPHP::$_files[$path]))
        {
            return QuickPHP::$_files[$path];
        }

        if(QuickPHP::$profiling === true and class_exists('Profiler', false))
        {
            $benchmark = Profiler::start('QuickPHP', 'QuickPHP::' . __FUNCTION__ );
        }

        if($array OR $dir === 'config' OR $dir === 'locale')
        {
            $paths = array_reverse(QuickPHP::$_paths);
            $found = array();

            foreach ($paths as $dir)
            {
                if(is_file($dir . $path))
                {
                    $found[] = $dir . $path;
                }
            }
        }
        else
        {
            $found = false;

            foreach (QuickPHP::$_paths as $dir)
            {
                if(is_file($dir . $path))
                {
                    $found = $dir . $path;
                    break;
                }
            }
        }

        if(QuickPHP::$caching === true)
        {
            QuickPHP::$_files[$path]  = $found;
            QuickPHP::$_files_changed = true;
        }

        if(isset($benchmark))
        {
            Profiler::stop($benchmark);
        }

        return $found;
    }

    /**
     * 加载一个文件并返回输出
     *
     * $foo = QuickPHP::load('foo.php');
     *
     * @param   string
     * @return  mixed
     */
    public static function load($file)
    {
        try
        {
            return include $file;
        }
        catch (Exception $e)
        {
            throw new QuickPHP_Exception('Could not find file {0}', array($file));
        }

    }

    /**
     * 创建一组新的日志对象
     *
     * @param   string   组名
     * @return  QuickPHP_Log
     */
    public static function log($group)
    {
        if( ! isset(QuickPHP::$log))
        {
            QuickPHP::$log = Log::instance()->attach(new Log_Driver_File());
        }

        return QuickPHP::$_log;
    }

    /**
     * 创建一组新的配置对象
     *
     * @param   string   组名
     * @return  QuickPHP_Config
     */
    public static function config($group)
    {
        $cache_key = "QuickPHP::cache()";

        if (QuickPHP::$_config == null)
        {
            QuickPHP::$config = QuickPHP::cache($cache_key);
        }

        if( ! isset(QuickPHP::$_config[$group]) or empty(QuickPHP::$_config[$group]))
        {
            QuickPHP::$config          = Config::instance()->attach(new Config_Driver_File());
            QuickPHP::$_config[$group] = QuickPHP::$config->load($group);
            QuickPHP::cache($cache_key, QuickPHP::$_config);
        }

        return QuickPHP::$_config[$group];
    }

    /**
     * 返回路由处理器
     *
     * @return  QuickPHP_Router
     */
    public static function route()
    {
        if(empty(QuickPHP::$router))
        {
            QuickPHP::$router = Router::instance();
        }

        return QuickPHP::$router;
    }

    /**
     * 内核级的高速缓存，用以存储配置器，基准测试等数据
     *
     * // 存储高速缓存
     * QuickPHP::cache('foo', 'hello, world');
     *
     * // 读取高速缓存
     * $foo = QuickPHP::cache('foo');
     *
     * [ref-var]: http://php.net/var_export
     *
     * @throws  QuickPHP_Exception
     * @param   string   缓存名称
     * @param   mixed    缓存数据
     * @param   integer  有效期限，单位秒
     * @return  mixed    返回字符串、数组或者空
     */
    public static function cache($name, $data = null, $lifetime = 60)
    {
        QuickPHP::$cache_dir = empty(QuickPHP::$cache_dir) ? RUNTIME . '_caching' : QuickPHP::$cache_dir;

        $file = sha1("QuickPHP::cache({$name})") . '.txt';
        $dir  = rtrim(QuickPHP::$cache_dir, '/') . '/' . strtoupper(substr($file,0,2)) . '/';

        if ($data === null)
        {
            if (is_file($dir . $file))
            {
                if ((time() - filemtime($dir . $file)) < $lifetime)
                {
                    try
                    {
                        return unserialize(file_get_contents($dir . $file));
                    }
                    catch (Exception $e)
                    {
                        return false;
                    }
                }
                else
                {
                    try
                    {
                        unlink($dir.$file);
                    }
                    catch (Exception $e)
                    {
                        // Cache has mostly likely already been deleted,
                        // let return happen normally.
                    }
                }
            }

            return null;
        }

        if ( ! is_dir($dir))
        {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        $data = serialize($data);

        try
        {
            return (bool) file_put_contents($dir.$file, $data, LOCK_EX);
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * 从消息文件中取得一条消息，消息文件均存储在messages目录
     *
     * // 从 messages/text.php 获取 "username" 的值
     * $username = QuickPHP::message('text', 'username');
     *
     * @param   string  要获取消息的文件名
     * @param   string  要获得的消息键的路径
     * @param   mixed   默认值，如果路径不存在则返回该值
     * @return  string  指定路径的消息字符串
     * @return  array   当没有指定路径,则返回完整数组的消息列表
     * @uses    arr::merge
     * @uses    arr::path
     */
    public static function message($file, $path = null, $default = null)
    {
        $cache_key = "QuickPHP::message()";

        if (QuickPHP::$_messages == null)
        {
            QuickPHP::$_messages = QuickPHP::cache($cache_key);
        }

        if( ! isset(QuickPHP::$_messages[$file]) or empty(QuickPHP::$_messages[$file]))
        {
            QuickPHP::$_messages[$file] = array();

            $files = QuickPHP::find('messages', $file);

            if(!empty($files))
            {
                if(is_array($files))
                {
                    foreach ($files as $val)
                    {
                        QuickPHP::$_messages[$file] = array_merge(QuickPHP::$_messages[$file], QuickPHP::load($val));
                    }
                }
                else
                {
                    QuickPHP::$_messages[$file] = array_merge(QuickPHP::$_messages[$file], QuickPHP::load($files));
                }
            }

            QuickPHP::cache($cache_key, QuickPHP::$_messages);
        }

        if($path === null)
        {
            return QuickPHP::$_messages[$file];
        }

        return arr::path(QuickPHP::$_messages[$file], $path, $default);
    }

    public static function lang($file, $lang = null)
    {
        if( ! isset(QuickPHP::$_locale[$lang]))
        {
            QuickPHP::$_locale[$file] = array();

            $files = QuickPHP::find('locale', $file);

            if($files)
            {
                foreach ($files as $msg)
                {
                    QuickPHP::$_locale[$file] = array_merge(QuickPHP::$_locale[$file], QuickPHP::load($msg));
                }
            }
        }

        if($path === null)
        {
            return QuickPHP::$_locale[$file];
        }

        return arr::path(QuickPHP::$_locale[$file], $path, $default);
    }

    /**
     * 系统自定义的错误管理器, 将所有 PHP 错误转向 ErrorExceptions.
     *
     * @throws  ErrorException
     * @return  true
     */
    public static function error_handler($code, $error, $file = null, $line = null)
    {
        if(error_reporting() & $code)
        {
            throw new ErrorException($error, $code, 0, $file, $line);
        }

        return true;
    }

    /**
     * 系统自定义异常处理器，显示错误信息，异常的来源，和堆栈跟踪的错误等。
     *
     * @uses    QuickPHP::exception_text
     * @param   object   exception object
     * @return  boolean
     */
    public static function exception_handler(Exception $e)
    {
        if(IN_PRODUCTION)
        {
            header('Content-Type: text/html; charset=' . QuickPHP::$charset, true, $code);
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");

            include QuickPHP::find('errors', '404');
            echo ob_get_clean();

            return true;
        }

        return QuickPHP_Exception::handler($e);
    }

    /**
     * 所有程序在执行结束后调用该方法
     *
     * @uses    QuickPHP::exception_handler
     * @return  void
     */
    public static function shutdown_handler()
    {
        if( ! QuickPHP::$_init)
        {
            return true;
        }

        try
        {
            if(QuickPHP::$caching === true and QuickPHP::$_files_changed === true)
            {
                QuickPHP::cache('QuickPHP::find()', QuickPHP::$_files);
            }
        }
        catch(Exception $e)
        {
            QuickPHP::exception_handler($e);
        }

        if(QuickPHP::$is_cli)
        {
            error_get_last();
            exit(1);
        }

        if(QuickPHP::$errors and $error = error_get_last() and in_array($error['type'], QuickPHP::$shutdown_errors))
        {
            ob_get_level() and ob_clean();

            $ErrorException = new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']);

            QuickPHP::exception_handler($ErrorException);

            exit(1);
        }

        if(QuickPHP::$profiling === true and ! headers_sent())
        {
            include QuickPHP::find('errors', 'stats');
        }
    }

}
