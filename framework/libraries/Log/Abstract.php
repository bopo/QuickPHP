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
 * QuickPHP 日志读写操作抽象类.
 *
 * @category   QuickPHP
 * @package    Log
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
abstract class QuickPHP_Logger_Abstract
{

	/**
	 * Numeric log level to string lookup table.
	 * @var array
	 */
	protected $_log_levels = array(
		LOG_EMERG   => 'EMERGENCY',
		LOG_CRIT    => 'CRITICAL',
		LOG_ERR     => 'ERROR',
		LOG_WARNING => 'WARNING',
		LOG_NOTICE  => 'NOTICE',
		LOG_INFO    => 'INFO',
		LOG_DEBUG   => 'DEBUG',
	);

    /**
     * 写一个数组的信息.
     *
     * $writer->write($messages);
     *
     * @param   array  messages
     * @return  void
     */
    abstract public function write(array $messages);

    /**
     * Allows the writer to have a unique key when stored.
     * 允许作者都有各自独特的钥匙当储存。
     *
     * echo $writer;
     *
     * @return  string
     */
    final public function __toString()
    {
        return spl_object_hash($this);
    }
}