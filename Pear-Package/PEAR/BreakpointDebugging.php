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
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  SVN: $Id$
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
use \BreakpointDebugging as B;

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
     * @param string $message                Exception message.
     * @param int    $id                     Exception identification number.
     * @param object $previous               Previous exception.
     *
     * @return void
     */
    function __construct($message, $id = null, $previous = null)
    {
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
     * @var bool "Xdebug" existing-flag.
     */
    private static $_xdebugExists;

    /**
     * @var string Upper case 3 character prefix of operating system name.
     */
    private static $_os = '';

    /**
     * @var stirng Your username.
     */
    private static $_userName = '';

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
    private static $_maxLogFileByteSize;

    /**
     * @var int Max log parameter nesting level.
     */
    private static $_maxLogParamNestingLevel = 20;

    /**
     * @var int Maximum count of elements in log. ( Total of parameters or array elements )
     */
    private static $_maxLogElementNumber = 50;

    /**
     * @var int Maximum string type byte-count of log.
     */
    private static $_maxLogStringSize = 3000;

    /**
     * @var string Work directory of this package.
     */
    private static $_workDir;

    /**
     * @var array Locations to be not Fixed.
     */
    private $_notFixedLocations = array ();

    /**
     * @var array Values to trace.
     */
    private $_valuesToTrace = array ();

    /**
     * @var bool Once error display flag.
     */
    private static $_onceErrorDispFlag = false;

    /**
     * @var bool Is it internal method?
     */
    private static $_isInternal = false;

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param $property Same as property.
     *
     * @return void
     */
    static function setXebugExists($property)
    {
        self::$_xdebugExists = $property;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getXebugExists()
    {
        return self::$_xdebugExists;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getOs()
    {
        return self::$_os;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param $property Same as property.
     *
     * @return void
     */
    static function setUserName($property)
    {
        self::$_userName = $property;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getUserName()
    {
        return self::$_userName;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param $property Same as property.
     *
     * @return void
     */
    static function setMaxLogFileByteSize($property)
    {
        self::$_maxLogFileByteSize = $property;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getMaxLogFileByteSize()
    {
        return self::$_maxLogFileByteSize;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param $property Same as property.
     *
     * @return void
     */
    static function setMaxLogParamNestingLevel($property)
    {
        self::$_maxLogParamNestingLevel = $property;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getMaxLogParamNestingLevel()
    {
        return self::$_maxLogParamNestingLevel;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param $property Same as property.
     *
     * @return void
     */
    static function setMaxLogElementNumber($property)
    {
        self::$_maxLogElementNumber = $property;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getMaxLogElementNumber()
    {
        return self::$_maxLogElementNumber;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param $property Same as property.
     *
     * @return void
     */
    static function setMaxLogStringSize($property)
    {
        self::$_maxLogStringSize = $property;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getMaxLogStringSize()
    {
        return self::$_maxLogStringSize;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param $property Same as property.
     *
     * @return void
     */
    static function setWorkDir($property)
    {
        self::$_workDir = $property;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getWorkDir()
    {
        return self::$_workDir;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    function getNotFixedLocations()
    {
        return $this->_notFixedLocations;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    function getValuesToTrace()
    {
        return $this->_valuesToTrace;
    }

    /**
     * Sets private property. We must invoke extended class method instead of this.
     *
     * @param $property Same as property.
     *
     * @return void
     */
    static function setIsInternal($property)
    {
        self::$_isInternal = $property;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getIsInternal()
    {
        return self::$_isInternal;
    }

    /**
     * Constructs instance.
     */
    function __construct()
    {
        self::$_os = strtoupper(substr(PHP_OS, 0, 3));
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
        $value = (string) ini_get($phpIniVariable);
        $cmpResult = false;
        if (is_array($cmpValue)) {
            foreach ($cmpValue as $eachCmpValue) {
                if (!is_string($eachCmpValue)) {
                    throw new \BreakpointDebugging_ErrorException('', 2);
                }
                if ($value === $eachCmpValue) {
                    $cmpResult = true;
                    break;
                }
            }
        } else {
            if (!is_string($cmpValue)) {
                throw new \BreakpointDebugging_ErrorException('', 3);
            }
            if ($value !== $cmpValue) {
                $cmpResult = true;
            }
        }
        if ($cmpResult) {
            echo "<pre>$errorMessage</pre>" .
            "<pre>Current value =</pre>";
            var_dump($value);
        }
    }

    /**
     * This registers as function or method being not fixed.
     *
     * @param bool &$isRegister Is this registered?
     *
     * @return void
     *
     * @example static $isRegister = false; \BreakpointDebugging::registerNotFixedLocation($isRegister);
     */
    final static function registerNotFixedLocation(&$isRegister)
    {
        B::assert(func_num_args() === 1, 1);
        B::assert(is_bool($isRegister), 2);

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
        $_BreakpointDebugging->_notFixedLocations[] = $backTrace2;
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

        B::assert(func_num_args() === 1, 1);
        B::assert(is_array($values), 2);

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
        $_BreakpointDebugging->_valuesToTrace[$file][$line] = $backTrace2;
        $_BreakpointDebugging->_valuesToTrace[$file][$line]['values'] = $values;
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
        // Analyzes character sets of character string.
        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8' || $charSet === 'ASCII') {
            return $string;
        } else if ($charSet === false) {
            self::$_onceErrorDispFlag = true;
            throw new \BreakpointDebugging_ErrorException('This is not single character sets.', 3);
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
    final private static function _setOwner($name, $permission)
    {
        if (self::$_os === 'WIN') { // In case of Windows.
            return;
        }
        // In case of Unix.
        chmod($name, $permission);
        if (trim(`echo \$USER`) === 'root') {
            $user = self::$_userName;
            `chown \$user.\$user \$name`;
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
     * @param stirng $mode       The file mode.
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
     * @example fwrite($pFile, \BreakpointDebugging::compressIntArray(array(0xFFFFFFFF, 0x7C, 0x7D, 0x7E, 0x0A, 0x0D)));
     */
    static function compressIntArray($intArray)
    {
        $compressBytes = '';
        foreach ($intArray as $int) {
            B::internalAssert(preg_match('`^[0-9]$ | ^[1-9][0-9]+$`xX', $int) === 1, 1);
            for ($diff = 1, $delimiter = 0x80, $tmpBytes = ''; $diff; $int = $diff / 0x7D) {
                // This changes from decimal number to 126 number.
                $diff = 0x7D * (int) ($int / 0x7D);
                $byte = $int - $diff;
                // Changes end of line character.
                if ($byte === "\n") { // For data reading by "fgets()" in Windows and Unix.
                    $tmpBytes .= chr(0x7E | $delimiter);
                } else if ($byte === "\r") { // For line feed of Windows.
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
                $tmpByte = "\n";
            } else if ($tmpByte === 0x7F) {
                $tmpByte = "\r";
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
        // Changes underscore and name space separator to directory separator.
        $className = str_replace(array ('_', '\\'), '/', $className) . '.php';
        include_once $className;
    }

    /**
     * Global exception handler.
     * Displays exception-log in case of debug, or logs exception in case of release.
     *
     * @param object $pException Exception information.
     *
     * @return void
     */
    final static function exceptionHandler($pException)
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE & self::UNIT_TEST) {
            $callStack = $pException->getTrace();
            $call = array_key_exists(0, $callStack) ? $callStack[0] : array ();
            if ((array_key_exists('class', $call) && $call['class'] === 'BreakpointDebugging_InAllCase')
            && (array_key_exists('function', $call) && $call['function'] === 'internal')) { // In case of direct call from "BreakpointDebugging_InAllCase::internal()".
                \PHPUnit_Util_ErrorHandler::handleError($pException->getCode(), $pException->getMessage(), $pException->getFile(), $pException->getLine());
            }
        }
        // For call stack display.
        if (method_exists('\BreakpointDebugging', 'changeExecutionModeForUnitTest')) {
            $storeExeMode = B::changeExecutionModeForUnitTest();
        }

        $error = new \BreakpointDebugging_Error();
        $error->exceptionHandler2($pException, self::$prependExceptionLog);
        if ($storeExeMode & B::UNIT_TEST) {
            $_BreakpointDebugging_EXE_MODE = $storeExeMode;
        }
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

        // For call stack display.
        if (method_exists('\BreakpointDebugging', 'changeExecutionModeForUnitTest')) {
            $storeExeMode = B::changeExecutionModeForUnitTest();
        }
        $error = new \BreakpointDebugging_Error();
        $error->errorHandler2($errorNumber, $errorMessage, self::$prependErrorLog);
        if ($storeExeMode & B::UNIT_TEST) {
            $_BreakpointDebugging_EXE_MODE = $storeExeMode;
        }
        return true;
    }

    /**
     * Calls global handler inside global handler.
     *
     * @param string $message A message.
     * @param int    $id      Exception identification number.
     *
     * @return void
     */
    final protected static function internal($message, $id)
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (self::$_onceErrorDispFlag) {
            return;
        }
        // Is internal method.
        self::$_isInternal = true;
        self::$_onceErrorDispFlag = true;
        self::exceptionHandler(new \BreakpointDebugging_ErrorException($message, $id, null, 2));
        if ($_BreakpointDebugging_EXE_MODE & self::REMOTE_DEBUG) { // In case of remote debug.
            // Remote debug must end immediately to avoid eternal execution.
            exit;
        }
    }

    /**
     * Calls exception handler inside global error handling or global exception handling. (For this package developer).
     *
     * @param string $message Exception message.
     * @param int    $id      Exception identification number.
     *
     * @return void
     * @example \BreakpointDebugging::internalException($message, 1);
     */
    final static function internalException($message, $id)
    {
        self::internal($message, $id);
    }

    /**
     * Calls "__destructor()" of other object for debug by keeping "$_BreakpointDebugging".
     *
     * @return void
     */
    final static function shutdown()
    {
        global $_BreakpointDebugging;

        foreach ($GLOBALS as &$variable) {
            if (is_object($variable)) {
                // Excludes this object.
                if ($variable === $_BreakpointDebugging) {
                    continue;
                }
                if (is_callable(array ($variable, '__destruct'))) {
                    // Calls "__destruct" class method.
                    $variable = null;
                }
            }
        }
    }

}

global $_BreakpointDebugging_EXE_MODE;

if ($_BreakpointDebugging_EXE_MODE === BreakpointDebugging_InAllCase::RELEASE) { // In case of release.
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
    class BreakpointDebugging_Exception extends \BreakpointDebugging_Exception_InAllCase
    {

    }

    /**
     * This class executes error or exception handling, and it is only in case of release mode.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    final class BreakpointDebugging extends \BreakpointDebugging_InAllCase
    {
        /**
         * Empties in release.
         *
         * @return void
         */
        static function breakpoint()
        {

        }

        /**
         * Empties in release.
         *
         * @return void
         */
        static function limitInvokerFilePaths()
        {

        }

        /**
         * Empties in release.
         *
         * @return void
         */
        static function assert()
        {

        }

        /**
         * Empties in release.
         *
         * @return void
         */
        static function internalAssert()
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

    if (assert_options(ASSERT_ACTIVE, 0) === false) { // Ignore assert().
        throw new \BreakpointDebugging_ErrorException('', 1);
    }
    // Ignores "Xdebug" in case of release because must not stop.
    BreakpointDebugging::setXebugExists(false);
} else { // In case of not release.
    // This does not invoke extended class method exceptionally because its class is not defined.
    BreakpointDebugging_InAllCase::setXebugExists(extension_loaded('xdebug'));
    include_once __DIR__ . '/BreakpointDebugging_Option.php';
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

// This sets global exception handler.
set_exception_handler('\BreakpointDebugging::exceptionHandler');
// Uses "PHPUnit" error handler in case of command.
if (isset($_SERVER['SERVER_ADDR'])) { // In case of not command.
    // This sets global error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds and so on.)
    set_error_handler('\BreakpointDebugging::errorHandler', -1);
}
global $_BreakpointDebugging;
$_BreakpointDebugging = new \BreakpointDebugging();
spl_autoload_register('\BreakpointDebugging::autoload');
register_shutdown_function('\BreakpointDebugging::shutdown');

?>
