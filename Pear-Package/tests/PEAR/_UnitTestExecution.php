<?php

chdir(str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2));
unset($matches);

require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;

B::checkExeMode(true);
$breakpointDebuggingPHPUnit = new \BreakpointDebugging_PHPUnit();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Please, choose unit tests files by customizing.
$breakpointDebugging_UnitTestFiles = array (
    'ExampleTest.php',
    'PHPUnit1Test.php',
    'PHPUnit2Test.php',
    'BreakpointDebugging-ExceptionTest.php',
    'BreakpointDebugging-InAllCaseTest.php',
    'BreakpointDebuggingTest.php',
    'BreakpointDebugging/Error-InAllCaseTest.php',
    'BreakpointDebugging/ErrorTest.php',
    'BreakpointDebugging/LockByFileExistingTest.php',
    'BreakpointDebugging/LockByFlockTest.php',
    'BreakpointDebugging/LockByShmopTest.php',
    'BreakpointDebugging/OverrideClassTest.php',
);

// Executes unit tests.
// $breakpointDebuggingPHPUnit->executeUnitTest($breakpointDebugging_UnitTestFiles); exit;
//
// Makes up code coverage report, then displays in browser.
if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release.
    // $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging.php'); exit; // "BreakpointDebugging", "BreakpointDebugging_Middle" class is ? (Windows).
    // $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/Error-InAllCaseTest.php', 'PEAR/BreakpointDebugging/Error.php'); exit; //
} else { // In case of debug.
    // $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_InDebug.php'); exit; //
    // $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/ErrorTest.php', 'PEAR/BreakpointDebugging/Error_InDebug.php'); exit; //
}
// In case of debug or release.
// $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAROtherPackage/BreakpointDebugging_PHPUnit.php'); exit;
// $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php'); exit; //
// $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php')); exit; // OK.
// $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/LockByFlockTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFlock.php')); exit; // OK.
// $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/LockByShmopTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByShmop.php')); exit; //
// $breakpointDebuggingPHPUnit->displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php'); exit; //
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Please, choose unit tests files by customizing.
$breakpointDebugging_UnitTestFiles = array (
    'Example2Test.php',
    'BreakpointDebugging-PHPUnitTest.php',
);

// Executes unit tests of unit test code.
$breakpointDebuggingPHPUnit->executeUnitTestSimple($breakpointDebugging_UnitTestFiles); exit;

$breakpointDebuggingPHPUnit->displayCodeCoverageReportSimple('BreakpointDebugging-PHPUnitTest.php', 'BreakpointDebugging_PHPUnit.php'); exit;
