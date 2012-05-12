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
 * QuickPHP 时间和时区助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  date
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: date.php 8641 2012-01-05 08:35:39Z bopo $
 */
class QuickPHP_date
{
    public static $months;
    public static $offsets;

    /**
     * UNIX时间戳转换到DOS格式
     *
     * @param   integer  UNIX 时间戳
     * @return  integer
     */
    public static function unix2dos($timestamp = false)
    {
        $timestamp = ($timestamp === false) ? getdate() : getdate($timestamp);

        if($timestamp['year'] < 1980)
        {
            return (1 << 21 | 1 << 16);
        }

        $timestamp['year'] -= 1980;

        return ($timestamp['year'] << 25 | $timestamp['mon'] << 21 | $timestamp['mday'] << 16 | $timestamp['hours'] << 11 | $timestamp['minutes'] << 5 | $timestamp['seconds'] >> 1);
    }

    /**
     * DOS时间戳转换到UNIX格式
     *
     * @param   integer  DOS 时间戳
     * @return  integer
     */
    public static function dos2unix($timestamp = false)
    {
        $sec  = 2 * ($timestamp & 0x1f);
        $min  = ($timestamp >> 5) & 0x3f;
        $hrs  = ($timestamp >> 11) & 0x1f;
        $day  = ($timestamp >> 16) & 0x1f;
        $mon  = ($timestamp >> 21) & 0x0f;
        $year = ($timestamp >> 25) & 0x7f;

        return mktime($hrs, $min, $sec, $mon, $day, $year + 1980);
    }

    /**
     * 返回两个时区时差（以秒计）
     * @see     http://php.net/timezones
     *
     * @param   string          目标时区
     * @param   string|boolean  作为基准的时区
     * @return  integer
     */
    public static function offset($remote, $local = true)
    {
        $remote = (string) $remote;
        $local  = ($local === true) ? date_default_timezone_get() : (string) $local;
        $cache  = $remote . $local;

        if(empty(self::$offsets[$cache]))
        {
            $remote     = new DateTimeZone($remote);
            $local      = new DateTimeZone($local);
            $time_there = new DateTime('now', $remote);
            $time_here  = new DateTime('now', $local);

            self::$offsets[$cache] = $remote->getOffset($time_there) - $local->getOffset($time_here);
        }

        return self::$offsets[$cache];
    }

    /**
     * 返回按步进递增的秒数集合
     *
     * $result = date::seconds($step = 5, $start = 0, $end = 60);
     *
     * Returns
     * array(00,05,10,15,20,25,30,35...55);
     *
     * @param   integer  amount to increment each step by, 1 to 30
     * @param   integer  start value
     * @param   integer  end value
     * @return  array    A mirrored (foo => foo) array from 1-60.
     */
    public static function seconds($step = 1, $start = 0, $end = 60)
    {
        $step    = (int) $step;
        $seconds = array();

        for ($i = $start; $i < $end; $i += $step)
        {
            $seconds[$i] = ($i < 10) ? '0' . $i : $i;
        }

        return $seconds;
    }

    /**
     * 返回按步进递增的分钟集合
     *
     * @param   integer  $step (1~30)
     * @return  array    (1~60).
     */
    public static function minutes($step = 5)
    {
        return date::seconds($step);
    }

    /**
     * Number of hours in a day.
     *
     * @param   integer  amount to increment each step by
     * @param   boolean  use 24-hour time
     * @param   integer  the hour to start at
     * @return  array    A mirrored (foo => foo) array from start-12 or start-23.
     */
    public static function hours($step = 1, $long = false, $start = null)
    {
        $step  = (int) $step;
        $long  = (bool) $long;
        $hours = array();

        if($start === null)
        {
            $start = ($long === false) ? 1 : 0;
        }

        $hours = array();
        $size  = ($long === true) ? 23 : 12;

        for ($i = $start; $i <= $size; $i += $step)
        {
            $hours[$i] = $i;
        }

        return $hours;
    }

    /**
     * 返回某个小时是 AM或者PM(上午，下午).
     *
     * @param   integer  小时
     * @return  string
     */
    public static function ampm($hour)
    {
        $hour = intval($hour);
        return ($hour > 11) ? 'PM' : 'AM';
    }

    /**
     * 将一个12小时制时间转化成24小时制时间
     *
     * @param   integer  要转化的小时
     * @param   string   AM或 PM
     * @return  string
     */
    public static function adjust($hour, $ampm)
    {
        $hour = (int) $hour;
        $ampm = strtolower($ampm);

        switch ($ampm)
        {
            case 'am' :

                if($hour == 12)
                {
                    $hour = 0;
                }

                break;

            case 'pm' :

                if($hour < 12)
                {
                    $hour += 12;
                }

                break;
        }

        return sprintf('%02s', $hour);
    }

    /**
     * 返回月的天数.
     *
     * @param   integer  要检索月份
     * @param   integer  要检索月份的年份，默认为本年度
     * @return  array    以数组形式返回该月的天的集合.
     */
    public static function days($month, $year = false)
    {
        $month = (int) $month;
        $year  = (int) $year;

        // 使用本年作为要检索月份的年份
        $year = ($year == false) ? date('Y') : $year;

        // 判断缓存中已经检索过的月份信息,缓存的目的提高效率
        if(empty(self::$months[$year][$month]))
        {
            self::$months[$year][$month] = array();

            // 使用date函数来查找特定月份的天数
            $total = date('t', mktime(1, 0, 0, $month, 1, $year)) + 1;

            for ($i = 1; $i < $total; $i++)
            {
                self::$months[$year][$month][$i] = $i;
            }
        }

        return self::$months[$year][$month];
    }

    /**
     * 返回一年的月份集合
     *
     * @return  array  (1-12)
     */
    public static function months()
    {
        return date::hours();
    }

    /**
     * 返回一个范围区间的年份
     * 默认使用当前的年份正负5为区间
     *
     * @param   integer  开始年份
     * @param   integer  结束年份
     * @return  array
     */
    public static function years($start = false, $end = false)
    {
        $start = ($start === false) ? date('Y') - 5 : (int) $start;
        $end   = ($end === false) ? date('Y') + 5 : (int) $end;
        $years = array();
        $end   += 1;

        for ($i = $start; $i < $end; $i++)
        {
            $years[$i] = $i;
        }

        return $years;
    }

    /**
     * 返回两个时间戳之间的时间差，返回人类可读的格式
     *
     * @param integer $time1 时间戳
     * @param integer $time2 时间戳, 默认当前时间
     * @param string $output 时间格式
     * @return array
     */
    public static function timespan($time1, $time2 = null, $output = 'years,months,weeks,days,hours,minutes,seconds')
    {
        $years  = $months = $weeks = $days = $hours = $minutes = $seconds = false;
        $output = preg_split('/[^a-z]+/', strtolower((string) $output));

        if(empty($output))
        {
            return false;
        }

        // 设置输出变量
        extract(array_flip($output), EXTR_SKIP);

        // 设置默认值
        $time1 = max(0, (int) $time1);
        $time2 = empty($time2) ? time() : max(0, (int) $time2);

        // 计算时间差 (秒数)
        $timespan = abs($time1 - $time2);

        // 差几年, 60 * 60 * 24 * 365
        ! empty($years) and $timespan -= 31556926 * ($years = (int) floor($timespan / 31556926));

        // 差几月, 60 * 60 * 24 * 30
        ! empty($months) and $timespan -= 2629744 * ($months = (int) floor($timespan / 2629743.83));

        // 差几周, 60 * 60 * 24 * 7
        ! empty($weeks) and $timespan -= 604800 * ($weeks = (int) floor($timespan / 604800));

        // 差几日, 60 * 60 * 24
        ! empty($days) and $timespan -= 86400 * ($days = (int) floor($timespan / 86400));

        // 差几时, 60 * 60
        ! empty($hours) and $timespan -= 3600 * ($hours = (int) floor($timespan / 3600));

        // 差几分, 60
        ! empty($minutes) and $timespan -= 60 * ($minutes = (int) floor($timespan / 60));

        // 差几秒, 1
        ! empty($seconds) and $seconds = $timespan;

        unset($timespan, $time1, $time2);

        // 拒绝访问这些变量
        $deny = array_flip(array('deny', 'key', 'difference', 'output'));

        $difference = array();

        foreach ($output as $key)
        {
            if(isset($$key) and ! isset($deny[$key]))
            {
                $difference[$key] = $$key;
            }
        }

        if(empty($difference))
        {
            return false;
        }

        return $difference;
    }

    /**
     * 返回两个时间戳之间的时间差，返回字符串格式：年，月，周，天，时，分，秒
     *
     * @param   integer       时间戳
     * @param   integer       时间戳, 默认当前时间
     * @param   string        输出格式
     * @return  string
     */
    public static function timespan_string($time1, $time2 = null, $output = 'years,months,weeks,days,hours,minutes,seconds')
    {
        if($difference = date::timespan($time1, $time2, $output) and is_array($difference))
        {
            $last = end($difference);
            $last = key($difference);
            $span = array();

            foreach ($difference as $name => $amount)
            {
                if($amount === 0)
                {
                    continue;
                }

                $span[] = ($name === $last ? ' and ' : ', ') . $amount . ' ' . ($amount === 1 ? inflector::singular($name) : $name);
            }

            if(count($span) === 1)
            {
                $span[0] = ltrim($span[0], 'and ');
            }

            $difference = trim(implode('', $span), ',');
        }
        elseif(is_int($difference))
        {
            $difference = $difference . ' ' . ($difference === 1 ? inflector::singular($output) : $output);
        }

        return $difference;
    }

    public static function solarterms($date = null)
    {
        return $solarterms;
    }

}