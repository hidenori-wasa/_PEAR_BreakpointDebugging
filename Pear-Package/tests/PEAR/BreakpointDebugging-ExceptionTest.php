<?php

chdir(__DIR__ . '/../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(true);
/**
 * Test class for BreakpointDebugging_Exception.
 * Generated by PHPUnit on 2012-09-30 at 16:24:28.
 */
class BreakpointDebugging_ExceptionTest extends \BreakpointDebugging_UnitTestOverriding
{
    /**
     * @covers \BreakpointDebugging_Exception<extended>
     */
    public function test__construct_A()
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
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=1
     */
    public function test__construct_B()
    {
        new \BreakpointDebugging_Exception('dummy', 'dummy', 'dummy', 'dummy', 'dummy');
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=2
     */
    public function test__construct_C()
    {
        new \BreakpointDebugging_Exception(1);
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=3
     */
    public function test__construct_D()
    {
        new \BreakpointDebugging_Exception('OK', 'Error');
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=7
     */
    public function test__construct_E()
    {
        new \BreakpointDebugging_Exception('OK', 1, null, 'Error');
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=7
     */
    public function test__construct_F()
    {
        new \BreakpointDebugging_Exception('OK', 1, null, -1);
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=5
     */
    public function test__construct_G()
    {
        new \BreakpointDebugging_Exception('OK', 1, 'Error', 1);
    }

    /**
     * @covers \BreakpointDebugging_Exception<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Exception FUNCTION=__construct ID=6
     */
    public function test__construct_H()
    {
        $previous = new \Exception('Previous message.');
        // SJIS message.
        new \BreakpointDebugging_Exception("\x95\xB6\x8E\x9A", 1, $previous, 1);
    }

}

?>
