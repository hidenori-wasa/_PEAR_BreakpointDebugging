<?php

chdir(__DIR__ . '/../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::isUnitTestExeMode(true);
class BreakpointDebugging_LockByFlockTest extends \BreakpointDebugging_UnitTestOverriding
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
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \BreakpointDebugging_ErrorException
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__clone
     */
    function test__clone()
    {
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
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=1
     */
    function testLockThenUnlock_C()
    {
        $this->lockByFlock->unlock();
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=unlock ID=1
     */
    function testLockThenUnlock_D()
    {
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
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=1
     */
    function testLockThenUnlock_E()
    {
        $this->lockByFlock->lock();
        // Calls "__destruct()".
        $this->lockByFlock = null; // Error.
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=__destruct ID=1
     */
    function testLockThenUnlock_F()
    {
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
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=1
     */
    function testSingleton_B()
    {
        // Constructs instance of other class.
        $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock<extended>
     *
     * @expectedException        \PHPUnit_Framework_Error
     * @expectedExceptionMessage CLASS=BreakpointDebugging_Lock FUNCTION=singletonBase ID=1
     */
    function testSingleton_C()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (($_BreakpointDebugging_EXE_MODE & B::RELEASE) && !extension_loaded('shmop')) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Constructs instance of other class.
        $LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
    }

}

?>
