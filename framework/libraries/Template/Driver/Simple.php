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
 * QuickPHP模板引擎Simple驱动
 *
 * @category    QuickPHP
 * @package     Template
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Simple.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Template_Driver_Simple implements Template_Interface
{

    protected $_config    = NULL;
    protected $_data      = array();
    protected $_delimiter = array('{{', '}}');

    /**
     * 构造函数
     */
    public function __construct($config = array())
    {
        $this->_config = $config;

        if(isset($this->_config['left_delimiter']) && isset($this->_config['right_delimiter']))
        {
            if(is_string($this->_config['left_delimiter']))
            {
                $this->_delimiter[0] = $this->_config['left_delimiter'];
            }

            if(is_string($this->_config['right_delimiter']))
            {
                $this->_delimiter[1] = $this->_config['right_delimiter'];
            }
        }
    }

    /**
     * 分配模板引擎变量方法
     *
     * Example:
     * $template->assign( 'TITLE',     'My Document Title' );
     * $template->assign( 'userlist',  array(
     * array( 'ID' => 123,  'NAME' => 'John Doe' ),
     * array( 'ID' => 124,  'NAME' => 'Jack Doe' ),
     * );
     * @access public
     * @param string $name Parameter Name
     * @param mixed $value Parameter Value
     * @desc Assign Template Content
     */
    public function assign($var, $value = null)
    {
        if(is_array($var))
        {
            foreach ($var as $key => $val)
            {
                if($key != '')
                {
                    $this->_data[$key] = $val;
                }
            }
        }
        else
        {
            if($var != '')
            {
                $this->_data[$var] = $value;
            }
        }
    }

    /**
     * 追加模版变量方法
     *
     * <code>
     * $template->append( 'userlist',  array( 'ID' => 123,  'NAME' => 'John Doe' ) );
     * </code>
     * @access public
     * @param string $name Parameter Name
     * @param mixed $value Parameter Value
     * @desc Assign Template Content
     */
    public function append($var, $value = null, $merge = false)
    {
        if($value === null) return true;

        if(is_array($var))
        {
            foreach ($var as $_key => $_val)
            {
                if($_key != '')
                {
                    if( ! @is_array($this->_data[$_key]))
                    {
                        settype($this->_data[$_key], 'array');
                    }

                    if($merge && is_array($_val))
                    {
                        foreach ($_val as $_mkey => $_mval)
                        {
                            $this->_data[$_key][$_mkey] = $_mval;
                        }
                    }
                    else
                    {
                        $this->_data[$_key][] = $_val;
                    }
                }
            }
        }
        else
        {
            if($var != '' && isset($value))
            {
                if( ! @is_array($this->_data[$var]))
                {
                    settype($this->_data[$var], 'array');
                }

                if($merge && is_array($value))
                {
                    foreach ($value as $_mkey => $_mval)
                    {
                        $this->_data[$var][$_mkey] = $_mval;
                    }
                }
                else
                {
                    $this->_data[$var][] = $value;
                }
            }
        }
    }

    /**
     * 渲染html方法
     *
     * @access public
     * @param array $_top Content Array
     * @desc Execute parsed Template
     */
    public function render($tempate = NULL, $_top = array(), $return = TRUE)
    {
        if( ! empty($this->_data))
        {
            $_top = array_merge($_top, $this->_data);
        }

        if(empty($tempate))
        {
            throw new QuickPHP_Template_Exception('template_content_null',array($tempate));
        }

        $_obj                  = $_top;
        $_stack_cnt            = 0;
        $_stack[$_stack_cnt++] = $_obj;
        $compile               = $this->_config['compile_dir'] . md5($tempate);
        $this->_compile($tempate, $compile);

        ob_start();
        include $compile;
        $buffer = ob_get_contents();
        ob_end_clean();

        if((bool) $return === TRUE)
        {
//            require DOCROOT . 'php_speedy/libs/php_speedy/php_speedy' . EXT;
//            $buffer = $compressor->finish($buffer);
            return $buffer;
        }
    }

    /**
     * 返回编译文件是否过期
     *
     * @access private
     * @param string $filename
     * @return mixed
     * @desc Determine Last Filechange Date
     */
    private function expired($filename = null)
    {
        if(file_exists($filename))
        {
            $expire  = ((bool) IN_PRODUCTION) ? $this->_config['compile_lifetime'] : -1;

            if(empty($expire))
            {
                return FALSE;
            }

            if($expire == -1)
            {
                return TRUE;
            }

            $expire += filemtime($filename);

            if($expire > time())
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * 包含文件的操作
     *
     * <!-- #include virtual="include.html" -->
     * <!-- #include relative="include.html" -->
     * <!-- #include file="include.html" -->
     * @param   string $inc_file (模版文件名)
     * @return  string (包含后整个的整个模版文件)
     */
    private function merge($tempate)
    {
        $content = file_get_contents($tempate);

        if(preg_match_all('/<!-- \#layout name=[\"|\']([a-zA-Z0-9\/_\.-]+)[\"|\'] block=[\"|\']([a-zA-Z0-9\/_\.-]+)[\"|\'] -->/', $content, $var))
        {
            $layout = $this->_config['layout_dir'] . $var[1][0];
            $blocks = $var[2][0];

            if( ! file_exists($layout))
            {
                $this->_debug($tempate, $var[0][0]);
            }

            $preg    = '/<!-- \#layout name=[\"|\']([a-zA-Z0-9\/_\.-]+)[\"|\'] block=[\"|\']([a-zA-Z0-9\/_\.-]+)[\"|\'] -->/s';
            $temp    = preg_replace($preg, "", $content);
            $content = file_get_contents($layout);
            $content = str_replace($this->_delimiter[0] . $blocks . $this->_delimiter[1], $temp, $content);
        }

        // 必须严格匹配
        if(preg_match_all('/<!-- \#include (virtual|file|relative)=[\"|\']([a-zA-Z0-9\/_\.-]+)[\"|\'] -->/', $content, $var))
        {
            foreach ($var[2] as $key => $tag)
            {
                $tmpfile = '';
                $option  = $var[1][$key];

                if($option == 'file' || $option == 'relative')
                {
                    $folder = dirname($tempate);
                }

                if($option == 'virtual' || substr($tag, 0, 1) == '/')
                {
                    $folder = $this->_config['template_dir'];
                }

                $tmpfile = realpath($folder . "/" . $tag);

                if( ! file_exists($tmpfile))
                {
                    $this->_debug($tempate, $var[0][$key]);
                }

                $content = preg_replace('/<!-- \#include ' . $option . '=[\"|\']' . str_replace("/", "\\/", preg_quote($tag)) . '[\"|\'] -->/i', $this->merge($tmpfile), $content);
            }
        }

        return $content;
    }

    private function _debug($tempate, $tag)
    {
        $line  = 0;
        $lines = file($tempate);

        foreach ($lines as $key => $val)
        {
            if(ereg($tag, $val))
            {
                $line = $key + 1;
                break;
            }
        }

        $exception = new QuickPHP_Template_Exception('template_syntax_invalid',array($tag));
        $exception->setLineNumber($line);
        $exception->setTemplateFile($tempate);
        throw $exception;
    }

    /**
     * 模板引擎编译方法
     *
     * @param string  Compiled Template Filename
     * @desc Creates Compiled PHP Template
     */
    private function _compile($tempate, $compile)
    {
        // 编译文件过期判断
        if($this->expired($compile) !== TRUE)
        {
            return TRUE;
        }

        $tempate = $this->_config['template_dir'] . $tempate . $this->_config['template_suffix'];

        list($L, $R) = $this->_delimiter;

        if( ! file_exists($tempate))
        {
            throw new QuickPHP_Template_Exception('template_content_null',array($tempate));
        }

        // 编译文件到缓存目录
        $page = $this->merge($tempate);
        $page = preg_replace("#" . preg_quote("{{?") . "(.*?)" . preg_quote("?}}") . "#ims", '<?php $1 ?>', $page);
        $page = preg_replace("#" . preg_quote("{{*") . "(.*?)" . preg_quote("*}}") . "#s", '', $page);
        $page = preg_replace("/<!-- ENDIF.+?-->/", "<?php } ?>", $page);
        $page = preg_replace("/<!-- END[ a-zA-Z0-9_.]* -->/", "<?php } \$_obj=\$_stack[--\$_stack_cnt];} ?>", $page);
        $page = str_replace("<!-- ELSE -->", "<?php } else { ?>", $page);

        // loop 'BEGIN - END' Blocks
        if(preg_match_all('/<!-- BEGIN ([a-zA-Z0-9_\.]+) -->/', $page, $var))
        {
            foreach ($var[1] as $tag)
            {
                list($parent, $block) = $this->_var_name($tag);

                $code = "<?php " . "if ( ! empty(\${$parent}['{$block}'])){ ".PHP_EOL
                    . "if ( ! is_array(\${$parent}['{$block}'])) ".PHP_EOL
                    . "\${$parent}['{$block}'] = "
                    . "array(array('{$block}'=>\${$parent}['{$block}']));".PHP_EOL
                    . "\$_stack[\$_stack_cnt++]=\$_obj; ".PHP_EOL
                    . "\$_rowcnt[\$_stack_cnt] = 0; ".PHP_EOL
                    . "foreach (\${$parent}['{$block}'] as \$k=>\$v) { ".PHP_EOL
                    . "if (is_array(\$v)) \${$block}=\$v;"
                    . "elseif (is_object(\$v)) \${$block}=\$v->as_array(); else \${$block}=array(); ".PHP_EOL
                    . "\${$block}['_KEY']=\$k; ".PHP_EOL
                    . "\${$block}['_VALUE']=\$v; ".PHP_EOL
                    . "\${$block}['_ROWCNT']=++\$_rowcnt[\$_stack_cnt]; ".PHP_EOL
                    . "\${$block}['_ROWBIT']=\$_rowcnt[\$_stack_cnt]%2; ".PHP_EOL
                    . "\$_obj = \$$block; ?>";

                $page = str_replace("<!-- BEGIN $tag -->", $code, $page);
            }
        }

        // 'IF nnn=top.var' Blocks
        if(preg_match_all('/<!-- (ELSE)?IF ([a-zA-Z0-9_.]+)([!=<>]+)(.*?) -->/', $page, $var))
        {
            foreach ($var[2] as $cnt => $tag)
            {
                $else = ($var[1][$cnt] == 'ELSE') ? '}else' : '';
                list($parent, $block) = $this->_var_name($tag);

                $cmp = $var[3][$cnt];
                $val = $var[4][$cnt];

                if( ! preg_match("/^['|\"]/i", $val))
                {
                    $val = $this->_var_name($val);
                    $val = "\$" . $val[0] . "['" . $val[1] . "']";
                }

                if($cmp == '=' OR $cmp == '===')
                {
                    $cmp = '==';
                }

                if($cmp == '<>')
                {
                    $cmp = '!=';
                }

                $code = "<?php {$else}if (\$$parent" . "['$block'] $cmp $val){ ?>";
                $page = str_replace($var[0][$cnt], $code, $page);
            }
        }

        // 'IF nnn' Blocks
        if(preg_match_all('/<!-- (ELSE)?IF ([a-zA-Z0-9_.]+) -->/', $page, $var))
        {
            foreach ($var[2] as $cnt => $tag)
            {
                $else = ($var[1][$cnt] == 'ELSE') ? '}else' : '';
                list($parent, $block) = $this->_var_name($tag);
                $code = "<?php {$else}if ( ! empty(\$$parent" . "['$block'])){ ?>";
                $page = str_replace($var[0][$cnt], $code, $page);
            }
        }

        //  一般变量的编译{变量名}
        if(preg_match_all('/' . $L . '([\'"a-zA-Z0-9_\. >]+)' . $R . '/', $page, $var))
        {
            foreach ($var[1] as $flag)
            {
                list($cmd, $tag) = $this->_cmd_name($flag);

                if( ! strchr('\'"', $tag[0]) || is_numeric($tag))
                {
                    list($block, $scalar) = $this->_var_name($tag);

                    if($cmd == 'echo')
                    {
                        $code = "<?php if(isset(\${$block}['{$scalar}'])) { {$cmd} \${$block}['{$scalar}']; } ?>";
                    }
                    else
                    {
                        $code = "<?php {$cmd} \${$block}['{$scalar}']; ?>";
                    }
                }
                else
                {
                    if($cmd == 'echo')
                    {
                        $code = "<?php if(isset({$tag})) { {$cmd} {$tag};} ?>";
                    }
                    else
                    {
                        $code = "<?php {$cmd} {$tag}; ?>";
                    }
                }

                $page = str_replace($L . $flag . $R, $code, $page);
            }
        }

        // {date::today(release_date)}
        if(preg_match_all('/' . $L . '([a-zA-Z0-9_]+::[a-zA-Z0-9_]+)\((.*?)\)' . $R . '/', $page, $var))
        {
            foreach ($var[2] as $cnt => $tag)
            {
                list($cmd, $tag) = $this->_cmd_name($tag);

                $helper = $var[1][$cnt];

                if( ! strlen($tag))
                {
                    $code = "<?php $cmd $helper(); ?>";
                }
                else
                {
                    $tags  = explode(',', $tag);
                    $param = array();

                    foreach ($tags as $tag)
                    {
                        if(substr($tag, 0, 1) == '"' || substr($tag, 0, 1) == "'")
                        {
                            $param[] = str_replace(array('"'), "'", $tag);
                        }
                        else
                        {
                            list($block, $skalar) = $this->_var_name($tag);
                            $param[] = "\$$block" . "['".trim($skalar)."']";
                        }
                    }

                    $param = implode(",", $param);
                    $code  = "<?php $cmd $helper($param); ?>";
                }

                $page = str_replace($var[0][$cnt], $code, $page);
            }
        }

        // Compile Template File.
        if(IN_PRODUCTION == true)
        {
            $page = HTML_Compressor::compress($page);
        }

        if(FALSE == file_put_contents($compile, $page))
        {
            throw new QuickPHP_Template_Exception('write_error', array($tempate));
        }

        return true;
    }

    /**
     * 将模板变量名解析到数组中 (Array-Name/Key-Name)
     * 实例:
     * {example}         :  array( "_obj",            "example" )  ->  $_obj['example']
     * {example.value}   :  array( "_obj['example']", "value" )    ->  $_obj['example']['value']
     * {example.0.value} :  array( "_obj['example'][0]",   "value" )    ->  $_obj['example'][0]['value']
     * {top.example}     :  array( "_stack[0]",            "example" )  ->  $_stack[0]['example']
     * {parent.example}  :  array( "_stack[$_stack_cnt-1]","example" )  ->  $_stack[$_stack_cnt-1]['example']
     * {parent.parent.example} :  array( "_stack[$_stack_cnt-2]",  "example" )  ->  $_stack[$_stack_cnt-2]['example']
     * {GLOBALS.global} {_SERVER.PHP_SELF}
     *
     * @param: string $tag (模板中的变量名)
     * @return: array  Array Name, Key Name
     * @access: private
     */
    protected function _var_name($tag)
    {
        $parent_level = 0;

        $globals = array('_ENV', '_COOKIE', '_GET', '_POST', '_REQUEST', '_SESSION', 'GLOBALS', '_SERVER');
        $tag     = explode(".", $tag);
        $obj     = '_obj';

        while($tag[$parent_level] == 'parent')
        {
            $parent_level++;
            $tag = array_slice($tag, $parent_level);
        }

        if($tag[$parent_level] == 'top')
        {
            $obj = '_stack[0]';
            $tag = array_slice($tags, $parent_level);
        }
        elseif($parent_level > 0)
        {
            $obj = '_stack[$_stack_cnt-' . $parent_level . ']';
        }
        elseif(in_array($tags[0], $globals))
        {
            $obj = (string) $globals[0];
            $tag = array_slice($globals, 1);
        }

        $etag = is_array($tag) ? end($tag) : $tag;

        if(count($tag) > 1)
        {
            array_pop($tag);

            foreach ($tag as $parent)
            {
                $obj .= is_numeric($parent) ? "[".$parent."]" : "['".$parent."']";
            }
        }

        return array($obj, $etag);
    }

    /**
     * 转译变量指令
     * 实例:
     * {variable}           : array( "echo",              "variable" ) -> echo $_obj['variable']
     * {variable > new_name}: array( "_obj['new_name']=", "variable" ) -> $_obj['new_name'] = $_obj['variable']
     *
     * @param string $tag Variale Name used in Template
     * @return array  Array Command, Variable
     * @access private
     */
    private function _cmd_name($tag)
    {
        $cmd = " echo ";

        if( ! is_string($tag))
        {
            return NULL;
        }

        if(preg_match('/^(.*) >(>?) ([a-zA-Z0-9_\.]+)$/', $tag, $tagvar))
        {
            list($newblock, $newscalar) = $this->_var_name($tagvar[3]);

            $tag = $tagvar[1];
            $cmd = "\${$newblock}['{$newscalar}'] " . (strlen($tagvar[2]) == 0 ? '' : '.') . "=";
        }

        return array($cmd, $tag);
    }

}