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
 * 日志消息类,用以将错误信息或者用户指定信息写入日志文件
 * [!!] 这个类没有需要支持的扩展，使用驱动方式,可以自写扩展驱动.
 *
 * @category    QuickPHP
 * @package     Log
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Log.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Log
{

    /**
     * @var  string  时间格式
     */
    public static $timestamp = 'Y-m-d H:i:s';

    /**
     * @var  string  时区
     */
    public static $timezone;

    /**
     * @var mixed 静态实例
     */
    private static $_instance;

    // 临时消息寄存容器
    protected $_messages = array();

    // 日志驱动寄存容器
    protected $_writers  = array();

    /**
     * 静态实例方法.
     *
     * $log = Log::instance();
     *
     * @return  Log
     */
    public static function instance()
    {
        if(Log::$_instance === null)
        {
            Log::$_instance = new Log();
            register_shutdown_function(array(Log::$_instance, 'write'));
        }

        return Log::$_instance;
    }

    /**
     * 指定一个日志驱动，并且设置写入类型
     *
     * $log->attach($writer);
     *
     * @param   object  驱动实例
     * @param   array   写入类型
     * @return  $this
     */
    public function attach(Log_Abstract $writer, array $types = null)
    {
        $this->_writers["{$writer}"] = array('object' => $writer, 'types' => $types);
        return $this;
    }

    /**
     * 分离日志驱动。
     *
     * $log->detach($writer);
     *
     * @param   object  驱动实例
     * @return  $this
     */
    public function detach(Log_Abstract $writer)
    {
        unset($this->_writers["{$writer}"]);
        return $this;
    }

    /**
     * 添加一个消息到日志中。
     *
     * $log->add('error', 'Could not locate user: :user', array(':user' => $username,));
     *
     * @param   string  消息类型
     * @param   string  消息正文
     * @param   array   消息替换参数
     * @return  $this
     */
    public function add($type, $message, array $values = null)
    {
        if(Log::$timezone)
        {
            $time = new DateTime('now', new DateTimeZone(Log::$timezone));
            $time = $time->format(Log::$timestamp);
        }
        else
        {
            $time = date(Log::$timestamp);
        }

        if((bool) $values)
        {
            $message = strtr($message, $values);
        }

        $this->_messages[] = array('time' => $time, 'type' => $type, 'body' => $message);
        return $this;
    }

    /**
     * 写入并且清除所有临时消息.
     *
     * $log->write();
     *
     * @return void
     */
    public function write()
    {
        if(empty($this->_messages))
        {
            return true;
        }

        $messages = $this->_messages;

        foreach ($this->_writers as $writer)
        {
            if(empty($writer['types']))
            {
                $writer['object']->write($messages);
            }
            else
            {
                $filtered = array();

                foreach ($messages as $message)
                {
                    if(in_array($message['type'], $writer['types']))
                    {
                        $filtered[] = $message;
                    }
                }

                $writer['object']->write($filtered);
            }
        }

        $this->_messages = array();
    }
}