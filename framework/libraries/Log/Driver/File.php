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
 * 日志存储类，文件写入类
 *
 * @category   QuickPHP
 * @package    Log
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
class QuickPHP_Log_Driver_File extends Log_Abstract
{

    // 存储日志文件的路径
    protected $_directory;

    /**
     * 初始化相应参数
     *
     * $writer = new QuickPHP_Log_File($directory);
     *
     * @param   string  存储日志文件的路径
     * @return  void
     */
    public function __construct($directory)
    {
        if( ! is_dir($directory) or ! is_writable($directory))
        {
            throw new Log_Exception('Directory {0} must be writable', array(debug::path($directory)));
        }

        $this->_directory = realpath($directory) . DIRECTORY_SEPARATOR;
    }

    /**
     * 写入日志内容
     *
     * $writer->write($messages);
     *
     * @param   array   日志内容
     * @return  void
     */
    public function write(array $messages)
    {
        $directory = $this->_directory . date('Y') . DIRECTORY_SEPARATOR;

        if( ! is_dir($directory))
        {
            mkdir($directory, 0777);
            chmod($directory, 0777);
        }

        $directory .= date('m') . DIRECTORY_SEPARATOR;

        if( ! is_dir($directory))
        {
            mkdir($directory, 0777);
            chmod($directory, 0777);
        }

        $filename = $directory . date('d') . EXT;

        if( ! file_exists($filename))
        {
            file_put_contents($filename, QuickPHP::FILE_SECURITY . ' ?>' . PHP_EOL);
            chmod($filename, 0666);
        }

        $format = 'time --- type: body';

        foreach ($messages as $message)
        {
            file_put_contents($filename, PHP_EOL . strtr($format, $message), FILE_APPEND);
        }
    }
}