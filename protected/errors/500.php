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
 * 错误处理显示页面，用以展示错误堆栈信息
 *
 * @copyright  Copyright (c) 2010 http://quickphp.net All rights reserved.
 * @license    http://framework.quickphp.net/licenses/LICENSE-2.0
 * @version    $Id: 500.php 8324 2011-10-05 15:01:47Z bopo $
 */
$error_id = uniqid('error');
?>

<style type="text/css">
#QuickPHP_error { background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; }
#QuickPHP_error h1,
#QuickPHP_error h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
#QuickPHP_error h1 a,
#QuickPHP_error h2 a { color: #fff; }
#QuickPHP_error h2 { background: #222; }
#QuickPHP_error h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
#QuickPHP_error p { margin: 0; padding: 0.2em 0; }
#QuickPHP_error a { color: #1b323b; }
#QuickPHP_error pre { overflow: auto; white-space: pre-wrap; }
#QuickPHP_error table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
#QuickPHP_error table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
#QuickPHP_error div.content { padding: 0.4em 1em 1em; overflow: hidden; }
#QuickPHP_error pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
#QuickPHP_error pre.source span.line { display: block; }
#QuickPHP_error pre.source span.highlight { background: #f0eb96; }
#QuickPHP_error pre.source span.line span.number { color: #666; }
#QuickPHP_error ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
#QuickPHP_error ol.trace li { margin: 0; padding: 0; }
.js .collapsed { display: none; }
</style>

<script type="text/javascript">
document.documentElement.className = 'js';
function koggle(elem)
{
    elem = document.getElementById(elem);

    if (elem.style && elem.style['display'])
        // Only works with the "style" attr
        var disp = elem.style['display'];
    else if (elem.currentStyle)
        // For MSIE, naturally
        var disp = elem.currentStyle['display'];
    else if (window.getComputedStyle)
        // For most other browsers
        var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

    // Toggle the state of the "display" style
    elem.style.display = disp == 'block' ? 'none' : 'block';
    return false;
}
</script>

<div id="QuickPHP_error">
    <h1><span class="type"><?php echo $type ?> [ <?php echo $code ?> ]:</span> <span class="message"><?php echo htmlspecialchars($message) ?></span></h1>
    <div id="<?php echo $error_id ?>" class="content">
        <p><span class="file"><?php echo debug::path($file) ?> [ <?php echo $line ?> ]</span></p>
        <?php echo debug::source($file, $line) ?>
        <ol class="trace">
        <?php foreach (debug::trace($trace) as $i => $step): ?>
            <li>
                <p>
                    <span class="file">
                        <?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
                            <a href="#<?php echo $source_id ?>" onclick="return koggle('<?php echo $source_id ?>')"><?php echo debug::path($step['file']) ?> [ <?php echo $step['line'] ?> ]</a>
                        <?php else: ?>
                            {<?php echo ('PHP internal call') ?>}
                        <?php endif; ?>
                    </span>
                   &raquo;
                    <?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><a href="#<?php echo $args_id ?>" onclick="return koggle('<?php echo $args_id ?>')"><?php echo ('arguments') ?></a><?php endif ?>)
                </p>
                <?php if (isset($args_id)): ?>
                <div id="<?php echo $args_id ?>" class="collapsed">
                    <table cellspacing="0">
                    <?php foreach ($step['args'] as $name => $arg): ?>
                        <tr>
                            <td><code><?php echo $name ?></code></td>
                            <td><pre><?php echo debug::dump($arg) ?></pre></td>
                        </tr>
                    <?php endforeach ?>
                    </table>
                </div>
                <?php endif ?>
                <?php if (isset($source_id)): ?>
                    <pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
                <?php endif ?>
            </li>
            <?php unset($args_id, $source_id); ?>
        <?php endforeach ?>
        </ol>
    </div>
    <h2><a href="#<?php echo $env_id = $error_id.'environment' ?>" onclick="return koggle('<?php echo $env_id; ?>')"><?php echo ('Environment') ?></a></h2>
    <div id="<?php echo $env_id; ?>" class="content collapsed">
        <?php $included = get_included_files() ?>
        <h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo ('Included files') ?></a> (<?php echo count($included) ?>)</h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                <tr>
                    <td><code><?php echo debug::path($file) ?></code></td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php $included = get_loaded_extensions() ?>
        <h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" onclick="return koggle('<?php echo $env_id ?>')"><?php echo ('Loaded extensions') ?></a> (<?php echo count($included) ?>)</h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($included as $file): ?>
                <tr>
                    <td><code><?php echo debug::path($file) ?></code></td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
        <?php if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
        <h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var ?></a></h3>
        <div id="<?php echo $env_id ?>" class="collapsed">
            <table cellspacing="0">
                <?php foreach ($GLOBALS[$var] as $key => $value): ?>
                <tr>
                    <td><code><?php echo htmlspecialchars($key) ?></code></td>
                    <td><pre><?php echo debug::dump($value) ?></pre></td>
                </tr>
                <?php endforeach ?>
            </table>
        </div>
        <?php endforeach ?>
    </div>
</div>