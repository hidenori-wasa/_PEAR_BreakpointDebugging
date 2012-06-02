<?php

chdir(__DIR__ . '/../../../../../../');
require_once './BreakpointDebugging_MySetting.php';

class Test
{

    private $shmopId;

    function __construct()
    {
        $this->shmopId = shmop_open(1234, 'w', 0600, 10);
    }

    function __destruct()
    {
        shmop_close($this->shmopId);
    }

    private function _incrementSheredMemory()
    {
        $tmpCount = shmop_read($this->shmopId, 0, 10);
        $tmpCount++;
        // Wait 0.01 seconds.
        usleep(10000);
        shmop_write($this->shmopId, sprintf('0x%08X', $tmpCount), 0);
    }

    function testLock()
    {
        // Extend maximum execution time.
        set_time_limit(300);
        $start = microtime(true);
        switch ('BreakpointDebugging_LockByShmop1') {
            case 'shmop':



                break;
            case 'BreakpointDebugging_LockByShmop1':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt', 60, 300, 10000);
                for ($count = 0; $count < 1000; $count++) {
                    $lockByShmop1->lock();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->unlock();
                }
                break;
            case 'BreakpointDebugging_LockByShmop2':
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt', 60, 300, 10000);
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
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt', 60, 300, 10000);
                $lockByShmop2 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir2/FileForLockByShmop.txt', 60, 300, 10000);
                for ($count = 0; $count < 125; $count++) {
                    $lockByShmop1->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByShmop2->lock();
                    $this->_incrementSheredMemory();
                    $this->_incrementSheredMemory();
                    $lockByShmop1->unlock();
                    $this->_incrementSheredMemory();
                    $lockByShmop2->unlock();
                }
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
                $lockByShmop1 = \BreakpointDebugging_LockByShmop::singleton(__DIR__ . '/SomethingDir/FileForLockByShmop.txt');
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
