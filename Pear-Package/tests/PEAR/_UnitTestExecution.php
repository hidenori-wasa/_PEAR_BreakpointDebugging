<?php

chdir(str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2));
require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;

B::checkExeMode(true);
// Please, choose unit tests files by customizing.
$unitTestCommands = array (
    'BreakpointDebugging-ExceptionTest.php',
    'BreakpointDebugging-InAllCaseTest.php',
    'BreakpointDebuggingTest.php',
    'BreakpointDebugging-UnitTestCallerTest.php',
    'BreakpointDebugging/Error-InAllCaseTest.php',
    'BreakpointDebugging/ErrorTest.php',
    'BreakpointDebugging/LockByFileExistingTest.php',
    'BreakpointDebugging/LockByFlockTest.php',
    'BreakpointDebugging/LockByShmopTest.php',
    'BreakpointDebugging/OverrideClassTest.php',
);

// Executes unit tests.
B::executeUnitTest($unitTestCommands); exit;

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
// B::displayCodeCoverageReport('BreakpointDebugging-UnitTestCallerTest.php', 'PEAR/BreakpointDebugging_UnitTestCaller.php'); // Windows is not OK.
// B::displayCodeCoverageReport('BreakpointDebugging/LockByFileExistingTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFileExisting.php')); // OK.
B::displayCodeCoverageReport('BreakpointDebugging/LockByFlockTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByFlock.php')); // OK.
B::displayCodeCoverageReport('BreakpointDebugging/LockByShmopTest.php', array ('PEAR/BreakpointDebugging/Lock.php', 'PEAR/BreakpointDebugging/LockByShmop.php')); // Release unit test is not OK.
// B::displayCodeCoverageReport('BreakpointDebugging/OverrideClassTest.php', 'PEAR/BreakpointDebugging/OverrideClass.php'); // OK.

echo '<pre>"B::displayCodeCoverageReport()" ended.</pre>';

?>
