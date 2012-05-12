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
 * QuickPHP 电子邮件发送操作
 *
 * @category    QuickPHP
 * @package     Email
 * @author      BoPo <ibopo@126.com>
 * @copyright   Copyright &copy; 2010 QuickPHP
 * @license     http://www.quickphp.net/license/
 * @version     $Id: Email.php 8761 2012-01-15 05:10:59Z bopo $
 */
class QuickPHP_Email
{

    protected $useragent      = "QuickPHP Agent";
    protected $mailpath       = "/usr/sbin/sendmail"; // Sendmail path
    protected $protocol       = "mail"; // mail/sendmail/smtp
    protected $smtp_host      = ""; // SMTP Server.  Example: mail.earthlink.net
    protected $smtp_user      = ""; // SMTP Username
    protected $smtp_pass      = ""; // SMTP Password
    protected $smtp_port      = "25"; // SMTP Port
    protected $smtp_timeout   = 5; // SMTP Timeout in seconds
    protected $wordwrap       = TRUE; // true/false  Turns word-wrap on/off
    protected $wrapchars      = "76"; // Number of characters to wrap at.
    protected $mailtype       = "text"; // text/html  Defines email formatting
    protected $charset        = "gb2312"; // Default char set: iso-8859-1 or us-ascii
    protected $multipart      = "mixed"; // "mixed" (in the body) or "related" (separate)
    protected $alt_message    = ''; // Alternative message for HTML emails
    protected $validate       = FALSE; // true/false.  Enables email validation
    protected $priority       = "3"; // Default priority (1 - 5)
    protected $newline        = "\n"; // Default newline. "\r\n" or "\n" (Use "\r\n" to comply with RFC 822)
    protected $crlf           = "\n"; // The RFC 2045 compliant CRLF for quoted-printable is "\r\n".  Apparently some servers,
    // even on the receiving end think they need to muck with CRLFs, so using "\n", while
    // distasteful, is the only thing that seems to work for all environments.
    protected $bcc_batch_mode = FALSE; // true/false  Turns on/off Bcc batch feature
    protected $bcc_batch_size = 200; // If bcc_batch_mode = true, sets max number of Bccs in each batch
    protected $_subject       = "";
    protected $_body          = "";
    protected $_finalbody     = "";
    protected $_alt_boundary  = "";
    protected $_atc_boundary  = "";
    protected $_header_str    = "";
    protected $_smtp_connect  = "";
    protected $_encoding      = "8bit";
    protected $_safe_mode     = FALSE;
    protected $_IP            = FALSE;
    protected $_smtp_auth     = FALSE;
    protected $_replyto_flag  = FALSE;
    protected $_debug_msg     = array();
    protected $_recipients    = array();
    protected $_cc_array      = array();
    protected $_bcc_array     = array();
    protected $_headers       = array();
    protected $_attach_name   = array();
    protected $_attach_type   = array();
    protected $_attach_disp   = array();
    protected $_protocols     = array('mail', 'sendmail', 'smtp');
    protected $_base_charsets = array('gb2312', 'us-ascii');
    protected $_bit_depths    = array('7bit', '8bit');
    protected $_priorities    = array('1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)');

    /**
     * 初始化函数
     *
     * 构造函数将 config 数组遍历并设置到相应名称的属性上
     * @access  public
     * @param   array
     * @return  void
     */
    public function __construct($config = array())
    {
        $this->_clear();

        foreach ($config as $key => $val)
        {
            if(isset($this->$key))
            {
                $method = 'set_' . $key;

                if(method_exists($this, $method))
                {
                    $this->$method($val);
                }
                else
                {
                    $this->$key = $val;
                }
            }
        }

        $this->_smtp_auth = ($this->smtp_user == '' and $this->smtp_pass == '') ? false : true;
        $this->_safe_mode = (@ini_get("safe_mode") == 0) ? false : true;
    }

    /**
     * 清空所有配置
     *
     * @access  public
     * @return  void
     */
    protected function _clear($clear_attachments = false)
    {
        $this->_subject      = "";
        $this->_body         = "";
        $this->_finalbody    = "";
        $this->_header_str   = "";
        $this->_replyto_flag = false;
        $this->_recipients   = array();
        $this->_headers      = array();
        $this->_debug_msg    = array();

        $this->_set_header('User-Agent', $this->useragent);
        $this->_set_header('Date', $this->_set_date());

        if($clear_attachments !== false)
        {
            $this->_attach_name = array();
            $this->_attach_type = array();
            $this->_attach_disp = array();
        }
    }

    /**
     * 设置发信人邮件地址、发信人姓名
     *
     * @access  public
     * @param   string 发信人邮件地址
     * @param   string 发信人姓名
     * @return  void
     */
    public function from($from, $name = '')
    {
        if(preg_match('/\<(.*)\>/', $from, $match))
        {
            $from = $match['1'];
        }

        if($this->validate)
        {
            $this->validate_email($this->_str_to_array($from));
        }

        if($name != '' && substr($name, 0, 1) != '"')
        {
            $name = '"' . $name . '"';
        }

        $this->_set_header('From', $name . ' <' . $from . '>');
        $this->_set_header('Return-Path', '<' . $from . '>');
    }

    /**
     * 设置 Reply-to
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     */
    public function reply_to($replyto, $name = '')
    {
        if(preg_match('/\<(.*)\>/', $replyto, $match))
        {
            $replyto = $match['1'];
        }

        if($this->validate)
        {
            $this->validate_email($this->_str_to_array($replyto));
        }

        if($name == '')
        {
            $name = $replyto;
        }

        if(substr($name, 0, 1) != '"')
        {
            $name = '"' . $name . '"';
        }

        $this->_set_header('Reply-To', $name . ' <' . $replyto . '>');
        $this->_replyto_flag = true;
    }

    /**
     * 收信人的电子邮件地址
     *
     * @access  public
     * @param   string 电子邮件地址，多个用户“,”分割
     * @return  void
     */
    public function to($to)
    {
        $to = $this->_str_to_array($to);
        $to = $this->clean_email($to);

        if($this->validate)
        {
            $this->validate_email($to);
        }

        if($this->_get_protocol() != 'mail')
        {
            $this->_set_header('To', implode(", ", $to));
        }

        switch ($this->_get_protocol())
        {
            case 'smtp' :
                $this->_recipients = $to;
                break;
            case 'sendmail' :
                $this->_recipients = implode(", ", $to);
                break;
            case 'mail' :
                $this->_recipients = implode(", ", $to);
                break;
        }
    }

    /**
     * 设置 抄送
     *
     * @access  public
     * @param   string 电子邮件地址，多个用户“,”分割
     * @return  void
     */
    public function cc($cc)
    {
        $cc = $this->_str_to_array($cc);
        $cc = $this->clean_email($cc);

        if($this->validate)
        {
            $this->validate_email($cc);
        }

        $this->_set_header('Cc', implode(", ", $cc));

        if($this->_get_protocol() == "smtp")
        {
            $this->_cc_array = $cc;
        }
    }

    /**
     * 设置暗送
     *
     * @access  public
     * @param   string  电子邮件地址，多个用户“,”分割
     * @param   string
     * @return  void
     */
    public function bcc($bcc, $limit = '')
    {
        if($limit != '' && is_numeric($limit))
        {
            $this->bcc_batch_mode = true;
            $this->bcc_batch_size = $limit;
        }

        $bcc = $this->_str_to_array($bcc);
        $bcc = $this->clean_email($bcc);

        if($this->validate)
        {
            $this->validate_email($bcc);
        }

        if(($this->_get_protocol() == "smtp") or ($this->bcc_batch_mode && count($bcc) > $this->bcc_batch_size))
        {
            $this->_bcc_array = $bcc;
        }
        else
        {
            $this->_set_header('Bcc', implode(", ", $bcc));
        }
    }

    /**
     * 设置邮件主题
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function subject($subject)
    {
        $subject = preg_replace("/(\r\n)|(\r)|(\n)/", "", $subject);
        $subject = preg_replace("/(\t)/", " ", $subject);
        $this->_set_header('Subject', trim($subject));
    }

    /**
     * 设置正文
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function message($body)
    {
        $this->_body = stripslashes(rtrim(str_replace("\r", "", $body)));
    }

    /**
     * 指定附件
     *
     * @access  public
     * @param   string
     * @return  string
     */
    public function attach($filename, $disposition = 'attachment')
    {
        $this->_attach_name[] = $filename;
        $this->_attach_type[] = $this->_mime_types(next(explode('.', basename($filename))));
        $this->_attach_disp[] = $disposition; // Can also be 'inline'  Not sure if it matters
    }

    /**
     * 添加一个header内容
     *
     * @access  public
     * @param   string
     * @param   string
     * @return  void
     */
    protected function _set_header($header, $value)
    {
        $this->_headers[$header] = $value;
    }

    /**
     * 字符串转数组
     *
     * @access  public
     * @param   string
     * @return  array
     */
    protected function _str_to_array($email)
    {
        if( ! is_array($email))
        {
            $xmail = explode(',', $email);

            foreach($xmail as $key=>$val)
            {
                if( ! empty($val))
                {
                    $emails[] = trim($val);
                }
            }
        }

        return $emails;
    }

    /**
     * 设置复合值
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_alt_message($str = '')
    {
        $this->alt_message = ($str == '') ? '' : $str;
    }

    /**
     * 设置mail类型
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_mailtype($type = 'text')
    {
        $this->mailtype = ($type == 'html') ? 'html' : 'text';
    }

    /**
     * 设置整字换行
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_wordwrap($wordwrap = true)
    {
        $this->wordwrap = ($wordwrap === false) ? false : true;
    }

    /**
     *
     * 设置协议
     *
     * @access  public
     * @param emue $protocol (mail,smtp,sendmail)
     */
    public function set_protocol($protocol = 'mail')
    {
        $this->protocol = ( ! in_array($protocol, $this->_protocols, true)) ? 'mail' : strtolower($protocol);
    }

    /**
     * 设置优先级别
     *
     * @access  public
     * @param   integer
     * @return  void
     */
    public function set_priority($n = 3)
    {
        if( ! is_numeric($n))
        {
            $this->priority = 3;
            return;
        }

        if($n < 1 or $n > 5)
        {
            $this->priority = 3;
            return;
        }

        $this->priority = $n;
    }

    /**
     * 设置换行符字符
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function set_newline($newline = "\n")
    {
        if($newline != "\n" and $newline != "\r\n" and $newline != "\r")
        {
            $this->newline = "\n";
            return;
        }

        $this->newline = $newline;
    }

    /**
     * 设置邮件边界
     *
     * @access  private
     * @return  void
     */
    protected function _set_boundaries()
    {
        $this->_alt_boundary = "B_ALT_" . uniqid(''); // multipart/alternative
        $this->_atc_boundary = "B_ATC_" . uniqid(''); // attachment boundary
    }

    /**
     * 得到邮件ID
     *
     * @access  private
     * @return  string
     */
    protected function _get_message_id()
    {
        $from = $this->_headers['Return-Path'];
        $from = str_replace(">", "", $from);
        $from = str_replace("<", "", $from);

        return "<" . uniqid('') . strstr($from, '@') . ">";
    }

    /**
     * 获得邮件协议
     *
     * @access  private
     * @param   bool
     * @return  string
     */
    protected function _get_protocol($return = true)
    {
        $this->protocol = strtolower($this->protocol);
        $this->protocol = ( ! in_array($this->protocol, $this->_protocols, true)) ? 'mail' : $this->protocol;

        if($return == true)
        {
            return $this->protocol;
        }
    }

    /**
     * 获得邮件编码
     *
     * @access  private
     * @param   bool
     * @return  string
     */
    protected function _get_encoding($return = true)
    {
        $this->_encoding = ( ! in_array($this->_encoding, $this->_bit_depths)) ? '7bit' : $this->_encoding;

        if( ! in_array($this->charset, $this->_base_charsets, true))
        {
            $this->_encoding = "8bit";
        }

        if($return == true)
        {
            return $this->_encoding;
        }
    }

    /**
     * 获得邮件的类型 (text/html/attachment)
     *
     * @access  private
     * @return  string
     */
    protected function _get_content_type()
    {
        if($this->mailtype == 'html' && count($this->_attach_name) == 0)
        {
            return 'html';
        }
        elseif($this->mailtype == 'html' && count($this->_attach_name) > 0)
        {
            return 'html-attach';
        }
        elseif($this->mailtype == 'text' && count($this->_attach_name) > 0)
        {
            return 'plain-attach';
        }
        else
        {
            return 'plain';
        }
    }

    /**
     * 设置 RFC 822 数据
     *
     * @access  private
     * @return  string
     */
    protected function _set_date()
    {
        $timezone = date("Z");
        $operator = (substr($timezone, 0, 1) == '-') ? '-' : '+';
        $timezone = abs($timezone);
        $timezone = ($timezone / 3600) * 100 + ($timezone % 3600) / 60;

        return sprintf("%s %s%04d", date("D, j M Y H:i:s"), $operator, $timezone);
    }

    /**
     * Mime message
     *
     * @access  private
     * @return  string
     */
    protected function _get_mime_message()
    {
        return "This is a multi-part message in MIME format." .
            $this->newline . "Your email application may not support this format.";
    }

    /**
     * 验证邮件地址
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    public function validate_email($email)
    {
        if( ! is_array($email))
        {
            $email = array($email);
        }

        foreach ($email as $val)
        {
            if( ! $this->valid_email($val))
            {
                $this->_set_error_message('email_invalid_address', $val);
                return false;
            }
        }
    }

    /**
     * 验证邮件地址是否是合法的格式
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    public function valid_email($address)
    {
        if( ! preg_match('/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix', $address))
        {
            return false;
        }

        return true;
    }

    /**
     * 自动分离outlook 邮件地址本格式的邮件 例如:BoPo <ibopo@126.com>类型的
     *
     * @access  public
     * @param   string
     * @return  string
     */
    public function clean_email($email)
    {
        if( ! is_array($email))
        {
            if(preg_match('/\<(.*)\>/', $email, $match))
            {
                return $match['1'];
            }
            else
            {
                return $email;
            }
        }

        $clean_email = array();

        for ($i = 0; $i < count($email); $i++)
        {
            if(preg_match('/\<(.*)\>/', $email[$i], $match))
            {
                $clean_email[] = $match['1'];
            }
            else
            {
                $clean_email[] = $email[$i];
            }
        }

        return $clean_email;
    }

    /**
     * 构建一个纯文本邮件，如果邮件中有html的标记，将自动去除
     *
     * @access  private
     * @return  string
     */
    protected function _get_alt_message()
    {
        if($this->alt_message != "")
        {
            return $this->word_wrap($this->alt_message, '76');
        }

        if(eregi('\<body(.*)\</body\>', $this->_body, $match))
        {
            $body = $match['1'];
            $body = substr($body, strpos($body, ">") + 1);
        }
        else
        {
            $body = $this->_body;
        }

        $body = trim(strip_tags($body));
        $body = preg_replace('#<!--(.*)--\>#', "", $body);
        $body = str_replace("\t", "", $body);

        for ($i = 20; $i >= 3; $i--)
        {
            $n = "";

            for ($x = 1; $x <= $i; $x++)
            {
                $n .= "\n";
            }

            $body = str_replace($n, "\n\n", $body);
        }

        return $this->word_wrap($body, '76');
    }

    /**
     * 自动换行
     *
     * @access  public
     * @param   string
     * @param   integer
     * @return  string
     */
    public function word_wrap($str, $charlim = '')
    {
        if($charlim == '')
        {
            $charlim = ($this->wrapchars == "") ? "76" : $this->wrapchars;
        }

        $str = preg_replace("| +|", " ", $str);
        $str = preg_replace("/\r\n|\r/", "\n", $str);

        $unwrap = array();

        if(preg_match_all('|(\{unwrap\}.+?\{/unwrap\})|s', $str, $matches))
        {
            for ($i = 0; $i < count($matches['0']); $i++)
            {
                $unwrap[] = $matches['1'][$i];
                $str = str_replace($matches['1'][$i], "{{unwrapped" . $i . "}}", $str);
            }
        }

        $str    = wordwrap($str, $charlim, "\n", false);
        $output = "";

        foreach (explode("\n", $str) as $line)
        {
            if(strlen($line) <= $charlim)
            {
                $output .= $line . $this->newline;
                continue;
            }

            $temp = '';

            while((strlen($line)) > $charlim)
            {
                if(preg_match('!\[url.+\]|://|wwww.!', $line))
                {
                    break;
                }

                $temp .= substr($line, 0, $charlim - 1);
                $line  = substr($line, $charlim - 1);
            }

            if($temp != '')
            {
                $output .= $temp . $this->newline . $line;
            }
            else
            {
                $output .= $line;
            }

            $output .= $this->newline;
        }

        if(count($unwrap) > 0)
        {
            foreach ($unwrap as $key => $val)
            {
                $output = str_replace("{{unwrapped" . $key . "}}", $val, $output);
            }
        }

        return $output;
    }

    /**
     * 构建headers数据
     *
     * @access  public
     * @param   string
     * @return  string
     */
    protected function _build_headers()
    {
        $this->_set_header('X-Sender', $this->clean_email($this->_headers['From']));
        $this->_set_header('X-Mailer', $this->useragent);
        $this->_set_header('X-Priority', $this->_priorities[$this->priority - 1]);
        $this->_set_header('Message-ID', $this->_get_message_id());
        $this->_set_header('Mime-Version', '1.0');
    }

    /**
     * 向邮件的header写数据
     *
     * @access  public
     * @return  void
     */
    protected function _write_headers()
    {
        if($this->protocol == 'mail')
        {
            $this->_subject = $this->_headers['Subject'];
            unset($this->_headers['Subject']);
        }

        reset($this->_headers);
        $this->_header_str = "";

        foreach ($this->_headers as $key => $val)
        {
            $val = trim($val);

            if($val != "")
            {
                $this->_header_str .= $key . ": " . $val . $this->newline;
            }
        }

        if($this->_get_protocol() == 'mail')
        {
            $this->_header_str = substr($this->_header_str, 0, - 1);
        }
    }

    /**
     * 构建邮件正文和附件
     *
     * @access  public
     * @return  void
     */
    protected function _build_message()
    {
        if($this->wordwrap === true and $this->mailtype != 'html')
        {
            $this->_body = $this->word_wrap($this->_body);
        }

        $this->_set_boundaries();
        $this->_write_headers();

        $hdr = ($this->_get_protocol() == 'mail') ? $this->newline : '';

        switch ($this->_get_content_type())
        {
            case 'plain' :
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->_get_encoding();

                if($this->_get_protocol() == 'mail')
                {
                    $this->_header_str .= $hdr;
                    $this->_finalbody = $this->_body;
                    return;
                }

                $hdr .= $this->newline . $this->newline . $this->_body;
                $this->_finalbody = $hdr;
                return;
                break;
            case 'html' :
                $hdr .= "Content-Type: multipart/alternative; boundary=\"" . $this->_alt_boundary . "\"" . $this->newline;
                $hdr .= $this->_get_mime_message() . $this->newline . $this->newline;
                $hdr .= "--" . $this->_alt_boundary . $this->newline;
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->_get_encoding() . $this->newline . $this->newline;
                $hdr .= $this->_get_alt_message() . $this->newline . $this->newline . "--" . $this->_alt_boundary . $this->newline;
                $hdr .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: quoted-printable";
                $this->_body = $this->_prep_quoted_printable($this->_body);

                if($this->_get_protocol() == 'mail')
                {
                    $this->_header_str .= $hdr;
                    $this->_finalbody = $this->_body . $this->newline . $this->newline . "--" . $this->_alt_boundary . "--";

                    return;
                }

                $hdr .= $this->newline . $this->newline;
                $hdr .= $this->_body . $this->newline . $this->newline . "--" . $this->_alt_boundary . "--";
                $this->_finalbody = $hdr;
                return;
                break;
            case 'plain-attach' :
                $hdr .= "Content-Type: multipart/" . $this->multipart . "; boundary=\"" . $this->_atc_boundary . "\"" . $this->newline;
                $hdr .= $this->_get_mime_message() . $this->newline . $this->newline;
                $hdr .= "--" . $this->_atc_boundary . $this->newline;
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->_get_encoding();

                if($this->_get_protocol() == 'mail')
                {
                    $this->_header_str .= $hdr;
                    $body = $this->_body . $this->newline . $this->newline;
                }

                $hdr .= $this->newline . $this->newline;
                $hdr .= $this->_body . $this->newline . $this->newline;
                break;
            case 'html-attach' :
                $hdr .= "Content-Type: multipart/" . $this->multipart . "; boundary=\"" . $this->_atc_boundary . "\"" . $this->newline;
                $hdr .= $this->_get_mime_message() . $this->newline . $this->newline;
                $hdr .= "--" . $this->_atc_boundary . $this->newline;
                $hdr .= "Content-Type: multipart/alternative; boundary=\"" . $this->_alt_boundary . "\"" . $this->newline . $this->newline;
                $hdr .= "--" . $this->_alt_boundary . $this->newline;
                $hdr .= "Content-Type: text/plain; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: " . $this->_get_encoding() . $this->newline . $this->newline;
                $hdr .= $this->_get_alt_message() . $this->newline . $this->newline . "--" . $this->_alt_boundary . $this->newline;
                $hdr .= "Content-Type: text/html; charset=" . $this->charset . $this->newline;
                $hdr .= "Content-Transfer-Encoding: quoted-printable";
                $this->_body = $this->_prep_quoted_printable($this->_body);

                if($this->_get_protocol() == 'mail')
                {
                    $this->_header_str .= $hdr;
                    $body = $this->_body . $this->newline . $this->newline;
                    $body .= "--" . $this->_alt_boundary . "--" . $this->newline . $this->newline;
                }

                $hdr .= $this->newline . $this->newline;
                $hdr .= $this->_body . $this->newline . $this->newline;
                $hdr .= "--" . $this->_alt_boundary . "--" . $this->newline . $this->newline;
                break;
        }

        $attachment = array();
        $z = 0;

        for ($i = 0; $i < count($this->_attach_name); $i++)
        {
            $filename = $this->_attach_name[$i];
            $basename = basename($filename);
            $ctype    = $this->_attach_type[$i];

            if( ! file_exists($filename))
            {
                $this->_set_error_message('email_attachment_missing', $filename);
                return false;
            }

            $h  = "--" . $this->_atc_boundary . $this->newline;
            $h .= "Content-type: " . $ctype . "; ";
            $h .= "name=\"" . $basename . "\"" . $this->newline;
            $h .= "Content-Disposition: " . $this->_attach_disp[$i] . ";" . $this->newline;
            $h .= "Content-Transfer-Encoding: base64" . $this->newline;

            $attachment[$z++] = $h;
            $file = filesize($filename) + 1;

            if( ! $fp = fopen($filename, 'r'))
            {
                $this->_set_error_message('email_attachment_unredable', $filename);
                return false;
            }

            $attachment[$z++] = chunk_split(base64_encode(fread($fp, $file)));
            fclose($fp);
        }

        if($this->_get_protocol() == 'mail')
        {
            $this->_finalbody = $body . implode($this->newline, $attachment) . $this->newline . "--" . $this->_atc_boundary . "--";
            return;
        }

        $this->_finalbody = $hdr . implode($this->newline, $attachment) . $this->newline . "--" . $this->_atc_boundary . "--";

        return true;
    }

    /**
     * Prep Quoted Printable
     *
     * Prepares string for Quoted-Printable Content-Transfer-Encoding
     * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
     *
     * @access  public
     * @param   string
     * @param   integer
     * @return  string
     */
    protected function _prep_quoted_printable($str, $charlim = '')
    {
        if($charlim == '' or $charlim > '76')
        {
            $charlim = '76';
        }

        $str    = preg_replace("| +|", " ", $str);
        $str    = preg_replace("/\r\n|\r/", "\n", $str);
        $str    = str_replace(array('{unwrap}', '{/unwrap}'), '', $str);
        $lines  = preg_split("/\n/", $str);
        $escape = '=';
        $output = '';

        foreach ($lines as $line)
        {
            $length = strlen($line);
            $temp = '';

            for ($i = 0; $i < $length; $i++)
            {
                $char  = substr($line, $i, 1);
                $ascii = ord($char);

                if($i == ($length - 1))
                {
                    $char = ($ascii == '32' or $ascii == '9') ? $escape . sprintf('%02s', dechex($char)) : $char;
                }

                if($ascii == '61')
                {
                    $char = $escape . strtoupper(sprintf('%02s', dechex($ascii))); // =3D
                }

                if((strlen($temp) + strlen($char)) >= $charlim)
                {
                    $output .= $temp . $escape . $this->crlf;
                    $temp    = '';
                }

                $temp .= $char;
            }

            $output .= $temp . $this->crlf;
        }

        $output = substr($output, 0, strlen($this->crlf) * - 1);

        return $output;
    }

    /**
     * 发送邮件
     *
     * @access  public
     * @return  bool
     */
    public function send()
    {
        if($this->_replyto_flag == false)
            $this->reply_to($this->_headers['From']);

        if(( ! isset($this->_recipients) and ! isset($this->_headers['To'])) and ( ! isset($this->_bcc_array) and ! isset($this->_headers['Bcc'])) and ( ! isset($this->_headers['Cc'])))
        {
            $this->_set_error_message('email_no_recipients');
            return false;
        }

        $this->_build_headers();

        if($this->bcc_batch_mode and count($this->_bcc_array) > 0)
            if(count($this->_bcc_array) > $this->bcc_batch_size)
                return $this->batch_bcc_send();

        $this->_build_message();

        if( ! $this->_spool_email())
            return FALSE;
        else
            return TRUE;
    }

    /**
     * 密送邮件
     *
     * @access  public
     * @return  bool
     */
    public function batch_bcc_send()
    {
        $float  = $this->bcc_batch_size - 1;
        $flag   = 0;
        $set    = "";
        $chunk  = array();

        for ($i = 0; $i < count($this->_bcc_array); $i++)
        {
            if(isset($this->_bcc_array[$i]))
                $set .= ", " . $this->_bcc_array[$i];

            if($i == $float)
            {
                $chunk[] = substr($set, 1);
                $float   = $float + $this->bcc_batch_size;
                $set     = "";
            }

            if($i == count($this->_bcc_array) - 1)
                $chunk[] = substr($set, 1);
        }

        for ($i = 0; $i < count($chunk); $i++)
        {
            unset($this->_headers['Bcc']);

            $bcc = $this->_str_to_array($chunk[$i]);
            $bcc = $this->clean_email($bcc);

            if($this->protocol != 'smtp')
                $this->_set_header('Bcc', implode(", ", $bcc));
            else
                $this->_bcc_array = $bcc;

            $this->_build_message();
            $this->_spool_email();
        }
    }

    /**
     * Unwrap special elements
     *
     * @access  private
     * @return  void
     */
    protected function _unwrap_specials()
    {
        $this->_finalbody = preg_replace_callback('/\{unwrap\}(.*?)\{\/unwrap\}/si',
            array($this, '_remove_nl_callback'), $this->_finalbody);
    }

    /**
     * 去掉回车换行等字符
     *
     * @access  private
     * @return  string
     */
    protected function _remove_nl_callback($matches)
    {
        return preg_replace("/(\r\n)|(\r)|(\n)/", "", $matches['1']);
    }

    /**
     * 发送邮件到邮件服务器
     *
     *
     * @access  private
     * @return  bool
     */
    protected function _spool_email()
    {
        $this->_unwrap_specials();

        switch ($this->_get_protocol())
        {
            case 'mail' :
                if( ! $this->_send_with_mail())
                {
                    $this->_set_error_message('email_send_failure_phpmail');
                    return false;
                }
                break;
            case 'sendmail' :
                if( ! $this->_send_with_sendmail())
                {
                    $this->_set_error_message('email_send_failure_sendmail');
                    return false;
                }
                break;
            case 'smtp' :
                if( ! $this->_send_with_smtp())
                {
                    $this->_set_error_message('email_send_failure_smtp');
                    return false;
                }
                break;
        }

        $this->_set_error_message('email_sent', $this->_get_protocol());

        return TRUE;
    }

    /**
     * 使用mail()函数发送
     *
     * @access private
     * @return bool
     */
    protected function _send_with_mail()
    {
        if($this->_safe_mode == TRUE)
        {
            if( ! mail($this->_recipients, $this->_subject, $this->_finalbody, $this->_header_str))
                return FALSE;
            else
                return TRUE;
        }
        else
        {
            if( ! mail($this->_recipients, $this->_subject, $this->_finalbody, $this->_header_str, "-f" . $this->clean_email($this->_headers['From'])))
                return FALSE;
            else
                return TRUE;
        }
    }

    /**
     * 使用Sendmail发送
     *
     * @access  private
     * @return  bool
     */
    protected function _send_with_sendmail()
    {
        $fp = @popen($this->mailpath . " -oi -f " . $this->clean_email($this->_headers['From']) . " -t", 'w');

        if( ! is_resource($fp))
        {
            $this->_set_error_message('email_no_socket');
            return false;
        }

        fputs($fp, $this->_header_str);
        fputs($fp, $this->_finalbody);
        pclose($fp) >> 8 & 0xFF;

        return true;
    }

    /**
     * 使用SMTP发送
     *
     * @access  private
     * @return  bool
     */
    protected function _send_with_smtp()
    {
        if($this->smtp_host == '')
        {
            $this->_set_error_message('email_no_hostname');
            return false;
        }

        $this->_smtp_connect();
        $this->_smtp_authenticate();
        $this->_send_command('from', $this->clean_email($this->_headers['From']));

        foreach ($this->_recipients as $val)
            $this->_send_command('to', $val);

        if(count($this->_cc_array) > 0)
            foreach ($this->_cc_array as $val)
                if($val != "")
                    $this->_send_command('to', $val);

        if(count($this->_bcc_array) > 0)
            foreach ($this->_bcc_array as $val)
                if($val != "")
                    $this->_send_command('to', $val);

        $this->_send_command('data');
        $this->_send_data($this->_header_str . $this->_finalbody);
        $this->_send_data('.');

        $reply = $this->_get_smtp_data();
        $this->_set_error_message($reply);

        if(substr($reply, 0, 3) != '250')
        {
            $this->_set_error_message('email_smtp_error', $reply);
            return false;
        }

        $this->_send_command('quit');
        return true;
    }

    /**
     * 连接 SMTP 服务器
     *
     * @access  public
     * @param   string
     * @return  string
     */
    protected function _smtp_connect()
    {
        $this->_smtp_connect = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);

        if( ! is_resource($this->_smtp_connect))
        {
            $this->_set_error_message('email_smtp_error', $errno . " " . $errstr);
            return false;
        }

        $this->_set_error_message($this->_get_smtp_data());

        return $this->_send_command('hello');
    }

    /**
     * 发送 SMTP 命令
     *
     * @access  protected
     * @param   string
     * @param   string
     * @return  string
     */
    protected function _send_command($cmd, $data = '')
    {
        switch ($cmd)
        {
            case 'hello' :
                if($this->_smtp_auth or $this->_get_encoding() == '8bit')
                    $this->_send_data('EHLO ' . $this->_get_hostname());
                else
                    $this->_send_data('HELO ' . $this->_get_hostname());

                $resp = 250;
                break;
            case 'from' :
                $this->_send_data('MAIL FROM:<' . $data . '>');
                $resp = 250;
                break;
            case 'to' :
                $this->_send_data('RCPT TO:<' . $data . '>');
                $resp = 250;
                break;
            case 'data' :
                $this->_send_data('DATA');
                $resp = 354;
                break;
            case 'quit' :
                $this->_send_data('QUIT');
                $resp = 221;
                break;
        }

        $reply = $this->_get_smtp_data();
        $this->_debug_msg[] = "<pre>" . $cmd . ": " . $reply . "</pre>";

        if(substr($reply, 0, 3) != $resp)
        {
            $this->_set_error_message('email_smtp_error', $reply);
            return false;
        }

        if($cmd == 'quit')
            fclose($this->_smtp_connect);

        return true;
    }

    /**
     * SMTP 权限认证
     *
     * @access  private
     * @return  bool
     */
    protected function _smtp_authenticate()
    {
        if( ! $this->_smtp_auth)
            return true;

        if($this->smtp_user == "" and $this->smtp_pass == "")
        {
            $this->_set_error_message('email_no_smtp_unpw');
            return false;
        }

        $this->_send_data('AUTH LOGIN');
        $reply = $this->_get_smtp_data();

        if(substr($reply, 0, 3) != '334')
        {
            $this->_set_error_message('email_filed_smtp_login', $reply);
            return false;
        }

        $this->_send_data(base64_encode($this->smtp_user));
        $reply = $this->_get_smtp_data();

        if(substr($reply, 0, 3) != '334')
        {
            $this->_set_error_message('email_smtp_auth_un', $reply);
            return false;
        }

        $this->_send_data(base64_encode($this->smtp_pass));
        $reply = $this->_get_smtp_data();

        if(substr($reply, 0, 3) != '235')
        {
            $this->_set_error_message('email_smtp_auth_pw', $reply);
            return false;
        }

        return true;
    }

    /**
     * 发送 SMTP 数据
     *
     * @access  private
     * @return  bool
     */
    protected function _send_data($data)
    {
        if( ! fwrite($this->_smtp_connect, $data . $this->newline))
        {
            $this->_set_error_message('email_smtp_data_failure', $data);
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * 获得 SMTP 数据
     *
     * @access  private
     * @return  string
     */
    protected function _get_smtp_data()
    {
        $data = '';

        while((bool) ($str = fgets($this->_smtp_connect, 512)))
        {
            $data .= $str;

            if(substr($str, 3, 1) == " ")
                break;
        }

        return $data;
    }

    /**
     * 获得主机名称
     *
     * @access  private
     * @return  string
     */
    protected function _get_hostname()
    {
        return (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost.localdomain';
    }

    /**
     * 获得IP地址
     *
     * @access  private
     * @return  string
     */
    protected function _get_ip()
    {
        if($this->_IP !== false)
            return $this->_IP;

        $cip = (isset($_SERVER['HTTP_CLIENT_IP']) and $_SERVER['HTTP_CLIENT_IP'] != "")
            ? $_SERVER['HTTP_CLIENT_IP'] : false;

        $rip = (isset($_SERVER['REMOTE_ADDR']) and $_SERVER['REMOTE_ADDR'] != "")
            ? $_SERVER['REMOTE_ADDR'] : false;

        $fip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and $_SERVER['HTTP_X_FORWARDED_FOR'] != "")
            ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;

        if($cip && $rip)
            $this->_IP = $cip;
        elseif($rip)
            $this->_IP = $rip;
        elseif($cip)
            $this->_IP = $cip;
        elseif($fip)
            $this->_IP = $fip;

        if(strstr($this->_IP, ','))
        {
            $x          = explode(',', $this->_IP);
            $this->_IP  = end($x);
        }

        if( ! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $this->_IP))
            $this->_IP = '0.0.0.0';

        unset($cip, $rip, $fip);
        return $this->_IP;
    }

    /**
     * 获得调试信息
     *
     * @access  public
     * @return  string
     */
    public function print_debugger()
    {
        $msg = '';

        if(count($this->_debug_msg) > 0)
            foreach ($this->_debug_msg as $val)
                $msg .= $val;

        $msg .= "<pre>" . $this->_header_str . "\n" . htmlspecialchars($this->_subject) . "\n" . htmlspecialchars($this->_finalbody) . '</pre>';
        echo $msg;
    }

    /**
     * 设置邮件正文内容
     *
     * @access  public
     * @param   string
     * @return  string
     */
    protected function _set_error_message($msg, $val = '')
    {
        $this->_debug_msg[] = str_replace('%s', $val, $msg) . "<br />";
    }

    /**
     * Mime 类型函数
     *
     * @access  private
     * @param   string
     * @return  string
     */
    protected function _mime_types($ext = "")
    {
        return QuickPHP::config('mimes')->get(strtolower($ext), "application/x-unknown-content-type");
    }

    public function set_charset($charset = 'gb2312')
    {
        $this->charset = $charset;
    }

}