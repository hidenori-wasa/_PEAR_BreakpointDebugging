<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestOverridingBase as BU;

function test4_()
{
    global $lineA_;

    //$exeMode = &B::refStatic('$exeMode');
    //$exeMode |= B::IGNORING_BREAK_POINT;
    BU::$exeMode |= B::IGNORING_BREAK_POINT;
    BreakpointDebugging_Error_InAllCaseTest::$error->handleException2(new \Exception(), B::$prependExceptionLog);
    $lineA_ = __LINE__ - 1;
    //$exeMode &= ~B::IGNORING_BREAK_POINT;
    BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
}

function test3_()
{
    global $lineB_;

    test4_();
    $lineB_ = __LINE__ - 1;
}

//$exeMode = &B::refStatic('$exeMode');
//$exeMode |= B::IGNORING_BREAK_POINT;
BU::$exeMode |= B::IGNORING_BREAK_POINT;
B::addValuesToTrace(array (array ('TestString')));
BreakpointDebugging_Error_InAllCaseTest::$error->handleException2(new \Exception(), B::$prependExceptionLog);
$line__ = __LINE__ - 1;
//$exeMode &= ~B::IGNORING_BREAK_POINT;
BU::$exeMode &= ~B::IGNORING_BREAK_POINT;

test3_();
$lineC_ = __LINE__ - 1;

?>
