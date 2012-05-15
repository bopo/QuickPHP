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
 * QuickPHP 文本助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  text
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: text.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_text
{
    public static $i;

    /**
     * 限制的词组的单词数
     *
     * @param   string   phrase to limit words of
     * @param   integer  number of words to limit to
     * @param   string   end character or entity
     * @return  string
     */
    public static function limit_words($str, $limit = 100, $end_char = null)
    {
        $limit    = (int) $limit;
        $end_char = ($end_char === null) ? '&#8230;' : $end_char;

        if(trim($str) === '')
        {
            return $str;
        }

        if($limit <= 0)
        {
            return $end_char;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,' . $limit . '}/u', $str, $matches);
        return rtrim($matches[0]) . (strlen($matches[0]) === strlen($str) ? '' : $end_char);
    }

    /**
     * 限制一个中文字符串的字数
     *
     * @param string $string 要限制的中文字符串
     * @param integer $limit 限制长度
     * @param string $charset 字符编码
     */
    public static function limit_chinese($string, $limit = 10, $charset = 'UTF-8')
    {
        return mb_substr($string, 0, $limit, $charset);
    }

    /**
     * 限制的短语，以一定数目的字符
     *
     * @param   string   phrase to limit characters of
     * @param   integer  number of characters to limit to
     * @param   string   end character or entity
     * @param   boolean  enable or disable the preservation of words while limiting
     * @return  string
     */
    public static function limit_chars($str, $limit = 100, $end_char = null, $preserve_words = false)
    {
        $end_char = ($end_char === null) ? '&#8230;' : $end_char;
        $limit    = (int) $limit;

        if(trim($str) === '' or Unicode::strlen($str) <= $limit)
        {
            return $str;
        }

        if($limit <= 0)
        {
            return $end_char;
        }

        if($preserve_words == false)
        {
            return rtrim(Unicode::substr($str, 0, $limit)) . $end_char;
        }

        preg_match('/^.{' . ($limit - 1) . '}\S*/us', $str, $matches);
        return rtrim($matches[0]) . (strlen($matches[0]) == strlen($str) ? '' : $end_char);
    }

    /**
     * 两个或两个以上候补字符串。
     *
     * @param   string  strings to alternate between
     * @return  string
     */
    public static function alternate()
    {
        if(func_num_args() === 0)
        {
            text::$i = 0;
            return '';
        }

        $args = func_get_args();
        return $args[(text::$i++ % count($args))];
    }

    /**
     * 生成一个给定类型和长度的随机字符串。
     *
     * @param   string   $type 类型
     * @param   integer  $length 长度
     * @return  string
     *
     * @tutorial  alnum     字母数字类型
     * @tutorial  alpha     字母类型
     * @tutorial  hexdec    十六进制类型  (0-9和 a-f)
     * @tutorial  numeric   数字类型 , 0-9
     * @tutorial  nozero    自然数字类型 , 1-9
     * @tutorial  distinct  混合型
     */
    public static function random($type = 'alnum', $length = 8)
    {
        $utf8 = false;

        switch ($type)
        {
            case 'alnum' :
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha' :
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec' :
                $pool = '0123456789abcdef';
                break;
            case 'numeric' :
                $pool = '0123456789';
                break;
            case 'nozero' :
                $pool = '123456789';
                break;
            case 'distinct' :
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default :
                $pool = (string) $type;
                $utf8 = ! Unicode::is_ascii($pool);
                break;
        }

        $pool = ($utf8 === true) ? Unicode::str_split($pool, 1) : str_split($pool, 1);
        $max  = count($pool) - 1;
        $str  = '';

        for ($i = 0; $i < $length; $i++)
        {
            $str .= $pool[mt_rand(0, $max)];
        }

        if($type === 'alnum' and $length > 1)
        {
            if(ctype_alpha($str))
            {
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
            }
            elseif(ctype_digit($str))
            {
                $str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
            }
        }

        return $str;
    }

    /**
     * 移除字符串中双斜线，替换成单斜线
     *
     * @param   string  $str 要减少斜线的字符串
     * @return  string
     */
    public static function reduce_slashes($str)
    {
        return preg_replace('#(?<!:)//+#', '/', $str);
    }

    /**
     * 替换为一个字符串给定的词
     *
     * @param   string   phrase to replace words in
     * @param   array    words to replace
     * @param   string   replacement string
     * @param   boolean  replace words across word boundries (space, period, etc)
     * @return  string
     */
    public static function censor($str, $badwords, $replacement = '#', $replace_partial_words = false)
    {
        foreach ((array) $badwords as $key => $badword)
        {
            $badwords[$key] = str_replace('\*', '\S*?', preg_quote((string) $badword));
        }

        $regex = '(' . implode('|', $badwords) . ')';

        if($replace_partial_words == true)
        {
            $regex = '(?<=\b|\s|^)' . $regex . '(?=\b|\s|$)';
        }

        $regex = '!' . $regex . '!ui';

        if(Unicode::strlen($replacement) == 1)
        {
            $regex .= 'e';
            return preg_replace($regex, 'str_repeat($replacement, Unicode::strlen(\'$1\'))', $str);
        }

        return preg_replace($regex, $replacement, $str);
    }

    /**
     * 查找类似的文本之间的词语
     *
     * @param   array   $words
     * @return  string
     */
    public static function similar(array $words)
    {
        $word = current($words);

        for ($i = 0, $max = strlen($word); $i < $max; ++$i)
        {
            foreach ($words as $w)
            {
                if( ! isset($w[$i]) or $w[$i] !== $word[$i])
                {
                    break 2;
                }
            }
        }

        return substr($word, 0, $i);
    }

    /**
     * 文本转换为电子邮件地址和锚的链接
     *
     * @param   string   $text
     * @return  string
     */
    public static function auto_link($text)
    {
        return text::auto_link_urls(text::auto_link_emails($text));
    }

    /**
     * 文本转换成锚链接地址
     *
     * @param   string   $text
     * @return  string
     */
    public static function auto_link_urls($text)
    {
        if(preg_match_all('~\b(?<!href="|">)(?:ht|f)tps?://\S+(?:/|\b)~i', $text, $matches))
        {
            foreach ($matches[0] as $match)
            {
                $text = str_replace($match, html::anchor($match), $text);
            }
        }

        // 查找所有不包含http://的部分，并自动加上http://
        if(preg_match_all('~\b(?<!://)www(?:\.[a-z0-9][-a-z0-9]*+)+\.[a-z]{2,6}\b~i', $text, $matches))
        {
            foreach ($matches[0] as $match)
            {
                $text = str_replace($match, html::anchor('http://' . $match, $match), $text);
            }
        }

        return $text;
    }

    /**
     * 文本转换成电子邮件链接地址
     *
     * @param   string   $text
     * @return  string
     */
    public static function auto_link_emails($text)
    {
        // Finds all email addresses that are not part of an existing html mailto anchor
        // Note: The "58;" negative lookbehind prevents matching of existing encoded html mailto anchors
        //       The html entity for a colon (:) is &#58; or &#058; or &#0058; etc.

        if(preg_match_all('~\b(?<!href="mailto:|">|58;)(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}\b~i', $text, $matches))
        {
            foreach ($matches[0] as $match)
            {
                $text = str_replace($match, html::mailto($match), $text);
            }
        }

        return $text;
    }

    /**
     * 自动转化<p>和<br />到文本。基本类同nl2br函数。
     *
     * @param   string   subject
     * @return  string
     */
    public static function auto_p($str)
    {
        if(($str = trim($str)) === '')
        {
            return '';
        }

        $str = str_replace(array("\r\n", "\r"), "\n", $str);
        $str = preg_replace('~^[ \t]+~m', '', $str);
        $str = preg_replace('~[ \t]+$~m', '', $str);

        if((bool) ($html_found = (strpos($str, '<') !== false)))
        {
            $no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';
            $str  = preg_replace('~^<' . $no_p . '[^>]*+>~im', "\n$0", $str);
            $str  = preg_replace('~</' . $no_p . '\s*+>$~im', "$0\n", $str);
        }

        $str = '<p>' . trim($str) . '</p>';
        $str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);

        if($html_found !== false)
        {
            $str = preg_replace('~<p>(?=</?' . $no_p . '[^>]*+>)~i', '', $str);
            $str = preg_replace('~(</?' . $no_p . '[^>]*+>)</p>~i', '$1', $str);
        }

        $str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);

        return $str;
    }

    /**
     * 返回人类可读的比特大小.
     *
     * @see  参考文献:
     * @see  Aidan Lister: http://aidanlister.com/repos/v/function.size_readable.php
     * @see  Quentin Zervaas: http://www.phpriot.com/d/code/strings/filesize-format/
     *
     * @param   integer  $bytes 比特大小
     * @param   string   $force_unit 设置指定输出单位
     * @param   string   $format 输出格式
     * @param   boolean  $si
     * @return  string
     */
    public static function bytes($bytes, $force_unit = null, $format = null, $si = true)
    {
        $format = ($format === null) ? '%01.2f %s' : (string) $format;

        if($si == false or strpos($force_unit, 'i') !== false)
        {
            $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            $mod   = 1024;
        }
        else
        {
            $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
            $mod   = 1000;
        }

        if(($power = array_search((string) $force_unit, $units)) === false)
        {
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }

        return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
    }
}