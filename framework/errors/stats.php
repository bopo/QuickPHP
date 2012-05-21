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
 * 系统概况展示页面
 *
 * @copyright  Copyright (c) 2010 http://quickphp.net All rights reserved.
 * @license    http://framework.quickphp.net/licenses/LICENSE-2.0
 * @version    $Id: stats.php 8320 2011-10-05 14:59:55Z bopo $
 */
$group_stats        = Profiler::group_stats();
$group_cols         = array('min', 'max', 'average', 'total');
$application_cols   = array('min', 'max', 'average', 'current');
$application_title  = array('min'=>'最小值', 'max'=>'最大值', 'average'=>'平均', 'total'=>'总计');
?>

<style type="text/css">
.quickphp table.profiler {
    width: 99%;
    margin: 0 auto 1em;
    border-collapse: collapse;
}

.quickphp table.profiler th,.quickphp table.profiler td {
    padding: 0.2em 0.4em;
    background: #fff;
    border: solid 1px #999;
    border-width: 1px 0;
    text-align: left;
    font-weight: normal;
    font-size: 1em;
    color: #111;
    vertical-align: top;
    text-align: right;
}

.quickphp table.profiler th.name {
    text-align: left;
}

.quickphp table.profiler tr.group th {
    font-size: 1.4em;
    background: #222;
    color: #eee;
    border-color: #222;
}

.quickphp table.profiler tr.group td {
    background: #222;
    color: #777;
    border-color: #222;
}

.quickphp table.profiler tr.group td.time {
    padding-bottom: 0;
}

.quickphp table.profiler tr.headers th {
    text-transform: lowercase;
    font-variant: small-caps;
    background: #ddd;
    color: #777;
}

.quickphp table.profiler tr.mark th.name {
    width: 40%;
    font-size: 1.2em;
    background: #fff;
    vertical-align: middle;
}

.quickphp table.profiler tr.mark td {
    padding: 0;
}

.quickphp table.profiler tr.mark.final td {
    padding: 0.2em 0.4em;
}

.quickphp table.profiler tr.mark td>div {
    position: relative;
    padding: 0.2em 0.4em;
}

.quickphp table.profiler tr.mark td div.value {
    position: relative;
    z-index: 2;
}

.quickphp table.profiler tr.mark td div.graph {
    position: absolute;
    top: 0;
    bottom: 0;
    right: 0;
    left: 100%;
    background: #71bdf0;
    z-index: 1;
}

.quickphp table.profiler tr.mark.memory td div.graph {
    background: #acd4f0;
}

.quickphp table.profiler tr.mark td.current {
    background: #eddecc;
}

.quickphp table.profiler tr.mark td.min {
    background: #d2f1cb;
}

.quickphp table.profiler tr.mark td.max {
    background: #ead3cb;
}

.quickphp table.profiler tr.mark td.average {
    background: #ddd;
}

.quickphp table.profiler tr.mark td.total {
    background: #d0e3f0;
}

.quickphp table.profiler tr.time td {
    border-bottom: 0;
    font-weight: bold;
}

.quickphp table.profiler tr.memory td {
    border-top: 0;
}

.quickphp table.profiler tr.final th.name {
    background: #222;
    color: #fff;
}

.quickphp table.profiler abbr {
    border: 0;
    color: #777;
    font-weight: normal;
}

.quickphp table.profiler:hover tr.group td {
    color: #ccc;
}

.quickphp table.profiler:hover tr.mark td div.graph {
    background: #1197f0;
}

.quickphp table.profiler:hover tr.mark.memory td div.graph {
    background: #7cc1f0;
}
</style>

<div class="quickphp">
    <?php
    foreach (Profiler::groups() as $group => $benchmarks)
    :?>
    <table class="profiler">
    <tr class="group">
        <th class="name" rowspan="2"><?php
        if($group !== 'QuickPHP') echo (($group)); else {echo ('框架执行');}?></th>
        <td class="time" colspan="4"><?php
        echo number_format($group_stats[$group]['total']['time'], 6)?> <abbr
            title="seconds">s</abbr></td>
    </tr>

    <tr class="group">
        <td class="memory" colspan="4"><?php
        echo number_format($group_stats[$group]['total']['memory'] / 1024, 4)?> <abbr
            title="kilobyte">kB</abbr></td>
    </tr>

    <tr class="headers">
        <th class="name"><?php
        echo ('基准测试')?></th>
            <?php
        foreach ($group_cols as $key)
        :
            ?>
            <th class="<?php
            echo $key?>"><?php
            echo $application_title[$key]; (($key));?></th>
            <?php
        endforeach
        ?>
        </tr>
        <?php
        foreach ($benchmarks as $name => $tokens)
        :
            ?>
        <tr class="mark time">
            <?php
            $stats = Profiler::stats($tokens)?>
            <th class="name" rowspan="2" scope="rowgroup"><?php
            echo html::chars($name), ' (', count($tokens), ')'?></th>
            <?php
            foreach ($group_cols as $key)
            :
                ?>
            <td class="<?php
                echo $key?>">
        <div>
        <div class="value"><?php
                echo number_format($stats[$key]['time'], 6)?> <abbr title="seconds">s</abbr></div>
                    <?php
                if ($key === 'total' )
                :
                    ?>
                        <div class="graph" style="left: <?php
                    echo max(0, 100 - $stats[$key]['time'] / $group_stats[$group]['max']['time'] * 100)?>%"></div>

                <?php endif ?>
                </div>
        </td>
            <?php
            endforeach
            ?>
        </tr>
    <tr class="mark memory">
        <?php foreach ($group_cols as $key):?>
        <td class="<?php echo $key?>">
        <div>
            <div class="value">
                <?php echo number_format($stats[$key]['memory'] / 1024, 4)?> <abbr title="kilobyte">kB</abbr>
            </div>
            <?php if ($key === 'total' ):?>
                <div class="graph" style="left: <?php echo max(0, 100 - $stats[$key]['memory'] / $group_stats[$group]['max']['memory'] * 100)?>%"></div>
            <?php endif ?>
        </div>
        </td>
            <?php endforeach ?>
        </tr>
        <?php endforeach ?>
    </table>
    <?php endforeach ?>

    <table class="profiler">
        <?php $stats = Profiler::application()?>
        <tr class="final mark time">
        <th class="name" rowspan="2" scope="rowgroup">
        <?php echo '应用执行' . ' (' . $stats['count'] . ')'?>
        </th>
            <?php foreach ($application_cols as $key):?>
            <td class="<?php echo $key?>">
                <?php echo number_format($stats[$key]['time'], 6)?> <abbr title="seconds">s</abbr></td>
            <?php endforeach ?>
        </tr>
        <tr class="final mark memory">
            <?php foreach ($application_cols as $key): ?>
            <td class="<?php echo $key?>"><?php echo number_format($stats[$key]['memory'] / 1024, 4)?> <abbr title="kilobyte">kB</abbr></td>
            <?php endforeach ?>
        </tr>
    </table>
</div>