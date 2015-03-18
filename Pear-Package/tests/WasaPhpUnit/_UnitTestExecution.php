<?php

// Changes current directory to web root.
chdir('../../');
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;

B::checkExeMode(true);
$breakpointDebugging_PHPUnit = new \BreakpointDebugging_PHPUnit();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Please, choose unit tests files by customizing.
$breakpointDebugging_UnitTestFiles = array (
    'sub/ExampleTest.php',
    'PHPUnit1Test.php',
    'PHPUnit2Test.php',
);

// Specifies the test directory.
$breakpointDebugging_PHPUnit->setTestDir('../../../Plugin/WasaPhpUnit/Test/Case/');
// Executes unit tests.
$breakpointDebugging_PHPUnit->executeUnitTest($breakpointDebugging_UnitTestFiles); exit;

// Makes up code coverage report, then displays in browser.
if (B::isDebug()) { // In case of debug.
    // $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_InDebug.php'); exit;
} else { // In case of release.
    // $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging.php'); exit;
}
// In case of debug or release.
// $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAROtherPackage/BreakpointDebugging_PHPUnit.php'); exit;
