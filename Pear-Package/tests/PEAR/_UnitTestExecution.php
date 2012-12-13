<?php

chdir(__DIR__ . '/../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

// Please, choose unit tests files by customizing.
$testFileNames = array (
    '--stop-on-failure BreakpointDebugging-ExceptionTest.php',
    '--stop-on-failure BreakpointDebugging-InAllCaseTest.php',
    '--stop-on-failure BreakpointDebugging/ErrorTest.php',
    '--stop-on-failure BreakpointDebugging/LockByFileExistingTest.php',
    '--stop-on-failure BreakpointDebugging/LockByFlockTest.php',
    '--stop-on-failure BreakpointDebugging/LockByShmopTest.php',
    '--stop-on-failure BreakpointDebugging/OverrideClassTest.php',
    '--stop-on-failure BreakpointDebuggingTest.php',
);
// Executes unit tests.
B::executeUnitTest($testFileNames, __DIR__);

?>
