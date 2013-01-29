<?php

chdir(__DIR__ . '/../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(true);
class ExampleTest extends \BreakpointDebugging_UnitTestOverriding
{
    /**
     * @expectedException        PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=ExampleTest FUNCTION=_something ID=123
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