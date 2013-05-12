<?php

// This file is sample code.

namespace Your_Name;

use \BreakpointDebugging as B;

chdir('../../../');
require_once './NativeClass.php'; // Test class.

B::isUnitTestExeMode(false); // Checks the execution mode.

$testNumber = 2;

if ($testNumber === 1) {
    // Registers the function being not fixed.
    static $isRegister = false;
    B::registerNotFixedLocation($isRegister);
    // SJIS + UTF-8
    var_dump(B::convertMbString("\x95\xB6\x8E\x9A \xE6\x96\x87\xE5\xAD\x97 "));
    echo 'Is not displayed.';
} else if ($testNumber === 2) {
    // Registers the function being not fixed.
    static $isRegister = false;
    B::registerNotFixedLocation($isRegister);
    function fnThrow()
    {
        throw new \PEAR_Exception('test exception.');
    }

    function fnTestC()
    {
        // B::assert(false, 101); // This is error location.
        fnThrow($GLOBALS, array ($GLOBALS)); // This is exception location.
        // fnThrow($GLOBALS); // This is exception location.
    }

    function fnTestB()
    {
        // Registers the function being not fixed.
        static $isRegister = false;
        B::registerNotFixedLocation($isRegister);
        global $object, $array, $varietyObject;
        define('TEST_CONST', '<TEST CONST>');

        $testString = '<TEST STRING>';
        // Adds a value to trace.
        B::addValuesToTrace(array ('TEST_CONST' => TEST_CONST, '$testString' => $testString, '$varietyObject' => $varietyObject));
        for ($count = 0; $count <= 10; $count++) {
            B::addValuesToTrace(array ('$count' => $count));
        }

        try {
            fnTestC(true, false, 1, 1.1, "\x95\xB6\x8E\x9A ", $object, $array, tmpfile(), null, $varietyObject);
        } catch (\Exception $prevException) {
            // Something error repair.
            //      .
            //      .
            //      .
            // If repair failed.
            if (true) {
                // Does not parse HTML tag because it is changed with "htmlspecialchars()" for internal tag.
                B::$prependExceptionLog = '<i>Repair failed.</i> αβ∞' . PHP_EOL;
                // Must throw together previous exception when code catches exception for error repair.
                throw new \Exception('Repair failed.', 0, $prevException);
            }
        }
    }

    function fnTestA()
    {
        fnTestB();
    }

    // A tag inside of the "<pre class='xdebug-var-dump' dir='ltr'>" tag isn't changed because the prepend logging is executed "htmlspecialchars()".
    B::$prependErrorLog = '<i>Some error happened.</i> αβ∞' . PHP_EOL;
    for ($globalCount = 0; $globalCount <= 20; $globalCount++) {
        B::addValuesToTrace(array ('$globalCount' => $globalCount));
    }
    fnTestA();
    echo 'END.';
}

?>
