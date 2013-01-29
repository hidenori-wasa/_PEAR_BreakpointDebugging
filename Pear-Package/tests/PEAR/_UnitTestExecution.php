<?php

chdir(__DIR__ . '/../../');

require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

// Please, choose unit tests files by customizing.
$testFileNames = array (
    // '--stop-on-failure ExampleTest.php',
    // '--stop-on-failure BreakpointDebugging-ExceptionTest.php',
    '--stop-on-failure BreakpointDebugging-InAllCaseTest.php',
    // '--stop-on-failure BreakpointDebugging/ErrorTest.php',
    // '--stop-on-failure BreakpointDebugging/LockByFileExistingTest.php',
    // '--stop-on-failure BreakpointDebugging/LockByFlockTest.php',
    // '--stop-on-failure BreakpointDebugging/LockByShmopTest.php',
    // '--stop-on-failure BreakpointDebugging/OverrideClassTest.php',
    // '--stop-on-failure BreakpointDebuggingTest.php',
);
// Executes unit tests.
// B::executeUnitTest($testFileNames);
// Makes up code coverage report, then displays in browser.
// B::displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAR/BreakpointDebugging.php'); // OK
B::displayCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php'); // Win is OK.
// B::displayCodeCoverageReport('BreakpointDebugging/ErrorTest.php', array ('PEAR/BreakpointDebugging/Error.php', 'PEAR/BreakpointDebugging/Error_Option.php'));
// B::displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php'));
// B::displayCodeCoverageReport('BreakpointDebugging/LockByFlockTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFlock.php')); // OK.
// B::displayCodeCoverageReport('BreakpointDebugging/LockByShmopTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByShmop.php'));
// B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php');
// B::displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_Option.php');

?>