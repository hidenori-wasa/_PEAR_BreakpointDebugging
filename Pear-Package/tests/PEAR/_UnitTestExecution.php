<?php

chdir(__DIR__ . '/../../');
require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;

// Please, choose unit tests files by customizing.
// You must specify array element to one if you want step execution.
if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release.
    $testFileNames = array (
        '--stop-on-failure BreakpointDebugging-InAllCaseTest.php',
        '--stop-on-failure BreakpointDebugging/Error-InAllCaseTest.php',
    );
} else { // In case of debug.
    $testFileNames = array (
        '--stop-on-failure BreakpointDebugging-ExceptionTest.php',
        '--stop-on-failure BreakpointDebuggingTest.php',
        '--stop-on-failure BreakpointDebugging/ErrorTest.php',
        '--stop-on-failure BreakpointDebugging/LockByFileExistingTest.php',
        '--stop-on-failure BreakpointDebugging/LockByFlockTest.php',
        '--stop-on-failure BreakpointDebugging/LockByShmopTest.php',
        '--stop-on-failure BreakpointDebugging/OverrideClassTest.php',
    );
}
$testFileNames[] = '--stop-on-failure BreakpointDebugging-UnitTestCallerTest.php';

// Executes unit tests.
// B::executeUnitTest($testFileNames); exit;
//
// Makes up code coverage report, then displays in browser.
if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release.
    // B::displayCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php'); // Windows is OK.
    // B::displayCodeCoverageReport('BreakpointDebugging/Error-InAllCaseTest.php', 'PEAR/BreakpointDebugging/Error.php'); // Windows is OK.
} else { // In case of debug.
    // B::displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAR/BreakpointDebugging.php'); // OK
    // B::displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_Option.php'); // Windows is OK.
    // B::displayCodeCoverageReport('BreakpointDebugging/ErrorTest.php', 'PEAR/BreakpointDebugging/Error_Option.php'); // OK
    // B::displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php')); // OK
    // B::displayCodeCoverageReport('BreakpointDebugging/LockByFlockTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFlock.php')); // OK.
    // B::displayCodeCoverageReport('BreakpointDebugging/LockByShmopTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByShmop.php')); // OK.
    // B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php'); // OK.
}
B::displayCodeCoverageReport('BreakpointDebugging-UnitTestCallerTest.php', 'PEAR/BreakpointDebugging_UnitTestCaller.php');

?>
