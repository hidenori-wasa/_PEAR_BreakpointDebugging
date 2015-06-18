<?php

// Changes current directory to web root.
chdir('../../');
require_once './BreakpointDebugging_Inclusion.php';
require_once './BreakpointDebugging_ProductionSwitcher.php';
require_once './BreakpointDebugging_IniSetOptimizer.php';

use \BreakpointDebugging as B;

B::checkExeMode(true);
$breakpointDebugging_PHPUnit = new \BreakpointDebugging_PHPUnit();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Please, choose unit tests files by customizing.
$breakpointDebugging_UnitTestFiles = array (
    /*
     */
    'BreakpointDebugging-IniSetOptimizerTest.php',
    'BreakpointDebugging-ProductionSwitcherTest.php',
    'ExampleTest.php',
    'PHPUnit1Test.php',
    'PHPUnit2Test.php',
    'BreakpointDebugging-ExceptionTest.php',
    'BreakpointDebugging-InAllCaseTest.php',
    'BreakpointDebuggingTest.php',
    'BreakpointDebugging/ErrorInAllCaseTest.php',
    'BreakpointDebugging/ErrorTest.php',
    'BreakpointDebugging/LockByFileExistingTest.php',
    'BreakpointDebugging/LockByFlockTest.php',
    'BreakpointDebugging/LockByShmopRequestTest.php',
    'BreakpointDebugging/OverrideClassTest.php',
    /*
     */
);

// Executes unit tests.
// $breakpointDebugging_PHPUnit->executeUnitTest($breakpointDebugging_UnitTestFiles); exit;

// Makes up code coverage report, then displays in browser.
if (B::isDebug()) { // In case of debug.
    // $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_InDebug.php'); exit;
    // $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging/ErrorTest.php', 'PEAR/BreakpointDebugging/Error.php'); exit;
} else { // In case of release.
    // $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging.php'); exit; // "BreakpointDebugging", "BreakpointDebugging_Middle" class is ? (Windows).
    // $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging/ErrorInAllCaseTest.php', 'PEAR/BreakpointDebugging/ErrorInAllCaseTest.php'); exit;
}
// In case of debug or release.
//$breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging-IniSetOptimizerTest.php', array('BreakpointDebugging_Optimizer.php','BreakpointDebugging_IniSetOptimizer.php')); exit;
$breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging-ProductionSwitcherTest.php', array('BreakpointDebugging_Optimizer.php','BreakpointDebugging_ProductionSwitcher.php')); exit;
// $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAROtherPackage/BreakpointDebugging_PHPUnit.php'); exit;
// $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php'); exit;
// $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php')); exit; // OK.
// $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging/LockByFlockTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFlock.php')); exit; // OK.
$breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging/LockByShmopRequestTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByShmopRequest.php')); exit;
// $breakpointDebugging_PHPUnit->displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php'); exit;
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Please, choose unit tests files by customizing.
$breakpointDebugging_UnitTestFiles = array (
    'ExampleTestSimple.php',
);

// Executes unit tests of unit test code.
$breakpointDebugging_PHPUnit->executeUnitTestSimple($breakpointDebugging_UnitTestFiles); exit;
//
$breakpointDebugging_PHPUnit->displayCodeCoverageReportSimple($breakpointDebugging_UnitTestFiles, 'BreakpointDebugging/LockByFlock.php'); exit;
// $breakpointDebugging_PHPUnit->displayCodeCoverageReportSimple($breakpointDebugging_UnitTestFiles, 'BreakpointDebugging/PHPUnit/FrameworkTestCaseSimple.php'); exit;
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Please, choose unit tests files by customizing.
$breakpointDebugging_UnitTestFiles = array (
    'BreakpointDebugging-PHPUnitTestSimple.php',
);

// Executes unit tests of unit test code.
// $breakpointDebugging_PHPUnit->executeUnitTestSimple($breakpointDebugging_UnitTestFiles, 'SIMPLE_OWN'); exit;
//
// $breakpointDebugging_PHPUnit->displayCodeCoverageReportSimple($breakpointDebugging_UnitTestFiles, 'BreakpointDebugging_PHPUnit.php', 'SIMPLE_OWN'); exit;
// $breakpointDebugging_PHPUnit->displayCodeCoverageReportSimple($breakpointDebugging_UnitTestFiles, 'BreakpointDebugging/PHPUnit/FrameworkTestCase.php', 'SIMPLE_OWN'); exit;
