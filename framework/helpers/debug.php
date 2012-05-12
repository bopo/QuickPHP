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
 * QuickPHP 调试助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  debug
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: debug.php 8582 2011-12-19 01:47:02Z bopo $
 */
class QuickPHP_debug
{
    /**
     * 返回一个对任何数量的调试信息的HTML字符串 变量，每一个“<pre>”的标签包裹
     *
     * echo QuickPHP::debug($foo, $bar, $baz);
     *
     * @param   mixed   variable to debug
     * @param   ...
     * @return  string
     */
    public static function dump()
    {
        if(func_num_args() === 0)
        {
            return;
        }

        $variables  = func_get_args();
        $output     = array();

        foreach ($variables as $var)
        {
            $output[] = debug::_dump($var, 1024);
        }

        return '<pre class="debug">' . implode("\n", $output) . '</pre>';
    }

    /**
     * 处理数组和对象递归 ，var_dump 自定义版本。
     *
     * @param   mixed    要递归变量
     * @param   integer  最大长度
     * @param   integer  递归层次
     * @return  string
     */
    protected static function _dump($var, $length = 128, $level = 0)
    {
        if($var === NULL)
        {
            return '<small>Null</small>';
        }
        elseif(is_bool($var))
        {
            return '<small>Boolean</small> ' . ($var ? 'TRUE' : 'FALSE');
        }
        elseif(is_float($var))
        {
            return '<small>Float</small> ' . $var;
        }
        elseif(is_resource($var))
        {
            if(($type = get_resource_type($var)) === 'stream' and $meta = stream_get_meta_data($var))
            {
                $meta = stream_get_meta_data($var);

                if(isset($meta['uri']))
                {
                    $file = $meta['uri'];

                    if(function_exists('stream_is_local'))
                    {
                        if(stream_is_local($file))
                        {
                            $file = debug::path($file);
                        }
                    }

                    return '<small>Resource</small><span>(' . $type . ')</span> ' . htmlspecialchars($file, ENT_NOQUOTES, QuickPHP::$charset);
                }
            }
            else
            {
                return '<small>Resource</small><span>(' . $type . ')</span>';
            }
        }
        elseif(is_string($var))
        {
            if(Unicode::strlen($var) > $length)
            {
                $str = htmlspecialchars(Unicode::substr($var, 0, $length), ENT_NOQUOTES, QuickPHP::$charset) . '&nbsp;&hellip;';
            }
            else
            {
                $str = htmlspecialchars($var, ENT_NOQUOTES, QuickPHP::$charset);
            }

            return '<small>String</small><span>(' . strlen($var) . ')</span> "' . $str . '"';
        }
        elseif(is_array($var))
        {
            $output = array();
            $space  = str_repeat($s = '    ', $level);

            if( ! isset($_marker) or $_marker === NULL)
            {
                $_marker = uniqid("\x00");
            }

            if(empty($var))
            {
                // Do nothing
            }
            elseif(isset($var[$_marker]))
            {
                $output[] = "(\n$space$s*RECURSION*\n$space)";
            }
            elseif($level < 5)
            {
                $output[] = "<span>(";
                $var[$_marker] = TRUE;

                foreach ($var as $key => & $val)
                {
                    if($key === $_marker)
                    {
                        continue;
                    }

                    if( ! is_int($key))
                    {   
                        $key = '"' . htmlspecialchars($key, ENT_NOQUOTES, QuickPHP::$charset) . '"';
                    }

                    $output[] = "$space$s$key => " . debug::_dump($val, $length, $level + 1);
                }

                unset($var[$_marker]);
                $output[] = "$space)</span>";
            }
            else
            {
                $output[] = "(\n$space$s...\n$space)";
            }

            return '<small>Array</small><span>(' . count($var) . ')</span> ' . implode("\n", $output);
        }
        elseif(is_object($var))
        {
            $array  = (array) $var;
            $output = array();
            $space  = str_repeat($s = '    ', $level);
            $hash   = spl_object_hash($var);

            static $objects = array();

            if(empty($var))
            {
                // Do nothing
            }
            elseif(isset($objects[$hash]))
            {
                $output[] = "{\n$space$s*RECURSION*\n$space}";
            }
            elseif($level < 10)
            {
                $output[]       = "<code>{";
                $objects[$hash] = TRUE;

                foreach ($array as $key => & $val)
                {
                    if($key[0] === "\x00")
                    {
                        $access = '<small>' . ($key[1] === '*' ? 'protected' : 'private') . '</small>';
                        $key    = substr($key, strrpos($key, "\x00") + 1);
                    }
                    else
                    {
                        $access = '<small>public</small>';
                    }

                    $output[] = "$space$s$access $key => " . debug::_dump($val, $length, $level + 1);
                }

                unset($objects[$hash]);
                $output[] = "$space}</code>";
            }
            else
            {
                $output[] = "{\n$space$s...\n$space}";
            }

            return '<small>object</small> <span>' . get_class($var) . '(' . count($array) . ')</span> ' . implode("\n", $output);
        }
        else
        {
            return '<small>' . gettype($var) . '</small> ' . htmlspecialchars(print_r($var, TRUE), ENT_NOQUOTES, QuickPHP::$charset);
        }
    }

    /**
     * Removes application, system, modpath, or docroot from a filename,
     * replacing them with the plain text equivalents. Useful for debugging
     * when you want to display a shorter path.
     *
     * // Displays SYSPATH/classes/Quick.php
     * echo debug::path(QuickPHP::find('classes', 'Quick'));
     *
     * 替换错误路径
     *
     * @param   string  path to debug
     * @return  string
     */
    public static function path($file)
    {
        $file = str_replace("\\", "/", $file);

        if(strpos($file, APPPATH) === 0)
        {
            $file = 'APPPATH/' . substr($file, strlen(APPPATH));
        }
        elseif(strpos($file, SYSPATH) === 0)
        {
            $file = 'SYSPATH/' . substr($file, strlen(SYSPATH));
        }
        elseif(strpos($file, DOCROOT) === 0)
        {
            $file = 'DOCROOT/' . substr($file, strlen(DOCROOT));
        }

        return $file;
    }

    /**
     * Returns an HTML string, highlighting a specific line of a file, with some
     * number of lines padded above and below.
     *
     * // Highlights the current line of the current file
     * echo debug::source(__FILE__, __LINE__);
     *
     * @param   string   file to open
     * @param   integer  line number to highlight
     * @param   integer  number of padding lines
     * @return  string   source of file
     * @return  FALSE    file is unreadable
     */
    public static function source($file, $line_number, $padding = 5)
    {
        if( ! $file or ! is_readable($file))
            return FALSE;

        $file   = fopen($file, 'r');
        $line   = 0;
        $range  = array('start' => $line_number - $padding, 'end' => $line_number + $padding);
        $format = '% ' . strlen($range['end']) . 'd';
        $source = '';

        while(($row = fgets($file)) !== FALSE)
        {
            if(++$line > $range['end'])
            {
                break;
            }

            if($line >= $range['start'])
            {
                $row = htmlspecialchars($row, ENT_NOQUOTES, QuickPHP::$charset);
                $row = '<span class="number">' . sprintf($format, $line) . '</span> ' . $row;

                if($line === $line_number)
                {
                    $row = '<span class="line highlight">' . $row . '</span>';
                }
                else
                {
                    $row = '<span class="line">' . $row . '</span>';
                }

                $source .= $row;
            }
        }

        fclose($file);
        return '<pre class="source"><code>' . $source . '</code></pre>';
    }

    /**
     * Returns an array of HTML strings that represent each step in the backtrace.
     *
     * // Displays the entire current backtrace
     * echo implode('<br/>', debug::trace());
     *
     * @param   string  path to debug
     * @return  string
     */
    public static function trace(array $trace = NULL)
    {
        if($trace === NULL)
        {
            $trace = debug_backtrace();
        }

        $statements = array('include', 'include_once', 'require', 'require_once');
        $output     = array();

        foreach ($trace as $step)
        {
            if( ! isset($step['function']))
            {
                continue;
            }

            if(isset($step['file']) and isset($step['line']))
            {
                $source = debug::source($step['file'], $step['line']);
            }

            if(isset($step['file']))
            {
                $file = $step['file'];

                if(isset($step['line']))
                {
                    $line = $step['line'];
                }
            }

            $function = $step['function'];

            if(in_array($step['function'], $statements))
            {
                if(empty($step['args']))
                {
                    $args = array();
                }
                else
                {
                    $args = array($step['args'][0]);
                }
            }
            elseif(isset($step['args']))
            {
                if( ! function_exists($step['function']) or strpos($step['function'], '{closure}') !== FALSE)
                {
                    $params = NULL;
                }
                else
                {
                    if(isset($step['class']))
                    {
                        if(method_exists($step['class'], $step['function']))
                        {
                            $reflection = new ReflectionMethod($step['class'], $step['function']);
                        }
                        else
                        {
                            $reflection = new ReflectionMethod($step['class'], '__call');
                        }
                    }
                    else
                    {
                        $reflection = new ReflectionFunction($step['function']);
                    }

                    $params = $reflection->getParameters();
                }

                $args = array();

                foreach ($step['args'] as $i => $arg)
                {
                    if(isset($params[$i]))
                    {
                        $args[$params[$i]->name] = $arg;
                    }
                    else
                    {
                        $args[$i] = $arg;
                    }
                }
            }

            if(isset($step['class']))
            {
                $function = $step['class'] . $step['type'] . $step['function'];
            }

            $output[] = array(
                'function' => $function,
                'args'     => isset($args) ? $args : null,
                'file'     => isset($file) ? $file : null,
                'line'     => isset($line) ? $line : null,
                'source'   => isset($source) ? $source : null);

            unset($function, $args, $file, $line, $source);
        }

        return $output;
    }
}