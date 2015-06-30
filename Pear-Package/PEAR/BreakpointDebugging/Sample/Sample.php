<?php

// This file is sample code.

namespace Your_Name;

use \BreakpointDebugging as B;

$projectDirPath = str_repeat('../', preg_match_all('`/`xX', $_SERVER['PHP_SELF'], $matches) - 2);
chdir(__DIR__ . '/' . $projectDirPath);
require_once './BreakpointDebugging_Inclusion.php';
// Copies the "BreakpointDebugging_*.php" file into current work directory.
B::copyResourceToCWD('NativeClass.php', 'BreakpointDebugging/Sample/');
require_once './NativeClass.php'; // Test class.

B::checkExeMode(); // Checks the execution mode.

$testNumber = 1;

echo '<pre>Error output test.' . PHP_EOL;
echo '    Displays error in case of "DEBUG".' . PHP_EOL;
echo '    Logs error to "' . BREAKPOINTDEBUGGING_WORK_DIR_NAME . 'ErrorLog' . DIRECTORY_SEPARATOR . '" in case of "RELEASE".</pre>';

if ($testNumber === 1) {
    echo 'Tests plural character sets.';
    // Registers the function being not fixed.
    static $isRegister = false;
    B::registerNotFixedLocation($isRegister);
    // SJIS + UTF-8
    var_dump(B::convertMbString("\x95\xB6\x8E\x9A \xE6\x96\x87\xE5\xAD\x97 "));
    echo 'Is not displayed.';
} else if ($testNumber === 2) {
    echo 'Tests a error output.';
    // Registers the function being not fixed.
    static $isRegister = false;
    B::registerNotFixedLocation($isRegister);

    function fnThrow()
    {
        throw new \PEAR_Exception('test exception.');
    }

    function fnTestC()
    {
        // trigger_error('The trigger error test.');
        fnThrow($GLOBALS, array ($GLOBALS)); // This is exception location.
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
    echo 'Is not displayed.';
}

?>
