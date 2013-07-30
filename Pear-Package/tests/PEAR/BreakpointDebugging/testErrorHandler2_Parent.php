<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_Error_InAllCaseTest as T;

function test4()
{
    static $isRegister = false;

    B::registerNotFixedLocation($isRegister);

    trigger_error2();
    T::$lineA = __LINE__ - 1;
}

function test3()
{
    test4();
    T::$lineB = __LINE__ - 1;
}

trigger_error2();
$line_ = __LINE__ - 1;
test3();
$lineC = __LINE__ - 1;

?>
