<?php

/**
 * Class to display code coverage report.
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
$projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
chdir(__DIR__ . '/' . $projectDirPath);
require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;

/**
 * Class to display code coverage report.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 */
class BreakpointDebugging_DisplayCodeCoverageReport
{
    /**
     * Displays the code coverage report.
     *
     * @return void
     */
    function __construct()
    {
        // If we pushed "Code coverage report" button.
        if (isset($_GET['classFilePath'])) {
            self::_displayCodeCoverageReport($_GET['classFilePath'], $_GET['codeCoverageReportDir']);
        } else { // In case of first time when this page was called.
            $codeCoverageReportDir = B::getStatic('$_codeCoverageReportDir');
            $classFilePaths = B::getStatic('$_classFilePaths');
            $thisFileName = str_replace('\\', '/', __FILE__);
            $thisFileName = substr($thisFileName, strlen($_SERVER['DOCUMENT_ROOT']) + 1);
            $projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 1);
            $thisFileName = $projectDirPath . $thisFileName;
            if (!is_array($classFilePaths)) {
                $classFilePaths = array ($classFilePaths);
            }
            // Makes the "Code coverage report" buttons.
            foreach ($classFilePaths as $classFilePath) {
                $data = array (
                    'classFilePath' => $classFilePath,
                    'codeCoverageReportDir' => $codeCoverageReportDir,
                );
                $data = http_build_query($data);
                echo <<<EOD
<form method="post" action="$thisFileName?$data">
    <input type="submit" value="Code coverage report of ($classFilePath)."/>
</form>
EOD;
            }
        }
    }

    /**
     * Displays the code coverage report in browser.
     *
     * @param string $classFilePath         Class file path.
     * @param string $codeCoverageReportDir Code coverage report directory.
     *
     * @return void
     * @codeCoverageIgnore
     * Because "phpunit" command cannot run during "phpunit" command running.
     */
    private function _displayCodeCoverageReport($classFilePath, $codeCoverageReportDir)
    {
        B::assert(func_num_args() === 2, 1);
        B::assert(is_string($classFilePath), 2);
        B::assert(is_string($codeCoverageReportDir), 3);

        $classFilePath = str_replace(array ('/', '\\'), '_', $classFilePath);
        $codeCoverageReportDir = str_replace('\\', '/', $codeCoverageReportDir);
        $codeCoverageReportDir = substr($codeCoverageReportDir, strlen($_SERVER['DOCUMENT_ROOT']) + 1);
        $projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 1);
        header("Location: $projectDirPath$codeCoverageReportDir/$classFilePath.html");
    }

}

B::checkSecurity(B::UNIT_TEST);
new \BreakpointDebugging_DisplayCodeCoverageReport();

?>
