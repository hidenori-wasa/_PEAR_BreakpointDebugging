<?php

use \BreakpointDebugging as B;

function test4()
{
    global $lineA;
    static $isRegister = false;

    B::registerNotFixedLocation($isRegister);

    trigger_error2();
    $lineA = __LINE__ - 1;
}

function test3()
{
    global $lineB;

    test4();
    $lineB = __LINE__ - 1;
}

trigger_error2();
$line_ = __LINE__ - 1;
test3();
$lineC = __LINE__ - 1;

?>
