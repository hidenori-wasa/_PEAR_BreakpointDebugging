<?php

// This file is sample code.

namespace Your_Name;
// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

require_once './BreakpointDebugging_MySetting.php'; // We must include.
require_once './NativeClass.php'; // Test class.

// Register the function being not fixed.
static $isRegister; B::registerNotFixedLocation( $isRegister);

/**
 * Function has been fixed.
 *
 * @param object $object
 *
 */
function fnTestC()
{
    // echo B::buildErrorCallStackLog('EXCEPTION', 'Description of exception.');
    // assert(false); // This is error location.
    throw new \Exception('test exception 1.'); // This is exception location.
}

/**
 * Function has been not fixed.
 *
 * @param object $object
 *
 */
function fnTestB()
{
    // Register the function being not fixed.
    static $isRegister; B::registerNotFixedLocation( $isRegister);
    global $object, $array, $varietyObject;
	define ('TEST_CONST', '<TEST CONST>');
	
	$testString = '<TEST STRING>';
	// Add value to trace.
    B::addValuesToTrace(array('TEST_CONST' => TEST_CONST, '$testString' => $testString, '$varietyObject' => $varietyObject));
    for ($count = 0; $count <= 10; $count++) {
        B::addValuesToTrace(array('$count' => $count));
    }
    
    try {
        fnTestC(true, false, 1, 1.1, 'ABC', $object, $array, tmpfile(), null, $varietyObject);
    } catch (\Exception $exception) {
        // A tag inside of the "<pre class='xdebug-var-dump' dir='ltr'>" tag isn't changed because the prepend logging is executed "htmlspecialchars()".
        B::$prependExceptionLog = '<i>This exception caused in fnTestB().</i> αβ∞' . PHP_EOL;
        // This writes inside of "catch()", then display logging or log.
        B::exceptionHandler($exception);
        // This doesn't specify previous exception because "B::exceptionHandler()" logged.
        throw new \Exception('test exception 2.');
    }
}

/**
 * Function has been fixed.
 *
 * @param object $object
 *
 */
function fnTestA()
{
    fnTestB();
}

// A tag inside of the "<pre class='xdebug-var-dump' dir='ltr'>" tag isn't changed because the prepend logging is executed "htmlspecialchars()".
B::$prependErrorLog = '<i>Some error happened.</i> αβ∞' . PHP_EOL;

var_dump(true, false, 1, 1.1, 'ABC', $object, $array, tmpfile(), null);

for ($globalCount = 0; $globalCount <= 20; $globalCount++) {
    B::addValuesToTrace(array('$globalCount' => $globalCount));
}

try {
    fnTestA();
} catch (\Exception $exception) {
    B::$prependExceptionLog = '<i>Some global exception happened.</i> αβ∞' . PHP_EOL;
    // This specifies previous exception, and global exception handler will process.
    throw new \Exception('test exception 3.', 3, $exception);
}

echo 'END';

?>
