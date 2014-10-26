<?php

namespace BreakpointDebugging;

require_once './BreakpointDebugging_Inclusion.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_Window as BW;
use \BreakpointDebugging_PHPUnit_StaticVariableStorage as BSS;
use \BreakpointDebugging_PHPUnit_FrameworkTestCase as BSF;

B::checkExeMode(); // Checks the execution mode.

$array = array (
    'fruit1' => 'リンゴ',
    'fruit2' => 'ゴーヤ',
    'ヤクザ',
    'fruit4' => false,
    'fruit5' => 'ロクデナシ'
);

for (reset($array); ($key = key($array)) !== null; next($array)) {
    var_dump($key);
}

exit;

$shmopKeyFilePath = B::getStatic('$_workDir') . '/LockByShmopRequest.txt';
//set_error_handler('\BreakpointDebugging::handleError', 0);
// Opens shared memory key file.
$pFile = fopen($shmopKeyFilePath, 'rb');
//restore_error_handler();
$sharedMemoryID = \BreakpointDebugging_Shmop::getSharedMemoryID($pFile);
$result = fclose($pFile);
B::assert($result === true);

// Says to shutdown response process.
$result = shmop_write($sharedMemoryID, '1', 73);
B::assert($result !== false);
// This process waits until response process was shutdowned.
while (true) {
    // If response process was shutdowned.
    if (shmop_read($sharedMemoryID, 73, 1) === '0') {
        exit('Response process was shutdowned.');
    }
    sleep(1);
}





// Constructs instance.
$LockByShmopRequest = &\BreakpointDebugging_LockByShmopRequest::singleton();
$LockByShmopRequest->lock();
//usleep(10000000);
$LockByShmopRequest->unlock();
exit('Success!');



$value = popen('php.exe -f C:\xampp\htdocs\Pear-Package\BreakpointDebugging_LockByShmopResponse.php', 'r');

// Excepts socket resources.
if (is_resource($value) //
// && get_resource_type($value) === '???' //
) {
    var_dump(get_resource_type($value));
}

exit('Done!');



$pPipe = popen($testFileName, 'rb');
pclose($pPipe);
exit('Done!');



$testFileName = B::getStatic('$_workDir') . '/test.txt';
$result = file_put_contents($testFileName, '');
B::assert($result !== false);
$pFile = fopen($testFileName, 'rb');
// fclose($pFile);
exit('Done!');



$test1 = <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>テスト１</title>
	</head>
	<body style="background-color: black; color: white; font-size: 25px">
		テスト１ボディー
	</body>
</html>
EOD;
BW::virtualOpen('test1', $test1);

$test2 = <<<EOD
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>テスト２</title>
	</head>
	<body style="background-color: black; color: white; font-size: 25px">
		テスト２ボディー
	</body>
</html>
EOD;
BW::virtualOpen('test2', $test2);
echo 'メインテストページ';
exit;



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
    BW::virtualOpen('WindowID1', $htmlFileContent1);
    BW::virtualOpen('WindowID2', $htmlFileContent2);
    BW::scriptClearance();
    BW::htmlAddition('WindowID1', 'pre', 0, 'Error message 1-1.' . PHP_EOL);
    BW::scriptClearance();
    BW::htmlAddition('WindowID1', 'pre', 0, 'Error message 1-2.' . PHP_EOL);
    BW::scriptClearance();
    BW::htmlAddition('WindowID2', 'pre', 0, 'Error message 2-1.' . PHP_EOL);
    BW::scriptClearance();
    BW::htmlAddition('WindowID2', 'pre', 0, 'Error message 2-2.' . PHP_EOL);
    // BW::close('WindowID2');
    // BW::close('WindowID1');
    BW::scriptClearance();
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
    BSS::storeGlobals(BSF::$_globalRefs, BSF::$_globals, array ());
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
    BSS::restoreGlobals(BSF::$_globalRefs, BSF::$_globals);
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
    BSS::storeProperties($staticProperties, array ());
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyA');
    ob_start();
    var_dump(\TestClassB::$testRecursiveArrayProperty);
    $beforeTestRecursiveArrayProperty = ob_get_clean();

    // Change value.
    \TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA = 'testPropertyZ';
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyZ');

    // Restores static properties.
    BSS::restoreProperties(BSF::refStaticProperties2());
    B::assert(\TestClassB::$GLOBALS[0]->testObjectProperty->testPropertyA === 'testPropertyA');
    ob_start();
    var_dump(\TestClassB::$testRecursiveArrayProperty);
    $afterTestRecursiveArrayProperty = ob_get_clean();
    B::assert($beforeTestRecursiveArrayProperty === $afterTestRecursiveArrayProperty);

    echo '<pre>Test ended.</pre>';
}

test();
