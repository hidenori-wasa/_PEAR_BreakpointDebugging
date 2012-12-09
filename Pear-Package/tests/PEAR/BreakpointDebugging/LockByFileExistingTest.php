<?php

chdir(__DIR__ . '/../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;
use \BreakpointDebugging_UnitTestAssert as U;

B::checkUnitTestExeMode();

class BreakpointDebugging_LockByFileExistingTest extends \BreakpointDebugging_UnitTest //// For step execution.
//class BreakpointDebugging_LockByFileExistingTest extends \PHPUnit_Framework_TestCase // For continuation execution.
{
    protected $lockByFileExisting;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    function setUp()
    {
        // Constructs instance.
        $this->lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    function tearDown()
    {
        // Destructs instance.
        $this->lockByFileExisting = null;
        B::setPropertyForTest('\BreakpointDebugging_Lock', '$_internalInstance', null);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::__clone
     */
    function test__clone()
    {
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', '__clone');
            $tmp = clone $this->lockByFileExisting;
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
        $this->assertTrue(false);
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
        $internalInstance = &\BreakpointDebugging_LockByFileExisting::internalSingleton();
        $internalInstance->lock();
        $internalInstance->lock();
        $this->lockByFileExisting->lock();
        $this->lockByFileExisting->lock();

        $this->assertTrue(B::getPropertyForTest($internalInstance, '$lockCount') === 2);
        $this->assertTrue(B::getPropertyForTest($this->lockByFileExisting, '$lockCount') === 2);

        \BreakpointDebugging_Lock::forceUnlocking();

        $this->assertTrue(B::getPropertyForTest($internalInstance, '$lockCount') === 0);
        $this->assertTrue(B::getPropertyForTest($this->lockByFileExisting, '$lockCount') === 0);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::lock
     * @covers \BreakpointDebugging_LockByFileExisting::unlock
     */
    function testLockThenUnlock_A()
    {
        try {
            $this->lockByFileExisting->lock();
            clearstatcache();
            $this->assertTrue(is_file(B::getPropertyForTest($this->lockByFileExisting, '$lockFilePath')));
            $this->lockByFileExisting->unlock();
            clearstatcache();
            $this->assertTrue(!is_file(B::getPropertyForTest($this->lockByFileExisting, '$lockFilePath')));
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::lock
     * @covers \BreakpointDebugging_LockByFileExisting::unlock
     */
    function testLockThenUnlock_C()
    {
        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::lock
     * @covers \BreakpointDebugging_LockByFileExisting::unlock
     */
    function testLockThenUnlock_D()
    {
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', 'unlock');
            $this->lockByFileExisting->unlock(); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::lock
     * @covers \BreakpointDebugging_LockByFileExisting::unlock
     */
    function testLockThenUnlock_E()
    {
        try {
            $this->lockByFileExisting->lock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', '__destruct');
            // Calls "__destruct()".
            $this->lockByFileExisting = null; // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::lock
     * @covers \BreakpointDebugging_LockByFileExisting::unlock
     */
    function testLockThenUnlock_F()
    {
        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', 'unlock');
            $this->lockByFileExisting->unlock(); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::lock
     * @covers \BreakpointDebugging_LockByFileExisting::unlock
     */
    function testLockThenUnlock_G()
    {
        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', '__destruct');
            // Calls "__destruct()".
            $this->lockByFileExisting = null; // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::internalSingleton
     */
    function testInternalSingleton()
    {
        $internalInstance = B::getPropertyForTest('\BreakpointDebugging_Lock', '$_internalInstance');
        $this->assertTrue($internalInstance === null);

        $testInstance = &\BreakpointDebugging_LockByFileExisting::internalSingleton();

        $internalInstance = B::getPropertyForTest('\BreakpointDebugging_Lock', '$_internalInstance');
        $this->assertTrue($internalInstance instanceof \BreakpointDebugging_LockByFileExisting);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::singleton
     */
    function testSingleton_A()
    {
        try {
            $lockByFileExisting1 = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
            $lockByFileExisting2 = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10); // Same object.
        } catch (\Exception $e) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue($lockByFileExisting1 === $lockByFileExisting2);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::singleton
     */
    function testSingleton_B()
    {
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', 'singletonBase');
            // Constructs instance of other class.
            $LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
        $this->assertTrue(false);
    }

    /**
     * @covers \BreakpointDebugging_LockByFileExisting::singleton
     */
    function testSingleton_C()
    {
        try {
            U::registerAssertionFailureLocationOfUnitTest('BreakpointDebugging_Lock', 'singletonBase');
            // Constructs instance of other class.
            $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
        $this->assertTrue(false);
    }

}

?>
