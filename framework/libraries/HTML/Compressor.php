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
 * html压缩类
 *
 * <code>
 * $html = file_get_contents("http://127.0.0.1/");
 * echo HTML_Compressor::compress($html);
 * </code>
 *
 * @category    QuickPHP
 * @package     HTML
 * @subpackage  HTML_Compressor
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license.html
 * @version     $Id: $
 */
class HTML_Compressor
{
    protected static $tempPreBlock        = "%%%HTMLCOMPRESS~PRE&&&";
    protected static $tempTextAreaBlock   = "%%%HTMLCOMPRESS~TEXTAREA&&&";
    protected static $tempScriptBlock     = "%%%HTMLCOMPRESS~SCRIPT&&&";
    protected static $tempStyleBlock      = "%%%HTMLCOMPRESS~STYLE&&&";
    protected static $tempPHPBlock        = "%%%HTMLCOMPRESS~PHP&&&";

    protected static $commentPattern      = "<!--\\s*[^\\[].*?-->";
    protected static $itsPattern          = ">\\s+?<";
    protected static $prePattern          = "<pre[^>]*?>.*?</pre>";
    protected static $taPattern           = "<textarea[^>]*?>.*?</textarea>";
    protected static $phpPattern          = "<\?php([^-@][\\w\\W]*?)\?>";

    // 脚本标签
    protected static $scriptPattern   = "(?:<script\\s*>|<script type=['\"]text/javascript['\"]\\s*>)(.*?)</script>";
    protected static $stylePattern    = "<style[^>()]*?>(.+)</style>";

    // 单行注释，
    protected static $signleCommentPattern    = "[^\S|;]//.*";
    // 多行注释
    protected static $multiCommentPattern     = "/\\*.*?\\*/";

    // trim去空格和换行符
    protected static $trimPattern             = "\\n\\s*";
    protected static $trimPattern2            = "\\s*\\r";

    public static function compress($html)
    {
        if($html == null || strlen($html) == 0)
            return $html;

        $preBlocks      =
        $taBlocks       =
        $scriptBlocks   =
        $styleBlocks    =
        $phpBlocks      = array();

        $result = $html;

        // 分析出PHP代码
        preg_match_all("#".self::$phpPattern."#is", $result, $matches);
        if(isset($matches[0]) && !empty($matches[0]))
        {
            $result     = str_replace($matches[0], self::$tempPHPBlock, $result);
            $phpBlocks  = $matches[0];
        }

        // 分析出PRE标签
        preg_match_all("#".self::$prePattern."#is", $result, $matches);

        if(isset($matches[0]) && !empty($matches[0]))
        {
            $result     = str_replace($matches[0], self::$tempPreBlock, $result);
            $preBlocks  = $matches[0];
        }

        // 分析出TEXTAREA标签
        preg_match_all("#".self::$taPattern."#is", $result, $matches);
        if(isset($matches[0]) && !empty($matches[0]))
        {
            $result     = str_replace($matches[0], self::$tempTextAreaBlock, $result);
            $taBlocks   = $matches[0];
        }

        // 分析出 SCRIPT 标签
        preg_match_all("#".self::$scriptPattern."#is", $result, $matches);

        if(isset($matches[0]) && !empty($matches[0]))
        {
            $result         = str_replace($matches[0], self::$tempScriptBlock, $result);
            $scriptBlocks   = $matches[0];
        }

        // 不处理嵌入式CSS
        preg_match_all("#".self::$stylePattern."#is", $result, $matches);

        if(isset($matches[0]) && !empty($matches[0]))
        {
            $result         = str_replace($matches[0], self::$tempStyleBlock, $result);
            $styleBlocks    = $matches[0];
        }

        // 处理HTML
        $result = self::processHTML($result);

        //process preserved blocks
        $result = self::processPreBlocks($result, $preBlocks);
        $result = self::processTextareaBlocks($result, $taBlocks);
        $result = self::processScriptBlocks($result, $scriptBlocks);
        $result = self::processStyleBlocks($result, $styleBlocks);
        $result = self::processPHPBlocks($result, $phpBlocks);

        unset($preBlocks, $taBlocks, $scriptBlocks, $styleBlocks, $phpBlocks);

        return trim($result);
    }

    protected static function processHTML($html)
    {
        $result = $html;

        //remove comments 去掉注释
        $result = preg_replace("#".self::$commentPattern."#is", "", $result);

        //remove inter-tag spaces 去掉内嵌空格
        $result = preg_replace("#".self::$itsPattern."#is", "><", $result);

        //remove multi whitespace characters 去掉多空白字符
        $result = preg_replace("#\\s{2,}#", " ", $result);

        return $result;
    }

    protected static function processPHPBlocks($html, array $blocks = null)
    {
        foreach($blocks as $key => $block)
            $blocks[$key] = self::compressPHP($block);

        preg_match_all("#" . self::$tempPHPBlock . "#is", $html, $matches);

        $result = str_replace($matches[0], $blocks, $html);
        $result = preg_replace("#" . self::$commentPattern . "#", "", $result);

        return trim($result);
    }

    protected static function processPreBlocks($html, array $blocks = null)
    {
        preg_match_all("#" . self::$tempPreBlock . "#is", $html, $matches);
        $result = str_replace($matches[0], $blocks, $html);

        return $result;
    }

    protected static function processTextareaBlocks($html, array $blocks = null)
    {
        preg_match_all("#" . self::$tempTextAreaBlock . "#is", $html, $matches);
        $result = str_replace($matches[0], $blocks, $html);

        return trim($result);
    }

    protected static function processScriptBlocks($html, array $blocks = null)
    {
        foreach($blocks as $key => $block)
            $blocks[$key] = self::compressJavaScript($block);

        preg_match_all("#" . self::$tempScriptBlock . "#is", $html, $matches);
        $result = str_replace($matches[0], $blocks, $html);

        return trim($result);
    }

    protected static function processStyleBlocks($html, array $blocks = null)
    {
        foreach($blocks as $key => $block)
            $blocks[$key] = self::compressCssStyles($block);

        preg_match_all("#" . self::$tempStyleBlock . "#is", $html, $matches);
        $result = str_replace($matches[0], $blocks, $html);

        return trim($result);
    }

    protected static function compressPHP($source)
    {
        //check if block is not empty
        preg_match("#".self::$phpPattern."#is", $source, $matches);

        if($matches[0])
            $source = self::compressPHPJs($matches[0]);

        return trim($source);

    }

    protected static function compressJavaScript($source)
    {
        //check if block is not empty
        preg_match("#".self::$scriptPattern."#is", $source, $matches);

        if($matches[0])
            $source = self::compressPHPJs($matches[0]);

        return trim($source);
    }

    protected static function compressCssStyles($source)
    {
        //check if block is not empty
        preg_match("#".self::$stylePattern."#is", $source, $matches);

        if($matches[1])
        {
            $result = preg_replace("#".self::$multiCommentPattern."#is", "", $matches[0]);
            $result = preg_replace("#".self::$trimPattern."#is", "", $result);
            $result = preg_replace("#".self::$trimPattern2."#is", "", $result);
            $source = str_replace($matches[0], $result, $source);
        }

        return trim($source);
    }

    protected static function compressPHPJs($source)
    {
        // 去掉注释
        $source = preg_replace("#".self::$signleCommentPattern."#i", "", $source);
        $source = preg_replace("#".self::$multiCommentPattern."#is", "", $source);
        $source = preg_replace("#".self::$trimPattern2."#is", "", $source);
        $source = preg_replace("#".self::$trimPattern."#is", "", $source);

        return trim($source);
    }
}
