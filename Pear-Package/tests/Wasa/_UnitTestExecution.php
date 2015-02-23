<?php

chdir('../../');
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;

B::checkExeMode(true);
$breakpointDebugging_PHPUnit = new \BreakpointDebugging_PHPUnit();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$testDir = '../../../Plugin/WasaPhpUnit/Test/Case/';
// Please, choose unit tests files by customizing.
$breakpointDebugging_UnitTestFiles = array (
    $testDir . 'ExampleTest.php',
    $testDir . 'PHPUnit1Test.php',
    $testDir . 'PHPUnit2Test.php',
);

// Executes unit tests.
$breakpointDebugging_PHPUnit->executeUnitTest($breakpointDebugging_UnitTestFiles); exit;
//
// Makes up code coverage report, then displays in browser.
if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release.
    // $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging.php'); exit;
} else { // In case of debug.
    // $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_InDebug.php'); exit;
}
// In case of debug or release.
// $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAROtherPackage/BreakpointDebugging_PHPUnit.php'); exit;
