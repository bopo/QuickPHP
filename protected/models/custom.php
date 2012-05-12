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
 * $Id: custom.php 8646 2012-01-05 11:01:20Z bopo $
 *
 * 首页模块(Home)
 *
 * @package    Search
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */
class Custom_Model extends ORM
{
    /**
     * 保存当前对象
     *
     * @return  ORM
     */
    public function save()
    {
        if ($this->empty_pk())
            if(isset($this->created))
                $this->created = time();
        else
            if(isset($this->modified))
                $this->modified = time();

        return parent::save();
    }

    /**
     * 保存当前对象
     *
     * @return  ORM
     */
    public function register($array)
    {
        foreach( $array as $key => $value )
            $this->$key = $value;

        return $this->save();
    }

    public function pagination($size = 20)
    {
        $items      = clone $this;
        $records    = $this->count_all();
        $pagination = Pagination::factory()->initialize($records, 3, $size);

        $items->order_by('id','desc');
        $items->limit($pagination->limit());
        $items->offset($pagination->offset());

        return array(
            'items' => $items->find_all()->as_array(),
            'pages' => $pagination->generate(),
        );
    }

}