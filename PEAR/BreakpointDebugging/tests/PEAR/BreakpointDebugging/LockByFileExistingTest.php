<?php

chdir(__DIR__ . '/../../../../../');
require_once './PEAR_Setting/BreakpointDebugging_MySetting.php';

use \BreakpointDebugging as B;

B::checkUnitTestExeMode();

class LockByFileExistingTest extends PHPUnit_Framework_TestCase
{
    protected $lockByFileExisting;

    protected function setUp()
    {
        // Constructs instance.
        $this->lockByFileExisting = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
        // Deletes locking flag file.
        $path = B::getPropertyForTest($this->lockByFileExisting, '$lockFilePath');
        if (is_file($path)) {
            unlink($path);
        }
    }

    protected function tearDown()
    {
        // When we execute unit test, we must catch exception of "__destruct()" because exception is thrown.
        try {
            // Destructs instance.
            $this->lockByFileExisting = null;
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            return;
        }
    }

    function testWhole1()
    {
        try {
            $this->lockByFileExisting->lock();
            clearstatcache();
            $this->assertTrue(is_file(B::getPropertyForTest($this->lockByFileExisting, '$lockFilePath')));
            $this->lockByFileExisting->unlock();
            clearstatcache();
            $this->assertTrue(!is_file(B::getPropertyForTest($this->lockByFileExisting, '$lockFilePath')));
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue(true);
    }

    function testFopen()
    {
        try {
            // Extend maximum execution time.
            set_time_limit(300);
            restore_error_handler();
            @unlink(BreakpointDebugging::$workDir . '/LockFlag.file');
            for ($count = 0; $count < 10; $count++) {
                while (!($pFile = @fopen(BreakpointDebugging::$workDir . '/LockFlag.file', 'x+b')));
                chmod(BreakpointDebugging::$workDir . '/LockFlag.file', 0600);
                fclose($pFile);
                while (!@unlink(BreakpointDebugging::$workDir . '/LockFlag.file'));
            }
            set_error_handler('BreakpointDebugging::errorHandler', -1);
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue(true);
    }

    function testLockByFileExisting1()
    {
        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue(true);
    }

    function testLockByFileExisting2()
    {
        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue(true);
    }

    function testLockByFileExisting3()
    {
        try {
            $lockByFileExisting1 = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10);
            $lockByFileExisting2 = &\BreakpointDebugging_LockByFileExisting::singleton(5, 10); // Same object.
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        $this->assertTrue($lockByFileExisting1 === $lockByFileExisting2);
    }

    function testLockByFileExisting4()
    {
        try {
            $this->lockByFileExisting->unlock(); // Error.
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testLockByFileExisting5()
    {
        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        try {
            $this->lockByFileExisting->unlock(); // Error.
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testLockByFileExisting6()
    {
        try {
            $this->lockByFileExisting->lock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        try {
            // Calls "__destruct()".
            $this->lockByFileExisting = null; // Error.
        } catch (\BreakpointDebugging_Error_Exception $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    function testLockByFileExisting7()
    {
        try {
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->lock();
            $this->lockByFileExisting->unlock();
        } catch (\Exception $exception) {
            $this->assertTrue(false);
            return;
        }
        try {
            // Calls "__destruct()".
            $this->lockByFileExisting = null; // Error.
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
            $LockByShmop = &\BreakpointDebugging_LockByShmop::singleton(5, 10); // Error.
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
