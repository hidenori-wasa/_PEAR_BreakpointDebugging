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
     * @param string $message  Exception message.
     * @param int    $id       Exception identification number.
     * @param object $previous Previous exception.
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
     * @var mixed Temporary variable.
     */
    static $tmp;

    /**
     * @var array Static properties reference.
     */
    protected static $staticProperties;

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
    private static $_notFixedLocations;

    /**
     * @var array Values to trace.
     */
    private static $_valuesToTrace;

    /**
     * @var bool Once error display flag.
     */
    private static $_onceErrorDispFlag = false;

    /**
     * @var bool Calling exception handler directly?
     */
    private static $_callingExceptionHandlerDirectly = false;

    /**
     * @var string The project work directory.
     */
    private static $_pwd;

    /**
     * Initializes static properties.
     */
    function __construct()
    {
        B::limitAccess('BreakpointDebugging_Option.php');

        self::$_pwd = getcwd();
        self::$_os = strtoupper(substr(PHP_OS, 0, 3));
        self::$staticProperties['$_os'] = &self::$_os;
        self::$staticProperties['$_userName'] = &self::$_userName;
        self::$staticProperties['$_maxLogFileByteSize'] = &self::$_maxLogFileByteSize;
        self::$staticProperties['$_maxLogParamNestingLevel'] = &self::$_maxLogParamNestingLevel;
        self::$staticProperties['$_maxLogElementNumber'] = &self::$_maxLogElementNumber;
        self::$staticProperties['$_maxLogStringSize'] = &self::$_maxLogStringSize;
        self::$staticProperties['$_workDir'] = &self::$_workDir;
        self::$staticProperties['$_onceErrorDispFlag'] = &self::$_onceErrorDispFlag;
        self::$staticProperties['$_callingExceptionHandlerDirectly'] = &self::$_callingExceptionHandlerDirectly;
        self::$staticProperties['$_valuesToTrace'] = &self::$_valuesToTrace;
        self::$staticProperties['$_notFixedLocations'] = &self::$_notFixedLocations;
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
        B::limitAccess('BreakpointDebugging_Option.php');

        return self::$staticProperties[$propertyName];
    }

    /**
     * Sets a static property value.
     *
     * @param string $propertyName Static property name.
     * @param mixed  $value        Static property value.
     *
     * @return void
     */
    static function setStatic($propertyName, $value)
    {
        B::limitAccess('BreakpointDebugging_Option.php');

        self::$staticProperties[$propertyName] = $value;
    }

    /**
     * Gets private property.
     *
     * @return Same as property.
     */
    static function getXebugExists()
    {
        B::limitAccess('BreakpointDebugging_Option.php');

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
        B::limitAccess('BreakpointDebugging_Option.php');

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
        B::assert(func_num_args() === 1);
        B::assert(is_bool($isRegister));

        // When it has been registered.
        if ($isRegister) {
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
        B::setStatic('$_notFixedLocations', self::$_notFixedLocations);
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
        } else { // In case of scope of start page file.
            // @codeCoverageIgnoreStart
            $backTrace2['file'] = &$backTrace[0]['file'];
            // @codeCoverageIgnoreEnd
        }
        self::$_valuesToTrace[$file][$line] = $backTrace2;
        self::$_valuesToTrace[$file][$line]['values'] = $values;
        B::setStatic('$_valuesToTrace', self::$_valuesToTrace);
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
        B::limitAccess('BreakpointDebugging_Option.php');

        // Analyzes character sets of character string.
        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8'
            || $charSet === 'ASCII'
        ) {
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
    protected static function setOwner($name, $permission)
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
        B::limitAccess('BreakpointDebugging_Option.php');

        if (mkdir($dirName)) {
            B::setOwner($dirName, $permission);
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
        B::limitAccess('BreakpointDebugging_Option.php');

        $pFile = fopen($fileName, $mode);
        if ($pFile) {
            B::setOwner($fileName, $permission);
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
        B::limitAccess('BreakpointDebugging_Option.php');

        $compressBytes = '';
        foreach ($intArray as $int) {
            B::assert(preg_match('`^[0-9]$ | ^[1-9][0-9]+$`xX', $int) === 1, 1);
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
        B::limitAccess('BreakpointDebugging_Option.php');

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
    static function exceptionHandler($pException)
    {
        B::limitAccess('BreakpointDebugging_Option.php');

        $error = new \BreakpointDebugging_Error();
        $error->exceptionHandler2($pException, self::$prependExceptionLog);
    }

    /**
     * Error handler.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     *
     * @return bool Without system log (true).
     */
    static function errorHandler($errorNumber, $errorMessage)
    {
        B::limitAccess('BreakpointDebugging_Option.php');

        $error = new \BreakpointDebugging_Error();
        $error->errorHandler2($errorNumber, $errorMessage, self::$prependErrorLog, debug_backtrace());

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
        B::exceptionHandler(new \BreakpointDebugging_ErrorException($message, $id, null, 2));
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

if ($_BreakpointDebugging_EXE_MODE === BreakpointDebugging_InAllCase::RELEASE) { // In case of release.
    /**
     * Dummy class for release.
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
     * The class for release.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     * @codeCoverageIgnore
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
        static function limitAccess()
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
