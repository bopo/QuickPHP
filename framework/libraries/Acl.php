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
 * Provides Access Control List feature to the application.
 * Acl(访问控制列表)。
 *
 * <p>DooAcl performs authorization checks for the specified resource and action. It checks against the rules defined in acl.conf.php.</p>
 * <p>Only when the user is allowed by one of the rules, will he be able to access the action.
 * If the user role cannot be found in both deny and allow list, he will not be able to access the action/resource</p>
 *
 * <p>Rules has to be defined in this way:</p>
 * <code>
 * # Allow member to access all actions in Sns and Blog resource.
 * $acl['member']['allow'] = array(
 * 'SnsController'=>'*',
 * 'BlogController'=>'*',
 * );
 *
 * # Allow anonymous visitors for Blog index only.
 * $acl['anonymous']['allow'] = array(
 * 'BlogController'=>'index',
 * );
 *
 * # Deny member from banUser, showVipHome, etc.
 * $acl['member']['deny'] = array(
 * 'SnsController'=>array('banUser', 'showVipHome'),
 * 'BlogController' =>array('deleteComment', 'writePost')
 * );
 *
 * # Admin can access all except Sns showVipHome
 * $acl['admin']['allow'] = '*';
 * $acl['admin']['deny'] = array(
 * 'SnsController'=>array('showVipHome')
 * );
 *
 * # If member is denied, reroute to the following routes.
 * $acl['member']['failRoute'] = array(
 * //if not found this will be used
 * '_default'=>'/error/member',
 *
 * //if denied from sns banUser
 * 'SnsController/banUser'=>'/error/member/sns/notAdmin',
 *
 * 'SnsController/showVipHome'=>'/error/member/sns/notVip',
 * 'BlogController'=>'/error/member/blog/notAdmin'
 * );
 * </code>
 *
 * <p>You have to assign the rules to DooAcl in bootstrap.</p>
 * <code>
 * # set rules
 * QuickPHP::acl()->rules = $acl;
 *
 * # The default route to be reroute to when resource is denied. If not set, 404 error will be displayed.
 * QuickPHP::acl()->defaultFailedRoute = '/error';
 * </code>
 *
 * @category    QuickPHP
 * @package     Libraries
 * @subpackage  Acl
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Acl.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_Acl
{
    /**
     * @var $_instance
     */
    protected static $_instance;
    
    /**
     * 设置Acl规则。 定义在acl.php
     * @var array
     */
    public $rules;
    
    /**
     * 默认的控制规则,如果没有自定义失败控制规则是定义的某些规则。
     * @var string|array
     */
    public $defaultFailedRoute = array('/error-default/failed-route/please-set-in-route', 404);
    
    /**
     * 返回一个单身Acl的实例。
     *
     * @param   string  访问规则
     * @return  Cache
     */
    public static function instance(array $rules = null)
    {
        if(! isset(Acl::$_instance))
        {
            Acl::$_instance = new Acl($rules);
        }
        
        return Acl::$_instance;
    }
    
    final public function __construct($rules = NULL)
    {
        $this->rules = $rules;
    }
    
    final public function rule($rule = NULL, $params = array())
    {
        $this->rules += array($rule, $params);
    }
    
    final public function rules($rules = NULL)
    {
        $this->rules += $rules;
    }
    
    /**
     * 检查用户角色是否可以访问资源、行为列表以及两者均可访问。
     *
     * <code>
     * //Check if member is allowed for BlogController->post
     * QuickPHP::acl()->isAllowed('member', 'BlogController', 'post' );
     *
     * //Check if member is allowed for BlogController
     * QuickPHP::acl()->isAllowed('member', 'blog');
     * </code>
     *
     * @param string $role Role of a user, usually retrieve from user's login session
     * @param string $resource Resource name (use Controller class name)
     * @param string $action Action name (use Method name)
     * @return bool
     */
    final public function isAllowed($role, $resource, $action = '')
    {
        if($action == '')
        {
            return isset($this->rules[$role]['allow'][$resource]);
        }
        else
        {
            if(isset($this->rules[$role]['allow'][$resource]))
            {
                $actionlist = $this->rules[$role]['allow'][$resource];
                
                if($actionlist === '*')
                {
                    return true;
                }
                else
                {
                    return in_array($action, $actionlist);
                }
            }
            else
            {
                return false;
            }
        }
    }
    
    /**
     * 检查用户角色是否已经被屏蔽访问资源、行为列表以及两者均可访问。
     *
     * <code>
     * //Check if member is denied from BlogController->post
     * QuickPHP::acl()->isDenied('member', 'BlogController', 'post' );
     *
     * //Check if member is denied from BlogController
     * QuickPHP::acl()->isDenied('member', 'blog');
     * </code>
     *
     * @param string $role Role of a user, usually retrieve from user's login session
     * @param string $resource Resource name (use Controller class name)
     * @param string $action Action name (use Method name)
     * @return bool
     */
    final public function isDenied($role, $resource, $action = '')
    {
        if($action == '')
        {
            return isset($this->rules[$role]['deny'][$resource]);
        }
        else
        {
            if(isset($this->rules[$role]['deny'][$resource]))
            {
                $actionlist = $this->rules[$role]['deny'][$resource];
                
                if($actionlist === '*')
                {
                    return TRUE;
                }
                else
                {
                    return in_array($action, $actionlist);
                }
            }
            else
            {
                return FALSE;
            }
        }
    }
    
    /**
     * 检查用户的角色,是能访问资源/行动。
     *
     * @param string $role Role of a user, usually retrieve from user's login session
     * @param string $resource Resource name (use Controller class name)
     * @param string $action Action name (use Method name)
     * @return array|string Returns the fail route if user cannot access the resource.
     */
    final public function process($role, $resource, $action = '')
    {
        if($this->isDenied($role, $resource, $action))
        {
            if(isset($this->rules[$role]['failRoute']))
            {
                $route = $this->rules[$role]['failRoute'];
                
                if(is_string($route))
                {
                    return array($route, 'internal');
                }
                else
                {
                    if(isset($route[$resource]))
                    {
                        return (is_string($route[$resource])) ? array($route[$resource], 'internal') : $route[$resource];
                    }
                    elseif(isset($route[$resource . '/' . $action]))
                    {
                        return (is_string($route)) ? array($route, 'internal') : $route[$resource . '/' . $action];
                    }
                    elseif(isset($route['_default']))
                    {
                        return (is_string($route['_default'])) ? array($route['_default'], 'internal') : $route['_default'];
                    }
                    else
                    {
                        return (is_string($this->defaultFailedRoute)) ? array($this->defaultFailedRoute, 404) : $this->defaultFailedRoute;
                    }
                }
            }
        }
        elseif($this->isAllowed($role, $resource, $action) == false)
        {
            if(isset($this->rules[$role]['failRoute']))
            {
                $route = $this->rules[$role]['failRoute'];
                
                if(is_string($route))
                {
                    return array($route, 'internal');
                }
                else
                {
                    if(isset($route[$resource]))
                    {
                        return (is_string($route[$resource])) ? array($route[$resource], 'internal') : $route[$resource];
                    }
                    elseif(isset($route[$resource . '/' . $action]))
                    {
                        return (is_string($route)) ? array($route, 'internal') : $route[$resource . '/' . $action];
                    }
                    elseif(isset($route['_default']))
                    {
                        return (is_string($route['_default'])) ? array($route['_default'], 'internal') : $route['_default'];
                    }
                    else
                    {
                        return (is_string($this->defaultFailedRoute)) ? array($this->defaultFailedRoute, 404) : $this->defaultFailedRoute;
                    }
                }
            }
        }
    }
}