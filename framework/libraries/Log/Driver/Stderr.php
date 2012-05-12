<?php defined('SYSPATH') or die('No direct script access.');
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