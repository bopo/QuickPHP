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
 * 系统性能基准测试类
 *
 * @package     QuickPHP
 * @category    Profiler
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Profiler.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_Profiler
{
    /**
     * @var  integer   同时基准测试最大数量
     */
    public static $rollover  = 1000;

    /**
     * @var  integer   标记名称集合
     */
    protected static $_marks = array();

    /**
     * 开始已经新的统计，并返回该统计的令牌标记
     *
     * $token = Profiler::start('test', 'profiler');
     *
     * @param   string  测试组名
     * @param   string  标签名
     * @return  string  令牌名称
     */
    public static function start($group, $name)
    {
        static $counter = 0;

        $token = 'qp/' . base_convert($counter++, 10, 32);

        Profiler::$_marks[$token] = array(
            'name'         => (string) $name, // Start the benchmark
            'group'        => ($group),
            'stop_time'    => false,
            'start_time'   => microtime(true),
            'stop_memory'  => false,
            'start_memory' => memory_get_usage(), // Set the stop keys without values
        );

        return $token;
    }

    /**
     * 停止一个令牌标记的统计
     *
     * Profiler::stop($token);
     *
     * @param   string  token
     * @return  void
     */
    public static function stop($token)
    {
        Profiler::$_marks[$token]['stop_time']   = microtime(true);
        Profiler::$_marks[$token]['stop_memory'] = memory_get_usage();
    }

    /**
     * 删除一个令牌标记的内容
     *
     * Profiler::delete($token);
     *
     * @param   string  token
     * @return  void
     */
    public static function delete($token)
    {
        unset(Profiler::$_marks[$token]);
    }

    /**
     * 返回已经标记的所有组名
     *
     * $groups = Profiler::groups();
     *
     * @return  array
     */
    public static function groups()
    {
        $groups = array();

        foreach (Profiler::$_marks as $token => $mark)
        {
            $groups[$mark['group']][$mark['name']][] = $token;
        }

        return $groups;
    }

    /**
     * 以指定令牌标记过滤为结果，返回最小值,最大值,平均值和性能总计的一组数组集合。
     *
     * $stats = Profiler::stats($tokens);
     *
     * @param   array  要过滤的令牌值
     * @return  array  min, max, average, total
     * @uses    Profiler::total
     */
    public static function stats(array $tokens)
    {
        $min = $max = array('time' => null, 'memory' => null);
        $total = array('time' => 0, 'memory' => 0);

        foreach ($tokens as $token)
        {
            list ($time, $memory) = Profiler::total($token);

            if($max['time'] === null or $time > $max['time'])
            {
                $max['time'] = $time;
            }

            if($min['time'] === null or $time < $min['time'])
            {
                $min['time'] = $time;
            }

            $total['time'] += $time;

            if($max['memory'] === null or $memory > $max['memory'])
            {
                $max['memory'] = $memory;
            }

            if($min['memory'] === null or $memory < $min['memory'])
            {
                $min['memory'] = $memory;
            }

            $total['memory'] += $memory;
        }

        $count   = count($tokens);
        $average = array('time' => $total['time'] / $count, 'memory' => $total['memory'] / $count);

        return array('min' => $min, 'max' => $max, 'total' => $total, 'average' => $average);
    }

    /**
     * 获得最小值，最大值，平均值以及性能总计的数组集合
     *
     * $stats = Profiler::group_stats('test');
     *
     * @param   mixed  单一组名, 数组形式多个组名; 默认为全部
     * @return  array  min, max, average, total
     * @uses    Profiler::groups
     * @uses    Profiler::stats
     */
    public static function group_stats($groups = null)
    {
        $stats  = array();
        $groups = ($groups === null) ? Profiler::groups() : array_intersect_key(Profiler::groups(), array_flip((array) $groups));

        foreach ($groups as $group => $names)
        {
            foreach ($names as $name => $tokens)
            {
                $_stats = Profiler::stats($tokens);
                $stats[$group][$name] = $_stats['total'];
            }
        }

        $groups = array();

        foreach ($stats as $group => $names)
        {
            $groups[$group]['min'] = $groups[$group]['max'] = array('time' => null, 'memory' => null);
            $groups[$group]['total'] = array('time' => 0, 'memory' => 0);

            foreach ($names as $total)
            {
                if( ! isset($groups[$group]['min']['time']) or $groups[$group]['min']['time'] > $total['time'])
                {
                    $groups[$group]['min']['time'] = $total['time'];
                }

                if( ! isset($groups[$group]['min']['memory']) or $groups[$group]['min']['memory'] > $total['memory'])
                {
                    $groups[$group]['min']['memory'] = $total['memory'];
                }

                if( ! isset($groups[$group]['max']['time']) or $groups[$group]['max']['time'] < $total['time'])
                {
                    $groups[$group]['max']['time'] = $total['time'];
                }

                if( ! isset($groups[$group]['max']['memory']) or $groups[$group]['max']['memory'] < $total['memory'])
                {
                    $groups[$group]['max']['memory'] = $total['memory'];
                }

                $groups[$group]['total']['time']   += $total['time'];
                $groups[$group]['total']['memory'] += $total['memory'];
            }

            $count = count($names);
            $groups[$group]['average']['time']   = $groups[$group]['total']['time'] / $count;
            $groups[$group]['average']['memory'] = $groups[$group]['total']['memory'] / $count;
        }

        return $groups;
    }

    /**
     * 得到了总执行时间和内存使用已任一个列表。
     *
     * list($time, $memory) = Profiler::total($token);
     *
     * @param   string  token
     * @return  array   execution time, memory
     */
    public static function total($token)
    {
        $mark = Profiler::$_marks[$token];

        if($mark['stop_time'] === false)
        {
            $mark['stop_time']   = microtime(true);
            $mark['stop_memory'] = memory_get_usage();
        }

        return array($mark['stop_time'] - $mark['start_time'], $mark['stop_memory'] - $mark['start_memory']);
    }

    /**
     * 得到了总程序跑的时间和内存使用。缓存结果,以便它可以比较的要求。
     *
     * list($time, $memory) = Profiler::application();
     *
     * @return  array  执行时间,内存使用量
     * @uses    QuickPHP::cache
     */
    public static function application()
    {
        // 载入的统计数据缓存,有效期为1的一天
        $stats = QuickPHP::cache('profiler_application_stats', null, 3600 * 24);

        // 初始化统计数组
        if( ! is_array($stats) or $stats['count'] > Profiler::$rollover)
            $stats = array(
            'min'   => array('time' => null, 'memory' => null),
            'max'   => array('time' => null, 'memory' => null),
            'total' => array('time' => null, 'memory' => null),
            'count' => 0);

        // 获得运行时间
        $time = microtime(true) - QUICKPHP_START_TIME;

        // 获得总内存使用量
        $memory = memory_get_usage() - QUICKPHP_START_MEMORY;

        // 计算最大时间
        if($stats['max']['time'] === null or $time > $stats['max']['time'])
        {
            $stats['max']['time'] = $time;
        }

        // 计算最小时间
        if($stats['min']['time'] === null or $time < $stats['min']['time'])
        {
            $stats['min']['time'] = $time;
        }

        // 添加运行时间总数
        $stats['total']['time'] += $time;

        // 计算最大内存
        if($stats['max']['memory'] === null or $memory > $stats['max']['memory'])
        {
            $stats['max']['memory'] = $memory;
        }

        // 计算最小内存
        if($stats['min']['memory'] === null or $memory < $stats['min']['memory'])
        {
            $stats['min']['memory'] = $memory;
        }

        // 添加内存使用总量
        $stats['total']['memory'] += $memory;

        // 增加标签统计数
        $stats['count']++;

        // 计算平均值
        $stats['average'] = array('time' => $stats['total']['time'] / $stats['count'], 'memory' => $stats['total']['memory'] / $stats['count']);

        // 缓存一个新统计
        QuickPHP::cache('profiler_application_stats', $stats);

        // 设置当前的应用程序执行时间和记忆
        // 不要缓存的话,他们具体到当前请求上才有
        $stats['current']['time']   = $time;
        $stats['current']['memory'] = $memory;

        // 返回运行时间和内存使用量总数
        return $stats;
    }
}