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
return array
(
    /** 平台类型 */
    'platforms' => array (
        'windows nt 6.1'    => 'Windows 7',
        'windows nt 6.0'    => 'Windows Vista',
        'windows nt 5.2'    => 'Windows 2003',
        'windows nt 5.0'    => 'Windows 2000',
        'windows nt 5.1'    => 'Windows XP',
        'windows nt 4.0'    => 'Windows NT 4.0',
        'winnt4.0'          => 'Windows NT 4.0',
        'winnt 4.0'         => 'Windows NT',
        'winnt'             => 'Windows NT',
        'windows 98'        => 'Windows 98',
        'win98'             => 'Windows 98',
        'windows 95'        => 'Windows 95',
        'win95'             => 'Windows 95',
        'windows'           => 'Unknown Windows OS',
        'os x'              => 'Mac OS X',
        'ppc mac'           => 'Power PC Mac',
        'freebsd'           => 'FreeBSD',
        'ppc'               => 'Macintosh',
        'linux'             => 'Linux',
        'debian'            => 'Debian',
        'sunos'             => 'Sun Solaris',
        'beos'              => 'BeOS',
        'apachebench'       => 'ApacheBench',
        'aix'               => 'AIX',
        'irix'              => 'Irix',
        'osf'               => 'DEC OSF',
        'hp-ux'             => 'HP-UX',
        'netbsd'            => 'NetBSD',
        'bsdi'              => 'BSDi',
        'openbsd'           => 'OpenBSD',
        'gnu'               => 'GNU/Linux',
        'unix'              => 'Unknown Unix OS'
    ),

    /** 浏览器类型 */
    'browsers' => array(
        'Opera'             => 'Opera',
        'MSIE'              => 'Internet Explorer',
        'Internet Explorer' => 'Internet Explorer',
        'Shiira'            => 'Shiira',
        'Firefox'           => 'Firefox',
        'Chimera'           => 'Chimera',
        'Phoenix'           => 'Phoenix',
        'Firebird'          => 'Firebird',
        'Camino'            => 'Camino',
        'Netscape'          => 'Netscape',
        'OmniWeb'           => 'OmniWeb',
        'Mozilla'           => 'Mozilla',
        'Safari'            => 'Safari',
        'Konqueror'         => 'Konqueror',
        'icab'              => 'iCab',
        'Lynx'              => 'Lynx',
        'Links'             => 'Links',
        'hotjava'           => 'HotJava',
        'amaya'             => 'Amaya',
        'IBrowse'           => 'IBrowse'
    ),

    /** 移动设备浏览器类型 */
    'mobiles' => array(
        'mobileexplorer'    => 'Mobile Explorer',
        'openwave'          => 'Open Wave',
        'opera mini'        => 'Opera Mini',
        'operamini'         => 'Opera Mini',
        'elaine'            => 'Palm',
        'palmsource'        => 'Palm',
        'digital paths'     => 'Palm',
        'avantgo'           => 'Avantgo',
        'xiino'             => 'Xiino',
        'palmscape'         => 'Palmscape',
        'nokia'             => 'Nokia',
        'ericsson'          => 'Ericsson',
        'blackBerry'        => 'BlackBerry',
        'motorola'          => 'Motorola'
    ),

    /** 搜索引擎机器人类型 可以自己增加 */
    'robots' => array(
        'slurp'             => 'Inktomi Slurp',
        'yahoo'             => 'Yahoo',
        'lycos'             => 'Lycos',
        'msnbot'            => 'MSNBot',
        'infoseek'          => 'InfoSeek Robot 1.0',
        'askjeeves'         => 'AskJeeves',
        'googlebot'         => 'Googlebot',
        'baiduspider'       => 'BaiDuSpider',
        'fastcrawler'       => 'FastCrawler',
    )
);