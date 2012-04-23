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

$testNumber = 2;

if ($testNumber === 1) {
    // Register the function being not fixed.
    static $isRegister;
    B::registerNotFixedLocation($isRegister);
    // SJIS + UTF-8
    var_dump(B::convertMbString("\x95\xB6\x8E\x9A \xE6\x96\x87\xE5\xAD\x97 "));
    exit('END');
} else if ($testNumber === 2) {
    // Register the function being not fixed.
    static $isRegister;
    B::registerNotFixedLocation($isRegister);

    // This function needs for call stack.
    function throwException($message = '', $code = 0, $previous = null)
    {
        assert(is_string($message));
        assert(is_int($code));
        assert($previous instanceof \Exception || $previous === null);

        throw new \Exception(B::convertMbString($message), $code, $previous);
    }

    function fnTestC()
    {
        // assert(false); // This is error location.
        throwException('test exception 1.'); // This is exception location.
    }

    function fnTestB()
    {
        // Register the function being not fixed.
        static $isRegister;
        B::registerNotFixedLocation($isRegister);
        global $object, $array, $varietyObject;
        define('TEST_CONST', '<TEST CONST>');

        $testString = '<TEST STRING>';
        // Add value to trace.
        B::addValuesToTrace(array('TEST_CONST' => TEST_CONST, '$testString' => $testString, '$varietyObject' => $varietyObject));
        for ($count = 0; $count <= 10; $count++) {
            B::addValuesToTrace(array('$count' => $count));
        }

        try {
            //fnTestC(true, false, 1, 1.1, "\x95\xB6\x8E\x9A \xE6\x96\x87\xE5\xAD\x97 ", $object, $array, tmpfile(), null, $varietyObject);
            fnTestC(true, false, 1, 1.1, "\x95\xB6\x8E\x9A ", $object, $array, tmpfile(), null, $varietyObject);
        } catch (\Exception $exception) {
            // A tag inside of the "<pre class='xdebug-var-dump' dir='ltr'>" tag isn't changed because the prepend logging is executed "htmlspecialchars()".
            B::$prependExceptionLog = '<i>This exception caused in fnTestB().</i> αβ∞' . PHP_EOL;
            // This writes inside of "catch()", then display logging or log.
            B::exceptionHandler($exception);
            // This doesn't specify previous exception because "B::exceptionHandler()" logged.
            throwException('test exception 2.');
        }
    }

    function fnTestA()
    {
        fnTestB();
    }

    // A tag inside of the "<pre class='xdebug-var-dump' dir='ltr'>" tag isn't changed because the prepend logging is executed "htmlspecialchars()".
    B::$prependErrorLog = '<i>Some error happened.</i> αβ∞' . PHP_EOL;

    for ($globalCount = 0; $globalCount <= 20; $globalCount++) {
        B::addValuesToTrace(array('$globalCount' => $globalCount));
    }

    try {
        fnTestA();
    } catch (\Exception $exception) {
        B::$prependExceptionLog = '<i>Some global exception happened.</i> αβ∞' . PHP_EOL;
        // This specifies previous exception, and global exception handler will process.
        throwException('test exception 3.', 3, $exception);
    }

    echo 'END';
}
?>
