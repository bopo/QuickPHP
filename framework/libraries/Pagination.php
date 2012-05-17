<?php defined('SYSPATH') or die('No direct access allowed.');
// +----------------------------------------------------------------------+
// | Quick PHP Framework Version 0.10                                     |
// +----------------------------------------------------------------------+
// | Copyright (c) 2007 Quick.cn All rights reserved.                     |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: BoPo <ibopo@126.com>                                         |
// +----------------------------------------------------------------------+
/**
 * 分页类文件
 *
 * @author BoPo <ibopo@126.com>
 * @link http://www.quickphp.net/
 * @copyright Copyright &copy; 2007 Quick
 * @license http://www.quickphp.net/license/
 * @version $Id: Pagination.php 138 2012-01-30 03:35:57Z bopo $
 * @package libraries
 */
/**
 * 分页类
 * @package libraries
 * @author BoPo <ibopo@126.com>
 * @since 0.10
 * @version $Id: Pagination.php 138 2012-01-30 03:35:57Z bopo $
 */
class Pagination implements Countable, Iterator, SeekableIterator, ArrayAccess
{
    protected static $_instance;
    protected $pagecount;
    protected $uri_assoc;
    protected $limit;
    protected $recordcount;
    protected $current;
    protected $perpage;
    protected $uri;
    protected $ar           = array();
    protected $page_flag    = 'p';
    protected $uri_string   = '';
    protected $request      = array();

    public static function factory($config = array())
    {
        empty(self::$_instance) AND self::$_instance = new self($config);
        return self::$_instance;
    }

    public function __construct()
    {
        $this->request = QuickPHP::route()->get('query_array');
        $this->current = isset($this->request[$this->page_flag]) ? $this->request[$this->page_flag] : 1;
    }

    /**
     * 初始化函数
     * @param int $recordcount
     * @param int $pagesize
     * @param int $default
     * @param int $perpage
     */
    public function initialize($recordcount = 0, $perpage = 3, $pagesize = 20)
    {
        $this->recordcount  = $recordcount;
        $this->pagecount    = ceil($recordcount / $pagesize);
        $this->perpage      = $perpage;
        $this->current      = $this->_current();
        $this->offset       = ($this->current - 1) * $pagesize;
        $this->offset       = ($this->offset > 0) ? $this->offset : 0;
        $this->limit        = $pagesize;

        return $this;
    }

    protected function _current()
    {
        if($this->current < 1)
        {
            return 1;
        }

        if($this->current >= $this->pagecount)
        {
            return $this->pagecount;
        }

        return $this->current;
    }

    /**
     * URL重组,自动判断是否开启请求字符串方式
     * @return string
     */
    protected function _newarg($num = 1)
    {
        $request = $this->request;
        unset($request[$this->page_flag]);
        $request[$this->page_flag] = $num;
        $query_string = http_build_query($request);

        return url::site(QuickPHP::route()->get('current_uri', NULL), 'http') . "?" . $query_string;
    }

    /**
     * 数据库 limit 字符串
     * @return string
     */
    public function limit()
    {
        return $this->limit;
    }

    public function offset()
    {
        return $this->offset;
    }

    public function sql_limit()
    {
        return "LIMIT " . $this->limit . " OFFSET " . $this->offset;
    }

    /**
     * 模板输出函数
     * @param bool $sel
     * @return string
     */
    public function generate()
    {
        $result['recordcount'] = $this->recordcount;
        $result['pagecount'] = $this->pagecount;
        $result['current'] = $this->current;

        if($this->current == 1)
        {
            $result['first'] = '';
            $result['prev'] = '';
        }
        else
        {
            $result['first'] = $this->_newarg(1);
            $result['prev'] = $this->_newarg($this->current - 1);
        }

        $startpage  = $this->current - $this->perpage;
        $endpage    = $this->current + $this->perpage;

        if($startpage < 1)
        {
            $endpage  += abs($startpage)+1;
            $startpage = 1;
        }

        if($endpage > $this->pagecount)
        {
            $startpage -= abs($this->pagecount - $endpage);
            $endpage  = $this->pagecount;
        }

        if($startpage <=1)
        {
            $startpage = 1;
        }

        $n = 0;

        for ($i = $startpage; $i <= $endpage; $i++)
        {
            if($i == 0) 
            {
                continue;
            }

            if($i != $this->current)
            {
                $result['pages'][$n]['link'] = $this->_newarg($i);
                $result['pages'][$n]['title'] = $i;
            }
            else
            {
                $result['pages'][$n]['link'] = NULL;
                $result['pages'][$n]['title'] = $i;
                $result['current'] = $i;
            }

            $n++;
        }

        if($this->current == $this->pagecount || $this->pagecount == 0)
        {
            $result['next'] = '';
            $result['end'] = '';
        }
        else
        {
            $result['next'] = $this->_newarg($this->current + 1);
            $result['end'] = $this->_newarg($this->pagecount);
        }

        return $result;
    }

    public function current()
    {
        return $this->current;
    }

    public function pagecount()
    {
        return $this->pagecount;
    }

    public function pagesize()
    {
        return $this->limit;
    }

}
