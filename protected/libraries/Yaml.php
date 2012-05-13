<?php
defined('SYSPATH') or die('No direct access allowed.');
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
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Yaml offers convenience methods to load and dump YAML.
 *
 * @package    symfony
 * @subpackage yaml
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Yaml
{
    protected static $spec = '1.2';
    
    /**
     * Sets the YAML specification version to use.
     *
     * @param string $version The YAML specification version
     */
    static public function setSpecVersion($version)
    {
        if(! in_array($version, array('1.1', '1.2')))
        {
            throw new InvalidArgumentException(sprintf('Version %s of the YAML specifications is not supported', $version));
        }
        
        Yaml::$spec = $version;
    }
    
    /**
     * Gets the YAML specification version to use.
     *
     * @return string The YAML specification version
     */
    static public function getSpecVersion()
    {
        return Yaml::$spec;
    }
    
    /**
     * Loads YAML into a PHP array.
     *
     * The load method, when supplied with a YAML stream (string or file),
     * will do its best to convert YAML in a file into a PHP array.
     *
     * Usage:
     * <code>
     * $array = Yaml::load('config.yml');
     * print_r($array);
     * </code>
     *
     * @param string $input Path of YAML file or string containing YAML
     *
     * @return array The YAML converted to a PHP array
     *
     * @throws \InvalidArgumentException If the YAML is not valid
     */
    public static function load($input)
    {
        $file = '';
        
        // if input is a file, process it
        if(strpos($input, "\n") === false && is_file($input))
        {
            $file = $input;
            
            ob_start();
            $retval = include ($input);
            $content = ob_get_clean();
            
            // if an array is returned by the config file assume it's in plain php form else in YAML
            $input = is_array($retval) ? $retval : $content;
        }
        
        // if an array is returned by the config file assume it's in plain php form else in YAML
        if(is_array($input))
        {
            return $input;
        }
        
        $yaml = new Yaml_Parser();
        
        try
        {
            $ret = $yaml->parse($input);
        }
        catch (Exception $e)
        {
            throw new InvalidArgumentException(sprintf('Unable to parse %s: %s', $file ? sprintf('file "%s"', $file) : 'string', $e->getMessage()));
        }
        
        return $ret;
    }
    
    /**
     * Dumps a PHP array to a YAML string.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.
     *
     * @param array   $array PHP array
     * @param integer $inline The level where you switch to inline YAML
     *
     * @return string A YAML string representing the original PHP array
     */
    public static function dump($array, $inline = 2)
    {
        $yaml = new Yaml_Dumper();
        
        return $yaml->dump($array, $inline);
    }
}
