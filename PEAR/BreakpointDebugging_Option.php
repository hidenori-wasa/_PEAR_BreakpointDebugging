<?php

/**
 * Class which is for breakpoint debugging.
 *
 * "*_Option.php" file does not use on release. Therefore, response time is zero on release.
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
 * "function and method" with "assert()".
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
 *          BreakpointDebugging_Error_Exception
 *          BreakpointDebugging_UnitTest_Exception
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
 * Unit test exception.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_UnitTest_Exception extends \BreakpointDebugging_Exception
{

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
     * Debugs by using breakpoint.
     * We must define this class method outside namespace, and we must not change method name when we call this method.
     *
     * @param string $message        Message.
     * @param array  &$callStackInfo A call stack info.
     *
     * @return void
     * @example B::breakpoint('Error message.', debug_backtrace());
     */
    static function breakpoint($message, &$callStackInfo)
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

        if (B::$xdebugExists) {
            xdebug_break(); // Breakpoint. See local variable value by doing step execution here.
        }

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
        assert(func_num_args() <= 3);
        assert($phpIniVariable !== 'error_log');
        assert(is_string($setValue));

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
                throw new \BreakpointDebugging_Error_Exception('"ini_set()" failed.');
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
     *      // class BreakpointDebuggingTest extends \BreakpointDebugging_UnitTest // For step execution.
     *      class BreakpointDebuggingTest extends \PHPUnit_Framework_TestCase // For continuation execution.
     *      {
     *          .
     *          .
     *          .
     */
    static function checkUnitTestExeMode()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if ($_BreakpointDebugging_EXE_MODE !== (B::LOCAL_DEBUG_OF_RELEASE | B::UNIT_TEST) && $_BreakpointDebugging_EXE_MODE !== (B::RELEASE | B::UNIT_TEST)) {
            exit('You must set "$_BreakpointDebugging_EXE_MODE = $LOCAL_DEBUG_OF_RELEASE | $UNIT_TEST" or "$_BreakpointDebugging_EXE_MODE = $RELEASE | $UNIT_TEST" into "./PEAR_Setting/BreakpointDebugging_MySetting.php" in case of unit test.' . PHP_EOL);
        }
    }

    /**
     * Executes unit test.
     *
     * ### Execution procedure ###
     * Procedure 1: Please, start a apache.
     * Procedure 2: Please, drop php unit test file which calls this method to web browser.
     * Procedure 3: Please, rewrite web browser URL prefix to "localhost", and push return.
     * Please, if you want remote execution, upload unit test files, and execute with browser.
     *
     * @param array  $testFileNames Unit test file names.
     * @param string $currentDir    Unit test current directory.
     *
     * @return void
     *
     * @Example page which executes unit tests.
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
     *
     * @Example page which calls test-class method from start page of IDE project for debug.
     * <?php
     * require_once './tests/SomethingTest.php'; // Includes test PHP page.
     *
     * $pSomethingTest = new \SomethingTest();
     *
     * $pSomethingTest->setUp();
     * $pSomethingTest->testSomething();
     * $pSomethingTest->tearDown();
     * ?>
     */
    static function executeUnitTest($testFileNames, $currentDir)
    {
        self::checkUnitTestExeMode();

        $isSubclassOfBreakpointDebugging_UnitTest = function($currentDir, $testFileName, &$className) {
            require_once "$currentDir/$testFileName";
            $className = str_replace(array ('-', '/'), '_', substr($testFileName, 0, -4));
            $pClassReflection = new \ReflectionClass($className);
            return $pClassReflection->isSubclassOf('BreakpointDebugging_UnitTest');
        };

        $testFileName = $testFileNames[0];
        //require_once "$currentDir/$testFileName";
        if ($isSubclassOfBreakpointDebugging_UnitTest($currentDir, $testFileName, &$className)) {
            // Only one unit test process must be executed because static variable can not be initialized.
            if (count($testFileNames) !== 1) {
                $pUnitTest = new \BreakpointDebugging_UnitTest();
                $pUnitTest->assertTrue(false);
                exit;
            }
            // Constructs a test instance.
            $pClass = new $className;
            // Destructs a test instance.
            $pClass = null;
            echo '<pre>Unit test ended.</pre>';
            return;
        }

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
            // Array element of "BreakpointDebugging_UnitTest" class must be comment.
            if ($isSubclassOfBreakpointDebugging_UnitTest($currentDir, $testFileName, &$className)) {
                $pUnitTest = new \BreakpointDebugging_UnitTest();
                $pUnitTest->assertTrue(false);
                exit;
            }
            // If test file name contains '_'.
            if (strpos($testFileName, '_') !== false) {
                echo "You must change its array element and its file name into '-' because '$testFileName' contains '_'." . PHP_EOL;
                if (B::$xdebugExists) {
                    xdebug_break();
                }
                return;
            }
            echo $testFileName . PHP_EOL;
            echo `$phpunit $currentDir/$testFileName`;
            echo '//////////////////////////////////////////////////////////////////////////' . PHP_EOL;
        }
        echo 'Unit test ended.</pre>';
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
// When "Xdebug" does not exist.
if (!B::$xdebugExists) {
    if ($_BreakpointDebugging_EXE_MODE & (B::LOCAL_DEBUG | B::LOCAL_DEBUG_OF_RELEASE)) { // In case of local host.
        exit(
        '### ERROR ###<br/>' . PHP_EOL .
        'FILE: ' . __FILE__ . ' LINE: ' . __LINE__ . '<br/>' . PHP_EOL .
        '"Xdebug" extension has been not loaded though this is a local host.<br/>' . PHP_EOL .
        '"Xdebug" extension is required because (uses breakpoint, displays for fatal error and avoids infinity recursive function call).<br/>'
        );
    }
}

?>
