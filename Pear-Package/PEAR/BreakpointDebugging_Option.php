<?php

/**
 * Class which is for breakpoint debugging.
 *
 * "*_Option.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
 *
 * ### Environment which can do breakpoint debugging. ###
 * Debugger which can use breakpoint.
 * The present recommendation debugging environment is
 * "WindowsXP Professional" + "NetBeans IDE 7.2.1" + "XAMPP 1.7.3" or
 * "Ubuntu desktop" + "NetBeans IDE 7.2.1" + "XAMPP for Linux 1.7.3a".
 * Do not use version greater than "XAMPP 1.7.3" for "NetBeans IDE 7.2.1"
 * because MySQL version causes discordance.
 * Notice: Use "phpMyAdmin" to see database and to execute "MySQL" command.
 *
 * ### The advantage of breakpoint debugging. ###
 * Can find a position of a bug immediately.
 * In addition to it, we can examine value of variable.
 * Therefore, can debug quickly.
 *
 * ### How to code breakpoint debugging. ###
 * We must code as follows to process in "BreakpointDebugging" class.
 * We should verify an impossible "parameters and return value" of
 * "function and method" with "\BreakpointDebugging::assert()".
 * Also, we should verify other impossible values of those.
 * We do not need error and exception handler coding because an error and an exception
 * which wasn't caught are processed by global handler in "BreakpointDebugging" class.
 *
 * ### The execution procedure. ###
 * Procedure 1: Please, install "XDebug" by seeing "http://xdebug.org/docs/install"
 *      in case of your local host.
 *      "Xdebug" extension is required because "uses breakpoint,
 *      displays for fatal error and detects infinity recursive function call".
 * Procedure 2: If you want remote debug, please set 'xdebug.remote_host =
 *      "<name or ip of host which debugger exists>"' into "php.ini" file, if remote server supports.
 * Procedure 3: Please, set *.php file format to utf8, but we should create backup of
 *      php files because multibyte strings may be destroyed.
 * Procedure 4: Please, copy "BreakpointDebugging_Including.php" into your project directory.
 * And, copy "BreakpointDebugging_MySetting*.php" to
 * "const BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME" of your project directory.
 * Procedure 5: Please, edit BreakpointDebugging_MySetting*.php for customize.
 *      Then, it fixes part setting about all debugging modes.
 * Procedure 6: Please, copy following in your project php code.
 *      require_once './BreakpointDebugging_Including.php';
 * Procedure 7: Please, check debugging-mode using "B::isUnitTestExeMode()" in start page,
 *      and set debugging mode to
 *      "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags('...');"
 *      into "BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php'".
 *
 *      Then, use "B::getStatic('$exeMode')" to get value.
 *      Lastly, we must execute all codes using "\BreakpointDebugging::displayCodeCoverageReport()" before release.
 *      Then, we must set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags('RELEASE');".
 *      Because "XDebug" information is not displayed on 'RELEASE' mode.
 * Procedure 8: Please, if you use "Unix", register your username as
 *      "User" and "Group" into "lampp/apache/conf/httpd.conf".
 *      And, register "export PATH=$PATH:/opt/lampp/bin" into "~/.profile".
 * Procedure 9: Please, if you can change "php.ini" file,
 *      use "B::iniCheck()" instead of "B::iniSet()" in "*_MySetting.php" file,
 *      and move it to "*_MySetting_Option.php" file
 *      because decreases the read and the parse bytes.
 *      Also, use "B::iniCheck()" instead of "B::iniSet()"
 *      in "*_MySetting_Option.php" file.
 *
 * Caution: Do not execute "ini_set('error_log', ...)" because
 * this package uses local log rotation instead of system log.
 *
 * Option procedure: Please, register at top of the function or method or file
 *      which has been not fixed. Please, copy following.
 *      "static $isRegister = false; \BreakpointDebugging::registerNotFixedLocation($isRegister);"
 *      Then, we can discern function or method or file
 *      which has been not fixed with browser screen or log.
 * Option procedure: Please, register local variable or global variable
 *      which you want to see with "\BreakpointDebugging::addValuesToTrace()".
 *
 * ### Exception hierarchical structure ###
 *  PEAR_Exception
 *      BreakpointDebugging_Exception
 *          BreakpointDebugging_ErrorException
 *
 * ### Useful class index. ###
 * This class override a class without inheritance, but only public member can be inherited.
 *      class BreakpointDebugging_OverrideClass
 * Class which locks php-code by file existing.
 *      class BreakpointDebugging_LockByFileExisting
 * Class which locks php-code by shared memory operation.
 *      class BreakpointDebugging_LockByShmop
 * Class which locks php-code by "flock()".
 *      class BreakpointDebugging_LockByFlock
 *
 * My viewpoint about PHP-types for reading my PHP code.
 *      Any types of PHP have only ID. ( Contents of ID are pointer to movable memory which has variable, array ID district or object instance or resource ).
 *
 *      Variable copy is new ID creation, and points the same memory area. ( Memory area reference count and ID count is incremented ).
 *      Then, when memory area is updated, its memory area is allocated newly, then writes updated value.
 *      But, object type is reference copy. If you want to copy, use "$cloneObject = clone $object;".
 *
 *      Variable reference copy is ID copy, and points the same memory area. ( ID count is incremented ).
 *      Then, when memory area is updated, writes updated value.
 *
 *      When "unset()" function deletes only ID, ID count is decremented, then if it became 0, memory area which is pointed is deleted.
 *
 *      When variable is written null value, memory area reference count is decremented, then if it became 0, memory area which is pointed is deleted.
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
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

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
    /**
     * Constructs instance.
     *
     * @param string $message                Exception message.
     * @param int    $id                     Exception identification number.
     * @param object $previous               Previous exception.
     * @param int    $omissionCallStackLevel Omission call stack level.
     *                                       Uses for assertion or error exception throwing because invokes plural inside a class method when we execute error unit test.
     *
     * @return void
     */
    function __construct($message, $id = null, $previous = null, $omissionCallStackLevel = 0)
    {
        B::assert(func_num_args() <= 4, 1);
        B::assert(is_string($message), 2);
        B::assert(is_int($id) || $id === null, 3);
        B::assert($previous instanceof \Exception || $previous === null, 5);
        B::assert(mb_detect_encoding($message, 'utf8', true) !== false, 6);

        // Adds "[[[CLASS=<class name>] FUNCTION=<function name>] ID=<identification number>]" to message in case of unit test.
        if (B::getStatic('$exeMode') & B::UNIT_TEST) {
            B::assert(is_int($omissionCallStackLevel) && $omissionCallStackLevel >= 0, 7);

            if ($id === null) {
                $idString = '';
            } else {
                $idString = ' ID=' . $id;
            }
            $function = '';
            $class = '';
            $callStack = $this->getTrace();
            if (array_key_exists($omissionCallStackLevel, $callStack)) {
                $call = $callStack[$omissionCallStackLevel];
                if (array_key_exists('function', $call)) {
                    $function = ' FUNCTION=' . $call['function'];
                }
                if (array_key_exists('class', $call)) {
                    $class = ' CLASS=' . $call['class'];
                }
            }
            $message .= $class . $function . $idString;
        }
        parent::__construct($message, $id, $previous);
    }

}

/**
 * This class executes error or exception handling, and it is except release mode.
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
     * @var array Setting option filenames.
     */
    private static $_onceFlag;

    /**
     * @var string Browser execution pass.
     */
    private static $_browserPass = 'C:\Program Files\Mozilla Firefox\firefox.exe';

    /**
     * @var string Include-paths.
     */
    private static $_includePaths;

    /**
     * @var  string Unit test directory.
     */
    private static $_unitTestDir;

    /**
     * Limits static properties accessing.
     *
     * @return void
     */
    static function initialize()
    {
        B::limitAccess('BreakpointDebugging.php');

        B::assert(func_num_args() === 0);

        parent::initialize();

        self::$staticProperties['$_includePaths'] = &self::$_includePaths;
        self::$staticPropertyLimitings['$_includePaths'] = ''; // For unit test.
        $tmp = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';
        self::$staticPropertyLimitings['$_userName'] = $tmp;
        self::$staticPropertyLimitings['$_maxLogFileByteSize'] = $tmp;
        self::$staticPropertyLimitings['$_maxLogParamNestingLevel'] = $tmp;
        self::$staticPropertyLimitings['$_maxLogElementNumber'] = $tmp;
        self::$staticPropertyLimitings['$_maxLogStringSize'] = $tmp;
        self::$staticPropertyLimitings['$_workDir'] = $tmp;
        self::$staticPropertyLimitings['$_onceErrorDispFlag'] = 'BreakpointDebugging/UnitTestOverriding.php';
        self::$staticPropertyLimitings['$_callingExceptionHandlerDirectly'] = array (
            'BreakpointDebugging/Error.php',
            'BreakpointDebugging/UnitTestOverriding.php'
        );
        self::$staticPropertyLimitings['$_valuesToTrace'] = ''; // For unit test.
        self::$staticPropertyLimitings['$exeMode'] = ''; // For unit test.
    }

    /**
     * If "Apache HTTP Server" does not support "suEXEC", this method displays security warning.
     *
     * @return void
     */
    static function checkSuperUserExecution()
    {
        // If this is not remote debug.
        if (self::$exeMode & ~B::REMOTE_DEBUG) {
            return;
        }
        if (B::getStatic('$_os') !== 'WIN' && trim(`echo \$USER`) === 'root'
        ) {
            echo '<pre>Security warning: Recommends to change to "Apache HTTP Server" which Supported "suEXEC" because this "Apache HTTP Server" is executed by "root" user.</pre>';
        }
    }

    /**
     * For debug.
     *
     * @param string $propertyName Same as parent.
     *
     * @return Same as parent.
     */
    static function getStatic($propertyName)
    {
        self::assert(func_num_args() === 1, 1);
        self::assert(is_string($propertyName), 2);

        return parent::getStatic($propertyName);
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
        self::limitAccess(self::$staticPropertyLimitings[$propertyName]);

        self::assert(func_num_args() === 1, 1);
        self::assert(is_string($propertyName), 2);

        return parent::refStatic($propertyName);
    }

    /**
     * For debug.
     *
     * @return Same as parent.
     */
    static function getXebugExists()
    {
        self::assert(func_num_args() === 0);

        return parent::getXebugExists();
    }

    /**
     * For debug.
     *
     * @param bool $value Same as parent.
     *
     * @return Same as parent.
     */
    static function setXebugExists($value)
    {
        self::limitAccess('BreakpointDebugging.php');

        self::assert(func_num_args() === 1, 1);
        self::assert(is_bool($value), 2);

        parent::setXebugExists($value);
    }

    /**
     * For debug.
     *
     * @param string $phpIniVariable Same as parent.
     * @param mixed  $cmpValue       Same as parent.
     * @param string $errorMessage   Same as parent.
     *
     * @return Same as parent.
     */
    static function iniCheck($phpIniVariable, $cmpValue, $errorMessage)
    {
        self::assert(func_num_args() === 3, 1);
        self::assert(is_string($phpIniVariable), 2);
        self::assert(is_string($cmpValue) || is_array($cmpValue), 3);
        self::assert(is_string($errorMessage), 4);

        parent::iniCheck($phpIniVariable, $cmpValue, $errorMessage);
    }

    /**
     * For debug.
     *
     * @param string $string Same as parent.
     *
     * @return Same as parent.
     */
    static function convertMbString($string)
    {
        self::assert(func_num_args() === 1, 1);
        self::assert(is_string($string), 2);

        return parent::convertMbString($string);
    }

    /**
     * For debug.
     *
     * @param string $name       Same as parent.
     * @param int    $permission Same as parent.
     *
     * @return Same as parent.
     */
    protected static function setOwner($name, $permission)
    {
        self::assert(func_num_args() === 2, 1);
        self::assert(is_string($name), 2);
        self::assert(is_int($permission), 3);

        parent::setOwner($name, $permission);
    }

    /**
     * For debug.
     *
     * @param stirng $dirName    Same as parent.
     * @param int    $permission Same as parent.
     *
     * @return Same as parent.
     */
    static function mkdir($dirName, $permission = 0777)
    {
        self::assert(func_num_args() <= 2, 1);
        self::assert(is_string($dirName), 2);
        self::assert(is_int($permission), 3);

        parent::mkdir($dirName, $permission);
    }

    /**
     * For debug.
     *
     * @param stirng $fileName   Same as parent.
     * @param stirng $mode       Same as parent.
     * @param int    $permission Same as parent.
     *
     * @return Same as parent.
     */
    static function fopen($fileName, $mode, $permission)
    {
        self::assert(func_num_args() === 3, 1);
        self::assert(is_string($fileName), 2);
        self::assert(is_string($mode), 3);
        self::assert(is_int($permission) && 0 <= $permission && $permission <= 0777, 4);

        return parent::fopen($fileName, $mode, $permission);
    }

    /**
     * For debug.
     *
     * @param array $intArray Same as parent.
     *
     * @return Same as parent.
     */
    static function compressIntArray($intArray)
    {
        self::assert(func_num_args() === 1, 1);
        self::assert(is_array($intArray), 2);

        return parent::compressIntArray($intArray);
    }

    /**
     * For debug.
     *
     * @param mixed $compressBytes Same as parent.
     *
     * @return Same as parent.
     */
    static function decompressIntArray($compressBytes)
    {
        self::assert(func_num_args() === 1, 1);
        self::assert(is_string($compressBytes) || $compressBytes === false, 2);

        return parent::decompressIntArray($compressBytes);
    }

    /**
     * For debug.
     *
     * @param object $pException Same as parent.
     *
     * @return Same as parent.
     */
    static function handleException($pException)
    {
        self::assert(func_num_args() === 1, 1);
        self::assert($pException instanceof \Exception, 2);

        if (self::$exeMode & self::UNIT_TEST) {
            $callStack = $pException->getTrace();
            $call = array_key_exists(0, $callStack) ? $callStack[0] : array ();
            // In case of direct call from "BreakpointDebugging_InAllCase::callExceptionHandlerDirectly()".
            if ((array_key_exists('class', $call) && $call['class'] === 'BreakpointDebugging_InAllCase')
                && (array_key_exists('function', $call) && $call['function'] === 'callExceptionHandlerDirectly')
            ) {
                throw $pException;
                // @codeCoverageIgnoreStart
            }
            // @codeCoverageIgnoreEnd
        }

        parent::handleException($pException);
    }

    /**
     * For debug.
     *
     * @param int    $errorNumber  Same as parent.
     * @param string $errorMessage Same as parent.
     *
     * @return Same as parent.
     */
    static function handleError($errorNumber, $errorMessage)
    {
        self::assert(is_int($errorNumber), 2);
        self::assert(is_string($errorMessage), 3);

        parent::handleError($errorNumber, $errorMessage);

        return true;
    }

    ///////////////////////////// For package user. /////////////////////////////
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

        if (self::$exeMode & B::IGNORING_BREAK_POINT) {
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

        if (self::$exeMode & self::REMOTE_DEBUG) {
            // @codeCoverageIgnoreStart
            // Remote debug must end immediately to avoid eternal execution.
            exit;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Checks a invoker file path.
     *
     * @param array  $includePaths    The including paths.
     * @param string $invokerFilePath Invoker file path.
     * @param string $fullFilePath    A full file path.
     * @param string $os              Kind of OS.
     *
     * @return boolean
     */
    private static function _checkInvokerFilePath($includePaths, $invokerFilePath, $fullFilePath, $os)
    {
        B::assert(func_num_args() === 4, 1);
        B::assert(is_array($includePaths), 2);
        B::assert(is_string($invokerFilePath), 3);
        B::assert(is_string($fullFilePath), 4);
        B::assert(is_string($os), 5);

        foreach ($includePaths as $includePath) {
            $invokerFullFilePath = realpath("$includePath/$invokerFilePath");
            if ($invokerFullFilePath === false) {
                continue;
            }
            if ($os === 'WIN') {
                $invokerFullFilePath = strtolower($invokerFullFilePath);
            }
            if ($fullFilePath === $invokerFullFilePath) {
                return true;
            }
        }
        return false;
    }

    /**
     * Limits the invoker file paths.
     *
     * @param mixed $invokerFilePaths Invoker file paths.
     * @param bool  $enableUnitTest   Is this enable in unit test?
     *
     * @return void
     */
    static function limitAccess($invokerFilePaths, $enableUnitTest = false)
    {
        static $invokingLocations = array ();

        $callStack = debug_backtrace();
        // Makes invoking location information.
        $count = count($callStack);
        for ($key = 1; $key < $count; $key++) {
            if (array_key_exists('file', $callStack[$key])) {
                break;
            }
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        $fullFilePath = $callStack[$key]['file'];
        $os = strtoupper(substr(PHP_OS, 0, 3));
        if ($os === 'WIN') {
            $fullFilePath = strtolower($fullFilePath);
        }
        $line = $callStack[$key]['line'];
        if (array_key_exists($fullFilePath, $invokingLocations) && array_key_exists($line, $invokingLocations[$fullFilePath])
        ) {
            // Skips same.
            return true;
        }
        // Stores the invoking location information.
        $invokingLocations[$fullFilePath][$line] = true;

        self::assert(func_num_args() <= 2, 1);
        self::assert(is_array($invokerFilePaths) || is_string($invokerFilePaths), 2);
        self::assert(is_bool($enableUnitTest), 3);

        if (!$enableUnitTest && self::$exeMode & self::UNIT_TEST && (!isset(self::$_unitTestDir)
            || strpos($fullFilePath, self::$_unitTestDir) === 0)
        ) {
            return;
        }

        if ($os === 'WIN') {
            $fullFilePath = strtolower($fullFilePath);
        }
        if (!isset(self::$_includePaths)) {
            self::$_includePaths = ini_get('include_path');
            if ($os === 'WIN') {
                self::$_includePaths = strtolower(self::$_includePaths);
            }
            self::$_includePaths = explode(PATH_SEPARATOR, self::$_includePaths);
        }
        if (is_array($invokerFilePaths)) {
            foreach ($invokerFilePaths as $invokerFilePath) {
                if (self::_checkInvokerFilePath(self::$_includePaths, $invokerFilePath, $fullFilePath, $os)) {
                    return;
                }
            }
            // @codeCoverageIgnoreStart
        } else {
            // @codeCoverageIgnoreEnd
            if (self::_checkInvokerFilePath(self::$_includePaths, $invokerFilePaths, $fullFilePath, $os)) {
                return;
            }
        }
        $class = '';
        $function = '';
        if (array_key_exists('class', $callStack[$key])) {
            $class = $callStack[$key]['class'] . '::';
        }
        if (array_key_exists('function', $callStack[$key])) {
            $function = $callStack[$key]['function'];
        }
        self::callExceptionHandlerDirectly("'$class$function()' must not invoke in '$fullFilePath' file.", 4);
        // @codeCoverageIgnoreStart
    }

    // @codeCoverageIgnoreEnd
    /**
     * Throws exception if assertion is false. Also, has identification code for unit test.
     *
     * @param bool $assertion Assertion.
     * @param int  $id        Exception identification number inside function.
     *
     * @return void
     * @usage
     *      \BreakpointDebugging::assert(<judgment expression>, <identification number inside function>);
     *      It is possible to assert that <judgment expression> is "This must be". Especially, this uses to verify a function's argument.
     *      For example: \BreakpointDebugging::assert(3 <= $value && $value <= 5); // $value should be 3-5.
     *      Caution: Don't change the value of variable in "\BreakpointDebugging::assert()" function because there isn't executed in case of release.
     */
    static function assert($assertion, $id = 0)
    {
        if (func_num_args() > 2) {
            self::callExceptionHandlerDirectly('Parameter number mistake.', 1);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!is_bool($assertion)) {
            self::callExceptionHandlerDirectly('Assertion must be bool.', 2);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
        if (!is_int($id)) {
            self::callExceptionHandlerDirectly('Exception identification number must be integer.', 3);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd

        if (!$assertion) {
            self::callExceptionHandlerDirectly('Assertion failed.', $id);
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * This changes a character sets to display a multibyte character string with local window of debugger, and this returns it.
     *
     * @return array Some changed variables.
     *
     * @example $gDebugValue = \BreakpointDebugging::convertMbStringForDebug('SJIS', $scalar1, $array2, $scalar2);
     */
    static function convertMbStringForDebug()
    {
        // In case of local.
        if (self::$exeMode & (self::LOCAL_DEBUG | self::LOCAL_DEBUG_OF_RELEASE)) {
            // Character set string to want to display, and some variables.
            $mbStringArray = func_get_args();
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
        self::assert(func_num_args() === 2, 1);
        self::assert(is_string($charSet), 2);
        self::assert(is_array($mbParamArray), 3);

        $displayMbStringArray = array ();
        $count = 0;
        foreach ($mbParamArray as $mbString) {
            if (is_array($mbString)) {
                $displayMbStringArray[$count] = self::_convertMbStringForDebugSubroop($charSet, $mbString);
            } else if (is_string($mbString)) {
                $displayMbStringArray[$count] = mb_convert_encoding($mbString, $charSet, 'auto');
            } else {
                $displayMbStringArray[$count] = $mbString;
            }
            $count++;
        }
        return $displayMbStringArray;
    }

    /**
     * "ini_set()" with validation except for release mode.
     * Sets with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
     *
     * @param string $phpIniVariable "php.ini" variable.
     * @param string $setValue       Value of variable.
     * @param bool   $doCheck        Does this class method check to copy to the release file?
     *
     * @return void
     */
    static function iniSet($phpIniVariable, $setValue, $doCheck = true)
    {
        self::assert(func_num_args() <= 3, 1);
        self::assert($phpIniVariable !== 'error_log', 2);
        self::assert(is_string($phpIniVariable), 3);
        self::assert(is_string($setValue), 4);
        self::assert(is_bool($doCheck), 5);

        $getValue = ini_get($phpIniVariable);
        if ($setValue !== $getValue) {
            // In case of remote.
            if ($doCheck === true && (self::$exeMode & self::REMOTE_DEBUG)
            ) {
                $backTrace = debug_backtrace();
                $baseName = basename($backTrace[0]['file']);
                $cmpName = '_MySetting_Option.php';
                if (B::getStatic('$_os') === 'WIN') {
                    $baseName = strtolower($baseName);
                    $cmpName = strtolower($cmpName);
                }
                $cmpNameLength = strlen($cmpName);
                if (!substr_compare($baseName, $cmpName, 0 - $cmpNameLength, $cmpNameLength, true)) {
                    // @codeCoverageIgnoreStart
                    $notExistFlag = true;
                    foreach (self::$_onceFlag as $cmpName) {
                        if (!strcmp($baseName, $cmpName)) {
                            $notExistFlag = false;
                            break;
                        }
                    }
                    if ($notExistFlag) {
                        self::$_onceFlag[] = $baseName;
                        $packageName = substr($baseName, 0, 0 - $cmpNameLength);
                        echo <<<EOD
<pre>
### "\BreakpointDebugging::iniSet()": You must copy from "./{$packageName}_MySetting_Option.php" to user place folder of "./{$packageName}_MySetting.php" for release because set value and value of php.ini differ.
### Also, if remote "php.ini" was changed, you must redo "B::REMOTE_DEBUG" mode.
</pre>
EOD;
                    }
                    echo <<<EOD
<pre>
	file: {$backTrace[0]['file']}
	line: {$backTrace[0]['line']}
</pre>
EOD;
                }
                // @codeCoverageIgnoreEnd
            }
            if (ini_set($phpIniVariable, $setValue) === false) {
                throw new \BreakpointDebugging_ErrorException('"ini_set()" failed.', 6);
            }
        }
    }

    /**
     * Gets property for test.
     *
     * @param mixed  $objectOrClassName A object or class name.
     * @param string $propertyName      Property name or constant name.
     *
     * @return mixed Property value.
     *
     * @example $propertyValue = \BreakpointDebugging::getPropertyForTest('ClassName', 'CONST_NAME');
     *          $propertyValue = \BreakpointDebugging::getPropertyForTest('ClassName', '$_privateStaticName');
     *          $propertyValue = \BreakpointDebugging::getPropertyForTest($object, '$_privateStaticName');
     *          $propertyValue = \BreakpointDebugging::getPropertyForTest($object, '$_privateAutoName');
     */
    static function getPropertyForTest($objectOrClassName, $propertyName)
    {
        self::assert(func_num_args() === 2, 1);
        self::assert(is_string($propertyName), 2);

        if (is_object($objectOrClassName)) {
            $className = get_class($objectOrClassName);
        } else if (is_string($objectOrClassName)) {
            $className = $objectOrClassName;
        } else {
            throw new \BreakpointDebugging_ErrorException('Parameter1 must be object or string.', 3);
        }
        $classReflection = new \ReflectionClass($className);
        $propertyReflections = $classReflection->getProperties();
        foreach ($propertyReflections as $propertyReflection) {
            $propertyReflection->setAccessible(true);
            $paramName = '$' . $propertyReflection->getName();
            if ($paramName !== $propertyName) {
                continue;
            }
            if ($propertyReflection->isStatic()) {
                return $propertyReflection->getValue($propertyReflection);
            } else {
                return $propertyReflection->getValue($objectOrClassName);
            }
        }
        $constants = $classReflection->getConstants();
        foreach ($constants as $constName => $constValue) {
            if ($constName !== $propertyName) {
                continue;
            }
            return $constValue;
        }
        throw new \BreakpointDebugging_ErrorException('"' . $className . '::' . $propertyName . '" property does not exist.', 4);
    }

    /**
     * Sets property for test.
     *
     * @param mixed  $objectOrClassName A object or class name.
     * @param string $propertyName      Property name or constant name.
     * @param mixed  $value             A value to set.
     *
     * @return void
     *
     * @example \BreakpointDebugging::setPropertyForTest('ClassName', '$_privateStaticName', $value);
     *          \BreakpointDebugging::setPropertyForTest($object, '$_privateStaticName', $value);
     *          \BreakpointDebugging::setPropertyForTest($object, '$_privateAutoName', $value);
     */
    static function setPropertyForTest($objectOrClassName, $propertyName, $value)
    {
        self::assert(func_num_args() === 3, 1);
        self::assert(is_string($propertyName), 2);

        if (is_object($objectOrClassName)) {
            $className = get_class($objectOrClassName);
        } else if (is_string($objectOrClassName)) {
            $className = $objectOrClassName;
        } else {
            throw new \BreakpointDebugging_ErrorException('Parameter1 must be object or string.', 3);
        }
        $classReflection = new \ReflectionClass($className);
        $propertyReflections = $classReflection->getProperties();
        foreach ($propertyReflections as $propertyReflection) {
            $propertyReflection->setAccessible(true);
            $paramName = '$' . $propertyReflection->getName();
            if ($paramName !== $propertyName) {
                continue;
            }
            if ($propertyReflection->isStatic()) {
                $propertyReflection->setValue($propertyReflection, $value);
                return;
            } else {
                $propertyReflection->setValue($objectOrClassName, $value);
                return;
            }
        }
        throw new \BreakpointDebugging_ErrorException('"' . $className . '::' . $propertyName . '" property does not exist.', 4);
    }

    /**
     * Checks unit-test-execution-mode, and sets unit test directory.
     *
     * @param bool $isUnitTest Is it unit test?
     *
     * @return mixed Unit test directory, or false.
     *
     * @example
     *      <?php
     *
     *      chdir(__DIR__ . '/../../');
     *      require_once './BreakpointDebugging_Including.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      B::isUnitTestExeMode(true);
     *
     *      class BreakpointDebuggingTest extends \BreakpointDebugging_UnitTestOverriding
     *      {
     *          .
     *          .
     *          .
     */
    static function isUnitTestExeMode($isUnitTest)
    {
        $isFalse = false;

        self::assert(func_num_args() === 1, 1);
        self::assert(is_bool($isUnitTest), 2);

        if (!isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1'
        ) { // In case of command or local host.
            if (self::$exeMode === (B::LOCAL_DEBUG | B::UNIT_TEST)) {
                $isFalse = !$isUnitTest;
            } else {
                // @codeCoverageIgnoreStart
                $isFalse = $isUnitTest;
                // @codeCoverageIgnoreEnd
            }
        } else {
            if (self::$exeMode === (B::REMOTE_DEBUG | B::UNIT_TEST)) {
                $isFalse = !$isUnitTest;
            } else {
                // @codeCoverageIgnoreStart
                $isFalse = $isUnitTest;
                // @codeCoverageIgnoreEnd
            }
        }
        if ($isUnitTest && $isFalse
        ) {
            // @codeCoverageIgnoreStart
            exit('<pre>You must set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>');
            // @codeCoverageIgnoreEnd
        } else if ($isFalse) {
            // @codeCoverageIgnoreStart
            exit('<pre>You must not set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>');
            // @codeCoverageIgnoreEnd
        }

        if (isset(self::$_unitTestDir)) {
            return false;
        }

        $unitTestCurrentDir = debug_backtrace();
        if (array_key_exists(1, $unitTestCurrentDir) && array_key_exists('class', $unitTestCurrentDir[1])
            && $unitTestCurrentDir[1]['class'] === 'BreakpointDebugging' && array_key_exists('function', $unitTestCurrentDir[1])
            && $unitTestCurrentDir[1]['function'] === 'executeUnitTest'
        ) { // Calling from "\BreakpointDebugging::executeUnitTest()".
            $unitTestCurrentDir = dirname($unitTestCurrentDir[1]['file']);
        } else { // In case of command.
            $unitTestCurrentDir = dirname($unitTestCurrentDir[0]['file']);
        }
        $unitTestCurrentDir .= DIRECTORY_SEPARATOR;
        if (B::getStatic('$_os') === 'WIN') { // In case of Windows.
            self::$_unitTestDir = strtolower($unitTestCurrentDir);
        } else { // In case of Unix.
            self::$_unitTestDir = $unitTestCurrentDir;
        }

        return self::$_unitTestDir;
    }

    /**
     * Executes unit test.
     *
     * ### Execution procedure ###
     * Procedure 1: Please, start a apache.
     * Procedure 2: Please, drop page like example page which executes unit tests to web browser.
     * Procedure 3: Please, rewrite web browser URL prefix to "localhost", and push return.
     *
     * Please, if you want remote execution, then upload "page like example page",
     * unit test files and following "PHPUnit" files, then execute with browser.
     *      PEAR/PHP/CodeCoverage.php
     *      PEAR/PHP/CodeCoverage/
     *          Copyright (c) 2009-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
     *      PEAR/PHP/Invoker.php
     *      PEAR/PHP/Invoker/
     *          Copyright (c) 2011-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
     *      PEAR/PHP/Timer.php
     *      PEAR/PHP/Timer/
     *          Copyright (c) 2010-2011 Sebastian Bergmann <sb@sebastian-bergmann.de>
     *      PEAR/PHP/Token.php
     *      PEAR/PHP/Token/
     *          Copyright (c) 2009-2012 Sebastian Bergmann <sb@sebastian-bergmann.de>
     *      PEAR/PHPUnit/
     *          Copyright (c) 2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
     *
     * @param array $unitTestCommands Commands of unit tests.
     *                                 Debugs its unit test file if array element is one.
     *                                 Does continuation unit tests if array element is more than one.
     *
     * @return void
     *
     * @Example page which executes unit tests.
     * <?php
     *
     * chdir(__DIR__ . '/../../');
     * require_once './BreakpointDebugging_Including.php';
     *
     * use \BreakpointDebugging as B;
     *
     * // Please, choose unit tests files by customizing.
     * // You must specify array element to one if you want step execution.
     * $unitTestCommands = array (
     *     '--stop-on-failure BreakpointDebuggingTest.php',
     *     '--stop-on-failure BreakpointDebugging/LockTest.php',
     * );
     * // Executes unit tests.
     * B::executeUnitTest($unitTestCommands);
     *
     * ?>
     *
     * @Example of unit test file.
     *      chdir(__DIR__ . '/../../');
     *
     *      require_once './BreakpointDebugging_Including.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      B::isUnitTestExeMode(true);
     *
     *      class SomethingTest extends \BreakpointDebugging_UnitTestOverriding
     *      {
     *          protected $pSomething;
     *
     *          protected function setUp()
     *          {
     *              // Executes the need setup always.
     *              parent::setUp();
     *              // Constructs instance.
     *              $this->pSomething = new \Something();
     *          }
     *
     *          protected function tearDown()
     *          {
     *              // Destructs instance.
     *              $this->pSomething = null;
     *          }
     *
     *          /*
     *           * @expectedException        \BreakpointDebugging_ErrorException
     *           * @expectedExceptionMessage CLASS=SomethingTest FUNCTION=testSomething_A ID=123
     *           *
     *          function testSomething_A()
     *          {
     *              throw new \BreakpointDebugging_ErrorException('Something message.', 123);
     *          }
     *
     *          function testSomething_B()
     *          {
     *              try {
     *                  B::assert(true, 1);
     *                  B::assert(false, 2);
     *              } catch (\BreakpointDebugging_ErrorException $e) {
     *                  $this->assertTrue(strripos($e->getMessage(), 'CLASS=SomethingTest FUNCTION=testSomething_B ID=2'));
     *                  return;
     *              }
     *              $this->fail();
     *          }
     *      }
     */
    static function executeUnitTest($unitTestCommands)
    {
        $unitTestCurrentDir = self::isUnitTestExeMode(true);

        self::assert(func_num_args() === 1, 1);
        self::assert(is_array($unitTestCommands), 2);
        self::assert(!empty($unitTestCommands), 3);

        if (count($unitTestCommands) === 1) {
            // @codeCoverageIgnoreStart
            $command = $unitTestCommands[0];
            $commandElements = explode(' ', $command);
            $testFileName = array_pop($commandElements);
            array_push($commandElements, "$unitTestCurrentDir$testFileName");
            array_unshift($commandElements, 'dummy');
            include_once 'PHPUnit/Autoload.php';
            $pPHPUnit_TextUI_Command = new \PHPUnit_TextUI_Command;
            echo "<pre>Starts Debugging of '$testFileName' file." . PHP_EOL;
            echo '//////////////////////////////////////////////////////////////////////////' . PHP_EOL;
            // Uses "PHPUnit" error handler.
            restore_error_handler();
            echo $pPHPUnit_TextUI_Command->run($commandElements, true);
        } else {
            // @codeCoverageIgnoreEnd
            if (self::$exeMode & (B::REMOTE_DEBUG | B::RELEASE)) {
                // @codeCoverageIgnoreStart
                exit('<pre>Executes on "local server only" because continuation unit test requires many load on remote server.</pre>');
                // @codeCoverageIgnoreEnd
            }
            // In case of extending test class except "\BreakpointDebugging_UnitTestOverriding" class.
            if (B::getStatic('$_os') === 'WIN') { // In case of Windows.
                $phpunit = 'phpunit.bat';
            } else { // In case of Unix.
                // Command execution path by "bash" differs because "Apache" is root user in case of default, therefore uses full path for command.
                while (true) {
                    $phpunit = `which phpunit`;
                    $phpunit = trim($phpunit);
                    if ($phpunit) {
                        break;
                    }

                    //$phpunit = `export PATH=/opt/lampp/bin:/opt/local/bin:/usr/bin:/usr/bin/X11:/usr/share/php;which phpunit`;
                    $userName = B::getStatic('$_userName');
                    $phpunit = `sudo -u $userName which phpunit`;

                    $phpunit = trim($phpunit);
                    if ($phpunit) {
                        break;
                    }
                    exit('<pre>"phpunit" command does not exist.</pre>');
                }
                if (!is_executable($phpunit)) {
                    exit('<pre>"phpunit" command is not executable. (' . $phpunit . ')</pre>');
                }
            }
            echo '<pre>Starts continuation unit tests.' . PHP_EOL;
            echo '//////////////////////////////////////////////////////////////////////////' . PHP_EOL;
            foreach ($unitTestCommands as $command) {
                $commandElements = explode(' ', $command);
                $testFileName = array_pop($commandElements);
                $commandOptions = implode(' ', $commandElements);
                // If test file name contains '_'.
                if (strpos($testFileName, '_') !== false) {
                    echo "You must change its array element and its file name into '-' because '$testFileName' contains '_'." . PHP_EOL;
                    if (self::getXebugExists()) {
                        xdebug_break();
                    }
                    return;
                }
                echo $testFileName . PHP_EOL;
                // Executes unit test command.
                echo `"$phpunit" $commandOptions "$unitTestCurrentDir$testFileName"`;
                echo '//////////////////////////////////////////////////////////////////////////' . PHP_EOL;
            }
            echo '</pre>';
        }
    }

    /**
     * Displays the code coverage report in browser.
     *
     * @param string $classFilePath Class file path.
     * @param string $browserPass   Browser pass to execute.
     * @param string $workDir       Working directory.
     *
     * @return void
     */
    private static function _displayCodeCoverageReport($classFilePath, $browserPass, $workDir)
    {
        self::assert(func_num_args() === 3, 1);
        self::assert(is_string($classFilePath), 2);
        self::assert(is_string($browserPass), 3);
        self::assert(is_string($workDir), 4);

        // If unit test.
        $callStack = debug_backtrace();
        if (array_key_exists(2, $callStack)
            && stripos($callStack[2]['function'], 'test') === 0
        ) {
            return;
        }
        // @codeCoverageIgnoreStart
        $classFilePath = str_replace(array ('/', '\\'), '_', $classFilePath);
        `"$browserPass" "file:///$workDir/CodeCoverageReport/$classFilePath.html"`;
    }

    // @codeCoverageIgnoreEnd
    /**
     * Makes up code coverage report, then displays in browser.
     *
     * @param string $unitTestFilePath Relative path of unit test file.
     * @param mixed  $classFilePaths   It is relative path of class which see the code coverage, and its current directory must be project directory.
     *
     * @return void
     * @example
     *      <?php
     *
     *      chdir(__DIR__ . '/../../');
     *      require_once './BreakpointDebugging_Including.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      // Makes up code coverage report, then displays in browser.
     *      B::displayCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php');
     *      B::displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php'));
     *          .
     *          .
     *          .
     */
    static function displayCodeCoverageReport($unitTestFilePath, $classFilePaths)
    {
        self::assert(func_num_args() === 2, 1);
        self::assert(is_string($unitTestFilePath), 2);
        self::assert(is_string($classFilePaths) || is_array($classFilePaths), 3);

        $unitTestCurrentDir = debug_backtrace();
        $unitTestCurrentDir = dirname($unitTestCurrentDir[0]['file']);
        $workDir = B::getStatic('$_workDir');
        echo '<pre>' . `phpunit --coverage-html "$workDir/CodeCoverageReport" "$unitTestCurrentDir/$unitTestFilePath"` . '</pre>';

        $browserPass = self::$_browserPass;
        if (is_array($classFilePaths)) {
            foreach ($classFilePaths as $classFilePath) {
                self::_displayCodeCoverageReport($classFilePath, $browserPass, $workDir);
            }
        } else {
            self::_displayCodeCoverageReport($classFilePaths, $browserPass, $workDir);
        }
    }

    /**
     * Executes function by parameter array, then displays executed function line, file, parameters and results.
     * Does not exist in case of release because this method uses for a function verification display.
     *
     * @param string $functionName Function name.
     * @param array  $params       Parameter array.
     *
     * @return Executed function result.
     *
     * @example $return = \BreakpointDebugging::displayVerification('function_name', func_get_args());
     *          $return = \BreakpointDebugging::displayVerification('function_name', array($object, $resource, &$reference));
     */
    static function displayVerification($functionName, $params)
    {
        self::assert(func_num_args() === 2, 1);
        self::assert(is_string($functionName), 2);
        self::assert(is_array($params), 3);

        self::$tmp = $params;
        $paramNumber = count($params);
        $propertyNameToSend = '\BreakpointDebugging::$tmp';
        $callStackInfo = debug_backtrace();
        echo '<pre><b>Executed function information.</b></br></br>';
        echo "<b>FILE</b> = {$callStackInfo[0]['file']}</br>";
        echo "<b>LINE</b> = {$callStackInfo[0]['line']}</br>";
        echo '<b>NAME</b> = ' . $functionName . '(';
        $paramString = array ();
        for ($count = 0; $count < $paramNumber; $count++) {
            $paramString[] = $propertyNameToSend . '[' . $count . ']';
            var_dump($params[$count]);
        }
        echo ')';
        $code = $functionName . '(' . implode(',', $paramString) . ')';
        $return = eval('$return = ' . $code . '; echo "<br/><b>RETURN</b> = "; var_dump($return); return $return;');
        echo '//////////////////////////////////////////////////////////////////////////////////////';
        return $return;
    }

}

// When "Xdebug" does not exist.
if (!B::getXebugExists()) {
    if (B::getStatic('$exeMode') & (B::LOCAL_DEBUG | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of local host.
        exit(
            '<pre>' .
            '### ERROR ###' . PHP_EOL .
            'FILE: ' . __FILE__ . ' LINE: ' . __LINE__ . PHP_EOL .
            '"Xdebug" extension has been not loaded though this is a local host.' . PHP_EOL .
            '"Xdebug" extension is required because (uses breakpoint, displays for fatal error and avoids infinity recursive function call).' .
            '</pre>'
        );
    }
}

register_shutdown_function('\BreakpointDebugging::checkSuperUserExecution');

?>
