<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestCaller as BU;

// $somethingGlobal = ''; // We must not add global variable here. (Autodetects)
//
// unset($_POST); // We must not delete global variable here. (Autodetects)
//
// include_once __DIR__ . '/AFileWhichHasStaticStatus.php'; // We must not include a file which has static status here. (Autodetects)
class ExampleTest extends \BreakpointDebugging_PHPUnitFrameworkTestCase
{
    private $_pSomething;
    private static $_pStaticSomething;

    static function setUpBeforeClass()
    {
        // global $somethingGlobal;
        // $somethingGlobal = ''; // We must not add global variable here. (Autodetects)
        //
        // unset($_POST); // We must not delete global variable here. (Autodetects)
        //
        // include_once __DIR__ . '/AFileWhichHasStaticStatus.php'; // We must not include a file which has static status here. (Autodetects)
        //
        // We must not construct test instance here. (Cannot autodetect)
        // because we want to initialize class auto attribute (auto class method's local static and auto property).
        // self::$_pStaticSomething = &BreakpointDebugging_LockByFlock::singleton();

        $_POST = 'DUMMY_POST'; // We can change global variable here.
        \BreakpointDebugging::$prependErrorLog = 'DUMMY_prependErrorLog'; // We can change static property here.
    }

    static function tearDownAfterClass()
    {
        parent::assertTrue($_POST === 'DUMMY_POST');
        parent::assertTrue(\BreakpointDebugging::$prependErrorLog === 'DUMMY_prependErrorLog');
    }

    protected function setUp()
    {
        // This is required.
        parent::setUp();

        // Constructs an instance per test.
        // We must construct test instance here
        // because we want to initialize class auto attribute (auto class method's local static and auto property).
        $this->_pSomething = &BreakpointDebugging_LockByFlock::singleton();
        //
        // global $somethingGlobal;
        // $somethingGlobal = ''; // We must not add global variable here. (Autodetects)
        //
        // unset($_POST); // We must not delete global variable here. (Autodetects)
        //
        // include_once __DIR__ . '/AFileWhichHasStaticStatus.php'; // We must not include a file which has static status here. (Autodetects)
    }

    protected function tearDown()
    {
        // global $somethingGlobal;
        // $somethingGlobal = ''; // We must not add global variable here. (Autodetects)
        //
        // unset($_POST); // We must not delete global variable here. (Autodetects)
        //
        // include_once __DIR__ . '/AFileWhichHasStaticStatus.php'; // We must not include a file which has static status here. (Autodetects)
        //
        // This is required.
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
        BU::markTestSkippedInDebug();

        // global $somethingGlobal;
        // $somethingGlobal = ''; // We must not add global variable here. (Autodetects)
        //
        // unset($_POST); // We must not delete global variable here. (Autodetects)
        //
        // include_once __DIR__ . '/AFileWhichHasStaticStatus.php'; // We must not include a file which has static status here. (Autodetects)
        //
        // Destructs the instance.
        $this->_pSomething = null;

        BU::$exeMode |= B::IGNORING_BREAK_POINT; // Reference variable must specify class name because it cannot extend.
        $this->isCalled();
    }

    /**
     * @covers \Example<extended>
     */
    public function testSomething_B()
    {
        BU::markTestSkippedInRelease();

        // global $somethingGlobal;
        // $somethingGlobal = ''; // We must not add global variable here. (Autodetects)
        //
        // unset($_POST); // We must not delete global variable here. (Autodetects)
        //
        // include_once __DIR__ . '/AFileWhichHasStaticStatus.php'; // We must not include a file which has static status here. (Autodetects)
        //
        // How to use "try-catch" syntax instead of "@expectedException" and "@expectedExceptionMessage".
        // This way can test an error after static status was changed.
        try {
            B::assert(true, 101);
            B::assert(false, 102);
        } catch (\BreakpointDebugging_ErrorException $e) {
            $this->assertTrue(preg_match('`CLASS=ExampleTest FUNCTION=testSomething_B ID=102\.$`X', $e->getMessage()) === 1);
            return;
        }
        $this->fail();
    }

}

?>
