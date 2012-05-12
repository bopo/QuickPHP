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
 * File-based configuration reader. Multiple configuration directories can be
 * used by attaching multiple instances of this class to [QuickPHP_Config].
 *
 * @category   QuickPHP
 * @package    Config
 * @author     QuickPHP Team
 * @copyright  (c) 2008-2009 QuickPHP Team
 * @license    http://www.QuickPHP.net/license
 */
class QuickPHP_Config_Driver_File extends QuickPHP_Config_Abstract
{
    protected $_configuration_group;
    protected $_configuration_modified = FALSE;

    public function __construct($directory = 'config')
    {
        $this->_directory = trim($directory, '/');
        parent::__construct();
    }

    /**
     * 加载并合并所有的配置到组文件
     *
     * $config->load($name);
     *
     * @param   string  configuration group name
     * @param   array   configuration array
     * @return  $this   clone of the current object
     * @uses    QuickPHP::load
     */
    public function load($group, array $config = NULL)
    {
        $files  = QuickPHP::find($this->_directory, $group, NULL, TRUE);
        $config = array();

        if( ! empty($files))
        {
            foreach ($files as $file)
            {
                $config = arr::merge($config, QuickPHP::load($file));
            }
        }

        return parent::load($group, $config);
    }
}