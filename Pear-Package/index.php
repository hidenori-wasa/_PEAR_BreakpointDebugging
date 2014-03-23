<?php

// $output = `printenv`;
// $output = `php -v`;
// exit('<pre>' . $output . '</pre>');
// xdebug_break();

require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit_UtilGlobalState as BGS;
use \BreakpointDebugging_PHPUnit_FrameworkTestCase as BSF;

B::checkExeMode(); // Checks the execution mode.

$filteredSuperGlobals = array (
    $_COOKIE,
    $_ENV,
    $_GET,
    $_POST,
    $_SERVER,
);

$filteredSuperGlobalTypes = array (
    INPUT_COOKIE,
    INPUT_ENV,
    INPUT_GET,
    INPUT_POST,
    INPUT_SERVER,
);

var_dump(ini_get('filter.default'));
var_dump(ini_get('filter.default_flags'));

for ($count = 0; $count < count($filteredSuperGlobals); $count++) {
    $filteredSuperGlobalType = $filteredSuperGlobalTypes[$count];
    $filteredSuperGlobal = &$filteredSuperGlobals[$count];
    echo 'Type = ' . $filteredSuperGlobalType . '<br />';
    foreach ($filteredSuperGlobal as $filteredSuperGlobalElementKey => &$filteredSuperGlobalElement) {
        $filteredSuperGlobalElement = 'DUMMY';
        if (!filter_has_var($filteredSuperGlobalType, $filteredSuperGlobalElementKey)) {
            // continue;
            // xdebug_break();
        }
        echo $filteredSuperGlobalElementKey;
        var_dump(filter_input($filteredSuperGlobalType, $filteredSuperGlobalElementKey, FILTER_UNSAFE_RAW));
    }
    var_dump($filteredSuperGlobal);
}
return;


$htmlFileContent1 = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>TEST1</title>
    </head>
    <body style="background-color: black; color: white; font-size: 25px">
        <pre></pre>
    </body>
</html>
EOD;

$htmlFileContent2 = <<<EOD
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>TEST2</title>
    </head>
    <body style="background-color: aqua; color: black; font-size: 25px">
        <pre></pre>
    </body>
</html>
EOD;

if (!array_key_exists('test', $_GET)) {
    $once = false;
    B::windowVirtualOpen('WindowID1', $htmlFileContent1);
    B::windowVirtualOpen('WindowID2', $htmlFileContent2);
    B::windowScriptClearance();
    B::windowHtmlAddition('WindowID1', 'pre', 0, 'Error message 1-1.' . PHP_EOL);
    B::windowScriptClearance();
    B::windowHtmlAddition('WindowID1', 'pre', 0, 'Error message 1-2.' . PHP_EOL);
    B::windowScriptClearance();
    B::windowHtmlAddition('WindowID2', 'pre', 0, 'Error message 2-1.' . PHP_EOL);
    B::windowScriptClearance();
    B::windowHtmlAddition('WindowID2', 'pre', 0, 'Error message 2-2.' . PHP_EOL);
    // B::windowClose('WindowID2');
    // B::windowClose('WindowID1');
    B::windowScriptClearance();
}
return;
//
//
//
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
$testClassA = new \TestClassA();
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
    $refGlobalRefs = &BSF::refGlobalRefs();
    $refGlobals = &BSF::refGlobals();
    BGS::storeGlobals($refGlobalRefs, $refGlobals, array ());
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
    $globalRefs = BSF::refGlobalRefs();
    $globals = BSF::refGlobals();
    BGS::restoreGlobals($globalRefs, $globals);
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
    // Stores static properties.
    $staticProperties = array ();
    BGS::storeProperties($staticProperties, array ());
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyA');
    ob_start();
    var_dump(\TestClassB::$testRecursiveArrayProperty);
    $beforeTestRecursiveArrayProperty = ob_get_clean();

    // Change value.
    \TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA = 'testPropertyZ';
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyZ');

    // Restores static properties.
    $staticProperties = BSF::refStaticProperties2();
    BGS::restoreProperties($staticProperties);
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyA');
    ob_start();
    var_dump(\TestClassB::$testRecursiveArrayProperty);
    $afterTestRecursiveArrayProperty = ob_get_clean();
    B::assert($beforeTestRecursiveArrayProperty === $afterTestRecursiveArrayProperty);

    echo '<pre>Test ended.</pre>';
}

test();
