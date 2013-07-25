<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestCaller as BU;

B::checkExeMode(true);
class BreakpointDebugging_LockByFlockTest extends \BreakpointDebugging_PHPUnitFrameworkTestCase
{
    protected $lockByFlock;

    function setUp()
    {
        parent::setUp();
        // Unlinks synchronization file.
        $lockFilePath = B::getStatic('$_workDir') . '/LockByFlock.txt';
        if (is_file($lockFilePath)) {
            B::unlink(array ($lockFilePath));
        }
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
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__clone ID=101.
     */
    function test__clone()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        $tmp = clone $this->lockByFlock;
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     */
    public function test__destruct()
    {
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByFlock);
        // Calls "__destruct".
        $this->lockByFlock = null;
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     */
    public function testForceUnlocking()
    {
        $this->lockByFlock->lock();
        $this->lockByFlock->lock();

        $this->assertTrue(B::getPropertyForTest($this->lockByFlock, '$lockCount') === 2);

        \BreakpointDebugging_Lock::forceUnlocking();

        $this->assertTrue(B::getPropertyForTest($this->lockByFlock, '$lockCount') === 0);
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
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

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
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

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
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

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
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

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
        $this->assertTrue($lockByFlock1 === $lockByFlock2);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_B()
    {
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.
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
        BU::markTestSkippedInRelease(); // Because this unit test is assertion.

        if ((BU::$exeMode & B::REMOTE)
            && !extension_loaded('shmop')
        ) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Constructs instance of other class.
        $LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
    }

}

?>
