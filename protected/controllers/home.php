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
 * $Id: home.php 8727 2012-01-12 07:25:28Z bopo $
 *
 * 首页模块(Home)
 *
 * @package    Home
 * @author     BoPo <ibopo@126.com>
 * @copyright  (c) 2008-2009 QuickPHP
 * @license    http://www.quickphp.net/license.html
 */

// require_once 'Zend/Search/Lucene.php';

class Home_Controller extends Template_Controller
{
    private function test1()
    {
    }
    private function test2()
    {
    }

    public function __call($method, $args)
    {

        // throw new Exception("Error Processing Request", 1);
        
        // var_dump(QuickPHP::lang('calendar.su',array('aaaa')));
        // $index = new Zend_Search_Lucene(RUNTIME.'/_indexs', true);

        // $doc = new Zend_Search_Lucene_Document();

        // // Store document URL to identify it in search result.
        // $doc->addField(Zend_Search_Lucene_Field::Text('url', $docUrl));

        // // Index document content
        // $doc->addField(Zend_Search_Lucene_Field::UnStored('contents', $docContent));

        // // Add document to the index.
        // $index->addDocument($doc);

        // // Write changes to the index.
        // $index->commit();


        // $hits = $index->find('x');

        // var_dump($hits);

        // foreach ($hits as $hit) {
        //     echo $hit->id;
        //     echo $hit->score;

        //     echo $hit->title;
        //     echo $hit->author;
        // }
//        var_dump(feed::parse('http://manhuashu.net/feed/all.html'));
    }
}