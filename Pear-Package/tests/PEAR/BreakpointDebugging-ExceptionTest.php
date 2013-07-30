<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestCaller as BU;

/**
 * Test class for BreakpointDebugging_Exception.
 * Generated by PHPUnit on 2012-09-30 at 16:24:28.
 */
class BreakpointDebugging_ExceptionTest extends \BreakpointDebugging_PHPUnitFrameworkTestCase
{
    /**
     * @covers \BreakpointDebugging_Exception<extended>
     */
    public function test__construct()
    {
        $previous = new \Exception('Previous message.');
        new \BreakpointDebugging_Exception('Test message.', 1, $previous, 0);
        new \BreakpointDebugging_Exception('Test message.', 1, $previous);
        new \BreakpointDebugging_Exception('Test message.', 1);
        new \BreakpointDebugging_Exception('Test message.');
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=1.
     */
    public function test__construct_A()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        $previous = new \Exception('Previous message.');
        // SJIS message.
        new \BreakpointDebugging_Exception('Current message.', 1, $previous, 1, 'Does not exist.');
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=2.
     */
    public function test__construct_B()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        new \BreakpointDebugging_Exception(100);
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=3.
     */
    public function test__construct_C()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        new \BreakpointDebugging_Exception('Current message.', 'Incorrect type.');
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=4.
     */
    public function test__construct_D()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        new \BreakpointDebugging_Exception('Current message.', 1, 'Incorrect type.');
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=5.
     */
    public function test__construct_E()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        $previous = new \Exception('Previous message.');
        // SJIS message.
        new \BreakpointDebugging_Exception('Current message.', 1, $previous, 'Incorrect type.');
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=101.
     */
    public function test__construct_H()
    {
        $previous = new \Exception('Previous message.');
        // SJIS message.
        new \BreakpointDebugging_Exception("\x95\xB6\x8E\x9A", 1, $previous, 1);
    }

}

?>
