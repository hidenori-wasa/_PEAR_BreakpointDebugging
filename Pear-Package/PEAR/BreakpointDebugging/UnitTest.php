<?php

/**
 * Unit test step execution by extending instead of "PHPUnit_Framework_TestCase".
 *
 * This class is like "PHPUnit_Framework_TestCase".
 * However, executes a test class method in turn only.
 * Therefore, cannot use extension feature like annotation.
 * Also, uses few memory because doesn't create objects of test class methods at once.
 * Therefore, is easy to execute with remote server.
 *
 * @example of usage.
 *      class SomethingTest extends \BreakpointDebugging_UnitTest // For step execution.
 *      {
 *          protected $pSomething;
 *
 *          protected function setUp()
 *          {
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
 *          function testSomething()
 *          {
 *              try {
 *                  BreakpointDebugging_UnitTest::registerAssertionFailureLocationOfUnitTest('TargetClassName', 'TargetClassMethodName');
 *                  $this->pSomething->Something(); // Error.
 *              } catch (\BreakpointDebugging_UnitTest_Exception $e) {
 *                  return;
 *              }
 *              $this->assertTrue(false);
 *          }
 *      }
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
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestAssert as U;

if (false) {

    /**
     * Class which does unit test step execution by extending instead of "PHPUnit_Framework_TestCase".
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    class BreakpointDebugging_UnitTest
    {
        /**
         * Executes a test class method in turn only.
         */
        final function __construct()
        {
            // Parses a class object which extended this class.
            $pClassReflection = new ReflectionClass($this);
            $pMethodReflections = $pClassReflection->getMethods();
            // Takes out "setUp()" and "tearDown()" class method.
            foreach ($pMethodReflections as $pMethodReflection) {
                if ($pMethodReflection->name === 'setUp') {
                    $pSetUp = $pMethodReflection;
                } else if ($pMethodReflection->name === 'tearDown') {
                    $pTearDown = $pMethodReflection;
                }
            }
            // Calls in test class method in turn.
            foreach ($pMethodReflections as $pMethodReflection) {
                // Skips class method except test class method.
                if (substr_compare($pMethodReflection->name, 'test', 0, 4, true)) {
                    continue;
                }
                if (isset($pSetUp)) {
                    // Calls in "setUp()" class method.
                    $pSetUp->invoke($this);
                }
                // Calls in test class method.
                $pMethodReflection->invoke($this);
                if (isset($pTearDown)) {
                    // Calls in "tearDown()" class method.
                    $pTearDown->invoke($this);
                }
            }
        }

        /**
         * Asserts conditional expression.
         *
         * @param bool $expression Conditional expression.
         *
         * @return void
         */
        final function assertTrue($expression)
        {
            global $_BreakpointDebugging_EXE_MODE;

            assert(is_bool($expression));

            if (!$expression) {
                U::displayErrorCallStack('"assertTrue()" was failed.');
            }
        }

    }

} else if (true) {

    /**
     * Class which does unit test continuation execution by extending "PHPUnit_Framework_TestCase".
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    class BreakpointDebugging_UnitTest extends \PHPUnit_Framework_TestCase
    {

    }

} else {
    U::displayErrorCallStack('Undefined constant number.');
}

?>
