<?php

chdir(__DIR__ . '/../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::checkUnitTestExeMode();

class LockByFlockTest extends PHPUnit_Framework_TestCase
{
    protected $lockByFlock;

    protected function setUp()
    {
        // Constructs instance.
        $this->lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10);
    }

    protected function tearDown()
    {
        // When we execute unit test, we must catch exception of "__destruct()" because exception is thrown.
        try {
            // Destructs instance.
            $this->lockByFlock = null;
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            return;
        }
    }

    function testLockByFlock1()
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

    function testLockByFlock2()
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

    function testLockByFlock3()
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

    function testLockByFlock4()
    {
        try {
            $this->lockByFlock->unlock(); // Error.
        } catch (\BreakpointDebugging_UnitTest_Exception $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testLockByFlock5()
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

    function testLockByFlock6()
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

    function testLockByFlock7()
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

    function testLockByShmop8()
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

    function testLockByShmop9()
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
