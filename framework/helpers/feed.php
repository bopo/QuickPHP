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
 * QuickPHP Feed操作助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: feed.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_feed
{
    /**
     * 解析Feed操作，可以是远程的.
     *
     * @param   string  远程 feed URL
     * @param   integer 获取项目的层数
     * @return  array
     */
    public static function parse($feed, $limit = 0)
    {
        if( ! function_exists('simplexml_load_file'))
        {
            throw new QuickPHP_Exception('Feed Error: SimpleXML must be installed!');
        }

        $ER    = error_reporting(0);
        $limit = (int) $limit;
        $load  = (is_file($feed) or valid::url($feed)) ? 'simplexml_load_file' : 'simplexml_load_string';
        $feed  = $load($feed, 'SimpleXMLElement', LIBXML_NOCDATA);

        error_reporting($ER);

        if($feed === false)
        {
            return array();
        }

        $feed  = isset($feed->channel) ? $feed->xpath('//item') : $feed->entry;
        $i     = 0;
        $items = array();

        foreach ($feed as $item)
        {
            if($limit > 0 and $i++ === $limit)
            {
                break;
            }

            $items[] = (array) $item;
        }

        return $items;
    }

    /**
     * 通过输入的参数创建一个RSS档案.
     *
     * @param array     info
     * @param array     items
     * @param string    format
     * @param string    encoding
     */
    public static function create($info, $items, $format = 'rss2', $encoding = 'UTF-8')
    {
        $info += array('title' => 'Generated Feed', 'link' => '', 'generator' => 'QuickPHP');
        $feed  = '<?xml version="1.0" encoding="' . $encoding . '"?><rss version="2.0"><channel></channel></rss>';
        $feed  = simplexml_load_string($feed);

        foreach ($info as $name => $value)
        {
            if(($name === 'pubDate' or $name === 'lastBuildDate') and (is_int($value) or ctype_digit($value)))
            {
                $value = date(DATE_RFC822, $value);
            }
            elseif(($name === 'link' or $name === 'docs') and strpos($value, '://') === false)
            {
                $value = url::site($value, 'http');
            }

            $feed->channel->addChild($name, $value);
        }

        foreach ($items as $item)
        {
            $row = $feed->channel->addChild('item');

            foreach ($item as $name => $value)
            {
                if($name === 'pubDate' and (is_int($value) or ctype_digit($value)))
                {
                    $value = date(DATE_RFC822, $value);
                }
                elseif(($name === 'link' or $name === 'guid') and strpos($value, '://') === false)
                {
                    $value = url::site($value, 'http');
                }

                $row->addChild($name, $value);
            }
        }

        return $feed->asXML();
    }
}