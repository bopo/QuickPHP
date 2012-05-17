<?php
/**
 * Copyright (c) 2008 Endeavor Security, Inc.
 *
 * This file is part of Signature Wizard System (SWS in short).
 *
 * You should have received a copy of the Software License Agreement
 * along with SWS. All rights reserved.
 *
 * @package library_local
 * @author  Chris.chen <chris.chen@endeavorsecurity.com>
 * @copyright (c) Endeavor Security, Inc. 2008 (http://www.endeavorsecurity.com)
 * @license   http://www.endeavorsecurity.com/sws/license.html
 * @version   $Id: Netbios.php 868 2009-09-11 09:17:33Z bopo $
 */

/**
 * This is a php module that addresses parsing and viewing netbios packets.
 */
class Netbios
{
    function strhex($string)
    {
        $hex = "";

        for ($i=0; $i < strlen($string); $i++)
        {
            $hex .= dechex(ord($string[$i]));
        }

        return $hex;
    }

    function snortHex ($original, $breakSize = 50)
    {
        $hex    = "";
        $in_hex = 0;
        $count  = 0;

        for ($i=0; $i<strlen($original); $i++)
        {
            if ($original[$i] == ' ')
            {
                $count=0;
            }
            else
            {
                $count++;
            }

            if ($count == $breakSize)
            {
                $count = 0;
                $hex .= "<br>";
            }

            if (preg_match("/[0-9,a-z,A-Z, sp, \\, \/, \., \,, \{, \}, \%]/", $original[$i]) == TRUE)
            {
                if ($in_hex == 1)
                {
                    $in_hex = 0;
                    $hex .= " |";
                }

                $hex .= $original[$i];
            }
            else
            {
                $ordinal = ord($original[$i]);

                if ($in_hex == 0)
                {
                    $in_hex = 1;
                    $hex.="|";
                }

                if ($ordinal < 16)
                {
                    $hex.=" 0" . dechex($ordinal);
                }
                else
                {
                    $hex.=" " . dechex($ordinal);
                }
                $count = 0;
            }
        }

        if ($in_hex == 1)
        {
            $in_hex = 0;
            $hex .= " |";
        }

        return $hex;
    }

    function protocolTypeName ($protocolNumber)
    {
        if ($protocolNumber == 6)
        {
            return "TCP";
        }

        if ($protocolNumber == 17)
        {
            return "UDP";
        }

        if ($protocolNumber == 1)
        {
            return "ICMP";
        }

        return  $protocolNumber;
    }

    function bios2ascii ($in_first, $in_second)
    {
        return (($in_first - ord('A')) * 16) + ($in_second - ord('A'));
    }

    function name2ascii ($name)
    {
        $local_text = "";
        // printf ("converting %s --> ", $name);
        for ($i = 0; $i < 32; $i=$i+2)
        {
            // printf ("converting %c to %c ", ord($name[$i]), ord($name[$i+1]));
            $local_text .= chr (bios2ascii(ord($name[$i]), ord($name[$i+1])));
            // printf ("to %c <br>", bios2ascii(ord($name[$i]), ord($name[$i+1])));
        }

        return $local_text;
    }

    function getValue ($string, $length, $index)
    {
        $value = 0;

        for ($i = 0; $i < $length; $i++)
        {
            $value = $value * 256;
            $value = $value + ord($string[$i + $index]);
        }

        return $value;
    }


    function smbCommand ($smb_command)
    {
        switch ($smb_command)
        {
            case 0x00:
                return "Make directory";
                break;
            case 0x01:
                return "Delete directory";
                break;
            case 0x02:
                return "Open File";
                break;
            case 0x03:
                return "Create File";
                break;
            case 0x04:
                return "Close File";
                break;
            case 0x05:
                return "Commit all files";
                break;
            case 0x06:
                return "Delete File";
                break;
            case 0x07:
                return "Rename File";
                break;
            case 0x72:
                return "Verify Dialect";
                break;
            case 0x75:
                return "Tree Connect and X";
                break;
            default:
                return "Not added to script command: $smb_command";
                break;
        }
    }


    function showNetbios ($payload)
    {
        $local_text = "";
        $smb_header = chr(255)."SMB";

        $session_type = getValue ($payload, 1, 0);

        switch ($session_type)
        {
            case 0:
                $local_text = "SESSION TYPE: SESSION MESSAGE<br>";
                $local_text.= "FLAG:".getValue ($payload, 1, 1)."<br>";
                $local_text.= "LENGTH OF PACKET:".getValue ($payload, 2, 2)."<br>";
                $payload    = substr ($payload, 4);
                // SMD Check
                $smb_check  = substr ($payload, 0, 4);

                if (strncmp($smb_check, $smb_header, 4) == 0)
                {
                    // This is a SMB Packer
                    $smb_command    = getValue(substr($payload, 4, 1), 1, 0);
                    $smb_auth_id    = getValue($payload, 2, 28);
                    $smb_process_id = getValue($payload, 2, 30);
                    $smb_user_auth  = getValue ($payload, 2, 32);
                    $smb_multiplex  = getValue ($payload, 2, 34);

                    $local_text    .= "SMB COMMAND: ".smbCommand($smb_command)."<br>";
                    $payload        = substr ($payload, 42);
                    $text           = snortHex($payload, 40);
                    $local_text    .= "<br>PAYLOAD: ".wordwrap ($text, 50, "<br/>\n");
                }
                else
                {
                    $text           = snortHex($payload, 40);
                    $local_text    .= "<br>PAYLOAD: ".wordwrap ($text, 50, "<br/>\n");
                }
                break;
            case 0x81:
                $local_text     = "SESSION TYPE: SESSION REQUEST";
                $CalledName     = substr ($payload, 5, 32);
                $local_text    .= "<br>CALLED NAME: ".name2ascii($CalledName);
                $CallingName    = substr ($payload, 39, 32);
                $local_text    .= "<br>CALLING NAME: ".name2ascii($CallingName);
                break;
            case 0x82:
                $local_text     = "SESSION TYPE: POSITIVE SESSION RESPONSE";
                $text           = snortHex($payload, 40);
                $local_text    .= "<br>PAYLOAD: ".wordwrap ($text, 50, "<br/>\n");
                break;
            case 0x83:
                $local_text     = "SESSION TYPE: NEGATIVE SESSION RESPONSE";
                $text           = snortHex($payload, 40);
                $local_text    .= "<br>PAYLOAD: ".wordwrap ($text, 50, "<br/>\n");
                break;
            case 0x84:
                $local_text     = "SESSION TYPE: RETARGET SESSION RESPONSE";
                $text           = snortHex($payload, 40);
                $local_text    .= "<br>PAYLOAD: ".wordwrap ($text, 50, "<br/>\n");
                break;
            case 0x85:
                $local_text = "SESSION TYPE: SESSION KEEP ALIVE";
                $text = snortHex($payload, 40);
                $local_text .= "<br>PAYLOAD: ".wordwrap ($text, 50, "<br/>\n");
                break;
            default:
                $local_text = "SESSION TYPE: $session_type is unknown";
                $text = snortHex($payload, 40);
                $local_text .= "<br>PAYLOAD: ".wordwrap ($text, 50, "<br/>\n");
                break;
        }
        return $local_text;
    }
}
