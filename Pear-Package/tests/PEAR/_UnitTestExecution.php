<?php

$projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
chdir(__DIR__ . '/' . $projectDirPath);
require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(true);

// Please, choose unit tests files by customizing.
// You must specify array element to one if you want step execution.
$unitTestCommands = array (
    '--stop-on-failure BreakpointDebugging-ExceptionTest.php',
    '--stop-on-failure BreakpointDebugging-InAllCaseTest.php',
    '--stop-on-failure BreakpointDebuggingTest.php',
    '--stop-on-failure BreakpointDebugging-UnitTestCallerTest.php',
    '--stop-on-failure BreakpointDebugging/Error-InAllCaseTest.php',
    '--stop-on-failure BreakpointDebugging/ErrorTest.php',
    '--stop-on-failure BreakpointDebugging/LockByFileExistingTest.php',
    '--stop-on-failure BreakpointDebugging/LockByFlockTest.php',
    '--stop-on-failure BreakpointDebugging/LockByShmopTest.php',
    '--stop-on-failure BreakpointDebugging/OverrideClassTest.php',
);

// Executes unit tests.
// B::executeUnitTest($unitTestCommands, true); exit;
//
// Makes up code coverage report, then displays in browser.
if (B::getStatic('$exeMode') & B::RELEASE) { // In case of release.
    // B::displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging.php'); // "BreakpointDebugging", "BreakpointDebugging_Middle" class is OK.
    // B::displayCodeCoverageReport('BreakpointDebugging/Error-InAllCaseTest.php', 'PEAR/BreakpointDebugging/Error.php'); // Windows is OK.
} else { // In case of debug.
    // B::displayCodeCoverageReport('BreakpointDebuggingTest.php', 'PEAR/BreakpointDebugging_Option.php'); // Windows is OK.
    // B::displayCodeCoverageReport('BreakpointDebugging/ErrorTest.php', 'PEAR/BreakpointDebugging/Error_Option.php'); // OK.
}
// In case of debug or release.
// B::displayCodeCoverageReport('BreakpointDebugging-ExceptionTest.php', 'PEAR/BreakpointDebugging_UnitTestCaller.php'); // OK.
// B::displayCodeCoverageReport('BreakpointDebugging-InAllCaseTest.php', 'PEAR/BreakpointDebugging.php'); // Windows is OK.
B::displayCodeCoverageReport('BreakpointDebugging-UnitTestCallerTest.php', 'PEAR/BreakpointDebugging_UnitTestCaller.php'); // Windows is not OK.
// B::displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php')); // OK.
// B::displayCodeCoverageReport('BreakpointDebugging/LockByFlockTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFlock.php')); // OK.
B::displayCodeCoverageReport('BreakpointDebugging/LockByShmopTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByShmop.php')); // Release unit test is not OK.
// B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php'); // OK.

echo '<pre>"B::displayCodeCoverageReport()" ended.</pre>';

?>
