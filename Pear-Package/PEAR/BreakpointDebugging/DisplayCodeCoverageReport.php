<?php

/**
 * Class to display code coverage report.
 *
 * Runs security check of developer's IP, the request protocol and so on.
 * Then, displays the code coverage report inside "700" permission directory
 * by embedding its content
 * if cascading style sheet file path exists in code coverage report file.
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

B::checkDevelopmentSecurity();
B::limitAccess('BreakpointDebugging_UnitTestCaller.php');
/**
 * Class to display code coverage report.
 *
 * @category PHP
 * @package  BreakpointDebugging
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging
 * @codeCoverageIgnore
 * Because "phpunit" command cannot run during "phpunit" command running.
 */
class BreakpointDebugging_DisplayCodeCoverageReport
{
    /**
     * Displays the code coverage report in browser.
     *
     * @return void
     */
    function __construct()
    {
        // If we pushed "Code coverage report" button.
        if (isset($_GET['classFilePath'])) {
            $classFileName = str_replace(array ('/', '\\'), '_', $_GET['classFilePath']);
            $codeCoverageReportPath = str_replace('\\', '/', $_GET['codeCoverageReportPath']);
            // Opens code coverage report.
            $pFile = fopen($codeCoverageReportPath . $classFileName . '.html', 'rb');
            while (!feof($pFile)) {
                $line = fgets($pFile);
                // Outputs raw data after that if header ends.
                if (preg_match('`</head[[:^alpha:]]`xXi', $line)) {
                    echo $line;
                    break;
                }

                // $line = '    <link rel="stylesheet" type="text/css" href="style.css">    <link rel="stylesheet" type="text/css" href="other_style.css">'; // For debug.
                $matches = array ();
                // Embeds its content if cascading style sheet file path exists.
                if (preg_match_all('`(.*)(<link[[:blank:]].*href[[:blank:]]*=[[:blank:]]*"(.*\.css)"[[:blank:]]*>)`xXiU', $line, $matches)) {
                    $lastStrlen = 0;
                    $replaceLine = '';
                    for ($count = 0; $count < count($matches[1]); $count++) {
                        echo $matches[1][$count];
                        $cssFilePath = $codeCoverageReportPath . $matches[3][$count];
                        if (file_exists($cssFilePath)) {
                            echo '<style type="text/css">' . PHP_EOL
                            . '<!--' . PHP_EOL;
                            readfile($cssFilePath);
                            echo '-->' . PHP_EOL
                            . '</style>' . PHP_EOL;
                        } else {
                            echo $matches[2][$count];
                        }
                        $lastStrlen += strlen($matches[0][$count]);
                    }
                    echo substr($line, $lastStrlen);
                } else {
                    echo $line;
                }
            }
            fpassthru($pFile);
            fclose($pFile);
        } else { // In case of first time when this page was called.
            $classFilePaths = B::getStatic('$_classFilePaths');
            $thisFileURI = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 1) . substr(str_replace('\\', '/', __FILE__), strlen($_SERVER['DOCUMENT_ROOT']) + 1);
            if (!is_array($classFilePaths)) {
                $classFilePaths = array ($classFilePaths);
            }
            // Makes the "Code coverage report" buttons.
            foreach ($classFilePaths as $classFilePath) {
                $data = array (
                    'classFilePath' => $classFilePath,
                    'codeCoverageReportPath' => B::getStatic('$_codeCoverageReportPath'),
                );
                $data = http_build_query($data);
                echo <<<EOD
<form method="post" action="$thisFileURI?$data">
    <input type="submit" value="Code coverage report of ($classFilePath)."/>
</form>
EOD;
            }
        }
    }

}

new \BreakpointDebugging_DisplayCodeCoverageReport();

?>
