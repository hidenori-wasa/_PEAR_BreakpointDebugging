<?php

// Please, see "\BreakpointDebugging_PHPUnit::executeUnitTest()" class method document for detail.

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;

function localStaticVariable()
{
    // static $localStatic = 'Local static value.'; // We must not define local static variable of function. (Autodetects)
}

class LocalStaticVariableOfStaticMethod
{
    static $staticProperty = 'Initial value.'; // We can define static property here.

    static function localStaticVariable()
    {
        // static $localStatic = 'Local static value.'; // We must not define local static variable of static class method. (Autodetects)
    }

    function localStaticVariableOfInstance()
    {
        static $localStatic = 'Local static value.'; // We can define local static variable of auto class method.
    }

}

// global $something;
// $something = 'Defines global variable.'; // We must not define global variable here. (Autodetects)
//
// $_FILES = 'Changes the value.'; // We must not change global variable and static property here. (Autodetects)
//
// $_FILES = &$bugReference; // We must not overwrite global variable and static property with reference here. (Autodetects)
// unset($bugReference);
//
// unset($_FILES); // We must not delete global variable here. (Autodetects)
//
// spl_autoload_register('\ExampleTest::autoload', true, true); // We must not register autoload function at top of stack by "spl_autoload_register()". (Autodetects)
//
// include_once __DIR__ . '/AFile.php'; // We must not include a file because autoload is only once per file. (Autodetects)
class ExampleTest extends \BreakpointDebugging_PHPUnit_FrameworkTestCase
{
    private $_pTestObject;

    static function autoload($className)
    {

    }

    static function setUpBeforeClass()
    {
        // global $something;
        // $something = 'Defines global variable.'; // We must not define global variable here. (Autodetects)
        //
        // $_FILES = 'Changes the value.'; // We must not change global variable and static property here. (Autodetects)
        //
        // $_FILES = &$bugReference; // We must not overwrite global variable and static property with reference here. (Autodetects)
        //
        // unset($_FILES); // We must not delete global variable here. (Autodetects)
        //
        // spl_autoload_register('\ExampleTest::autoload', true, true); // We must not register autoload function at top of stack by "spl_autoload_register()". (Autodetects)
        //
        // include_once __DIR__ . '/AFile.php'; // We must not include a file because autoload is only once per file. (Autodetects)
    }

    static function tearDownAfterClass()
    {

    }

    protected function setUp()
    {
        // This is required at top.
        parent::setUp();

        // We must construct the test instance here.
        $this->_pTestObject = &BreakpointDebugging_LockByFlock::singleton();

        global $something;
        $something = 'Defines global variable 2.'; // We can define global variable here.

        $_FILES = 'Changes the value 2.'; // We can change global variable and static property here.

        $_FILES = &$aReference2; // We can overwrite global variable except static property with reference here.

        unset($_FILES); // We can delete global variable here.
        //
        // spl_autoload_register('\ExampleTest::autoload', true, true); // We must not register autoload function at top of stack by "spl_autoload_register()". (Autodetects)
        //
        // include_once __DIR__ . '/AFile.php'; // We must not include a file because autoload is only once per file. (Cannot detect!)
    }

    protected function tearDown()
    {
        // spl_autoload_register('\ExampleTest::autoload', true, true); // We must not register autoload function at top of stack by "spl_autoload_register()". (Autodetects)
        //
        // Destructs the test instance to reduce memory use.
        $this->_pTestObject = null;

        // This is required at bottom.
        parent::tearDown();
    }

    function isCalled()
    {
        throw new \BreakpointDebugging_ErrorException('Something message.', 101); // This is reflected in "@expectedException" and "@expectedExceptionMessage".
    }

    /**
     * @covers \Example<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=ExampleTest FUNCTION=isCalled ID=101.
     */
    public function testSomething_A()
    {
        global $something;
        $something = 'Defines global variable 3.'; // We can define global variable here.

        $_FILES = 'Changes the value 3.'; // We can change global variable and static property here.

        $_FILES = &$aReference3; // We can overwrite global variable except static property with reference here.

        unset($_FILES); // We can delete global variable here.
        //
        // spl_autoload_register('\ExampleTest::autoload', true, true); // We must not register autoload function at top of stack by "spl_autoload_register()". (Autodetects)
        //
        // include_once __DIR__ . '/AFile.php'; // We must not include a file because autoload is only once per file. (Cannot detect!)

        BU::markTestSkippedInDebug();

        // Destructs the instance.
        $this->_pTestObject = null;

        BU::$exeMode |= B::IGNORING_BREAK_POINT;
        $this->isCalled();
    }

    /**
     * @covers \Example<extended>
     */
    public function testSomething_B()
    {
        BU::markTestSkippedInRelease();

        // How to use "try-catch" syntax instead of "@expectedException" and "@expectedExceptionMessage".
        // This way can test an error after static status was changed.
        try {
            B::assert(true, 101);
            B::assert(false, 102);
        } catch (\BreakpointDebugging_ErrorException $e) {
            parent::assertTrue(preg_match('`CLASS=ExampleTest FUNCTION=testSomething_B ID=102\.$`X', $e->getMessage()) === 1);
            return;
        }
        $this->fail();
    }

    /**
     * @covers \Example<extended>
     */
    public function testIncompletedColor()
    {
        // parent::markTestIncomplete();
    }

}

?>
