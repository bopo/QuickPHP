<?php
///$Id$


class Local_Sws_Helper_Packet extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * check bar flag
     *
     * @param string $src
     * @return mixed
     */
    private function checkBarFlag($src)
    {
        $barFlag1 = 0;
        $barFlag2 = 0;
        for($i=0; $i<strlen($src); $i++)
        {
            $c = $src[$i];
            if($c == "|")
            {
                if($i == 0)
                {
                    $barFlag1 = 1;
                }
                else
                {
                    $barFlag2 = 1;
                }
                continue;
            }
            // BlankSpace must be exist in the old hex string
            if($c == " ")
            {
                if($barFlag1)
                {
                    if($barFlag2)
                    {
                        return 1; // "|P| 00 |I| 00 |P| 00 |E | 00 "
                    }
                    else
                    {
                        return 0; // "| 00 02 00 26 00 00 40 b1 0c 10 5c 00 |P| 00 |I| 00 |P| 00 |E | 00 "
                    }
                }
                else
                {
                    if($barFlag2)
                    {
                        return 0; // "P| 00 |I| 00 |P| 00 |E | 00 "
                    }
                    else
                    {
                        return 1; // "00 |P| 00 |I| 00 |P| 00 |E | 00 "
                    }
                }
            }
        }
        // no blankspace declared no old hex part
        return $barFlag1; // "PIPE" or "|PIPE"
    }
    /**
     * filterslash
     *
     * @param string $original
     * @return string
     */
    private function filterslash ($original)
    {
        $hex = "";
        $lc  = "";

        for ($i=0; $i<strlen($original); $i++)
        {
            $c = $original[$i];
            $hex .= $c;
            if ($c == "c" || $c == "C")
            {
                if($lc == "5" && ($i % 2) == 1)
                {
                    $hex .= "5c";
                }
            }
            $lc = $c;
        }
        return $hex;
    }
    /**
     * check the correctly mixed string
     *
     * @param string $original
     * @return string
     */
    private function mix2hex($original)
    {
        $hex        = "";
        $in_hex     = $this->checkBarFlag($original);
        $hexCheck   = true;

        for($i=0; $i<strlen($original); $i++)
        {
            $c = $original[$i];
            if($c == "|")
            {
                $in_hex = ($in_hex == 1 ? 0: 1); // ??
                continue;
            }

            if($hexCheck && $c == " ")
            {
                $hexCheck = false;
                // if start hex string is odd number, so remove the first digit
                if(strlen($hex) % 2 == 1) $hex = substr($hex, 1);
            }

            if($in_hex)
            {
                if(preg_match("/[0-9,a-f,A-F]/", $c) == TRUE) $hex .= $c;
                continue;
            }
            $hex .= $this->hexValue($c);
        }

        if(strlen($hex) % 2 == 1)
        {
            if($hexCheck) // if start hex string is odd number, so remove the first digit
            {
                $hex = substr($hex, 1);
            }
            else // if end hex string is odd number, so remove the last digit
            {
                $hex = substr($hex, 0, strlen($hex) - 1);
            }
        }

        return $hex;
    }
    /**
     * get hex value
     *
     * @param string $char
     * @return string
     */
    private function hexValue ($char)
    {
        $ordinal = ord($char);

        if ($ordinal < 16)
        {
            return "0".dechex($ordinal);
        }
        else
        {
            return dechex($ordinal);
        }
    }
    /**
     * added by CD 04142006  SMB% = > SMB\%
     *
     * @param string $original
     * @return string
     */
    private function addslashlike ($original)
    {
        $hex = "";

        for ($i=0; $i<strlen($original); $i+=2)
        {
            $fc = $original[$i];
            $sc = $original[$i+1];

            if ( ($fc == '2' ) && ( $sc == '5' ) )
            {
                $hex .= "5c";
            }

            $hex .= $fc . $sc ;
        }
        return $hex;
    }
    /**
     * direct
     *
     * @param string $payPacket
     * @return string output sql like where.
     */
    public function direct($payPacket)
    {
        $payPacket = $this->mix2hex($payPacket);
        $payPacket = $this->filterslash($payPacket);
        $payPacket = $this->addslashlike($payPacket);
        return "concat('%',x'".$payPacket."','%')";
    }
}
