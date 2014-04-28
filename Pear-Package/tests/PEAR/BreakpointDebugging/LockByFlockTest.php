<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;

class BreakpointDebugging_LockByFlockTest extends \BreakpointDebugging_PHPUnit_FrameworkTestCase
{
    protected $lockByFlock;

    function setUp()
    {
        parent::setUp();
        // Constructs instance.
        $this->lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10);
    }

    function tearDown()
    {
        // Destructs instance.
        $this->lockByFlock = null;
        parent::tearDown();
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     */
    function testMultiprocess()
    {
        // Destructs instance.
        $this->lockByFlock = null;
        $main = new \tests_PEAR_BreakpointDebugging_MultiprocessTest_Main();
        if (!$main->test(1234, '\BreakpointDebugging_LockByFlock')) {
            // Displays error call stack information, then stops at breakpoint, then exits.
            parent::fail();
        }
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__clone ID=101.
     */
    function test__clone()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $tmp = clone $this->lockByFlock;
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     */
    public function test__destruct()
    {
        parent::assertTrue(BU::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByFlock);
        // Calls "__destruct".
        $this->lockByFlock = null;
        parent::assertTrue(BU::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     */
    public function testForceUnlocking()
    {
        $this->lockByFlock->lock();
        $this->lockByFlock->lock();

        parent::assertTrue(BU::getPropertyForTest($this->lockByFlock, '$lockCount') === 2);

        \BreakpointDebugging_Lock::forceUnlocking();

        parent::assertTrue(BU::getPropertyForTest($this->lockByFlock, '$lockCount') === 0);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     */
    function testLockThenUnlock_A()
    {
        $this->lockByFlock->lock();
        $this->lockByFlock->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     */
    function testLockThenUnlock_B()
    {
        $this->lockByFlock->lock();
        $this->lockByFlock->lock();
        $this->lockByFlock->unlock();
        $this->lockByFlock->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=101.
     */
    function testLockThenUnlock_C()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->lockByFlock->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=101.
     */
    function testLockThenUnlock_D()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        try {
            $this->lockByFlock->lock();
            $this->lockByFlock->unlock();
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->lockByFlock->unlock(); // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=101.
     */
    function testLockThenUnlock_E()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->lockByFlock->lock();
        // Calls "__destruct()".
        $this->lockByFlock = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=101.
     */
    function testLockThenUnlock_F()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->lockByFlock->lock();
        $this->lockByFlock->lock();
        $this->lockByFlock->unlock();
        // Calls "__destruct()".
        $this->lockByFlock = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     */
    function testSingleton_A()
    {
        $lockByFlock1 = &\BreakpointDebugging_LockByFlock::singleton(5, 10);
        $lockByFlock2 = &\BreakpointDebugging_LockByFlock::singleton(5, 10); // Same object.
        parent::assertTrue($lockByFlock1 === $lockByFlock2);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_B()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.
        // Constructs instance of other class.
        $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_C()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        if ((BU::$exeMode & B::REMOTE) //
            && !extension_loaded('shmop') //
        ) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Constructs instance of other class.
        $LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
    }

}

?>
