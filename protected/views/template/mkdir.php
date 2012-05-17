<?php
$template = "<?php defined('SYSPATH') or die('No direct access allowed.');
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
 * $Id: $
 *
 * {{controller}}
 *
 * @package    {{controller}}
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */

class {{controller}}_Controller extends Template_Controller
{
    public function __call(\$method, \$args)
    {
    }
}
";
mkdir('controller');
if ($handle = opendir('.'))
{
    while (false !== ($file = readdir($handle)))
    {
        if (is_dir($file))
        {
            $filename = 'controller/'.$file.'.php';
            $tpl = str_replace("{{controller}}", ucfirst($file), $template);
            file_put_contents($filename, $tpl);

            echo "$file\n";
        }
    }
    closedir($handle);
}

//if ($handle = opendir('.'))
//{
//    while (false !== ($file = readdir($handle)))
//    {
//        if (!is_dir($file))
//        {
//            $dirname = str_replace(".html","", $file);
//            $newfile = $dirname . '/index.html';
//            mkdir($dirname);
//            copy($file,$newfile);
//            echo "$file\n";
//        }
//    }
//    closedir($handle);
//}