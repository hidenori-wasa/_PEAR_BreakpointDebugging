<?php

require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnitUtilFilesystem as BF;

B::checkExeMode(); // Checks the execution mode.

phpinfo();
return;


$exePath = BF::streamResolveIncludePath('BreakpointDebugging/MultiprocessUnitTestForWindows.exe');
$testPathA = BF::streamResolveIncludePath('tests/PEAR/BreakpointDebugging-ExceptionTest.php');
// Runs multiprocess unit test for Windows.
// $result = `MultiprocessUnitTestForWindows.exe`;
// $result = `$exePath -h`;
// $result = `$exePath -help`;
$result = `$exePath $testPathA 2`;
// $result = `$exePath "TestA.php" 3`;
// $result = `$exePath TestA.php 2 "TestB.php" 3`;

echo "<pre>$result</pre>";

return;
////////////////////////////////////////////////////////////////////////////////
B::checkExeMode(); // Checks the execution mode.
class TestClassA
{
    public $testPropertyA = 'testPropertyA';

}

class TestClassB
{
    public $testPropertyB = 'testPropertyB';
    static public $testRecursiveArrayProperty = array ();
    public $testObjectProperty;
    static $GLOBALS;

    function __construct()
    {
        self::$testRecursiveArrayProperty[] = &self::$testRecursiveArrayProperty;
        self::$testRecursiveArrayProperty[0][] = &self::$testRecursiveArrayProperty;
        self::$testRecursiveArrayProperty[0][0][] = &self::$testRecursiveArrayProperty;
        $this->testObjectProperty = new \TestClassA();
    }

}

$testClassB = new \TestClassB();
$testArray = array ($testClassB);
function test()
{
    global $_BreakpointDebugging_EXE_MODE, $testClassB, $testArray, $referenceA, $referenceB, $referenceC, $referenceD, $recursiveReferenceA;

    $referenced = 'referenced';
    $referenceA = &$referenced;
    $referenceB = array (&$referenced);
    $referenceC = array (array (&$referenced));
    $referenceD = array (array (array (&$referenced)));
    $recursiveReferenceA = array (&$recursiveReferenceA, &$referenced);
    $HTTP_HOST = $GLOBALS['_SERVER']['HTTP_HOST'];
    $PHP_SELF = $GLOBALS['_SERVER']['PHP_SELF'];
    $BreakpointDebugging_EXE_MODE = $_BreakpointDebugging_EXE_MODE;
    $testPropertyA = $testClassB->testObjectProperty->testPropertyA;
    $testPropertyA2 = $testArray[0]->testObjectProperty->testPropertyA;

    // Stores a variable.
    \BreakpointDebugging_PHPUnitUtilGlobalState::backupGlobals(array ());
    B::assert($referenceA === 'referenced');
    B::assert($referenceB === array ('referenced'));
    B::assert($referenceC === array (array ('referenced')));
    B::assert($referenceD === array (array (array ('referenced'))));
    B::assert($recursiveReferenceA[1] === 'referenced');
    B::assert($GLOBALS['_SERVER']['HTTP_HOST'] === $HTTP_HOST);
    B::assert($GLOBALS['_SERVER']['PHP_SELF'] === $PHP_SELF);
    B::assert($_BreakpointDebugging_EXE_MODE === $BreakpointDebugging_EXE_MODE);
    B::assert($testClassB->testObjectProperty->testPropertyA === $testPropertyA);
    B::assert($testArray[0]->testObjectProperty->testPropertyA === $testPropertyA2);
    B::assert(!array_key_exists('ADDITION', $GLOBALS));

    // Change value.
    $referenced = 'referenceDummy';
    $_SERVER['PHP_SELF'] = 'PHP_SELF_DUMMY';
    unset($_SERVER['HTTP_HOST']);
    $_BreakpointDebugging_EXE_MODE = 'BreakpointDebugging_EXE_MODE_DUMMY';
    $testClassB->testObjectProperty->testPropertyA = 'testPropertyZ';
    $GLOBALS['ADDITION'] = null;
    B::assert($referenceA === 'referenceDummy');
    B::assert($referenceB === array ('referenceDummy'));
    B::assert($referenceC === array (array ('referenceDummy')));
    B::assert($referenceD === array (array (array ('referenceDummy'))));
    B::assert($recursiveReferenceA[1] === 'referenceDummy');
    B::assert(!isset($GLOBALS['_SERVER']['HTTP_HOST']));
    B::assert($GLOBALS['_SERVER']['PHP_SELF'] === 'PHP_SELF_DUMMY');
    B::assert($_BreakpointDebugging_EXE_MODE === 'BreakpointDebugging_EXE_MODE_DUMMY');
    B::assert($testClassB->testObjectProperty->testPropertyA === 'testPropertyZ');
    B::assert($testArray[0]->testObjectProperty->testPropertyA === 'testPropertyZ');
    B::assert(array_key_exists('ADDITION', $GLOBALS));

    // Restores variable.
    \BreakpointDebugging_PHPUnitUtilGlobalState::restoreGlobals();
    B::assert($referenceA === 'referenceDummy'); // Copy.
    B::assert($referenceB === array ('referenceDummy')); // Copy.
    B::assert($referenceC === array (array ('referenceDummy'))); // Copy.
    B::assert($referenceD === array (array (array ('referenced')))); // Serialization.
    B::assert($recursiveReferenceA[1] === 'referenced'); // Serialization.
    B::assert($GLOBALS['_SERVER']['HTTP_HOST'] === $HTTP_HOST);
    B::assert($GLOBALS['_SERVER']['PHP_SELF'] === $PHP_SELF);
    B::assert($_BreakpointDebugging_EXE_MODE === $BreakpointDebugging_EXE_MODE);
    B::assert($testClassB->testObjectProperty->testPropertyA === $testPropertyA);
    B::assert($testArray[0]->testObjectProperty->testPropertyA === $testPropertyA2);
    B::assert(!array_key_exists('ADDITION', $GLOBALS));

    // Change value.
    $referenced = 'referenceConnection';
    B::assert($referenceA === 'referenceConnection'); // Copy. Reference has been connecting.
    B::assert($referenceB === array ('referenceConnection')); // Copy. Reference has been connecting.
    B::assert($referenceC === array (array ('referenceConnection'))); // Copy. Reference has been connecting.
    B::assert($referenceD === array (array (array ('referenced')))); // Serialization. Reference has been broken.
    B::assert($recursiveReferenceA[1] === 'referenced'); // Serialization. Reference has been broken.
    //////////////////////////////////////////////////////////////////////////////////////////////////
    \TestClassB::$GLOBALS = $testArray;
    // Stores static attributes.
    \BreakpointDebugging_PHPUnitUtilGlobalState::backupStaticAttributes(array ());
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyA');
    ob_start();
    var_dump(\TestClassB::$testRecursiveArrayProperty);
    $beforeTestRecursiveArrayProperty = ob_get_clean();

    // Change value.
    \TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA = 'testPropertyZ';
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyZ');

    // Restores static attributes.
    \BreakpointDebugging_PHPUnitUtilGlobalState::restoreStaticAttributes();
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyA');
    ob_start();
    var_dump(\TestClassB::$testRecursiveArrayProperty);
    $afterTestRecursiveArrayProperty = ob_get_clean();
    B::assert($beforeTestRecursiveArrayProperty === $afterTestRecursiveArrayProperty);

    echo '<pre>Test ended.</pre>';
}

test();

?>
