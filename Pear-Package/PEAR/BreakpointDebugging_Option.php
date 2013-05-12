<?php

/**
 * Class which is for breakpoint debugging.
 *
 * "*_Option.php" file does not use on release. Therefore, response time is zero in release.
 * These file names put "_" to cause error when we do autoload.
 *
 * ### Environment which can do breakpoint debugging. ###
 * Debugger which can use breakpoint.
 * At April, 2013 recommendation debugging environment is
 * "WindowsXP Professional" + "NetBeans IDE 7.1.2" + "XAMPP 1.7.3" or
 * "Ubuntu desktop" + "NetBeans IDE 7.1.2" + "XAMPP for Linux 1.7.3".
 * Do not use version greater than "XAMPP 1.7.3" for "NetBeans IDE 7.1.2"
 * because MySQL version causes discordance.
 * Notice: Use "phpMyAdmin" to see database and to execute "MySQL" command.
 *         Also, "NetBeans IDE 7.3" cannot keep switchback at April, 2013.
 *         However, "NetBeans IDE 7.3" supports "PHP5.4" and "HTML5".
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
 *      Because "XDebug" information is not displayed on remote release mode.
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
 *      BreakpointDebugging_Exception_InAllCase
 *          BreakpointDebugging_Exception
 *              BreakpointDebugging_ErrorException
 *              BreakpointDebugging_OutOfLogRangeException
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
use \BreakpointDebugging_InAllCase as BA;

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
final class BreakpointDebugging extends \BreakpointDebugging_UnitTestCaller
{
    /**
     * @var array Setting option filenames.
     */
    private static $_onceFlag = array ();

    /**
     * @var string Include-paths.
     */
    private static $_includePaths;

    /**
     * Limits static properties accessing.
     *
     * @return void
     */
    static function initialize()
    {
        B::limitAccess('BreakpointDebugging.php');

        B::assert(func_num_args() === 0, 1);

        parent::initialize();

        parent::$staticProperties['$_includePaths'] = &self::$_includePaths;
        $tmp = BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php';
        parent::$staticPropertyLimitings['$_userName'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogFileByteSize'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogParamNestingLevel'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogElementNumber'] = $tmp;
        parent::$staticPropertyLimitings['$_maxLogStringSize'] = $tmp;
        parent::$staticPropertyLimitings['$_workDir'] = $tmp;
        parent::$staticPropertyLimitings['$_onceErrorDispFlag'] = 'BreakpointDebugging/UnitTestOverriding.php';
        parent::$staticPropertyLimitings['$_callingExceptionHandlerDirectly'] = array (
            'BreakpointDebugging/Error.php',
            'BreakpointDebugging/UnitTestOverriding.php'
        );
    }

    /**
     * If "Apache HTTP Server" does not support "suEXEC", this method displays security warning.
     *
     * @return void
     */
    static function checkSuperUserExecution()
    {
        // If this is remote debug, unix and root user.
        if (BA::$exeMode & B::REMOTE
            && B::getStatic('$_os') !== 'WIN'
            && trim(`echo \$USER`) === 'root'
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
        self::limitAccess(parent::$staticPropertyLimitings[$propertyName]);

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
        self::assert(func_num_args() === 0, 1);

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

        if (BA::$exeMode & B::UNIT_TEST) {
            BreakpointDebugging_UnitTestCaller::handleUnitTestException($pException);
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
        if (array_key_exists($fullFilePath, $invokingLocations)
            && array_key_exists($line, $invokingLocations[$fullFilePath])
        ) {
            // Skips same.
            return true;
        }
        // Stores the invoking location information.
        $invokingLocations[$fullFilePath][$line] = true;

        self::assert(func_num_args() <= 2, 1);
        self::assert(is_array($invokerFilePaths) || is_string($invokerFilePaths), 2);
        self::assert(is_bool($enableUnitTest), 3);

        if (!$enableUnitTest
            && (BA::$exeMode & B::UNIT_TEST)
            && (!isset(parent::$unitTestDir) || strpos($fullFilePath, parent::$unitTestDir) === 0)
        ) {
            return;
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
     * Throws exception if assertion is false. Also, has identification code for debug unit test.
     *
     * @param bool $assertion Assertion.
     * @param int  $id        Exception identification number inside function.
     *                        I recommend from 0 to 99 if you do not detect by unit test.
     *                        I recommend from 100 if you detect by unit test.
     *                        This number must not overlap with other assertion or exception identification number inside function.
     *
     * @return void
     * @usage
     *      \BreakpointDebugging::assert(<judgment expression>, <identification number inside function>);
     *      It is possible to assert that <judgment expression> is "This must be". Especially, this uses to verify a function's argument.
     *      For example: \BreakpointDebugging::assert(3 <= $value && $value <= 5); // $value should be 3-5.
     *      Caution: Don't change the value of variable in "\BreakpointDebugging::assert()" function because there isn't executed in case of release.
     */
    static function assert($assertion, $id = null)
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
        if (!is_int($id)
            && !is_null($id)
        ) {
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
        if (!(BA::$exeMode & B::REMOTE)) {
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
            // In case of remote debug.
            if ($doCheck === true
                && (BA::$exeMode & B::REMOTE)
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
### Also, if remote "php.ini" was changed, you must redo remote debug mode.
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
                throw new \BreakpointDebugging_ErrorException('"ini_set()" failed.', 101);
            }
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
    if (!(B::getStatic('$exeMode') & B::REMOTE)) { // In case of local.
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
