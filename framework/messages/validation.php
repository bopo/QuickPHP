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

return array(
    // 类错误
    'invalid_rule'  => '无效的规则：{0}',
    'i18n_array'    => 'I18N 键 {0} 必须遵循 in_lang 规则且为数组形式',
    'not_callable'  => '校验(Validation)的回调 {0} 不可调用',

    // 通常错误
    'unknown_error' => '验证字段 {0} 时，发生未知错误。',
    'required'      => '字段 {0} 必填。',
    'min_length'    => '字段 {0} 最少 {1} 字符。',
    'max_length'    => '字段 {0} 最多 {1} 字符。',
    'exact_length'  => '字段 {0} 必须包含 {1} 字符。',
    'in_array'      => '字段 {0} 必须选中下拉列表的选项。',
    'matches'       => '字段 {0} 必须与 {1} 字段一致。',
    'valid_url'     => '字段 {0} 必须包含有效的 URL。',
    'valid_email'   => '字段 {0} 无效 Email 地址格式。',
    'valid_ip'      => '字段 {0} 无效 IP 地址。',
    'valid_type'    => '字段 {0} 只可以包含 {1} 字符。',
    'range'         => '字段 {0} 越界指定范围。',
    'regex'         => '字段 {0} 与给定输入模式不匹配。',
    'depends_on'    => '字段 {0} 依赖于 :depend 栏位。',

    // 上传错误
    'user_aborted'  => '文件 {0} 上传过程中被中断。',
    'invalid_type'  => '文件 {0} 非法文件格式。',
    'max_size'      => '文件 {0} 超出最大允许范围. 最大文件大小 {1}。',
    'max_width'     => '文件 {0} 的最大允许宽度 {1} 是 :sizepx。',
    'max_height'    => '文件 {0} 的最大允许高度 {1} 是 :sizepx。',
    'min_width'     => '文件 {0} 太小，最小文件宽度大小 {1}px。',
    'min_height'    => '文件 {0} 太小，最小文件高度大小 {1}px。',

    // 字段类型
    'alpha'         => '字母',
    'alpha_numeric' => '字母和数字',
    'alpha_dash'    => '字母，破折号和下划线',
    'digit'         => '数字',
    'numeric'       => '数字',
);
