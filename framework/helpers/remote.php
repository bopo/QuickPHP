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
 * QuickPHP 远程URL助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  remote
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: remote.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_remote
{
    /**
     * 获得远程HTTP状态
     *
     * @param string 需要获取远程的url
     */
    public static function status($url)
    {
        if ( ! valid::url($url, 'http'))
        {
            return false;
        }

        $url = parse_url($url);

        if (empty($url['path']))
        {
            $url['path'] = '/';
        }

        $remote = fsockopen($url['host'], 80, $errno, $errstr, 5);

        if ( ! is_resource($remote))
        {
            return FALSE;
        }

        $CRLF = "\r\n";

        fwrite($remote, 'HEAD ' . $url['path'] . ' HTTP/1.0' . $CRLF);
        fwrite($remote, 'Host: ' . $url['host'] . $CRLF);
        fwrite($remote, 'Connection: close' . $CRLF);
        fwrite($remote, 'User-Agent: QuickPHP Framework (+http://www.quickphp.net/)' . $CRLF);
        fwrite($remote, $CRLF);

        while ( ! feof($remote))
        {
            $line = trim(fgets($remote, 512));

            if ($line !== '' AND preg_match('#^HTTP/1\.[01] (\d{3})#', $line, $matches))
            {
                $response = (int) $matches[1];
                break;
            }
        }

        fclose($remote);

        return isset($response) ? $response : false;
    }

}