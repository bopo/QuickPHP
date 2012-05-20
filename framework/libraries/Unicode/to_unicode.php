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
 * Unicode::to_unicode
 *
 * @category   Library
 * @package    Unicode
 * @author     bopo <ibopo@126.com>
 * @copyright  (c) 2007 Quick
 * @license    http://www.quickphp.net/licenses/
 */
function _to_unicode($str)
{
    $mState = 0; 
    $mUcs4  = 0; 
    $mBytes = 1; 
    $out    = array();
    $len    = strlen($str);

    for ($i = 0; $i < $len; $i++)
    {
        $in = ord($str[$i]);

        if($mState == 0)
        {
            if(0 == (0x80 & $in))
            {
                $out[]  = $in;
                $mBytes = 1;
            }
            elseif(0xC0 == (0xE0 & $in))
            {
                $mUcs4  = $in;
                $mUcs4  = ($mUcs4 & 0x1F) << 6;
                $mState = 1;
                $mBytes = 2;
            }
            elseif(0xE0 == (0xF0 & $in))
            {
                $mUcs4  = $in;
                $mUcs4  = ($mUcs4 & 0x0F) << 12;
                $mState = 2;
                $mBytes = 3;
            }
            elseif(0xF0 == (0xF8 & $in))
            {
                // First octet of 4 octet sequence
                $mUcs4  = $in;
                $mUcs4  = ($mUcs4 & 0x07) << 18;
                $mState = 3;
                $mBytes = 4;
            }
            elseif(0xF8 == (0xFC & $in))
            {
                $mUcs4  = $in;
                $mUcs4  = ($mUcs4 & 0x03) << 24;
                $mState = 4;
                $mBytes = 5;
            }
            elseif(0xFC == (0xFE & $in))
            {
                $mUcs4  = $in;
                $mUcs4  = ($mUcs4 & 1) << 30;
                $mState = 5;
                $mBytes = 6;
            }
            else
            {
                trigger_error('Unicode::to_unicode: Illegal sequence identifier in UTF-8 at byte ' . $i, E_USER_WARNING);
                return false;
            }
        }
        else
        {
            if(0x80 == (0xC0 & $in))
            {
                $shift = ($mState - 1) * 6;
                $tmp   = $in;
                $tmp   = ($tmp & 0x0000003F) << $shift;
                $mUcs4 |= $tmp;
                
                if(0 == --$mState)
                {
                    if(((2 == $mBytes) and ($mUcs4 < 0x0080)) or ((3 == $mBytes) and ($mUcs4 < 0x0800)) or ((4 == $mBytes) and ($mUcs4 < 0x10000)) or (4 < $mBytes) or (($mUcs4 & 0xFFFFF800) == 0xD800) or ($mUcs4 > 0x10FFFF))
                    {
                        trigger_error('Unicode::to_unicode: Illegal sequence or codepoint in UTF-8 at byte ' . $i, E_USER_WARNING);
                        return false;
                    }

                    if(0xFEFF != $mUcs4)
                    {
                        $out[] = $mUcs4;
                    }

                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                }
            }
            else
            {
                trigger_error('Unicode::to_unicode: Incomplete multi-octet sequence in UTF-8 at byte ' . $i, E_USER_WARNING);
                return false;
            }
        }
    }
    
    return $out;
}