<?php

// Changes current directory to web root.
chdir('../../');
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;

B::checkExeMode(true);
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Please, choose unit tests files by customizing.
$breakpointDebugging_UnitTestFiles = array (
    'sub/ExampleTest.php',
    'PHPUnit1Test.php',
    'PHPUnit2Test.php',
);

// Specifies the test directory.
BU::setTestDir('../../../Plugin/WasaPhpUnit/Test/Case/');
// Executes unit tests.
BU::executeUnitTest($breakpointDebugging_UnitTestFiles); exit;

// Makes up code coverage report, then displays in browser.
if (B::isDebug()) { // In case of debug.
    // BU::displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_InDebug.php'); exit;
} else { // In case of release.
    // BU::displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging.php'); exit;
}
// In case of debug or release.
// BU::displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAROtherPackage/BreakpointDebugging_PHPUnit.php'); exit;
