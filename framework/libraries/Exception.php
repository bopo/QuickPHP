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
 * QuickPHP 核心异常处理类
 * @see http://php.net/manual/en/language.exceptions.php
 *
 * @category    QuickPHP
 * @package     Librares
 * @subpackage  Exception
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Config.php 8641 2012-01-05 08:35:39Z bopo $
 */
class QuickPHP_Exception extends Exception
{
    /**
     *
     * @var  array  PHP 错误代码
     */
    public static $php_errors = array(
        E_ERROR             => 'Fatal Error',
        E_USER_ERROR        => 'User Error',
        E_PARSE             => 'Parse Error',
        E_WARNING           => 'Warning',
        E_USER_WARNING      => 'User Warning',
        E_STRICT            => 'Strict',
        E_NOTICE            => 'Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
    );

    /**
     * @var  string  错误信息模板
     */
    public static $error_view = 'error';

    /**
     * 重载异常构造函数.
     *
     * throw new QuickPHP_Exception('出现异常了, {0}', array($user));
     *
     * @param   string          错误信息
     * @param   array           错误变量集
     * @param   integer|string  异常代码
     * @return  void
     */
    public function __construct($message, array $variables = null, $code = 0)
    {

        $directory = rtrim(strtolower(str_replace("Exception", "", get_class($this))), '_');
        $messages  = QuickPHP::message($directory, $message);

        if(is_array($variables))
        {
            foreach($variables as $key => $val)
            {
                $variable["{{$key}}"] = $val;
            }
        }

        $messages = !empty($messages) ? $messages : $message;

        if(isset($variable))
        {
            $messages = strtr($messages, $variable);
        }

        if (defined('E_DEPRECATED'))
        {
            QuickPHP_Exception::$php_errors[E_DEPRECATED] = 'Deprecated';
        }

        $this->code = $code;
        parent::__construct($messages, (int) $code);
    }

    /**
     * 魔术方法.
     *
     * echo $exception;
     *
     * @uses    QuickPHP_Exception::text
     * @return  string
     */
    public function __toString()
    {
        return $this->getTraceAsString();
    }

    /**
     * 异常管理类，展示错误信息，错误行所在源代码的上下内容，以及堆栈信息
     *
     *
     * @uses    QuickPHP_Exception::text
     * @param   object   异常对象
     * @return  boolean
     */
    public static function handler(Exception $e)
    {
        try
        {
            $type    = get_class($e);
            $code    = $e->getCode();
            $file    = $e->getFile();
            $line    = $e->getLine();
            $trace   = $e->getTrace();
            $message = $e->getMessage();

            if ($e instanceof ErrorException)
            {
                if (isset(QuickPHP_Exception::$php_errors[$code]))
                {
                    $code = QuickPHP_Exception::$php_errors[$code];
                }

                if (version_compare(PHP_VERSION, '5.3', '<'))
                {
                    for ($i = count($trace) - 1; $i > 0; --$i)
                    {
                        if (isset($trace[$i - 1]['args']))
                        {
                            $trace[$i]['args'] = $trace[$i - 1]['args'];
                            unset($trace[$i - 1]['args']);
                        }
                    }
                }
            }

            if (is_object(QuickPHP::$log))
            {
                QuickPHP::$log->add(Log::ERROR, QuickPHP_Exception::text($e));
                QuickPHP::$log->write();
            }

            if (QuickPHP::$is_cli)
            {
                exit($e->getTraceAsString());
            }

            if ( ! headers_sent())
            {
                $http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;
                header('Content-Type: text/html; charset='.QuickPHP::$charset, true, $http_header_status);
            }

            if (request::is_ajax())
            {
                require_once "FirePHP.php";
                FirePHP::instance()->error($message,'exception');
                FirePHP::instance()->trace('exception');
                exit($e->getTraceAsString().PHP_EOL);
            }

            ob_start();

            if ($view_file = QuickPHP::find('errors', QuickPHP_Exception::$error_view))
            {
                include $view_file;
            }
            else
            {
                throw new QuickPHP_Exception('not exist: views/{0}', array(QuickPHP_Exception::$error_view));
            }

            echo ob_get_clean();
            exit(0);
        }
        catch (Exception $e)
        {
            ob_get_level() and ob_clean();
            echo $e->getTraceAsString() . PHP_EOL;
            exit(0);
        }
    }

    /**
     * 输出单行错误提示信息
     *
     * 格式如下:
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   object  Exception
     * @return  string
     */
    public static function text(Exception $e)
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e), $e->getCode(), strip_tags($e->getMessage()), debug::path($e->getFile()), $e->getLine());
    }
}
