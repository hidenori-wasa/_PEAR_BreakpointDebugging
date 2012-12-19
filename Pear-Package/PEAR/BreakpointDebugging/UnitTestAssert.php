<?php

/**
 * Class which discerns unit test assertion failure.
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2012, Hidenori Wasa
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
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

/**
 * Class which discerns unit test assertion failure.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class BreakpointDebugging_UnitTestAssert
{
    private static $_isEnabled = false;

    /**
     * @var string Class name which causes assertion failure of unit test.
     */
    private static $_assertClassName = '';

    /**
     * @var string Class method name which causes assertion failure of unit test.
     */
    private static $_assertMethodName = '';

    /**
     * Registers class method name which causes assertion failure of unit test.
     * Then, discerns between "assertion failure" or "unit test of assertion failure".
     * Extends feature of "PHPUnit" package if this class method was called. (Not the default.)
     *
     * @param string $className  Class name.
     * @param string $methodName Class method name.
     *
     * @return void
     *
     * @See the file level document of "UnitTest.php".
     */
    static function registerAssertionFailureLocationOfUnitTest($className, $methodName)
    {
        self::$_isEnabled = true;
        self::$_assertClassName = $className;
        self::$_assertMethodName = $methodName;
    }

    /**
     * Is it unit test of assertion failure?
     *
     * @return bool Unit test of assertion failure returns true.
     */
    static function isUnitTestOfAssertionFailure()
    {
        // Did you use feature except "PHPUnit" package?
        if (!self::$_isEnabled) {
            return true;
        }

        $cmpAssertClassMethodName = function ($prevCall, $className, $methodName) {
            if (isset($prevCall)) {
                // Searches the location which calls "Registered class method which causes assertion failure of unit test" in call stack.
                if (array_key_exists('class', $prevCall) && $prevCall['class'] === $className
                && array_key_exists('function', $prevCall) && $prevCall['function'] === $methodName) {
                    // Unregisters class method name which causes assertion failure of unit test.
                    \BreakpointDebugging_UnitTestAssert::registerAssertionFailureLocationOfUnitTest('', '');
                    return true;
                }
                // Assertion error.
                return false;
            }
            // Is unit test which the direct global error callback class method is called.
            return true;
        };

        $debugBacktrace = array_reverse(debug_backtrace());
        foreach ($debugBacktrace as $key => $call) {
            // Searches the location which calls "assert() function" in call stack.
            if (array_key_exists('function', $call) && $call['function'] === 'assert') {
                return $cmpAssertClassMethodName($prevCall, self::$_assertClassName, self::$_assertMethodName);
            }
            // Searches the location which calls "BreakpointDebugging_InAllCase::internalAssert() class method" in call stack.
            if (array_key_exists('class', $call) && $call['class'] === 'BreakpointDebugging_InAllCase'
            && array_key_exists('function', $call) && $call['function'] === 'internalAssert') {
                return $cmpAssertClassMethodName($prevCall, self::$_assertClassName, self::$_assertMethodName);
            }
            $prevCall = $call;
        }
        // Not assertion.
        return true;
    }
}

?>
