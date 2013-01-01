<?php

chdir(__DIR__ . '/../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

// Please, choose unit tests files by customizing.
$testFileNames = array (
// '--stop-on-failure ExampleTest.php',
// '--stop-on-failure BreakpointDebugging-ExceptionTest.php',
// '--stop-on-failure BreakpointDebugging-InAllCaseTest.php',
// '--stop-on-failure BreakpointDebugging/ErrorTest.php',
// '--stop-on-failure BreakpointDebugging/LockByFileExistingTest.php',
// '--stop-on-failure BreakpointDebugging/LockByFlockTest.php',
// '--stop-on-failure BreakpointDebugging/LockByShmopTest.php',
// '--stop-on-failure BreakpointDebugging/OverrideClassTest.php',
// '--stop-on-failure BreakpointDebuggingTest.php',
);
// Executes unit tests.
// B::executeUnitTest($testFileNames, __DIR__);
//
// Makes up code coverage report, then displays in browser.
// B::makeCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAR/BreakpointDebugging.php', __DIR__); // OK
// B::makeCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php', __DIR__);
// B::makeCodeCoverageReport('BreakpointDebugging/ErrorTest.php', 'PEAR/BreakpointDebugging/Error.php', __DIR__);
// B::makeCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php'), __DIR__);
// B::makeCodeCoverageReport('BreakpointDebugging/LockByFlockTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFlock.php'), __DIR__); // OK.
// B::makeCodeCoverageReport('BreakpointDebugging/LockByShmopTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByShmop.php'), __DIR__);
// B::makeCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php', __DIR__);
B::makeCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_Option.php', __DIR__);

?>
