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
     * @var  string Unit test directory.
     */
    protected static $unitTestDir;

    /**
     * @var mixed It is relative path of class which see the code coverage, and its current directory must be project directory.
     */
    private static $_classFilePaths;

    /**
     * @var string The code coverage report directory.
     */
    private static $_codeCoverageReportDir;

    /**
     * @var string Font style.
     */
    private static $_style;

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

        self::$staticProperties['$_classFilePaths'] = &self::$_classFilePaths;
        self::$staticProperties['$_codeCoverageReportDir'] = &self::$_codeCoverageReportDir;
        parent::$staticPropertyLimitings['$_includePaths'] = '';
        parent::$staticPropertyLimitings['$_valuesToTrace'] = '';
        parent::$staticPropertyLimitings['$exeMode'] = 'BreakpointDebugging/UnitTestOverriding.php';
        self::$_style = <<<EOD
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css">
            <!--
            body
            {
                background-color: #fff;
                color: #2e3436;
                font-size: 1em;
                font-weight: bold;
                margin: 0 auto;
                width: 100%;
            }
            -->
        </style>
    </head>
    <body>
EOD;
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
            || strripos($callStack[1]['file'], 'UnitTestOverriding.php') === strlen($callStack[1]['file']) - strlen('UnitTestOverriding.php')
        ) {
            B::iniSet('xdebug.var_display_max_depth', '5', false);
            var_dump($pException);
            exit;
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
     * @return string Unit test directory.
     */
    private static function _getUnitTestDir()
    {
        B::assert(!isset(self::$unitTestDir));

        $unitTestCurrentDir = debug_backtrace();
        $unitTestCurrentDir = dirname($unitTestCurrentDir[1]['file']) . DIRECTORY_SEPARATOR;
        if (B::getStatic('$_os') === 'WIN') { // In case of Windows.
            self::$unitTestDir = strtolower($unitTestCurrentDir);
        } else { // In case of Unix.
            self::$unitTestDir = $unitTestCurrentDir;
        }

        return self::$unitTestDir;
    }

    //////////////////////////////////////// For package user ////////////////////////////////////////
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
     *      chdir(__DIR__ . '/../../');
     *      require_once './BreakpointDebugging_Including.php';
     *
     *      use \BreakpointDebugging as B;
     *
     *      B::isUnitTestExeMode(true);
     *
     *      class SomethingTest extends \BreakpointDebugging_UnitTestOverriding
     *      {
     *          .
     *          .
     *          .
     */
    static function isUnitTestExeMode($isUnitTest = false)
    {
        if (func_num_args() === 0) {
            echo '<pre>You must not set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'..._UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>';
            B::$exeMode |= B::IGNORING_BREAK_POINT;
            throw new \BreakpointDebugging_ErrorException('', 101);
        }
        B::assert(is_bool($isUnitTest), 1);

        if (!$isUnitTest) {
            echo '<pre>You must not set "$_BreakpointDebugging_EXE_MODE = BreakpointDebugging_setExecutionModeFlags(\'..._UNIT_TEST\');" into "' . BREAKPOINTDEBUGGING_PEAR_SETTING_DIR_NAME . 'BreakpointDebugging_MySetting.php".</pre>';
            B::$exeMode |= B::IGNORING_BREAK_POINT;
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
        $commandElements = explode(' ', $command);
        $testFileName = array_pop($commandElements);
        array_push($commandElements, self::$unitTestDir . $testFileName);
        array_unshift($commandElements, 'dummy');
        include_once 'PHPUnit/Autoload.php';
        $pPHPUnit_TextUI_Command = new \BreakpointDebugging_PHPUnitTextUICommand;
        // Uses "PHPUnit" error handler.
        restore_error_handler();
        echo '<pre>';
        $pPHPUnit_TextUI_Command->run($commandElements, true);
        echo '</pre>';
        // Uses my error handler.
        set_error_handler('\BreakpointDebugging::handleError', -1);
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
     *                                Debugs its unit test file if array element is one.
     *                                Does continuation unit tests if array element is more than one.
     * @param bool  $runByCommand     Runs by command or runs by IDE?
     *                                You must specify false if you want step execution by IDE.
     *                                Then, we must use private static property instead of using local static variable because unit tests run on same processes, if this is false.
     *                                Then, we must use "\BreakpointDebugging::setPropertyForTest()" to restore value after unit test class method was run.
     *
     * @return void
     *
     * @Example page which runs unit tests.
     * <?php
     *
     * $projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
     * chdir(__DIR__ . '/' . $projectDirPath);
     * require_once './BreakpointDebugging_Including.php';
     *
     * use \BreakpointDebugging as B;
     *
     * B::isUnitTestExeMode(true);
     *
     * // Please, choose unit tests files by customizing.
     * // You must specify array element to one if you want step execution.
     * $unitTestCommands = array (
     *     '--stop-on-failure SomethingTest.php',
     *     '--stop-on-failure Something/SubTest.php',
     * );
     *
     * // Executes unit tests.
     * B::executeUnitTest($unitTestCommands); exit;
     *
     * ?>
     *
     * @Example of unit test file.
     * <?php
     *
     * chdir(__DIR__ . '/../../');
     *
     * require_once './BreakpointDebugging_Including.php';
     *
     * use \BreakpointDebugging as B;
     * use \BreakpointDebugging_UnitTestOverridingBase as BU;
     *
     * B::isUnitTestExeMode(true);
     *
     * class SomethingTest extends \BreakpointDebugging_UnitTestOverriding
     * {
     *     private $_pSomething;
     *
     *     static function setUpBeforeClass()
     *     {
     *          parent::setUpBeforeClass();
     *          ...;
     *     }
     *
     *     protected function setUp()
     *     {
     *          parent::setUp();
     *          // Constructs instance.
     *          $this->_pSomething = new \Something();
     *     }
     *
     *     protected function tearDown()
     *     {
     *          // Destructs instance.
     *          $this->_pSomething = null;
     *          parent::tearDown();
     *     }
     *
     *     /*
     *      * @expectedException        \BreakpointDebugging_ErrorException
     *      * @expectedExceptionMessage CLASS=SomethingTest FUNCTION=testSomething_A ID=101
     *      *
     *     function testSomething_A()
     *     {
     *          BU::$exeMode |= B::IGNORING_BREAK_POINT;
     *          throw new \Something_ErrorException('Something message.', 101);
     *     }
     *
     *     function testSomething_B()
     *     {
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
    static function executeUnitTest($unitTestCommands, $runByCommand = false)
    {
        B::checkSecurity(B::UNIT_TEST);
        echo self::$_style;
        $separator = '<pre>//////////////////////////////////////////////////////////////////////////' . PHP_EOL;

        if (parent::$exeMode & B::RELEASE) {
            echo '<pre>\'RELEASE_UNIT_TEST\' execution mode.</pre>';
        } else {
            echo '<pre>\'DEBUG_UNIT_TEST\' execution mode.</pre>';
        }

        B::assert(func_num_args() <= 2, 1);
        B::assert(is_array($unitTestCommands), 2);
        B::assert(!empty($unitTestCommands), 3);

        $unitTestCurrentDir = self::_getUnitTestDir();
        if ($runByCommand) {
            echo '<pre>Starts unit tests by command.</pre>';
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
            foreach ($unitTestCommands as $command) {
                $commandElements = explode(' ', $command);
                $testFileName = array_pop($commandElements);
                $commandOptions = implode(' ', $commandElements);
                // If test file name contains '_'.
                if (strpos($testFileName, '_') !== false) {
                    echo "You must change its array element and its file name into '-' because '$testFileName' contains '_'." . PHP_EOL;
                    if (B::getXebugExists()
                        && !(B::getStatic('$exeMode') & B::IGNORING_BREAK_POINT)
                    ) {
                        // @codeCoverageIgnoreStart
                        xdebug_break();
                    }
                    // @codeCoverageIgnoreEnd
                    return;
                }
                echo $separator;
                echo "Runs \"phpunit $command\" command." . PHP_EOL;
                // Runs unit test command.
                echo `"$phpunit" $commandOptions "$unitTestCurrentDir$testFileName"`;
                echo '</pre>';
            }
        } else {
            echo '<pre>Starts unit tests by IDE.</pre>';
            foreach ($unitTestCommands as $command) {
                echo $separator;
                echo "Runs \"phpunit $command\" command.";
                self::_runPHPUnitCommand($command);
                echo '</pre>';
            }
        }
        echo $separator;
        echo 'Unit tests have done.</pre>';
    }

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
     *      $projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
     *      chdir(__DIR__ . '/' . $projectDirPath);
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
     * @codeCoverageIgnore
     * Because "phpunit" command cannot run during "phpunit" command running.
     */
    static function displayCodeCoverageReport($unitTestFilePath, $classFilePaths)
    {
        B::assert(func_num_args() === 2, 1);
        B::assert(is_string($unitTestFilePath), 2);
        B::assert(is_string($classFilePaths) || is_array($classFilePaths), 3);

        echo self::$_style;
        $codeCoverageReportDir = 'CodeCoverageReport';
        $workDir = B::getStatic('$_workDir');
        self::_getUnitTestDir();

        $displayErrorsStoring = ini_get('display_errors');
        ini_set('display_errors', '');
        self::_runPHPUnitCommand("--coverage-html $workDir/$codeCoverageReportDir $unitTestFilePath");
        ini_set('display_errors', $displayErrorsStoring);

        self::$_classFilePaths = $classFilePaths;
        self::$_codeCoverageReportDir = $workDir . '/' . $codeCoverageReportDir;
        require_once __DIR__ . '/BreakpointDebugging/DisplayCodeCoverageReport.php';
        exit;
    }

}

?>
