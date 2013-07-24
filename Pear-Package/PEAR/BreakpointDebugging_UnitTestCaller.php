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
        B::assert($previous instanceof \Exception || $previous === null, 4);

        if (mb_detect_encoding($message, 'utf8', true) === false) {
            throw new \BreakpointDebugging_ErrorException('Exception message is not "UTF8".', 101);
        }

        // Adds "[[[CLASS=<class name>] FUNCTION=<function name>] ID=<identification number>]" to message in case of unit test.
        if (B::getStatic('$exeMode') & B::UNIT_TEST) {
            B::assert(is_int($omissionCallStackLevel) && $omissionCallStackLevel >= 0, 5);

            if ($id === null) {
                $idString = '.';
            } else {
                $idString = ' ID=' . $id . '.';
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
abstract class BreakpointDebugging_UnitTestCaller extends \BreakpointDebugging_InAllCase
{
    /**
     * @var int Execution mode.
     */
    static $exeMode;

    /**
     * @var  string Unit test directory.
     */
    protected static $unitTestDir;

    /**
     * @var mixed It is relative path of class which see the code coverage, and its current directory must be project directory.
     */
    private static $_classFilePaths;

    /**
     * @var string The code coverage report directory path.
     */
    private static $_codeCoverageReportPath;

    /**
     * Limits static properties accessing.
     *
     * @return void
     */
    static function initialize()
    {
        B::limitAccess(array ('BreakpointDebugging.php', 'BreakpointDebugging_Option.php'));

        B::assert(func_num_args() === 0, 1);

        parent::initialize();

        self::$exeMode = &parent::$exeMode;
        self::$staticProperties['$_classFilePaths'] = &self::$_classFilePaths;
        self::$staticProperties['$_codeCoverageReportPath'] = &self::$_codeCoverageReportPath;
        parent::$staticPropertyLimitings['$_includePaths'] = '';
        parent::$staticPropertyLimitings['$_valuesToTrace'] = '';
        parent::$staticPropertyLimitings['$exeMode'] = 'BreakpointDebugging/PHPUnitFrameworkTestCase.php';
    }

    /**
     * Displays exception if release unit test error of "local or remote".
     *
     * @param object $pException Exception information.
     *
     * @return void
     * @codeCoverageIgnore
     * Because unit test is exited.
     */
    static function displaysException($pException)
    {
        B::assert(func_num_args() === 1, 1);
        B::assert($pException instanceof \Exception, 2);

        $callStack = debug_backtrace();
        if (!array_key_exists(1, $callStack)
            || !array_key_exists('file', $callStack[1])
            || strripos($callStack[1]['file'], 'PHPUnitFrameworkTestCase.php') === strlen($callStack[1]['file']) - strlen('PHPUnitFrameworkTestCase.php')
        ) {
            B::iniSet('xdebug.var_display_max_depth', '5', false);
            var_dump($pException);
            B::exitForError();
        }
    }

    /**
     * Handles unit test exception.
     *
     * @param object $pException Exception information.
     *
     * @return void
     */
    static function handleUnitTestException($pException)
    {
        B::assert(func_num_args() === 1, 1);
        B::assert($pException instanceof \Exception, 2);

        $callStack = $pException->getTrace();
        $call = array_key_exists(0, $callStack) ? $callStack[0] : array ();
        // In case of direct call from "BreakpointDebugging_InAllCase::callExceptionHandlerDirectly()".
        // This call is in case of debug mode.
        if ((array_key_exists('class', $call) && $call['class'] === 'BreakpointDebugging_InAllCase')
            && (array_key_exists('function', $call) && $call['function'] === 'callExceptionHandlerDirectly')
        ) {
            throw $pException;
            // @codeCoverageIgnoreStart
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Gets unit test directory.
     *
     * @return void
     */
    private static function _getUnitTestDir()
    {
        $unitTestCurrentDir = debug_backtrace();
        $unitTestCurrentDir = dirname($unitTestCurrentDir[1]['file']) . DIRECTORY_SEPARATOR;
        if (B::getStatic('$_os') === 'WIN') { // In case of Windows.
            self::$unitTestDir = strtolower($unitTestCurrentDir);
        } else { // In case of Unix.
            self::$unitTestDir = $unitTestCurrentDir;
        }
    }

    //////////////////////////////////////// For package user ////////////////////////////////////////
    /**
     * Marks the test as skipped in debug.
     *
     * @return void
     */
    static function markTestSkippedInDebug()
    {
        if (!(self::$exeMode & B::RELEASE)) {
            \PHPUnit_Framework_Assert::markTestSkipped();
        }
    }

    /**
     * Marks the test as skipped in release.
     *
     * @return void
     */
    static function markTestSkippedInRelease()
    {
        if (self::$exeMode & B::RELEASE) {
            \PHPUnit_Framework_Assert::markTestSkipped();
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
        B::assert(func_num_args() === 2, 1);
        B::assert(is_string($propertyName), 2);
        B::assert(is_object($objectOrClassName) || is_string($objectOrClassName), 3);

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
        throw new \BreakpointDebugging_ErrorException('"' . $className . '::' . $propertyName . '" property does not exist.', 101);
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
        B::assert(is_object($objectOrClassName) || is_string($objectOrClassName), 3);

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
        throw new \BreakpointDebugging_ErrorException('"' . $className . '::' . $propertyName . '" property does not exist.', 101);
    }

    /**
     * Checks unit-test-execution-mode, and sets unit test directory.
     *
     * @param bool $isUnitTest It is unit test?
     *
     * @return void
     *
     * @example
     *      <?php
     *
     *      $projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
     *      chdir(__DIR__ . '/' . $projectDirPath);
     *      require_once './BreakpointDebugging_Inclusion.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      B::checkExeMode(true);
     *
     *      class SomethingTest extends \BreakpointDebugging_PHPUnitFrameworkTestCase
     *      {
     *          .
     *          .
     *          .
     */
    static function checkExeMode($isUnitTest = false)
    {
        if (func_num_args() === 0) {
            echo '<pre>You must not set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'..._UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>';
            self::$exeMode |= B::IGNORING_BREAK_POINT;
            throw new \BreakpointDebugging_ErrorException('', 101);
        }
        B::assert(is_bool($isUnitTest), 1);

        if (!$isUnitTest) {
            echo '<pre>You must not set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'..._UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>';
            self::$exeMode |= B::IGNORING_BREAK_POINT;
            throw new \BreakpointDebugging_ErrorException('', 102);
        }
    }

    /**
     * Runs "phpunit" command.
     *
     * @param string $command The command character-string which excepted "phpunit".
     *
     * @return void
     */
    private static function _runPHPUnitCommand($command)
    {
        $command = ltrim($command);
        $commandElements = explode(' ', $command);
        $testFileName = array_pop($commandElements);
        array_push($commandElements, self::$unitTestDir . $testFileName);
        array_unshift($commandElements, 'dummy');
        include_once 'PHPUnit/Autoload.php';
        $pPHPUnit_TextUI_Command = new \PHPUnit_TextUI_Command;
        // Checks command line switches.
        if (in_array('--process-isolation', $commandElements)) {
            throw new \BreakpointDebugging_ErrorException('You must not use "--process-isolation" command line switch because this unit test is run in other process.' . PHP_EOL . 'So, you cannot debug unit test code with IDE.', 101);
        }
        // Uses "PHPUnit" package error handler.
        restore_error_handler();
        // Runs unit test continuously.
        $pPHPUnit_TextUI_Command->run($commandElements, false);
        // Uses "BreakpointDebugging" package error handler.
        set_error_handler('\BreakpointDebugging::handleError', -1);
    }

    /**
     * Executes unit tests continuously, and debugs with IDE.
     *
     * We have to use private static property instead of using local static variable.
     * Then, we can use unit test's "--static-backup" command line switch.
     *
     * Also, we must not use unit test's "--process-isolation" command line switch because its tests is run in other process.
     * Therefore, we cannot debug unit test code with IDE.
     *
     * How to run multiprocess unit test (Unix only):
     *      Procedure 1: Use process control functions "pcntl_...()" inside your unit test class method "test...()".
     *      Procedure 2: Judge by using "parent::assertTrue(<conditional expression>)".
     *
     * ### Running procedure. ###
     * Please, run the following procedure.
     * Procedure 1: Make "page like example page" and unit test files.
     * Procedure 2: Copy following "PHPUnit" files into "PEAR" directory of project directory.
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
     * Procedure 3: Run "page like example page" with IDE.
     *
     * @param array  $unitTestFilePaths   The file paths of unit tests.
     * @param string $commandLineSwitches Command-line-switches except "--stop-on-failure --static-backup".
     *
     * @return void
     *
     * @Example page which runs unit tests.
     * <?php
     *
     * $projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
     * chdir(__DIR__ . '/' . $projectDirPath);
     * require_once './BreakpointDebugging_Inclusion.php';
     *
     * use \BreakpointDebugging as B;
     *
     * B::checkExeMode(true);
     *
     * // Please, choose unit tests files by customizing.
     * $unitTestFilePaths = array (
     *     'SomethingTest.php',
     *     'Something/SubTest.php',
     * );
     *
     * // Executes unit tests.
     * B::executeUnitTest($unitTestFilePaths); exit;
     *
     * ?>
     *
     * @Example of unit test file.
     * <?php
     *
     * $projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
     * chdir(__DIR__ . '/' . $projectDirPath);
     * require_once './BreakpointDebugging_Inclusion.php';
     *
     * use \BreakpointDebugging as B;
     * use \BreakpointDebugging_UnitTestCaller as BU;
     *
     * B::checkExeMode(true);
     *
     * class SomethingTest extends \BreakpointDebugging_PHPUnitFrameworkTestCase
     * {
     *     private $_pSomething;
     *
     *     static function setUpBeforeClass()
     *     {
     *          // We must not change static status at here because its change affects to next unit test file if you run unit tests continuously.
     *          // Please, use "setUp()" class method instead of this class method.
     *          ...
     *     }
     *
     *     protected function setUp()
     *     {
     *          // This is required.
     *          parent::setUp();
     *          // Constructs instance.
     *          $this->_pSomething = new \Something();
     *     }
     *
     *     protected function tearDown()
     *     {
     *          // Destructs instance.
     *          $this->_pSomething = null;
     *          // This is required.
     *          parent::tearDown();
     *     }
     *
     *     /*
     *      * @expectedException        \BreakpointDebugging_ErrorException
     *      * @expectedExceptionMessage CLASS=SomethingTest FUNCTION=testSomething_A ID=101
     *      *
     *     function testSomething_A()
     *     {
     *          BU::markTestSkippedInDebug();
     *
     *          BU::$exeMode |= B::IGNORING_BREAK_POINT;
     *          throw new \Something_ErrorException('Something message.', 101);
     *     }
     *
     *     function testSomething_B()
     *     {
     *          BU::markTestSkippedInRelease();
     *
     *          try {
     *              B::assert(true, 101);
     *              B::assert(false, 102);
     *          } catch (\BreakpointDebugging_ErrorException $e) {
     *              $this->assertTrue(strripos($e->getMessage(), 'CLASS=SomethingTest FUNCTION=testSomething_B ID=102'));
     *              return;
     *          }
     *          $this->fail();
     *      }
     *  }
     *
     * ?>
     *
     * @codeCoverageIgnore
     * Because "phpunit" command cannot run during "phpunit" command running.
     */
    static function executeUnitTest($unitTestFilePaths, $commandLineSwitches = '')
    {
        static $unitTestFilePathsStorage = array ();

        /*         * ***
          $runByCommand = false; // Running by command does not support.
         * *** */
        if (!B::checkDevelopmentSecurity()) {
            B::exitForError();
        }

        foreach ($unitTestFilePaths as $unitTestFilePath) {
            if (in_array($unitTestFilePath, $unitTestFilePathsStorage, true)) {
                throw new \BreakpointDebugging_ErrorException('Unit test file path must be unique.', 101);
            }
            $unitTestFilePathsStorage[] = $unitTestFilePath;
        }

        echo file_get_contents('BreakpointDebugging/css/FontStyle.html', true);
        echo '<pre>';
        $separator = PHP_EOL . '//////////////////////////////////////////////////////////////////////////' . PHP_EOL;

        if (self::$exeMode & B::RELEASE) {
            echo '<b>\'RELEASE_UNIT_TEST\' execution mode.</b>' . PHP_EOL;
        } else {
            echo '<b>\'DEBUG_UNIT_TEST\' execution mode.</b>' . PHP_EOL;
        }

        B::assert(func_num_args() <= 2, 1);
        B::assert(is_array($unitTestFilePaths), 2);
        B::assert(!empty($unitTestFilePaths), 3);
        B::assert(is_string($commandLineSwitches), 4);

        self::_getUnitTestDir();
        foreach ($unitTestFilePaths as $unitTestFilePath) {
            // If test file path contains '_'.
            if (strpos($unitTestFilePath, '_') !== false) {
                echo "You have to change from '_' of '$unitTestFilePath' to '-' because you cannot run unit tests." . PHP_EOL;
                //if (B::getXebugExists()
                if (function_exists('xdebug_break')
                    && !(self::$exeMode & B::IGNORING_BREAK_POINT)
                ) {
                    xdebug_break();
                }
                continue;
            }
            $command = $commandLineSwitches . ' --stop-on-failure --static-backup ' . $unitTestFilePath;
            echo $separator;
            echo "Runs <b>\"phpunit $command\"</b> command." . PHP_EOL;
            self::_runPHPUnitCommand($command);
        }
        /*         * ***
          }
         * *** */
        echo $separator;
        echo '<b>Unit tests have done.</b></pre>';
    }

    /**
     * Creates code coverage report, then displays in browser.
     *
     * @param string $unitTestFilePath    Relative path of unit test file.
     * @param mixed  $classFilePaths      It is relative path of class which see the code coverage, and its current directory must be project directory.
     * @param string $commandLineSwitches Command-line-switches except "--static-backup --coverage-html".
     *
     * @return void
     * @example
     *      <?php
     *
     *      $projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
     *      chdir(__DIR__ . '/' . $projectDirPath);
     *      require_once './BreakpointDebugging_Inclusion.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      // Makes up code coverage report, then displays in browser.
     *      B::displayCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php');
     *      B::displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php'));
     *          .
     *          .
     *          .
     * @codeCoverageIgnore
     * Because "phpunit" command cannot run during "phpunit" command running.
     */
    static function displayCodeCoverageReport($unitTestFilePath, $classFilePaths, $commandLineSwitches = '')
    {
        if (!B::checkDevelopmentSecurity()) {
            B::exitForError();
        }

        B::assert(func_num_args() === 2, 1);
        B::assert(is_string($unitTestFilePath), 2);
        B::assert(is_string($classFilePaths) || is_array($classFilePaths), 3);

        echo file_get_contents('BreakpointDebugging/css/FontStyle.html', true);

        if (!extension_loaded('xdebug')) {
            B::exitForError('"BreakpointDebugging::displayCodeCoverageReport()" needs "xdebug" extention.');
        }
        $codeCoverageReportPath = B::getStatic('$_workDir') . '/CodeCoverageReport/';
        // Deletes code coverage report directory files.
        if (is_dir($codeCoverageReportPath)) {
            foreach (scandir($codeCoverageReportPath) as $codeCoverageReportDirElement) {
                $errorLogDirElementPath = $codeCoverageReportPath . $codeCoverageReportDirElement;
                if (is_file($errorLogDirElementPath)) {
                    // Deletes a file.
                    B::unlink(array ($errorLogDirElementPath));
                }
            }
        }

        self::_getUnitTestDir();
        // Creates code coverage report.
        $displayErrorsStorage = ini_get('display_errors');
        ini_set('display_errors', '');
        echo '<pre><b>';
        self::_runPHPUnitCommand($commandLineSwitches . ' --static-backup --coverage-html ' . $codeCoverageReportPath . ' ' . $unitTestFilePath);
        echo '</b></pre>';
        ini_set('display_errors', $displayErrorsStorage);
        // Displays the code coverage report in browser.
        self::$_classFilePaths = $classFilePaths;
        self::$_codeCoverageReportPath = $codeCoverageReportPath;
        include_once './BreakpointDebugging_DisplayCodeCoverageReport.php';
        exit;
    }

}

?>
