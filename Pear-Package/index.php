<?php

require_once './BreakpointDebugging_Including.php';

use \BreakpointDebugging as B;
use \TestBase as T;

B::isUnitTestExeMode(); // Checks the execution mode.
class TestBase
{
    static $testStaticPropertyBase = __CLASS__;
    static $testStaticProperty;

    static function test()
    {
        self::$testStaticProperty = &self::$testStaticPropertyBase;

        var_dump(self::$testStaticProperty);
        var_dump(T::$testStaticProperty);
    }

}

class TestMiddle extends T
{
    static function test()
    {
        var_dump(self::$testStaticProperty);
        var_dump(parent::$testStaticProperty);
        var_dump(TestMiddle::$testStaticProperty);
        var_dump(T::$testStaticProperty);
    }

}

class TestDerived extends \TestMiddle
{
    static function test()
    {
        var_dump(self::$testStaticProperty);
        var_dump(parent::$testStaticProperty);
        var_dump(TestDerived::$testStaticProperty);
        var_dump(T::$testStaticProperty);
    }

}

T::test();
TestMiddle::test();
TestDerived::test();

?>
