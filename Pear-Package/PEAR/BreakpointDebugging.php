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
    const RECURSIVE_ARRAY = '### DANGER: You used recursive array! ###';

    /**
     * @const string Character string which means using "$GLOBALS".
     */
    const GLOBALS_USING = '### DANGER: You used "$GLOBALS"! ###';

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
     * @var string Upper case 3 character prefix of operating system name.
     */
    private static $_os = '';

    /**
     * @var stirng Your username.
     */
    private static $_userName = '';

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
     * @var int Execution mode.
     */
    protected static $exeMode;

    /**
     * @var int Native execution mode.
     */
    private static $_nativeExeMode;

    /**
     * Debugs by using breakpoint.
     * We must define this class method outside namespace, and we must not change method name when we call this method.
     *
     * @param string $message       Message.
     * @param array  $callStackInfo A call stack info.
     *
     * @return void
     * @example B::breakpoint('Error message.', debug_backtrace());
     */
    static function breakpoint($message, $callStackInfo)
    {
        B::assert(func_num_args() === 2, 1);
        B::assert(is_string($message), 2);
        B::assert(is_array($callStackInfo), 3);

        if (self::$exeMode === (B::REMOTE | B::RELEASE)
            || (self::$exeMode & B::IGNORING_BREAK_POINT)) {
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

        if (self::getXebugExists()) {
            xdebug_break(); // Breakpoint. See local variable value by doing step execution here.
            // Push stop button if is thought error message.
        }

        //if (self::$exeMode & (self::REMOTE | self::RELEASE) === self::REMOTE) { // In case of remote debug.
        if (self::$_nativeExeMode === self::REMOTE) { // In case of remote debug.
            // @codeCoverageIgnoreStart
            // Remote debug must end immediately to avoid eternal execution.
            exit;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Checks unit-test-execution-mode.
     *
     * @//param string $unitTestKind Unit test kind.
     * @param bool $isUnitTest It is unit test?
     *
     * @return void
     *
     * @example
     *      <?php
     *
     *      require_once './BreakpointDebugging_Including.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      B::isUnitTestExeMode(); // Checks the execution mode.
     *          .
     *          .
     *          .
     */
    //static function isUnitTestExeMode($unitTestKind = 'FALSE')
    static function isUnitTestExeMode($isUnitTest = false)
    {
        //if (self::$exeMode & B::UNIT_TEST) { // In case of unit test.
        //    return parent::isUnitTestExeMode($isUnitTest);
        //} else
        //if ($isUnitTest !== 'FALSE') {
        if ($isUnitTest) {
            //throw new \BreakpointDebugging_ErrorException('<pre>You mistook "\BreakpointDebugging::isUnitTestExeMode(\'...\');".</pre>');
            throw new \BreakpointDebugging_ErrorException('<pre>You must not set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'..._UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>', 3);
        }
    }

    /**
     * Initializes static properties.
     *
     * @return void
     */
    static function initialize()
    {
        global $_BreakpointDebugging_EXE_MODE;

        B::limitAccess(array ('BreakpointDebugging_UnitTestCaller.php', 'BreakpointDebugging_Option.php'));

        self::$_pwd = getcwd();
        self::$_nativeExeMode = self::$exeMode = $_BreakpointDebugging_EXE_MODE;
        unset($_BreakpointDebugging_EXE_MODE);
        self::$staticProperties['$exeMode'] = &self::$exeMode;
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
     * Gets a static property reference.
     *
     * @param string $propertyName Static property name.
     *
     * @return mixed& Static property.
     */
    static function &refStatic($propertyName)
    {
        B::limitAccess('BreakpointDebugging_Option.php');

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
                'BreakpointDebugging_Option.php'
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
                $backTrace2['function'] = '';
            }
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
        B::limitAccess('BreakpointDebugging_Option.php');

        // Analyzes character sets of character string.
        $charSet = mb_detect_encoding($string);
        if ($charSet === 'UTF-8' || $charSet === 'ASCII'
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

    /**
     * Clears recursive array element.
     *
     * @param array $parentArray  Parent array to search element.
     * @param array $parentsArray Array of parents to compare ID.
     *
     * @return array Array which changed.
     */
    private static function _clearRecursiveArrayElement($parentArray, $parentsArray)
    {
        if (count($parentArray) > B::getStatic('$_maxLogElementNumber')) {
            $parentArray = array_slice($parentArray, 0, B::getStatic('$_maxLogElementNumber'), true);
            $parentArray[] = ''; // Array element out of area.
            //$parentArray[] = array ('Out of area.'); // Array element out of area.
        }
        // If this may be "$GLOBALS".
        if (array_key_exists('GLOBALS', $parentArray) && is_array($parentArray['GLOBALS'])
        ) {
            // Makes array by copying element because must not do "unset()" of "$GLOBALS".
            foreach ($parentArray as $childKey => $childArray) {
                // Changes the 'GLOBALS' nest element to string.
                if ($childKey === 'GLOBALS') {
                    $changingArray['GLOBALS'] = self::GLOBALS_USING;
                    //$changingArray['GLOBALS'] = array ('Recursive array!');
                    continue;
                }
                // Does reference copy because may be reference variable.
                $changingArray[$childKey] = &$parentArray[$childKey];
            }
            $parentArray = $changingArray;
        }
        // Creates array to change from recursive array to string.
        $changingArray = $parentArray;
        // Searches recursive array.
        foreach ($parentArray as $childKey => $childArray) {
            // If not recursive array.
            if (!is_array($childArray)) {
                //if (!is_array($childArray)
                //    || (count($childArray) === 1 && array_key_exists(0, $childArray) && !is_array($childArray[0]))
                //) {
                continue;
            }
            // Stores the child array.
            $elementStoring = $parentArray[$childKey];
            // Checks reference of parents array by changing from child array to string.
            $parentArray[$childKey] = self::RECURSIVE_ARRAY;
            //$parentArray[$childKey] = array ('Recursive array!');
            foreach ($parentsArray as $cmpParentArrays) {
                // If a recursive array.
                if (!is_array(current($cmpParentArrays))) {
                    // Deletes recursive array reference.
                    unset($changingArray[$childKey]);
                    // Marks recursive array.
                    $changingArray[$childKey] = self::RECURSIVE_ARRAY;
                    //$changingArray[$childKey] = array ('Recursive array!');
                    // Restores child array.
                    $parentArray[$childKey] = $elementStoring;
                    continue 2;
                }
            }
            // Restores child array.
            $parentArray[$childKey] = $elementStoring;
            // Adds current child element to parents array. Also, does reference copy because checks recursive-reference by using this.
            $parentsArray[][$childKey] = &$parentArray[$childKey];
            // Clears recursive array element in under hierarchy.
            $changingArray[$childKey] = self::_clearRecursiveArrayElement($parentArray[$childKey], $parentsArray);
            // Takes out parent array.
            array_pop($parentsArray);
        }
        return $changingArray;
    }

    /**
     * Clears recursive array element.
     *
     * @param array &$recursiveArray Recursive array. Keeps reference to this-variable by reference copy.
     *
     * @return array Array which changed.
     */
    static function clearRecursiveArrayElement(&$recursiveArray)
    {
        $parentsArray = array (array (&$recursiveArray));
        return self::_clearRecursiveArrayElement($recursiveArray, $parentsArray);
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
    static function handleException($pException)
    {
        B::limitAccess('BreakpointDebugging_Option.php');

        $error = new \BreakpointDebugging_Error();
        $error->handleException2($pException, self::$prependExceptionLog);
        //if ((self::$_nativeExeMode & (self::RELEASE | self::UNIT_TEST)) === (self::RELEASE | self::UNIT_TEST)) {
        if ((self::$_nativeExeMode & self::UNIT_TEST) === self::UNIT_TEST) {
//            $callStack = debug_backtrace();
//            if (!array_key_exists(0, $callStack)
//                || !array_key_exists('file', $callStack[0])
//                || strripos($callStack[0]['file'], 'UnitTestOverriding.php') === strlen($callStack[0]['file']) - strlen('UnitTestOverriding.php')
//            ) {
//                B::iniSet('xdebug.var_display_max_depth', 5, false);
//                var_dump($pException);
//                exit;
////            $error = null;
////            $storeExeMode = self::$exeMode;
////            self::$exeMode = (self::$_nativeExeMode & self::REMOTE) | self::UNIT_TEST;
////            include_once __DIR__ . '/BreakpointDebugging/Error_Option.php';
////            $error->handleException2($pException, self::$prependExceptionLog);
////            self::$exeMode = $storeExeMode;
//            }
            BreakpointDebugging_UnitTestCaller::displaysException($pException);
            BreakpointDebugging_UnitTestCaller::handleUnitTestException($pException);
        }
    }

    /**
     * Handles an error.
     *
     * @param int    $errorNumber  Error number.
     * @param string $errorMessage Error message.
     *
     * @return bool Without system log (true).
     */
    static function handleError($errorNumber, $errorMessage)
    {
        B::limitAccess('BreakpointDebugging_Option.php');

        $error = new \BreakpointDebugging_Error();
        $error->handleError2($errorNumber, $errorMessage, self::$prependErrorLog, debug_backtrace());

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
                'BreakpointDebugging/Error_Option.php',
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

if ($_BreakpointDebugging_EXE_MODE & BA::UNIT_TEST) { // In case of unit test.
    include_once __DIR__ . '/BreakpointDebugging_UnitTestCaller.php';
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
    class BreakpointDebugging_UnitTestCaller extends BA
    {

    }

}

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
     * @//codeCoverageIgnore
     */
    //final class BreakpointDebugging extends \BreakpointDebugging_UnitTestCaller
    class BreakpointDebugging_Middle extends \BreakpointDebugging_UnitTestCaller
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
         * @return void
         */
        static function assert()
        {
            // @codeCoverageIgnoreStart
            // Because this class method is overridden.
        }

        // @codeCoverageIgnoreEnd
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

//        /**
//         * Checks unit-test-execution-mode, and sets unit test directory.
//         *
//         * @param string $unitTestKind Unit test kind.
//         *
//         * @return void
//         */
//        static function isUnitTestExeMode($unitTestKind = 'FALSE')
//        {
//            if (self::$exeMode & B::UNIT_TEST) { // In case of unit test.
//                //\BreakpointDebugging_UnitTestCaller::isUnitTestExeMode($unitTestKind);
//                return parent::isUnitTestExeMode($unitTestKind);
//            } else if ($unitTestKind !== 'FALSE') {
//                throw new \BreakpointDebugging_ErrorException('<pre>You mistook "\BreakpointDebugging::isUnitTestExeMode(\'...\');".</pre>', 3);
//            }
//        }
    }

    if ($_BreakpointDebugging_EXE_MODE & BA::UNIT_TEST) { // In case of unit test.
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
                    && !(self::$exeMode & B::IGNORING_BREAK_POINT)
                    && function_exists('xdebug_break')
                ) {
                    xdebug_break(); // You must use "\BreakpointDebugging_UnitTestOverridingBase::markTestSkippedInRelease(); // Because this unit test is assertion." at top of unit test class method.
                }
            }

        }

    } else {
        final class BreakpointDebugging extends \BreakpointDebugging_Middle
        {

        }

        if (assert_options(ASSERT_ACTIVE, 0) === false) { // Ignore assert().
            throw new \BreakpointDebugging_ErrorException('', 1);
        }
    }

    if ($_BreakpointDebugging_EXE_MODE === (B::REMOTE | B::RELEASE)) { // In case of remote release.
        // Ignores "Xdebug" in case of remote release because must not stop.
        //BreakpointDebugging::setXebugExists(false);
        BA::setXebugExists(false);
    } else {
        BA::setXebugExists(extension_loaded('xdebug'));
    }
} else { // In case of not release.
    // This does not invoke extended class method exceptionally because its class is not defined.
    BA::setXebugExists(extension_loaded('xdebug'));
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

// This sets global exception handler.
set_exception_handler('\BreakpointDebugging::handleException');
// Uses "PHPUnit" error handler in case of command.
if (isset($_SERVER['SERVER_ADDR'])) { // In case of not command.
    // This sets global error handler.( -1 sets all bits on 1. Therefore, this specifies error, warning and note of all kinds and so on.)
    set_error_handler('\BreakpointDebugging::handleError', -1);
}
spl_autoload_register('\BreakpointDebugging::autoload');
register_shutdown_function('\BreakpointDebugging::shutdown');
\BreakpointDebugging::initialize();

?>
