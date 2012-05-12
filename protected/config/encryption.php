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
 * @package  Encrypt
 *
 * Encrypt configuration is defined in groups which allows you to easily switch
 * between different encryption settings for different uses.
 * Note: all groups inherit and overwrite the default group.
 *
 * Group Options:
 * key    - Encryption key used to do encryption and decryption. The default option
 * should never be used for a production website.
 *
 * For best security, your encryption key should be at least 16 characters
 * long and contain letters, numbers, and symbols.
 * @note Do not use a hash as your key. This significantly lowers encryption entropy.
 *
 * mode   - MCrypt encryption mode. By default, MCRYPT_MODE_NOFB is used. This mode
 * offers initialization vector support, is suited to short strings, and
 * produces the shortest encrypted output.
 * @see http://php.net/mcrypt
 *
 * cipher - MCrypt encryption cipher. By default, the MCRYPT_RIJNDAEL_128 cipher is used.
 * This is also known as 128-bit AES.
 * @see http://php.net/mcrypt
 */
return array
(
    'key'    => 'K0H@NA+PHP_7hE-SW!FtFraM3w0R|<',
    'mode'   => MCRYPT_MODE_NOFB,
    'cipher' => MCRYPT_RIJNDAEL_128
);