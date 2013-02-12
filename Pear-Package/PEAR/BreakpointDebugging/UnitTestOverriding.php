<?php

/**
 * (Debugs one unit test file, or tests the all unit test files) by "\BreakpointDebugging::executeUnitTest()" class method.
 *
 * This class extends "PHPUnit_Framework_TestCase".
 * This class does not use "phpunit" command to debug its unit test file
 * if array parameter element of "\BreakpointDebugging::executeUnitTest()" is one.
 * Therefore, we can execute unit test with remote server without installing "PHPUnit".
 * Please, add following into "\PHPUnit_Framework_TestCase" class.
 *      function __get($propertyName)
 *      {
 *          return $this->$propertyName;
 *      }
 *
 *      function __set($propertyName, $value)
 *      {
 *          \BreakpointDebugging::limitAccess('BreakpointDebugging/UnitTestOverriding.php', true);
 *          $this->$propertyName = $value;
 *      }
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
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

if (isset($_SERVER['SERVER_ADDR'])) { // In case of not command.
    /**
     * Debugs one unit test file by "B::executeUnitTest()" class method.
     * Notice:  You must code one array element comment which hands to "B::executeUnitTest()" before you execute this mode.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    class BreakpointDebugging_UnitTestOverriding extends \PHPUnit_Framework_TestCase
    {
        /**
         * Overrides "\PHPUnit_Framework_TestCase::runBare()" to display call stack when error occurred.
         *
         * @return void
         */
        public function runBare()
        {
            $this->numAssertions = 0;

            // Backup the $GLOBALS array and static attributes.
            if ($this->runTestInSeparateProcess !== TRUE
                && $this->inIsolation !== TRUE
            ) {
                if ($this->backupGlobals === NULL
                    || $this->backupGlobals === TRUE
                ) {
                    PHPUnit_Util_GlobalState::backupGlobals($this->backupGlobalsBlacklist);
                }

                if (version_compare(PHP_VERSION, '5.3', '>')
                    && $this->backupStaticAttributes === TRUE
                ) {
                    PHPUnit_Util_GlobalState::backupStaticAttributes($this->backupStaticAttributesBlacklist);
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
                B::exceptionHandler($e); // Displays error call stack information.
                exit;
            } catch (Exception $e) {
                B::exceptionHandler($e); // Displays error call stack information.
                exit;
            }

            // Tear down the fixture. An exception raised in tearDown() will be
            // caught and passed on when no exception was raised before.
            try {
                $this->tearDown();
                if ($this->inIsolation) {
                    $this->tearDownAfterClass();
                }
            } catch (Exception $_e) {
                B::exceptionHandler($e); // Displays error call stack information.
                exit;
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

            // Restore the $GLOBALS array and static attributes.
            if ($this->runTestInSeparateProcess !== TRUE
                && $this->inIsolation !== TRUE
            ) {
                if ($this->backupGlobals === NULL
                    || $this->backupGlobals === TRUE
                ) {
                    PHPUnit_Util_GlobalState::restoreGlobals($this->backupGlobalsBlacklist);
                }

                if (version_compare(PHP_VERSION, '5.3', '>')
                    && $this->backupStaticAttributes === TRUE
                ) {
                    PHPUnit_Util_GlobalState::restoreStaticAttributes();
                }
            }

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
                    echo '<pre><b>It is error if this test has been not using "@expectedException" annotation, or it requires "@expectedException" annotation.</b></pre>';
                    B::exceptionHandler($e); // Displays error call stack information.
                    exit;
                }
                // "@expectedException" annotation should be success.
                try {
                    $this->assertThat($e, new PHPUnit_Framework_Constraint_Exception($this->getExpectedException()));
                } catch (Exception $dummy) {
                    echo '<pre><b>Is error, or this test mistook "@expectedException" annotation value.</b></pre>';
                    B::exceptionHandler($e); // Displays error call stack information.
                    exit;
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
                    echo '<pre><b>Is error, or this test mistook "@expectedExceptionMessage" annotation value.</b></pre>';
                    B::exceptionHandler($e); // Displays error call stack information.
                    exit;
                }
                // "@expectedExceptionCode" annotation should be success.
                try {
                    if ($this->expectedExceptionCode !== NULL) {
                        $this->assertThat($e, new PHPUnit_Framework_Constraint_ExceptionCode($this->expectedExceptionCode));
                    }
                } catch (Exception $dummy) {
                    echo '<pre><b>Is error, or this test mistook "@expectedExceptionCode" annotation value.</b></pre>';
                    B::exceptionHandler($e); // Displays error call stack information.
                    exit;
                }
                return;
            }
            // In case of success.
            if ($this->getExpectedException() !== NULL) {
                // "@expectedException" should not exist.
                echo '<pre>Is error in "' . $class->name . '::' . $name . '".</pre>';
                $this->assertThat(NULL, new PHPUnit_Framework_Constraint_Exception($this->getExpectedException()));
            }

            return $testResult;
        }

        /**
         * Sets up initializing which is needed at least in unit test.
         *
         * @return void
         */
        protected function setUp()
        {
            $onceErrorDispFlag = &B::refStatic('$_onceErrorDispFlag');
            $onceErrorDispFlag = false;
            $callingExceptionHandlerDirectly = &B::refStatic('$_callingExceptionHandlerDirectly');
            $callingExceptionHandlerDirectly = false;
        }

        /**
         * Overrides "\PHPUnit_Framework_Assert::assertTrue()" to display error call stack information.
         *
         * @param bool   $condition Conditional expression.
         * @param string $message   Error message.
         *
         * @return void
         */
        static function assertTrue($condition, $message = '')
        {
            B::assert(is_bool($condition), 1);
            B::assert(is_string($message), 2);

            try {
                parent::assertTrue($condition, $message);
            } catch (\Exception $e) {
                B::exceptionHandler($e); // Displays error call stack information.
                exit;
            }
        }

        /**
         * Overrides "\PHPUnit_Framework_Assert::fail()" to display error call stack information.
         *
         * @param string $message The fail message.
         *
         * @return void
         * @throws PHPUnit_Framework_AssertionFailedError
         */
        public static function fail($message = '')
        {
            B::assert(is_string($message), 1);

            try {
                parent::fail($message);
            } catch (\Exception $e) {
                B::exceptionHandler($e); // Displays error call stack information.
                exit;
            }
        }

    }

} else { // In case of command.
    /**
     * Tests the all unit test files by "B::executeUnitTest()" class method.
     * Notice:  You must code all array element comment which hands to "B::executeUnitTest()" before you execute this mode.
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    class BreakpointDebugging_UnitTestOverriding extends \PHPUnit_Framework_TestCase
    {
        /**
         * Sets up initializing which is needed at least in unit test.
         *
         * @return void
         */
        protected function setUp()
        {
            $onceErrorDispFlag = &B::refStatic('$_onceErrorDispFlag');
            $onceErrorDispFlag = false;
            $callingExceptionHandlerDirectly = &B::refStatic('$_callingExceptionHandlerDirectly');
            $callingExceptionHandlerDirectly = false;
        }

    }

}

?>
