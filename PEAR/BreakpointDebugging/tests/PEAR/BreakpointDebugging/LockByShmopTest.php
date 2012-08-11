<?php

chdir(__DIR__ . '/../../../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::checkUnitTestExeMode();

class LockByShmopTest extends PHPUnit_Framework_TestCase
{
    protected $LockByShmop;

    protected function setUp()
    {
        // Constructs instance.
        $this->LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
        // Deletes locking flag file.
        $path = B::getPropertyForTest($this->LockByShmop, '$lockFilePath');
        if (is_file($path)) {
            unlink($path);
        }
    }

    protected function tearDown()
    {
        // When we execute unit test, we must catch exception of "__destruct()" because exception is thrown.
        try {
            // Destructs instance.
            $this->LockByShmop = null;
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            return;
        }
    }

    function testLockByShmop1()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue(true);
    }

    function testLockByShmop2()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
            $this->LockByShmop->unlock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue(true);
    }

    function testLockByShmop3()
    {
        try {
            $LockByShmop1 = &\BreakpointDebugging_LockByShmop::singleton(5, 10);
            $LockByShmop2 = &\BreakpointDebugging_LockByShmop::singleton(5, 10); // Same object.
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue($LockByShmop1 === $LockByShmop2);
    }

    function testLockByShmop4()
    {
        try {
            $this->LockByShmop->unlock(); // Error.
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testLockByShmop5()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        try {
            $this->LockByShmop->unlock(); // Error.
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testLockByShmop6()
    {
        try {
            $this->LockByShmop->lock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        try {
            // Calls "__destruct()".
            $this->LockByShmop = null; // Error.
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testLockByShmop7()
    {
        try {
            $this->LockByShmop->lock();
            $this->LockByShmop->lock();
            $this->LockByShmop->unlock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        try {
            // Calls "__destruct()".
            $this->LockByShmop = null; // Error.
        } catch (\BreakpointDebugging_Error_Exception $exception) {
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
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testLockByShmop9()
    {
        try {
            // Constructs instance of other class.
            $lockByFlock = &\BreakpointDebugging_LockByFlock::singleton(5, 10); // Error.
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

}

?>
