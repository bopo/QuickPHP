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
 * QuickPHP模板引擎异常处理
 *
 * QuickPHP_Template_Exception represents an exception caused by invalid template syntax.
 *
 * @category    QuickPHP
 * @package     Template
 * @author      BoPo <ibopo@126.com>
 * @copyright Copyright &copy; 2010 QuickPHP
 * @license http://www.quickphp.net/license/
 * @version    $Id: Exception.php 8320 2011-10-05 14:59:55Z bopo $
 */
class QuickPHP_Template_Exception extends QuickPHP_Exception
{
    protected $_template   = '';
    protected $_lineNumber = 0;
    protected $_fileName   = '';

    /**
     * @return string the template source code that causes the exception. This is empty if {@link getTemplateFile TemplateFile} is not empty.
     */
    public function getSource()
    {
        return $this->_template;
    }

    /**
     * @param string the template source code that causes the exception
     */
    public function setSource(string $value = null)
    {
        $this->_template = $value;
    }

    /**
     * @return string the template file that causes the exception. This could be empty if the template is an embedded template. In this case, use {@link getTemplateSource TemplateSource} to obtain the actual template content.
     */
    public function getTemplateFile()
    {
        return $this->_fileName;
    }

    /**
     * @param string the template file that causes the exception
     */
    public function setTemplateFile($file = null)
    {
        $this->file = $file;
    }

    /**
     * @return integer the line number at which the template has error
     */
    public function getTemplateLine()
    {
        return $this->code;
    }

    /**
     * @param integer the line number at which the template has error
     */
    public function setLineNumber($line = 0)
    {
        $this->line = $line;
    }

    public function __destruct()
    {
    }
}
