<?php

chdir(__DIR__ . '/../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::checkUnitTestExeMode();
class BreakpointDebugging_LockByFileExistingTest extends \BreakpointDebugging_UnitTestOverriding
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
    }

    /**
     * @covers                   \BreakpointDebugging_LockByFileExisting::__clone
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__clone ID=1
     */
    function test__clone()
    {
        $tmp = clone $this->lockByFileExisting;
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::__destruct
     */
    public function test__destruct()
    {
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByFileExisting);
        // Calls "__destruct".
        $this->lockByFileExisting = null;
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @todo Implement testForceUnlocking().
     */
    public function testForceUnlocking()
    {
        $this->lockByFileExistingInternal->lock();
        $this->lockByFileExistingInternal->lock();
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->lock();

        $this->assertTrue(B::getPropertyForTest($this->lockByFileExistingInternal, '$lockCount') === 2);
        $this->assertTrue(B::getPropertyForTest($this->lockByFileExisting, '$lockCount') === 2);

        \BreakpointDebugging_Lock::forceUnlocking();

        $this->assertTrue(B::getPropertyForTest($this->lockByFileExistingInternal, '$lockCount') === 0);
        $this->assertTrue(B::getPropertyForTest($this->lockByFileExisting, '$lockCount') === 0);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::lock
     * @covers \BreakpointDebugging_LockByFileExisting::unlock
     */
    function testLockThenUnlock_A()
    {
        $this->lockByFileExisting->lock();
        clearstatcache();
        $this->assertTrue(is_file(B::getPropertyForTest($this->lockByFileExisting, '$lockFilePath')));
        $this->lockByFileExisting->unlock();
        clearstatcache();
        $this->assertTrue(!is_file(B::getPropertyForTest($this->lockByFileExisting, '$lockFilePath')));
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::lock
     * @covers \BreakpointDebugging_LockByFileExisting::unlock
     */
    function testLockThenUnlock_C()
    {
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->unlock();
        $this->lockByFileExisting->unlock();
    }

    /**
     * @covers                   \BreakpointDebugging_LockByFileExisting::lock
     * @covers                   \BreakpointDebugging_LockByFileExisting::unlock
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=1
     */
    function testLockThenUnlock_D()
    {
        $this->lockByFileExisting->unlock();
    }

    /**
     * @covers                   \BreakpointDebugging_LockByFileExisting::lock
     * @covers                   \BreakpointDebugging_LockByFileExisting::unlock
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=1
     */
    function testLockThenUnlock_E()
    {
        $this->lockByFileExisting->lock();
        // Calls "__destruct()".
        $this->lockByFileExisting = null; // Error.
    }

    /**
     * @covers                   \BreakpointDebugging_LockByFileExisting::lock
     * @covers                   \BreakpointDebugging_LockByFileExisting::unlock
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=1
     */
    function testLockThenUnlock_F()
    {
        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->lockByFileExisting->unlock(); // Error.
    }

    /**
     * @covers                   \BreakpointDebugging_LockByFileExisting::lock
     * @covers                   \BreakpointDebugging_LockByFileExisting::unlock
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=1
     */
    function testLockThenUnlock_G()
    {
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->unlock();
        // Calls "__destruct()".
        $this->lockByFileExisting = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::singleton
     */
    function testSingleton_A()
    {
        $lockByFileExisting1 = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
        $lockByFileExisting2 = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10); // Same object.
        $this->assertTrue($lockByFileExisting1 === $lockByFileExisting2);
    }

    /**
     * @covers                   \BreakpointDebugging_LockByFileExisting::singleton
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=1
     */
    function testSingleton_B()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (($_BreakpointDebugging_EXE_MODE & B::RELEASE) && !extension_loaded('shmop')) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Constructs instance of other class.
        $LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
    }

    /**
     * @covers                   \BreakpointDebugging_LockByFileExisting::singleton
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=1
     */
    function testSingleton_C()
    {
        // Constructs instance of other class.
        $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10); // Error.
    }

}

?>
