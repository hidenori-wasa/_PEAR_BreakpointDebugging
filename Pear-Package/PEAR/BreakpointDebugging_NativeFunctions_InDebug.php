<?php

/**
 * Class for native-functions debugging in case of debug mode.
 *
 * LICENSE:
 * Copyright (c) 2014-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/BREAKPOINTDEBUGGING_LICENSE.txt".
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
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
 * "*_InDebug.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
 *
 * PHP version 5.3.2-5.4.x
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
final class NativeFunctions
{
    /**
     * Error window name.
     *
     * @const string
     */
    const ERROR_WINDOW_NAME = 'BreakpointDebugging_NativeFunctions_Error';

    private static $_resources = array ();
    private static $_info = array ();

    /**
     * Same as native function.
     *
     * @param string $nativeFunctionName Native function name.
     * @param array  $args               Arguments.
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
