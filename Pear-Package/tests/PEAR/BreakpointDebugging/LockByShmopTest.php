<?php

chdir(__DIR__ . '/../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(true);
class BreakpointDebugging_LockByShmopTest extends \BreakpointDebugging_UnitTestOverriding
{
    protected $LockByShmop;

    function setUp()
    {
        parent::setUp();
        if ((B::getStatic('$exeMode') & B::RELEASE) && !extension_loaded('shmop')) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Constructs instance.
        $this->LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
    }

    function tearDown()
    {
        // Destructs instance.
        $this->LockByShmop = null;
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__clone
     */
    function test__clone()
    {
        $tmp = clone $this->LockByShmop;
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    public function test__destruct()
    {
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByShmop);
        // Calls "__destruct".
        $this->LockByShmop = null;
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    public function testForceUnlocking()
    {
        $this->LockByShmop->lock();
        $this->LockByShmop->lock();

        $this->assertTrue(B::getPropertyForTest($this->LockByShmop, '$lockCount') === 2);

        \BreakpointDebugging_Lock::forceUnlocking();

        $this->assertTrue(B::getPropertyForTest($this->LockByShmop, '$lockCount') === 0);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    function testLockThenUnlock_A()
    {
        $this->LockByShmop->lock();
        $this->LockByShmop->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    function testLockThenUnlock_B()
    {
        $this->LockByShmop->lock();
        $this->LockByShmop->lock();
        $this->LockByShmop->unlock();
        $this->LockByShmop->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=1
     */
    function testLockThenUnlock_C()
    {
        $this->LockByShmop->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=1
     */
    function testLockThenUnlock_D()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->LockByShmop->unlock(); // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=1
     */
    function testLockThenUnlock_E()
    {
        $this->LockByShmop->lock();
        // Calls "__destruct()".
        $this->LockByShmop = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=1
     */
    function testLockThenUnlock_F()
    {
        $this->LockByShmop->lock();
        $this->LockByShmop->lock();
        $this->LockByShmop->unlock();
        // Calls "__destruct()".
        $this->LockByShmop = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     */
    function testSingleton_A()
    {
        $LockByShmop1 = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
        $LockByShmop2 = &\BreakpointDebugging_LockByShmop::singleton(5, 10); // Same object.
        $this->assertTrue($LockByShmop1 === $LockByShmop2);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=1
     */
    function testSingleton_B()
    {
        // Constructs instance of other class.
        $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=1
     */
    function testSingleton_C()
    {
        // Constructs instance of other class.
        $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10);
    }

}

?>
