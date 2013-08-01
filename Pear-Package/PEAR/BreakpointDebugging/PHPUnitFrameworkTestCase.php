<?php

/**
 * Debugs unit tests code continuously by IDE. With "\BreakpointDebugging::executeUnitTest()" class method. Supports "php" version 5.3.0 since then.
 *
 * This class extends "PHPUnit_Framework_TestCase".
 * Also, we can execute unit test with remote server without installing "PHPUnit".
 * Please, add following into "\PHPUnit_Framework_TestCase" class.
 *      function __get($propertyName)
 *      {
 *          return $this->$propertyName;
 *      }
 *
 *      function __set($propertyName, $value)
 *      {
 *          \BreakpointDebugging::limitAccess('BreakpointDebugging/PHPUnitFrameworkTestCase.php', true);
 *          $this->$propertyName = $value;
 *      }
 *
 * PHP version 5.3
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   PHP
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnitUtilGlobalState as BGS;

/**
 * Debugs unit tests code continuously by IDE. With "\BreakpointDebugging::executeUnitTest()" class method. Supports "php" version 5.3.0 since then.
 *
 * @category   PHP
 * @package    PHPUnit
 * @subpackage Framework
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: 3.6.11
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
abstract class BreakpointDebugging_PHPUnitFrameworkTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var bool Once flag of "splAutoloadRegister()".
     */
    private static $_splAutoloadRegisterOnceFlag = true;

    /**
     * @var string The test class method name.
     */
    private static $_testMethodName;

    /**
     * @var array List to except to store global variable.
     */
    private static $_backupGlobalsBlacklist;

    /**
     * @var array List to except to store static property.
     */
    private static $_backupStaticAttributesBlacklist;

    /**
     * @var int The output buffering level.
     */
    private $_obLevel;

    /**
     * Stores static status inside autoload handler because static status may be changed.
     *
     * @param string $className the included class name
     *                          Or, the class name which calls class member of static.
     *                          Or, the class name which creates new instance.
     *                          Or, the class name when extends base class.
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    static function autoload($className)
    {
        static $nestLevel = 0;

        // If class file has been loaded first.
        if ($nestLevel === 0) {
            // Checks justice.
            BGS::checkGlobals(self::$_testMethodName);
            BGS::checkStaticAttributes();
        }
        $nestLevel++;
        B::autoload($className);
        $nestLevel--;
        // If class file has been loaded completely including dependency files.
        if ($nestLevel === 0) {
            // Stores the "$GLOBALS" and static attributes before variable value is changed.
            BGS::backupGlobals(self::$_backupGlobalsBlacklist);
            BGS::backupStaticAttributes(self::$_backupStaticAttributesBlacklist);
        }
    }

    /**
     * This method is called before a test class method is executed.
     * Sets up initializing which is needed at least in unit test.
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    protected function setUp()
    {
        // Unlinks internal synchronization file.
        $internalLockFilePath = B::getStatic('$_workDir') . '/LockByFileExistingOfInternal.txt';
        if (is_file($internalLockFilePath)) {
            B::unlink(array ($internalLockFilePath));
        }
        // Stores the output buffering level.
        $this->_obLevel = ob_get_level();
    }

    /**
     * This method is called after a test class method is executed.
     * Cleans up environment which is needed at least in unit test.
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    protected function tearDown()
    {
        // Restores the output buffering level.
        while (ob_get_level() > $this->_obLevel) {
            ob_end_clean();
        }
    }

    /**
     * Overrides "\PHPUnit_Framework_TestCase::runBare()" to display call stack when error occurred.
     * And, sets autoload class method to store static status by "spl_autoload_register()".
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    public function runBare()
    {
        static $onceFlagPerTestFile = true;

        $this->numAssertions = 0;

        if ($this->backupStaticAttributes) {
            // For autoload.
            self::$_testMethodName = $this->getName();
            if ($onceFlagPerTestFile) {
                $onceFlagPerTestFile = false;
                // For autoload.
                self::$_backupGlobalsBlacklist = $this->backupGlobalsBlacklist;
                self::$_backupStaticAttributesBlacklist = $this->backupStaticAttributesBlacklist;
                // Checks justice of global variables after unit test file is included.
                BGS::checkGlobals();
                // Stores static attributes.
                BGS::backupStaticAttributes(self::$_backupStaticAttributesBlacklist);
            }
            if (self::$_splAutoloadRegisterOnceFlag) {
                self::$_splAutoloadRegisterOnceFlag = false;
                spl_autoload_register('\BreakpointDebugging_PHPUnitFrameworkTestCase::autoload', true, true);
            }
        }

        // Start output buffering.
        ob_start();
        $this->outputBufferingActive = TRUE;

        // Clean up stat cache.
        clearstatcache();

        try {
            if ($this->inIsolation) {
                $this->setUpBeforeClass();
            }
            $this->setExpectedExceptionFromAnnotation();
            $this->setUp();
            $this->checkRequirements();
            $this->assertPreConditions();
            $this->testResult = $this->runTest();
            $this->verifyMockObjects();
            $this->assertPostConditions();
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
        } catch (PHPUnit_Framework_IncompleteTest $e) {
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
            $this->statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_SkippedTest $e) {
            $this->status = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
            $this->statusMessage = $e->getMessage();
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            B::exitForError($e); // Displays error call stack information.
        } catch (Exception $e) {
            B::exitForError($e); // Displays error call stack information.
        }

        // Tear down the fixture. An exception raised in tearDown() will be
        // caught and passed on when no exception was raised before.
        try {
            $this->tearDown();
            if ($this->inIsolation) {
                $this->tearDownAfterClass();
            }
        } catch (Exception $_e) {
            B::exitForError($_e); // Displays error call stack information.
        }

        // Stop output buffering.
        if ($this->outputCallback === FALSE) {
            $this->output = ob_get_contents();
        } else {
            $this->output = call_user_func_array($this->outputCallback, array (ob_get_contents()));
        }

        ob_end_clean();
        $this->outputBufferingActive = FALSE;

        // Clean up stat cache.
        clearstatcache();

        // Checks justice of global variables which had been defined inside unit test method.
        BGS::checkGlobals(self::$_testMethodName);
        // Restores "$GLOBALS" and static attributes if those have been stored.
        BGS::restoreGlobals($this->backupGlobalsBlacklist);
        BGS::restoreStaticAttributes();

        // Clean up INI settings.
        foreach ($this->iniSettings as $varName => $oldValue) {
            ini_set($varName, $oldValue);
        }

        $this->iniSettings = array ();

        // Clean up locale settings.
        foreach ($this->locale as $category => $locale) {
            setlocale($category, $locale);
        }

        // Perform assertion on output.
        if (!isset($e)) {
            try {
                if ($this->outputExpectedRegex !== NULL) {
                    $this->hasPerformedExpectationsOnOutput = TRUE;
                    $this->assertRegExp($this->outputExpectedRegex, $this->output);
                    $this->outputExpectedRegex = NULL;
                } else if ($this->outputExpectedString !== NULL) {
                    $this->hasPerformedExpectationsOnOutput = TRUE;
                    $this->assertEquals($this->outputExpectedString, $this->output);
                    $this->outputExpectedString = NULL;
                }
            } catch (Exception $_e) {
                $e = $_e;
            }
        }

        // Workaround for missing "finally".
        if (isset($e)) {
            $this->onNotSuccessfulTest($e);
        }
    }

    /**
     * Overrides "\PHPUnit_Framework_TestCase::runTest()" to display call stack when annotation failed.
     *
     * @return mixed
     * @throws RuntimeException
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    protected function runTest()
    {
        $name = $this->getName(false);
        if ($name === NULL) {
            throw new PHPUnit_Framework_Exception('PHPUnit_Framework_TestCase::$name must not be NULL.');
        }

        try {
            $class = new ReflectionClass($this);
            $method = $class->getMethod($name);
        } catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }

        try {
            $testResult = $method->invokeArgs($this, array_merge($this->data, $this->dependencyInput));
        } catch (Exception $e) {
            // If "\PHPUnit_Framework_Assert::markTestIncomplete()" was called, or if "\PHPUnit_Framework_Assert::markTestSkipped()" was called.
            if ($e instanceof PHPUnit_Framework_IncompleteTest
                || $e instanceof PHPUnit_Framework_SkippedTest
            ) {
                throw $e;
            }
            // If "@expectedException" annotation is not string.
            if (!is_string($this->getExpectedException())) {
                echo '<b>It is error if this test has been not using "@expectedException" annotation, or it requires "@expectedException" annotation.</b>';
                B::exitForError($e); // Displays error call stack information.
            }
            // "@expectedException" annotation should be success.
            try {
                $this->assertThat($e, new PHPUnit_Framework_Constraint_Exception($this->getExpectedException()));
            } catch (Exception $dummy) {
                echo '<b>Is error, or this test mistook "@expectedException" annotation value.</b>';
                B::exitForError($e); // Displays error call stack information.
            }
            // "@expectedExceptionMessage" annotation should be success.
            try {
                $expectedExceptionMessage = $this->expectedExceptionMessage;
                if (is_string($expectedExceptionMessage)
                    && !empty($expectedExceptionMessage)
                ) {
                    $this->assertThat($e, new PHPUnit_Framework_Constraint_ExceptionMessage($expectedExceptionMessage));
                }
            } catch (Exception $dummy) {
                echo '<b>Is error, or this test mistook "@expectedExceptionMessage" annotation value.</b>';
                B::exitForError($e); // Displays error call stack information.
            }
            // "@expectedExceptionCode" annotation should be success.
            try {
                if ($this->expectedExceptionCode !== NULL) {
                    $this->assertThat($e, new PHPUnit_Framework_Constraint_ExceptionCode($this->expectedExceptionCode));
                }
            } catch (Exception $dummy) {
                echo '<b>Is error, or this test mistook "@expectedExceptionCode" annotation value.</b>';
                B::exitForError($e); // Displays error call stack information.
            }
            return;
        }
        if ($this->getExpectedException() !== NULL) {
            // "@expectedException" should not exist.
            echo '<b>Is error in "' . $class->name . '::' . $name . '".</b>';
            $this->assertThat(NULL, new PHPUnit_Framework_Constraint_Exception($this->getExpectedException()));
        }

        return $testResult;
    }

    /**
     * Overrides "\PHPUnit_Framework_Assert::assertTrue()" to display error call stack information.
     *
     * @param bool   $condition Conditional expression.
     * @param string $message   Error message.
     *
     * @return void
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    static function assertTrue($condition, $message = '')
    {
        B::assert(is_bool($condition), 1);
        B::assert(is_string($message), 2);

        try {
            parent::assertTrue($condition, $message);
        } catch (\Exception $e) {
            B::exitForError($e); // Displays error call stack information.
        }
    }

    /**
     * Overrides "\PHPUnit_Framework_Assert::fail()" to display error call stack information.
     *
     * @param string $message The fail message.
     *
     * @return void
     * @throws PHPUnit_Framework_AssertionFailedError
     * @author Hidenori Wasa <public@hidenori-wasa.com>
     */
    public static function fail($message = '')
    {
        B::assert(is_string($message), 1);

        try {
            parent::fail($message);
        } catch (\Exception $e) {
            B::exitForError($e); // Displays error call stack information.
        }
    }

}

?>
