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
 * [Tag cloud][ref-tcl] creation library.
 *
 * [ref-tcl]: http://en.wikipedia.org/wiki/Tag_cloud
 *
 * $Id: Tagcloud.php 138 2012-01-30 03:35:57Z bopo $
 *
 * @package    Tagcloud
 * @author     Kohana Team
 * @copyright  (c) 2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Tagcloud
{

    public $shuffle    = false;
    public $min_size   = 80;
    public $max_size   = 140;
    public $attributes = array('class' => 'tag');

    protected $biggest;
    protected $elements;
    protected $smallest;

    /**
     * Creates a new Tagcloud instance and returns it.
     *
     * @chainable
     * @param   array    elements of the tagcloud
     * @param   integer  minimum font size
     * @param   integer  maximum font size
     * @return  Tagcloud
     */
    public static function factory(array $elements, $min_size = null, $max_size = null, $shuffle = false)
    {
        return new Tagcloud($elements, $min_size, $max_size, $shuffle);
    }

    /**
     * Construct a new tagcloud. The elements must be passed in as an array,
     * with each entry in the array having a "title" ,"link", and "count" key.
     * Font sizes will be applied via the "style" attribute as a percentage.
     *
     * @param   array    elements of the tagcloud
     * @param   integer  minimum font size
     * @param   integer  maximum font size
     * @return  void
     */
    public function __construct(array $elements, $min_size = null, $max_size = null, $shuffle = false)
    {
        $this->elements = $elements;

        if($shuffle !== false)
        {
            $this->shuffle = true;
        }

        $counts = array();

        foreach ($elements as $data)
        {
            $counts[] = $data['count'];
        }

        $this->biggest  = max($counts);
        $this->smallest = min($counts);

        if ($min_size !== null)
        {
            $this->min_size = $min_size;
        }

        if ($max_size !== null)
        {
            $this->max_size = $max_size;
        }
    }

    /**
     * Magic __toString method. Returns all of the links as a single string.
     *
     * @return  string
     */
    public function __toString()
    {
        return implode("\n", $this->render());
    }

    /**
     * Renders the elements of the tagcloud into an array of links.
     *
     * @return  array
     */
    public function render()
    {
        if ($this->shuffle === true)
        {
            shuffle($this->elements);
        }

        $attr   = $this->attributes;
        $range  = max($this->biggest  - $this->smallest, 1);
        $scale  = max($this->max_size - $this->min_size, 1);
        $output = array();

        foreach ($this->elements as $data)
        {
            if (strpos($data['title'], ' ') !== false)
            {
                $data['title'] = str_replace(' ', '&nbsp;', $data['title']);
            }

            $size = ((($data['count'] - $this->smallest) * $scale) / $range) + $this->min_size;
            $attr['style'] = 'font-size: '.round($size, 0).'%';
            $output[] = html::anchor($data['link'], $data['title'], $attr)."\n";
        }

        return $output;
    }

}