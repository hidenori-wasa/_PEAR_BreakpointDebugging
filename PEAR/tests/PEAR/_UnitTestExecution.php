<?php

chdir(__DIR__ . '/../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

// Please, choose unit tests files by customizing.
$testFileNames = array (
    'BreakpointDebugging-ExceptionTest.php',
    'BreakpointDebugging-InAllCaseTest.php',
    'BreakpointDebuggingTest.php',
    'BreakpointDebugging/ErrorTest.php',
    'BreakpointDebugging/LockByFileExistingTest.php',
    'BreakpointDebugging/LockByFlockTest.php',
    'BreakpointDebugging/LockByShmopTest.php',
    'BreakpointDebugging/OverrideClassTest.php',
);
// Executes unit tests.
B::executeUnitTest($testFileNames, __DIR__);

?>
