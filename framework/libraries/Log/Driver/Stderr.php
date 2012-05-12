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
 * STDERR log writer. Writes out messages to STDERR.
 *
 * @package    QuickPHP
 * @category   Logging
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2011 QuickPHP Team
 * @license    http://QuickPHPphp.com/license
 */
class QuickPHP_Log_Driver_StdErr extends QuickPHP_Log_Abstract
{
	/**
	 * Writes each of the messages to STDERR.
	 *
	 * $writer->write($messages);
	 *
	 * @param   array   messages
	 * @return  void
	 */
	public function write(array $messages)
	{
		$format = 'time --- type: body';

		foreach ($messages as $message)
		{
			fwrite(STDERR, PHP_EOL.strtr($format, $message));
		}
	}
}