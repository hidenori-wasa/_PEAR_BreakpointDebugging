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

/**
 * Function has been fixed.
 *
 * @param object $object
 *
 */
function fnTestC()
{
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

    try {
        fnTestC(true, false, 1, 1.1, 'ABC', $object, $array, tmpfile(), null, $varietyObject);
    } catch (\Exception $exception) {
        $prependLog = 'This exception caused in fnTestB(). <予定終了>' . PHP_EOL;
        // This writes inside of "catch()", then display logging or log.
        B::exceptionHandler($exception, $prependLog);
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

B::$prependErrorLog = 'Some error happened. <予定終了>' . PHP_EOL;
B::$prependGlobalExceptionLog = 'Some global exception happened. <予定終了>' . PHP_EOL;

var_dump(true, false, 1, 1.1, 'ABC', $object, $array, tmpfile(), null);

try {
    fnTestA();
} catch (\Exception $exception) {
    // This specifies previous exception, and global exception handler will process.
    throw new \Exception('test exception 3.', 3, $exception);
}

echo 'END';

?>
