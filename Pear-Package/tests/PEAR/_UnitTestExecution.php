<?php

chdir(str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2));
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnitStepExecution as BU;

B::checkExeMode(true);
// Please, choose unit tests files by customizing.
$unitTestCommands = array (
    'ExampleTest.php',
    'BreakpointDebugging-ExceptionTest.php',
    'BreakpointDebugging-InAllCaseTest.php',
    'BreakpointDebuggingTest.php',
    'BreakpointDebugging-PHPUnitStepExecutionTest.php',
    'BreakpointDebugging/Error-InAllCaseTest.php',
    'BreakpointDebugging/ErrorTest.php',
    'BreakpointDebugging/LockByFileExistingTest.php',
    'BreakpointDebugging/LockByFlockTest.php',
    'BreakpointDebugging/LockByShmopTest.php',
    'BreakpointDebugging/OverrideClassTest.php',
);

// Executes unit tests.
// BU::executeUnitTest($unitTestCommands); exit;

// Makes up code coverage report, then displays in browser.
if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release.
    // BU::displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging.php'); // "BreakpointDebugging", "BreakpointDebugging_Middle" class is OK (Windows).
    // BU::displayCodeCoverageReport('BreakpointDebugging/Error-InAllCaseTest.php', 'PEAR/BreakpointDebugging/Error.php'); //
} else { // In case of debug.
    // BU::displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_InDebug.php'); // Windows is OK.
    // BU::displayCodeCoverageReport('BreakpointDebugging/ErrorTest.php', 'PEAR/BreakpointDebugging/Error_InDebug.php'); //
}
// In case of debug or release.
// BU::displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAR/BreakpointDebugging_PHPUnitStepExecution.php'); //
// BU::displayCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php'); //
// BU::displayCodeCoverageReport('BreakpointDebugging-PHPUnitStepExecutionTest.php', 'PEAR/BreakpointDebugging_PHPUnitStepExecution.php'); //
BU::displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php')); //
// BU::displayCodeCoverageReport('BreakpointDebugging/LockByFlockTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFlock.php')); //
// BU::displayCodeCoverageReport('BreakpointDebugging/LockByShmopTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByShmop.php')); //
// BU::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php'); //

echo '<pre>"\BreakpointDebugging_PHPUnitStepExecution::displayCodeCoverageReport()" ended.</pre>';

?>
