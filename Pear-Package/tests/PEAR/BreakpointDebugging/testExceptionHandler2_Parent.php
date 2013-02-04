<?php

use \BreakpointDebugging as B;

function test4_()
{
    global $lineA_, $_BreakpointDebugging_EXE_MODE;

    $storeExeMode = BreakpointDebugging_ErrorTest::changeExeMode();
    BreakpointDebugging_ErrorTest::$error->exceptionHandler2(new \Exception(), B::$prependExceptionLog);
    $lineA_ = __LINE__ - 1;
    $_BreakpointDebugging_EXE_MODE = $storeExeMode;
}

function test3_()
{
    global $lineB_;

    test4_();
    $lineB_ = __LINE__ - 1;
}

global $_BreakpointDebugging_EXE_MODE;

$storeExeMode = BreakpointDebugging_ErrorTest::changeExeMode();
BreakpointDebugging_ErrorTest::$error->exceptionHandler2(new \Exception(), B::$prependExceptionLog);
$line__ = __LINE__ - 1;
$_BreakpointDebugging_EXE_MODE = $storeExeMode;
test3_();
$lineC_ = __LINE__ - 1;

?>
