<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestCaller as BU;
use \BreakpointDebugging_Error_InAllCaseTest as T;

function test4_()
{
    BU::$exeMode |= B::IGNORING_BREAK_POINT;
    BreakpointDebugging_Error_InAllCaseTest::$error->handleException2(new \Exception(), B::$prependExceptionLog);
    T::$lineA_ = __LINE__ - 1;
    BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
}

function test3_()
{
    test4_();
    T::$lineB_ = __LINE__ - 1;
}

BU::$exeMode |= B::IGNORING_BREAK_POINT;
B::addValuesToTrace(array (array ('TestString')));
BreakpointDebugging_Error_InAllCaseTest::$error->handleException2(new \Exception(), B::$prependExceptionLog);
$line__ = __LINE__ - 1;
BU::$exeMode &= ~B::IGNORING_BREAK_POINT;

test3_();
$lineC_ = __LINE__ - 1;

?>
