<?php

/**
 * Class for native-functions debugging in case of debug mode.
 *
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
 *
 * PHP version 5.3.2-5.4.x
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2014, Hidenori Wasa
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
 * @link     http://pear.php.net/package/BreakpointDebugging
 */

namespace BreakpointDebugging;

// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;
use \BreakpointDebugging_Window as BW;

/**
 * Class for native-functions debugging in case of debug mode.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class NativeFunctions
{
    /**
     * @const string Error window name.
     */
    const ERROR_WINDOW_NAME = 'BreakpointDebugging_NativeFunctions_Error';

    private static $_resources = array ();
    private static $_info = array ();

    /**
     * Same as native function.
     *
     * @param string $nativeFunctionName Native function name.
     * @param array $args Arguments.
     *
     * @return Same as native function.
     */
    static function callNativeFunction($nativeFunctionName, $args)
    {
        $fopenResource = call_user_func_array('\\' . $nativeFunctionName, $args);
        B::assert($fopenResource !== false);

        self::$_resources[] = $fopenResource;
        $callStackInfo = debug_backtrace();
        $errorFile = '';
        $errorLine = '';
        if (!empty($callStackInfo)) {
            $call = $callStackInfo[1];
            if (array_key_exists('file', $call)) {
                $errorFile = $call['file'];
            }
            if (array_key_exists('line', $call)) {
                $errorLine = $call['line'];
            }
        }
        self::$_info[] = <<<EOD
<strong>"fopen()" function must be "close()".</strong>
FILE: $errorFile
LINE: $errorLine
EOD;
        return $fopenResource;
    }

    /**
     * Verifies at shutdown.
     *
     * @return void
     */
    static function shutdown()
    {
        $number = count(self::$_resources);
        $openedResourceExists = false;
        for ($count = 0; $count < $number; $count++) {
            if (is_resource(self::$_resources[$count])) {
                $openedResourceExists = true;
            }
        }
        if (!$openedResourceExists) {
            return;
        }
        BW::virtualOpen(self::ERROR_WINDOW_NAME, B::getErrorHtmlFileTemplate());
        for ($count = 0; $count < $number; $count++) {
            if (is_resource(self::$_resources[$count])) {
                BW::htmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, self::$_info[$count]);
            }
        }
    }

}

/**
 * Same as native function.
 *
 * @return Same as native function.
 */
function fopen()
{
    return NativeFunctions::callNativeFunction('fopen', func_get_args());
}

/**
 * Same as native function.
 *
 * @param resource $handle Same as native function.
 *
 * @return Same as native function.
 */
function fclose($handle)
{
    $result = \fclose($handle);
    B::assert($result === true);
    unset($handle);
    return $result;
}

/**
 * Same as native function.
 *
 * @return Same as native function.
 */
function popen()
{
    return NativeFunctions::callNativeFunction('popen', func_get_args());
}

register_shutdown_function('\BreakpointDebugging\NativeFunctions::shutdown');
