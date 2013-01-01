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
 * "WindowsXP Professional" + "NetBeans IDE 7.1.2" + "XAMPP 1.7.3" or
 * "Ubuntu desktop" + "NetBeans IDE 7.1.2" + "XAMPP for Linux 1.7.3a".
 * Do not use version greater than "XAMPP 1.7.3" for "NetBeans IDE 7.1.2"
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
 * Procedure 2: If you want remote debug, please set "xdebug.remote_host =
 *      "<name or ip of host which debugger exists>"" into "php.ini" file.
 * Procedure 3: Please, set *.php file format to utf8, but we should create backup of
 *      php files because multibyte strings may be destroyed.
 * Procedure 4: Please, copy BreakpointDebugging_MySetting*.php to "./PEAR_Setting/"
 *      of your project directory.
 * Procedure 5: Please, edit BreakpointDebugging_MySetting*.php for customize.
 *      Then, it fixes part setting about all debugging modes.
 * Procedure 6: Please, copy following in your project php code.
 *      require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';
 * Procedure 7: Please, set debugging mode to "$_BreakpointDebugging_EXE_MODE" into
 *      "./PEAR_Setting/BreakpointDebugging_MySetting.php".
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
 *      "static $isRegister; \BreakpointDebugging::registerNotFixedLocation($isRegister);"
 *      Then, we can discern function or method or file
 *      which has been not fixed with browser screen or log.
 * Option procedure: Please, register local variable or global variable
 *      which you want to see with "\BreakpointDebugging::addValuesToTrace()".
 *
 * ### The debugging mode which we can use. ###
 * First "LOCAL_DEBUG" mode is breakpoint debugging with local server.
 *      We can use breakpoint.
 * Second "LOCAL_DEBUG_OF_RELEASE" mode is breakpoint debugging
 *      to emulate release with local server.
 *      We can use breakpoint.
 * Third "REMOTE_DEBUG" mode is browser display debugging with remote server.
 *      We can use breakpoint, if remote server supports.
 * Last "RELEASE" mode is logging-debug with remote server,
 *      and we must set on last for security.
 *      We cannot use breakpoint.
 * "LOCAL_DEBUG_OF_RELEASE | UNIT_TEST" mode tests by "phpunit" command with local server.
 * "RELEASE | UNIT_TEST" mode tests by "phpunit" command with remote server,
 *      if remote server supports.
 *      This does logging same as "RELEASE" mode,
 *      but enables "Xdebug" which displays fatal error.
 *  ### Exception hierarchical structure ###
 *  PEAR_Exception
 *      BreakpointDebugging_Exception
 *          BreakpointDebugging_ErrorException
 *
 * ### Useful function index. ###
 * This registers as function or method being not fixed.
 *      \BreakpointDebugging::registerNotFixedLocation(&$isRegister)
 * Add values to trace.
 *      \BreakpointDebugging::addValuesToTrace($values)
 * This writes inside of "catch()", then display logging or log.
 *      \BreakpointDebugging::$prependExceptionLog
 *      \BreakpointDebugging::exceptionHandler($exception)
 * This changes to unify multibyte character strings such as system-output in UTF8,
 * and this returns.
 *      \BreakpointDebugging::convertMbString($string)
 * This changes a character sets to display a multibyte character string
 * with local window of debugger, and this returns it. (Debug only.)
 *      \BreakpointDebugging::convertMbStringForDebug($params)
 * This is ini_set() with validation except for release mode.
 * I set with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
 *      \BreakpointDebugging::iniSet($phpIniVariable, $setValue, $doCheck = true)
 * This checks php.ini setting.
 *      \BreakpointDebugging::iniCheck($phpIniVariable, $cmpValue, $errorMessage)
 * Gets property for test. (Unit test only.)
 *      \BreakpointDebugging::getPropertyForTest($objectOrClassName, $propertyName)
 * Sets property for test. (Unit test only.)
 *      \BreakpointDebugging::setPropertyForTest($objectOrClassName, $propertyName, $value)
 * Checks unit-test-execution-mode. (Unit test only.)
 *      \BreakpointDebugging::checkUnitTestExeMode()
 * Executes unit test. (Unit test only.)
 *      \BreakpointDebugging::executeUnitTest($testFileNames, $currentDir)
 * "mkdir" method which sets permission and sets own user to owner.
 *      \BreakpointDebugging::mkdir($dirName, $permission = 0777)
 * "fopen" method which sets the file mode, permission and sets own user to owner.
 *      \BreakpointDebugging::fopen($fileName, $mode, $permission)
 * Executes function by parameter array, then displays executed function line, file, parameters and results. (Debug only.)
 *      \BreakpointDebugging::displayVerification($functionName, $params)
 * Compresses integer array.
 *      \BreakpointDebugging::compressIntArray($intArray)
 * Decompresses to integer array.
 *      \BreakpointDebugging::decompressIntArray($compressBytes)
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
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

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
    private $_onceFlag = array ();

    /**
     * @var array Temporary parameter array.
     */
    public $tmpParams;

//    /**
//     * @var string Unit test mode.
//     */
//    static $unitTestMode;

    /**
     * @var string Browser execution pass.
     */
    static $browserPass = '"C:\Program Files\Mozilla Firefox\firefox.exe"';

    /**
     * This constructer create object only one time.
     *
     * @return void
     */
    function __construct()
    {
        parent::__construct();

        static $createOnlyOneTime = false;

        self::assert($createOnlyOneTime === false, 1);
        $createOnlyOneTime = true;
    }

    /**
     * If "Apache HTTP Server" does not support "suEXEC", this method displays security warning.
     */
    function __destruct()
    {
        global $_BreakpointDebugging_EXE_MODE;

        // If this is not remote debug.
        if ($_BreakpointDebugging_EXE_MODE & ~B::REMOTE_DEBUG) {
            return;
        }
        if (trim(`echo \$USER`) === 'root') {
            echo 'Security warning: Recommends to change to "Apache HTTP Server" which Supported "suEXEC" because this "Apache HTTP Server" is executed by "root" user.<br/>';
        }
    }

    /**
     * Debugs by using breakpoint.
     * We must define this class method outside namespace, and we must not change method name when we call this method.
     *
     * @param string $message        Message.
     * @param array  $callStackInfo A call stack info.
     *
     * @return void
     * @example B::breakpoint('Error message.', debug_backtrace());
     */
    static function breakpoint($message, $callStackInfo)
    {
        global $_BreakpointDebugging_EXE_MODE;

        B::internalAssert(func_num_args() === 2, 1);
        B::internalAssert(is_string($message), 2);
        B::internalAssert(is_array($callStackInfo), 3);

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

        if (B::$xdebugExists) {
            xdebug_break(); // Breakpoint. See local variable value by doing step execution here.
            // Push stop button if is thought error message.
        }

        if ($_BreakpointDebugging_EXE_MODE & self::REMOTE_DEBUG) {
            // Remote debug must end immediately to avoid eternal execution.
            exit;
        }
    }

    /**
     * Changes execution mode for purpose which displays unit test error.
     *
     * @return Original execution mode value.
     */
    protected static function changeExecutionModeForUnitTest()
    {
        global $_BreakpointDebugging_EXE_MODE;

        $original = $_BreakpointDebugging_EXE_MODE;
        if ($_BreakpointDebugging_EXE_MODE === (B::LOCAL_DEBUG_OF_RELEASE | B::UNIT_TEST)) {
            $_BreakpointDebugging_EXE_MODE = B::LOCAL_DEBUG;
        } else if ($_BreakpointDebugging_EXE_MODE === (B::RELEASE | B::UNIT_TEST)) {
            $_BreakpointDebugging_EXE_MODE = B::REMOTE_DEBUG;
        }
        return $original;
    }

    /**
     * Throws exception if assertion is false. Also, has identification code for unit test.
     *
     * @param type $assertion Assertion.
     * @param type $id        Identification number inside function.
     *
     * @usage
     *      \BreakpointDebugging::assert(<judgment expression>, <identification number inside function>);
     *      It is possible to assert that <judgment expression> is "This must be". Especially, this uses to verify a function's argument.
     *      For example: \BreakpointDebugging::assert(3 <= $value && $value <= 5); // $value should be 3-5.
     *      Caution: Don't change the value of variable in "\BreakpointDebugging::assert()" function because there isn't executed in case of release.
     */
    static function assert($assertion, $id = 0)
    {
        if (!is_bool($assertion)) {
            throw new \BreakpointDebugging_ErrorException('First parameter mistake.', 1);
        }
        if (!is_int($id)) {
            throw new \BreakpointDebugging_ErrorException('Second parameter mistake.', 2);
        }

        if (!$assertion) {
            self::internal('Assertion failed.', $id);
        }
    }

    /**
     * Asserts inside global error handling or global exception handling. (For this package developer).
     *
     * @param bool $expression Judgment expression.
     * @param int  $id         Exception identification number.
     *
     * @return void
     * @example \BreakpointDebugging::internalAssert($expression, 1);
     */
    static function internalAssert($expression, $id)
    {
        if (func_num_args() !== 2 || !is_bool($expression) || $expression === false || !is_int($id)) {
            self::internal('Assertion failed.', $id);
        }
    }

    /**
     * This changes a character sets to display a multibyte character string with local window of debugger, and this returns it.
     *
     * @param array $params Character set string to want to display, and Some variables.
     *
     * @return array Some changed variables.
     *
     * @example $gDebugValue = \BreakpointDebugging::convertMbStringForDebug('SJIS', $scalar1, $array2, $scalar2);
     */
    static function convertMbStringForDebug($params)
    {
        global $_BreakpointDebugging_EXE_MODE;

        // In case of local.
        if ($_BreakpointDebugging_EXE_MODE & (self::LOCAL_DEBUG | self::LOCAL_DEBUG_OF_RELEASE)) {
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
        global $_BreakpointDebugging_EXE_MODE;

        // In case of local.
        if ($_BreakpointDebugging_EXE_MODE & (self::LOCAL_DEBUG | self::LOCAL_DEBUG_OF_RELEASE)) {
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
        global $_BreakpointDebugging_EXE_MODE, $_BreakpointDebugging;
        self::assert(func_num_args() <= 3, 1);
        self::assert($phpIniVariable !== 'error_log', 2);
        self::assert(is_string($setValue), 3);

        $getValue = ini_get($phpIniVariable);
        if ($setValue !== $getValue) {
            // In case of remote.
            if ($doCheck === true && ($_BreakpointDebugging_EXE_MODE & self::REMOTE_DEBUG)) {
                $backTrace = debug_backtrace();
                $baseName = basename($backTrace[0]['file']);
                $cmpName = '_MySetting_Option.php';
                if (B::$os === 'WIN') {
                    $baseName = strtolower($baseName);
                    $cmpName = strtolower($cmpName);
                }
                $cmpNameLength = strlen($cmpName);
                if (!substr_compare($baseName, $cmpName, 0 - $cmpNameLength, $cmpNameLength, true)) {
                    $notExistFlag = true;
                    foreach ($_BreakpointDebugging->_onceFlag as $cmpName) {
                        if (!strcmp($baseName, $cmpName)) {
                            $notExistFlag = false;
                            break;
                        }
                    }
                    if ($notExistFlag) {
                        $_BreakpointDebugging->_onceFlag[] = $baseName;
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
            }
            if (ini_set($phpIniVariable, $setValue) === false) {
                throw new \BreakpointDebugging_ErrorException('"ini_set()" failed.', 4);
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
        if (is_object($objectOrClassName)) {
            $className = get_class($objectOrClassName);
        } else {
            $className = $objectOrClassName;
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
        throw new \BreakpointDebugging_ErrorException('"' . $className . '::' . $propertyName . '" property does not exist.');
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
        if (is_object($objectOrClassName)) {
            $className = get_class($objectOrClassName);
        } else {
            $className = $objectOrClassName;
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
        throw new \BreakpointDebugging_ErrorException('"' . $className . '::' . $propertyName . '" property does not exist.');
    }

    /**
     * Checks unit-test-execution-mode.
     *
     * @return void
     *
     * @example
     *      <?php
     *      chdir(__DIR__ . '/../../');
     *      require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';
     *      use \BreakpointDebugging as B;
     *      B::checkUnitTestExeMode();
     *      class BreakpointDebuggingTest extends \BreakpointDebugging_UnitTestOverriding
     *      {
     *          .
     *          .
     *          .
     */
    static function checkUnitTestExeMode()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (!isset($_SERVER['SERVER_ADDR']) || $_SERVER['SERVER_ADDR'] === '127.0.0.1') { // In case of command or local host.
            if ($_BreakpointDebugging_EXE_MODE === (B::LOCAL_DEBUG_OF_RELEASE | B::UNIT_TEST)) {
                return;
            }
        } else {
            if ($_BreakpointDebugging_EXE_MODE === (B::RELEASE | B::UNIT_TEST)) {
                return;
            }
        }
        exit('<pre>You must set "$_BreakpointDebugging_EXE_MODE = $setExecutionMode(\'UNIT_TEST\');" into "./PEAR_Setting/BreakpointDebugging_MySetting.php".</pre>');
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
     * @param array  $unitTestCommands Commands of unit tests.
     *                                 Debugs its unit test file if array element is one.
     *                                 Does continuation unit tests if array element is more than one.
     * @param string $currentDir       Unit test current directory.
     *
     * @return void
     *
     * @Example page which executes unit tests.
     * <?php
     * chdir(__DIR__ . '/../../');
     * require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';
     * use \BreakpointDebugging as B;
     * // Please, choose unit tests files by customizing.
     * $unitTestCommands = array (
     *     '--stop-on-failure BreakpointDebuggingTest.php',
     *     '--stop-on-failure BreakpointDebugging/LockTest.php',
     * );
     * // Executes unit tests.
     * B::executeUnitTest($unitTestCommands, __DIR__);
     * ?>
     *
     * @Example of unit test file.
     *      use \BreakpointDebugging as B;
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
     *                  $this->assertTrue(strpos($e->getMessage(), 'CLASS=SomethingTest FUNCTION=testSomething_B ID=2'));
     *                  return;
     *              }
     *              $this->fail();
     *          }
     *
     *      }
     */
    static function executeUnitTest($unitTestCommands, $currentDir)
    {
        global $_BreakpointDebugging_EXE_MODE;

        self::checkUnitTestExeMode();

        self::assert(func_num_args() === 2, 3);
        self::assert(is_array($unitTestCommands), 1);
        self::assert(is_string($currentDir), 2);

        //B::$unitTestMode = count($unitTestCommands) === 1 ? 'DEBUG' : 'ALL';
        //if (B::$unitTestMode === 'DEBUG') {
        if (count($unitTestCommands) === 1) {
            $command = $unitTestCommands[0];
            $commandElements = explode(' ', $command);
            $testFileName = array_pop($commandElements);
            array_push($commandElements, "$currentDir/$testFileName");
            array_unshift($commandElements, 'dummy');
            include_once 'PHPUnit/Autoload.php';
            $pPHPUnit_TextUI_Command = new \PHPUnit_TextUI_Command;
            echo "<pre>Starts Debugging of '$testFileName' file." . PHP_EOL;
            echo '//////////////////////////////////////////////////////////////////////////' . PHP_EOL;
            // Uses "PHPUnit" error handler.
            restore_error_handler();
            echo $pPHPUnit_TextUI_Command->run($commandElements, true);
            //} else if (B::$unitTestMode === 'ALL') {
        } else {
            if ($_BreakpointDebugging_EXE_MODE & (B::REMOTE_DEBUG | B::RELEASE)) {
                exit('<pre>Executes on "local server only" because continuation unit test requires many load on remote server.</pre>');
            }
            // In case of extending test class except "\BreakpointDebugging_UnitTestOverriding" class.
            if (B::$os === 'WIN') { // In case of Windows.
                $phpunit = 'phpunit.bat';
            } else { // In case of Unix.
                // Command execution path by "bash" differs because "Apache" is root user in case of default, therefore uses full path for command.
                while (true) {
                    $phpunit = `which phpunit`;
                    $phpunit = trim($phpunit);
                    if ($phpunit) {
                        break;
                    }
                    $phpunit = `export PATH=/opt/lampp/bin:/opt/local/bin:/usr/bin:/usr/bin/X11:/usr/share/php;which phpunit`;
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
                    if (B::$xdebugExists) {
                        xdebug_break();
                    }
                    return;
                }
                echo $testFileName . PHP_EOL;
                // Executes unit test command.
                echo `$phpunit $commandOptions $currentDir/$testFileName`;
                echo '//////////////////////////////////////////////////////////////////////////' . PHP_EOL;
            }
            echo '</pre>';
        }
    }

    /**
     * Makes up code coverage report, then displays in browser.
     *
     * @param string $unitTestFilePath Relative path of unit test file.
     * @param mixed  $classFilePaths   It is relative path of class which see the code coverage, and its current directory must be project directory.
     * @param string $currentDir       Unit test current directory.
     *
     * @example
     *      <?php
     *      chdir(__DIR__ . '/../../');
     *      require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';
     *      use \BreakpointDebugging as B;
     *      // Makes up code coverage report, then displays in browser.
     *      \BreakpointDebugging::makeCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php', __DIR__);
     *      \BreakpointDebugging::makeCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php'), __DIR__);
     *          .
     *          .
     *          .
     */
    static function makeCodeCoverageReport($unitTestFilePath, $classFilePaths, $currentDir)
    {
        $workDir = self::$workDir;
        echo '<pre>' . `phpunit --coverage-html $workDir/CodeCoverageReport $currentDir/$unitTestFilePath` . '</pre>';

        $displayBrowser = function($classFilePath, $browserPass, $workDir) {
            $classFilePath = str_replace(array ('/', '\\'), '_', $classFilePath);
            `$browserPass "file:///$workDir/CodeCoverageReport/$classFilePath.html"`;
        };
        $browserPass = self::$browserPass;
        if (is_array($classFilePaths)) {
            foreach ($classFilePaths as $classFilePath) {
                $displayBrowser($classFilePath, $browserPass, $workDir);
            }
        } else {
            $displayBrowser($classFilePaths, $browserPass, $workDir);
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
     * @example $return = $_BreakpointDebugging->displayVerification('function_name', func_get_args());
     *          $return = $_BreakpointDebugging->displayVerification('function_name', array($object, $resource, &$reference));
     */
    function displayVerification($functionName, $params)
    {
        self::assert(is_string($functionName), 1);
        self::assert(is_array($params), 2);

        $this->tmpParams = $params;
        $paramNumber = count($params);
        $propertyNameToSend = '$_BreakpointDebugging->tmpParams';
        $callStackInfo = debug_backtrace();
        echo "<pre>Executed function LINE: {$callStackInfo[0]['line']}    FILE: {$callStackInfo[0]['file']}</pre>";
        echo 'NAME = ' . $functionName . '(';
        $paramString = array ();
        for ($count = 0; $count < $paramNumber; $count++) {
            $paramString[] = $propertyNameToSend . '[' . $count . ']';
            var_dump($params[$count]);
        }
        echo ')';
        $code = $functionName . '(' . implode(',', $paramString) . ')';
        $return = eval('global $_BreakpointDebugging; $return = ' . $code . '; echo "<br/><br/>RETURN = "; var_dump($return); echo "<br/>"; return $return;');
        return $return;
    }

}

// When "Xdebug" does not exist.
if (!B::$xdebugExists) {
    if ($_BreakpointDebugging_EXE_MODE & (B::LOCAL_DEBUG | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of local host.
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

?>
