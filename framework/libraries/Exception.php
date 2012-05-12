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
 * @package     Exception
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Exception.php 8604 2011-12-22 04:40:40Z bopo $
 */
class QuickPHP_Exception extends Exception
{

    /**
     * @var  array  PHP error code => human readable name
     */
    public static $php_errors = array(
        E_ERROR              => 'Fatal Error',
        E_USER_ERROR         => 'User Error',
        E_PARSE              => 'Parse Error',
        E_WARNING            => 'Warning',
        E_USER_WARNING       => 'User Warning',
        E_STRICT             => 'Strict',
        E_NOTICE             => 'Notice',
        E_RECOVERABLE_ERROR  => 'Recoverable Error',
    );

    /**
     * @var  string  error rendering view
     */
    public static $error_view = 'error';

    /**
     * Creates a new translated exception.
     *
     * throw new QuickPHP_Exception('Something went terrible wrong, :user', array(':user' => $user));
     *
     * @param   string          error message
     * @param   array           translation variables
     * @param   integer|string  the exception code
     * @return  void
     */
    public function __construct($message, array $variables = NULL, $code = 0)
    {
        $message = explode(".", $message);
        $message = QuickPHP::message(current($message), end($message));

        if(is_array($variables))
            foreach($variables as $key => $val)
                $variable["{{$key}}"] = $val;

        // E_DEPRECATED only exists in PHP >= 5.3.0
        if (defined('E_DEPRECATED'))
            QuickPHP_Exception::$php_errors[E_DEPRECATED] = 'Deprecated';

        // Save the unmodified code
        $this->code = $code;

        if(isset($variable))
            $message = strtr($message, $variable);

        parent::__construct($message, (int) $code);
    }

    /**
     * Magic object-to-string method.
     *
     *     echo $exception;
     *
     * @uses    QuickPHP_Exception::text
     * @return  string
     */
    public function __toString()
    {
        return QuickPHP_Exception::text($this);
    }

    /**
     * Inline exception handler, displays the error message, source of the
     * exception, and the stack trace of the error.
     *
     * @uses    QuickPHP_Exception::text
     * @param   object   exception object
     * @return  boolean
     */
    public static function handler(Exception $e)
    {
        try
        {
            $type    = get_class($e);
            $code    = $e->getCode();
            $message = $e->getMessage();
            $file    = $e->getFile();
            $line    = $e->getLine();
            $trace   = $e->getTrace();

            if ($e instanceof ErrorException)
            {
                // Use the human-readable error name
                if (isset(QuickPHP_Exception::$php_errors[$code]))
                    $code = QuickPHP_Exception::$php_errors[$code];

                if (version_compare(PHP_VERSION, '5.3', '<'))
                {
                    // Workaround for a bug in ErrorException::getTrace() that exists in
                    // all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
                    for ($i = count($trace) - 1; $i > 0; --$i)
                    {
                        if (isset($trace[$i - 1]['args']))
                        {
                            // Re-position the args
                            $trace[$i]['args'] = $trace[$i - 1]['args'];

                            // Remove the args
                            unset($trace[$i - 1]['args']);
                        }
                    }
                }
            }

            // Create a text version of the exception
            $error = QuickPHP_Exception::text($e);

            if (is_object(QuickPHP::$log))
            {
                // Add this exception to the log
                QuickPHP::$log->add(Log::ERROR, $error);

                // Make sure the logs are written
                QuickPHP::$log->write();
            }

            if (QuickPHP::$is_cli)
                exit($error);

            if ( ! headers_sent())
            {
                // Make sure the proper http header is sent
                $http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;
                header('Content-Type: text/html; charset='.QuickPHP::$charset, TRUE, $http_header_status);
            }

            if (request::is_ajax()) // 后续增加 firephp
                exit(PHP_EOL.$error.PHP_EOL);

            ob_start();

            if ($view_file = QuickPHP::find('errors', QuickPHP_Exception::$error_view))
                include $view_file;
            else
                throw new QuickPHP_Exception('not exist: views/{0}', array(QuickPHP_Exception::$error_view));

            echo ob_get_clean();
            exit(0);
        }
        catch (Exception $e)
        {
            ob_get_level() and ob_clean();
            echo QuickPHP_Exception::text($e), PHP_EOL;
            exit(0);
        }
    }

    /**
     * Get a single line of text representing the exception:
     *
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