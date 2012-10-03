<?php

// This file is sample code.

namespace Your_Name;

// File to have "use" keyword does not inherit scope into a file including itself,
// also it does not inherit scope into a file including,
// and moreover "use" keyword alias has priority over class definition,
// therefore "use" keyword alias does not be affected by other files.
use \BreakpointDebugging as B;

require_once './NativeClass.php'; // Test class.

$testNumber = 4;

if ($testNumber === 1) {
    trigger_error('trigger_error', E_USER_WARNING); // Continues because this error kind is "warning".
    B::internalAssert(true); // Continues and does not exist in "B::RELEASE" because this is assertion.
    B::internalAssert(false); // Continues and does not exist in "B::RELEASE" because this is assertion.
    B::internalException('internalException'); // Continues except for "B::RELEASE" because we want step execution for seeing variable value.
    throw new \PEAR_Exception('PEAR Exception.'); // Ends at this location.
    echo 'Is not displayed.';
} else if ($testNumber === 2) {
    function test2()
    {
        trigger_error('trigger_error', E_USER_WARNING); // Continues because this error kind is "warning".
        B::internalAssert(true); // Continues and does not exist in "B::RELEASE" because this is assertion.
        B::internalAssert(false); // Continues and does not exist in "B::RELEASE" because this is assertion.
        B::internalException('internalException'); // Continues except for "B::RELEASE" because we want step execution for seeing variable value.
        throw new \PEAR_Exception('PEAR Exception.'); // Ends at this location.
        echo 'Is not displayed.';
    }

    function test1()
    {
        test2();
    }

    test1();
} else if ($testNumber === 3) {
    // Registers the function being not fixed.
    static $isRegister;
    B::registerNotFixedLocation($isRegister);
    // SJIS + UTF-8
    var_dump(B::convertMbString("\x95\xB6\x8E\x9A \xE6\x96\x87\xE5\xAD\x97 "));
    echo 'Is not displayed.';
} else if ($testNumber === 4) {
    // Registers the function being not fixed.
    static $isRegister;
    B::registerNotFixedLocation($isRegister);
    function fnThrow()
    {
        throw new \PEAR_Exception('test exception.');
    }

    function fnTestC()
    {
        // assert(false); // This is error location.
        fnThrow(); // This is exception location.
    }

    function fnTestB()
    {
        // Registers the function being not fixed.
        static $isRegister;
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
