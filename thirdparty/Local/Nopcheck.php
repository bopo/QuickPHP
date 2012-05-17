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
 * @version   $Id: Nopcheck.php 868 2009-09-11 09:17:33Z bopo $
 */

/**
 * Mark string '|'
 */
class Nopcheck
{
    function markString ($formatString, $marker, $sM, $eM)
    {
        if(empty($sM)){
            $sM = 0;
        }
        if(empty($eM)){
            $eM = 0;
        }
        for ($j = $sM; $j <= $eM; $j++) {
            if(isset($formatString[$j])){
                $flag = $formatString[$j];
            }
            else{
                $flag = 0;
            }
            $formatString[$j] = $flag | $marker;
        }
        return $formatString;
    }

    function nopCheck($inString, $formatString)
    {
        $nop[144] = 1;
        $nop[15] = 1;             /* two byte instruction prefix */
        $nop[240] = 1;             /* lock */
        $nop[242] = 1;             /* repne/repnz */
        $nop[243] = 1;             /* rep/repe/repz */
        $nop[46] = 1;             /* CS segment override */
        $nop[54] = 1;             /* SS segment override */
        $nop[62] = 1;             /* DS segment override */
        $nop[38] = 1;             /* ES segment override */
        $nop[100] = 1;             /* FS segment override */
        $nop[101] = 1;             /* GS segment override  */
        $nop[102] = 1;             /* operand-size override */
        $nop[103] = 1;             /* address-size override */
        $nop[4] = 1;             /* add [byte],%al    */
        $nop[5] = 1;             /* add [dword],%eax  */
        $nop[6] = 1;             /* push es           */
        $nop[12] = 1;             /* or [byte],%al     */
        $nop[13] = 1;             /* or [dword],%eax   */
        $nop[14] = 1;             /* push cs           */
        $nop[20] = 1;             /* adc [byte],%al    */
        $nop[21] = 1;             /* adc [dword],%eax  */
        $nop[22] = 1;             /* push ss           */
        $nop[28] = 1;             /* sbb [byte],%al    */
        $nop[29] = 1;             /* sbb [dword],%eax  */
        $nop[30] = 1;             /* push ds           */
        $nop[36] = 1;             /* and [byte],%al    */
        $nop[37] = 1;             /* and [dword],%eax  */
        $nop[39] = 1;             /* daa        '''    */
        $nop[44] = 1;             /* sub [byte],%al    */
        $nop[45] = 1;             /* sub [dword],%eax  */
        $nop[47] = 1;             /* das        '/'    */
        $nop[52] = 1;             /* xor [byte],%al    */
        $nop[53] = 1;             /* xor [dword],%eax  */
        $nop[55] = 1;             /* aaa        '7'    */
        $nop[60] = 1;             /* cmp [byte],%al    */
        $nop[61] = 1;             /* cmp [dword],%eax  */
        $nop[63] = 1;             /* aas        '?'    */
        $nop[64] = 1;             /* inc %eax   '@'    */
        $nop[65] = 1;             /* inc %ecx   'A'    */
        $nop[66] = 1;             /* inc %edx   'B'    */
        $nop[67] = 1;             /* inc %ebx   'C'    */
        $nop[68] = 1;             /* inc %esp   'D'    */
        $nop[69] = 1;             /* inc %ebp   'E'    */
        $nop[70] = 1;             /* inc %esi   'F'    */
        $nop[71] = 1;             /* inc %edi   'G'    */
        $nop[72] = 1;             /* dec %eax   'H'    */
        $nop[73] = 1;             /* dec %ecx   'I'    */
        $nop[74] = 1;             /* dec %edx   'J'    */
        $nop[75] = 1;             /* dec %ebx   'K'    */
        $nop[76] = 1;             /* dec %esp   'L'    */
        $nop[77] = 1;             /* dec %ebp   'M'    */
        $nop[78] = 1;             /* dec %esi   'N'    */
        $nop[79] = 1;             /* dec %edi   'O'    */
        $nop[80] = 1;             /* push %eax  'P'    */
        $nop[81] = 1;             /* push %ecx  'Q'    */
        $nop[82] = 1;             /* push %edx  'R'    */
        $nop[83] = 1;             /* push %ebx  'S'    */
        $nop[84] = 1;             /* push %esp  'T'    */
        $nop[85] = 1;             /* push %ebp  'U'    */
        $nop[86] = 1;             /* push %esi  'V'    */
        $nop[87] = 1;             /* push %edi  'W'    */
        $nop[88] = 1;             /* pop %eax   'X'    */
        $nop[89] = 1;             /* pop %ecx   'Y'    */
        $nop[90] = 1;             /* pop %edx   'Z'    */
        $nop[91] = 1;             /* pop %ebx   '['    */
        $nop[93] = 1;             /* pop %ebp   ']'    */
        $nop[94] = 1;             /* pop %esi   '^'    */
        $nop[95] = 1;             /* pop %edi   '_'    */
        $nop[96] = 1;             /* pusha      '`'    */
        $nop[104] = 1;             /* push [dword]      */
        $nop[106] = 1;             /* push [byte]       */
        $nop[112] = 1;             /* jo     [byte]     */
        $nop[113] = 1;             /* jno    [byte]     */
        $nop[114] = 1;             /* jc     [byte]     */
        $nop[115] = 1;             /* jnc    [byte]     */
        $nop[116] = 1;             /* jz     [byte]     */
        $nop[117] = 1;             /* jnz    [byte]     */
        $nop[118] = 1;             /* jna    [byte]     */
        $nop[119] = 1;             /* ja     [byte]     */
        $nop[120] = 1;             /* js     [byte]     */
        $nop[121] = 1;             /* jns    [byte]     */
        $nop[122] = 1;             /* jpe    [byte]     */
        $nop[123] = 1;             /* jpo    [byte]     */
        $nop[124] = 1;             /* jl     [byte]     */
        $nop[125] = 1;             /* jnl    [byte]     */
        $nop[126] = 1;             /* jng    [byte]     */
        $nop[127] = 1;             /* jg     [byte]     */
        $nop[145] = 1;             /* xchg %eax,%ecx    */
        $nop[146] = 1;             /* xchg %eax,%edx    */
        $nop[147] = 1;             /* xchg %eax,%ebx    */
        $nop[149] = 1;             /* xchg %eax,%ebp    */
        $nop[150] = 1;             /* xchg %eax,%esi    */
        $nop[151] = 1;             /* xchg %eax,%edi    */
        $nop[152] = 1;             /* cwtl              */
        $nop[153] = 1;             /* cltd              */
        $nop[155] = 1;             /* fwait             */
        $nop[156] = 1;             /* pushf             */
        $nop[158] = 1;             /* sahf              */
        $nop[159] = 1;             /* lahf              */
        $nop[168] = 1;             /* test [byte],%al   */
        $nop[169] = 1;             /* test [dword],%eax */
        $nop[176] = 1;             /* mov [byte],%al    */
        $nop[177] = 1;             /* mov [byte],%cl    */
        $nop[178] = 1;             /* mov [byte],%dl    */
        $nop[179] = 1;             /* mov [byte],%bl    */
        $nop[180] = 1;             /* mov [byte],%ah    */
        $nop[181] = 1;             /* mov [byte],%ch    */
        $nop[182] = 1;             /* mov [byte],%dh    */
        $nop[183] = 1;             /* mov [byte],%bh    */
        $nop[184] = 1;             /* mov [dword],%eax  */
        $nop[185] = 1;             /* mov [dword],%ecx  */
        $nop[186] = 1;             /* mov [dword],%edx  */
        $nop[187] = 1;             /* mov [dword],%ebx  */
        $nop[189] = 1;             /* mov [dword],%ebp  */
        $nop[190] = 1;             /* mov [dword],%esi  */
        $nop[191] = 1;             /* mov [dword],%edi  */
        $nop[212] = 1;             /* aam [byte]        */
        $nop[213] = 1;             /* aad [byte]        */
        $nop[214] = 1;             /* salc              */
        $nop[224] = 1;             /* loopne [byte]     */
        $nop[225] = 1;             /* loope  [byte]     */
        $nop[226] = 1;             /* loop   [byte]     */
        $nop[227] = 1;             /* jecxz  [byte]     */
        $nop[235] = 1;             /* jmp    [byte]     */
        $nop[245] = 1;             /* cmc               */
        $nop[248] = 1;             /* clc               */
        $nop[249] = 1;             /* stc               */
        $nop[252] = 1;             /* cld               */
        $nop[253] = 1;             /* std               */

        $count = 0;
        $started = 0;

        for ($i = 0; $i < strlen($inString); $i++) {
            $nextByte = ord($inString[$i]);
            if(isset($nop[$nextByte]) && $nop[$nextByte] == 1) {
                ++$count;
                if ($started == 0) {
                    $started = $i;
                }
            }
            else {
                if ($count > 40) {
                    $lastgood = $i - 1;
                    $formatString = markString ($formatString, 2, $started, $lastgood);
                }
                $started = 0;
                $count = 0;
            }
        }

        return $formatString;
    }
}
