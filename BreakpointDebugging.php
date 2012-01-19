<?php

/**
 * This makes it possible to do breakpoint debugging.
 * 
 * ### Environment which can do breakpoint debugging. ###
 * For example, in case of windows environment, there is "VS.Php" debugger.
 *
 * ### The advantage of breakpoint debugging. ###
 * it is to be able to find a position of a bug immediately.
 * In addition to it, condition of variable can be examined.
 * Therefore, it is possible to do debugging quickly.
 *
 * ### How to do breakpoint debugging coding. ###
 * We have to do coding as follows to process in "BreakpointDebugging" class.
 * We have to verify a impossible return value of function or method with "assert()".
 * We have to verify a impossible value in code.
 * Also, an error and an exception which wasn't caught are processed in "BreakpointDebugging" class.
 *
 * ### The execution procedure. ###
 * Procedure 1: Please, set php file format to utf8, but we should create backup of php files because multibyte strings may be destroyed.
 * Procedure 2: Please, copy BreakpointDebugging_MySetting.php as your project php file.
 * Procedure 3: Please, edit BreakpointDebugging_MySetting.php for customize.
 *      Then, it is possible to make specific setting about all debugging modes.
 * Procedure 4: Please, set a breakpoint into BreakpointDebugging_breakpoint() of BreakpointDebugging_MySetting.php.
 * Procedure 5: Please, set debugging mode to $_BreakpointDebugging_EXE_MODE.
 * Procedure 6: Please, register at top of the function or method to have been not fixed. Please, copy following.
 * "static $isRegister; BreakpointDebugging::registerNotFixedLocation( $isRegister);"
 * Then, it is possible to discern function or method which does not fix with browser screen or log.
 *
 * ### The debugging mode which we can use. ###
 * First "LOCAL_DEBUG" mode is breakpoint debugging with local personal computer.
 *     For example, VS.Php environment.
 * Second "LOCAL_DEBUG_OF_RELEASE" mode is breakpoint debugging to emulate release with local personal computer.
 *     For example, XAMPP environment.
 * Third "REMOTE_DEBUG" mode is browser display debugging with remote personal computer.
 *     For example, we debug client server environment by browser.
 * Last "RELEASE" mode is log debugging with remote personal computer, and we must set on last for security.
 *     For example, on release.
 *
 * ### Useful function index. ###
 * Please, register at top of the function or method being not fixed.
 *     final static function BreakpointDebugging::registerNotFixedLocation(&$isRegister)
 * Add values to trace
 *     final static function BreakpointDebugging::addValuesToTrace($values)
 * This writes inside of "catch()", then display logging or log.
 *     BreakpointDebugging::$prependExceptionLog
 *     final static function BreakpointDebugging::exceptionHandler($exception)
 * This return the function call stack log.
 *     final static function BreakpointDebugging::buildErrorCallStackLog($errorKind, $errorMessage)
 * This changes to unify multibyte character strings such as system-output in UTF8, and this returns.
 *     final static function BreakpointDebugging::convertMbString($string)
 * This changes a character sets to display a multibyte character string with local window of debugger, and this returns it.
 * But, this doesn't exist in case of release.
 *     static function BreakpointDebugging::convertMbStringForDebug($params)
 * 
 * PHP version 5.3
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
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */

require_once __DIR__ . '/PEAR/Exception.php';

/**
 * This class is own package exception.
 * 
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_Exception extends PEAR_Exception
{
    /**
     * This is "PEAR_Exception" breakpoint in case of local.
     * 
     * @param string $message  Exception message.
     * @param int    $code     Exception code.
     * @param string $previous Previous exception.
     * 
     * @return void
     */
    function __construct($message, $code = null, $previous = null)
    {
        global $_BreakpointDebugging_EXE_MODE;
        assert(func_num_args() <= 3);
        assert(is_string($message));
        assert(is_int($code));
        assert($previous instanceof Exception || $previous === null);
        assert(mb_detect_encoding($message, 'utf8', true) != false);
        
        parent::__construct($message, $previous, $code);
        // In case of local-debug. "BreakpointDebugging_breakpoint()" is called. Therefore we do the step execution to error place, and we can see condition of variables.
        if ($_BreakpointDebugging_EXE_MODE & (BreakpointDebugging::LOCAL_DEBUG | BreakpointDebugging::LOCAL_DEBUG_OF_RELEASE)) { // In case of local.
            BreakpointDebugging_breakpoint();
        }
    }
}

/**
 * This class is own package error exception.
 * 
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_Error_Exception extends BreakpointDebugging_Exception
{
}

/**
 * @const int $_BreakpointDebugging_EXE_MODE Debug mode constant.
 */
global $_BreakpointDebugging_EXE_MODE;

/**
 * This class executes error or exception handling
 * 
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_InAllCase
{
    // ### Debug mode constant number ###
    
    /**
     * @const int First mode is breakpoint debug with your personal computer.
     */
    const LOCAL_DEBUG = 1;
    
    /**
     * @const int Next mode is breakpoint debug to emulate release mode with your personal computer.
     */
    const LOCAL_DEBUG_OF_RELEASE = 2;
    
    /**
     * @const int Next mode is browser display debug with remote personal computer.
     */
    const REMOTE_DEBUG = 4;
    
    /**
     * @const int Next mode is log debug with remote personal computer. That is, this is a release mode.
     */
    const RELEASE = 8;
    
    /**
     * @var string This prepend to logging when self::exceptionHandler() is called.
     */
    public static $prependExceptionLog = '';
    
    /**
     * @var string This prepend to logging when self::errorHandler() is called.
     */
    public static $prependErrorLog = '';
    
    /**
     * @var int Max log parameter nesting level.
     */
    public static $maxLogParamNestingLevel = 20;
    
    /**
     * @var array Locations to be not Fixed
     */
    public $notFixedLocations;
    
    /**
     * @var array Values to trace
     */
    public $valuesToTrace = array();
    
    /**
     * Please, register at top of the function or method being not fixed.
     * 
     * @param bool &$isRegister Is this registered?
     * 
     * @return void
     * 
     * @example static $isRegister; BreakpointDebugging::registerNotFixedLocation( $isRegister);
     */
    final static function registerNotFixedLocation(&$isRegister)
    {
        // When it has been registered.
        if ($isRegister) {
            return;
        }
        $isRegister = true;
        
        global $_BreakpointDebugging;
        
        $backTrace = debug_backtrace(true);
        // In case of scope of method or function or included file.
        if (array_key_exists(1, $backTrace)) {
            $backTrace2 = &$backTrace[1];
        } else { // In case of scope of top file.
            $backTrace2['file'] = &$backTrace[0]['file'];
        }
        $_BreakpointDebugging->notFixedLocations[] = $backTrace2;
    }
    
    /**
     * Add values to trace
     * 
     * @param array $values Values
     * 
     * @return void
     * 
     * @example BreakpointDebugging::addValuesToTrace(array('TEST_CONST' => TEST_CONST, '$testString' => $testString, '$varietyObject' => $varietyObject));
     */
    final static function addValuesToTrace($values)
    {
        global $_BreakpointDebugging;
        
        $backTrace = debug_backtrace(true);
        $callInfo = &$backTrace[0];
        if (array_key_exists('file', $callInfo)) {
            // The file name to call
            $file = &$callInfo['file'];
        } else {
            return;
        }
        if (array_key_exists('line', $callInfo)) {
            // The line number to call
            $line = &$callInfo['line'];
        } else {
            return;
        }
        // In case of scope of method or function or included file.
        if (array_key_exists(1, $backTrace)) {
            $backTrace2 = &$backTrace[1];
        } else { // In case of scope of top file.
            $backTrace2['file'] = &$backTrace[0]['file'];
        }
        $_BreakpointDebugging->valuesToTrace[$line][$file] = $backTrace2;
        $_BreakpointDebugging->valuesToTrace[$line][$file]['values'] = $values;
    }
    
    /**
     * This writes inside of "catch()", then display logging or log.
     * 
     * @param object $pException Exception info.
     * 
     * @return void
     */
    final static function exceptionHandler($pException)
    {
        $error = new BreakpointDebugging_Error();
        $error->exceptionHandler2($pException, self::$prependExceptionLog);
    }
    
    /**
     * This return the function call stack log.
     * 
     * @param int    $errorKind    Error kind.
     * @param string $errorMessage Error message.
     * 
     * @return string Function call stack log.
     * 
     * @example $log = BreakpointDebugging::buildErrorCallStackLog('EXCEPTION', 'Description of exception.');
     */
    final static function buildErrorCallStackLog($errorKind, $errorMessage)
    {
        $error = new BreakpointDebugging_Error();
        $trace = debug_backtrace(true);
        unset($trace[0]);
        // Add scope of top file.
        array_push($trace,	array());
        return $error->buildErrorCallStackLog2($errorKind, $errorMessage, $trace);
    }
    
    /**
     * This method changes it to unify multibyte character strings such as system-output or user input, and this returns UTF-8 multibyte character strings.
     * In other words, this is not mixing a character sets, therefore this is not breaking character strings.
     * 
     * @param string $string Character string which may be not UTF8.
     * 
     * @return string UTF8 character string.
     */
    final static function convertMbString($string)
    {
        assert(func_num_args() == 1);
        assert(is_string($string));
        $string = mb_convert_encoding($string, 'utf8', 'auto');
        $return = mb_detect_encoding($string);
        assert($return == 'UTF-8' || $return == 'ASCII');
        return $string;
    }
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * This does autoload with word which was divided by name space separator and underscore separator as directory.
     * 
     * @param string $className This is class name to do "new" and "extends".
     * 
     * @return void
     */
    final static function autoload($className)
    {
        // This changes underscore and name space separator into directory separator.
        $className = str_replace(array('_', '\\'), '/', $className) . '.php';
        include_once $className;
    }
    
    /**
     * Error handler.
     * 
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     * @param string $errorFile    Error file name.
     * @param int    $errorLine    Error file line.
     * 
     * @return bool Did the error handling end?
     */
    final static function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine)
    {
        $error = new BreakpointDebugging_Error();
        return $error->errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine, self::$prependErrorLog);
    }
}

if ($_BreakpointDebugging_EXE_MODE & BreakpointDebugging_InAllCase::RELEASE) { // In case of release.
    /**
    * This class executes error or exception handling, and it is only in case of release mode.
     * 
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <wasa_@nifty.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    final class BreakpointDebugging extends BreakpointDebugging_InAllCase
    {
        /**
         * This is ini_set() without validation in case of release mode.
         * I set with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
         * 
         * @param string $phpIniVariable This is php.ini variable.
         * @param string $setValue       Value of variable.
         * 
         * @return void
         */
        static function iniSet( $phpIniVariable, $setValue)
        {
            ini_set($phpIniVariable, $setValue);
        }
        
        /**
        * This is ini_check() without validate in case of release mode.
         * 
         * @return void
         */
        static function iniCheck()
        {
        }
    }
    if (assert_options(ASSERT_ACTIVE, 0) === false) { // Ignore assert().
        throw new BreakpointDebugging_Error_Exception('');
    }
} else { // In case of not release.
    include_once __DIR__ . '/BreakpointDebugging_Option.php';
}

// This sets global exception handler.
set_exception_handler('BreakpointDebugging::exceptionHandler');
// This sets error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds and so on.)
set_error_handler('BreakpointDebugging::errorHandler', -1);
$_BreakpointDebugging = new BreakpointDebugging();
spl_autoload_register('BreakpointDebugging::autoload', true, true);

?>
