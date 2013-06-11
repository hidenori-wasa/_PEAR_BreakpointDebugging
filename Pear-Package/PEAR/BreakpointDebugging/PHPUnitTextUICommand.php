<?php

/**
 * ?????
 *
 * ?????
 * ?????
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
if (isset($_SERVER['SERVER_ADDR'])) { // In case of not command.
    /**
     * ?????
     *
     * @category PHP
     * @package  BreakpointDebugging
     * @author   Hidenori Wasa <public@hidenori-wasa.com>
     * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
     * @version  Release: @package_version@
     * @link     http://pear.php.net/package/BreakpointDebugging
     */
    class BreakpointDebugging_PHPUnitTextUICommand extends \PHPUnit_TextUI_Command
    {
        /**
         * @param array   $argv
         * @param boolean $exit
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
                    return PHPUnit_TextUI_TestRunner::SUCCESS_EXIT;
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

            return $ret;
        }

    }

}

?>
