<?php

chdir(__DIR__ . '/../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::checkUnitTestExeMode();

class LockByFlockTest extends PHPUnit_Framework_TestCase
{
    protected $lockByFlock;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    function setUp()
    {
        // Constructs instance.
        $this->lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    function tearDown()
    {
        // Destructs instance.
        $this->lockByFlock = null;
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::__clone
     */
    function test__clone()
    {
        try {
            $tmp = clone $this->lockByFlock;
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::__destruct
     */
    public function test__destruct()
    {
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByFlock);
        // Calls "__destruct".
        $this->lockByFlock = null;
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::forceUnlocking
     */
    public function testForceUnlocking()
    {
        $this->lockByFlock->lock();
        $this->lockByFlock->lock();

        $this->assertTrue(B::getPropertyForTest($this->lockByFlock, '$lockCount') === 2);

        BreakpointDebugging_LockByFileExisting::forceUnlocking();

        $this->assertTrue(B::getPropertyForTest($this->lockByFlock, '$lockCount') === 0);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::lock
     * @covers \BreakpointDebugging_LockByFlock::unlock
     */
    function testLockThenUnlock_A()
    {
        try {
            $this->lockByFlock->lock();
            $this->lockByFlock->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue(true);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::lock
     * @covers \BreakpointDebugging_LockByFlock::unlock
     */
    function testLockThenUnlock_B()
    {
        try {
            $this->lockByFlock->lock();
            $this->lockByFlock->lock();
            $this->lockByFlock->unlock();
            $this->lockByFlock->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue(true);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::lock
     * @covers \BreakpointDebugging_LockByFlock::unlock
     */
    function testLockThenUnlock_C()
    {
        try {
            $this->lockByFlock->unlock(); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::lock
     * @covers \BreakpointDebugging_LockByFlock::unlock
     */
    function testLockThenUnlock_D()
    {
        try {
            $this->lockByFlock->lock();
            $this->lockByFlock->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            $this->lockByFlock->unlock(); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::lock
     * @covers \BreakpointDebugging_LockByFlock::unlock
     */
    function testLockThenUnlock_E()
    {
        try {
            $this->lockByFlock->lock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            // Calls "__destruct()".
            $this->lockByFlock = null; // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::lock
     * @covers \BreakpointDebugging_LockByFlock::unlock
     */
    function testLockThenUnlock_F()
    {
        try {
            $this->lockByFlock->lock();
            $this->lockByFlock->lock();
            $this->lockByFlock->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            // Calls "__destruct()".
            $this->lockByFlock = null; // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::singleton
     */
    function testSingleton_A()
    {
        try {
            $lockByFlock1 = &\BreakpointDebugging_LockByFlock::singleton(5, 10);
            $lockByFlock2 = &\BreakpointDebugging_LockByFlock::singleton(5, 10); // Same object.
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue($lockByFlock1 === $lockByFlock2);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::singleton
     */
    function testSingleton_B()
    {
        try {
            // Constructs instance of other class.
            $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFlock::singleton
     */
    function testSingleton_C()
    {
        try {
            // Constructs instance of other class.
            $LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

}

?>
