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

abstract class Abstract_Frontend_Controller extends Template_Controller
{
    const ALLOW_PRODUCTION = TRUE;

    public $directory = 'frontend';

    /**
     * Loads the template [View] object.
     */
    public function before()
    {
//        if(!file_exists(APPAPTH.'install.lock'))
//            return url::redirect('install');

        return parent::before();
    }

    /**
     * Assigns the template [View] as the request response.
     */
    public function after()
    {
        return parent::after();
    }

    /**
     * Assigns the template [View] as the request response.
     */
    public function success($message = NULL, $redirect = NULL)
    {
        return $this->message($message, 'success', $redirect);
    }

    /**
     * Assigns the template [View] as the request response.
     */
    public function error($message = NULL)
    {
        return $this->message($message, 'error');
    }

    /**
     * Assigns the template [View] as the request response.
     */
    public function message($message = NULL, $status = 'success', $redirect = NULL)
    {
        $value = array('status' => $status, 'message' => $message);

        if(! empty($redirect))
        {
            if(!preg_match ("/\.html$/i", $redirect) and !preg_match ("/^http:\/\//i", $redirect))
                $value['redirect'] = url::bind($redirect);
            else
                $value['redirect'] = $redirect;
        }

        if(request::is_ajax())
            return exit(json_encode($value));
        elseif(! empty($redirect))
            return url::redirect($redirect);
        else
            return ;
    }

}