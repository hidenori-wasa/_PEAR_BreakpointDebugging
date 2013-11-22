<?php

/**
 * Class for breakpoint debugging.
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2012-2013, Hidenori Wasa
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
use \BreakpointDebugging as B;
use \BreakpointDebugging_InAllCase as BA;

require_once __DIR__ . '/PEAR/Exception.php';
/**
 * Own package exception.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
abstract class BreakpointDebugging_Exception_InAllCase extends \PEAR_Exception
{
    /**
     * Constructs instance.
     *
     * @param string $message  Exception message.
     * @param int    $id       Exception identification number.
     * @param object $previous Previous exception.
     *
     * @return void
     */
    function __construct($message, $id = null, $previous = null)
    {
        // trigger_error('Error test.'); // For debug.
        // throw new \Exception('Exception test.'); // For debug.

        if ($previous === null) {
            parent::__construct($message, $id);
        } else {
            parent::__construct($message, $previous, $id);
        }
    }

}

/**
 * This class executes error or exception handling.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
abstract class BreakpointDebugging_InAllCase
{
    // ### Debug mode constant number ###
    /**
     * @const int This flag means executing on practical use server. This flag means executing on local development server if it is not set.
     */
    const REMOTE = 1;

    /**
     * @const int This flag means executing release code. This flag means executing debug code if it is not set.
     */
    const RELEASE = 2;

    /**
     * @const int This flag means executing unit test. This flag means not executing unit test if it is not set.
     */
    const UNIT_TEST = 4;

    /**
     * @const int This flag means ignoring break point. This flag means enabling break point if it is not set.
     */
    const IGNORING_BREAK_POINT = 8;

    /**
     *  @const string Character string which means recursive array.
     */
    const RECURSIVE_ARRAY = '### Omits recursive array. ###';

    /**
     * @const string Character string which means using "$GLOBALS".
     */
    const GLOBALS_USING = '### Omits own recursive array inside "$GLOBALS". ###';

    /**
     * @const string Error window name.
     */
    const ERROR_WINDOW_NAME = 'BreakpointDebugging_Error';

    /**
     * @var mixed Temporary variable.
     */
    static $tmp;

    /**
     * @var array Static properties reference.
     */
    protected static $staticProperties;

    /**
     * @var array Static property limitings reference.
     */
    protected static $staticPropertyLimitings;

    /**
     * @var bool "Xdebug" existing-flag.
     */
    private static $_xdebugExists;

    /**
     * @var stirng Your username.
     */
    private static $_userName = '';

    /**
     * @var string Developer IP for remote error log file manager, remote code coverage report display and remote unit test.
     */
    private static $_developerIP = '127.0.0.1';

    /**
     * @var string This prepend to logging when self::handleException() is called.
     */
    static $prependExceptionLog = '';

    /**
     * @var string This prepend to logging when self::handleError() is called.
     */
    static $prependErrorLog = '';

    /**
     * @var int Maximum log file byte size.
     */
    private static $_maxLogFileByteSize;

    /**
     * @var int Max log parameter nesting level.
     */
    private static $_maxLogParamNestingLevel;

    /**
     * @var int Maximum count of elements in log. ( Total of parameters or array elements )
     */
    private static $_maxLogElementNumber;

    /**
     * @var int Maximum string type byte-count of log.
     */
    private static $_maxLogStringSize;

    /**
     * @var string Work directory of this package.
     */
    private static $_workDir;

    /**
     * @var array Locations to be not Fixed.
     */
    private static $_notFixedLocations = array ();

    /**
     * @var array Values to trace.
     */
    private static $_valuesToTrace;

    /**
     * @var bool Once error display flag.
     */
    private static $_onceErrorDispFlag = false;

    /**
     * @var array Once JavaScript flag.
     */
    private static $_onceJavaScript = array ();

    /**
     * @var bool Calling exception handler directly?
     */
    private static $_callingExceptionHandlerDirectly = false;

    /**
     * @var string The project work directory.
     */
    private static $_pwd;

    /**
     * @var int Execution mode.
     */
    protected static $exeMode;

    /**
     * @var int Native execution mode.
     */
    private static $_nativeExeMode;

    /**
     * @var string HTML file content of error window.
     */
    protected static $errorHtmlFileContent;

    ///////////////////////////// For package user from here. /////////////////////////////
    /**
     * Debugs by using breakpoint.
     * We must define this class method outside namespace, and we must not change method name when we call this method.
     *
     * @param string $message       Message.
     * @param array  $callStackInfo A call stack info.
     *
     * @return void
     * @example B::breakpoint('Error message.', debug_backtrace());
     * @codeCoverageIgnore
     * Because I do not want to stop at breakpoint.
     */
    static function breakpoint($message, $callStackInfo)
    {
        B::assert(func_num_args() === 2);
        B::assert(is_string($message));
        B::assert(is_array($callStackInfo));

        if (self::$exeMode === (B::REMOTE | B::RELEASE)
            || (self::$exeMode & B::IGNORING_BREAK_POINT)
        ) {
            return;
        }

        reset($callStackInfo);
        if (!empty($callStackInfo)) {
            $call = each($callStackInfo);
            $call = $call['value'];
            if (array_key_exists('file', $call)) {
                $errorFile = $call['file'];
            }
            if (array_key_exists('line', $call)) {
                $errorLine = $call['line'];
            }
        }

        flush();
        if (self::getXebugExists()) {
            xdebug_break(); // Breakpoint. See local variable value by doing step execution here.
            // Push stop button if is thought error message.
        }

        if (self::$_nativeExeMode === self::REMOTE) { // In case of remote debug.
            // Remote debug must end immediately to avoid eternal execution.
            exit;
        }
    }

    /**
     * Error exit. You can detect error exit location by call stack after break if you use this.
     *
     * @param mixed $error Error message or error exception instance.
     *
     * @return void
     * @codeCoverageIgnore
     * Because this class method exits.
     */
    static function exitForError($error = '')
    {
        self::$exeMode &= ~B::IGNORING_BREAK_POINT;
        // If this is not actual environment release.
        if (self::$_nativeExeMode !== (self::REMOTE | self::RELEASE)) {
            // Displays error call stack instead of log.
            self::$exeMode &= ~self::RELEASE;
            // Avoids exception throw.
            self::$_nativeExeMode &= ~self::UNIT_TEST;
            // Ends the output buffering.
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
        }
        if (is_string($error)) {
            self::handleError(E_USER_ERROR, $error);
        } else if ($error instanceof \Exception) {
            self::handleException($error);
        } else {
            throw new \BreakpointDebugging_ErrorException('You mistook type of first parameter.');
        }
        exit;
    }

    /**
     * Checks security before we run development page.
     *
     * @param mixed $necessaryExeMode Necessary execution mode. Does not check execution mode if this is false.
     *
     * @return bool Success or failure.
     */
    static function checkDevelopmentSecurity($necessaryExeMode = false)
    {
        B::assert(func_num_args() <= 1);
        B::assert($necessaryExeMode === false || is_int($necessaryExeMode));

        // Checks the execution mode.
        if ($necessaryExeMode !== false
            && !(self::$exeMode & $necessaryExeMode)
        ) {
            switch ($necessaryExeMode) {
                case self::RELEASE:
                    $message = '<b>You must set' . PHP_EOL
                        . '    "BREAKPOINTDEBUGGING_MODE=RELEASE"' . PHP_EOL
                        . 'to this project execution parameter.</b>';
                    break;
                default :
                    throw new \BreakpointDebugging_ErrorException('"' . __METHOD__ . '" parameter1 is mistake.', 101);
            }
            self::windowVirtualOpen(self::ERROR_WINDOW_NAME, self::$errorHtmlFileContent);
            self::windowHtmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, $message);
            return false;
        }
        if (!(self::$exeMode & self::REMOTE)) { // In case of local.
            return true;
        }
        // Checks client IP address.
        if ($_SERVER['REMOTE_ADDR'] !== self::$_developerIP) {
            self::windowVirtualOpen(self::ERROR_WINDOW_NAME, self::$errorHtmlFileContent);
            self::windowHtmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, '<b>You must set "$developerIP = \'' . $_SERVER['REMOTE_ADDR'] . '\';" ' . PHP_EOL
                . "\t" . 'into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php" file.' . PHP_EOL
                . 'Or, you mistook start "php" page.</b>'
            );
            return false;
        }
        // Checks the request protocol.
        if (!array_key_exists('HTTPS', $_SERVER)
            || empty($_SERVER['HTTPS'])
            || $_SERVER['HTTPS'] === 'off'
        ) {
            self::windowVirtualOpen(self::ERROR_WINDOW_NAME, self::$errorHtmlFileContent);
            self::windowHtmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, '<b>You must use "https" protocol.' . PHP_EOL
                . 'Or, you mistook start "php" page.</b>'
            );
            return false;
        }
        return true;
    }

    /**
     * Checks unit-test-execution-mode.
     *
     * @param bool $isUnitTest It is unit test?
     *
     * @return void
     *
     * @example
     *      <?php
     *
     *      require_once './BreakpointDebugging_Inclusion.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      B::checkExeMode(); // Checks the execution mode.
     *          .
     *          .
     *          .
     * @codeCoverageIgnore
     * Because this class method does not exist in case of unit test.
     */
    static function checkExeMode($isUnitTest = false)
    {
        if (self::$exeMode & self::UNIT_TEST) {
            \BreakpointDebugging_PHPUnitStepExecution::checkExeMode($isUnitTest);
            return;
        }

        if ($isUnitTest) {
            self::windowVirtualOpen(self::ERROR_WINDOW_NAME, self::$errorHtmlFileContent);
            $pearSettingDirName = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME;
            $errorMessage = <<<EOD
You must set
    "BREAKPOINTDEBUGGING_MODE=DEBUG_UNIT_TEST" or
    "BREAKPOINTDEBUGGING_MODE=RELEASE_UNIT_TEST"
to this project execution parameter.
Or, comment out "\$_BreakpointDebugging_EXE_MODE = 2;" of "{$pearSettingDirName}BreakpointDebugging_MySetting.php".
EOD;
            self::windowHtmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, '<b>' . $errorMessage . '</b>');
            exit;
        }
    }

    /**
     * Gets a static property value.
     *
     * @param string $propertyName Static property name.
     *
     * @return mixed Static property value.
     */
    static function getStatic($propertyName)
    {
        return self::$staticProperties[$propertyName];
    }

    /**
     * Gets a static property reference.
     *
     * @param string $propertyName Static property name.
     *
     * @return mixed& Static property.
     */
    static function &refStatic($propertyName)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        return self::$staticProperties[$propertyName];
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getXebugExists()
    {
        B::limitAccess(
            array ('BreakpointDebugging.php',
                'BreakpointDebugging_InDebug.php'
            )
        );

        return self::$_xdebugExists;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param bool $value Same as property.
     *
     * @return void
     */
    static function setXebugExists($value)
    {
        self::$_xdebugExists = $value;
    }

    /**
     * Checks "php.ini" file-setting.
     *
     * @param string $phpIniVariable "php.ini" file-setting variable.
     * @param mixed  $cmpValue       Value which should set in case of string.
     *                               Value which should avoid in case of array.
     * @param string $errorMessage   Error message.
     *
     * @return void
     */
    static function iniCheck($phpIniVariable, $cmpValue, $errorMessage)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        $value = (string) ini_get($phpIniVariable);
        $cmpResult = false;
        if (is_array($cmpValue)) {
            foreach ($cmpValue as $eachCmpValue) {
                if (!is_string($eachCmpValue)) {
                    throw new \BreakpointDebugging_ErrorException('', 101);
                }
                if ($value === $eachCmpValue) {
                    $cmpResult = true;
                    break;
                }
            }
        } else {
            if ($value !== $cmpValue) {
                $cmpResult = true;
            }
        }
        if ($cmpResult) {
            self::windowVirtualOpen(self::ERROR_WINDOW_NAME, self::$errorHtmlFileContent);
            ob_start();

            echo "$errorMessage" . PHP_EOL
            . "Current value =";
            var_dump($value);

            self::windowHtmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, ob_get_clean());
        }
    }

    /**
     * This registers as function or method being not fixed.
     *
     * @param bool &$isRegister Is this registered?
     *
     * @return void
     *
     * @example \BreakpointDebugging::registerNotFixedLocation(self::$_isRegister[__METHOD__]);
     */
    final static function registerNotFixedLocation(&$isRegister)
    {
        B::assert(func_num_args() === 1);

        // When it has been registered.
        if (!empty($isRegister)) {
            return;
        }
        $isRegister = true;

        $backTrace = debug_backtrace();
        // In case of scope of method or function or included file.
        if (array_key_exists(1, $backTrace)) {
            $backTrace2 = &$backTrace[1];
        } else { // In case of scope of start page file.
            // @codeCoverageIgnoreStart
            $backTrace2['file'] = &$backTrace[0]['file'];
            // @codeCoverageIgnoreEnd
        }
        self::$_notFixedLocations[] = $backTrace2;
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
        B::assert(func_num_args() === 1);
        B::assert(is_array($values));

        $backTrace = debug_backtrace();
        $callInfo = &$backTrace[0];
        if (array_key_exists('file', $callInfo)) {
            // The file name to call.
            $file = &$callInfo['file'];
        } else {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        if (array_key_exists('line', $callInfo)) {
            // The line number to call.
            $line = &$callInfo['line'];
        } else {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        // In case of scope of method or function or included file.
        if (array_key_exists(1, $backTrace)) {
            $backTrace2 = &$backTrace[1];
            if (array_key_exists('function', $backTrace2)
                && ($backTrace2['function'] === 'include_once' || $backTrace2['function'] === '{closure}')
            ) {
                // @codeCoverageIgnoreStart
                // Because including or lambda function is not unit test class method.
                $backTrace2['function'] = '';
            }
            // @codeCoverageIgnoreEnd
        } else { // In case of scope of start page file.
            // @codeCoverageIgnoreStart
            $backTrace2['file'] = &$backTrace[0]['file'];
            // @codeCoverageIgnoreEnd
        }
        self::$_valuesToTrace[$file][$line] = $backTrace2;
        self::$_valuesToTrace[$file][$line]['values'] = $values;
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
    static function convertMbString($string)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        // Analyzes character sets of character string.
        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8' || $charSet === 'ASCII'
        ) {
            return $string;
        } else if ($charSet === false) {
            self::$_onceErrorDispFlag = true;
            throw new \BreakpointDebugging_ErrorException('This is not single character sets.', 101);
        }
        return mb_convert_encoding($string, 'UTF-8', $charSet);
    }

    /**
     * If "Apache" is root user, this method changes the file or directory user to my user. And sets permission.
     *
     * @param string $name              The file or directory name.
     * @param int    $permission        The file or directory permission.
     * @param int    $timeout           Seconds number of timeout.
     * @param int    $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return bool Success or failure.
     */
    static function chmod($name, $permission, $timeout = 10, $sleepMicroSeconds = 100000)
    {
        if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
            return true;
        }
        // In case of Unix.
        if ((fileperms($name) & 0777) !== $permission) {
            return self::_retryForFilesystemFunction('chmod', array ($name, $permission), $timeout, $sleepMicroSeconds);
        }
        return true;
    }

    /**
     * "mkdir" method which sets permission and sets own user to owner.
     *
     * @param array $params            "mkdir()" parameters.
     * @param int   $timeout           Seconds number of timeout.
     * @param int   $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return bool Success or failure.
     */
    static function mkdir(array $params, $timeout = 10, $sleepMicroSeconds = 100000)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        if (self::_retryForFilesystemFunction('mkdir', $params, $timeout, $sleepMicroSeconds)) {
            if (array_key_exists(1, $params)) {
                $permission = $params[1];
            } else {
                $permission = 0777;
            }
            return B::chmod($params[0], $permission, $timeout, $sleepMicroSeconds);
        }
        // @codeCoverageIgnoreStart
        // Because "PHPUnit" throws "\PHPUnit_Framework_Error_Warning".
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Removes directory with retry.
     *
     * @param array $params            "rmdir()" parameters.
     * @param int   $timeout           Seconds number of timeout.
     * @param int   $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return bool Success or failure.
     */
    static function rmdir(array $params, $timeout = 10, $sleepMicroSeconds = 100000)
    {
        return self::_retryForFilesystemFunction('rmdir', $params, $timeout, $sleepMicroSeconds);
    }

    /**
     * Retries until specified timeout for function of filesystem because OS has permission and sometimes fails.
     *
     * @param string $functionName      Execution function name.
     * @param array  $params            Execution function parameters.
     * @param int    $timeout           The timeout.
     * @param int    $sleepMicroSeconds Micro seconds for sleep.
     * @param bool   $isTermination     This call is termination?
     *
     * @return mixed The result or false.
     * @throw Instance of \Exception.
     */
    private static function _retryForFilesystemFunction($functionName, array $params, $timeout, $sleepMicroSeconds, $isTermination = false)
    {
        if ($isTermination) {
            $startTime = 0;
        } else {
            $startTime = time();
        }
        while (true) {
            $isTimeout = time() - $startTime > $timeout;
            try {
                if ($isTermination) {
                    // Detects error last.
                    $result = call_user_func_array($functionName, $params);
                    // @codeCoverageIgnoreStart
                } else {
                    // @codeCoverageIgnoreEnd
                    // Does not detect error except last.
                    set_error_handler('\BreakpointDebugging::handleError', 0);
                    $result = @call_user_func_array($functionName, $params);
                    restore_error_handler();
                }
                if ($result !== false) {
                    clearstatcache();
                    return $result;
                }
                if ($isTermination) {
                    // @codeCoverageIgnoreStart
                    // Because "PHPUnit" throws "\PHPUnit_Framework_Error_Warning".
                    return false;
                    // @codeCoverageIgnoreEnd
                }
                if ($isTimeout) {
                    usleep($sleepMicroSeconds);
                    return self::_retryForFilesystemFunction($functionName, $params, $timeout, $sleepMicroSeconds, true);
                }
            } catch (\Exception $e) {
                if (!$isTermination) {
                    restore_error_handler();
                }
                if ($isTimeout) {
                    // Detects exception last.
                    throw $e;
                }
            }
            // Waits micro second which is specified.
            usleep($sleepMicroSeconds);
        }
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * "fopen" method which sets the file mode, permission and sets own user to owner.
     *
     * @param array $params            "fopen()" parameters.
     * @param int   $permission        The file permission.
     * @param int   $timeout           Seconds number of timeout.
     * @param int   $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return resource The file pointer resource or false.
     */
    static function fopen(array $params, $permission = null, $timeout = 10, $sleepMicroSeconds = 100000)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        $pFile = self::_retryForFilesystemFunction('fopen', $params, $timeout, $sleepMicroSeconds);
        if ($pFile) {
            if (is_null($permission)
                || B::chmod($params[0], $permission, $timeout, $sleepMicroSeconds)
            ) {
                return $pFile;
            }
            // @codeCoverageIgnoreStart
            // Because "PHPUnit" throws "\PHPUnit_Framework_Error_Warning".
        }
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Unlinks with retry.
     *
     * @param array $params            "unlink()" parameters.
     * @param int   $timeout           Seconds number of timeout.
     * @param int   $sleepMicroSeconds Micro seconds to sleep.
     *
     * @return bool Success or failure.
     */
    static function unlink(array $params, $timeout = 10, $sleepMicroSeconds = 100000)
    {
        return self::_retryForFilesystemFunction('unlink', $params, $timeout, $sleepMicroSeconds);
    }

    /**
     * Compresses integer array.
     *
     * @param array $intArray Integer array.
     *
     * @return string Compression character string.
     * @example fwrite($pFile, \BreakpointDebugging::compressIntArray(array(0xFFFFFFFF, 0x7C, 0x7D, 0x7E, 0x0A, 0x0D)));
     */
    static function compressIntArray($intArray)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        $compressBytes = '';
        foreach ($intArray as $int) {
            B::assert(preg_match('`^[0-9]$ | ^[1-9][0-9]+$`xX', $int) === 1);
            for ($diff = 1, $delimiter = 0x80, $tmpBytes = ''; $diff; $int = $diff / 0x7D) {
                // This changes from decimal number to 126 number.
                $diff = 0x7D * (int) ($int / 0x7D);
                $byte = $int - $diff;
                // Changes end of line character.
                if ($byte === 0xA) { // Changes "\n" to read a data by "fgets()" in Windows and Unix.
                    $tmpBytes .= chr(0x7E | $delimiter);
                } else if ($byte === 0xD) { // Changes "\r" to read a data by "fgets()" in Windows.
                    $tmpBytes .= chr(0x7F | $delimiter);
                } else {
                    $tmpBytes .= chr($byte | $delimiter);
                }
                $delimiter = 0;
            }
            $compressBytes .= strrev($tmpBytes);
        }
        // Adds "PHP_EOL" For data reading by "fgets()".
        return $compressBytes . PHP_EOL;
    }

    /**
     * Decompresses to integer array.
     *
     * @param mixed $compressBytes Compression character string by "\BreakpointDebugging::compressIntArray()".
     *
     * @return array Integer array.
     * @example while ($intArray = \BreakpointDebugging::decompressIntArray(fgets($pFile))) {
     */
    static function decompressIntArray($compressBytes)
    {
        B::limitAccess('BreakpointDebugging_InDebug.php');

        $compressBytes = trim($compressBytes, PHP_EOL);
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
                $tmpByte = 0xA; // "\n".
            } else if ($tmpByte === 0x7F) {
                $tmpByte = 0xD; // "\r".
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

    /**
     * Clears recursive array element.
     *
     * @param array $parentArray   Parent array to search element.
     * @param array $parentsArray Array of parents to compare ID.
     *
     * @return array Array which changed.
     */
    private static function _clearRecursiveArrayElement($parentArray, $parentsArray)
    {
        if (count($parentArray) > B::getStatic('$_maxLogElementNumber')) {
            $parentArray = array_slice($parentArray, 0, B::getStatic('$_maxLogElementNumber'), true);
            $parentArray[] = ''; // Array element out of area.
        }

        // Changes the 'GLOBALS' nest element to string.
        if (array_diff_key($GLOBALS, $parentArray) === array ()) {
            $parentArray = array ();
            // Copies global variables except "$GLOBALS['GLOBALS']" element because "$GLOBALS" copy does not increment reference count, so it is special in array.
            foreach ($GLOBALS as $key => $value) {
                if ($key === 'GLOBALS') {
                    // Marks recursive child array.
                    $parentArray[$key] = self::GLOBALS_USING;
                    continue;
                }
                $parentArray[$key] = $value;
            }
        }

        // Creates array to change from recursive array to string.
        $changeArray = $parentArray;
        // Searches recursive array.
        foreach ($parentArray as $childKey => $childArray) {
            // If not recursive array.
            if (!is_array($childArray)) {
                continue;
            }
            // Stores the child array.
            $elementStorage = $parentArray[$childKey];
            // Checks reference of parents array by change from child array to string.
            $parentArray[$childKey] = self::RECURSIVE_ARRAY;
            foreach ($parentsArray as &$cmpParentArray) {
                $parentRecursiveArray = &$cmpParentArray[key($cmpParentArray)];
                // If a recursive array.
                if (!is_array($parentRecursiveArray)) {
                    // Restores recursive parent array.
                    $parentRecursiveArray = $elementStorage;
                    // Deletes recursive child array reference because avoids input parameter change.
                    // Because array copy copies reference ID of its array element.
                    unset($changeArray[$childKey]);
                    // Marks recursive child array.
                    $changeArray[$childKey] = self::RECURSIVE_ARRAY;
                    continue 2;
                }
            }
            // Restores child array.
            $parentArray[$childKey] = $elementStorage;
            // Adds current child element to parents array. Also, does reference copy because checks recursive-reference by using this.
            $parentsArray[][$childKey] = &$parentArray[$childKey];
            // Deletes recursive child array reference because avoids input parameter change.
            // Because array copy copies reference ID of its array element.
            unset($changeArray[$childKey]);
            // Clears recursive array element in under hierarchy.
            $changeArray[$childKey] = self::_clearRecursiveArrayElement($parentArray[$childKey], $parentsArray);
            // Takes out parent array.
            array_pop($parentsArray);
        }
        return $changeArray;
    }

    /**
     * Clears recursive array element.
     *
     * @param mixed &$recursiveArray Recursive array. Keeps reference to this-variable by reference copy.
     *
     * @return mixed Array which changed.
     */
    static function clearRecursiveArrayElement(&$recursiveArray)
    {
        if (!is_array($recursiveArray)) {
            return $recursiveArray;
        }
        $parentsArray = array (array (&$recursiveArray));
        return self::_clearRecursiveArrayElement($recursiveArray, $parentsArray);
    }

    /**
     * Changes from full file path to a class name.
     *
     * @param string $fullFilePath Full file path.
     *
     * @return mixed Class name or false.
     */
    static function fullFilePathToClassName($fullFilePath)
    {
        if (empty($fullFilePath)) {
            return false;
        }
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        if (self::$exeMode & self::UNIT_TEST) {
            array_unshift($includePaths, dirname($_SERVER['SCRIPT_FILENAME']));
        }
        foreach ($includePaths as $includePath) {
            $fullIncludePath = realpath($includePath);
            if (BREAKPOINTDEBUGGING_IS_WINDOWS) {
                $result = stripos($fullFilePath, $fullIncludePath);
            } else {
                $result = strpos($fullFilePath, $fullIncludePath);
            }
            if ($result === 0) {
                $className = substr($fullFilePath, strlen($fullIncludePath) + 1, - strlen('.php'));
                // Changes directory separator and '-' to underscore.
                $className = str_replace(array ('/', '\\', '-'), '_', $className);
                if (!in_array($className, get_declared_classes())) {
                    continue;
                }
                return $className;
            }
        }
        return false;
    }

    /**
     * Opens a initialized virtual Window.
     * CAUTION: This window cannot request including link to itself.
     *          This window permits only a display.
     *
     * @param string $windowName      Window name which opens.
     * @param string $htmlFileContent HTML file content to initialize.
     *
     * @return void
     */
    static function windowVirtualOpen($windowName, $htmlFileContent)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        echo '<script type="text/javascript">' . PHP_EOL
        . '<!--' . PHP_EOL;

        if (!array_key_exists(__FUNCTION__, self::$_onceJavaScript)) {
            self::$_onceJavaScript[__FUNCTION__] = true;
            echo 'function BreakpointDebugging_windowVertualOpen($windowName, $htmlFileContent)' . PHP_EOL
            . '{' . PHP_EOL
            . '    var $openedWindow = open("", $windowName, "");' . PHP_EOL
            . '    $openedWindow.close();' . PHP_EOL
            . '    var $newDocument = open("", $windowName, "").document;' . PHP_EOL
            . '    $newDocument.write($htmlFileContent);' . PHP_EOL
            . '    $newDocument.close();' . PHP_EOL
            . '}' . PHP_EOL;
        }

        $htmlFileContent = str_replace(array ('\\', '\'', "\r", "\n"), array ('\\\\', '\\\'', '\r', '\n'), $htmlFileContent);
        echo "BreakpointDebugging_windowVertualOpen('$windowName', '$htmlFileContent');" . PHP_EOL
        . '//-->' . PHP_EOL
        . '</script>' . PHP_EOL;
    }

    /**
     * Closes out browser window.
     *
     * @param string $windowName
     *
     * @return void
     */
    static function windowClose($windowName)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        echo '<script type="text/javascript">' . PHP_EOL
        . '<!--' . PHP_EOL
        . "var openedWindow = open('', '$windowName');" . PHP_EOL
        . 'openedWindow.close();' . PHP_EOL
        . '//-->' . PHP_EOL
        . '</script>';
    }

    /**
     * Writes HTML character string inside a tag of opened window.
     *
     * @param string $windowName Opened window name.
     * @param string $tagName    The tag name.
     * @param int    $tagNumber  The tag number from 0.
     * @param string $html       HTML character string which writes inside a tag of opened window.
     *
     * @return void
     */
    static function windowHtmlAddition($windowName, $tagName, $tagNumber, $html)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        $html = str_replace(array ('\\', '\'', "\r", "\n"), array ('\\\\', '\\\'', '\r', '\n'), $html);
        echo '<script type="text/javascript">' . PHP_EOL
        . '<!--' . PHP_EOL
        . "open('', '$windowName').document.getElementsByTagName('$tagName')[$tagNumber].innerHTML += '$html';" . PHP_EOL
        . '//-->' . PHP_EOL
        . '</script>' . PHP_EOL;
    }

    /**
     * Scrolls window by distance.
     *
     * @param string $windowName Opened window name.
     * @param int    $dy         Y vector distance.
     *
     * @return void
     */
    static function windowScrollBy($windowName, $dy)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        for ($count = $dy / 5; $count > 0; $count--) {
            echo '<script type="text/javascript">' . PHP_EOL
            . '<!--' . PHP_EOL
            . "open('', '$windowName', '').scrollBy(0, 5);" . PHP_EOL
            . '//-->' . PHP_EOL
            . '</script>' . PHP_EOL;
            flush();
        }
    }

    /**
     * Clears window's header script.
     *
     * @return void
     */
    static function windowScriptClearance($start = 0)
    {
        if (!isset($_SERVER['SERVER_ADDR'])) { // In case of command line.
            return;
        }

        echo '<script type="text/javascript">' . PHP_EOL
        . '<!--' . PHP_EOL;

        if (!array_key_exists(__FUNCTION__, self::$_onceJavaScript)) {
            self::$_onceJavaScript[__FUNCTION__] = true;
            echo 'function BreakpointDebugging_windowScriptClearance()' . PHP_EOL
            . '{' . PHP_EOL
            . '    var $head = document.getElementsByTagName("head")[0];' . PHP_EOL
            . '    var $scripts = document.getElementsByTagName("script");' . PHP_EOL
            . "    for(var \$count = \$scripts.length - 1; \$count >= $start; \$count--){" . PHP_EOL
            . '        $head.removeChild($scripts[$count]);' . PHP_EOL
            . '    }' . PHP_EOL
            . '}' . PHP_EOL;
        }

        echo "BreakpointDebugging_windowScriptClearance();" . PHP_EOL
        . '//-->' . PHP_EOL
        . '</script>' . PHP_EOL;
    }

    ///////////////////////////// For package user until here. /////////////////////////////
    /**
     * Initializes static properties.
     *
     * @return void
     */
    static function initialize()
    {
        global $_BreakpointDebugging_EXE_MODE;

        B::limitAccess(array ('BreakpointDebugging_InDebug.php'));

        self::$_pwd = getcwd();
        self::$_nativeExeMode = self::$exeMode = $_BreakpointDebugging_EXE_MODE;
        unset($GLOBALS['_BreakpointDebugging_EXE_MODE']);
        self::$staticProperties['$exeMode'] = &self::$exeMode;
        self::$staticProperties['$_userName'] = &self::$_userName;
        self::$staticProperties['$_developerIP'] = &self::$_developerIP;
        self::$staticProperties['$_maxLogFileByteSize'] = &self::$_maxLogFileByteSize;
        self::$staticProperties['$_maxLogParamNestingLevel'] = &self::$_maxLogParamNestingLevel;
        self::$staticProperties['$_maxLogElementNumber'] = &self::$_maxLogElementNumber;
        self::$staticProperties['$_maxLogStringSize'] = &self::$_maxLogStringSize;
        self::$staticProperties['$_workDir'] = &self::$_workDir;
        self::$staticProperties['$_onceErrorDispFlag'] = &self::$_onceErrorDispFlag;
        self::$staticProperties['$_callingExceptionHandlerDirectly'] = &self::$_callingExceptionHandlerDirectly;
        self::$staticProperties['$_valuesToTrace'] = &self::$_valuesToTrace;
        self::$staticProperties['$_notFixedLocations'] = &self::$_notFixedLocations;
        self::$errorHtmlFileContent = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>ERROR</title>
    </head>
    <body style="background-color: black; color: white; font-size: 1.5em">
        <pre></pre>
    </body>
</html>
EOD;
        self::$staticProperties['$errorHtmlFileContent'] = &self::$errorHtmlFileContent;
    }

    /**
     * It references "$staticProperties" property for static backup of "PHPUnit".
     *
     * @return array& "$staticProperties" property.
     */
    static function &refStaticProperties()
    {
        B::limitAccess('BreakpointDebugging_PHPUnitStepExecution.php');

        return self::$staticProperties;
    }

    /**
     * It references "$staticPropertyLimitings" property for static backup of "PHPUnit".
     *
     * @return array& "$staticPropertyLimitings" property.
     */
    static function &refStaticPropertyLimitings()
    {
        B::limitAccess('BreakpointDebugging_PHPUnitStepExecution.php');

        return self::$staticPropertyLimitings;
    }

    /**
     * Does autoload by path which was divided by name space separator and underscore separator as directory.
     *
     * @param string $className The class name which calls class member of static.
     *                          Or, the class name which creates new instance.
     *                          Or, the class name when extends base class.
     *
     * @return void
     */
    final static function autoload($className)
    {
        // Trims the left name space root.
        $className = ltrim($className, '\\');
        // Changes underscore and name space separator to directory separator.
        $filePath = str_replace(array ('_', '\\'), '/', $className) . '.php';
        include_once $filePath;
    }

    /**
     * Global exception handler.
     * Displays exception-log in case of debug, or logs exception in case of release.
     *
     * @param object $pException Exception information.
     *
     * @return void
     */
    static function handleException($pException)
    {
        B::limitAccess(
            array (
                'BreakpointDebugging.php',
                'BreakpointDebugging_InDebug.php',
            )
        );

        // Sets global internal error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds.)
        set_error_handler('\BreakpointDebugging_Error::handleInternalError', -1);

        try {
            // trigger_error('Internal error test.'); // For debug.
            // throw new \Exception('Internal exception test.'); // For debug.

            $error = new \BreakpointDebugging_Error();
            $error->handleException2($pException, self::$prependExceptionLog);
            if (self::$_nativeExeMode & self::UNIT_TEST) {
                \BreakpointDebugging_PHPUnitStepExecution::displaysException($pException);
                \BreakpointDebugging_PHPUnitStepExecution::handleUnitTestException($pException);
            }
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            // Because unit test may not cause internal exception inside "\BreakpointDebugging_Error_InAllCase::handleException2()".
            if (self::$_nativeExeMode & self::UNIT_TEST) {
                restore_error_handler();
                throw $e;
            }
            \BreakpointDebugging_Error::handleInternalException($e);
            // @codeCoverageIgnoreEnd
        }

        restore_error_handler();
    }

    /**
     * Global error handler.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     *
     * @return bool Without system log (true).
     */
    static function handleError($errorNumber, $errorMessage)
    {
        B::limitAccess(
            array (
                'BreakpointDebugging.php',
                'BreakpointDebugging_InDebug.php',
            )
        );

        // Sets global internal error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds.)
        set_error_handler('\BreakpointDebugging_Error::handleInternalError', -1);

        try {
            // trigger_error('Internal error test.'); // For debug.
            // throw new \Exception('Internal exception test.'); // For debug.

            $error = new \BreakpointDebugging_Error();
            $error->handleError2($errorNumber, $errorMessage, self::$prependErrorLog, debug_backtrace());
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            // Because unit test may not cause internal exception inside "\BreakpointDebugging_Error_InAllCase::handleError2()".
            if (self::$_nativeExeMode & self::UNIT_TEST) {
                restore_error_handler();
                throw $e;
            }
            \BreakpointDebugging_Error::handleInternalException($e);
            // @codeCoverageIgnoreEnd
        }

        restore_error_handler();

        return true;
    }

    /**
     * Calls global handler inside global handler.
     *
     * @param string $message A message.
     * @param mixed  $id      Exception identification number or null.
     *
     * @return void
     */
    protected static function callExceptionHandlerDirectly($message, $id)
    {
        if (self::$_onceErrorDispFlag) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }
        // Is this method.
        self::$_callingExceptionHandlerDirectly = true;
        self::$_onceErrorDispFlag = true;
        B::handleException(new \BreakpointDebugging_ErrorException($message, $id, null, 2));
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * Calls exception handler inside global error handling or global exception handling. (For this package developer).
     *
     * @param string $message Exception message.
     * @param int    $id      Exception identification number inside function.
     *
     * @return void
     * @example \BreakpointDebugging::internalException($message, 1);
     */
    final static function internalException($message, $id)
    {
        B::limitAccess(
            array (
                'BreakpointDebugging/Error.php',
                'BreakpointDebugging/Error_InDebug.php',
                'BreakpointDebugging/LockByFileExisting.php'
            )
        );

        B::assert(func_num_args() === 2);
        B::assert(is_string($message));
        B::assert(is_int($id));

        B::callExceptionHandlerDirectly($message, $id);
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * Debugs by calling "__destructor()" of all object.
     *
     * @return void
     * @codeCoverageIgnore
     */
    final static function shutdown()
    {
        // Keeps the project work directory at "__destruct".
        chdir(self::$_pwd);
    }

}

global $_BreakpointDebugging_EXE_MODE;

if ($_BreakpointDebugging_EXE_MODE & BA::RELEASE) { // In case of release.
    /**
     * The class for release.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    abstract class BreakpointDebugging_Middle extends \BreakpointDebugging_InAllCase
    {
        /**
         * Empties in release.
         *
         * @return void
         */
        static function limitAccess()
        {

        }

        /**
         * Empties in release.
         *
         * @param bool $assertion Dummy.
         *
         * @return void
         * @codeCoverageIgnore
         * Because this class method is overridden.
         */
        static function assert($assertion)
        {

        }

        /**
         * "ini_set()" in release.
         *
         * @param string $phpIniVariable Same as "ini_set()".
         * @param string $setValue       Same as "ini_set()".
         *
         * @return void
         */
        static function iniSet($phpIniVariable, $setValue)
        {
            ini_set($phpIniVariable, $setValue);
        }

    }

    if ($_BreakpointDebugging_EXE_MODE & BA::UNIT_TEST) { // In case of unit test.
        /**
         * The class for release unit test.
         *
         * @category PHP
         * @package  BreakpointDebugging
         * @author   Hidenori Wasa <public@hidenori-wasa.com>
         * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
         * @version  Release: @package_version@
         * @link     http://pear.php.net/package/BreakpointDebugging
         */
        final class BreakpointDebugging extends \BreakpointDebugging_Middle
        {
            /**
             * Assertion error is stopped at break point in case of release unit test.
             *
             * @param bool $assertion Assertion.
             *
             * @return void
             */
            static function assert($assertion)
            {
                if (!$assertion
                    && !(BA::$exeMode & B::IGNORING_BREAK_POINT)
                ) {
                    // @codeCoverageIgnoreStart
                    if (function_exists('xdebug_break')) {
                        xdebug_break(); // You must use "\BreakpointDebugging_PHPUnitStepExecution::markTestSkippedInRelease(); // Because this unit test is assertion." at top of unit test class method.
                    } else {
                        // Because unit test is exited.
                        ini_set('xdebug.var_display_max_depth', 5);

                        parent::windowVirtualOpen(parent::ERROR_WINDOW_NAME, parent::$errorHtmlFileContent);
                        ob_start();

                        var_dump(debug_backtrace());

                        self::windowHtmlAddition(self::ERROR_WINDOW_NAME, 'pre', 0, ob_get_clean());

                        // Ends the output buffering.
                        while (ob_get_level() > 0) {
                            ob_end_flush();
                        }
                        exit;
                    }
                }
                // @codeCoverageIgnoreEnd
            }

        }

    } else { // In case of not unit test.
        /**
         * The class for release.
         *
         * @category PHP
         * @package  BreakpointDebugging
         * @author   Hidenori Wasa <public@hidenori-wasa.com>
         * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
         * @version  Release: @package_version@
         * @link     http://pear.php.net/package/BreakpointDebugging
         */
        final class BreakpointDebugging extends \BreakpointDebugging_Middle
        {

        }

        if (assert_options(ASSERT_ACTIVE, 0) === false) { // Ignore assert().
            throw new \BreakpointDebugging_ErrorException('', 101);
        }
    }

    if ($_BreakpointDebugging_EXE_MODE === (B::REMOTE | B::RELEASE)) { // In case of remote release.
        // Ignores "Xdebug" in case of remote release because must not stop.
        BA::setXebugExists(false);
    } else {
        BA::setXebugExists(extension_loaded('xdebug'));
    }
} else { // In case of debug.
    // This does not invoke extended class method exceptionally because its class is not defined.
    BA::setXebugExists(extension_loaded('xdebug'));
    include_once __DIR__ . '/BreakpointDebugging_InDebug.php';
}

// Pushes autoload class method.
spl_autoload_register('\BreakpointDebugging::autoload');
// Shifts global exception handler.
set_exception_handler('\BreakpointDebugging::handleException');
// Shifts global error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds.)
set_error_handler('\BreakpointDebugging::handleError', -1);
// Pushes the shutdown class method.
register_shutdown_function('\BreakpointDebugging::shutdown');
// Initializes static class.
B::initialize();

if (B::getStatic('$exeMode') & BA::UNIT_TEST) { // In case of unit test.
    include_once 'BreakpointDebugging_PHPUnitStepExecution.php';
} else {
    /**
     * Dummy class for not unit test.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    class BreakpointDebugging_Exception extends \BreakpointDebugging_Exception_InAllCase
    {

    }

}
/**
 * Own package error exception.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_ErrorException extends \BreakpointDebugging_Exception
{

}

/**
 * "Out of log range" exception.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_OutOfLogRangeException extends \BreakpointDebugging_Exception
{

}

?>
