<?php

chdir(__DIR__ . '/../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestAssert as U;

B::checkUnitTestExeMode();

class BreakpointDebugging_LockByShmopTest extends \BreakpointDebugging_UnitTest
{
    protected $LockByShmop;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    function setUp()
    {
        global $_BreakpointDebugging_EXE_MODE;

        if (($_BreakpointDebugging_EXE_MODE & B::RELEASE) && !extension_loaded('shmop')) {
            $this->markTestSkipped('"shmop" extention has been not loaded.');
        }
        // Constructs instance.
        $this->LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    function tearDown()
    {
        // Destructs instance.
        $this->LockByShmop = null;
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::__clone
     */
    function test__clone()
    {
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', '__clone');
            $tmp = clone $this->LockByShmop;
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::__destruct
     */
    public function test__destruct()
    {
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') instanceof \BreakpointDebugging_LockByShmop);
        // Calls "__destruct".
        $this->LockByShmop = null;
        $this->assertTrue(B::getPropertyForTest('\BreakpointDebugging_Lock', '$_instance') === null);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::forceUnlocking
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
     * @covers \BreakpointDebugging_LockByShmop::lock
     * @covers \BreakpointDebugging_LockByShmop::unlock
     */
    function testLockThenUnlock_A()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::lock
     * @covers \BreakpointDebugging_LockByShmop::unlock
     */
    function testLockThenUnlock_B()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
            $this->LockByShmop->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::lock
     * @covers \BreakpointDebugging_LockByShmop::unlock
     */
    function testLockThenUnlock_C()
    {
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', 'unlock');
            $this->LockByShmop->unlock(); // Error.
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::lock
     * @covers \BreakpointDebugging_LockByShmop::unlock
     */
    function testLockThenUnlock_D()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', 'unlock');
            $this->LockByShmop->unlock(); // Error.
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::lock
     * @covers \BreakpointDebugging_LockByShmop::unlock
     */
    function testLockThenUnlock_E()
    {
        try {
            $this->LockByShmop->lock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', '__destruct');
            // Calls "__destruct()".
            $this->LockByShmop = null; // Error.
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::lock
     * @covers \BreakpointDebugging_LockByShmop::unlock
     */
    function testLockThenUnlock_F()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', '__destruct');
            // Calls "__destruct()".
            $this->LockByShmop = null; // Error.
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::singleton
     */
    function testSingleton_A()
    {
        try {
            $LockByShmop1 = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
            $LockByShmop2 = &\BreakpointDebugging_LockByShmop::singleton(5, 10); // Same object.
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue($LockByShmop1 === $LockByShmop2);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::singleton
     */
    function testSingleton_B()
    {
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', 'singletonBase');
            // Constructs instance of other class.
            $lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10); // Error.
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByShmop::singleton
     */
    function testSingleton_C()
    {
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', 'singletonBase');
            // Constructs instance of other class.
            $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10); // Error.
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            return;
        }
        $this->assertTrue(false);
    }

}

?>
