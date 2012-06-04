<?php

chdir(__DIR__ . '/../../../../../../');
require_once './BreakpointDebugging_MySetting.php';

class Test
{

    private $shmopId;

    function __construct()
    {
        $this->shmopId = shmop_open(1234, 'c', 0600, 10);
    }

    function __destruct()
    {
        shmop_close($this->shmopId);
    }

    private function _incrementSheredMemory()
    {
        $tmpCount = shmop_read($this->shmopId, 0, 10);
        $tmpCount++;
        // Sleep 0.0001 seconds.
        usleep(100);
        shmop_write($this->shmopId, sprintf('0x%08X', $tmpCount), 0);
    }

    function testLock()
    {
        // Extend maximum execution time.
        set_time_limit(300);
        $start = microtime(true);
        switch ('LockByShmop1') {
            case 'LockByShmop1':
                //$lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt', 60, 10, 1000);
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt', 60, 300, 1000);
                //for ($count = 0; $count < 625; $count++) {
                for ($count = 0; $count < 125; $count++) {
                    $lockByShmop1->lock();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->unlock();
                }
                break;
            case 'LockByShmop2':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt');
                for ($count = 0; $count < 125; $count++) {
                    $lockByShmop1->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->unlock();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->unlock();
                }
                break;
            case 'LockByShmop3':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt');
                $lockByShmop2 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir2/FileForLockByShmop.txt'); // Error.
                break;
            case 'LockByShmop4':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt');
                $lockByShmop1->lock();
                $lockByShmop1->unlock();
                $lockByShmop1->unlock(); // Error.
                break;
            case 'LockByShmop5':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt');
                $lockByShmop1->lock(); // Error.
                break;
            case 'LockByShmop6':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt', 5, 10);
                $lockByShmop1->unlock(); // Error.
                break;
            default:
                assert(false);
        }
        var_dump(shmop_read($this->shmopId, 0, 10) + 0, microtime(true) - $start);
    }

}

$Test = new Test();
$Test->testLock();
?>
