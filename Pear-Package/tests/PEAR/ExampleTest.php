<?php

use \BreakpointDebugging as B;

B::checkExeMode(true);
class ExampleTest extends \BreakpointDebugging_PHPUnitFrameworkTestCase
{
    /**
     * @expectedException        BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=ExampleTest FUNCTION=_something ID=123.
     * @expectedExceptionCode    123
     */
    public function testExample()
    {
        $this->_something();
    }

    private function _something()
    {
        B::assert(true, 122);
        // throw new \BreakpointDebugging_ErrorException('Dummy message.', 123);
        // $this->assertTrue(false);
        // $this->fail('Test message.');
        B::assert(false, 123);
        B::assert(false, 124);
    }

}

?>
