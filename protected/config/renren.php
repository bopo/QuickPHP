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
return array
(
        # modify by tom.wang at 2011-05-12 : add relate url for oauth flow
        'AUTHORIZEURL'      => 'https://graph.renren.com/oauth/authorize',          //进行连接授权的地址，不需要修改
        'ACCESSTOKENURL'    => 'https://graph.renren.com/oauth/token',              //获取access token的地址，不需要修改
        'SESSIONKEYURL'     => 'https://graph.renren.com/renren_api/session_key',   //获取session key的地址，不需要修改
        'CALLBACK'          => 'http://127.0.0.128/index.php',                      //回调地址，注意和您申请的应用一致

        'APIURL'            => 'http://api.renren.com/restserver.do',               //RenRen网的API调用地址，不需要修改
        'APIKey'            => 'e65b7c9febaf4a648a6d1d8490376ad4',                  //你的API Key，请自行申请
        'SecretKey'         => 'c824b5bc17304885ae9e122e25021201',                  //你的API 密钥
        'APIVersion'        => '1.0',                                               //当前API的版本号，不需要修改
        'decodeFormat'      => 'json',                                              //默认的返回格式，根据实际情况修改，支持：json,xml
        /*
         *@ 以下接口内容来自http://wiki.dev.renren.com/wiki/API，编写时请遵守以下规则：
         *  key  (键名)     : API方法名，直接Copy过来即可，请区分大小写
         *  value(键值)     : 把所有的参数，包括required及optional，除了api_key,method,v,format不需要填写之外，
         *                    其它的都可以根据你的实现情况来处理，以英文半角状态下的逗号来分割各个参数。
         */
        'APIMapping'    => array(
            'admin.getAllocation'                => '',
            'connect.getUnconnectedFriendsCount' => '',
            'friends.areFriends'                 => 'uids1,uids2',
            'friends.get'                        => 'page,count',
            'friends.getFriends'                 => 'page,count',
            'notifications.send'                 => 'to_ids,notification',
            'users.getInfo'                      => 'uids,fields',
        )
);

