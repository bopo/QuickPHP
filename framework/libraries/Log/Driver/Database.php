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
 * Syslog log writer.
 *
 * @category   QuickPHP
 * @package    Log
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
class QuickPHP_Log_Database extends Log_Abstract
{

    // The syslog identifier
    protected $_ident;

    protected $_syslog_levels = array(
        'ERROR'    => LOG_ERR,
        'CRITICAL' => LOG_CRIT,
        'STRACE'   => LOG_ALERT,
        'ALERT'    => LOG_WARNING,
        'INFO'     => LOG_INFO,
        'DEBUG'    => LOG_DEBUG);

    /**
     * Creates a new syslog logger.
     *
     * @see http://us2.php.net/openlog
     *
     * @param   string  syslog identifier
     * @param   int     facility to log to
     * @return  void
     */
    public function __construct($ident = 'QuickPHP', $facility = LOG_USER)
    {
        $this->_ident = $ident;
        openlog($this->_ident, LOG_CONS, $facility);
    }

    /**
     * Writes each of the messages into the syslog.
     *
     * @param   array   messages
     * @return  void
     */
    public function write(array $messages)
    {
        foreach ($messages as $message)
        {
            syslog($this->_syslog_levels[$message['type']], $message['body']);
        }
    }

    /**
     * Closes the syslog connection
     *
     * @return  void
     */
    public function __destruct()
    {
        closelog();
    }
}