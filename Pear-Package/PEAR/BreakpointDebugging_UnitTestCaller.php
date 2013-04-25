<?php

/**
 * Classes for unit test.
 *
 * This file does not use except unit test. Therefore, response time is zero in release.
 * This file names put "_" to cause error when we do autoload.
 *
 * PHP version 5.3
 *
 * LICENSE OVERVIEW:
 * 1. Do not change license text.
 * 2. Copyrighters do not take responsibility for this file code.
 *
 * LICENSE:
 * Copyright (c) 2013, Hidenori Wasa
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
 * Own package exception. For unit test.
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
 * Class for unit test.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_UnitTestCaller extends \BreakpointDebugging_InAllCase
{
    /**
     * @var string Browser execution pass.
     */
    private static $_browserPass = 'C:\Program Files\Mozilla Firefox\firefox.exe';

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
        B::limitAccess(array ('BreakpointDebugging.php', 'BreakpointDebugging_Option.php'));

        B::assert(func_num_args() === 0);

        parent::initialize();

        self::$staticPropertyLimitings['$_includePaths'] = ''; // For unit test.
        self::$staticPropertyLimitings['$_valuesToTrace'] = ''; // For unit test.
        self::$staticPropertyLimitings['$exeMode'] = ''; // For unit test.
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
        B::assert(func_num_args() === 2, 1);
        B::assert(is_string($propertyName), 2);

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
        B::assert(func_num_args() === 3, 1);
        B::assert(is_string($propertyName), 2);

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
     * @param string $unitTestKind Unit test kind.
     *
     * @return mixed Unit test directory, or void.
     *
     * @example
     *      <?php
     *
     *      chdir(__DIR__ . '/../../');
     *      require_once './BreakpointDebugging_Including.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      B::isUnitTestExeMode('DEBUG_UNIT_TEST'); // or B::isUnitTestExeMode('RELEASE_UNIT_TEST');
     *
     *      class BreakpointDebuggingTest extends \BreakpointDebugging_UnitTestOverriding
     *      {
     *          .
     *          .
     *          .
     */
    static function isUnitTestExeMode($unitTestKind)
    {
        B::assert(func_num_args() === 1, 1);
        B::assert(is_string($unitTestKind), 2);

        if (parent::$exeMode & B::RELEASE) {
            switch ($unitTestKind) {
                case 'RELEASE_UNIT_TEST':
                    break;
                case 'DEBUG_UNIT_TEST':
                    throw new \BreakpointDebugging_ErrorException('<pre>You must set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'DEBUG_UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>', 3);
                default:
                    throw new \BreakpointDebugging_ErrorException('<pre>You mistook "\BreakpointDebugging::isUnitTestExeMode(\'...\');".</pre>', 5);
            }
        } else {
            switch ($unitTestKind) {
                case 'DEBUG_UNIT_TEST':
                    break;
                case 'RELEASE_UNIT_TEST':
                    throw new \BreakpointDebugging_ErrorException('<pre>You must set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'RELEASE_UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>', 6);
                default:
                    throw new \BreakpointDebugging_ErrorException('<pre>You mistook "\BreakpointDebugging::isUnitTestExeMode(\'...\');".</pre>', 8);
            }
        }

        if (isset(self::$_unitTestDir)) {
            return;
        }

        $unitTestCurrentDir = debug_backtrace();
        if (array_key_exists(1, $unitTestCurrentDir)
            && array_key_exists('class', $unitTestCurrentDir[1])
            && $unitTestCurrentDir[1]['class'] === 'BreakpointDebugging_UnitTestCaller'
            && array_key_exists('function', $unitTestCurrentDir[1])
            && $unitTestCurrentDir[1]['function'] === 'executeUnitTest'
        ) { // Calling from "\BreakpointDebugging::executeUnitTest()".
            // @codeCoverageIgnoreStart
            $unitTestCurrentDir = dirname($unitTestCurrentDir[1]['file']);
        } else { // In case of command.
            // @codeCoverageIgnoreEnd
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
     *      B::isUnitTestExeMode('DEBUG_UNIT_TEST'); // or B::isUnitTestExeMode('RELEASE_UNIT_TEST');
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
        if (parent::$exeMode & self::RELEASE) {
            $unitTestCurrentDir = self::isUnitTestExeMode('RELEASE_UNIT_TEST');
        } else {
            $unitTestCurrentDir = self::isUnitTestExeMode('DEBUG_UNIT_TEST');
        }

        B::assert(func_num_args() === 1, 1);
        B::assert(is_array($unitTestCommands), 2);
        B::assert(!empty($unitTestCommands), 3);

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
            if (parent::$exeMode & B::REMOTE) { // In case of remote.
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
        B::assert(func_num_args() === 3, 1);
        B::assert(is_string($classFilePath), 2);
        B::assert(is_string($browserPass), 3);
        B::assert(is_string($workDir), 4);

        // If unit test calls this class method.
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
        B::assert(func_num_args() === 2, 1);
        B::assert(is_string($unitTestFilePath), 2);
        B::assert(is_string($classFilePaths) || is_array($classFilePaths), 3);

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

}

?>
