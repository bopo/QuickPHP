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
 * QuickPHP 表单助手
 *
 * @category    QuickPHP
 * @package     Helpers
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: form.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_form
{
    /**
     * Generates an opening HTML form tag.
     *
     * @param   string  form action attribute
     * @param   array   extra attributes
     * @param   array   hidden fields to be created immediately after the form tag
     * @return  string
     */
    public static function open($action = NULL, $attr = array(), $hidden = NULL)
    {
        empty($attr['method']) and $attr['method'] = 'post';

        if ($attr['method'] !== 'post' AND $attr['method'] !== 'get')
        {
            $attr['method'] = 'post';
        }

        if ($action === NULL)
        {
            $action = url::site(Router::$complete_uri);
        }
        elseif (strpos($action, '://') === false)
        {
            $action = url::site($action);
        }

        $attr['action'] = $action;

        $form = '<form'.form::attributes($attr).'>'."\n";

        empty($hidden) or $form .= form::hidden($hidden);

        return $form;
    }

    /**
     * Generates an opening HTML form tag that can be used for uploading files.
     *
     * @param   string  form action attribute
     * @param   array   extra attributes
     * @param   array   hidden fields to be created immediately after the form tag
     * @return  string
     */
    public static function open_multipart($action = NULL, $attr = array(), $hidden = array())
    {
        $attr['enctype'] = 'multipart/form-data';
        return form::open($action, $attr, $hidden);
    }

    /**
     * Generates a fieldset opening tag.
     *
     * @param   array   html attributes
     * @param   string  a string to be attached to the end of the attributes
     * @return  string
     */
    public static function open_fieldset($data = NULL, $extra = '')
    {
        return '<fieldset' . html::attributes((array) $data) . ' ' . $extra . '>' . "\n";
    }

    /**
     * Generates a fieldset closing tag.
     *
     * @return  string
     */
    public static function close_fieldset()
    {
        return '</fieldset>' . "\n";
    }

    /**
     * Generates a legend tag for use with a fieldset.
     *
     * @param   string  legend text
     * @param   array   HTML attributes
     * @param   string  a string to be attached to the end of the attributes
     * @return  string
     */
    public static function legend($text = '', $data = NULL, $extra = '')
    {
        return '<legend' . form::attributes((array) $data) . ' ' . $extra . '>' . $text . '</legend>' . "\n";
    }

    /**
     * Generates hidden form fields.
     * You can pass a simple key/value string or an associative array with multiple values.
     *
     * @param   string|array  input name (string) or key/value pairs (array)
     * @param   string        input value, if using an input name
     * @return  string
     */
    public static function hidden($data, $value = '')
    {
        if ( ! is_array($data))
        {
            $data = array ( $data => $value );
        }

        $input = '';

        foreach ($data as $name => $value)
        {
            $attr = array (
                'type'  => 'hidden',
                'name'  => $name,
                'value' => $value
            );

            $input .= form::input($attr) . "\n";
        }

        return $input;
    }

    /**
     * Creates an HTML form input tag. Defaults to a text type.
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   string        input value, when using a name
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function input($data, $value = '', $extra = '')
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }

        $data += array (
            'type'  => 'text',
            'value' => $value
        );

        return '<input'.form::attributes($data).' '.$extra.' />';
    }

    /**
     * Creates a HTML form password input tag.
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   string        input value, when using a name
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function password($data, $value = '', $extra = '')
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }

        $data['type'] = 'password';

        return form::input($data, $value, $extra);
    }

    /**
     * Creates an HTML form upload input tag.
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   string        input value, when using a name
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function upload($data, $value = '', $extra = '')
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }

        $data['type'] = 'file';

        return form::input($data, $value, $extra);
    }

    /**
     * Creates an HTML form textarea tag.
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   string        input value, when using a name
     * @param   string        a string to be attached to the end of the attributes
     * @param   boolean       encode existing entities
     * @return  string
     */
    public static function textarea($data, $value = '', $extra = '', $double_encode = TRUE)
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }

        $value = isset($data['value']) ? $data['value'] : $value;

        unset($data['value']);

        return '<textarea'.form::attributes($data, 'textarea').' '.$extra.'>'.html::specialchars($value, $double_encode).'</textarea>';
    }

    /**
     * Creates an HTML form select tag, or "dropdown menu".
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   array         select options, when using a name
     * @param   string|array  option key(s) that should be selected by default
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function select($data, $options = NULL, $selected = NULL, $extra = '')
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }
        else
        {
            if (isset($data['options']))
            {
                $options = $data['options'];
            }

            if (isset($data['selected']))
            {
                $selected = $data['selected'];
            }
        }

        if (is_array($selected))
        {
            $data['multiple'] = 'multiple';
        }
        else
        {
            $selected = array($selected);
        }

        $input = '<select'.form::attributes($data, 'select').' '.$extra.'>'."\n";

        foreach ((array) $options as $key => $val)
        {
            $key = (string) $key;

            if (is_array($val))
            {
                $input .= '<optgroup label="'.$key.'">'."\n";

                foreach ($val as $inner_key => $inner_val)
                {
                    $inner_key = (string) $inner_key;

                    $sel = in_array($inner_key, $selected) ? ' selected="selected"' : '';
                    $input .= '<option value="'.$inner_key.'"'.$sel.'>'.$inner_val.'</option>'."\n";
                }

                $input .= '</optgroup>'."\n";
            }
            else
            {
                $sel = in_array($key, $selected) ? ' selected="selected"' : '';
                $input .= '<option value="'.$key.'"'.$sel.'>'.$val.'</option>'."\n";
            }
        }

        $input .= '</select>';
        return $input;
    }

    /**
     * Creates an HTML form checkbox input tag.
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   string        input value, when using a name
     * @param   boolean       make the checkbox checked by default
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function checkbox($data, $value = '', $checked = false, $extra = '')
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }

        $data['type'] = 'checkbox';

        if ($checked == TRUE OR (isset($data['checked']) AND $data['checked'] == TRUE))
        {
            $data['checked'] = 'checked';
        }
        else
        {
            unset($data['checked']);
        }

        return form::input($data, $value, $extra);
    }

    /**
     * Creates an HTML form radio input tag.
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   string        input value, when using a name
     * @param   boolean       make the radio selected by default
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function radio($data = '', $value = '', $checked = false, $extra = '')
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }

        $data['type'] = 'radio';

        if ($checked == TRUE OR (isset($data['checked']) AND $data['checked'] == TRUE))
        {
            $data['checked'] = 'checked';
        }
        else
        {
            unset($data['checked']);
        }

        return form::input($data, $value, $extra);
    }

    /**
     * Creates an HTML form submit input tag.
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   string        input value, when using a name
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function submit($data = '', $value = '', $extra = '')
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }

        if (empty($data['name']))
        {
            unset($data['name']);
        }

        $data['type'] = 'submit';

        return form::input($data, $value, $extra);
    }

    /**
     * Creates an HTML form button input tag.
     *
     * @param   string|array  input name or an array of HTML attributes
     * @param   string        input value, when using a name
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function button($data = '', $value = '', $extra = '')
    {
        if ( ! is_array($data))
        {
            $data = array('name' => $data);
        }

        if (empty($data['name']))
        {
            unset($data['name']);
        }

        if (isset($data['value']) AND empty($value))
        {
            $value = arr::remove('value', $data);
        }

        return '<button'.form::attributes($data, 'button').' '.$extra.'>'.$value.'</button>';
    }

    /**
     * Closes an open form tag.
     *
     * @param   string  string to be attached after the closing tag
     * @return  string
     */
    public static function close($extra = '')
    {
        return '</form>'."\n".$extra;
    }

    /**
     * Creates an HTML form label tag.
     *
     * @param   string|array  label "for" name or an array of HTML attributes
     * @param   string        label text or HTML
     * @param   string        a string to be attached to the end of the attributes
     * @return  string
     */
    public static function label($data = '', $text = NULL, $extra = '')
    {
        if ( ! is_array($data))
        {
            if (is_string($data))
            {
                $data = array('for' => $data);
            }
            else
            {
                $data = array();
            }
        }

        if ($text === NULL AND isset($data['for']))
        {
            $text = ucwords(inflector::humanize($data['for']));
        }

        return '<label'.form::attributes($data).' '.$extra.'>'.$text.'</label>';
    }

    /**
     * Sorts a key/value array of HTML attributes, putting form attributes first,
     * and returns an attribute string.
     *
     * @param   array   HTML attributes array
     * @return  string
     */
    public static function attributes($attr, $type = NULL)
    {
        if (empty($attr))
        {
            return '';
        }

        if (isset($attr['name']) AND empty($attr['id']) AND strpos($attr['name'], '[') === false)
        {
            if ($type === NULL AND ! empty($attr['type']))
            {
                $type = $attr['type'];
            }

            switch ($type)
            {
                case 'text':
                case 'textarea':
                case 'password':
                case 'select':
                case 'checkbox':
                case 'file':
                case 'image':
                case 'button':
                case 'submit':
                    $attr['id'] = $attr['name'];
                break;
            }
        }

        $order = array
        (
            'action',
            'method',
            'type',
            'id',
            'name',
            'value',
            'src',
            'size',
            'maxlength',
            'rows',
            'cols',
            'accept',
            'tabindex',
            'accesskey',
            'align',
            'alt',
            'title',
            'class',
            'style',
            'selected',
            'checked',
            'readonly',
            'disabled'
        );

        $sorted = array();

        foreach ($order as $key)
        {
            if (isset($attr[$key]))
            {
                $sorted[$key] = $attr[$key];
                unset($attr[$key]);
            }
        }

        return html::attributes(array_merge($sorted, $attr));
    }

}