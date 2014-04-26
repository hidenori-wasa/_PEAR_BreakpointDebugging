<?php

use \BreakpointDebugging as B;
use \BreakpointDebugging_PHPUnit as BU;

class BreakpointDebugging_LockByFileExistingTest extends \BreakpointDebugging_PHPUnit_FrameworkTestCase
{
    protected $lockByFileExisting, $lockByFileExistingInternal;

    function setUp()
    {
        parent::setUp();
        // Constructs instance.
        $this->lockByFileExistingInternal = &\BreakpointDebugging_LockByFileExisting::internalSingleton(5, 10);
        $this->lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
    }

    function tearDown()
    {
        // Destructs instance.
        $this->lockByFileExisting = null;
        $this->lockByFileExistingInternal = null;
        parent::tearDown();
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     */
    function testMultiprocess()
    {
        // Destructs instance.
        $this->lockByFileExisting = null;
        $this->lockByFileExistingInternal = null;
        $main = new \tests_PEAR_BreakpointDebugging_MultiprocessTest_Main();
        if (!$main->test(1234, '\BreakpointDebugging_LockByFileExisting')) {
            parent::fail();
        }
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__clone ID=101.
     */
    function test__clone()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $tmp = clone $this->lockByFileExisting;
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     */
    public function test__destruct()
    {
        parent::assertTrue(BU::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByFileExisting);
        // Calls "__destruct".
        $this->lockByFileExisting = null;
        parent::assertTrue(BU::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     */
    public function testForceUnlocking()
    {
        $this->lockByFileExistingInternal->lock();
        $this->lockByFileExistingInternal->lock();
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->lock();

        parent::assertTrue(BU::getPropertyForTest($this->lockByFileExistingInternal, '$lockCount') === 2);
        parent::assertTrue(BU::getPropertyForTest($this->lockByFileExisting, '$lockCount') === 2);

        \BreakpointDebugging_Lock::forceUnlocking();

        parent::assertTrue(BU::getPropertyForTest($this->lockByFileExistingInternal, '$lockCount') === 0);
        parent::assertTrue(BU::getPropertyForTest($this->lockByFileExisting, '$lockCount') === 0);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     */
    function testLockThenUnlock_A()
    {
        $this->lockByFileExisting->lock();
        clearstatcache();
        parent::assertTrue(is_file(BU::getPropertyForTest($this->lockByFileExisting, '$lockFilePath')));
        $this->lockByFileExisting->unlock();
        clearstatcache();
        parent::assertTrue(!is_file(BU::getPropertyForTest($this->lockByFileExisting, '$lockFilePath')));
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     */
    function testLockThenUnlock_C()
    {
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->unlock();
        $this->lockByFileExisting->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=101.
     */
    function testLockThenUnlock_D()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->lockByFileExisting->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=101.
     */
    function testLockThenUnlock_E()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->lockByFileExisting->lock();
        // Calls "__destruct()".
        $this->lockByFileExisting = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=101.
     */
    function testLockThenUnlock_F()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->lockByFileExisting->unlock(); // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=101.
     */
    function testLockThenUnlock_G()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->unlock();
        // Calls "__destruct()".
        $this->lockByFileExisting = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     */
    function testSingleton_A()
    {
        $lockByFileExisting1 = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
        $lockByFileExisting2 = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10); // Same object.
        parent::assertTrue($lockByFileExisting1 === $lockByFileExisting2);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_B()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.

        if ((BU::$exeMode & B::REMOTE) && !extension_loaded('shmop')
        ) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Constructs instance of other class.
        $LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=101.
     */
    function testSingleton_C()
    {
        parent::markTestSkippedInRelease(); // Because this unit test is assertion.
        // Constructs instance of other class.
        $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10); // Error.
    }

}

?>
