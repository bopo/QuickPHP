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
 * QuickPHP 控制客户端网页缓存的header助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: expires.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_expires
{
    /**
     * 设置页面到期时间
     *
     * @param int $seconds 单位秒
     */
    public static function set($seconds = 60)
    {
        if(expires::check_headers())
        {
            $now      =
            $expires  = time();
            $expires += $seconds;

            header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $now));
            header('Expires: ' . gmdate('D, d M Y H:i:s T', $expires));
            header('Cache-Control: max-age=' . $seconds);

            return $expires;
        }

        return FALSE;
    }

    /**
     * 检查是否应更新的网页或发送未"Not Modified"状态
     *
     * @param $seconds 单位秒
     */
    public static function check($seconds = 60)
    {
        if( ! empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) and expires::check_headers())
        {
            if(($strpos = strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], ';')) !== false)
            {
                $mod_time = substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, $strpos);
            }
            else
            {
                $mod_time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            }

            $mod_time       = strtotime($mod_time);
            $mod_time_diff  = $mod_time + $seconds - time();

            if($mod_time_diff > 0)
            {
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $mod_time));
                header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + $mod_time_diff));
                header('Cache-Control: max-age=' . $mod_time_diff);
                header('Status: 304 Not Modified', TRUE, 304);

                exit(0);
            }
        }

        return false;
    }

    /**
     * 检查头已经创建或是有文件流发送
     *
     * @return boolean
     */
    public static function check_headers()
    {
        foreach (headers_list() as $header)
        {
            if((session_cache_limiter() == '' and stripos($header, 'Last-Modified:') === 0) or stripos($header, 'Expires:') === 0)
            {
                return false;
            }
        }

        return true;
    }
}