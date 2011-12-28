<?php

/**
 * This file is code except for release, therefore it does not read in case of release.
 * 
 * This reduces load of PHP parser in release mode, then it does speed up.
 * 
 * PHP version 5.3
 * 
 * LICENSE:
 * Copyright (c) 2011, Hidenori Wasa
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer
 * in the documentation and/or other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */

// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

/**
 * This class executes error or exception handling, and it is except release mode.
 * 
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging extends BreakpointDebugging_InCaseAll
{
    /**
     * This constructer create object only one time.
     * 
     * @return void
     */
    function __construct()
    {
        static $createOnlyOneTime = false;
        
        assert($createOnlyOneTime == false);
        $createOnlyOneTime = true;
    }
    
    /**
     * This changes a character sets to display a multibyte character string with local window of debugger, and this returns it.
     * But, this doesn't exist in case of release.
     * 
     * @param array $params Character set string to want to display, and Some variables.
     * 
     * @return array Some changed variables.
     * 
     * ### sample code
     * $gDebugValue = BreakpointDebugging::convertMbStringForDebug('SJIS', $scalar1, $array2, $scalar2);
     */
    static function convertMbStringForDebug($params)
    {
        global $_BreakpointDebugging_EXE_MODE;
        
        // In case of local.
        if ($_BreakpointDebugging_EXE_MODE & (self::LOCAL_DEBUG | self::LOCAL_DEBUG_OF_RELEASE)) {
            $mbStringArray = \func_get_args();
            $mbParamArray = array_slice($mbStringArray, 1);
            return self::_convertMbStringForDebugSubroop($mbStringArray[0], $mbParamArray);
        }
    }
    
    /**
     * This changes a multibyte character string array, and this returns it.
     * 
     * @param string $charSet      Character set.
     * @param array  $mbParamArray Parameter array.
     * 
     * @return array This does return multibyte character string for display.
     */
    private static function _convertMbStringForDebugSubroop($charSet, $mbParamArray)
    {
        global $_BreakpointDebugging_EXE_MODE;
        
        // In case of local.
        if ($_BreakpointDebugging_EXE_MODE & (self::LOCAL_DEBUG | self::LOCAL_DEBUG_OF_RELEASE)) {
            $count = 0;
            foreach ($mbParamArray as $mbString) {
                if (is_array($mbString)) {
                    $displayMbStringArray[$count] = self::_convertMbStringForDebugSubroop($charSet, $mbString);
                } else {
                    $displayMbStringArray[$count] = mb_convert_encoding($mbString, $charSet, 'auto');
                }
                $count++;
            }
            return $displayMbStringArray;
        }
    }
    
    
    /**
     * This validates to be same type.
     * 
     * @param mixed $cmp1 A variable to compare type.
     * @param mixed $cmp2 A variable to compare type.
     * 
     * @return bool Is this the same type?
     */
    private static function _isSameType($cmp1, $cmp2)
    {
        if (is_string($cmp1) === true && is_string($cmp2) === true) {
            return true;
        }
        if (is_int($cmp1) === true && is_int($cmp2) === true) {
            return true;
        }
        if (is_bool($cmp1) === true && is_bool($cmp2) === true) {
            return true;
        }
        if (is_array($cmp1) === true && is_array($cmp2) === true) {
            return true;
        }
        if (is_null($cmp1) === true && is_null($cmp2) === true) {
            return true;
        }
        if (is_float($cmp1) === true && is_float($cmp2) === true) {
            return true;
        }
        if (is_object($cmp1) === true && is_object($cmp2) === true) {
            return true;
        }
        if (is_resource($cmp1) === true && is_resource($cmp2) === true) {
            return true;
        }
        return false;
    }
    
    /**
     * This is ini_set() with validation except for release mode.
     * 
     * @param string $phpIniVariable This is php.ini variable.
     * @param string $setValue       Value of variable.
     * 
     * @return void
     */
    static function iniSet($phpIniVariable, $setValue)
    {
        global $_BreakpointDebugging_EXE_MODE;
        assert(func_num_args() == 2);
        
        $getValue = ini_get($phpIniVariable);
        assert(self::_isSameType($setValue, $getValue));
        if ($setValue === $getValue) {
            // In case of remote.
            if ($_BreakpointDebugging_EXE_MODE & self::REMOTE_DEBUG) {
                $backTrace = debug_backtrace(true);
                echo <<<EOD
<pre>
### BreakpointDebugging::iniSet(): You can do comment out because set value and value of php.ini are same.
### But, When remote php.ini is changed, make return completely from comment, then you must redo remote debug.
	file: {$backTrace[0]['file']}
	line: {$backTrace[0]['line']}

<pre/>
EOD;
            }
            return;
        }
        if (ini_set($phpIniVariable, $setValue) === false) {
            self::throwErrorException('');
        }
    }
    
    /**
     * This checks php.ini setting.
     * 
     * @param string $phpIniVariable The php.ini file setting variable.
     * @param mixed  $cmpValue       Value which should set in case of string.
     *                                Value which should avoid in case of array.
     * @param string $errorMessage   Error message.
     * 
     * @return void
     */
    static function iniCheck($phpIniVariable, $cmpValue, $errorMessage)
    {
        assert(func_num_args() == 3);
        $value = (string)ini_get($phpIniVariable);
        $cmpResult = false;
        if (is_array($cmpValue)) {
            foreach ($cmpValue as $eachCmpValue) {
                assert(self::_isSameType($value, $eachCmpValue));
                if ($value === $eachCmpValue) {
                    $cmpResult = true;
                    break;
                }
            }
        } else {
            assert(self::_isSameType($value, $cmpValue));
            if ($value !== $cmpValue) {
                $cmpResult = true;
            }
        }
        if ($cmpResult) {
            echo '<br/>' . $errorMessage . '<br/>' .
                'Current value is ' . $value . '<br/>';
        }
    }
}

// ### Assertion setting. ###
if (assert_options(ASSERT_ACTIVE, 1) === false) { // This makes the evaluation of assert() effective.
    B::throwErrorException('');
}
if (assert_options(ASSERT_WARNING, 1) === false) { // In case of failing in assertion, this generates a warning.
    B::throwErrorException('');
}
if (assert_options(ASSERT_BAIL, 0) === false) { // In case of failing in assertion, this doesn't end execution.
    B::throwErrorException('');
}
if (assert_options(ASSERT_QUIET_EVAL, 0) === false) { // As for assertion expression, this doesn't make error_reporting invalid.
    B::throwErrorException('');
}
// ### usage ###
//   assert(<judgment expression>);
//   It is possible to assert that <judgment expression> is "This should be". Especially, this uses to verify a function's argument.
//   For example: assert(3 <= $value && $value <= 5); // $value should be 3-5.
//   Caution: Don't change the value of variable in "assert()" function because there isn't executed in case of release.

?>
