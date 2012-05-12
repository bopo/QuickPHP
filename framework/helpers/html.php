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
 * QuickPHP Inflector HTML助手。用于生成各种HTML标签，使输出的HTML提供通用的安全方法
 *
 * @category    QuickPHP
 * @package     Helpers
 * @subpackage  html
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: html.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_html
{
    /**
     * @var  array  首选顺序属性
     */
    public static $attribute_order = array(
        'action',
        'method',
        'type',
        'id',
        'name',
        'value',
        'href',
        'src',
        'width',
        'height',
        'cols',
        'rows',
        'size',
        'maxlength',
        'rel',
        'media',
        'accept-charset',
        'accept',
        'tabindex',
        'accesskey',
        'alt',
        'title',
        'class',
        'style',
        'selected',
        'checked',
        'readonly',
        'disabled');

    /**
     * @var  boolean  连接是否打开新的窗口
     */
    public static $windowed_urls = FALSE;

    /**
     * Convert special characters to HTML entities. All untrusted content
     * should be passed through this method to prevent XSS injections.
     *
     * echo html::chars($username);
     *
     * @param   string   string to convert
     * @param   boolean  encode existing entities
     * @return  string
     */
    public static function chars($value, $double_encode = TRUE)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, QuickPHP::$charset, $double_encode);
    }

    /**
     * Convert all applicable characters to HTML entities. All characters
     * that cannot be represented in HTML with the current character set
     * will be converted to entities.
     *
     * echo html::entities($username);
     *
     * @param   string   string to convert
     * @param   boolean  encode existing entities
     * @return  string
     */
    public static function entities($value, $double_encode = TRUE)
    {
        return htmlentities((string) $value, ENT_QUOTES, QuickPHP::$charset, $double_encode);
    }

    /**
     * Create HTML link anchors. Note that the title is not escaped, to allow
     * HTML elements within links (images, etc).
     *
     * echo html::anchor('/user/profile', 'My Profile');
     *
     * @param   string  URL or URI string
     * @param   string  link text
     * @param   array   HTML anchor attributes
     * @param   string  use a specific protocol
     * @return  string
     * @uses    url::base
     * @uses    url::site
     * @uses    html::attributes
     */
    public static function anchor($uri, $title = null, array $attributes = null, $protocol = null)
    {
        if($title === null)
        {
            $title = $uri;
        }

        if($uri === '')
        {
            $uri = url::base(false, $protocol);
        }
        else
        {
            if(strpos($uri, '://') !== false)
            {
                if(html::$windowed_urls === true and empty($attributes['target']))
                {
                    $attributes['target'] = '_blank';
                }
            }
            elseif($uri[0] !== '#')
            {
                $uri = url::site($uri, $protocol);
            }
        }

        $attributes['href'] = $uri;
        return '<a' . html::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Creates an HTML anchor to a file. Note that the title is not escaped,
     * to allow HTML elements within links (images, etc).
     *
     * echo html::file_anchor('media/doc/user_guide.pdf', 'User Guide');
     *
     * @param   string  name of file to link to
     * @param   string  link text
     * @param   array   HTML anchor attributes
     * @param   string  non-default protocol, eg: ftp
     * @return  string
     * @uses    url::base
     * @uses    html::attributes
     */
    public static function file_anchor($file, $title = NULL, array $attributes = NULL, $protocol = NULL)
    {
        if($title === NULL)
        {
            $title = basename($file);
        }

        $attributes['href'] = url::base(FALSE, $protocol) . $file;
        return '<a' . html::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Generates an obfuscated version of a string. Text passed through this
     * method is less likely to be read by web crawlers and robots, which can
     * be helpful for spam prevention, but can prevent legitimate robots from
     * reading your content.
     *
     * echo html::obfuscate($text);
     *
     * @param   string  string to obfuscate
     * @return  string
     * @since   3.0.3
     */
    public static function obfuscate($string)
    {
        $safe = '';

        foreach (str_split($string) as $letter)
        {
            switch (rand(1, 3))
            {
                case 1 :
                    $safe .= '&#' . ord($letter) . ';';
                    break;
                case 2 :
                    $safe .= '&#x' . dechex(ord($letter)) . ';';
                    break;
                case 3 :
                    $safe .= $letter;
            }
        }

        return $safe;
    }

    /**
     * Generates an obfuscated version of an email address. Helps prevent spam
     * robots from finding email addresses.
     *
     * echo html::email($address);
     *
     * @param   string  email address
     * @return  string
     * @uses    html::obfuscate
     */
    public static function email($email)
    {
        return str_replace('@', '&#64;', html::obfuscate($email));
    }

    /**
     * Creates an email (mailto:) anchor. Note that the title is not escaped,
     * to allow HTML elements within links (images, etc).
     *
     * echo html::mailto($address);
     *
     * @param   string  email address to send to
     * @param   string  link text
     * @param   array   HTML anchor attributes
     * @return  string
     * @uses    html::email
     * @uses    html::attributes
     */
    public static function mailto($email, $title = NULL, array $attributes = NULL)
    {
        $email = html::email($email);

        if($title === NULL)
        {
            $title = $email;
        }

        return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;' . $email . '"' . html::attributes($attributes) . '>' . $title . '</a>';
    }

    /**
     * Creates a style sheet link element.
     *
     * echo html::style('media/css/screen.css');
     *
     * @param   string  file name
     * @param   array   default attributes
     * @param   boolean  include the index page
     * @return  string
     * @uses    url::base
     * @uses    html::attributes
     */
    public static function style($file, array $attributes = NULL, $index = FALSE)
    {
        if(strpos($file, '://') === FALSE)
        {
            $file = url::base($index) . $file;
        }

        $attributes['href'] = $file;
        $attributes['rel']  = 'stylesheet';
        $attributes['type'] = 'text/css';

        return '<link' . html::attributes($attributes) . ' />';
    }

    /**
     * Creates a script link.
     *
     * echo html::script('media/js/jquery.min.js');
     *
     * @param   string   file name
     * @param   array    default attributes
     * @param   boolean  include the index page
     * @return  string
     * @uses    url::base
     * @uses    html::attributes
     */
    public static function script($file, array $attributes = NULL, $index = FALSE)
    {
        if(strpos($file, '://') === FALSE)
        {
            $file = url::base($index) . $file;
        }

        $attributes['src'] = $file;
        $attributes['type'] = 'text/javascript';

        return '<script' . html::attributes($attributes) . '></script>';
    }

    /**
     * Creates a image link.
     *
     * echo html::image('media/img/logo.png', array('alt' => 'My Company'));
     *
     * @param   string   file name
     * @param   array    default attributes
     * @return  string
     * @uses    url::base
     * @uses    html::attributes
     */
    public static function image($file, array $attributes = NULL, $index = FALSE)
    {
        if(strpos($file, '://') === FALSE)
        {
            $file = url::base($index) . $file;
        }

        $attributes['src'] = $file;

        return '<img' . html::attributes($attributes) . ' />';
    }

    /**
     * Compiles an array of HTML attributes into an attribute string.
     * Attributes will be sorted using html::$attribute_order for consistency.
     *
     * echo '<div'.html::attributes($attrs).'>'.$content.'</div>';
     *
     * @param   array   attribute list
     * @return  string
     */
    public static function attributes(array $attributes = NULL)
    {
        if(empty($attributes))
        {
            return '';
        }

        $sorted = array();

        foreach (html::$attribute_order as $key)
        {
            if(isset($attributes[$key]))
            {
                $sorted[$key] = $attributes[$key];
            }
        }

        $attributes = $sorted + $attributes;
        $compiled   = '';

        foreach ($attributes as $key => $value)
        {
            if($value === NULL)
            {
                continue;
            }

            $compiled .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, QuickPHP::$charset) . '"';
        }

        return $compiled;
    }
}