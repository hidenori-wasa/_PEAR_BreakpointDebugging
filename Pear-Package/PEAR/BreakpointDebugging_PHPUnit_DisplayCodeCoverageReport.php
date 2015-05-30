<?php

/**
 * Class to display code coverage report.
 *
 * LICENSE:
 * Copyright (c) 2013-, Hidenori Wasa
 * All rights reserved.
 *
 * License content is written in "PEAR/BreakpointDebugging/BREAKPOINTDEBUGGING_LICENSE.txt".
 *
 * @category PHP
 * @package  BreakpointDebugging_PHPUnit
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://opensource.org/licenses/mit-license.php  MIT License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging_PHPUnit
 */
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_Window as BW;

if (!B::checkDevelopmentSecurity()) {
    exit;
}

B::limitAccess('BreakpointDebugging_PHPUnit.php');
/**
 * Class to display code coverage report.
 *
 * PHP version 5.3.2-5.4.x
 *
 * Runs security check of developer's IP, the request protocol and so on.
 * Then, displays the code coverage report inside "700" permission directory
 * by embedding its content
 * if cascading style sheet file path exists in code coverage report file.
 *
 * @category PHP
 * @package  BreakpointDebugging_PHPUnit
 * @author   Hidenori Wasa <public@hidenori-wasa.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD 2-Clause
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/BreakpointDebugging_PHPUnit
 *
 * @codeCoverageIgnore
 * Because "phpunit" command cannot run during "phpunit" command running.
 */
class BreakpointDebugging_PHPUnit_DisplayCodeCoverageReport
{

    /**
     * Displays the code coverage report in browser.
     *
     * @return void
     */
    function __construct()
    {
        if (isset($_GET['codeCoverageReportDeletion'])) {
            \BreakpointDebugging_PHPUnit::deleteCodeCoverageReport();
            // Closes this window.
            BW::close(__CLASS__);
        } else if (isset($_GET['codeCoverageReportPath'])) { // If we pushed "Code coverage report" button.
            $codeCoverageReportPath = $_GET['codeCoverageReportPath'];
            // Opens code coverage report.
            $pFile = B::fopen(array ($codeCoverageReportPath, 'rb'));
            while (!feof($pFile)) {
                $line = fgets($pFile);
                // Outputs raw data after that if header ends.
                if (preg_match('`</head[[:^alpha:]]`xXi', $line)) {
                    echo $line;
                    break;
                }

                $matches = array ();
                // Embeds its content if cascading style sheet file path exists.
                if (preg_match_all('`(.*) (<link [[:blank:]] .* href [[:blank:]]* = [[:blank:]]* "(.* style\.css)" [[:blank:]]* >)`xXiU', $line, $matches)) {
                    $lastStrlen = 0;
                    for ($count = 0; $count < count($matches[1]); $count++) {
                        echo $matches[1][$count];
                        $cssFilePath = dirname($codeCoverageReportPath) . '/' . $matches[3][$count];
                        if (is_file($cssFilePath)) {
                            echo '<style type="text/css">' . PHP_EOL
                            . '<!--' . PHP_EOL;
                            // echo preg_replace('`([^_[:alnum:]] (td [[:space:]]* \. (?!title) [_[:alnum:]]+ | pre [[:space:]]* \.source) [[:space:]]* { .*) }`xXisU', "\${1}" . '  font-size: 16px;' . PHP_EOL . '}', file_get_contents($cssFilePath));
                            readfile('BreakpointDebugging/css/PHP_CodeCoverage_Report_HTML_Renderer_Template_style.css', true);
                            echo '-->' . PHP_EOL
                            . '</style>' . PHP_EOL;
                        } else {
                            echo $matches[2][$count];
                        }
                        $lastStrlen += strlen($matches[0][$count]);
                    }
                    $line = substr($line, $lastStrlen);
                }
                echo $line;
            }
            while (!feof($pFile)) {
                $line = rtrim(fgets($pFile));
                // "hear document" and "Nowdoc" does not support.
                echo preg_replace('`(^<span\x20class="lineNum" .* </span>) (.* (@codeCoverageIgnore | @codeCoverageIgnoreStart | @codeCoverageIgnoreEnd) [[:blank:]]* $)`xXi', '${1}<span class="annotation">${2}</span>', $line) . PHP_EOL;
            }
            fclose($pFile);
            return;
        } else { // In case of first time when this page was called.
            ob_start();

            $classFilePaths = B::getStatic('$_classFilePaths');
            $thisFileURI = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 1) . substr(str_replace('\\', '/', __FILE__), strlen($_SERVER['DOCUMENT_ROOT']) + 1);
            if (!is_array($classFilePaths)) {
                $classFilePaths = array ($classFilePaths);
            }
            $fontStyle = 'style="font-size: 25px; font-weight: bold;"';
            // Makes the "Code coverage report" buttons.
            foreach ($classFilePaths as $classFilePath) {
                $classFileName = str_replace(array ('/', '\\'), '_', $classFilePath);
                $codeCoverageReportPath = str_replace('\\', '/', B::getStatic('$_codeCoverageReportPath')) . $classFileName . '.html';
                if (!is_file($codeCoverageReportPath)) {
                    echo <<<EOD
		<form>
			<input type="submit" value="Code coverage report of ($classFilePath)." disabled="disabled" $fontStyle/>
		</form>
		<br/>

EOD;
                    continue;
                }

                $queryString = B::httpBuildQuery(array ('codeCoverageReportPath' => $codeCoverageReportPath));
                echo <<<EOD
		<form method="post" action="$thisFileURI?$queryString">
			<input type="submit" value="Code coverage report of ($classFilePath)." $fontStyle/>
		</form>
		<br/>

EOD;
            }

            $queryString = B::httpBuildQuery(array ('codeCoverageReportDeletion' => true));
            echo <<<EOD
		<br/>
		<br/>
		<form method="post" action="$thisFileURI?$queryString">
			<input type="submit" value="Code coverage report deletion." $fontStyle/>
		</form>
EOD;

            $buffer = ob_get_clean();
            $htmlFileContent = <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>CodeCoverageReport</title>
	</head>
	<body style="background-color: black; color: white; font-size: 25px">
$buffer
	</body>
</html>

EOD;
            BW::virtualOpen(__CLASS__, $htmlFileContent);
        }
    }

}

new \BreakpointDebugging_PHPUnit_DisplayCodeCoverageReport();

?>
