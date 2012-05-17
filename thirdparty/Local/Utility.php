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
 * @author  Chris.chen <chris.chen@reyosoft.com>
 * @copyright (c) Endeavor Security, Inc. 2008 (http://www.endeavorsecurity.com)
 * @license   http://www.endeavorsecurity.com/sws/license.html
 * @version   $Id: Utility.php 868 2009-09-11 09:17:33Z bopo $
 */

/**
 * Class Utility
 *
 * All common function
 */
class Utility
{
    /**
     * View any string as a hexdump.
     *
     * This is most commonly used to view binary data from streams
     * or sockets while debugging, but can be used to view any string
     * with non-viewable characters.
     *
     * @param       string  $data        The string to be dumped
     * @param       bool    $htmloutput  Set to false for non-HTML output
     * @param       bool    $uppercase   Set to true for uppercase hex
     * @param       bool    $return      Set to true to return the dump
     * @param       array   $formating   the positions of token hitted
     *
     * @return      string               dump with html
     */
    function hexdump ($data, $htmloutput = true, $uppercase = false, $return = false, $formating = array())
    {
        $hexi = $ascii = $dump = '';
        // Upper or lower case hexidecimal
        if ($uppercase === false) {
            $x = 'x';
        }
        else {
            $x = 'X';
        }

        // Split the data into lines 16 chars long
        $lines = array();
        $i = 0;
        while ($i * 16 < strlen($data)) {
            $lines[] = substr($data, 16 * $i++, 16);
        }

        // Loop through each line
        if($htmloutput) {
            $dump .= "<table width=\"100%\" border=\"0\">";
        }
        $offset = 0;
        $fflag = false;
        foreach ($lines as $line) {
            // Loop through each char in the line
            for ($i = 0; $i < strlen($line); $i++) {
                // Convert to hexidecimal
                $ii = intval($offset + $i);
                if( isset($formating[$ii]) && ($formating[$ii] == 1)) {
                    if(!$fflag) {
                        $hexi .= "<span class=\"highlight\">";
                        $ascii .= "<span class=\"highlight\">";
                        $fflag = true;
                    }
                }
                else {
                    if($fflag) {
                        $hexi .= "</span>";
                        $ascii .= "</span>";
                    }
                    $fflag = false;
                }
                $hexi .= sprintf("%02$x&nbsp;", ord($line{$i}));
                // Replace non-viewable bytes with '.'
                if (ord($line{$i}) >= 32) {
                    $ascii .= $htmloutput === true ? htmlentities($line{$i}) : $line{$i};
                }
                else {
                    $ascii .= '.';
                }

                // Split into two columns
                if ($i == 7) {
                    $hexi .= '&nbsp;';
                    $ascii .= '&nbsp;';
                }
            }

            if($fflag) {
                $hexi .= "</span>";
                $ascii .= "</span>";
            }
            $fflag = false;

            // Join the hexi / ascii output
            if($htmloutput) {
                $dump .= "<tr><td><span class=\"hexStuff\">";
            }
            if($i < 16) {
                for($j=0; $j<16-$i; $j++) {
                    if($i + $j == 7){
                        $hexi .= "&nbsp";
                    }
                    $hexi .= "&nbsp;&nbsp;&nbsp;";
                }
            }
            $dump .= sprintf("%04$x&nbsp;&nbsp;&nbsp;%-49s&nbsp;&nbsp;&nbsp;%s\n", $offset, $hexi, $ascii);
            if($htmloutput) {
                $dump .= "</span></td></tr>";
            }
            // Line count
            $offset += 16;
            // Reset
            $hexi = $ascii = '';
        }
        if($htmloutput) {
            $dump .= "</table>";
        }

        // Strip last newline
        //$dump = substr($dump, 0, -1);
        $dump .= "\n";

        // Output method
        if ($return === false)
        {
            echo $dump;
        }
        else
        {
            return $dump;
        }
    }
}
