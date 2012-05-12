<?php defined('SYSPATH') or die('No direct script access.');
/**
 * STDOUT log writer. Writes out messages to STDOUT.
 *
 * @package    QuickPHP
 * @category   Logging
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2011 QuickPHP Team
 * @license    http://www.quickphp.net/license.html
 */
class QuickPHP_Log_Driver_StdOut extends QuickPHP_Log_Abstract
{
	/**
	 * Writes each of the messages to STDOUT.
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
			fwrite(STDOUT, PHP_EOL.strtr($format, $message));
		}
	}
}