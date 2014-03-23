<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;
use \BreakpointDebugging_Error_InAllCaseTest as T;

function test4_($error)
{
    BU::$exeMode |= B::IGNORING_BREAK_POINT;
    $error->handleException2(new \Exception(), B::$prependExceptionLog);
    T::$lineA_ = __LINE__ - 1;
    BU::$exeMode &= ~B::IGNORING_BREAK_POINT;
}

function test3_($error)
{
    test4_($error);
    T::$lineB_ = __LINE__ - 1;
}

BU::$exeMode |= B::IGNORING_BREAK_POINT;
B::addValuesToTrace(array (array ('TestString')));
$this->_error->handleException2(new \Exception(), B::$prependExceptionLog);
$line__ = __LINE__ - 1;
BU::$exeMode &= ~B::IGNORING_BREAK_POINT;

test3_($this->_error);
$lineC_ = __LINE__ - 1;

?>
