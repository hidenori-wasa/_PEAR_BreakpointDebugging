<?php

/**
 * Class which is for breakpoint debugging.
 *
 * "*_Option.php" file does not use on release. Therefore, response time is zero on release.
 * These file names put "_" to become error when we do autoload.
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
 * it is to be able to find a position of a bug immediately.
 * In addition to it, condition of variable can be examined.
 * Therefore, it is possible to do debugging quickly.
 *
 * ### How to do breakpoint debugging coding. ###
 * We have to do coding as follows to process in "BreakpointDebugging" class.
 * We have to verify a impossible return value of function or method with "assert()".
 * We have to verify a impossible value in code.
 * Also, an error and an exception which wasn't caught are processed in
 * "BreakpointDebugging" class.
 *
 * ### The execution procedure. ###
 * Procedure 1: Please, install "XDebug" by seeing "http://xdebug.org/docs/install".
 *      This is required to stop at breakpoint.
 * Procedure 2: If you execute "REMOTE_DEBUG", please set "xdebug.remote_host =
 *      "<name or ip of host which debugger exists>"" into "php.ini" file.
 * Procedure 3: Please, set *.php file format to utf8, but we should create backup of
 *      php files because multibyte strings may be destroyed.
 * Procedure 4: Please, copy BreakpointDebugging_MySetting*.php to "./PEAR_Setting/"
 *      of your project directory.
 * Procedure 5: Please, edit BreakpointDebugging_MySetting*.php for customize.
 *      Then, it fixes part setting about all debugging modes.
 * Procedure 6: Please, copy following in your project php code.
 *      "require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';"
 * Procedure 7: Please, set debugging mode to "$_BreakpointDebugging_EXE_MODE" into
 *      "./PEAR_Setting/BreakpointDebugging_MySetting.php".
 * Procedure 8: Please, if you use "Linux", register your username as
 *      "User" and "Group" into "lampp/apache/conf/httpd.conf".
 *      And, register "export PATH=$PATH:/opt/lampp/bin" into "~/.profile".
 * Procedure 9: Please, if you can change "php.ini" file,
 *      use "\BreakpointDebugging::iniCheck()" instead of
 *      "\BreakpointDebugging::iniSet(), ini_set()" in "*_MySetting.php" file,
 *      and move it to "*_MySetting_Option.php" file
 *      because decreases the read and the parse bytes.
 *
 * Caution: Do not execute "ini_set('error_log')"
 *      because this package uses local log instead of system log.
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
 * First "LOCAL_DEBUG" mode is breakpoint debugging with local personal computer.
 *      Debugger which can use breakpoint.
 * Second "LOCAL_DEBUG_OF_RELEASE" mode is breakpoint debugging to emulate release
 *      with local personal computer.
 *      Debugger which can use breakpoint.
 * Third "REMOTE_DEBUG" mode is browser display debugging with remote personal computer.
 *      And it is remote debugging by debugger.
 *      Debugger which can use breakpoint.
 * Last "RELEASE" mode is log debugging with remote personal computer,
 *      and we must set on last for security.
 *      On release
 * "UNIT_TEST" mode tests by "phpunit" command.
 *
 *  ### Exception hierarchical structure ###
 *  PEAR_Exception
 *      BreakpointDebugging_Exception
 *          BreakpointDebugging_Error_Exception
 *
 * ### Useful function index. ###
 * This outputs function call stack log.
 *      \BreakpointDebugging::outputErrorCallStackLog($errorKind, $errorMessage)
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
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

/**
 * Unit test exception.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <wasa_@nifty.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_UnitTest_Exception extends BreakpointDebugging_Exception
{

}

/**
 * This class executes error or exception handling, and it is except release mode.
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
     * @var array Setting option filenames.
     */
    private $_onceFlag = array ();

    /**
     * @var array Temporary parameter array.
     */
    public $tmpParams;

    /**
     * This constructer create object only one time.
     *
     * @return void
     */
    function __construct()
    {
        parent::__construct();

        static $createOnlyOneTime = false;

        assert($createOnlyOneTime === false);
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
     * This is function to debug with breakpoint.
     * We must define this function outside namespace, and we must not change method name when we call this method.
     *
     * @param string $message       Message.
     * @param array  $callStackInfo A call stack info.
     *
     * @return void
     * @example B::breakpoint('Error message.', debug_backtrace());
     */
    static function breakpoint($message, $callStackInfo)
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE & self::UNIT_TEST) {
            // Unit test cannot execute "xdebug_break()".
            return;
        }

        B::internalAssert(func_num_args() === 2);
        B::internalAssert(is_string($message));
        B::internalAssert(is_array($callStackInfo));

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

        $return = xdebug_break(); // Breakpoint. See local variable value by doing step execution here.
        self::internalAssert($return);

        if ($_BreakpointDebugging_EXE_MODE & self::REMOTE_DEBUG) {
            // Remote debug must end immediately to avoid eternal execution.
            exit;
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
                } else {
                    $displayMbStringArray[$count] = mb_convert_encoding($mbString, $charSet, 'auto');
                }
                $count++;
            }
            return $displayMbStringArray;
        }
    }

    /**
     * This validates to be same type.
     *
     * @param mixed $cmp1 A variable to compare type.
     * @param mixed $cmp2 A variable to compare type.
     *
     * @return bool Is this the same type?
     */
    private static function _isSameType($cmp1, $cmp2)
    {
        if (is_string($cmp1) === true && is_string($cmp2) === true) {
            return true;
        }
        if (is_int($cmp1) === true && is_int($cmp2) === true) {
            return true;
        }
        if (is_bool($cmp1) === true && is_bool($cmp2) === true) {
            return true;
        }
        if (is_array($cmp1) === true && is_array($cmp2) === true) {
            return true;
        }
        if (is_null($cmp1) === true && is_null($cmp2) === true) {
            return true;
        }
        if (is_float($cmp1) === true && is_float($cmp2) === true) {
            return true;
        }
        if (is_object($cmp1) === true && is_object($cmp2) === true) {
            return true;
        }
        if (is_resource($cmp1) === true && is_resource($cmp2) === true) {
            return true;
        }
        return false;
    }

    /**
     * This is ini_set() with validation except for release mode.
     * I set with "ini_set()" because "php.ini" file and ".htaccess" file isn't sometimes possible to be set on sharing server.
     *
     * @param string $phpIniVariable This is php.ini variable.
     * @param string $setValue       Value of variable.
     * @param bool   $doCheck        Does it check to copy to the release file?
     *
     * @return void
     */
    static function iniSet($phpIniVariable, $setValue, $doCheck = true)
    {
        global $_BreakpointDebugging_EXE_MODE, $_BreakpointDebugging;
        assert(func_num_args() <= 3);
        assert($phpIniVariable !== 'error_log');

        $getValue = ini_get($phpIniVariable);
        assert(self::_isSameType($setValue, $getValue));
        if ($setValue !== $getValue) {
            // In case of remote.
            if ($doCheck === true && ($_BreakpointDebugging_EXE_MODE & self::REMOTE_DEBUG)) {
                $backTrace = debug_backtrace();
                $baseName = basename($backTrace[0]['file']);
                $cmpName = '_MySetting_Option.php';
                $cmpNameLength = strlen($cmpName);
                if (!substr_compare($baseName, $cmpName, 0 - $cmpNameLength, $cmpNameLength, true)) {
                    $notExistFlag = true;
                    foreach ($_BreakpointDebugging->_onceFlag as $cmpName) {
                        if (!strcasecmp($baseName, $cmpName)) {
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
### But, When remote "php.ini" is changed, you must redo remote debug.<pre/>
EOD;
                    }
                    echo <<<EOD
	file: {$backTrace[0]['file']}
	line: {$backTrace[0]['line']}

<pre/>
EOD;
                }
            }
            if (ini_set($phpIniVariable, $setValue) === false) {
                throw new \BreakpointDebugging_Error_Exception('');
            }
        }
    }

    /**
     * This checks php.ini setting.
     *
     * @param string $phpIniVariable The php.ini file setting variable.
     * @param mixed  $cmpValue       Value which should set in case of string.
     *                                Value which should avoid in case of array.
     * @param string $errorMessage   Error message.
     *
     * @return void
     */
    static function iniCheck($phpIniVariable, $cmpValue, $errorMessage)
    {
        assert(func_num_args() === 3);
        $value = (string) ini_get($phpIniVariable);
        $cmpResult = false;
        if (is_array($cmpValue)) {
            foreach ($cmpValue as $eachCmpValue) {
                assert(self::_isSameType($value, $eachCmpValue));
                if ($value === $eachCmpValue) {
                    $cmpResult = true;
                    break;
                }
            }
        } else {
            assert(self::_isSameType($value, $cmpValue));
            if ($value !== $cmpValue) {
                $cmpResult = true;
            }
        }
        if ($cmpResult) {
            echo '<br/>' . $errorMessage . '<br/>' .
            'Current value is ' . $value . '<br/>';
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
    final static function getPropertyForTest($objectOrClassName, $propertyName)
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
        assert(false);
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
    final static function setPropertyForTest($objectOrClassName, $propertyName, $value)
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
        assert(false);
    }

    /**
     * Makes php unit test exception.
     *
     * @return void
     */
    static function makeUnitTestException()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE & B::UNIT_TEST) {
            // For debug which executes direct-test-class-method-call from "index.php".
            xdebug_break();
            // Throws exception for unit test.
            throw new \BreakpointDebugging_UnitTest_Exception('');
        }
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
     *      class BreakpointDebuggingTest extends PHPUnit_Framework_TestCase
     *      {
     *          .
     *          .
     *          .
     */
    static function checkUnitTestExeMode()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE !== (B::LOCAL_DEBUG_OF_RELEASE | B::UNIT_TEST) && $_BreakpointDebugging_EXE_MODE !== (B::RELEASE | B::UNIT_TEST)) {
            exit('You must set "$_BreakpointDebugging_EXE_MODE = $LOCAL_DEBUG_OF_RELEASE | $UNIT_TEST" or "$_BreakpointDebugging_EXE_MODE = $REMOTE_DEBUG | $UNIT_TEST" into "./PEAR_Setting/BreakpointDebugging_MySetting.php" in case of unit test.' . PHP_EOL);
        }
    }

    /**
     * Executes unit test.
     *
     * ### Execution procedure ###
     * Procedure 1: Please, start a apache.
     * Procedure 2: Please, drop php unit test file which calls this method to web browser.
     * Procedure 3: Please, rewrite web browser URL prefix to "localhost", and push return.
     * If you want step execution, please, set composition of project of "NetBeans IDE".
     * If you want remote execution, please, upload unit test files, and execute with browser.
     *
     * @param array  $testFileNames Unit test file names.
     * @param string $currentDir    Unit test current directory.
     *
     * @return void
     *
     * @example
     * <?php
     * chdir(__DIR__ . '/../../');
     * require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';
     * use \BreakpointDebugging as B;
     * // Please, choose unit tests files by customizing.
     * $testFileNames = array (
     *     'BreakpointDebuggingTest.php',
     *     'BreakpointDebugging/LockTest.php',
     * );
     * // Executes unit tests.
     * B::executeUnitTest($testFileNames, __DIR__);
     * ?>
     */
    static function executeUnitTest($testFileNames, $currentDir)
    {
        self::checkUnitTestExeMode();
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
                exit('"phpunit" command does not exist.');
            }
            if (!is_executable($phpunit)) {
                exit('"phpunit" command is not executable. (' . $phpunit . ')');
            }
        }
        echo '<pre>';
        foreach ($testFileNames as $testFileName) {
            // If test file name contains '_'.
            if (strpos($testFileName, '_') !== false) {
                echo "You must change its array element and its file name into '-' because '$testFileName' contains '_'." . PHP_EOL;
                xdebug_break();
                return;
            }
            echo $testFileName . PHP_EOL;
            echo `$phpunit $currentDir/$testFileName`;
            echo '//////////////////////////////////////////////////////////////////////////' . PHP_EOL;
        }
        echo '</pre>';
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
        assert(is_string($functionName));
        assert(is_array($params));

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

// ### Assertion setting. ###
if (assert_options(ASSERT_ACTIVE, 1) === false) { // This makes the evaluation of assert() effective.
    throw new \BreakpointDebugging_Error_Exception('');
}
if (assert_options(ASSERT_WARNING, 1) === false) { // In case of failing in assertion, this generates a warning.
    throw new \BreakpointDebugging_Error_Exception('');
}
if (assert_options(ASSERT_BAIL, 0) === false) { // In case of failing in assertion, this doesn't end execution.
    throw new \BreakpointDebugging_Error_Exception('');
}
if (assert_options(ASSERT_QUIET_EVAL, 0) === false) { // As for assertion expression, this doesn't make error_reporting invalid.
    throw new \BreakpointDebugging_Error_Exception('');
}
// ### usage ###
//   assert(<judgment expression>);
//   It is possible to assert that <judgment expression> is "This must be". Especially, this uses to verify a function's argument.
//   For example: assert(3 <= $value && $value <= 5); // $value should be 3-5.
//   Caution: Don't change the value of variable in "assert()" function because there isn't executed in case of release.

if (!extension_loaded('xdebug')) {
    exit(
    '### ERROR ###<br/>' . PHP_EOL .
    'FILE: ' . __FILE__ . ' LINE: ' . __LINE__ . '<br/>' . PHP_EOL .
    '"Xdebug" extension has been not loaded.<br/>' . PHP_EOL .
    '"Xdebug" extension is required because avoids infinity recursive function call.<br/>' . PHP_EOL .
    'Also, this package requires "Xdebug" extension.<br/>'
    );
}

?>
