<?php

use \BreakpointDebugging as B;

function test4_()
{
    global $lineA_;

    //throwException();
    BreakpointDebugging_ErrorTest::$error->exceptionHandler2(new \Exception(), B::$prependExceptionLog);
    $lineA_ = __LINE__ - 1;
}

function test3_()
{
    global $lineB_;

    test4_();
    $lineB_ = __LINE__ - 1;
}

//throwException();
BreakpointDebugging_ErrorTest::$error->exceptionHandler2(new \Exception(), B::$prependExceptionLog);
$line__ = __LINE__ - 1;
test3_();
$lineC_ = __LINE__ - 1;

?>
