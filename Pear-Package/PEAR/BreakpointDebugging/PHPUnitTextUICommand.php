<?php

/**
 * The class for running unit test continuously.
 *
 * I stripped "exit()" in case of success unit test because I want to execute continuously.
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
 * @package    PHPUnit
 * @subpackage TextUI
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */
if (isset($_SERVER['SERVER_ADDR'])) { // In case of not command.
    /**
     * I stripped "exit()" in case of success unit test because I want to execute continuously.
     * I hope to do "exit()" outside this parent class method in case of success unit test because this file is not needed.
     *
     * @package    PHPUnit
     * @subpackage TextUI
     * @author     Sebastian Bergmann <sebastian@phpunit.de>
     * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
     * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
     * @version    Release: 3.6.11
     * @link       http://www.phpunit.de/
     * @since      Class available since Release 3.0.0
     */
    class BreakpointDebugging_PHPUnitTextUICommand extends \PHPUnit_TextUI_Command
    {
        /**
         * Runs "phpunit" command. Overrides this for running a unit tests continuously.
         * And, sets autoload class method to store static status by "spl_autoload_register()".
         *
         * @param array   $argv Parameters of "phpunit" command.
         * @param boolean $exit Ends running in case of success?
         *
         * @return int Result status value.
         */
        public function run(array $argv, $exit = TRUE)
        {
            $this->handleArguments($argv);

            $runner = $this->createRunner();

            if (is_object($this->arguments['test']) &&
                $this->arguments['test'] instanceof PHPUnit_Framework_Test) {
                $suite = $this->arguments['test'];
            } else {
                $suite = $runner->getTest(
                    $this->arguments['test'], $this->arguments['testFile']
                );
            }

            if (count($suite) == 0) {
                $skeleton = new PHPUnit_Util_Skeleton_Test(
                        $suite->getName(),
                        $this->arguments['testFile']
                );

                $result = $skeleton->generate(TRUE);

                if (!$result['incomplete']) {
                    eval(str_replace(array ('<?php', '?>'), '', $result['code']));
                    $suite = new PHPUnit_Framework_TestSuite(
                            $this->arguments['test'] . 'Test'
                    );
                }
            }

            if ($this->arguments['listGroups']) {
                PHPUnit_TextUI_TestRunner::printVersionString();

                print "Available test group(s):\n";

                $groups = $suite->getGroups();
                sort($groups);

                foreach ($groups as $group) {
                    print " - $group\n";
                }

                if ($exit) {
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                } else {
                    $ret = PHPUnit_TextUI_TestRunner::SUCCESS_EXIT;
                    goto AFTER_TREATMENT;
                }
            }

            unset($this->arguments['test']);
            unset($this->arguments['testFile']);

            try {
                $result = $runner->doRun($suite, $this->arguments);
            } catch (PHPUnit_Framework_Exception $e) {
                print $e->getMessage() . "\n";
            }

            $ret = PHPUnit_TextUI_TestRunner::FAILURE_EXIT;

            if (isset($result) && $result->wasSuccessful()) {
                $ret = PHPUnit_TextUI_TestRunner::SUCCESS_EXIT;
            } else if (!isset($result) || $result->errorCount() > 0) {
                $ret = PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT;
            }

            AFTER_TREATMENT:
            return $ret;
        }

        /**
         * Prepares to run "phpunit" command.
         *
         * @param array $argv Parameters of "phpunit" command.
         *
         * @throws \BreakpointDebugging_ErrorException
         *
         * @return void
         */
        function prepareRun($argv)
        {
            static $onceFlag = true;

            array_shift($argv);
            // Checks command line switches.
            foreach ($argv as $option) {
                switch ($option) {
                    case '--static-backup':
                        if ($onceFlag) {
                            $onceFlag = false;
                            // Stores the "$GLOBALS" and static attributes.
                            \BreakpointDebugging_PHPUnitUtilGlobalState::backupGlobals(array ());
                            // Unit test preparation end.
                            \BreakpointDebugging_UnitTestCaller::$exeMode &= ~\BreakpointDebugging::UNIT_TEST_PREPARATION;
                            \BreakpointDebugging_PHPUnitUtilGlobalState::backupStaticAttributes(array ());
                        }
                        break;
                    case '--process-isolation':
                        throw new \BreakpointDebugging_ErrorException('You must not use "--process-isolation" command line switch because this unit test is run in other process.' . PHP_EOL . 'So, you cannot debug unit test code with IDE.', 101);
                }
            }
            // Unit test preparation end.
            \BreakpointDebugging_UnitTestCaller::$exeMode &= ~\BreakpointDebugging::UNIT_TEST_PREPARATION;
        }

    }

}

?>
