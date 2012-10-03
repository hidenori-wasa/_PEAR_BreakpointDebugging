<?php

/**
 * Class which is for breakpoint debugging.
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
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
use \BreakpointDebugging as B;

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

        B::internalAssert(func_num_args() <= 3);
        B::internalAssert(is_string($message));
        B::internalAssert(is_int($code) || $code === null);
        B::internalAssert($previous instanceof \Exception || $previous === null);
        B::internalAssert(mb_detect_encoding($message, 'utf8', true) !== false);

        if ($previous === null) {
            parent::__construct($message, $code);
        } else {
            parent::__construct($message, $previous, $code);
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
 * This class executes error or exception handling.
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
     * @const int Tests by "phpunit". This flag is used with other flag.
     */
    const UNIT_TEST = 16;

    /**
     * @var stirng My username.
     */
    static $_userName = '';

    /**
     * @var string This prepend to logging when self::exceptionHandler() is called.
     */
    static $prependExceptionLog = '';

    /**
     * @var string This prepend to logging when self::errorHandler() is called.
     */
    static $prependErrorLog = '';

    /**
     * @var int Maximum log file byte size.
     */
    static $maxLogFileByteSize;

    /**
     * @var int Max log parameter nesting level.
     */
    static $maxLogParamNestingLevel = 20;

    /**
     * @var int Maximum count of elements in log. ( Total of parameters or array elements )
     */
    static $maxLogElementNumber = 50;

    /**
     * @var int Maximum string type byte-count of log.
     */
    static $maxLogStringSize = 3000;

    /**
     * @var string Work directory of this package.
     */
    static $workDir;

    /**
     * @var array Locations to be not Fixed.
     */
    public $notFixedLocations = array ();

    /**
     * @var array Values to trace.
     */
    public $valuesToTrace = array ();

    /**
     * @var bool Once error display flag.
     */
    private static $_onceErrorDispFlag = false;

    /**
     * @var bool Is it internal method?
     */
    static $isInternal = false;

    /**
     * @var string Which handler of "none" or "error" or "exception"?
     */
    private static $_handlerOf = 'none';

    /**
     * This registers as function or method being not fixed.
     *
     * @param bool &$isRegister Is this registered?
     *
     * @return void
     *
     * @example static $isRegister; \BreakpointDebugging::registerNotFixedLocation( $isRegister);
     */
    final static function registerNotFixedLocation(&$isRegister)
    {
        // When it has been registered.
        if ($isRegister) {
            return;
        }
        $isRegister = true;

        global $_BreakpointDebugging;

        $backTrace = debug_backtrace();
        // In case of scope of method or function or included file.
        if (array_key_exists(1, $backTrace)) {
            $backTrace2 = &$backTrace[1];
        } else { // In case of scope of start page file.
            $backTrace2['file'] = &$backTrace[0]['file'];
        }
        $_BreakpointDebugging->notFixedLocations[] = $backTrace2;
    }

    /**
     * Add values to trace.
     *
     * @param array $values Values.
     *
     * @return void
     *
     * @example \BreakpointDebugging::addValuesToTrace(array('TEST_CONST' => TEST_CONST, '$testString' => $testString, '$varietyObject' => $varietyObject));
     */
    final static function addValuesToTrace($values)
    {
        global $_BreakpointDebugging;

        $backTrace = debug_backtrace();
        $callInfo = &$backTrace[0];
        if (array_key_exists('file', $callInfo)) {
            // The file name to call.
            $file = &$callInfo['file'];
        } else {
            return;
        }
        if (array_key_exists('line', $callInfo)) {
            // The line number to call.
            $line = &$callInfo['line'];
        } else {
            return;
        }
        // In case of scope of method or function or included file.
        if (array_key_exists(1, $backTrace)) {
            $backTrace2 = &$backTrace[1];
        } else { // In case of scope of start page file.
            $backTrace2['file'] = &$backTrace[0]['file'];
        }
        $_BreakpointDebugging->valuesToTrace[$file][$line] = $backTrace2;
        $_BreakpointDebugging->valuesToTrace[$file][$line]['values'] = $values;
    }

    /**
     * This outputs function call stack log.
     *
     * @param string $errorKind    Error kind.
     * @param string $errorMessage Error message.
     *
     * @return void
     *
     * @example \BreakpointDebugging::outputErrorCallStackLog('EXCEPTION', 'Description of exception.');
     */
    final static function outputErrorCallStackLog($errorKind, $errorMessage)
    {
        $error = new BreakpointDebugging_Error();
        $error->callStackInfo = debug_backtrace();
        unset($error->callStackInfo[0]);
        // Add scope of start page file.
        $error->callStackInfo[] = array ();
        $error->outputErrorCallStackLog2($errorKind, $errorMessage);
        B::breakpoint($errorMessage, $error->callStackInfo);
    }

    /**
     * This method changes it to unify multibyte character strings such as system-output or user input, and this returns UTF-8 multibyte character strings.
     * This is security for not mixing a character sets.
     *
     * @param string $string Character string which may be not UTF8.
     *
     * @return string UTF8 character string.
     * @example \BreakpointDebugging::convertMbString($warning['Message']);
     */
    final static function convertMbString($string)
    {
        assert(func_num_args() === 1);
        assert(is_string($string));
        // Analyzes character sets of character string.
        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8' || $charSet === 'ASCII') {
            return $string;
        } else if ($charSet === false) {
            self::$_onceErrorDispFlag = true;
            throw new BreakpointDebugging_Error_Exception('This is not single character sets.');
        }
        return mb_convert_encoding($string, 'UTF-8', $charSet);
    }

    /**
     * If "Apache" is root user, this method changes the file or directory user to my user. And sets permission.
     *
     * @param string $name       The file or directory name.
     * @param int    $permission The file or directory permission.
     *
     * @return void
     */
    private static function _setOwner($name, $permission)
    {
        if (strncmp(PHP_OS, 'WIN', 3) === 0) {
            return;
        } else if (PHP_OS === 'Linux') {
            chmod($name, $permission);
            if (trim(`echo \$USER`) === 'root') {
                $user = self::$_userName;
                `chown \$user.\$user \$name`;
            }
        } else {
            self::internalAssert(false);
        }
    }

    /**
     * "mkdir" method which sets permission and sets own user to owner.
     *
     * @param stirng $dirName    Directory name.
     * @param int    $permission Directory permission.
     *
     * @return void
     */
    static function mkdir($dirName, $permission = 0777)
    {
        if (mkdir($dirName)) {
            self::_setOwner($dirName, $permission);
        }
    }

    /**
     * "fopen" method which sets the file mode, permission and sets own user to owner.
     *
     * @param stirng $fileName   The file name.
     * @param int    $mode       The file mode.
     * @param int    $permission The file permission.
     *
     * @return resource The file pointer.
     */
    static function fopen($fileName, $mode, $permission)
    {
        $pFile = fopen($fileName, $mode);
        if ($pFile) {
            self::_setOwner($fileName, $permission);
        }
        return $pFile;
    }

    /**
     * Compresses integer array.
     *
     * @param array $intArray Integer array.
     *
     * @return string Compression character string.
     * @example // Adds "\r\n" For data reading by "fgets()" in Windows and Unix and Mac.
     *          fwrite($pFile, \BreakpointDebugging::compressIntArray(array(0xFFFFFFFF, 0x7C, 0x7D, 0x7E, 0x0A, 0x0D)) . "\r\n");
     */
    static function compressIntArray($intArray)
    {
        $compressBytes = '';
        foreach ($intArray as $int) {
            self::internalAssert(preg_match('`^[0-9]$ | ^[1-9][0-9]+$`xX', $int) === 1);
            for ($diff = 1, $delimiter = 0x80, $tmpBytes = ''; $diff; $int = $diff / 0x7D) {
                // This changes from decimal number to 126 number.
                $diff = 0x7D * (int) ($int / 0x7D);
                $byte = $int - $diff;
                // Changes end of line character.
                if ($byte === 0x0A) { // For data reading by "fgets()" in Windows and Unix.
                    $tmpBytes .= chr(0x7E | $delimiter);
                } else if ($byte === 0x0D) { // For data reading by "fgets()" in Mac.
                    $tmpBytes .= chr(0x7F | $delimiter);
                } else {
                    $tmpBytes .= chr($byte | $delimiter);
                }
                $delimiter = 0;
            }
            $compressBytes .= strrev($tmpBytes);
        }
        return $compressBytes;
    }

    /**
     * Decompresses to integer array.
     *
     * @param string $compressBytes Compression character string by "\BreakpointDebugging::compressIntArray()".
     *
     * @return array Integer array.
     * @example while ($intArray = \BreakpointDebugging::decompressIntArray(fgets($pFile))) {
     */
    static function decompressIntArray($compressBytes)
    {
        // Trims "\r\n" For Windows and Unix and Mac.
        $compressBytes = trim($compressBytes, "\r\n");
        $intArray = array ();
        $int = 0;
        $strlen = strlen($compressBytes);
        for ($count = 0; $count < $strlen; $count++) {
            // Gets compression byte.
            $compressByte = ord($compressBytes[$count]);
            // Trims delimiter-bit.
            $tmpByte = $compressByte & 0x7F;
            // Changes to end of line character.
            if ($tmpByte === 0x7E) {
                $tmpByte = 0x0A;
            } else if ($tmpByte === 0x7F) {
                $tmpByte = 0x0D;
            }
            // This changes from 126 number to decimal number.
            $int = $int * 0x7D + $tmpByte;
            // If this is delimiter bit.
            if ($compressByte & 0x80) {
                $intArray[] = $int;
                $int = 0;
            }
        }
        return $intArray;
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
        $className = str_replace(array ('_', '\\'), '/', $className) . '.php';
        include_once $className;
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
        if (method_exists('BreakpointDebugging', 'makeUnitTestException')) {
            B::makeUnitTestException();
        }
        self::$_handlerOf = 'exception'; // This registers as exception handler.
        $error = new BreakpointDebugging_Error();
        $error->exceptionHandler2($pException, self::$prependExceptionLog);
        self::$_handlerOf = 'none'; // This registers as none handler.
    }

    /**
     * Error handler.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     *
     * @return bool Without system log (true).
     */
    final static function errorHandler($errorNumber, $errorMessage)
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (method_exists('BreakpointDebugging', 'makeUnitTestException')) {
            B::makeUnitTestException();
        }
        $handlerStore = self::$_handlerOf; // Stores the handler.
        self::$_handlerOf = 'error'; // This registers as error handler.
        $error = new BreakpointDebugging_Error();
        $error->errorHandler2($errorNumber, $errorMessage, self::$prependErrorLog);
        self::$_handlerOf = $handlerStore; // Restores handler.
        return true;
    }

    /**
     * Method which calls handler inside handler.
     *
     * @param string $message     Exception message.
     * @param string $handlerKind Handler kind.
     *
     * @return void
     * @example \BreakpointDebugging::internalException($message);
     */
    final static function _internal($message, $handlerKind)
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (self::$_onceErrorDispFlag) {
            return;
        }
        // Is internal method.
        self::$isInternal = true;
        switch (self::$_handlerOf) {
            case 'exception': // Is inside exception handler.
            case 'error': // Is inside error handler.
                self::$_onceErrorDispFlag = true;
            case 'none': // Is outer of handler.
                switch ($handlerKind) {
                    case 'exception':
                        // Calls exception handler because exception handler cannot throw exception.
                        self::exceptionHandler(new \BreakpointDebugging_Error_Exception($message));
                        break;
                    case 'error':
                        // Calls error handler because error handler cannot trigger error.
                        self::errorHandler(E_USER_ERROR, $message);
                        break;
                    default:
                        B::breakpoint('"$handlerKind" is wrong value.', debug_backtrace());
                }
                break;
            default:
                B::breakpoint('"\BreakpointDebugging::$_handlerOf" is wrong value.', debug_backtrace());
        }
        if ($_BreakpointDebugging_EXE_MODE & self::REMOTE_DEBUG) { // In case of remote debug.
            // Remote debug must end immediately to avoid eternal execution.
            exit;
        }
    }

    /**
     * This is avoiding recursive method call inside error handling or exception handling.
     * And this is possible assertion inside error handling.
     *
     * @param bool   $expression Judgment expression.
     *
     * @return void
     * @example \BreakpointDebugging::internalAssert($expression);
     */
    final static function internalAssert($expression)
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (!($_BreakpointDebugging_EXE_MODE & self::RELEASE)) { // In case of not release.
            if (func_num_args() !== 1 || !is_bool($expression) || $expression === false) {
                self::_internal('Assertion failed.', 'error');
            }
        }
    }

    /**
     * Method which throw exception inside exception handler. (For this package developer)
     *
     * @param string $message Exception message.
     *
     * @return void
     * @example \BreakpointDebugging::internalException($message);
     */
    final static function internalException($message)
    {
        global $_BreakpointDebugging_EXE_MODE;

        self::_internal($message, 'exception');
        if ($_BreakpointDebugging_EXE_MODE & self::RELEASE) { // In case of release.
            exit;
        }
    }

    /**
     * We must call "__destructor()" of other object for debug by keeping "$_BreakpointDebugging".
     *
     * @return void
     */
    static function shutdown()
    {
        global $_BreakpointDebugging;

        self::$_handlerOf = 'none'; // This registers as none handler.
        foreach ($GLOBALS as &$variable) {
            if (is_object($variable)) {
                // Excludes this object.
                if ($variable === $_BreakpointDebugging) {
                    continue;
                }
                if (is_callable(array ($variable, '__destruct'))) {
                    // Destructs instance.
                    $variable = null;
                }
            }
        }
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
         * This is empty in case of release mode.
         *
         * @param string $message       Dummy.
         * @param array  $callStackInfo Dummy.
         *
         * @return void
         */
        static function breakpoint($message, $callStackInfo)
        {

        }

        /**
         * This is ini_set() without validation in case of release mode.
         * I set with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
         *
         * @param string $phpIniVariable This is php.ini variable.
         * @param string $setValue       Value of variable.
         * @param bool   $doCheck        It is dummy.
         *
         * @return void
         */
        static function iniSet($phpIniVariable, $setValue, $doCheck = true)
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
        throw new \BreakpointDebugging_Error_Exception('');
    }
} else { // In case of not release.
    include_once __DIR__ . '/BreakpointDebugging_Option.php';
}

// This sets global exception handler.
set_exception_handler('\BreakpointDebugging::exceptionHandler');
// This sets error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds and so on.)
set_error_handler('\BreakpointDebugging::errorHandler', -1);
$_BreakpointDebugging = new \BreakpointDebugging();
spl_autoload_register('\BreakpointDebugging::autoload', true, true);
register_shutdown_function('\BreakpointDebugging::shutdown');

?>
